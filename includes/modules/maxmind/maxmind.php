<?php
//
// +----------------------------------------------------------------------+
// |MaxMind CCFD Module for Zen-Cart Open Source E-commerce               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license.       |
// +----------------------------------------------------------------------+
//  $Id: maxmind.php 1.1 2007-01-05 23:21:39Z ses707 $
//

$oID = $insert_id;

$maxmind_license = $db->Execute("select configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MAXMIND_LICENSE'");
$h["license_key"] = $maxmind_license->fields['configuration_value'];

$check_status_query = "select customers_name, billing_street_address, billing_city, billing_postcode, customers_state, billing_country, customers_telephone, customers_email_address, cc_number, delivery_street_address, delivery_city, delivery_postcode, ip_address, cc_bin_name, cc_bin_phone from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'";
$check_status = $db->Execute($check_status_query);

$check_customer_password_query = "select customers_password from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $check_status->fields['customers_email_address'] . "'";
$check_customer_password = $db->Execute($check_customer_password_query);
		
$check_country_query = "select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_name = '" . $check_status->fields['billing_country'] . "'";
$check_country = $db->Execute($check_country_query);

$delivery_country_query = "select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_name = '" . $check_status->fields['delivery_country'] . "'";
$delivery_country = $db->Execute($delivery_country_query);
		
$check_state_query = "select zone_code from " . TABLE_ZONES . " where zone_name = '" . $check_status->fields['billing_state'] . "'";
$check_state = $db->Execute($check_state_query);

$delivery_state_query = "select zone_code from " . TABLE_ZONES . " where zone_name = '" . $check_status->fields['delivery_state'] . "'";
$delivery_state = $db->Execute($delivery_state_query);

require(DIR_WS_MODULES . 'maxmind/CreditCardFraudDetection.php');
$ccfs = new CreditCardFraudDetection;

$str = $check_status->fields['customers_email_address'];
list ($addy, $domain) = split ('[@]', $str);

$phstr = preg_replace( '/[^0123456789]/', '', $check_status->fields['customers_telephone']);
$phone = substr($phstr, 0, 6);

$cc_full = $check_status->fields['cc_number'];
$cc = substr($cc_full, 0, 6); 

//Split IP Address - Why these are in the same field bewilders me.
$full_order_ip = $check_status->fields['ip_address'];
$partial_order_ip = explode(' - ', $full_order_ip);

// Required fields
$h["i"] = $partial_order_ip[0];      							// set the client ip address
$h["city"] = $check_status->fields['billing_city'];             // set the billing city
$h["region"] = $check_state->fields['zone_code'];               // set the billing state
$h["postal"] = $check_status->fields['billing_postcode'];       // set the billing zip code
$h["country"] = $check_country->fields['countries_iso_code_2']; // set the billing country

// Recommended fields
$h["domain"] = $domain;											// Email domain
$h["bin"] = $cc;												// bank identification number
//$h["forwardedIP"] = "";										// X-Forwarded-For or Client-IP HTTP Header
// MD5 hash of e-mail address passed to emailMD5 if it detects '@' in the string
$h["emailMD5"] = $check_status->fields['customers_email_address']; // CCFS will hash for us
// CreditCardFraudDetection.php will take the MD5 hash of the username/password if the length of the string is not 32
$h["usernameMD5"] = $check_status->fields['customers_email_address']; // CCFS will hash for us
$h["passwordMD5"] = $check_customer_password['customers_password']; // CCFS will hash for us

// Optional fields
$h["binName"] = $check_status->fields['cc_bin_name'];			// bank name
$h["binPhone"] = $check_status->fields['cc_bin_phone'];			// bank customer service phone number on back of credit card
$h["custPhone"] = $phone;										// Area-code and local prefix of customer phone number
//$h["requested_type"] = "";									// Which level (free, city, premium) of CCFD to use
$h["shipAddr"] = $check_status->fields['delivery_street_address'];	// Shipping Address
$h["shipCity"] = $check_status->fields['delivery_city'];			// the City to Ship to
$h["shipRegion"] = $check_delivery_state->fields['delivery_zone_code'];	// the Region to Ship to
$h["shipPostal"] = $check_status->fields['delivery_postcode'];		// the Postal Code to Ship to
$h["shipCountry"] = $check_delivery_country->fields['delivery_countries_iso_code_2'];	// the country to Ship to

$h["txnID"] = $oID;												// Transaction ID
//$h["sessionID"] = "";											// Session ID

// If you want to disable Secure HTTPS or don't have Curl and OpenSSL installed
// uncomment the next line
// $ccfs->isSecure = 0;

// set the timeout to be five seconds
$ccfs->timeout = 5;

// uncomment to turn on debugging
// $ccfs->debug = 1;

// how many seconds to cache the ip addresses
// $ccfs->wsIpaddrRefreshTimeout = 3600*5;

// file to store the ip address for www.maxmind.com and www2.maxmind.com
// $ccfs->wsIpaddrCacheFile = "/tmp/maxmind.ws.cache";

// if useDNS is 1 then use DNS, otherwise use ip addresses directly
$ccfs->useDNS = 0;

$ccfs->isSecure = 0;

// next we set up the input hash
$ccfs->input($h);

// then we query the server
$ccfs->query();

// then we get the result from the server
$h = $ccfs->output();

// then finally we print out the result
$outputkeys = array_keys($h);
$sql_data_array = array( 'order_id' => $insert_id,
                         'country_match' => $h['countryMatch'],
                         'country_code' => $h['countryCode'],
                         'hi_risk' => $h['highRiskCountry'],
                         'distance' => $h['distance'],
                         'ip_region' => $h['ip_region'],
                         'ip_city' => $h['ip_city'],
                         'ip_latitude' => $h['ip_latitude'],
                         'ip_longitude' => $h['ip_longitude'],
                         'ip_isp' => $h['ip_isp'],
                         'ip_org' => $h['ip_org'],
                         'anonymous_proxy' => $h['anonymousProxy'],
                         'proxy_score' => $h['proxyScore'],
                         'trans_proxy' => $h['isTransProxy'],
                         'free_mail' => $h['freeMail'],
                         'carder_email' => $h['carderEmail'],
						 'high_risk_username' => $h['highRiskUsername'],
						 'high_risk_password' => $h['highRiskPassword'],
                         'bin_match' => $h['binMatch'],
                         'bin_country' => $h['binCountry'],
                         'bin_name_match' => $h['binNameMatch'],
                         'bin_name' => $h['binName'],
                         'bin_phone_match' => $h['binPhoneMatch'],
                         'bin_phone' => $h['binPhone'],
                         'cust_phone' => $h['custPhoneInBillingLoc'],
                         'ship_forward' => $h['shipForward'],
                         'city_postal_match' => $h['cityPostalMatch'],
                         'ship_city_postal_match' => $h['shipCityPostalMatch'],
                         'score' => $h['score'],
                         'queries_remaining' => $h['queriesRemaining'],
                         'maxmind_id' => $h['maxmindID'],
                         'err' => $h['err']);

	zen_db_perform(TABLE_ORDERS_MAXMIND, $sql_data_array);
?>