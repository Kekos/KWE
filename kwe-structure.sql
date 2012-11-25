CREATE TABLE `PREFIX_calendar` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `starttime` int(10) unsigned NOT NULL,
  `endtime` int(10) unsigned NOT NULL,
  `creator` smallint(5) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `PREFIX_controllers` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `class_name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `configurable` tinyint(1) NOT NULL,
  `has_favorite_config` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_name` (`class_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `PREFIX_controllers` (`id`, `name`, `class_name`, `configurable`, `has_favorite_config`) VALUES 
(1, 'Text', 'Text', 0, 0),
(2, 'News', 'News', 1, 1),
(3, 'Calendar', 'Calendar', 1, 1),
(4, 'Redirect', 'Redirect', 0, 0),
(5, 'File upload', 'Upload', 1, 0);

CREATE TABLE `PREFIX_controller_permissions` (
  `user` smallint(5) unsigned NOT NULL,
  `controller` smallint(5) unsigned NOT NULL,
  `favorite` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`user`,`controller`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

CREATE TABLE `PREFIX_news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `creator` smallint(5) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `PREFIX_pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `language` int(11) unsigned NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `public` tinyint(1) unsigned NOT NULL,
  `show_in_menu` tinyint(1) unsigned NOT NULL,
  `order` smallint(5) unsigned NOT NULL,
  `creator` smallint(5) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `editor` smallint(5) unsigned NOT NULL,
  `edited` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `PREFIX_pages` ( `title` , `url`, `language`, `parent`, `public`, `show_in_menu`, `order`, `creator`, `created`, `editor`, `edited` ) VALUES 
('Start', 'index', 1, 0, 1, 1, 1, 1, UNIX_TIMESTAMP(), 1, UNIX_TIMESTAMP()),
('The page could not be found', '404', 1, 0, 1, 0, 2, 1, UNIX_TIMESTAMP(), 1, UNIX_TIMESTAMP()),
('Start', 'index', 2, 0, 1, 1, 1, 1, UNIX_TIMESTAMP(), 1, UNIX_TIMESTAMP()),
('Sidan kunde inte hittas', '404', 2, 0, 1, 0, 2, 1, UNIX_TIMESTAMP(), 1, UNIX_TIMESTAMP());

CREATE TABLE `PREFIX_page_controllers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page` int(10) unsigned NOT NULL,
  `controller` smallint(5) unsigned NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `order` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `PREFIX_page_controllers` ( `page` , `controller`, `content`, `order` ) VALUES 
(1, 1, '<p>This is an exampel of the text module. You can write text, add images and other media.</p>', 1),
(2, 1, '<p>The page could not be found.</p>', 1),
(3, 1, '<p>Detta är ett exempel på artikelmodulen. Här skriver du rena sidor med text, bilder och annan media.</p>', 1),
(4, 1, '<p>Sidan kunde inte hittas.</p>', 1);

CREATE TABLE `PREFIX_permissions` (
  `user` smallint(5) unsigned NOT NULL,
  `page` int(10) unsigned NOT NULL,
  `permission` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`user`,`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `PREFIX_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `PREFIX_languages` (`id`, `name`, `code`) VALUES
(1, 'English', 'en'),
(2, 'Svenska', 'sv');

CREATE TABLE `PREFIX_users` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `rank` tinyint(1) unsigned NOT NULL,
  `online` tinyint(1) unsigned NOT NULL,
  `online_time` int(10) unsigned NOT NULL,
  `language` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;