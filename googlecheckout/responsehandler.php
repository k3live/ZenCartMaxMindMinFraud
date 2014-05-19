<?php
/*
  Copyright (C) 2007 Google Inc.

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/* **GOOGLE CHECKOUT ** v1.4
 * @version $Id: responsehandler.php 5342 2007-06-04 14:58:57Z ropu $
 * Script invoked for any callback notfications from the Checkout server
 * Can be used to process new order notifications, order state changes and risk notifications
 */

// 1. Setup the log file
// 2. Parse the http header to verify the source
// 3. Parse the XML message
// 4. Trasfer control to appropriate function
error_reporting(E_ALL);

// temporal disable of multisocket 
	define('MODULE_PAYMENT_GOOGLECHECKOUT_MULTISOCKET', 'False');

  chdir('./..');
  $curr_dir = getcwd();
  define('API_CALLBACK_ERROR_LOG', $curr_dir."/googlecheckout/logs/response_error.log");
  define('API_CALLBACK_MESSAGE_LOG', $curr_dir."/googlecheckout/logs/response_message.log");

  require_once($curr_dir.'/googlecheckout/library/googlemerchantcalculations.php');
  require_once($curr_dir.'/googlecheckout/library/googleresult.php');
  require_once($curr_dir.'/googlecheckout/library/googlerequest.php');
  require_once($curr_dir.'/googlecheckout/library/googleresponse.php');

  $Gresponse = new GoogleResponse();
  //Setup the log files
  $Gresponse->SetLogFiles(API_CALLBACK_ERROR_LOG, API_CALLBACK_MESSAGE_LOG, L_ALL);

  // Retrieve the XML sent in the HTTP POST request to the ResponseHandler
  $xml_response = isset($HTTP_RAW_POST_DATA)?
                    $HTTP_RAW_POST_DATA:file_get_contents("php://input");
  if (get_magic_quotes_gpc()) {
    $xml_response = stripslashes($xml_response);
  }
  list($root, $data) = $Gresponse->GetParsedXML($xml_response);
  if(isset($data[$root]['shopping-cart']['merchant-private-data']['session-data']['VALUE'])) {
    list($sess_id,$sess_name) = explode(";",
        $data[$root]['shopping-cart']['merchant-private-data']['session-data']['VALUE']);
    //If session management is supported by this PHP version
    if(function_exists('session_id'))
      session_id($sess_id);
    if(function_exists('session_name'))
      session_name($sess_name);
  }
  include('includes/application_top.php');
  include('includes/modules/payment/googlecheckout.php');
