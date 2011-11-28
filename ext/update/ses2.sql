CREATE TABLE IF NOT EXISTS `products_purchase_types_to_stores` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `purchase_type_id` int(11) NOT NULL DEFAULT '0',
  `stores_id` int(11) NOT NULL DEFAULT '0',
  `price` decimal(15,2) DEFAULT NULL,
  `tax_class_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_type_id_idx` (`purchase_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;