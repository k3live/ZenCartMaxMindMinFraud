CREATE TABLE `orders_maxmind` (
  `order_id` varchar(6) NOT NULL default '',
  `distance` varchar(5) NOT NULL default '',
  `country_match` char(3) NOT NULL default '',
  `country_code` varchar(21) NOT NULL default '',
  `free_mail` char(3) NOT NULL default '',
  `anonymous_proxy` char(3) NOT NULL default '',
  `score` varchar(5) NOT NULL default '',
  `bin_match` varchar(5) NOT NULL default '',
  `bin_country` varchar(5) NOT NULL default '',
  `err` varchar(20) NOT NULL default '',
  `proxy_score` varchar(5) NOT NULL default '',
  `spam_score` varchar(5) NOT NULL default '',
  `bin_name` varchar(20) NOT NULL default '',
  `cust_phone` varchar(15) NOT NULL default '',
  `ip_city` varchar(21) NOT NULL default '',
  `ip_latitude` varchar(21) NOT NULL default '',
  `ip_longitude` varchar(21) NOT NULL default '',
  `ip_region` varchar(21) NOT NULL default '',
  `ip_isp` varchar(30) NOT NULL default '',
  `ip_org` varchar(30) NOT NULL default '',
  `hi_risk` char(3) NOT NULL default '',
  `trans_proxy` char(3) NOT NULL default '',
  `carder_email` char(3) NOT NULL default '',
  `bin_name_match` varchar(8) NOT NULL default '',
  `bin_phone_match` varchar(8) NOT NULL default '',
  `bin_phone` varchar(10) NOT NULL default '',
  `ship_forward` char(3) NOT NULL default '',
  `city_postal_match` char(3) NOT NULL default '',
  `ship_city_postal_match` char(3) NOT NULL default '',
  `queries_remaining` varchar(5) NOT NULL default '',
  `maxmind_id` varchar(8) NOT NULL default '',
  KEY `order_id` (`order_id`)
) TYPE=MyISAM;

INSERT INTO `configuration` ( `configuration_id` , `configuration_title` , `configuration_key` , `configuration_value` , `configuration_description` , `configuration_group_id` , `sort_order` , `last_modified` , `date_added` , `use_function` , `set_function` )
VALUES (
'1500', 'MaxMind License Key', 'MAXMIND_LICENSE', 'MaxMind License Key', 'Enter your MaxMind License Key. If you do not have one please visit <a href="http://www.maxmind.com/app/ccv?rId=k3live" target="_blank"><u>MaxMind.com</u></a>.', '1', '50', NOW( ) , NOW( ) , NULL , NULL
);