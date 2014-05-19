<?php
//
// +----------------------------------------------------------------------+
// |MaxMind CCFD Module for Zen-Cart Open Source E-commerce               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license.       |
// +----------------------------------------------------------------------+
//  $Id: maxmind_orders.php 1.3 2007-01-05 23:07:39Z ses707 $
//

define('HEADING_TITLE', 'MaxMind Orders');
define('HEADING_TITLE_SEARCH', 'Order ID:');
define('HEADING_TITLE_STATUS', 'Status:');
define('HEADING_TITLE_SEARCH_DETAIL_ORDERS_PRODUCTS', 'Search by Product Name or <strong>ID:XX</strong> or Model ');
define('TEXT_INFO_SEARCH_DETAIL_FILTER_ORDERS_PRODUCTS', 'Search Filter: ');
define('TABLE_HEADING_PAYMENT_METHOD', 'Payment<br />Shipping');
define('TABLE_HEADING_ORDERS_ID','ID');

define('TEXT_BILLING_SHIPPING_MISMATCH','Billing and Shipping does not match ');

define('TABLE_HEADING_COMMENTS', 'Comments');
define('TABLE_HEADING_CUSTOMERS', 'Customers');
define('TABLE_HEADING_ORDER_TOTAL', 'Order Total');
define('TABLE_HEADING_DATE_PURCHASED', 'Date Purchased');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_TYPE', 'Order Type');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_QUANTITY', 'Qty.');
define('TABLE_HEADING_PRODUCTS_MODEL', 'Model');
define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_TAX', 'Tax');
define('TABLE_HEADING_TOTAL', 'Total');
define('TABLE_HEADING_PRICE_EXCLUDING_TAX', 'Price (ex)');
define('TABLE_HEADING_PRICE_INCLUDING_TAX', 'Price (inc)');
define('TABLE_HEADING_TOTAL_EXCLUDING_TAX', 'Total (ex)');
define('TABLE_HEADING_TOTAL_INCLUDING_TAX', 'Total (inc)');

define('TABLE_HEADING_CUSTOMER_NOTIFIED', 'Customer Notified');
define('TABLE_HEADING_DATE_ADDED', 'Date Added');

define('ENTRY_CUSTOMER', 'Customer:');
define('ENTRY_SOLD_TO', 'SOLD TO:');
define('ENTRY_DELIVERY_TO', 'Delivery To:');
define('ENTRY_SHIP_TO', 'SHIP TO:');
define('ENTRY_SHIPPING_ADDRESS', 'Shipping Address:');
define('ENTRY_BILLING_ADDRESS', 'Billing Address:');
define('ENTRY_PAYMENT_METHOD', 'Payment Method:');
define('ENTRY_CREDIT_CARD_TYPE', 'Credit Card Type:');
define('ENTRY_CREDIT_CARD_OWNER', 'Credit Card Owner:');
define('ENTRY_CREDIT_CARD_NUMBER', 'Credit Card Number:');
define('ENTRY_CREDIT_CARD_CVV', 'Credit Card CVV Number:');
define('ENTRY_CREDIT_CARD_EXPIRES', 'Credit Card Expires:');
define('ENTRY_SUB_TOTAL', 'Sub-Total:');
define('ENTRY_TAX', 'Tax:');
define('ENTRY_SHIPPING', 'Shipping:');
define('ENTRY_TOTAL', 'Total:');
define('ENTRY_DATE_PURCHASED', 'Date Purchased:');
define('ENTRY_STATUS', 'Status:');
define('ENTRY_DATE_LAST_UPDATED', 'Date Last Updated:');
define('ENTRY_NOTIFY_CUSTOMER', 'Notify Customer:');
define('ENTRY_NOTIFY_COMMENTS', 'Append Comments:');
define('ENTRY_PRINTABLE', 'Print Invoice');

define('TEXT_INFO_HEADING_DELETE_ORDER', 'Delete Order');
define('TEXT_INFO_DELETE_INTRO', 'Are you sure you want to delete this order?');
define('TEXT_INFO_RESTOCK_PRODUCT_QUANTITY', 'Restock product quantity');
define('TEXT_DATE_ORDER_CREATED', 'Date Created:');
define('TEXT_DATE_ORDER_LAST_MODIFIED', 'Last Modified:');
define('TEXT_INFO_PAYMENT_METHOD', 'Payment Method:');
define('TEXT_PAID', 'Paid');
define('TEXT_UNPAID', 'Un-paid');

define('TEXT_ALL_ORDERS', 'All Orders');
define('TEXT_NO_ORDER_HISTORY', 'No Order History Available');

