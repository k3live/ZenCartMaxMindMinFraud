ALTER TABLE `orders_maxmind` ADD `risk_score` VARCHAR( 5 ) NOT NULL AFTER `score` ,
ADD `explanation` BLOB NOT NULL AFTER `risk_score` ;