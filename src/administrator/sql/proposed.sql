CREATE TABLE IF NOT EXISTS `#__mymuse_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table.',
  `parentid` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `product_sku` varchar(64) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `title_alias` varchar(255) NOT NULL DEFAULT '',
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `price` decimal(10,4) default NULL,
  `product_discount` float(4,2) DEFAULT '0.00',
  `urls` text NOT NULL,
  `attribs` text NOT NULL,
  `version` int(11) unsigned NOT NULL DEFAULT '1',
  `ordering` int(11) NOT NULL DEFAULT '0',

  `access` int(11) unsigned NOT NULL DEFAULT '0',
  `hits` int(11) unsigned NOT NULL DEFAULT '0',

  `product_type` varchar(32) NULL COMMENT 'Parent, Physical, Digital, AllFiles', 

  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Set if product is featured.',
  `language` char(7) NOT NULL COMMENT 'The language code for the article.',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) unsigned NOT NULL DEFAULT '0',
  `created_by_alias` varchar(255) NOT NULL DEFAULT '',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) unsigned NOT NULL DEFAULT '0',
  `checked_out` int(10) UNSIGNED DEFAULT NULL,
  `checked_out_time` datetime DEFAULT NULL,
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',

  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_state` (`state`),
  KEY `idx_createdby` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__mymuse_product_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL DEFAULT '0',
  `catid` int(11) NOT NULL,
  `artistid` int(11) NOT NULL,
  `product_made_date` date DEFAULT '0000-00-00 00:00:00',
  `product_full_time` varchar(8) NULL,
  `product_country` char(2) NULL,
  `product_publisher` varchar(255) NULL,
  `product_producer` varchar(255) NULL,
  `product_studio` varchar(255) NULL,
  `product_coming_soon` int(1) NOT NULL DEFAULT '0',
  `product_preorder` int(1) NOT NULL DEFAULT '0',
  `special_status` varchar(32) NULL,
  `list_image` varchar(255) NOT NULL,
  `detail_image` varchar(255) NOT NULL,
  `metakey` varchar(1024) NOT NULL DEFAULT '',
  `metadesc` varchar(1024) NOT NULL DEFAULT '',
  `metadata` varchar(2048) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_catid` (`catid`),
  KEY `idx_artistid` (`artistid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS `#__mymuse_product_physical` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL DEFAULT '0',
  `product_in_stock` int(11) NOT NULL DEFAULT '1',
  `product_weight` decimal(10,4) DEFAULT NULL,
  `product_weight_uom` varchar(7) DEFAULT NULL,
  `product_length` decimal(10,4) DEFAULT NULL,
  `product_width` decimal(10,4) DEFAULT NULL,
  `product_height` decimal(10,4) DEFAULT NULL,
  `product_lwh_uom` varchar(7) DEFAULT NULL,
  `product_default` int(1) DEFAULT 0,
  `product_images` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS `#__mymuse_product_digital` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) unsigned NOT NULL DEFAULT '0',
  `file_name` varchar(2048) NULL,
  `file_length` varchar(32) NOT NULL,
  `file_ext` varchar(32) NOT NULL,
  `file_time` varchar(32) NOT NULL,
  `file_preview` varchar(255) NOT NULL DEFAULT '',
  `file_downloads` int(11) NOT NULL DEFAULT '0',
  `file_plays` int(11) NOT NULL DEFAULT '0',
  `isrc` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;