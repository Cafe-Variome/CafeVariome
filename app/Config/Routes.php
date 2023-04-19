<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->get('admin', 'Admin::Index');
$routes->get('Admin', 'Admin::Index');
$routes->get('admin/index', 'Admin::Index');
$routes->get('Admin/index', 'Admin::Index');
$routes->get('Admin/Index', 'Admin::Index');
$routes->get('admin/Index', 'Admin::Index');

$routes->post('AjaxApi/Query', 'AjaxApi::Query');
$routes->post('AjaxApi/getOntologyPrefixesAndRelationships', 'AjaxApi::getOntologyPrefixesAndRelationships');
$routes->post('AjaxApi/getSourceCounts', 'AjaxApi::getSourceCounts');
$routes->post('AjaxApi/LookupDirectory', 'AjaxApi::LookupDirectory');
$routes->post('AjaxApi/ImportFromDirectory', 'AjaxApi::ImportFromDirectory');
$routes->post('AjaxApi/ProcessFile', 'AjaxApi::ProcessFile');
$routes->post('AjaxApi/ProcessFiles', 'AjaxApi::ProcessFiles');
$routes->post('AjaxApi/GetPhenotypeAttributes/(:num)', 'AjaxApi::GetPhenotypeAttributes/$1');
$routes->post('AjaxApi', 'AjaxApi::');

$routes->get('Attribute', 'Attribute::Index');
$routes->get('Attribute/Index', 'Attribute::Index');
$routes->get('Attribute/List/(:num)', 'Attribute::List/$1');
$routes->get('Attribute/Details/(:num)', 'Attribute::Details/$1');
$routes->get('Attribute/Update/(:num)', 'Attribute::Update/$1');
$routes->post('Attribute/Update/(:num)', 'Attribute::Update/$1');
$routes->get('Attribute/OntologyAssociations/(:num)', 'Attribute::OntologyAssociations/$1');
$routes->post('Attribute/OntologyAssociations/(:num)', 'Attribute::OntologyAssociations/$1');
$routes->get('Attribute/DeleteAssociation/(:num)', 'Attribute::DeleteAssociation/$1');
$routes->post('Attribute/DeleteAssociation/(:num)', 'Attribute::DeleteAssociation/$1');

$routes->get('AttributeMapping', 'AttributeMapping::Index');
$routes->get('AttributeMapping/Index', 'AttributeMapping::Index');
$routes->get('AttributeMapping/List/(:num)', 'AttributeMapping::List/$1');
$routes->get('AttributeMapping/Create/(:num)', 'AttributeMapping::Create/$1');
$routes->post('AttributeMapping/Create/(:num)', 'AttributeMapping::Create/$1');
$routes->get('AttributeMapping/Delete/(:num)', 'AttributeMapping::Delete/$1');
$routes->post('AttributeMapping/Delete/(:num)', 'AttributeMapping::Delete/$1');

$routes->get('Auth', 'Auth::');
$routes->get('Auth/Index', 'Auth::Index');
$routes->get('Auth/Login', 'Auth::Login');
$routes->post('Auth/Login', 'Auth::Login');
$routes->get('Auth/Logout', 'Auth::Logout');

$routes->get('BeaconAPI', 'BeaconAPI::Index');
$routes->get('BeaconAPI/(:num)/info', 'BeaconAPI::info');
$routes->get('BeaconAPI/(:num)/service_info', 'BeaconAPI::service_info');
$routes->get('BeaconAPI/(:num)/service-info', 'BeaconAPI::service_info');
$routes->get('BeaconAPI/(:num)/configuration', 'BeaconAPI::configuration');
$routes->get('BeaconAPI/(:num)/entry_types', 'BeaconAPI::entry_types');
$routes->get('BeaconAPI/(:num)/map', 'BeaconAPI::map');
$routes->get('BeaconAPI/(:num)/filtering_terms', 'BeaconAPI::filtering_terms');
$routes->get('BeaconAPI/(:num)/individuals', 'BeaconAPI::individuals/$1');
$routes->post('BeaconAPI/(:num)/individuals', 'BeaconAPI::individuals/$1');

$routes->get('ContentAPI/hpoQuery', 'ContentAPI::hpoQuery');
$routes->get('ContentAPI/buildHPOTree', 'ContentAPI::buildHPOTree');
$routes->get('ContentAPI/loadOrpha', 'ContentAPI::loadOrpha');
$routes->get('ContentAPI/SingleSignOnIcon/(:num)', 'ContentAPI::SingleSignOnIcon/$1');

$routes->get('Credential', 'Credential::Index');
$routes->get('Credential/Index', 'Credential::Index');
$routes->get('Credential/Create', 'Credential::Create');
$routes->post('Credential/Create', 'Credential::Create');
$routes->get('Credential/Delete/(:num)', 'Credential::Delete/$1');
$routes->post('Credential/Delete/(:num)', 'Credential::Delete/$1');
$routes->get('Credential/Details/(:num)', 'Credential::Details/$1');
$routes->get('Credential/List', 'Credential::List');
$routes->get('Credential/Update/(:num)', 'Credential::Update/$1');
$routes->post('Credential/Update/(:num)', 'Credential::Update/$1');

$routes->get('DataFile', 'DataFile::Index');
$routes->get('DataFile/Index', 'DataFile::Index');
$routes->get('DataFile/Delete/(:num)', 'DataFile::Delete/$1');
$routes->post('DataFile/Delete/(:num)', 'DataFile::Delete/$1');
$routes->get('DataFile/DeleteRecords/(:num)', 'DataFile::DeleteRecords/$1');
$routes->post('DataFile/DeleteRecords/(:num)', 'DataFile::DeleteRecords/$1');
$routes->get('DataFile/Import/(:num)', 'DataFile::Import/$1');
$routes->get('DataFile/List/(:num)', 'DataFile::List/$1');
$routes->get('DataFile/Tasks/(:num)', 'DataFile::Tasks/$1');
$routes->get('DataFile/Upload/(:num)', 'DataFile::Upload/$1');
$routes->post('DataFile/Upload/(:num)', 'DataFile::Upload/$1');

$routes->get('Discover', 'Discover::Index');
$routes->get('Discover/Index', 'Discover::Index');
$routes->get('Discover/SelectNetwork', 'Discover::SelectNetwork');
$routes->get('Discover/QueryBuilder/(:num)', 'Discover::QueryBuilder/$1');

$routes->get('Home/Index', 'Home::index');


$routes->get('Source', 'Source::Index');
$routes->get('Source/Index', 'Source::Index');
$routes->get('Source/List', 'Source::List');
//$routes->get('Source/', 'Source::');
//
//
//$routes->get('', '');

$routes->get('UserInterfaceAPI/GetUIConstants', 'UserInterfaceAPI::GetUIConstants');



/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