//BOF - define value for languages_id//define home page  - added by colosports
  $attributes = $db->Execute("select languages_id
                                      from " . TABLE_LANGUAGES . "
                                      where name = '".$_SESSION['language']."'
                                      ");
  $languages_id = $attributes->fields['languages_id'];
//EOF - define value for languages_id//define home page  - added by colosports

//  zen_session_start();
  if (isset($_SESSION['cart']) && is_object($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
    $cart->restore_contents();
  }
  else {
    $Gresponse->SendServerErrorStatus("Shopping cart not obtained from session.");
  }
  $googlepayment = new googlecheckout();
  $Gresponse->SetMerchantAuthentication($googlepayment->merchantid, 
                                        $googlepayment->merchantkey);

// Check if is CGI install, if so .htaccess is needed
	if(MODULE_PAYMENT_GOOGLECHECKOUT_CGI != 'True') {
    $Gresponse->HttpAuthentication();
	}
  switch ($root) {
    case "request-received": {
      process_request_received_response($Gresponse);
      break;
    }
    case "error": {
      process_error_response($Gresponse);
      break;
    }
    case "diagnosis": {
      process_diagnosis_response($Gresponse);
      break;
    }
    case "checkout-redirect": {
      process_checkout_redirect($Gresponse);
      break;
    }
    case "merchant-calculation-callback": {
	  	if(MODULE_PAYMENT_GOOGLECHECKOUT_MULTISOCKET == 'True') {
		  	include_once($curr_dir .'/googlecheckout/multisocket.php');
		  	process_merchant_calculation_callback($Gresponse, 2.7, false);
	  		break;
	  	}
    }
    case "merchant-calculation-callback-single": {
// 			set_time_limit(5); 
      process_merchant_calculation_callback_single($Gresponse);
      break;
    }
//
//    case "new-order-notification_2": {
//
//    
//    // 1. Get cart contents
//    // 2. Add a row in orders table
//    // 3. Add a row for each product in orders_products table
//    // 4. Add rows if required to orders_products_attributes table
//    // 5. Add a row to orders_status_history and orders_total
//    // 6. Check stock configuration and update inventory if required
////        global $db;
//        list($root, $data) = $Gresponse->GetParsedXML();
//        $currencies = new currencies();
//    //    $products = $cart->get_products();
//    //Check if buyer had logged in
//        if(isset($customer_id) && $customer_id != '') {
//          $cust_id = $customer_id;
//          $oper="update";
//          $params = ' customers_id = '.$cust_id;
//        } else {
//    // Else check if buyer is a new user from Google Checkout
//          $customer_info = $db->Execute("select customers_id from " .
//                                $googlepayment->table_name  . " where buyer_id = " .
//                                makeSqlString($data[$root]['buyer-id']['VALUE'])  );
//          if($customer_info->RecordCount() == 0)  {
//            // Add if new user
//            $full_name = makeSqlString($data[$root]['buyer-billing-address']['contact-name']['VALUE']); //Returns full name.
//            $last_name = strrev(strtok(strrev($full_name), " ")); //Returns surname.
//            $first_name = substr($full_name, 0, ((strlen($last_name)+1)*-1)); //Returns full name minus last name.
//            $sql_data_array = array('customers_gender' => '',
//                            'customers_firstname' => $first_name,
//                              'customers_lastname' => $last_name,
//                              'customers_dob' => 'now()',
//                              'customers_email_address' => $data[$root]['buyer-billing-address']['email']['VALUE'],
//                              'customers_default_address_id' => 0,
//                              // I take email from 
//                              
//                              'customers_telephone' => $data[$root]['buyer-billing-address']['phone']['VALUE'],
//                              'customers_fax' => $data[$root]['buyer-billing-address']['fax']['VALUE'],
//                              'customers_password' => zen_encrypt_password(makeSqlString($data[$root]['buyer-id']['VALUE'])),
//                              'customers_newsletter' => '');
//            zen_db_perform(TABLE_CUSTOMERS, $sql_data_array);
//    
//            $cust_id = $db->insert_ID();
//            $sql_data_array = array('customers_info_id' => $cust_id,
//                                'customers_info_date_of_last_logon' => '',
//                                'customers_info_number_of_logons' => '',
//                                'customers_info_date_account_created' => 'now()',
//                                'customers_info_date_account_last_modified' => 'now()',
//                                'global_product_notifications' => '');
//            zen_db_perform(TABLE_CUSTOMERS_INFO, $sql_data_array);
//            $db->Execute("insert into ". $googlepayment->table_name . " " .
//                " values ( " . $cust_id. ", ". $data[$root]['buyer-id']['VALUE']. ")");
//            $oper="insert";
//            $params="";
//          } else {
//            $cust_id = $customer_info->fields['customers_id'];
//            $oper="update";
//            $params = ' customers_id = '.(int)$cust_id;
//          }
//        }
//    // Update address book with the latest entry
//    // This has the disadvantage of overwriting an existing address book entry of the user
//    //BOF - Fix address book bug - added by colosports
//      $buyer_state = $data[$root]['buyer-shipping-address']['region']['VALUE'];
//        $zone_answer = $db->Execute("select zone_id, zone_country_id from ". TABLE_ZONES . " where zone_code = '$buyer_state'");
//    
//        $full_name = makeSqlString($data[$root]['buyer-shipping-address']['contact-name']['VALUE']); //Returns full name.
//        $last_name = strrev(strtok(strrev($full_name), " ")); //Returns surname.
//        $first_name = substr($full_name, 0, ((strlen($last_name)+1)*-1)); //Returns full name minus last name.
//    
//       $sql_data_array = array('customers_id' => $cust_id,
//                              'entry_gender' => '',
//                              'entry_company' => $data[$root]['buyer-shipping-address']['company-name']['VALUE'],
//                              'entry_firstname' => $first_name,
//                              'entry_lastname' => $last_name,
//                              'entry_street_address' => $data[$root]['buyer-shipping-address']['address1']['VALUE'],
//                              'entry_suburb' => $data[$root]['buyer-shipping-address']['address2']['VALUE'],
//                              'entry_postcode' => $data[$root]['buyer-shipping-address']['postal-code']['VALUE'],
//                              'entry_city' => $data[$root]['buyer-shipping-address']['city']['VALUE'],
//                              'entry_state' => $data[$root]['buyer-shipping-address']['region']['VALUE'],
//                              'entry_country_id' => $zone_answer->fields['zone_country_id'],
//                              'entry_zone_id' => $zone_answer->fields['zone_id']);
//    
//    // Check database to see if the address exist.
//        $address_book = $db->Execute("select address_book_id from ". TABLE_ADDRESS_BOOK . "
//                where   customers_id = '$cust_id'
//                  and entry_street_address = '".makeSqlString($data[$root]['buyer-shipping-address']['address1']['VALUE'])."'
//                    and entry_suburb = '".makeSqlString($data[$root]['buyer-shipping-address']['address2']['VALUE'])."'
//                    and entry_postcode = '".makeSqlString($data[$root]['buyer-shipping-address']['postal-code']['VALUE'])."'
//                    and entry_city = '".makeSqlString($data[$root]['buyer-shipping-address']['city']['VALUE'])."'
//                  ");
//    //If the address does not exist, the address will be store in the address book.
//      if ($address_book->RecordCount() == 0) {
//        zen_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
//      }
//    //EOF - Fix address book bug - added by colosports
//
//
//
//
//
////      $zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_BEGIN');
////      j
//      $items = get_arr_result($data[$root]['shopping-cart']['items']['item']);
//      $products = $order_totals = array();
//      foreach($items as $item){
//        if(isset($item['merchant-private-item-data']['item']['VALUE'])) {
//          $products[] = unserialize(base64_decode(
//                           $item['merchant-private-item-data']['item']['VALUE']));
//        }
//        else if($item['merchant-private-item-data']['order_total']['VALUE']) {
//          $order_totals[] = unserialize(base64_decode(
//                    $item['merchant-private-item-data']['order_total']['VALUE']));
//        }
//      }
//      
//      require_once(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
//      
////Add the order details to the table
//// This table could be modified to hold the merchant id and key if required
//// so that different mids and mkeys can be used for different orders
//      $db->Execute("insert into " . $googlepayment->table_order . " values (" . $orders_id . ", ".
//          makeSqlString($data[$root]['google-order-number']['VALUE']) . ", " .
//          makeSqlFloat($data[$root]['order-total']['VALUE']) . ")");
//  
//      foreach($data[$root]['order-adjustment']['shipping'] as $ship); {
//        $shipping =  $ship['shipping-name']['VALUE'];
//        $ship_cost = $ship['shipping-cost']['VALUE'];
//      }
//      $tax_amt = $data[$root]['order-adjustment']['total-tax']['VALUE'];
//      $order_total = $data[$root]['order-total']['VALUE'];
//
//      // load selected payment module
//      require_once(DIR_WS_CLASSES . 'payment.php');
//      $payment_modules = new payment($_SESSION['payment']);
//      // load the selected shipping module
//      require_once(DIR_WS_CLASSES . 'shipping.php');
//      $shipping_modules = new shipping($_SESSION['shipping']);
//      
//      require_once(DIR_WS_CLASSES . 'order.php');
//      $order = new order;
////      print_r($order_totals);
//      $order->info['payment_module_code'] = 'googlecheckout';
//
//      $methods_hash = $googlepayment->getMethods();
//      if(isset($data[$root]['order-adjustment']['shipping']['merchant-calculated-shipping-adjustment']['shipping-name']['VALUE'])) {
//        $name = $data[$root]['order-adjustment']['shipping']['merchant-calculated-shipping-adjustment']['shipping-name']['VALUE'];
//      }
//      else {
//        $name = $data[$root]['order-adjustment']['shipping']['flat-rate-shipping-adjustment']['shipping-name']['VALUE'];
//      }
//      list($a, $method_name) = explode(': ',$name);
//
//      $order->info['shipping_method'] = $methods_hash[$method_name][0];
//      $order->info['shipping_module_code'] = $methods_hash[$method_name][2];
//
//
//      $order->info['total'] = $data[$root]['order-total']['VALUE'];
//      $order->info['subtotal'] = $data[$root]['order-total']['VALUE'] - ($ship_cost + $tax_amt) + @$coupons[0]['applied-amount']['VALUE'] ;
//      $order->info['coupon_code'] = @$coupons[0]['code']['VALUE'];
//      $order->info['shipping_method'] = $shipping;
//      $order->info['shipping_cost'] = $ship_cost;
//      $order->info['tax_groups']['tax'] = $tax_amt ;
//      $order->info['currency'] = DEFAULT_CURRENCY;
//      $order->info['currency_value'] = 1;
//      
//
//      print_r($order);
//      die;
//      $order->products = $products;
//      $order->create($order_totals, 2);
//      print_r($order);
//      
//      require(DIR_WS_CLASSES . 'order_total.php');
//      $order_total_modules = new order_total;
//      
//      $order_totals = $order_total_modules->pre_confirmation_check();
//      
//      $order_totals = $order_total_modules->process();
//      // create the order record
//      $insert_id = $order->create($order_totals, 2);
//      $payment_modules->after_order_create($insert_id);
//      // store the product info to the order
//      $order->create_add_products($insert_id);
//      $_SESSION['order_number_created'] = $insert_id;
//
//      // Tell google witch is the Zencart's internal order Number        
//       $Grequest = new GoogleRequest($googlepayment->merchantid, 
//                                      $googlepayment->merchantkey, 
//                                      MODULE_PAYMENT_GOOGLECHECKOUT_MODE==
//                                        'https://sandbox.google.com/checkout/'
//                                        ?"sandbox":"production",
//                                      DEFAULT_CURRENCY);
//       $Grequest->SetLogFiles(API_CALLBACK_ERROR_LOG, API_CALLBACK_MESSAGE_LOG);
//       $Grequest->SendMerchantOrderNumber(
//                     $data[$root]['google-order-number']['VALUE'] , $orders_id);
//    }

    case "new-order-notification": {
	    $orders_id = process_new_order_notification($Gresponse, $googlepayment, 
        $cart, $_SESSION['customer_id'], $_SESSION['languages_id']); 

// DEBUG!!! TODO
       $_SESSION['cart']->reset(TRUE);
                     
//Add the order details to the table
// This table could be modified to hold the merchant id and key if required
// so that different mids and mkeys can be used for different orders
      $db->Execute("insert into " . $googlepayment->table_order . " values (" . $orders_id . ", ".
          makeSqlString($data[$root]['google-order-number']['VALUE']) . ", " .
          makeSqlFloat($data[$root]['order-total']['VALUE']) . ")");
      
      if(is_array($data[$root]['order-adjustment']['shipping'])) {
      foreach($data[$root]['order-adjustment']['shipping'] as $ship); {
        $shipping =  $ship['shipping-name']['VALUE'];
        $ship_cost = $ship['shipping-cost']['VALUE'];
      }
      }
      $tax_amt = $data[$root]['order-adjustment']['total-tax']['VALUE'];
//      $order_total = $data[$root]['order-total']['VALUE'];
      
      require(DIR_WS_CLASSES . 'order.php');
      $order = new order();
// load the selected shipping module
      require(DIR_WS_CLASSES . 'shipping.php');
      $shipping_modules = new shipping($shipping);

			$coupons = get_arr_result($data[$root]['order-adjustment']['merchant-codes']['coupon-adjustment']);
      $items = get_arr_result($data[$root]['shopping-cart']['items']['item']);

      // Get Coustoms OT
      $ot_customs_total = 0;
      $ot_customs = array();
      foreach($items as $item){
        if($item['merchant-private-item-data']['order_total']['VALUE']) {
          $ot = unserialize(base64_decode(
                    $item['merchant-private-item-data']['order_total']['VALUE']));
          $ot_customs[] = $ot; 
          $ot_value = $ot['value'] * (strrpos($ot['text'], '-')===false?1:-1);
          $ot_customs_total += $currencies->get_value(DEFAULT_CURRENCY) * $ot_value;
        }
      }

// Update values so that order_total modules get the correct values
      $order->info['total'] = $data[$root]['order-total']['VALUE'];
      $order->info['subtotal'] = $data[$root]['order-total']['VALUE'] - 
                                  ($ship_cost + $tax_amt) + 
                                  @$coupons[0]['applied-amount']['VALUE'] -
                                  $ot_customs_total;
      $order->info['coupon_code'] = @$coupons[0]['code']['VALUE'];
      $order->info['shipping_method'] = $shipping;
      $order->info['shipping_cost'] = $ship_cost;
      $order->info['tax_groups']['tax'] = $tax_amt;
      $order->info['currency'] = DEFAULT_CURRENCY;
      $order->info['currency_value'] = 1;

      require(DIR_WS_CLASSES . 'order_total.php');
      $order_total_modules = new order_total();
      // Disable OT sent as items in the GC cart
      foreach($order_total_modules->modules as $ot_code => $order_total) {
        if(!in_array(substr($order_total, 0, strrpos($order_total, '.')),
                                   $googlepayment->ot_ignore)) {
          unset($order_total_modules->modules[$ot_code]);
        }
      }
//    Merge all OT
      $order_totals = $order_total_modules->process();
//    Not necessary, OT already disabled 
//      foreach($order_totals as $ot_code => $order_total){
//        if(!in_array($order_total['code'], $googlepayment->ot_ignore)){
//          unset($order_totals[$ot_code]);
//        }
//      }
      $order_totals = array_merge($order_totals, $ot_customs);

      if(isset($data[$root]['order-adjustment']['merchant-codes']['coupon-adjustment'])) {
        $order_totals[] = array('code' => @$coupons[0]['code']['VALUE'],
                                'title' => "<b>".MODULE_ORDER_TOTAL_COUPON_TITLE." ". @$coupons[0]['code']['VALUE'].":</b>",
                                'text' => @$coupons[0]['applied-amount']['currency']. " -". @$coupons[0]['applied-amount']['VALUE'],
                                'value' => @$coupons[0]['applied-amount']['VALUE'],
                                'sort_order' => 500);
      }

      function OT_cmp($a, $b)
      {
         if ($a['sort_order'] == $b['sort_order'])return 0;
         return ($a['sort_order'] < $b['sort_order']) ? -1 : 1;
      }
      
			usort($order_totals, "OT_cmp");			
//    Add OT to the Order
      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        $sql_data_array = array('orders_id' => makeSqlInteger($orders_id),
                                'title' => $order_totals[$i]['title'],
                                'text' => $order_totals[$i]['text'],
                                'value' => $order_totals[$i]['value'],
                                'class' => $order_totals[$i]['code'],
                                'sort_order' => makeSqlInteger($order_totals[$i]['sort_order']));
       zen_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
      }
      $Gresponse->SendAck();
// Tell google witch is the Zencart's internal order Number        
// disabled, this times out the New Order Notif 
//      $Grequest = new GoogleRequest($googlepayment->merchantid, 
//                                      $googlepayment->merchantkey, 
//                                      MODULE_PAYMENT_GOOGLECHECKOUT_MODE==
//                                        'https://sandbox.google.com/checkout/'
//                                        ?"sandbox":"production",
//                                      DEFAULT_CURRENCY);
//      $Grequest->SetLogFiles(API_CALLBACK_ERROR_LOG, API_CALLBACK_MESSAGE_LOG, L_ALL);
//      $Grequest->SendMerchantOrderNumber(
//                     $data[$root]['google-order-number']['VALUE'] , $orders_id);
      break;
    }
    case "order-state-change-notification": {
      process_order_state_change_notification($Gresponse, $googlepayment);
      break;
    }
    case "charge-amount-notification": {
      process_charge_amount_notification($Gresponse,$googlepayment);
      break;
    }
    case "chargeback-amount-notification": {
      process_chargeback_amount_notification($Gresponse);
      break;
    }
    case "refund-amount-notification": {
      process_refund_amount_notification($Gresponse);
      break;
    }
    case "risk-information-notification": {
      process_risk_information_notification($Gresponse, $googlepayment);
      break;
    }
    default: {
      $Gresponse->SendBadRequestStatus("Invalid or not supported Message");
      break;
    }
  }
  exit(0);

  function process_request_received_response($Gresponse) {
  }
  function process_error_response($Gresponse) {
  }
  function process_diagnosis_response($Gresponse) {
  }
  function process_checkout_redirect($Gresponse) {
  }
  
  function calculate_coupons($Gresponse, &$merchant_result, $price=0) {
  	global $order, $db;
  	list($root, $data) = $Gresponse->GetParsedXML();
		require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_general.php');
		$currencies = new currencies();
	  require_once(DIR_FS_CATALOG . DIR_WS_LANGUAGES . $_SESSION['language'] . '/discount_coupon.php');
    $codes = get_arr_result($data[$root]['calculate']['merchant-code-strings']['merchant-code-string']);
    //print_r($codes);
    $first_coupon = true;
    foreach($codes as $curr_code) {
  		$text_coupon_help = '';
    	
      //Update this data as required to set whether the coupon is valid, the code and the amount
	 		// Check for valid zone...   
	    $sql = "select coupon_id, coupon_amount, coupon_type, coupon_minimum_order, uses_per_coupon, uses_per_user,
              restrict_to_products, restrict_to_categories, coupon_zone_restriction, coupon_code
              from " . TABLE_COUPONS . "
              where coupon_code= '".zen_db_input($curr_code['code'])."'
              and coupon_active='Y'";
//      $sql = $db->bindVars($sql, ':couponIDEntered', , 'string');

      $coupon_result=$db->Execute($sql);
      $foundvalid = true;
      $check_flag = false;
      $check = $db->Execute("select zone_id, zone_country_id from " . 
        TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . 
        $coupon_result->fields['coupon_zone_restriction'] . "' and zone_country_id = '" . 
        $order->delivery['country']['id'] . "' order by zone_id");
			
      if ($coupon_result->fields['coupon_zone_restriction'] > 0) {
        while (!$check->EOF) {
          if ($check->fields['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
          $check->MoveNext();
        }
        $foundvalid = $check_flag;
      }
      
      //  added code here to handle coupon product restrictions
      // look through the items in the cart to see if this coupon is valid for any item in the cart
      $items = get_arr_result($data[$root]['shopping-cart']['items']['item']);
      $products = $order_totals = array();
      foreach($items as $item){
        if(isset($item['merchant-private-item-data']['item']['VALUE'])) {
          $products[] = unserialize(base64_decode(
                           $item['merchant-private-item-data']['item']['VALUE']));
        }
      }
      if ($foundvalid == true) {
        $foundvalid = false;
        for ($i=0; $i<sizeof($products); $i++) {
          if (is_product_valid($products[$i]['id'], $coupon_result->fields['coupon_id'])) {
            $foundvalid = true;
            continue;
          }
        }
      }
      
			$coupon = $db->Execute("select * from " . TABLE_COUPONS . " where coupon_code = '" . 
                zen_db_input($curr_code['code']) . "' and  coupon_type != 'G'");
	    
	    if (!$foundvalid || !$first_coupon || $coupon->RecordCount() < 1) {
	// invalid discount coupon code or more than one entered!
	      $text_coupon_help = $first_coupon?sprintf(TEXT_COUPON_FAILED,$curr_code['code']):GOOGLECHECKOUT_COUPON_ERR_ONE_COUPON;
	      $coupons = new GoogleCoupons("false", $curr_code['code'],0, $text_coupon_help);
      	$merchant_result->AddCoupons($coupons);
// BBG Start - Invalid discount coupon if coupon minimum order is over 0 and the order total doesn't meet the minimum     
     } else if ($coupon->fields['coupon_minimum_order']>0 
            && $order->info['total'] < $coupon->fields['coupon_minimum_order']) {
       $text_coupon_help = GOOGLECHECKOUT_COUPON_ERR_MIN_PURCHASE;
       $coupons = new GoogleCoupons("false", $curr_code['code'],0,$text_coupon_help);
       $merchant_result->AddCoupons($coupons);
// BBG End
	    } else {
	// valid discount coupon code
	      $lookup_coupon_id = $coupon->fields['coupon_id'];
	      $coupon_desc = $db->Execute("select * from " . TABLE_COUPONS_DESCRIPTION . 
                  " where coupon_id = '" . (int)$lookup_coupon_id . "' " .
                  " and language_id = '" . (int)$_SESSION['languages_id'] . "'");
	      $coupon_amount = $coupon->fields['coupon_amount'];
	      switch ($coupon->fields['coupon_type']) {
	        case 'F':
	        $text_coupon_help = GOOGLECHECKOUT_COUPON_DISCOUNT.$curr_code['code'];
	        break;
	        case 'P':
	        $text_coupon_help = GOOGLECHECKOUT_COUPON_DISCOUNT.$curr_code['code'];
	        $coupon_amount = $coupon_amount * $order->info['total'] / 100;
	        break;
	        case 'S':
	        $text_coupon_help = GOOGLECHECKOUT_COUPON_FREESHIP.$curr_code['code'];
	        $coupon_amount = $price;
	        break;
	        default:
	      }
	      $get_result=$db->Execute("select * from " . TABLE_COUPON_RESTRICT . " " .
            "where coupon_id='" . (int)$lookup_coupon_id . "' and category_id !='0'");
	      $cats = '';
	      while (!$get_result->EOF) {
	        if ($get_result->fields['coupon_restrict'] == 'N') {
	          $restrict = TEXT_CAT_ALLOWED;
	        } else {
	          $restrict = TEXT_CAT_DENIED;
	        }
	        $result = $db->Execute("SELECT * FROM " . TABLE_CATEGORIES . " c, " . 
            TABLE_CATEGORIES_DESCRIPTION . " cd WHERE c.categories_id = cd.categories_id " .
            "and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' " .
            "and c.categories_id='" . $get_result->fields['category_id'] . "'");
	        $cats .= '<br />' . $result->fields["categories_name"] . $restrict;
	        $get_result->MoveNext();
	      }
	      if ($cats=='') $cats = TEXT_NO_CAT_RESTRICTIONS;
	      $get_result=$db->Execute("select * from " . TABLE_COUPON_RESTRICT . 
          " where coupon_id='" . (int)$lookup_coupon_id . "' and product_id !='0'");
	
	      while (!$get_result->EOF) {
	        if ($get_result->fields['coupon_restrict'] == 'N') {
	          $restrict = TEXT_PROD_ALLOWED;
	        } else {
	          $restrict = TEXT_PROD_DENIED;
	        }
	        $result = $db->Execute("SELECT * FROM " . TABLE_PRODUCTS . " p, " . 
            TABLE_PRODUCTS_DESCRIPTION . " pd WHERE p.products_id = pd.products_id " .
            "and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' " .
            "and p.products_id = '" . $get_result->fields['product_id'] . "'");
	        $prods .= '<br />' . $result->fields['products_name'] . $restrict;
	        $get_result->MoveNext();
	      }
	      if ($prods=='') $prods = TEXT_NO_PROD_RESTRICTIONS;
        $coupons = new GoogleCoupons("true", $curr_code['code'],
          $currencies->get_value(DEFAULT_CURRENCY) * $coupon_amount, $text_coupon_help);
        $merchant_result->AddCoupons($coupons);
        $first_coupon = false;
	    }
	    
    }
  }
  
  function process_merchant_calculation_callback_single($Gresponse) {
  	global $googlepayment, $order, $db, $total_weight, $total_count;
		list($root, $data) = $Gresponse->GetParsedXML();
    $currencies = new currencies();
    
		$cart = $_SESSION['cart'];
		$methods_hash = $googlepayment->getMethods();
		require(DIR_WS_CLASSES . 'order.php');
		$order = new order;

  // Register a random ID in the session to check throughout the checkout procedure
  // against alterations in the shopping cart contents.
//  if (!tep_session_is_registered('cartID')) {
  //  tep_session_register('cartID');
 // }
		$cartID = $cart->cartID;
  	
		$total_weight = $cart->show_weight();
		$total_count = $cart->count_contents();
  		
        // Create the results and send it
		$merchant_calc = new GoogleMerchantCalculations(DEFAULT_CURRENCY);
    
  // Loop through the list of address ids from the callback.
		$addresses = get_arr_result($data[$root]['calculate']['addresses']['anonymous-address']);
	// Get all the enabled shipping methods.
		require(DIR_WS_CLASSES . 'shipping.php');
		
  // Required for some shipping methods (ie. USPS).
		require_once('includes/classes/http_client.php');
		foreach($addresses as $curr_address) {
    // Set up the order address.
        $curr_id = $curr_address['id'];
        $country = $curr_address['country-code']['VALUE'];
        $city = $curr_address['city']['VALUE'];
        $region = $curr_address['region']['VALUE'];
        $postal_code = $curr_address['postal-code']['VALUE'];
			$countr_query = $db->Execute("select * 
		                               from " . TABLE_COUNTRIES . " 
		                               where countries_iso_code_2 = '" . makeSqlString($country) ."'");
		
			$row = $countr_query->fields;
		 	$order->delivery['country'] = array('id' => $row['countries_id'], 
																					'title' => $row['countries_name'], 
																					'iso_code_2' => $country, 
																					'iso_code_3' => $row['countries_iso_code_3']);
	
			$order->delivery['country_id'] = $row['countries_id'];
			$order->delivery['format_id'] = $row['address_format_id'];
			
			$zone_query = $db->Execute("select * 
		                               from " . TABLE_ZONES . "
		                               where zone_code = '" . makeSqlString($region) ."'");
	
			$row = $zone_query->fields;
			$order->delivery['zone_id'] = $row['zone_id'];
			$order->delivery['state'] = $row['zone_name'];
			$order->delivery['city'] = $city;
			$order->delivery['postcode'] = $postal_code;
  		$shipping_modules = new shipping();
	
	    // Loop through each shipping method if merchant-calculated shipping
	    // support is to be provided
	    //print_r($data[$root]['calculate']['shipping']['method']);
			if(isset($data[$root]['calculate']['shipping'])) {
		        $shipping = get_arr_result($data[$root]['calculate']['shipping']['method']);

						if(MODULE_PAYMENT_GOOGLECHECKOUT_MULTISOCKET == 'True') {
	// Single
							// i get all the enabled shipping methods  
		         	$name = $shipping[0]['name'];
//            Compute the price for this shipping method and address id
			        list($a, $method_name) = explode(': ',$name);
							if((($order->delivery['country']['id'] == SHIPPING_ORIGIN_COUNTRY) 
                  && ($methods_hash[$method_name][1] == 'domestic_types'))
									||
								(($order->delivery['country']['id'] != SHIPPING_ORIGIN_COUNTRY) 
                  && ($methods_hash[$method_name][1] == 'international_types'))){
	//								reset the shipping class to set the new address
										if (class_exists($methods_hash[$method_name][2])) {			        	
					        		$GLOBALS[$methods_hash[$method_name][2]] = new $methods_hash[$method_name][2];
										}
							}
			        $quotes =  $shipping_modules->quote('', $methods_hash[$method_name][2]);
						}
						else {
	// Standard
			        foreach($shipping as $curr_ship) {
			         	$name = $curr_ship['name'];
	//            Compute the price for this shipping method and address id
				        list($a, $method_name) = explode(': ',$name);
								if((($order->delivery['country']['id'] == SHIPPING_ORIGIN_COUNTRY) 
                    && ($methods_hash[$method_name][1] == 'domestic_types'))
										||
									(($order->delivery['country']['id'] != SHIPPING_ORIGIN_COUNTRY) 
                    && ($methods_hash[$method_name][1] == 'international_types'))){
			//								reset the shipping class to set the new address
											if (class_exists($methods_hash[$method_name][2])) {			        	
						        		$GLOBALS[$methods_hash[$method_name][2]] = new $methods_hash[$method_name][2];
											}
								}
			        }
							$quotes =  $shipping_modules->quote();
						}
						reset($shipping);
		        foreach($shipping as $curr_ship) {
		         	$name = $curr_ship['name'];
//            Compute the price for this shipping method and address id
			        list($a, $method_name) = explode(': ',$name);
							unset($quote_povider);
							unset($quote_method);
							if((($order->delivery['country']['id'] == SHIPPING_ORIGIN_COUNTRY) 
                  && ($methods_hash[$method_name][1] == 'domestic_types'))
									||
								(($order->delivery['country']['id'] != SHIPPING_ORIGIN_COUNTRY) 
                  && ($methods_hash[$method_name][1] == 'international_types'))) {
								foreach($quotes as $key_provider => $shipping_provider) {
									// privider name (class)
									if($shipping_provider['id'] == $methods_hash[$method_name][2]) {
										// method name			
										$quote_povider = $key_provider;
										if(is_array($shipping_provider['methods']))
										foreach($shipping_provider['methods'] as $key_method => $shipping_method) {
											if($shipping_method['id'] == $methods_hash[$method_name][0]){
												$quote_method = $key_method;
												break;
											}										
										}
										break;
									}
								}
							}
	            //if there is a problem with the method, i mark it as non-shippable
	            if( isset($quotes[$quote_povider]['error']) ||
                  !isset($quotes[$quote_povider]['methods'][$quote_method]['cost'])) {
	            	$price = "9999.09";
	            	$shippable = "false";
	            }
	            else {
	            	$price = $quotes[$quote_povider]['methods'][$quote_method]['cost'];
	            	$shippable = "true";
	            }
	            // fix for item shipping function bug if called more than once in a session. 
	            $price = ($price>=0?$price:0);
	            $merchant_result = new GoogleResult($curr_id);
	            $merchant_result->SetShippingDetails($name, $currencies->get_value(DEFAULT_CURRENCY) * $price, $shippable);
	
	            if($data[$root]['calculate']['tax']['VALUE'] == "true") {
	              //Compute tax for this address id and shipping type
	              $amount = 15; // Modify this to the actual tax value
	              $merchant_result->SetTaxDetails($currencies->get_value(DEFAULT_CURRENCY) * $amount);
	            }
////							 start cupons and gift processing (working)
//								// only one coupon per order is valid!
//                $_POST['dc_redeem_code'] = 'ROPU';
//
////                require(DIR_WS_CLASSES . 'order.php');
////                $order = new order;
//                require_once(DIR_WS_CLASSES . 'order_total.php');
//                $order_total_modules = new order_total;
////                $order_total_modules->collect_posts();
////                $order_total_modules->pre_confirmation_check();
//                
////                print_r($order_total_modules);
//                   $order_totals = $order_total_modules->process();
////                print_r($order_totals);
//                                

								calculate_coupons($Gresponse, $merchant_result, $price);
								// end cupons		            
		            $merchant_calc->AddResult($merchant_result);
		        }
	        }
	        else {
		          $merchant_result = new GoogleResult($curr_id);
		          if($data[$root]['calculate']['tax']['VALUE'] == "true") {
		            //Compute tax for this address id and shipping type
		            $amount = 15; // Modify this to the actual tax value
		            $merchant_result->SetTaxDetails($currencies->get_value(DEFAULT_CURRENCY) * $amount);
		          }
		          calculate_coupons($Gresponse, $merchant_result);
		          $merchant_calc->AddResult($merchant_result);
	        }
      }
      $Gresponse->ProcessMerchantCalculations($merchant_calc);
  }
  
/**
 * New order notifications come through when someone checks out through your store:
 * 1. Get cart contents
 * 2. Add a row in orders table
 * 3. Add a row for each product in orders_products table
 * 4. Add rows if required to orders_products_attributes table
 * 5. Add a row to orders_status_history and orders_total
 * 6. Check stock configuration and update inventory if required
 */
  function process_new_order_notification($Gresponse, $googlepayment, $cart, $customer_id, $languages_id) {
    global $db;
    $currencies = new currencies();
    list($root, $data) = $Gresponse->GetParsedXML();
  
    if(isset($customer_id) && $customer_id != '') {
      $cust_id = $customer_id;
    $oper = 'update';
      $params = ' customers_id = '.$cust_id;
    } else {
// Else check if buyer is a new user from Google Checkout
      $customer_info = $db->Execute("select customers_id from " .
                            $googlepayment->table_name  . " where buyer_id = " .
                            makeSqlString($data[$root]['buyer-id']['VALUE'])  );
      if($customer_info->RecordCount() == 0)  {
        // Add if new user
        $full_name = makeSqlString($data[$root]['buyer-billing-address']['contact-name']['VALUE']); //Returns full name.
        $last_name = strrev(strtok(strrev($full_name), " ")); //Returns surname.
        $first_name = substr($full_name, 0, ((strlen($last_name)+1)*-1)); //Returns full name minus last name.
        $sql_data_array = array('customers_gender' => '',
                        'customers_firstname' => $first_name,
                          'customers_lastname' => $last_name,
                          'customers_dob' => 'now()',
                          'customers_email_address' => $data[$root]['buyer-billing-address']['email']['VALUE'],
                          'customers_default_address_id' => 0,
                          // I take email from 
                          
                          'customers_telephone' => $data[$root]['buyer-billing-address']['phone']['VALUE'],
                          'customers_fax' => $data[$root]['buyer-billing-address']['fax']['VALUE'],
                          'customers_password' => zen_encrypt_password(makeSqlString($data[$root]['buyer-id']['VALUE'])),
                          'customers_newsletter' => '');
        zen_db_perform(TABLE_CUSTOMERS, $sql_data_array);

        $cust_id = $db->insert_ID();
        $sql_data_array = array('customers_info_id' => $cust_id,
                            'customers_info_date_of_last_logon' => '',
                            'customers_info_number_of_logons' => '',
                            'customers_info_date_account_created' => 'now()',
                            'customers_info_date_account_last_modified' => 'now()',
                            'global_product_notifications' => '');
        zen_db_perform(TABLE_CUSTOMERS_INFO, $sql_data_array);
        $db->Execute("insert into ". $googlepayment->table_name . " " .
            " values ( " . $cust_id. ", ". $data[$root]['buyer-id']['VALUE']. ")");
        $oper="insert";
        $params="";
      } else {
        $cust_id = $customer_info->fields['customers_id'];
        $oper="update";
        $params = ' customers_id = '.(int)$cust_id;
      }
    }
// Update address book with the latest entry
// This has the disadvantage of overwriting an existing address book entry of the user
//BOF - Fix address book bug - added by colosports
	$buyer_state = $data[$root]['buyer-shipping-address']['region']['VALUE'];
    $zone_answer = $db->Execute("select zone_id, zone_country_id from ". TABLE_ZONES . " where zone_code = '$buyer_state'");

    $full_name = makeSqlString($data[$root]['buyer-shipping-address']['contact-name']['VALUE']); //Returns full name.
    $last_name = strrev(strtok(strrev($full_name), " ")); //Returns surname.
    $first_name = substr($full_name, 0, ((strlen($last_name)+1)*-1)); //Returns full name minus last name.

   $sql_data_array = array('customers_id' => $cust_id,
                          'entry_gender' => '',
                          'entry_company' => $data[$root]['buyer-shipping-address']['company-name']['VALUE'],
                          'entry_firstname' => $first_name,
                          'entry_lastname' => $last_name,
                          'entry_street_address' => $data[$root]['buyer-shipping-address']['address1']['VALUE'],
      	                  'entry_suburb' => $data[$root]['buyer-shipping-address']['address2']['VALUE'],
                          'entry_postcode' => $data[$root]['buyer-shipping-address']['postal-code']['VALUE'],
                          'entry_city' => $data[$root]['buyer-shipping-address']['city']['VALUE'],
                          'entry_state' => $data[$root]['buyer-shipping-address']['region']['VALUE'],
                          'entry_country_id' => $zone_answer->fields['zone_country_id'],
                          'entry_zone_id' => $zone_answer->fields['zone_id']);

// Check database to see if the address exist.
    $address_book = $db->Execute("select address_book_id from ". TABLE_ADDRESS_BOOK . "
    				where 	customers_id = '$cust_id'
							and entry_street_address = '".makeSqlString($data[$root]['buyer-shipping-address']['address1']['VALUE'])."'
						    and entry_suburb = '".makeSqlString($data[$root]['buyer-shipping-address']['address2']['VALUE'])."'
						    and entry_postcode = '".makeSqlString($data[$root]['buyer-shipping-address']['postal-code']['VALUE'])."'
						    and entry_city = '".makeSqlString($data[$root]['buyer-shipping-address']['city']['VALUE'])."'
    					");
//If the address does not exist, the address will be store in the address book.
	if ($address_book->RecordCount() == 0) {
		zen_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
	}
//EOF - Fix address book bug - added by colosports

  if($oper == "insert") {
    $address_book_id = $db->insert_ID();
    $db->Execute('update '. TABLE_CUSTOMERS . ' set customers_default_address_id= '. $address_book_id . ' where customers_id = ' . $cust_id  );
  }
	$coupons = get_arr_result($data[$root]['order-adjustment']['merchant-codes']['coupon-adjustment']);
  
  
  $methods_hash = $googlepayment->getMethods();
  if(isset($data[$root]['order-adjustment']['shipping']['merchant-calculated-shipping-adjustment']['shipping-name']['VALUE'])) {
    $name = $data[$root]['order-adjustment']['shipping']['merchant-calculated-shipping-adjustment']['shipping-name']['VALUE'];
  }
  else {
    $name = $data[$root]['order-adjustment']['shipping']['flat-rate-shipping-adjustment']['shipping-name']['VALUE'];
  }
  

  list($a, $method_name) = explode(': ',$name);
  
   $sql_data_array = array('customers_id' => $cust_id,
                           'customers_name' => $data[$root]['buyer-shipping-address']['contact-name']['VALUE'],
                           'customers_company' => $data[$root]['buyer-shipping-address']['company-name']['VALUE'],
                           'customers_street_address' => $data[$root]['buyer-shipping-address']['address1']['VALUE'],
                           'customers_suburb' => $data[$root]['buyer-shipping-address']['address2']['VALUE'],
                           'customers_city' => $data[$root]['buyer-shipping-address']['city']['VALUE'],
                           'customers_postcode' => $data[$root]['buyer-shipping-address']['postal-code']['VALUE'],
                           'customers_state' => $data[$root]['buyer-shipping-address']['region']['VALUE'],
                           'customers_country' => $data[$root]['buyer-shipping-address']['country-code']['VALUE'],
                           'customers_telephone' => $data[$root]['buyer-billing-address']['phone']['VALUE'],
                           'customers_email_address' => $data[$root]['buyer-shipping-address']['email']['VALUE'],
                           'customers_address_format_id' => 2,
                           'delivery_name' => $data[$root]['buyer-shipping-address']['contact-name']['VALUE'],
                           'delivery_company' => $data[$root]['buyer-shipping-address']['company-name']['VALUE'],
                           'delivery_street_address' => $data[$root]['buyer-shipping-address']['address1']['VALUE'],
                           'delivery_suburb' => $data[$root]['buyer-shipping-address']['address2']['VALUE'],
                           'delivery_city' => $data[$root]['buyer-shipping-address']['city']['VALUE'],
                           'delivery_postcode' => $data[$root]['buyer-shipping-address']['postal-code']['VALUE'],
                           'delivery_state' => $data[$root]['buyer-shipping-address']['region']['VALUE'],
                           'delivery_country' => $data[$root]['buyer-shipping-address']['country-code']['VALUE'],
                           'delivery_address_format_id' => 2,
                           'billing_name' => $data[$root]['buyer-billing-address']['contact-name']['VALUE'],
                           'billing_company' => $data[$root]['buyer-billing-address']['company-name']['VALUE'],
                           'billing_street_address' => $data[$root]['buyer-billing-address']['address1']['VALUE'],
                           'billing_suburb' => $data[$root]['buyer-billing-address']['address2']['VALUE'],
                           'billing_city' => $data[$root]['buyer-billing-address']['city']['VALUE'],
                           'billing_postcode' => $data[$root]['buyer-billing-address']['postal-code']['VALUE'],
                           'billing_state' => $data[$root]['buyer-billing-address']['region']['VALUE'],
                           'billing_country' => $data[$root]['buyer-billing-address']['country-code']['VALUE'],
                           'billing_address_format_id' => 2,
                           'payment_method' => 'Google Checkout',
                           'payment_module_code' => 'googlecheckout',
                           'shipping_method' => $methods_hash[$method_name][0],
                           'shipping_module_code' => $methods_hash[$method_name][2],
                           // ver esto!
                           'coupon_code' => @$coupons[0]['code']['VALUE'],
                           'ip_address' => $data[$root]['shopping-cart']['merchant-private-data']['ip-address']['VALUE'],
                           'cc_type' => '',
                           'cc_owner' => '',
                           'cc_number' => '',
                           'cc_expires' => '',
                           'date_purchased' => 'now()',
                           'orders_status' => 1,
                           'currency' => DEFAULT_CURRENCY,
                           'currency_value' => 1);
    zen_db_perform(TABLE_ORDERS, $sql_data_array);
//Insert entries into orders_products
    
    
    $items = get_arr_result($data[$root]['shopping-cart']['items']['item']);
	  $products = $order_totals = array();
	  foreach($items as $item){
	  	if(isset($item['merchant-private-item-data']['item']['VALUE'])) {
        $products[] = unserialize(base64_decode(
                         $item['merchant-private-item-data']['item']['VALUE']));
      }
      else if($item['merchant-private-item-data']['order_total']['VALUE']) {
        $order_totals[] = unserialize(base64_decode(
                  $item['merchant-private-item-data']['order_total']['VALUE']));
      }
	  }
	  
		$orders_id = $db->insert_ID();
    for($i=0; $i<sizeof($products); $i++) {
      $tax_answer = $db->Execute("select tax_rate from ". TABLE_TAX_RATES . 
      " as tr, ". TABLE_ZONES . " as z, ". TABLE_ZONES_TO_GEO_ZONES . " as ztgz " .
      "where z.zone_code= '". $data[$root]['buyer-shipping-address']['region']['VALUE'] . 
      "' and z.zone_id = ztgz.zone_id and tr.tax_zone_id=ztgz.geo_zone_id " .
      "and tax_class_id= ". $products[$i]['tax_class_id']);


      $products_tax = $tax_answer->fields['tax_rate'];
      $sql_data_array = array('orders_id' => $orders_id,
                          'products_id' => makeSqlInteger($products[$i]['id']),
                          'products_model' => $products[$i]['model'],
                          'products_name' => $products[$i]['name'],
                          'products_price' => makeSqlFloat(
                            $currencies->get_value(DEFAULT_CURRENCY) * $products[$i]['price']),
                          'final_price' => makeSqlFloat(
                            $currencies->get_value(DEFAULT_CURRENCY) * $products[$i]['final_price']),
                          'products_tax' => makeSqlFloat($products_tax),
                          'products_quantity' => makeSqlInteger($products[$i]['quantity'] ));
                          
                          //print_r($sql_data_array);
      zen_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
//Insert entries into orders_products_attributes
      $orders_products_id = $db->insert_ID();
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes']))  {
        while (list($option, $value) = each($products[$i]['attributes'])) {
//          $attributes = $db->Execute("select popt.products_options_name, " .
//                           "poval.products_options_values_name," .
//                           " pa.options_values_price, pa.price_prefix ".
//                           " from " . TABLE_PRODUCTS_OPTIONS .
//                           " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES 
//                           . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
//                          where pa.products_id = '" . $products[$i]['id'] . "'
//                            and pa.options_id = '" . makeSqlString($option) . "'
//                            and pa.options_id = popt.products_options_id
//                            and pa.options_values_id = '" . makeSqlString($value) . "'
//                            and pa.options_values_id = poval.products_options_values_id
//                            and popt.language_id = '" . $languages_id . "'
//                            and poval.language_id = '" . $languages_id . "'");

          $sql_data_array = array('orders_id' => $orders_id,
                          'orders_products_id' => $orders_products_id,
                          'products_options' => $products[$i][$option]['products_options_name'],
                          'products_options_values' => $products[$i][$option]['products_options_values_name'],
                          'options_values_price' => makeSqlFloat(
                              $currencies->get_value(DEFAULT_CURRENCY) 
                              * $products[$i][$option]['options_values_price']),
                          'price_prefix' => $products[$i][$option]['price_prefix']);
          zen_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);	//This should be set to zen_db_perform().
        }
      }
 
//Update Inventory
// colosport
      if (STOCK_LIMITED == 'true') {
        if (DOWNLOAD_ENABLED == 'true') {
          $stock_query_raw = "select p.products_quantity, pad.products_attributes_filename, 
                              p.product_is_always_free_shipping
                              from " . TABLE_PRODUCTS . " p
                              left join " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                               on p.products_id=pa.products_id
                              left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                               on pa.products_attributes_id=pad.products_attributes_id
                              WHERE p.products_id = '" . $products[$i]['id'] . "'";

          // Will work with only one option for downloadable products
          // otherwise, we have to build the query dynamically with a loop
          $products_attributes = $products[$i]['attributes'];
          if (is_array($products_attributes)) {
            $stock_query_raw .= " AND pa.options_id = '" . 
               $products_attributes[0]['option_id'] . "' AND " .
               "pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
          }
          $stock_values = $db->Execute($stock_query_raw);
        } else {
          $stock_values = $db->Execute("select * from " . TABLE_PRODUCTS . 
                          " where products_id = '" . $products[$i]['id'] . "'");
        }

        if ($stock_values->RecordCount() > 0) {
          // do not decrement quantities if products_attributes_filename exists
          if ((DOWNLOAD_ENABLED != 'true') 
              || $stock_values->fields['product_is_always_free_shipping'] == 2
              || (!$stock_values->fields['products_attributes_filename']) ) {
            $stock_left = $stock_values->fields['products_quantity'] - $products[$i]['quantity'];
//            $products[$i]['stock_reduce'] = $products[$i]['qty'];
          } else {
            $stock_left = $stock_values->fields['products_quantity'];
          }

 //  $products[$i]['stock_value'] = $stock_values->fields['products_quantity'];

          $db->Execute("update " . TABLE_PRODUCTS . " set products_quantity = " .
              "'" . $stock_left . "' where products_id = '" . $products[$i]['id'] . "'");
          //        if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {}
          if ($stock_left <= 0) {
            // only set status to off when not displaying sold out
            if (SHOW_PRODUCTS_SOLD_OUT == '0') {
              $db->Execute("update " . TABLE_PRODUCTS . " set products_status " .
                  "= 0 where products_id = '" . $products[$i]['id'] . "'");
            }
          }
        }
      }

      // Update products_ordered (for bestsellers list)
      //    $db->Execute("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . zen_get_prid($order->products[$i]['id']) . "'");
      $db->Execute("update " . TABLE_PRODUCTS . " set products_ordered = " .
          "products_ordered + " . sprintf('%f', $products[$i]['qty']) . " " .
          " where products_id = '" . $products[$i]['id'] . "'");    
    }
//Insert entry into orders_status_history
    $sql_data_array = array('orders_id' => $orders_id,
                           'orders_status_id' => 1,
                           'date_added' => 'now()',
                           'customer_notified' => 1,
                           'comments' => GOOGLECHECKOUT_STATE_NEW_ORDER_NUM . 
                            $data[$root]['google-order-number']['VALUE']. "\n".
                           GOOGLECHECKOUT_STATE_NEW_ORDER_MC_USED. 
                            ((@$data[$root]['order-adjustment']['merchant-calculation-successful']['VALUE'] == 'true')?'True':'False') . 
                           ((isset($customer_id) && $customer_id != '')?'':
                              ("\n" .GOOGLECHECKOUT_STATE_NEW_ORDER_BUYER_USER . 
                              $data[$root]['buyer-billing-address']['email']['VALUE'] . "\n" .
                           GOOGLECHECKOUT_STATE_NEW_ORDER_BUYER_PASS .  $data[$root]['buyer-id']['VALUE']))
                           );  //Add Order number to Comments box. For customer's reference.
    zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
   return $orders_id;
  }
  
function process_order_state_change_notification($Gresponse, $googlepayment) {
		global $db;		
    list($root, $data) = $Gresponse->GetParsedXML();
    $new_financial_state = $data[$root]['new-financial-order-state']['VALUE'];
    $new_fulfillment_order = $data[$root]['new-fulfillment-order-state']['VALUE'];

    $previous_financial_state = $data[$root]['previous-financial-order-state']['VALUE'];
    $previous_fulfillment_order = $data[$root]['previous-fulfillment-order-state']['VALUE'];

    $google_order_number = $data[$root]['google-order-number']['VALUE'];
		$google_order = $db->Execute("SELECT orders_id from " .
            "" . $googlepayment->table_order . " where google_order_number = " .
            "'". makeSqlString($google_order_number) ."'");

		$update = false;
		if($previous_financial_state != $new_financial_state)
    switch($new_financial_state) {
      case 'REVIEWING': {
        break;
      }
      case 'CHARGEABLE': {
				$update = true;
				$orders_status_id = 1;
				$comments = GOOGLECHECKOUT_STATE_STRING_TIME . $data[$root]['timestamp']['VALUE']. "\n".
          GOOGLECHECKOUT_STATE_STRING_NEW_STATE. $new_financial_state."\n".
          GOOGLECHECKOUT_STATE_STRING_ORDER_READY_CHARGE;
				$customer_notified = 0;
        break;
      }
      case 'CHARGING': {
        break;
      }
      case 'CHARGED': {
				$update = true;
				$orders_status_id = 2;
				$comments = GOOGLECHECKOUT_STATE_STRING_TIME . $data[$root]['timestamp']['VALUE']. "\n".
                    GOOGLECHECKOUT_STATE_STRING_NEW_STATE. $new_financial_state ;
				$customer_notified = 0;
        break;
      }

      case 'PAYMENT-DECLINED': {
				$update = true;
				$orders_status_id = 1;
				$comments = GOOGLECHECKOUT_STATE_STRING_TIME . $data[$root]['timestamp']['VALUE']. "\n".
                   GOOGLECHECKOUT_STATE_STRING_NEW_STATE. $new_financial_state .
                   GOOGLECHECKOUT_STATE_STRING_PAYMENT_DECLINED; 
				$customer_notified = 1;
        break;
      }
      case 'CANCELLED': {
				$update = true;
				$orders_status_id = 1;
				$customer_notified = 1;
				$comments = GOOGLECHECKOUT_STATE_STRING_TIME . $data[$root]['timestamp']['VALUE']. "\n".
                    GOOGLECHECKOUT_STATE_STRING_NEW_STATE. $new_financial_state ."\n".
                    GOOGLECHECKOUT_STATE_STRING_ORDER_CANCELED."\n".
                    GOOGLECHECKOUT_STATE_STRING_ORDER_CANCELED_REASON. $data[$root]['reason']['VALUE']; 
        break;
      }
      case 'CANCELLED_BY_GOOGLE': {
				$update = true;
				$orders_status_id = 1;
				$comments = GOOGLECHECKOUT_STATE_STRING_TIME . $data[$root]['timestamp']['VALUE']. "\n".
                    GOOGLECHECKOUT_STATE_STRING_NEW_STATE. $new_financial_state ."\n".
                    GOOGLECHECKOUT_STATE_STRING_ORDER_CANCELED_BY_GOOG."\n".
                    GOOGLECHECKOUT_STATE_STRING_ORDER_CANCELED_REASON. $data[$root]['reason']['VALUE']; 
				$customer_notified = 1;
        break;
      }
      default:
        break;
    }
    
    if($update) {
	    $sql_data_array = array('orders_id' => $google_order->fields['orders_id'],
	                           'orders_status_id' => $orders_status_id,
	                           'date_added' => 'now()',
	                           'customer_notified' => $customer_notified,
	                           'comments' => $comments);
		  zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		  $db->Execute("UPDATE " . TABLE_ORDERS . " SET orders_status = " .
          "'".$orders_status_id."' WHERE orders_id = " .
          "'".makeSqlInteger($google_order->fields['orders_id'])."'");
    }
    
 		$update = false;
 		if($previous_fulfillment_order != $new_fulfillment_order)
    switch($new_fulfillment_order) {
      case 'NEW': {
        break;
      }
      case 'PROCESSING': {
        break;
      }
      case 'DELIVERED': {
      	$update = true;
				$orders_status_id = 3;
				$comments = GOOGLECHECKOUT_STATE_STRING_TIME . $data[$root]['timestamp']['VALUE']. "\n".
                    GOOGLECHECKOUT_STATE_STRING_NEW_STATE. $new_fulfillment_order ."\n".
                    GOOGLECHECKOUT_STATE_STRING_ORDER_DELIVERED."\n"; 
				$customer_notified = 1;
        break;
      }
      case 'WILL_NOT_DELIVER': {
        break;
      }
      default:
         break;
    }

    if($update) {
	    $sql_data_array = array('orders_id' => $google_order->fields['orders_id'],
	                           'orders_status_id' => $orders_status_id,
	                           'date_added' => 'now()',
	                           'customer_notified' => $customer_notified,
	                           'comments' => $comments);
		  zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
		  $db->Execute("UPDATE " . TABLE_ORDERS . " SET orders_status = " .
          "'".$orders_status_id."' WHERE orders_id = " .
          "'".makeSqlInteger($google_order->fields['orders_id'])."'");
    }

    $Gresponse->SendAck();	  
  }  
  function process_charge_amount_notification($Gresponse, $googlepayment) {
  	global $db;		
    list($root, $data) = $Gresponse->GetParsedXML();
    $google_order_number = $data[$root]['google-order-number']['VALUE'];
		$google_order = $db->Execute("SELECT orders_id from " .
        "" . $googlepayment->table_order . " where " .
        " google_order_number = '". makeSqlString($google_order_number) ."'");
		
//   fwrite($message_log,sprintf("\n%s\n", $google_order->fields['orders_id']));
  	 
  	
    $sql_data_array = array(
                      'orders_id' => $google_order->fields['orders_id'],
                      'orders_status_id' => 2,
                      'date_added' => 'now()',
                      'customer_notified' => 0,
                      'comments' => GOOGLECHECKOUT_STATE_STRING_LATEST_CHARGE .
                      $data[$root]['latest-charge-amount']['currency'].
                      ' ' .$data[$root]['latest-charge-amount']['VALUE']."\n". 
                      GOOGLECHECKOUT_STATE_STRING_TOTAL_CHARGE .
                      $data[$root]['latest-charge-amount']['currency'].' ' .
                      $data[$root]['total-charge-amount']['VALUE']);
  	zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
  	$db->Execute("UPDATE " . TABLE_ORDERS . " SET orders_status = '". 2 ."' " .
     "WHERE orders_id = '".makeSqlInteger($google_order->fields['orders_id'])."'");
    $Gresponse->SendAck();
  }
  function process_chargeback_amount_notification($Gresponse) {
    $Gresponse->SendAck(); 
  }
  function process_refund_amount_notification($Gresponse) {
    $Gresponse->SendAck(); 
  }
  function process_risk_information_notification($Gresponse, $googlepayment) {
  	global $db;		
    list($root, $data) = $Gresponse->GetParsedXML();
    $google_order_number = $data[$root]['google-order-number']['VALUE'];
		$google_order = $db->Execute("SELECT orders_id from " .
        "" . $googlepayment->table_order . " where google_order_number = " .
        "'". makeSqlString($google_order_number) ."'");
		
//   fwrite($message_log,sprintf("\n%s\n", $google_order->fields['orders_id']));
  	 
  	
    $sql_data_array = array('orders_id' => $google_order->fields['orders_id'],
                           'orders_status_id' => 1,
                           'date_added' => 'now()',
                           'customer_notified' => 0,
                           'comments' => GOOGLECHECKOUT_STATE_STRING_RISK_INFO ."\n" .
				GOOGLECHECKOUT_STATE_STRING_RISK_ELEGIBLE.
        $data[$root]['risk-information']['eligible-for-protection']['VALUE']."\n" .
				GOOGLECHECKOUT_STATE_STRING_RISK_AVS.
        $data[$root]['risk-information']['avs-response']['VALUE']."\n" .
				GOOGLECHECKOUT_STATE_STRING_RISK_CVN.
        $data[$root]['risk-information']['cvn-response']['VALUE']."\n" .
				GOOGLECHECKOUT_STATE_STRING_RISK_CC_NUM.
        $data[$root]['risk-information']['partial-cc-number']['VALUE']."\n" .
		//MaxMind Start
				GOOGLECHECKOUT_STATE_STRING_RISK_IP_ADDRESS.
        $data[$root]['risk-information']['ip-address']['VALUE']."\n" .
		//MaxMind End
				GOOGLECHECKOUT_STATE_STRING_RISK_ACC_AGE.
        $data[$root]['risk-information']['buyer-account-age']['VALUE']."\n" 
                           								);  	
  	zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
  	$db->Execute("UPDATE " . TABLE_ORDERS . " SET orders_status = '". 1 ."' " .
        "WHERE orders_id = '".makeSqlInteger($google_order->fields['orders_id'])."'");
	//MaxMind Start
	$db->Execute("UPDATE " . TABLE_ORDERS . " SET ip_address = '". $data[$root]['risk-information']['ip-address']['VALUE'] ."' " .
		"WHERE orders_id = '".makeSqlInteger($google_order->fields['orders_id'])."'");
	//MaxMind End
	$Gresponse->SendAck();
  }

  //Functions to prevent SQL injection attacks
  function makeSqlString($str) {
		return zen_db_input($str);    
//    return addcslashes(stripcslashes($str), "'\"\\\0..\37!@\@\177..\377");
  }

  function makeSqlInteger($val) {
    return ((settype($val, 'integer'))?($val):0);
  }

  function makeSqlFloat($val) {
    return ((settype($val, 'float'))?($val):0);
  }
    /* In case the XML API contains multiple open tags
     with the same value, then invoke this function and
     perform a foreach on the resultant array.
     This takes care of cases when there is only one unique tag
     or multiple tags.
     Examples of this are "anonymous-address", "merchant-code-string"
     from the merchant-calculations-callback API
  */
  function get_arr_result($child_node) {
    $result = array();
    if(isset($child_node)) {
      if(is_associative_array($child_node)) {
        $result[] = $child_node;
      }
      else {
        foreach($child_node as $curr_node){
          $result[] = $curr_node;
        }
      }
    }
    return $result;
  }

  /* Returns true if a given variable represents an associative array */
  function is_associative_array( $var ) {
    return is_array( $var ) && !is_numeric( implode( '', array_keys( $var ) ) );
  } 
  // ** END GOOGLE CHECKOUT **
?>