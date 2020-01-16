-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 16, 2020 at 11:05 AM
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
(53, 'key_cloak_client_secret', ' ', 'Keycloak Client Secret', '', 'required'),
(54, 'key_cloak_login_uri', 'http://localhost/cvci4/auth/klogin', 'Keycloak Login Address', '', 'required'),
(55, 'key_cloak_logout_uri', 'http://localhost/cvci4/auth/logout', 'Keycloak Logout Address', '', 'required'),
(56, 'key_cloak_port', '8080', 'Keycloak Port', '', 'required'),
(57, 'elastic_url', 'http://localhost:9200', 'Elasticsearch Address', 'Elastic search address', 'required'),
(58, 'key_cloak_admin_id', 'f02288f5-1c48-4be0-9868-179028a77f8a', 'Keycloak Admin Id', 'Admin user id within key cloak server', 'required'),
(59, 'key_cloak_admin_username', ' ', 'Keycloak Admin Username', 'Admin user username within key cloak server', 'required'),
(60, 'key_cloak_admin_password', ' ', 'Keycloak Admin Password', 'Admin user password within key cloak server.', 'required'),
(63, 'neo4j_username', 'neo4j', 'Neo4J Username', '', 'required'),
(62, 'neo4j_port', '7474', 'Neo4J Port', '', 'required'),
(61, 'neo4j_server', 'http://localhost', 'Neo4J Server', '', 'required'),
(64, 'neo4j_password', ' ', 'Neo4J Password', '', 'required');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
