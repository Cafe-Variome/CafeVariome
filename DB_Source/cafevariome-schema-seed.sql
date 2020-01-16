-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 16, 2020 at 03:01 PM
-- Server version: 5.7.26
-- PHP Version: 7.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cafevariome`
--

-- --------------------------------------------------------

--
-- Table structure for table `eavs`
--

DROP TABLE IF EXISTS `eavs`;
CREATE TABLE IF NOT EXISTS `eavs` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `uid` varchar(50) NOT NULL,
  `source` varchar(50) NOT NULL,
  `fileName` mediumint(8) UNSIGNED NOT NULL,
  `subject_id` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `attribute` varchar(50) NOT NULL,
  `value` varchar(200) DEFAULT NULL,
  `elastic` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65539 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `description`) VALUES
(1, 'admin', 'Administrator'),
(2, 'members', 'General User');

-- --------------------------------------------------------

--
-- Table structure for table `local_phenotypes_lookup`
--

DROP TABLE IF EXISTS `local_phenotypes_lookup`;
CREATE TABLE IF NOT EXISTS `local_phenotypes_lookup` (
  `lookup_id` int(11) NOT NULL AUTO_INCREMENT,
  `network_key` varchar(32) NOT NULL,
  `phenotype_attribute` varchar(128) NOT NULL,
  `phenotype_values` varchar(256) NOT NULL,
  PRIMARY KEY (`lookup_id`),
  KEY `network_key` (`network_key`)
) ENGINE=InnoDB AUTO_INCREMENT=961 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
CREATE TABLE IF NOT EXISTS `menus` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(20) NOT NULL,
  PRIMARY KEY (`menu_id`),
  UNIQUE KEY `menu_name` (`menu_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `networks`
--