define('EMAIL_SEPARATOR', '------------------------------------------------------');
define('EMAIL_TEXT_SUBJECT', 'Order Update');
define('EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');
define('EMAIL_TEXT_INVOICE_URL', 'Detailed Invoice:');
define('EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
define('EMAIL_TEXT_COMMENTS_UPDATE', '<em>The comments for your order are: </em>');
define('EMAIL_TEXT_STATUS_UPDATED', 'Your order has been updated to the following status:' . "\n");
define('EMAIL_TEXT_STATUS_LABEL', '<strong>New status:</strong> %s' . "\n\n");
define('EMAIL_TEXT_STATUS_PLEASE_REPLY', 'Please reply to this email if you have any questions.' . "\n");

define('ERROR_ORDER_DOES_NOT_EXIST', 'Error: Order does not exist.');
define('SUCCESS_ORDER_UPDATED', 'Success: Order has been successfully updated.');
define('WARNING_ORDER_NOT_UPDATED', 'Warning: Nothing to change. The order was not updated.');

define('ENTRY_ORDER_ID','Invoice No. ');
define('TEXT_INFO_ATTRIBUTE_FREE', '&nbsp;-&nbsp;<span class="alert">FREE</span>');

define('TEXT_DOWNLOAD_TITLE', 'Order Download Status');
define('TEXT_DOWNLOAD_STATUS', 'Status');
define('TEXT_DOWNLOAD_FILENAME', 'Filename');
define('TEXT_DOWNLOAD_MAX_DAYS', 'Days');
define('TEXT_DOWNLOAD_MAX_COUNT', 'Count');

define('TEXT_DOWNLOAD_AVAILABLE', 'Available');
define('TEXT_DOWNLOAD_EXPIRED', 'Expired');
define('TEXT_DOWNLOAD_MISSING', 'Not on Server');

define('IMAGE_ICON_STATUS_CURRENT', 'Status - Available');
define('IMAGE_ICON_STATUS_EXPIRED', 'Status - Expired');
define('IMAGE_ICON_STATUS_MISSING', 'Status - Missing');

define('SUCCESS_ORDER_UPDATED_DOWNLOAD_ON', 'Download was successfully enabled');
define('SUCCESS_ORDER_UPDATED_DOWNLOAD_OFF', 'Download was successfully disabled');
define('TEXT_MORE', '... more');

define('TEXT_INFO_IP_ADDRESS', 'IP Address: ');
define('TEXT_DELETE_CVV_FROM_DATABASE','Delete CVV from database');
define('TEXT_DELETE_CVV_REPLACEMENT','Deleted');
define('TEXT_MASK_CC_NUMBER','Mask this number');

define('TEXT_INFO_EXPIRED_DATE', 'Expired Date:<br />');
define('TEXT_INFO_EXPIRED_COUNT', 'Expired Count:<br />');

define('TABLE_HEADING_CUSTOMER_COMMENTS', 'Customer<br />Comments');
define('TEXT_COMMENTS_YES', 'Customer Comments - YES');
define('TEXT_COMMENTS_NO', 'Customer Comments - NO');

//MaxMind
//IP Address Changes
define('TEXT_INFO_START_IP_ADDRESS', 'Start IP Address: ');
define('TEXT_INFO_END_IP_ADDRESS', 'End IP Address: ');

//Geographical IP address location checking
define('MAXMIND_COUNTRY', 'Match:');
define('MAXMIND_CODE', 'Country Code');
define('MAXMIND_HI_RISK', 'High Risk Country:');
define('MAXMIND_DISTANCE', 'Distance:');
define('MAXMIND_IP_REGION', 'Region: ');
define('MAXMIND_IP_CITY', 'City: ');
define('MAXMIND_IP_LATITUDE', 'Latitude: ');
define('MAXMIND_IP_LONGITUDE', 'Longitude: ');
define('MAXMIND_IP_ISP', 'ISP:');
define('MAXMIND_IP_ISP_ORG', 'ISP Org:');

//Proxy Detection
define('MAXMIND_ANONYMOUS_PROXY', 'Anonymous Proxy:');
define('MAXMIND_PROXY_SCORE', 'Proxy Score:');
define('MAXMIND_TRANSPARENT_PROXY', 'Transparent Proxy:');

//E-mail and Login Checks
define('MAXMIND_FREE_EMAIL', 'Free Email:');
define('MAXMIND_CARDER_EMAIL', 'Known Carder Email:');
define('MAXMIND_HIGH_RISK_USERNAME', 'High Risk User');
define('MAXMIND_HIGH_RISK_PASSWORD', 'Pass:');

//Issuing Bank BIN Number Checks
define('MAXMIND_BIN_COUNTRY_MATCH', 'Bin Country Match:');
define('MAXMIND_BIN_COUNTRY_OUTPUT', 'Bin Country Output: ');
define('MAXMIND_BIN_NAME_MATCH', 'Bin Name Match:');
define('MAXMIND_BIN_NAME_OUTPUT', 'Bin Name Output: ');
define('MAXMIND_BIN_PHONE_MATCH', 'Bin Phone Match:');
define('MAXMIND_BIN_PHONE_OUTPUT', 'Bin Phone Output: ');

//Address and Phone Number Checks
define('MAXMIND_CUST_PHONE', 'Phone Match:');
define('MAXMIND_SHIP_FORWARD', 'Known Ship Forwarding:');
define('MAXMIND_CITY_POSTAL_MATCH', 'City Postal Match:');
define('MAXMIND_SHIP_CITY_POSTAL_MATCH', 'Ship City Postal Match:');

//Risk Score
define('MAXMIND_SCORE', 'Score (0-10):');
define('MAXMIND_RISK_SCORE', 'Score (0-100):');
define('MAXMIND_EXPLANATION', 'Explanation:');

//Account Information
define('MAXMIND_QUERIES_REMAINING', 'Queries Remaining:');
define('MAXMIND_ID', 'MaxMind ID:');
define('MAXMIND_ERR', 'Error:');

//Standard and Premium
define('MAXMIND_STANDARD', '<b>You need to be subscribed to Standard Services at <a href="http://www.maxmind.com/app/ccfd_features?rId=k3live" target="_blank"><u>MaxMind.com</u></a> for the following fields:</b>');
define('MAXMIND_PREMIUM', '<b>You need to be subscribed to Premium Services at <a href="http://www.maxmind.com/app/ccfd_features?rId=k3live" target="_blank"><u>MaxMind.com</u></a> for the following fields:</b>');

//Admin Information and Functions
define('MAXMIND_DETAILS', 'See <a href="http://www.maxmind.com/app/fraud-detection-manual?rId=k3live" target="_blank"><u>MaxMind.com</u></a> for a detailed explanation of fields.');
define('MAXMIND_UPDATE_NOW', '<u>Update MaxMind Information</u>');
define('MAXMIND_UPDATE_FREE', '<u>Basic</u>');
define('MAXMIND_UPDATE_CITY', '<u>Standard</u>');
define('MAXMIND_UPDATE_PREMIUM', '<u>Premium</u>');
define('MAXMIND_UPDATE_CITY_B', '<u><b>Standard</b></u>');
define('MAXMIND_UPDATE_PREMIUM_B', '<u><b>Premium</b></u>');
define('MAXMIND_DELETE_NOW', '<u>Delete MaxMind Information</u>');
define('MAXMIND_BACK', '<b><- Go back to this order</b>');
define('MAXMIND_REPORT', 'Report Score Inaccuracy:');
define('MAXMIND_BIN_NAME_INPUT', 'Bin Name Input:');
define('MAXMIND_BIN_PHONE_INPUT', 'Bin Phone Input:');
define('TABLE_HEADING_MAXMIND', 'MaxMind');
define('MAXMIND_MINIFRAUD_VERSION_OUT_OF_DATE', 'It appears that your MaxMind miniFraud account has not been updated to version 1.1. Please visit <a href="http://www.maxmind.com/app/minfraud_version?rId=k3live" target="_blank">MaxMind.com</a> and select miniFraud version 1.1.<br />&nbsp;&nbsp;&nbsp;(Note: This warning will appear on older orders that do not contain an explanation and can be safely ignored.)');
define('MAXMIND_DELETED', 'Deleted MaxMind Information');

//Score Comments
define('MAXMIND_0', '<font color="#00CC00"> (Extremely Low Risk)</font>');
define('MAXMIND_1', '<font color="#00CC00"> (Very Low Risk)</font>');
define('MAXMIND_2', '<font color="#00CC00"> (Low Risk)</font>');
define('MAXMIND_3', '<font color="#00CC00"> (Low Risk)</font>');
define('MAXMIND_4', '<font color="#FF9900"> (Low-Medium Risk)</font>');
define('MAXMIND_5', '<font color="#FF9900"> (Medium Risk)</font>');
define('MAXMIND_6', '<font color="#FF9900"> (Medium-High Risk)</font>');
define('MAXMIND_7', '<font color="#FF0000"> (High Risk)</font>');
define('MAXMIND_8', '<font color="#FF0000"> (Very High Risk)</font>');
define('MAXMIND_9', '<font color="#FF0000"> (Extremely High Risk)</font>');
define('MAXMIND_10', '<font color="#FF0000"> (I can smell the fraud from here!)</font>');

//Warning Errors
define('MAXMIND_IP_NOT_FOUND', 'IP Address Not Found');
define('MAXMIND_COUNTRY_NOT_FOUND', 'Country Not Found');
define('MAXMIND_CITY_NOT_FOUND', 'City Not Found');
define('MAXMIND_CITY_REQUIRED', 'City Required');
define('MAXMIND_POSTAL_CODE_REQUIRED', 'Postal Code Required');
define('MAXMIND_POSTAL_CODE_NOT_FOUND', 'Postal Code Not Found');

//Fatal Errors
define('MAXMIND_INVALID_LICENSE_KEY', 'Invalid License Key');
define('MAXMIND_MAX_REQUESTS_PER_LICENSE', 'Maximum Requests Per License');
define('MAXMIND_IP_REQUIRED', 'IP Address Required');
define('MAXMIND_LICENSE_REQUIRED', 'License Required');
define('MAXMIND_COUNTRY_REQUIRED', 'Country Required');
define('MAXMIND_MAX_REQUESTS_REACHED', 'Maximum Requests Reached');

?>