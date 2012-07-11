-- SQL for KWE version 3.0 --
-- Christoffer Lindahl, 2011 --

-- Database --

CREATE DATABASE `kwe` DEFAULT CHARACTER SET utf8 COLLATE utf8_swedish_ci;

-- Users table --

CREATE TABLE `kwe_users` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `username` varchar(20) collate utf8_unicode_ci NOT NULL,
  `password` varchar(32) collate utf8_unicode_ci NOT NULL,
  `rank` tinyint(1) unsigned NOT NULL,
  `online` tinyint(1) unsigned NOT NULL,
  `online_time` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Users value --

INSERT INTO `kwe_users` ( `name` , `username`, `password`, `rank`, `online`, `online_time` ) 
VALUES ('Admin', 'admin', MD5('password'), 1, 0, UNIX_TIMESTAMP());

-- Controllers table --

CREATE TABLE `kwe_controllers` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `class_name` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `configurable` tinyint(1) NOT NULL,
  `has_favorite_config` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_name` (`class_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Controllers value --

INSERT INTO `kwe_controllers` (`id`, `name`, `class_name`, `configurable`, `has_favorite_config`) VALUES 
(1, 'Artikel', 'Text', 0, 0),
(2, 'Nyheter', 'News', 1, 1),
(3, 'Kalender', 'Calendar', 1, 1),
(4, 'Omdirigering', 'Redirect', 0, 0);

-- Pages table --

CREATE TABLE `kwe_pages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(30) collate utf8_unicode_ci NOT NULL,
  `url` varchar(40) collate utf8_unicode_ci NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `public` tinyint(1) unsigned NOT NULL,
  `show_in_menu` tinyint(1) unsigned NOT NULL,
  `order` smallint(5) unsigned NOT NULL,
  `creator` smallint(5) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `editor` smallint(5) unsigned NOT NULL,
  `edited` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Pages value --

INSERT INTO `kwe_pages` ( `title` , `url`, `parent`, `public`, `show_in_menu`, `order`, `creator`, `created`, `editor`, `edited` ) 
VALUES ('Start', 'index', 0, 1, 1, 1, 1, UNIX_TIMESTAMP(), 1, UNIX_TIMESTAMP());

-- Page controllers table --

CREATE TABLE `kwe_page_controllers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page` int(10) unsigned NOT NULL,
  `controller` smallint(5) unsigned NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `order` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Page controllers value --

INSERT INTO `kwe_page_controllers` ( `page` , `controller`, `content`, `order` ) 
VALUES (1, 1, '<p>Detta är ett exempel på artikelmodulen. Här skriver du rena sidor med text, bilder och annan media.</p>', 1);

-- Permissions table --

CREATE TABLE `kwe_permissions` (
  `user` smallint(5) unsigned NOT NULL,
  `page` int(10) unsigned NOT NULL,
  `permission` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`user`,`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Controller permissions table --

CREATE TABLE `kwe_controller_permissions` (
  `user` smallint(5) unsigned NOT NULL,
  `controller` smallint(5) unsigned NOT NULL,
  `favorite` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`user`,`controller`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;