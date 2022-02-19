-- phpMyAdmin SQL Dump
-- version 5.1.1deb3+bionic1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 19, 2022 at 08:09 PM
-- Server version: 5.7.37-0ubuntu0.18.04.1
-- PHP Version: 8.0.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
-- Table structure for table `attributes`
--

CREATE TABLE `attributes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(200) NOT NULL,
  `source_id` int(11) NOT NULL,
  `type` tinyint(3) NOT NULL,
  `min` double NOT NULL,
  `max` double NOT NULL,
  `show_in_interface` bit(1) NOT NULL,
  `include_in_interface_index` bit(1) NOT NULL,
  `storage_location` tinyint(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `attributes_ontology_prefixes_relationships`
--

CREATE TABLE `attributes_ontology_prefixes_relationships` (
  `id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `prefix_id` int(11) NOT NULL,
  `relationship_id` int(11) NOT NULL,
  `ontology_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `attribute_mappings`
--

CREATE TABLE `attribute_mappings` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `attribute_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `eavs`
--

CREATE TABLE `eavs` (
  `id` int(15) NOT NULL,
  `uid` char(36) NOT NULL,
  `source_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `subject_id` varchar(36) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `value_id` int(11) DEFAULT NULL,
  `indexed` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `title` varchar(64) NOT NULL,
  `url` varchar(256) DEFAULT NULL,
  `page_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `networks`
--

CREATE TABLE `networks` (
  `network_key` int(11) NOT NULL,
  `network_name` mediumtext NOT NULL,
  `network_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `network_groups`
--

CREATE TABLE `network_groups` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  `network_key` int(11) NOT NULL,
  `group_type` varchar(50) NOT NULL,
  `url` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `network_groups_sources`
--

CREATE TABLE `network_groups_sources` (
  `id` int(11) UNSIGNED NOT NULL,
  `source_id` int(11) NOT NULL,
  `group_id` int(11) UNSIGNED NOT NULL,
  `installation_key` varchar(32) CHARACTER SET latin1 NOT NULL,
  `network_key` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `network_requests`
--

CREATE TABLE `network_requests` (
  `id` int(10) NOT NULL,
  `network_key` int(11) NOT NULL,
  `installation_key` varchar(100) NOT NULL,
  `url` mediumtext NOT NULL,
  `justification` mediumtext NOT NULL,
  `email` varchar(50) NOT NULL,
  `ip` mediumtext NOT NULL,
  `token` varchar(32) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ontologies`
--

CREATE TABLE `ontologies` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `node_key` varchar(100) NOT NULL,
  `node_type` varchar(100) NOT NULL,
  `key_prefix` varchar(100) DEFAULT NULL,
  `term_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ontologies`
--

INSERT INTO `ontologies` (`id`, `name`, `description`, `node_key`, `node_type`, `key_prefix`, `term_name`) VALUES
(1, 'Human Phenotype Ontology', 'The Human Phenotype Ontology (HPO) is a formal ontology of human phenotypes. Developed in collaboration with members of the Open Biomedical Ontologies Foundry, HPO currently contains over 13,000 terms and over 156,000 annotations to hereditary diseases. Data from Online Mendelian Inheritance in Man and medical literature were used to generate the terms currently in the HPO. The ontology contains over 50,000 annotations between phenotypes and hereditary disease. ', 'hpoid', 'HPOterm', 'HP:', 'term'),
(2, 'ORPHA Net', 'Orphanet is a unique resource, gathering and improving knowledge on rare diseases so as to improve the diagnosis, care and treatment of patients with rare diseases. Orphanet aims to provide high-quality information on rare diseases, and ensure equal access to knowledge for all stakeholders. Orphanet also maintains the Orphanet rare disease nomenclature (ORPHAcode), essential in improving the visibility of rare diseases in health and research information systems.', 'orphaid', 'ORPHAterm', 'ORPHA:', 'term');

-- --------------------------------------------------------

--
-- Table structure for table `ontology_prefixes`
--

CREATE TABLE `ontology_prefixes` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `ontology_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ontology_prefixes`
--

INSERT INTO `ontology_prefixes` (`id`, `name`, `ontology_id`) VALUES
(1, 'hp:', 1),
(2, 'orpha:', 2),
(3, 'ordo:', 2);

-- --------------------------------------------------------

--
-- Table structure for table `ontology_relationships`
--

CREATE TABLE `ontology_relationships` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `ontology_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ontology_relationships`
--

INSERT INTO `ontology_relationships` (`id`, `name`, `ontology_id`) VALUES
(1, 'NOT_PHENOTYPE_OF', 1),
(2, 'PHENOTYPE_OF', 1),
(3, 'PHENOTYPE_OF', 2);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `Title` varchar(50) NOT NULL,
  `Content` mediumtext NOT NULL,
  `Author` int(11) UNSIGNED NOT NULL,
  `Active` bit(1) NOT NULL,
  `Removable` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `Title`, `Content`, `Author`, `Active`, `Removable`) VALUES
(1, 'Home', '<p><span style=\"font-size: 18pt;\">This is a Cafe Variome installation. Cafe Variome is a health data discovery platform. <br /></span></p>\r\n<p><span style=\"font-size: 18pt;\">To find out more about it, visit <a href=\"https://cafevariome.org\">https://cafevariome.org</a></span></p>\r\n<p>&nbsp;</p>\r\n<p><span style=\"font-size: 18pt;\">You can edit the content of this page through the admin dashboard.</span></p>', 1, b'1', b'0'),
(2, 'Contact', '<p>...</p>', 1, b'1', b'0');

-- --------------------------------------------------------

--
-- Table structure for table `pipelines`
--

CREATE TABLE `pipelines` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `subject_id_location` tinyint(3) NOT NULL DEFAULT '0',
  `subject_id_attribute_name` varchar(100) NOT NULL,
  `subject_id_assignment_batch_size` int(11) NOT NULL DEFAULT '1',
  `subject_id_prefix` varchar(16) NOT NULL,
  `grouping` tinyint(4) NOT NULL DEFAULT '0',
  `group_columns` varchar(200) DEFAULT NULL,
  `dateformat` tinyint(4) DEFAULT NULL,
  `internal_delimiter` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `value` varchar(100) NOT NULL,
  `setting_name` varchar(50) DEFAULT NULL,
  `info` mediumtext NOT NULL,
  `setting_group` varchar(50) DEFAULT NULL,
  `validation_rules` varchar(100) NOT NULL DEFAULT 'required|xss_clean'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_id`, `setting_key`, `value`, `setting_name`, `info`, `setting_group`, `validation_rules`) VALUES
(1, 'site_title', 'Cafe Variome', 'Site Title', 'Title as it appears in the web browser and on top left side of all pages.', 'main', 'required'),
(2, 'site_description', 'Cafe Variome - Description', 'Site Description', 'Description of the website that appears as metadata in the structure of public pages.', 'main', 'required'),
(3, 'site_author', 'Bioinformatics Research Group - University of Leicester', 'Site Author', 'Name of the owner of the website, whether a person or an organisation, that appears as metadata on public pages.', 'main', 'required'),
(4, 'site_keywords', 'healthcare data discovery, bioinformatics', 'Keywords', 'Keywords explaining activity of the website that appear as metadata on public pages. They help search engines find this website.', 'main', 'required'),
(5, 'email', 'admin@cafevariome.org', 'Administrator Email', 'Email of the person or group of people responsible for the website.', 'main', 'required'),
(6, 'allow_registrations', 'off', 'Allow User Registration', 'If set to on then users can register on the site, otherwise the signup is hidden', 'authentication', 'required'),
(7, 'discovery_requires_login', 'on', 'Authorization Required for Discovery?', 'If set to on then discovery searches cannot be done unless a user is logged in.', 'discovery', 'required'),
(8, 'show_sources_in_discover', 'on', 'Show Sources in Discovery', 'If set to off then only the search box will be shown in the discovery interface (i.e. not the sources to search)', 'discovery', 'required'),
(9, 'auth_server', '', 'Authorization Server URL', 'Central Cafe Variome Auth server url (WARNING: do not change)', 'main', 'required'),
(10, 'installation_key', '', 'Installation Key', 'Unique key for this installation (WARNING: do not change this value unless you know what you are doing)', 'main', 'required'),
(11, 'logo', 'cafevariome_full.png', 'Main Logo', 'Main logo that appears on top left side of the public pages. The file must be located in resources/images/logos/', 'main', 'required|xss_clean'),
(12, 'oidc_uri', 'https://auth.discoverynexus.org/auth', 'OpenID URL', 'URL (or IP address) of the OpenID provider authentication endpoint.', 'authentication', 'required'),
(13, 'oidc_realm', 'ERN', 'Realm Name', 'Some OpenID providers, like KeyCloak, have a realm that acts as a subspace separating users.', 'authentication', 'required'),
(14, 'oidc_client_id', 'my-client', 'Client ID', 'Client ID of the OpenID provider.', 'authentication', 'required'),
(15, 'oidc_client_secret', '65301ba7-ddfe-4844-a5b4-ddb1e37861ac', 'Client Secret', 'Client secret of the OpenID provider.', 'authentication', 'required'),
(17, 'oidc_port', '80', 'Port', 'If the OpenID provider uses any port other than 80 or 443, please specify the numeric value.', 'authentication', 'required'),
(18, 'elastic_url', 'http://localhost:9200', 'Elasticsearch Address', 'Elastic search address', 'elasticsearch', 'required'),
(19, 'neo4j_username', 'neo4j', 'Neo4J Username', 'Username that is used to communicate with Neo4J REST API.', 'neo4j', 'required'),
(20, 'neo4j_port', '7474', 'Neo4J Port', 'The port that the Neo4J REST API is running on. BY default it is 7474.', 'neo4j', 'required'),
(21, 'neo4j_server', 'http://localhost', 'Neo4J Server', 'The URL of the Neo4J REST API.', 'neo4j', 'required'),
(22, 'neo4j_password', 'neo4j123', 'Neo4J Password', 'Password that is used to communicate with Neo4J REST API.', 'neo4j', 'required'),
(23, 'hpo_autocomplete_url', 'https://www185.lamp.le.ac.uk/EpadGreg/hpo/query/', 'HPO Auto-complete', 'HPO Auto-complete', 'endpoint', 'required'),
(24, 'orpha_autocomplete_url', '', 'ORPHA Auto-complete', 'HPO Auto-complete', 'endpoint', 'required'),
(25, 'snomed_autocomplete_url', '', 'SNOMED Autocomplete ', 'SNOMED Autocomplete ', 'endpoint', 'required');

-- --------------------------------------------------------

--
-- Table structure for table `sources`
--

CREATE TABLE `sources` (
  `source_id` int(11) NOT NULL,
  `uid` varchar(11) NOT NULL,
  `owner_name` mediumtext NOT NULL,
  `email` mediumtext NOT NULL,
  `name` varchar(30) NOT NULL,
  `uri` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `long_description` mediumtext NOT NULL,
  `status` varchar(15) NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `record_count` bigint(255) NOT NULL DEFAULT '0',
  `elastic_status` tinyint(1) DEFAULT '0',
  `elastic_lock` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `uploaddatastatus`
--

CREATE TABLE `uploaddatastatus` (
  `FileName` varchar(100) NOT NULL,
  `uploadStart` datetime NOT NULL,
  `uploadEnd` datetime DEFAULT NULL,
  `Status` varchar(20) NOT NULL,
  `elasticStatus` varchar(20) NOT NULL,
  `source_id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `ID` int(11) UNSIGNED NOT NULL,
  `patient` varchar(50) DEFAULT NULL,
  `tissue` varchar(50) DEFAULT NULL,
  `pipeline_id` int(11) DEFAULT NULL,
  `setting_file` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `upload_jobs`
--

CREATE TABLE `upload_jobs` (
  `ID` int(8) NOT NULL,
  `source_id` int(11) NOT NULL,
  `user_id` mediumint(8) UNSIGNED NOT NULL,
  `linking_id` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
(1, '127.0.0.1', 'admin@cafevariome.org', '$2y$12$g2P1T2RBeLrG94gJjdF/H.Lu1b40U5YLe6DHQFQ.pW/O24sjrJ68e', 'admin@cafevariome.org', NULL, '', NULL, NULL, NULL, NULL, NULL, 1268889823, 1635243567, 1, 'Admin', 'Admin', 'Brookes Lab', '', 1, NULL, 0);
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
(1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users_groups_networks`
--

CREATE TABLE `users_groups_networks` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `group_id` int(11) UNSIGNED NOT NULL,
  `installation_key` varchar(100) NOT NULL,
  `network_key` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `values`
--

CREATE TABLE `values` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `display_name` varchar(200) NOT NULL,
  `frequency` int(11) NOT NULL,
  `show_in_interface` bit(1) NOT NULL,
  `include_in_interface_index` bit(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `value_mappings`
--

CREATE TABLE `value_mappings` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `value_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attributes`
--
ALTER TABLE `attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `SourceIDAttribute_FK` (`source_id`);

--
-- Indexes for table `attributes_ontology_prefixes_relationships`
--
ALTER TABLE `attributes_ontology_prefixes_relationships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Relationship_FK` (`relationship_id`),
  ADD KEY `Prefix_FK` (`prefix_id`),
  ADD KEY `Ontology_Attribute_FK` (`ontology_id`),
  ADD KEY `Attribute_Ontology_FK` (`attribute_id`);

--
-- Indexes for table `attribute_mappings`
--
ALTER TABLE `attribute_mappings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Attribute_Mapping_FK` (`attribute_id`);

--
-- Indexes for table `eavs`
--
ALTER TABLE `eavs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `source_id` (`source_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `attribute_id` (`attribute_id`),
  ADD KEY `value_id` (`value_id`),
  ADD KEY `indexed` (`indexed`),
  ADD KEY `uid_2` (`uid`,`attribute_id`,`value_id`),
  ADD KEY `subj_src` (`subject_id`,`source_id`),
  ADD KEY `comi` (`id`,`source_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Page_FK` (`page_id`);

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
-- Indexes for table `ontologies`
--
ALTER TABLE `ontologies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ontology_prefixes`
--
ALTER TABLE `ontology_prefixes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `OntologyPrefix_FK` (`ontology_id`);

--
-- Indexes for table `ontology_relationships`
--
ALTER TABLE `ontology_relationships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `OntologyRelationhip_FK` (`ontology_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Author_FK` (`Author`);

--
-- Indexes for table `pipelines`
--
ALTER TABLE `pipelines`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `uploaddatastatus`
--
ALTER TABLE `uploaddatastatus`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `Pipeline_Id_FK` (`pipeline_id`);

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
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `values`
--
ALTER TABLE `values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Attribute_FK` (`attribute_id`);

--
-- Indexes for table `value_mappings`
--
ALTER TABLE `value_mappings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Value_Mapping_FK` (`value_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attributes`
--
ALTER TABLE `attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attributes_ontology_prefixes_relationships`
--
ALTER TABLE `attributes_ontology_prefixes_relationships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attribute_mappings`
--
ALTER TABLE `attribute_mappings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eavs`
--
ALTER TABLE `eavs`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `network_groups`
--
ALTER TABLE `network_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `network_groups_sources`
--
ALTER TABLE `network_groups_sources`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `network_requests`
--
ALTER TABLE `network_requests`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ontologies`
--
ALTER TABLE `ontologies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ontology_prefixes`
--
ALTER TABLE `ontology_prefixes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ontology_relationships`
--
ALTER TABLE `ontology_relationships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pipelines`
--
ALTER TABLE `pipelines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `sources`
--
ALTER TABLE `sources`
  MODIFY `source_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uploaddatastatus`
--
ALTER TABLE `uploaddatastatus`
  MODIFY `ID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `upload_error`
--
ALTER TABLE `upload_error`
  MODIFY `ID` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `upload_jobs`
--
ALTER TABLE `upload_jobs`
  MODIFY `ID` int(8) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users_groups`
--
ALTER TABLE `users_groups`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users_groups_networks`
--
ALTER TABLE `users_groups_networks`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `values`
--
ALTER TABLE `values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `value_mappings`
--
ALTER TABLE `value_mappings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attributes`
--
ALTER TABLE `attributes`
  ADD CONSTRAINT `SourceIDAttribute_FK` FOREIGN KEY (`source_id`) REFERENCES `sources` (`source_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attributes_ontology_prefixes_relationships`
--
ALTER TABLE `attributes_ontology_prefixes_relationships`
  ADD CONSTRAINT `Attribute_Ontology_FK` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Ontology_Attribute_FK` FOREIGN KEY (`ontology_id`) REFERENCES `ontologies` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Prefix_FK` FOREIGN KEY (`prefix_id`) REFERENCES `ontology_prefixes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Relationship_FK` FOREIGN KEY (`relationship_id`) REFERENCES `ontology_relationships` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `attribute_mappings`
--
ALTER TABLE `attribute_mappings`
  ADD CONSTRAINT `Attribute_Mapping_FK` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `Page_FK` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `network_groups_sources`
--
ALTER TABLE `network_groups_sources`
  ADD CONSTRAINT `NetworkGroupIDFK` FOREIGN KEY (`group_id`) REFERENCES `network_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `SourceIDFK` FOREIGN KEY (`source_id`) REFERENCES `sources` (`source_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `network_requests`
--
ALTER TABLE `network_requests`
  ADD CONSTRAINT `NetworkRequest_NetworkKey_FK` FOREIGN KEY (`network_key`) REFERENCES `networks` (`network_key`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ontology_prefixes`
--
ALTER TABLE `ontology_prefixes`
  ADD CONSTRAINT `OntologyPrefix_FK` FOREIGN KEY (`ontology_id`) REFERENCES `ontologies` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `ontology_relationships`
--
ALTER TABLE `ontology_relationships`
  ADD CONSTRAINT `OntologyRelationhip_FK` FOREIGN KEY (`ontology_id`) REFERENCES `ontologies` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `Author_FK` FOREIGN KEY (`Author`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploaddatastatus`
--
ALTER TABLE `uploaddatastatus`
  ADD CONSTRAINT `Pipeline_Id_FK` FOREIGN KEY (`pipeline_id`) REFERENCES `pipelines` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
  ADD CONSTRAINT `GroupIDFK` FOREIGN KEY (`group_id`) REFERENCES `network_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `UserFK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `values`
--
ALTER TABLE `values`
  ADD CONSTRAINT `Attribute_FK` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `value_mappings`
--
ALTER TABLE `value_mappings`
  ADD CONSTRAINT `Value_Mapping_FK` FOREIGN KEY (`value_id`) REFERENCES `values` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
