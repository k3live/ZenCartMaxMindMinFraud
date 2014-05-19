<?php
//
// +----------------------------------------------------------------------+
// |MaxMind CCFD Module for Zen-Cart Open Source E-commerce               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license.       |
// +----------------------------------------------------------------------+
//  $Id: maxmind_update.php 1.3 2007-01-05 23:25:29Z ses707 $
//

require('includes/application_top.php');

$oID = $_GET['oID'];
$requested_type = $_GET['requested_type'];

$maxmind_license = $db->Execute("select configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MAXMIND_LICENSE'");

$check_status_query = "select customers_name, billing_street_address, billing_city, billing_state, billing_postcode, billing_country, customers_telephone, customers_email_address, cc_number, delivery_street_address, delivery_city, delivery_state, delivery_postcode, delivery_country, ip_address, cc_bin_name, cc_bin_phone from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'";
$check_status = $db->Execute($check_status_query);

$check_customer_password_query = "select customers_password from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $check_status->fields['customers_email_address'] . "'";
$check_customer_password = $db->Execute($check_customer_password_query);
		
if (strlen($check_status->fields['billing_country']) <= 2) {
	$check_country->fields = array (
		"countries_iso_code_2" => $check_status->fields['billing_country']); }
else {
$check_country_query = "select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_name = '" . $check_status->fields['billing_country'] . "'";
$check_country = $db->Execute($check_country_query); }

if (strlen($check_status->fields['delivery_country']) <= 2) {
	$check_delivery_country->fields = array (
		"countries_iso_code_2" => $check_status->fields['delivery_country']); }
else {
$check_delivery_country_query = "select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_name = '" . $check_status->fields['delivery_country'] . "'";
$check_delivery_country = $db->Execute($check_delivery_country_query); }
		
if (strlen($check_status->fields['billing_state']) <= 2) {
	$check_state->fields = array (
		"zone_code" => $check_status->fields['billing_state']); }
else {
$check_state_query = "select zone_code from " . TABLE_ZONES . " where zone_name = '" . $check_status->fields['billing_state'] . "'";
$check_state = $db->Execute($check_state_query); }

if (strlen($check_status->fields['delivery_state']) <= 2) {
	$check_delivery_state->fields = array (
		"zone_code" => $check_status->fields['delivery_state']); }
else {
$check_delivery_state_query = "select zone_code from " . TABLE_ZONES . " where zone_name = '" . $check_status->fields['delivery_state'] . "'";
$check_delivery_state = $db->Execute($check_delivery_state_query); }

$str = $check_status->fields['customers_email_address'];
list ($addy, $domain) = split ('[@]', $str);

$phstr = preg_replace( '/[^0123456789]/', '', $check_status->fields['customers_telephone']);
$phone = substr($phstr, 0, 6);

$cc_full = $check_status->fields['cc_number'];
$cc = substr($cc_full, 0, 6); 

//Split IP Address - Why these are in the same field bewilders me.
$full_order_ip = $check_status->fields['ip_address'];
$partial_order_ip = explode(' - ', trim($full_order_ip, '-'));

require(DIR_FS_CATALOG_MODULES . 'maxmind/CreditCardFraudDetection.php');

// Create a new CreditCardFraudDetection object
$ccfs = new CreditCardFraudDetection;

// Set inputs and store them in a hash
// See http://www.maxmind.com/app/ccv for more details on the input fields

// Enter your license key here (Required)
$h["license_key"] = $maxmind_license->fields['configuration_value'];

// Required fields
$h["i"] = $partial_order_ip[0];      				// set the client ip address
$h["city"] = $check_status->fields['billing_city'];             // set the billing city
$h["region"] = $check_state->fields['zone_code'];               // set the billing state
$h["postal"] = $check_status->fields['billing_postcode'];       // set the billing zip code
$h["country"] = $check_country->fields['countries_iso_code_2']; // set the billing country

// Recommended fields
$h["domain"] = $domain;						// Email domain
$h["bin"] = $cc;						// bank identification number
//$h["forwardedIP"] = "";					// X-Forwarded-For or Client-IP HTTP Header
// CreditCardFraudDetection.php will take
// MD5 hash of e-mail address passed to emailMD5 if it detects '@' in the string
$h["emailMD5"] = $check_status->fields['customers_email_address']; // CCFS will hash for us
// CreditCardFraudDetection.php will take the MD5 hash of the username/password if the length of the string is not 32
$h["usernameMD5"] = $check_status->fields['customers_email_address']; // CCFS will hash for us
$h["passwordMD5"] = $check_customer_password->fields['customers_password']; // CCFS will hash for us