DROP TABLE IF EXISTS `networks`;
CREATE TABLE IF NOT EXISTS `networks` (
  `network_key` int(11) NOT NULL,
  `network_name` text NOT NULL,
  `network_type` varchar(50) NOT NULL,
  PRIMARY KEY (`network_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `network_groups`
--

DROP TABLE IF EXISTS `network_groups`;
CREATE TABLE IF NOT EXISTS `network_groups` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  `network_key` int(11) NOT NULL,
  `group_type` varchar(50) NOT NULL,
  `url` varchar(256) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `network_key` (`network_key`),
  KEY `type` (`group_type`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `network_groups_sources`
--

DROP TABLE IF EXISTS `network_groups_sources`;
CREATE TABLE IF NOT EXISTS `network_groups_sources` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_id` mediumint(8) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL,
  `installation_key` varchar(100) NOT NULL,
  `network_key` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `source_id` (`source_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `network_requests`
--

DROP TABLE IF EXISTS `network_requests`;
CREATE TABLE IF NOT EXISTS `network_requests` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `network_key` int(11) NOT NULL,
  `installation_key` varchar(100) NOT NULL,
  `url` text NOT NULL,
  `justification` text NOT NULL,
  `email` varchar(50) NOT NULL,
  `ip` text NOT NULL,
  `token` varchar(32) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `NetworkRequest_NetworkKey_FK` (`network_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE IF NOT EXISTS `pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_name` varchar(50) NOT NULL,
  `page_content` text NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parent_menu` varchar(20) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_title` varchar(120) NOT NULL,
  `post_body` text NOT NULL,
  `post_date_sort` datetime NOT NULL,
  `post_date` varchar(30) NOT NULL,
  `post_visible` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_id` int(10) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `value` varchar(100) NOT NULL,
  `setting_name` varchar(50) DEFAULT NULL,
  `info` text NOT NULL,
  `validation_rules` varchar(100) NOT NULL DEFAULT 'required|xss_clean',
  PRIMARY KEY (`setting_id`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `value`, `setting_name`, `info`, `validation_rules`) VALUES
(1, 'site_title', 'Cafe Variome 2', 'Site Title', '', 'required'),
(2, 'site_description', 'Cafe Variome - Description', 'Site Description', '', 'required'),
(3, 'site_author', 'Bioinformatics Research Group - University of Leicester', 'Site Author', '', 'required'),
(4, 'site_keywords', 'healthcare data discovery, bioinformatics', 'Keywords', '', 'required'),
(5, 'email', 'admin@cafevariome.org', 'Administrator Email', '', 'required'),
(6, 'twitter', '', 'Twitter ID', 'If Twitter username is here set then Twitter icon link appears in contact page. Leave blank to disable.', ''),
(7, 'rss', 'local', 'RSS Feed Address', 'Specify a VALID rss feed or to use the internal Cafe Variome news feed then just enter local (on its own)', ''),
(8, 'google_analytics', '', 'Google Analytics ID', 'Google Analytics tracking ID', ''),
(9, 'cvid_prefix', 'vx', NULL, 'Prefix that is prepended to Cafe Variome IDs', 'alpha'),
(10, 'stats', 'on', NULL, '', 'required'),
(11, 'max_variants', '30000', NULL, '', 'required'),
(12, 'feature_table_name', 'variants', NULL, '', 'required'),
(13, 'messaging', 'off', 'Messaging Enabled?', 'Enables/disables the internal messaging system for all users', 'required'),
(14, 'database_structure', 'off', NULL, 'Enables the tab to change database structure in the settings admin interface', 'required'),
(15, 'federated', 'off', 'Federated Install', 'If set to on then the federated API is enables and allows remote discovery queries from other Cafe Variome installs', 'required'),
(16, 'federated_head', 'off', NULL, 'Sets this installation as the main federated head through which installs can be', 'required'),
(17, 'show_orcid_reminder', 'off', NULL, 'Shows a one off message to users on the home page reminding them to link their ORCID to their Cafe Variome account', 'required'),
(18, 'atomserver_enabled', 'off', NULL, '', 'required'),
(19, 'atomserver_user', '', NULL, '', 'required'),
(20, 'atomserver_password', '', NULL, '', 'required'),
(21, 'atomserver_uri', 'http://www.cafevariome.org/atomserver/v1/cafevariome/variants', NULL, '', 'required'),
(22, 'cafevariome_central', 'off', NULL, 'If set to on then this is a Cafe Variome Central installation - additional menus for describing the system will be enabled', 'required'),
(23, 'allow_registrations', 'on', 'Allow User Registration', 'If set to on then users can register on the site, otherwise the signup is hidden', 'required'),
(24, 'variant_count_cutoff', '0', NULL, 'If the number of variants discovered in a source is less than this then the results are hidden and the message in the variant_count_cutoff_message setting is displayed', 'required'),
(25, 'variant_count_cutoff_message', 'Unable to display results for this source, please contact admin@cafevariome.org', NULL, 'Message that is shown when the number of variants in less than that specified in the variant_count_cutoff setting', 'required'),
(26, 'dasigniter', 'on', NULL, 'If set to on then DASIgniter is enabled and variants in sources that are openAccess and linkedAccess will be available via DAS', 'required'),
(27, 'bioportalkey', '', NULL, 'In order to use phenotype ontologies you must sign up for a BioPortal account and supply your API key here. If this is left blank you only be able to use free text for phenotypes. Sign up at http://bioportal.bioontology.org/accounts/new', 'required'),
(28, 'template', 'default', 'Template', 'Specify the name of the css template file (located in views/css/)', 'required'),
(29, 'discovery_requires_login', 'off', 'Authorization Required for Discovery?', 'If set to on then discovery searches cannot be done unless a user is logged in.', 'required'),
(30, 'show_sources_in_discover', 'on', 'Show Sources in Discovery', 'If set to off then only the search box will be shown in the discovery interface (i.e. not the sources to search)', 'required'),
(31, 'use_elasticsearch', 'on', NULL, 'If set to on then elasticsearch will be used instead of the basic search (elasticsearch needs to be running of course)', 'required'),
(32, 'auth_server', ' ', 'Authorization Server URL', 'Central Cafe Variome Auth server url (WARNING: do not change)', 'required'),
(33, 'installation_key', ' ', 'Installation Key', 'Unique key for this installation (WARNING: do not change this value unless you know what you are doing)', 'required'),
(34, 'all_records_require_an_id', 'on', NULL, 'Checks whether all records have a record ID during import (which must be unique)', 'required'),
(35, 'site_requires_login', 'off', NULL, 'If enabled then users will be required to log in to access any part of the site. If not logged in they will be presented with a login form.', 'required'),
(36, 'allow_discovery', 'on', 'Discovery Enabled?', 'If set to on then users can discover on the site, otherwise the discovery is hidden', 'required'),
(37, 'current_font_link', 'Roboto', 'Font Link', 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(38, 'header_colour_from', '#a5cac2', 'Header Color From', 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(39, 'header_colour_to', '#afb3ba', 'Header Color To', 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(40, 'background', 'noisy_grid.png', 'Background Image File', 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(41, 'logo', 'cafevariome-logo-full.png', 'Logo File', 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(42, 'font_size', '14px', 'Font Size', 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(43, 'current_font_name', 'Roboto', 'Font Name', 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(44, 'id_prefix', 'vx', NULL, 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(45, 'id_current', '234333355', NULL, 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(46, 'report_usage', '1', NULL, 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(47, 'navbar_font_colour', '#eeeeee', 'Navbar Color', 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(48, 'navbar_font_colour_hover', '#ffffff', 'Navbar Hover Color', 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(49, 'navbar_selected_tab_colour', '#6c737e', 'Navbar Selected Tab Color', 'Imported from preferences.php by Mehdi Mehtarizadeh', 'required'),
(50, 'key_cloak_uri', ' ', 'Keycloak Server Address', 'URI for keycloak authentication server', 'required'),
(51, 'key_cloak_realm', 'cafe_key', 'Keycloak Realm Name', '', 'required'),
(52, 'key_cloak_client_id', 'my-client', 'Keycloak Client ID', '', 'required'),
(53, 'key_cloak_client_secret', '102759a7-1a70-499b-abf6-c2c11838408b', 'Keycloak Client Secret', '', 'required'),
(54, 'key_cloak_login_uri', ' ', 'Keycloak Login Address', '', 'required'),
(55, 'key_cloak_logout_uri', ' ', 'Keycloak Logout Address', '', 'required'),
(56, 'key_cloak_port', ' ', 'Keycloak Port', '', 'required'),
(57, 'elastic_url', 'http://localhost:9200', 'Elasticsearch Address', 'Elastic search address', 'required'),
(58, 'key_cloak_admin_id', 'f02288f5-1c48-4be0-9868-179028a77f8a', 'Keycloak Admin Id', 'Admin user id within key cloak server', 'required'),
(59, 'key_cloak_admin_username', 'admin', 'Keycloak Admin Username', 'Admin user username within key cloak server', 'required'),
(60, 'key_cloak_admin_password', ' ', 'Keycloak Admin Password', 'Admin user password within key cloak server.', 'required'),
(63, 'neo4j_username', 'neo4j', 'Neo4J Username', '', 'required'),
(62, 'neo4j_port', '7474', 'Neo4J Port', '', 'required'),
(61, 'neo4j_server', 'http://localhost', 'Neo4J Server', '', 'required'),
(64, 'neo4j_password', ' ', 'Neo4J Password', '', 'required');

-- --------------------------------------------------------

--
-- Table structure for table `sources`
--

DROP TABLE IF EXISTS `sources`;
CREATE TABLE IF NOT EXISTS `sources` (
  `source_id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_name` text NOT NULL,
  `email` text NOT NULL,
  `name` varchar(30) NOT NULL,
  `uri` text NOT NULL,
  `description` text NOT NULL,
  `long_description` text NOT NULL,
  `status` varchar(15) NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'mysql',
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `elastic_status` tinyint(1) DEFAULT '0',
  `elastic_lock` tinyint(1) DEFAULT '0',
  `elastic_data` tinyint(1) DEFAULT '0',
  `neo4j_data` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`source_id`),
  UNIQUE KEY `name` (`name`),
  KEY `status` (`status`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sources_groups`
--

DROP TABLE IF EXISTS `sources_groups`;
CREATE TABLE IF NOT EXISTS `sources_groups` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_id` mediumint(8) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `source_id` (`source_id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

DROP TABLE IF EXISTS `themes`;
CREATE TABLE IF NOT EXISTS `themes` (
  `theme_id` int(11) NOT NULL AUTO_INCREMENT,
  `theme_name` varchar(30) NOT NULL,
  `header_colour_from` varchar(20) NOT NULL,
  `header_colour_to` varchar(20) NOT NULL,
  `logo` varchar(50) NOT NULL,
  `background` varchar(50) NOT NULL,
  `navbar_font_colour` varchar(20) NOT NULL,
  `navbar_font_colour_hover` varchar(20) NOT NULL,
  `navbar_selected_tab_colour` varchar(20) NOT NULL,
  `font_name` varchar(50) NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`theme_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `uploaddatastatus`
--

DROP TABLE IF EXISTS `uploaddatastatus`;
CREATE TABLE IF NOT EXISTS `uploaddatastatus` (
  `FileName` varchar(40) NOT NULL,
  `uploadStart` datetime NOT NULL,
  `uploadEnd` datetime DEFAULT NULL,
  `Status` varchar(20) NOT NULL,
  `elasticStatus` varchar(20) NOT NULL,
  `source_id` int(11) NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient` varchar(50) DEFAULT NULL,
  `tissue` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `upload_error`
--

DROP TABLE IF EXISTS `upload_error`;
CREATE TABLE IF NOT EXISTS `upload_error` (
  `ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `error_id` mediumint(8) UNSIGNED NOT NULL,
  `message` varchar(500) NOT NULL,
  `error_code` int(5) NOT NULL,
  `source_id` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `upload_jobs`
--

DROP TABLE IF EXISTS `upload_jobs`;
CREATE TABLE IF NOT EXISTS `upload_jobs` (
  `ID` int(8) NOT NULL AUTO_INCREMENT,
  `source_id` int(11) NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `linking_id` varchar(50) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(254) NOT NULL,
  `activation_selector` varchar(255) DEFAULT NULL,
  `activation_code` varchar(255) DEFAULT NULL,
  `forgotten_password_selector` varchar(255) DEFAULT NULL,
  `forgotten_password_code` varchar(255) DEFAULT NULL,
  `forgotten_password_time` int(11) UNSIGNED DEFAULT NULL,
  `remember_selector` varchar(255) DEFAULT NULL,
  `remember_code` varchar(255) DEFAULT NULL,
  `created_on` int(11) UNSIGNED NOT NULL,
  `last_login` int(11) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) UNSIGNED DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `token` varchar(50) DEFAULT NULL,
  `remote` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_email` (`email`),
  UNIQUE KEY `uc_activation_selector` (`activation_selector`),
  UNIQUE KEY `uc_forgotten_password_selector` (`forgotten_password_selector`),
  UNIQUE KEY `uc_remember_selector` (`remember_selector`)
) ENGINE=InnoDB AUTO_INCREMENT=345 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`, `is_admin`, `token`, `remote`) VALUES
(1, '127.0.0.1', 'administrator', '$2y$12$g2P1T2RBeLrG94gJjdF/H.Lu1b40U5YLe6DHQFQ.pW/O24sjrJ68e', 'admin@admin.com', NULL, '', NULL, NULL, NULL, NULL, NULL, 1268889823, 1578482551, 1, 'Admin', 'istrator', 'ADMIN3', '+447487558409', 1, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
CREATE TABLE IF NOT EXISTS `users_groups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_users_groups` (`user_id`,`group_id`),
  KEY `fk_users_groups_users1_idx` (`user_id`),
  KEY `fk_users_groups_groups1_idx` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_groups`
--

INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES
(30, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users_groups_networks`
--

DROP TABLE IF EXISTS `users_groups_networks`;
CREATE TABLE IF NOT EXISTS `users_groups_networks` (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL,
  `installation_key` varchar(100) NOT NULL,
  `network_key` int(15) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `source_id` (`user_id`),
  KEY `group_id` (`group_id`),
  KEY `NetworkKey_FK` (`network_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `network_groups`
--
ALTER TABLE `network_groups`
  ADD CONSTRAINT `NetworkKey_NetworkGroup_FK` FOREIGN KEY (`network_key`) REFERENCES `networks` (`network_key`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `network_requests`
--
ALTER TABLE `network_requests`
  ADD CONSTRAINT `NetworkRequest_NetworkKey_FK` FOREIGN KEY (`network_key`) REFERENCES `networks` (`network_key`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users_groups`
--
ALTER TABLE `users_groups`
  ADD CONSTRAINT `fk_users_groups_groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_users_groups_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `users_groups_networks`
--
ALTER TABLE `users_groups_networks`
  ADD CONSTRAINT `NetworkKey_FK` FOREIGN KEY (`network_key`) REFERENCES `networks` (`network_key`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
