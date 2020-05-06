-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 25, 2020 at 10:07 PM
-- Server version: 5.7.29-0ubuntu0.18.04.1
-- PHP Version: 7.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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

CREATE TABLE `eavs` (
  `id` int(15) NOT NULL,
  `uid` char(32) NOT NULL,
  `source_id` int(11) NOT NULL,
  `fileName` mediumint(8) UNSIGNED NOT NULL,
  `subject_id` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `attribute` varchar(50) NOT NULL,
  `value` varchar(200) DEFAULT NULL,
  `elastic` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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

CREATE TABLE `local_phenotypes_lookup` (
  `lookup_id` int(11) NOT NULL,
  `network_key` varchar(32) NOT NULL,
  `phenotype_attribute` varchar(128) NOT NULL,
  `phenotype_values` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) UNSIGNED NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `MenuItems`
--

CREATE TABLE `MenuItems` (
  `id` int(11) NOT NULL,
  `Position` int(11) NOT NULL,
  `Title` varchar(64) NOT NULL,
  `Url` varchar(256) DEFAULT NULL,
  `Page_Id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `networks`
--

CREATE TABLE `networks` (
  `network_key` int(11) NOT NULL,
  `network_name` text NOT NULL,
  `network_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `network_groups`
--

CREATE TABLE `network_groups` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  `network_key` int(11) NOT NULL,
  `group_type` varchar(50) NOT NULL,
  `url` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `network_groups_sources`
--

CREATE TABLE `network_groups_sources` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `source_id` mediumint(8) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL,
  `installation_key` varchar(100) NOT NULL,
  `network_key` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `network_requests`
--

CREATE TABLE `network_requests` (
  `id` int(10) NOT NULL,
  `network_key` int(11) NOT NULL,
  `installation_key` varchar(100) NOT NULL,
  `url` text NOT NULL,
  `justification` text NOT NULL,
  `email` varchar(50) NOT NULL,
  `ip` text NOT NULL,
  `token` varchar(32) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Pages`
--

CREATE TABLE `Pages` (
  `id` int(11) NOT NULL,
  `Title` varchar(50) NOT NULL,
  `Content` text NOT NULL,
  `Author` int(11) UNSIGNED NOT NULL,
  `Active` bit(1) NOT NULL,
  `Removable` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Pages`
--

INSERT INTO `Pages` (`id`, `Title`, `Content`, `Author`, `Active`, `Removable`) VALUES
(1, 'Home', '<div class=\"&quot;row\">\r\n<div class=\"col\">\r\n<table style=\"border-collapse: collapse; width: 100%;\" border=\"0\">\r\n<tbody>\r\n<tr>\r\n<td style=\"width: 50%;\">\r\n<h3>What is Cafe Variome?</h3>\r\n<p>Cafe Variome is a flexible web-based, data discovery tool that can be quickly installed by any biomedical data owner to enable the &ldquo;existence&rdquo; rather than the &ldquo;substance&rdquo; of the data to be discovered.</p>\r\n<h3>What data is Cafe Variome designed for?</h3>\r\n<p>Cafe Variome has been designed for use with all sensitive biomedical data, whether this be genomic variants or cohort data.</p>\r\n<p>For full details please look at the <a href=\"https://www.cafevariome.org/about#Data\"> data section</a>.</p>\r\n<h3>Who is cafe Variome designed for?</h3>\r\n<p>Cafe Variome is designed for owners of sensitive biomedical data who would like to make their data discoverable but don\'t want to risk exposing the content to the outside world. This is not limited to individual institutions, federated Cafe Variome networks can be setup by consortia.</p>\r\n</td>\r\n<td style=\"width: 50%;\">\r\n<h3>Want to explore a Cafe Variome installation to learn more?</h3>\r\n<a href=\"https://central.cafevariome.org\" target=\"_blank\" rel=\"noopener\"><img class=\"img-responsive center-block\" style=\"max-width: 300px;\" title=\"Cafe Variome Central\" src=\"https://www.cafevariome.org/assets/images/CVLogos/cvc_transparent.png\" alt=\"Cafe Variome Central\" data-toggle=\"tooltip\" /></a>\r\n<h3>Take a look through <a href=\"https://central.cafevariome.org\" target=\"_blank\" rel=\"noopener\"> Cafe Variome Central</a>, our installation created from publicly available datasets.</h3>\r\n</td>\r\n</tr>\r\n<tr>\r\n<td style=\"width: 50%;\">&nbsp;</td>\r\n<td style=\"width: 50%;\">&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</div>\r\n</div>', 1, b'1', b'0'),
(2, 'Contact', '', 1, b'1', b'0');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_id` int(10) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `value` varchar(100) NOT NULL,
  `setting_name` varchar(50) DEFAULT NULL,
  `info` text NOT NULL,
  `validation_rules` varchar(100) NOT NULL DEFAULT 'required|xss_clean'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `value`, `setting_name`, `info`, `validation_rules`) VALUES
(1, 'site_title', 'Cafe Variome 2', 'Site Title', '', 'required'),
(2, 'site_description', 'Cafe Variome - Description', 'Site Description', '', 'required'),
(3, 'site_author', 'Bioinformatics Research Group - University of Leicester', 'Site Author', '', 'required'),
(4, 'site_keywords', 'healthcare data discovery, bioinformatics', 'Keywords', '', 'required'),
(5, 'email', 'admin@cafevariome.org', 'Administrator Email', '', 'required'),
(23, 'allow_registrations', 'on', 'Allow User Registration', 'If set to on then users can register on the site, otherwise the signup is hidden', 'required'),
(29, 'discovery_requires_login', 'off', 'Authorization Required for Discovery?', 'If set to on then discovery searches cannot be done unless a user is logged in.', 'required'),
(30, 'show_sources_in_discover', 'on', 'Show Sources in Discovery', 'If set to off then only the search box will be shown in the discovery interface (i.e. not the sources to search)', 'required'),
(32, 'auth_server', ' ', 'Authorization Server URL', 'Central Cafe Variome Auth server url (WARNING: do not change)', 'required'),
(33, 'installation_key', ' ', 'Installation Key', 'Unique key for this installation (WARNING: do not change this value unless you know what you are doing)', 'required'),
(65, 'logo', 'cafevariome_full.png', 'Main Logo', 'Main Logo', 'required|xss_clean'),
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
(64, 'neo4j_password', 'neo4j123', 'Neo4J Password', '', 'required');

-- --------------------------------------------------------

--
-- Table structure for table `sources`
--

CREATE TABLE `sources` (
  `source_id` int(11) NOT NULL,
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
  `neo4j_data` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sources_groups`
--

CREATE TABLE `sources_groups` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `source_id` mediumint(8) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `uploaddatastatus`
--

CREATE TABLE `uploaddatastatus` (
  `FileName` varchar(40) NOT NULL,
  `uploadStart` datetime NOT NULL,
  `uploadEnd` datetime DEFAULT NULL,
  `Status` varchar(20) NOT NULL,
  `elasticStatus` varchar(20) NOT NULL,
  `source_id` int(11) NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `ID` mediumint(8) UNSIGNED NOT NULL,
  `patient` varchar(50) DEFAULT NULL,
  `tissue` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `upload_error`
--

CREATE TABLE `upload_error` (
  `ID` mediumint(8) UNSIGNED NOT NULL,
  `error_id` mediumint(8) UNSIGNED NOT NULL,
  `message` varchar(500) NOT NULL,
  `error_code` int(5) NOT NULL,
  `source_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `upload_jobs`
--

CREATE TABLE `upload_jobs` (
  `ID` int(8) NOT NULL,
  `source_id` int(11) NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `linking_id` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
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
  `remote` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `ip_address`, `username`, `password`, `email`, `activation_selector`, `activation_code`, `forgotten_password_selector`, `forgotten_password_code`, `forgotten_password_time`, `remember_selector`, `remember_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`, `is_admin`, `token`, `remote`) VALUES
(1, '127.0.0.1', 'administrator', '$2y$12$g2P1T2RBeLrG94gJjdF/H.Lu1b40U5YLe6DHQFQ.pW/O24sjrJ68e', 'admin@cafevariome.org', NULL, '', NULL, NULL, NULL, NULL, NULL, 1268889823, 1587848114, 1, 'Admin', 'Admin', 'Brookes Lab', '07000000000', 1, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

CREATE TABLE `users_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users_groups`
--

INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES
(36, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users_groups_networks`
--

CREATE TABLE `users_groups_networks` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL,
  `installation_key` varchar(100) NOT NULL,
  `network_key` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `eavs`
--
ALTER TABLE `eavs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `source_id` (`source_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `attribute` (`attribute`),
  ADD KEY `value` (`value`),
  ADD KEY `elastic` (`elastic`),
  ADD KEY `uid_2` (`uid`,`attribute`,`value`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `local_phenotypes_lookup`
--
ALTER TABLE `local_phenotypes_lookup`
  ADD PRIMARY KEY (`lookup_id`),
  ADD KEY `network_key` (`network_key`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `MenuItems`
--
ALTER TABLE `MenuItems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Page_FK` (`Page_Id`);

--
-- Indexes for table `networks`
--
ALTER TABLE `networks`
  ADD PRIMARY KEY (`network_key`);

--
-- Indexes for table `network_groups`
--
ALTER TABLE `network_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `network_key` (`network_key`),
  ADD KEY `type` (`group_type`);

--
-- Indexes for table `network_groups_sources`
--
ALTER TABLE `network_groups_sources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `source_id` (`source_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `network_requests`
--
ALTER TABLE `network_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `NetworkRequest_NetworkKey_FK` (`network_key`);

--
-- Indexes for table `Pages`
--
ALTER TABLE `Pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Author_FK` (`Author`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `sources`
--
ALTER TABLE `sources`
  ADD PRIMARY KEY (`source_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `status` (`status`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `sources_groups`
--
ALTER TABLE `sources_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `source_id` (`source_id`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `uploaddatastatus`
--
ALTER TABLE `uploaddatastatus`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `upload_error`
--
ALTER TABLE `upload_error`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `upload_jobs`
--
ALTER TABLE `upload_jobs`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_email` (`email`),
  ADD UNIQUE KEY `uc_activation_selector` (`activation_selector`),
  ADD UNIQUE KEY `uc_forgotten_password_selector` (`forgotten_password_selector`),
  ADD UNIQUE KEY `uc_remember_selector` (`remember_selector`);

--
-- Indexes for table `users_groups`
--
ALTER TABLE `users_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_users_groups` (`user_id`,`group_id`),
  ADD KEY `fk_users_groups_users1_idx` (`user_id`),
  ADD KEY `fk_users_groups_groups1_idx` (`group_id`);

--
-- Indexes for table `users_groups_networks`
--
ALTER TABLE `users_groups_networks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `source_id` (`user_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `NetworkKey_FK` (`network_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `eavs`
--
ALTER TABLE `eavs`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2889;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `local_phenotypes_lookup`
--
ALTER TABLE `local_phenotypes_lookup`
  MODIFY `lookup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4299;
--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `MenuItems`
--
ALTER TABLE `MenuItems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `network_groups`
--
ALTER TABLE `network_groups`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `network_groups_sources`
--
ALTER TABLE `network_groups_sources`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
--
-- AUTO_INCREMENT for table `network_requests`
--
ALTER TABLE `network_requests`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `Pages`
--
ALTER TABLE `Pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;
--
-- AUTO_INCREMENT for table `sources`
--
ALTER TABLE `sources`
  MODIFY `source_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `sources_groups`
--
ALTER TABLE `sources_groups`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `uploaddatastatus`
--
ALTER TABLE `uploaddatastatus`
  MODIFY `ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `upload_error`
--
ALTER TABLE `upload_error`
  MODIFY `ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `upload_jobs`
--
ALTER TABLE `upload_jobs`
  MODIFY `ID` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `users_groups`
--
ALTER TABLE `users_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
--
-- AUTO_INCREMENT for table `users_groups_networks`
--
ALTER TABLE `users_groups_networks`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `MenuItems`
--
ALTER TABLE `MenuItems`
  ADD CONSTRAINT `Page_FK` FOREIGN KEY (`Page_Id`) REFERENCES `Pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `Pages`
--
ALTER TABLE `Pages`
  ADD CONSTRAINT `Author_FK` FOREIGN KEY (`Author`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