// Optional fields
$h["binName"] = $check_status->fields['cc_bin_name'];			// bank name
$h["binPhone"] = $check_status->fields['cc_bin_phone'];			// bank customer service phone number on back of credit card
$h["custPhone"] = $phone;						// Area-code and local prefix of customer phone number
$h["requested_type"] = $requested_type;							// Which level (free, city, premium) of CCFD to use
$h["shipAddr"] = $check_status->fields['delivery_street_address'];	// Shipping Address
$h["shipCity"] = $check_status->fields['delivery_city'];		// the City to Ship to
$h["shipRegion"] = $check_delivery_state->fields['zone_code'];	// the Region to Ship to
$h["shipPostal"] = $check_status->fields['delivery_postcode'];		// the Postal Code to Ship to
$h["shipCountry"] = $check_delivery_country->fields['countries_iso_code_2'];	// the country to Ship to

$h["txnID"] = $oID;							// Transaction ID
//$h["sessionID"] = "";							// Session ID

//$h["accept_language"] = "";
//$h["user_agent"] = "";

// If you want to disable Secure HTTPS or don't have Curl and OpenSSL installed
// uncomment the next line
// $ccfs->isSecure = 0;

// set the timeout to be five seconds
$ccfs->timeout = 5;

// uncomment to turn on debugging
// $ccfs->debug = 1;

// how many seconds to cache the ip addresses
// $ccfs->wsIpaddrRefreshTimeout = 3600*5;

// file to store the ip address for minfraud1.maxmind.com and minfraud2.maxmind.com
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
$sql_data_array = array( 'order_id' => $oID,
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
                         'risk_score' => $h['riskScore'],
                         'explanation' => $h['explanation'],
                         'queries_remaining' => $h['queriesRemaining'],
                         'maxmind_id' => $h['maxmindID'],
                         'err' => $h['err']);

	zen_db_perform(TABLE_ORDERS_MAXMIND, $sql_data_array);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<script language="JavaScript" src="includes/menu.js" type="text/JavaScript"></script>
<link href="includes/stylesheet.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS" />
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body onload="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
     <div class="pageHeading">MaxMind Update</div>
    </td>
  </tr>
  <tr>
   <td><?php echo '<a href="' . zen_href_link(FILENAME_ORDERS, 'oID=' . $oID . '&action=edit', 'NONSSL') . '">' . MAXMIND_BACK . '</a>'; ?></td>
  </tr>
  <table width="80%" border="1" align="center" cellpadding="3" cellspacing="0">
  <tr>
    <td valign="top">The Following Was Submitted to MaxMind</td>
    <td>The Following Was Received From MaxMind</td>
  </tr>
  <tr>
  	<td valign="top" width="50%">
<?php print_r(	'IP Address: ' . $partial_order_ip[0] . '<br />' .
				'Billing city: ' . $check_status->fields['billing_city'] . '<br />' .
				'Billing State: ' . $check_state->fields['zone_code'] . '<br />' .
				'Billing Postal Code: ' . $check_status->fields['billing_postcode'] . '<br />' .
				'Billing country: ' . $check_country->fields['countries_iso_code_2'] . '<br />' .
				'E-Mail Domain: ' . $domain . '<br />' .
				'Credit Card Number: ' . $cc . '<br />' .
				'Bin Name: ' . $check_status->fields['cc_bin_name'] . '<br />' .
				'Bin Phone: ' . $check_status->fields['cc_bin_phone'] . '<br />' .
				'Telephone Number: ' . $phone . '<br />' .
				'Requested Type: ' . $requested_type . '<br />' .
				'License Key: ' . $maxmind_license->fields['configuration_value'] . '<br />' .
				'E-Mail Address: ' . $check_status->fields['customers_email_address'] . '<br />' .
				'Username: ' . $check_status->fields['customers_email_address'] . '<br />' .
				'Password: ' . $check_customer_password->fields['customers_password'] . '<br />' .
				'Delivery Street Address: ' . $check_status->fields['delivery_street_address'] . '<br />' .
				'Delivery City: ' . $check_status->fields['delivery_city'] . '<br />' .
				'Delivery State: ' . $check_delivery_state->fields['zone_code'] . '<br />' .
				'Delivery Postal Code: ' . $check_status->fields['delivery_postcode'] . '<br />' .
				'Delivery Country: ' . $check_delivery_country->fields['countries_iso_code_2'] . '<br />' .
				'Order ID: ' . $oID); ?>
    </td>
    <td>
<?php
$numoutputkeys = count($h);
for ($i = 0; $i < $numoutputkeys; $i++) {
  $key = $outputkeys[$i];
  $value = $h[$key];
  print $key . " = " . $value . "\n";
  echo '<br />';
}
?>
    </td>
  </tr>
 </table>
  <tr>
   <td><?php echo '<a href="' . zen_href_link(FILENAME_MAXMIND_ORDERS, 'oID=' . $oID . '&action=edit', 'NONSSL') . '">' . MAXMIND_BACK . '</a>'; ?></td>
  </tr>
</table>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>