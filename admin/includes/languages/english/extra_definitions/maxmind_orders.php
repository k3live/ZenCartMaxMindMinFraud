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
//  $Id: maxmind_orders.php 1.0 2005-11-10 21:15:39Z ses707 $
//

define('HEADING_TITLE', 'MaxMind Orders');
define('HEADING_TITLE_SEARCH', 'Order ID:');
define('HEADING_TITLE_STATUS', 'Status:');

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

//MaxMind Information
define('MAXMIND_DISTANCE', 'Distance:');
define('MAXMIND_COUNTRY', 'Country Match:');
define('MAXMIND_CODE', 'Country Code:');
define('MAXMIND_FREE_EMAIL', 'Free Email:');
define('MAXMIND_ANONYMOUS', 'Anonymous Proxy:');
define('MAXMIND_SCORE', 'Score:');
define('MAXMIND_BIN_MATCH', 'Bin Match:');
define('MAXMIND_BIN_COUNTRY', 'Bin Country:');
define('MAXMIND_ERR', 'Error:');
define('MAXMIND_PROXY_SCORE', 'Proxy Score:');
define('MAXMIND_SPAM', 'Spam Score:');
define('MAXMIND_BIN_NAME', 'Bin Name:');
define('MAXMIND_BIN_COUNTRY', 'Bin Country:');
define('MAXMIND_IP_ISP', 'ISP:');
define('MAXMIND_IP_ISP_ORG', 'ISP Org:');
define('MAXMIND_IP_CITY', 'City:');
define('MAXMIND_IP_REGION', 'Region:');
define('MAXMIND_IP_LATITUDE', 'Latitude:');
define('MAXMIND_IP_LONGITUDE', 'Longitude:');
define('MAXMIND_PREMIUM', '<b>You need to be subscribed to Premium Services at <a href="http://www.maxmind.com/app/ccfd_features?rId=k3live" target="_blank"><u>MaxMind.com</u></a> for the following fields:</b>');
define('MAXMIND_HI_RISK', 'High Risk Country:');
define('MAXMIND_CUST_PHONE', 'Phone Match:');
define('MAXMIND_DETAILS', 'See <a href="http://www.maxmind.com/app/ccv?rId=k3live" target="_blank"><u>MaxMind.com</u></a> for a detailed explanation of fields.');
define('MAXMIND_UPDATE_NOW', '<u>Update MaxMind Information</u>');
define('MAXMIND_DELETE_NOW', '<u>Delete MaxMind Information</u>');
define('MAXMIND_BACK', '<b><- Go back to this order</b>');
define('MAXMIND_CITY_POSTAL_MATCH', 'City Postal Match:');
define('MAXMIND_SHIP_CITY_POSTAL_MATCH', 'Ship City Postal Match:');
define('MAXMIND_SHIP_FORWARD', 'Known Ship Forwarding:');
define('MAXMIND_ID', 'MaxMind ID:');
define('MAXMIND_QUERIES_REMAINING', 'Queries Remaining:');
define('MAXMIND_CARDER_EMAIL', 'Known Carder Email:');
define('MAXMIND_ID', 'MaxMind ID:');

//MaxMind Score Comments
define('MAXMIND_0', '<font color="#00CC00">0 (Extremely Low risk)</font>');
define('MAXMIND_1', '<font color="#00CC00">1 (Very Low risk)</font>');
define('MAXMIND_2', '<font color="#00CC00">2 (Low risk)</font>');
define('MAXMIND_3', '<font color="#00CC00">3 (Low risk)</font>');
define('MAXMIND_4', '<font color="#FF9900">4 (Low-Medium risk)</font>');
define('MAXMIND_5', '<font color="#FF9900">5 (Medium risk)</font>');
define('MAXMIND_6', '<font color="#FF9900">6 (Medium-high risk)</font>');
define('MAXMIND_7', '<font color="#FF9900">7 (High risk)</font>');
define('MAXMIND_8', '<font color="#FF0000">8 (Very High risk)</font>');
define('MAXMIND_9', '<font color="#FF0000">9 (Extremely High risk)</font>');
define('MAXMIND_10', '<font color="#FF0000">10 (I can smell the fraud from here)</font>');

//MaxMind Errors
define('MAXMIND_IP_NOT_FOUND', 'IP Address Not Found');
define('MAXMIND_COUNTRY_NOT_FOUND', 'Country Not Found');
define('MAXMIND_CITY_NOT_FOUND', 'City Not Found');
define('MAXMIND_CITY_REQUIRED', 'City Required');
define('MAXMIND_POSTAL_CODE_REQUIRED', 'Postal Code Required');
define('MAXMIND_POSTAL_CODE_NOT_FOUND', 'Postal Code Not Found');
define('MAXMIND_INVALID_LICENSE_KEY', 'Invalid License Key');
define('MAXMIND_MAX_REQUESTS_PER_IP', 'Maximum Requests Per IP');
define('MAXMIND_MAX_REQUESTS_PER_LICENSE', 'Maximum Requests Per License');
define('MAXMIND_IP_REQUIRED', 'IP Address Required');
define('MAXMIND_LICENSE_REQUIRED', 'License Required');
define('MAXMIND_COUNTRY_REQUIRED', 'Country Required');
define('MAXMIND_MAX_REQUESTS_REACHED', 'Maximum Requests Reached');

define('TABLE_HEADING_MAXMIND', 'MaxMind');

?>