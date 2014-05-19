<?php
//
// +----------------------------------------------------------------------+
// |MaxMind CCFD Module for Zen-Cart Open Source E-commerce               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license.       |
// +----------------------------------------------------------------------+
// $Id: cc.php 1.3 2007-08-15 06:03:24Z ses707 $
//
/**
 * Manual Credit Card payment module
 * This module is used for MANUAL processing of credit card data collected from customers.
 * It should ONLY be used if no other gateway is suitable, AND you must have SSL active on your server for your own protection.
 */
class cc extends base {
  /**
   * $code determines the internal 'code' name used to designate "this" payment module
   *
   * @var string
   */
  var $code;
  /**
   * $title is the displayed name for this payment method
   *
   * @var string
   */
  var $title;
  /**
   * $description is a soft name for this payment method
   *
   * @var string
   */
  var $description;
  /**
   * $enabled determines whether this module shows or not... in catalog.
   *
   * @var boolean
   */
  var $enabled;
  /**
   * @return cc
   */
  function cc() {
    global $order;

    $this->code = 'cc';
    if (IS_ADMIN_FLAG === true) {
      // Payment module title in Admin
      $this->title = MODULE_PAYMENT_CC_TEXT_ADMIN_TITLE;
    } else {
      $this->title = MODULE_PAYMENT_CC_TEXT_CATALOG_TITLE;
    }
    $this->description = MODULE_PAYMENT_CC_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_PAYMENT_CC_SORT_ORDER;
    $this->enabled = ((MODULE_PAYMENT_CC_STATUS == 'True') ? true : false);

    if ((int)MODULE_PAYMENT_CC_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_CC_ORDER_STATUS_ID;
    }

    if (is_object($order)) $this->update_status();
  }
  /**
   * calculate zone matches and flag settings to determine whether this module should display to customers or not
   *
   */
  function update_status() {
    global $order, $db;

    if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_CC_ZONE > 0) ) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_CC_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
      while (!$check->EOF) {
        if ($check->fields['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
        $check->MoveNext();
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
  }
  /**
   * JS validation which does error-checking of data-entry if this module is selected for use
   * (Number, Owner, and CVV Lengths)
   *
   * @return string
   */
  function javascript_validation() {
    $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
    '    var cc_owner = document.checkout_payment.cc_owner.value;' . "\n" .
    '    var cc_number = document.checkout_payment.cc_number.value;' . "\n";

    if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
      $js .= '    var cc_cvv = document.checkout_payment.cc_cvv.value;' . "\n";
    }

    $js .= '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n" .
    '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
    '      error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER . '";' . "\n" .
    '      error = 1;' . "\n" .
    '    }' . "\n";

    if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
      $js .= '    if (cc_cvv == "" || cc_cvv.length < ' . CC_CVV_MIN_LENGTH . ') {' . "\n" .
      '      error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_CVV . '";' . "\n" .
      '      error = 1;' . "\n" .
      '    }' . "\n";
    }

    $js .= '  }' . "\n";
    return $js;
  }
  /**
   * Builds set of input fields for collecting cc info
   *
   * @return array
   */
  function selection() {
    global $order;

    for ($i=1; $i<13; $i++) {
      $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B - (%m)',mktime(0,0,0,$i,1,2000)));
    }

    $today = getdate();
    for ($i=$today['year']; $i < $today['year']+10; $i++) {
      $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
    }

    $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

    $selection = array('id' => $this->code,
                       'module' => $this->title,
                       'fields' => array(array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
                                               'field' => zen_draw_input_field('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'id="'.$this->code.'-cc-owner"' . $onFocus),
                                               'tag' => $this->code.'-cc-owner'),
                                         array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
                                               'field' => zen_draw_input_field('cc_number', '', 'id="' . $this->code . '-cc-number"' . $onFocus),
                                               'tag' => $this->code . '-cc-number'),
                                         array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
                                               'field' => zen_draw_pull_down_menu('cc_expires_month', $expires_month, '', 'id="'.$this->code.'-cc-expires-month"' . $onFocus) . '&nbsp;' . zen_draw_pull_down_menu('cc_expires_year', $expires_year,  '', 'id="'.$this->code.'-cc-expires-year"' . $onFocus),
                                               'tag' => $this->code.'-cc-expires-month')
		               ));

    if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
      $selection['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_CVV,
                                     'field' => zen_draw_input_field('cc_cvv', '', 'size="4" maxlength="4" id="'.$this->code.'-cc-cvv"' . $onFocus),
                                     'tag' => $this->code.'-cc-cvv');
    }
	//MaxMind Start
	    if (MODULE_PAYMENT_CC_COLLECT_BIN_NAME == 'True')  {
      $selection['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_BIN_NAME,
                                     'field' => zen_draw_input_field('cc_bin_name', '', 'id="'.$this->code.'-cc-bin-name"' . $onFocus),
                                     'tag' => $this->code.'-cc-bin-name');
	}
	    if (MODULE_PAYMENT_CC_COLLECT_BIN_PHONE == 'True')  {
      $selection['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_BIN_PHONE,
                                     'field' => zen_draw_input_field('cc_bin_phone', '', 'id="'.$this->code.'-cc-bin-phone"' . $onFocus),
                                     'tag' => $this->code.'-cc-bin-phone');
	}
	//MaxMind End
    return $selection;
  }
  /**
   * Evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
   *
   */
  function pre_confirmation_check() {
    global $messageStack;
    /**
     * Load the cc_validation class
     */
    include(DIR_WS_CLASSES . 'cc_validation.php');

    $cc_validation = new cc_validation();
    $result = $cc_validation->validate($_POST['cc_number'], $_POST['cc_expires_month'], $_POST['cc_expires_year']);

    $error = '';
    switch ($result) {
      case -1:
      $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
      break;
      case -2:
      case -3:
      case -4:
      $error = TEXT_CCVAL_ERROR_INVALID_DATE;
      break;
      case false:
      $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
      break;
    }
    /**
     *
     */
    if ( ($result == false) || ($result < 1) ) {
      $payment_error_return = 'payment_error=' . $this->code . '&cc_owner=' . urlencode($_POST['cc_owner']) . '&cc_expires_month=' . $_POST['cc_expires_month'] . '&cc_expires_year=' . $_POST['cc_expires_year'];

      $messageStack->add_session('checkout_payment', $error . '<!-- ['.$this->code.'] -->', 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
    }

    $this->cc_card_type = $cc_validation->cc_type;
    $this->cc_card_number = $cc_validation->cc_number;
  }
  /**
   * Display Credit Card Information on the Checkout Confirmation Page
   *
   * @return array
   */
  function confirmation() {
    $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                          'fields' => array(array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
                          'field' => $_POST['cc_owner']),
                    array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
                          'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                    array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
                          'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['cc_expires_month'], 1, '20' . $_POST['cc_expires_year'])))));

    if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
      $confirmation['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_CVV,
                                        'field' => $_POST['cc_cvv']);
    }
	//MaxMind Start
	if (MODULE_PAYMENT_CC_COLLECT_BIN_NAME == 'True')  {
      $confirmation['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_BIN_NAME,
                                        'field' => $_POST['cc_bin_name']);
    }
	
	if (MODULE_PAYMENT_CC_COLLECT_BIN_PHONE == 'True')  {
      $confirmation['fields'][] = array('title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_BIN_PHONE,
                                        'field' => $_POST['cc_bin_phone']);
    }
	//MaxMind End
    return $confirmation;
  }
  /**
   * Build the data and actions to process when the "Submit" button is pressed on the order-confirmation screen.
   * This sends the data to the payment gateway for processing.
   * (These are hidden fields on the checkout confirmation page)
   *
   * @return string
   */
  function process_button() {
    $process_button_string = zen_draw_hidden_field('cc_owner', $_POST['cc_owner']) .
                             zen_draw_hidden_field('cc_expires', $_POST['cc_expires_month'] . $_POST['cc_expires_year']) .
                             zen_draw_hidden_field('cc_type', $this->cc_card_type) .
                             zen_draw_hidden_field('cc_number', $this->cc_card_number);
    if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
      $process_button_string .= zen_draw_hidden_field('cc_cvv', $_POST['cc_cvv']);
    }
	//MaxMind Start
	if (MODULE_PAYMENT_CC_COLLECT_BIN_NAME == 'True')  {
      $process_button_string .= zen_draw_hidden_field('cc_bin_name', $_POST['cc_bin_name']);
    }
	if (MODULE_PAYMENT_CC_COLLECT_BIN_PHONE == 'True')  {
      $process_button_string .= zen_draw_hidden_field('cc_bin_phone', $_POST['cc_bin_phone']);
    }
	//MaxMind End

    return $process_button_string;
  }
  /**
   * Store the CC info to the order
   *
   */
  function before_process() {
    global $order;
    if (defined('MODULE_PAYMENT_CC_STORE_NUMBER') && MODULE_PAYMENT_CC_STORE_NUMBER == 'True') {
      $order->info['cc_number'] = $_POST['cc_number'];
    }
    $order->info['cc_expires'] = $_POST['cc_expires'];
    $order->info['cc_type'] = $_POST['cc_type'];
    $order->info['cc_owner'] = $_POST['cc_owner'];
    $order->info['cc_cvv'] = $_POST['cc_cvv'];
	//MaxMind Start
	$order->info['cc_bin_name'] = $_POST['cc_bin_name'];
	$order->info['cc_bin_phone'] = $_POST['cc_bin_phone'];
	//MaxMind End

    $len = strlen($_POST['cc_number']);
    $this->cc_middle = substr($_POST['cc_number'], 6, ($len-8));
    if ( (defined('MODULE_PAYMENT_CC_EMAIL')) && (zen_validate_email(MODULE_PAYMENT_CC_EMAIL)) ) {
      $order->info['cc_number'] = substr($_POST['cc_number'], 0, 6) . str_repeat('X', (strlen($_POST['cc_number']) - 8)) . substr($_POST['cc_number'], -2);
    }
  }
  /**
   * Send the collected information via email to the store owner, storing outer digits and emailing middle digits
   *
   */
  function after_process() {
    global $insert_id;

    $message = sprintf(MODULE_PAYMENT_CC_TEXT_MIDDLE_DIGITS_MESSAGE, $insert_id, $this->cc_middle);
    $html_msg['EMAIL_MESSAGE_HTML'] = str_replace("\n\n",'<br />',$message);

    if ( (defined('MODULE_PAYMENT_CC_EMAIL')) && (zen_validate_email(MODULE_PAYMENT_CC_EMAIL)) ) {
      zen_mail(MODULE_PAYMENT_CC_EMAIL, MODULE_PAYMENT_CC_EMAIL, SEND_EXTRA_CC_EMAILS_TO_SUBJECT . $insert_id, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'cc_middle_digs');
    } //else {
      //$message = MODULE_PAYMENT_CC_TEXT_EMAIL_WARNING . $message;
      //$html_msg['EMAIL_MESSAGE_HTML'] = str_replace("\n\n",'<br />',$message);
      //zen_mail(EMAIL_FROM, EMAIL_FROM, MODULE_PAYMENT_CC_TEXT_EMAIL_ERROR . SEND_EXTRA_CC_EMAILS_TO_SUBJECT . $insert_id, $message, STORE_NAME, EMAIL_FROM, $html_msg, 'cc_middle_digs');
    //}
  }
  /**
   * Store additional order information
   *
   * @param int $zf_order_id
   */
  function after_order_create($zf_order_id) {
    global $db, $order;
    if (MODULE_PAYMENT_CC_COLLECT_CVV == 'True')  {
      $db->execute("update "  . TABLE_ORDERS . " set cc_cvv ='" . $order->info['cc_cvv'] . "' where orders_id = '" . $zf_order_id ."'");
    }
	//MaxMind Start
	if (MODULE_PAYMENT_CC_COLLECT_BIN_NAME == 'True')  {
      $db->execute("update "  . TABLE_ORDERS . " set cc_bin_name ='" . $order->info['cc_bin_name'] . "' where orders_id = '" . $zf_order_id ."'");
    }
	if (MODULE_PAYMENT_CC_COLLECT_BIN_PHONE == 'True')  {
      $db->execute("update "  . TABLE_ORDERS . " set cc_bin_phone ='" . $order->info['cc_bin_phone'] . "' where orders_id = '" . $zf_order_id ."'");
    }
	//Maxmind End
  }
  /**
   * Used to display error message details
   *
   * @return array
   */
  function get_error() {
    $error = array('title' => MODULE_PAYMENT_CC_TEXT_ERROR,
                   'error' => stripslashes(urldecode($_GET['error'])));

    return $error;
  }
  /**
   * Check to see whether module is installed
   *
   * @return boolean
   */
  function check() {
    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_CC_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }
  /**
   * Install the payment module and its configuration settings
   *
   */
  function install() {
    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Credit Card Module', 'MODULE_PAYMENT_CC_STATUS', 'True', 'Do you want to accept credit card payments?', '6', '130', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Split Credit Card Email Address', 'MODULE_PAYMENT_CC_EMAIL', '" . STORE_OWNER_EMAIL_ADDRESS . "', 'If an email address is entered, the middle digits of the credit card number will be sent to the email address (the outside digits are stored in the database with the middle digits censored)', '6', '131', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Collect & store the CVV number', 'MODULE_PAYMENT_CC_COLLECT_CVV', 'True', 'Do you want to collect the CVV number. Note: If you do the CVV number will be stored in the database in an encoded format.', '6', '132', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Store the Credit Card Number', 'MODULE_PAYMENT_CC_STORE_NUMBER', 'False', 'Do you want to store the Credit Card Number?<br /><br /><strong>WARNING: The Credit Card Number will be stored unenecrypted, and as such may represent a security problem.</strong>', '6', '133', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_CC_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '134' , now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_CC_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '135', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '136', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
    //MaxMind Start
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Collect & store the Bin Name', 'MODULE_PAYMENT_CC_COLLECT_BIN_NAME', 'True', 'Do you want to collect the Bin Name.', '6', '732', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Collect & store the Bin Phone', 'MODULE_PAYMENT_CC_COLLECT_BIN_PHONE', 'True', 'Do you want to collect the Bin Phone.', '6', '733', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    //MaxMind End
  
  }
  /**
   * Remove the module and all its settings
   *
   */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE\_PAYMENT\_CC\_%'");
  }
  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
   */
  function keys() {
      return array('MODULE_PAYMENT_CC_STATUS', 'MODULE_PAYMENT_CC_COLLECT_CVV', 'MODULE_PAYMENT_CC_STORE_NUMBER', 'MODULE_PAYMENT_CC_EMAIL', 'MODULE_PAYMENT_CC_ZONE', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', 'MODULE_PAYMENT_CC_SORT_ORDER',
	  //MaxMind Start
	  'MODULE_PAYMENT_CC_COLLECT_BIN_NAME',
	  'MODULE_PAYMENT_CC_COLLECT_BIN_PHONE'
	  //MaxMind End
	  );
  }
}
?>
