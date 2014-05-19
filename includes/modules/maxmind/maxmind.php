<?php
//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |
// | http://www.zen-cart.com/index.php                                    |
// |                                                                      |
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
//  $Id: maxmind.php 1.0 2005-11-10 21:15:39Z ses707 $
//

$oID = $insert_id;

$maxmind_license = $db->Execute("select configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MAXMIND_LICENSE'");
$h["license_key"] = $maxmind_license->fields['configuration_value'];

$check_status_query = "select customers_name, billing_street_address, billing_city, billing_postcode, customers_state, billing_country, customers_telephone, customers_email_address, cc_number, delivery_street_address, delivery_city, delivery_postcode, ip_address from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'";
$check_status = $db->Execute($check_status_query);
		
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

// Required fields
$h["i"] = $check_status->fields['ip_address'];             		// set the client ip address
$h["city"] = $check_status->fields['billing_city'];             // set the billing city
$h["region"] = $check_state->fields['zone_code'];               // set the billing state
$h["postal"] = $check_status->fields['billing_postcode'];       // set the billing zip code
$h["country"] = $check_country->fields['countries_iso_code_2']; // set the billing country

// Recommended fields
$h["domain"] = $domain;		// Email domain
$h["bin"] = $cc;			// bank identification number
//$h["forwardedIP"] = "";	// X-Forwarded-For or Client-IP HTTP Header
$h["custPhone"] = $phone;;	// Area-code and local prefix of customer phone number

// Optional fields
//$h["binName"] = "";	// bank name
//$h["binPhone"] = "";	// bank customer service phone number on back of credit card
//$h["requested_type"] = "";	// Which level (free, city, premium) of CCFD to use
$h["emailMD5"] = $check_status->fields['customers_email_address']; // CCFS will hash for us
$h["shipAddr"] = $check_status->fields['delivery_street_address'];	// Shipping Address
$h["shipCity"] = $check_status->fields['delivery_city'];	// the City to Ship to
$h["shipRegion"] = $check_delivery_state->fields['delivery_zone_code'];	// the Region to Ship to
$h["shipPostal"] = $check_status->fields['delivery_postcode'];	// the Postal Code to Ship to
$h["shipCountry"] = $check_delivery_country->fields['delivery_countries_iso_code_2'];	// the country to Ship to

$h["txnID"] = $oID;			// Transaction ID
//$h["sessionID"] = "";		// Session ID

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
                         'spam_score' => $h['spamScore'],
                         'trans_proxy' => $h['isTransProxy'],
                         'free_mail' => $h['freeMail'],
                         'carder_email' => $h['carderEmail'],
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