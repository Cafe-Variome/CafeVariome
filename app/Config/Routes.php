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
$routes->get('/', 'Home::Index');

/**
 * Cafe Variome Manual Routes
 * @author Mehdi Mehtarizadeh
 * Please do not change the routes below unless new methods/controllers are created.
 */

$routes->get('admin', 'Admin::Index');
$routes->get('Admin', 'Admin::Index');
$routes->get('admin/index', 'Admin::Index');
$routes->get('Admin/index', 'Admin::Index');
$routes->get('Admin/Index', 'Admin::Index');
$routes->get('admin/Index', 'Admin::Index');

$routes->post('AjaxApi/Query', 'AjaxApi::Query');
$routes->post('AjaxApi/query', 'AjaxApi::Query');
$routes->post('ajaxApi/query', 'AjaxApi::Query');
$routes->post('ajaxApi/Query', 'AjaxApi::Query');

$routes->post('AjaxApi/getOntologyPrefixesAndRelationships', 'AjaxApi::getOntologyPrefixesAndRelationships');
$routes->post('AjaxApi/getSourceCounts', 'AjaxApi::getSourceCounts');
$routes->post('AjaxApi/LookupDirectory', 'AjaxApi::LookupDirectory');
$routes->post('AjaxApi/ImportFromDirectory', 'AjaxApi::ImportFromDirectory');
$routes->post('AjaxApi/ProcessFile', 'AjaxApi::ProcessFile');
$routes->post('AjaxApi/ProcessFiles', 'AjaxApi::ProcessFiles');
$routes->post('AjaxApi/GetPhenotypeAttributes/(:num)', 'AjaxApi::GetPhenotypeAttributes/$1');
$routes->post('AjaxApi/CreateUserInterfaceIndex', 'AjaxApi::CreateUserInterfaceIndex');
$routes->post('AjaxApi/CountUploadedAndImportedFiles', 'AjaxApi::CountUploadedAndImportedFiles');
$routes->post('AjaxApi/IndexDataToElasticsearch', 'AjaxApi::IndexDataToElasticsearch');
$routes->post('AjaxApi/IndexDataToNeo4J', 'AjaxApi::IndexDataToNeo4J');
$routes->post('AjaxApi/StartService', 'AjaxApi::StartService');
$routes->post('AjaxApi/ShutdownService', 'AjaxApi::ShutdownService');

$routes->get('Attribute', 'Attribute::Index');
$routes->get('attribute', 'Attribute::Index');

$routes->get('Attribute/Index', 'Attribute::Index');
$routes->get('Attribute/index', 'Attribute::Index');
$routes->get('attribute/Index', 'Attribute::Index');
$routes->get('attribute/index', 'Attribute::Index');

$routes->get('Attribute/List/(:num)', 'Attribute::List/$1');
$routes->get('Attribute/list/(:num)', 'Attribute::List/$1');
$routes->get('attribute/List/(:num)', 'Attribute::List/$1');
$routes->get('attribute/list/(:num)', 'Attribute::List/$1');

$routes->get('Attribute/Details/(:num)', 'Attribute::Details/$1');
$routes->get('Attribute/details/(:num)', 'Attribute::Details/$1');
$routes->get('attribute/Details/(:num)', 'Attribute::Details/$1');
$routes->get('attribute/details/(:num)', 'Attribute::Details/$1');

$routes->get('Attribute/Update/(:num)', 'Attribute::Update/$1');
$routes->get('attribute/Update/(:num)', 'Attribute::Update/$1');
$routes->get('attribute/update/(:num)', 'Attribute::Update/$1');
$routes->get('Attribute/update/(:num)', 'Attribute::Update/$1');

$routes->post('Attribute/Update/(:num)', 'Attribute::Update/$1');
$routes->post('Attribute/update/(:num)', 'Attribute::Update/$1');
$routes->post('attribute/Update/(:num)', 'Attribute::Update/$1');
$routes->post('attribute/update/(:num)', 'Attribute::Update/$1');

$routes->get('Attribute/OntologyAssociations/(:num)', 'Attribute::OntologyAssociations/$1');
$routes->get('Attribute/ontologyassociations/(:num)', 'Attribute::OntologyAssociations/$1');
$routes->get('attribute/OntologyAssociations/(:num)', 'Attribute::OntologyAssociations/$1');
$routes->get('attribute/ontologyassociations/(:num)', 'Attribute::OntologyAssociations/$1');

$routes->post('Attribute/OntologyAssociations/(:num)', 'Attribute::OntologyAssociations/$1');
$routes->post('Attribute/ontologyassociations/(:num)', 'Attribute::OntologyAssociations/$1');
$routes->post('attribute/OntologyAssociations/(:num)', 'Attribute::OntologyAssociations/$1');
$routes->post('attribute/ontologyassociations/(:num)', 'Attribute::OntologyAssociations/$1');

$routes->get('Attribute/DeleteAssociation/(:num)', 'Attribute::DeleteAssociation/$1');
$routes->get('Attribute/deleteassociation/(:num)', 'Attribute::DeleteAssociation/$1');
$routes->get('attribute/DeleteAssociation/(:num)', 'Attribute::DeleteAssociation/$1');
$routes->get('attribute/deleteassociation/(:num)', 'Attribute::DeleteAssociation/$1');

$routes->post('Attribute/DeleteAssociation/(:num)', 'Attribute::DeleteAssociation/$1');
$routes->post('Attribute/deleteassociation/(:num)', 'Attribute::DeleteAssociation/$1');
$routes->post('attribute/DeleteAssociation/(:num)', 'Attribute::DeleteAssociation/$1');
$routes->post('attribute/deleteassociation/(:num)', 'Attribute::DeleteAssociation/$1');

$routes->get('AttributeMapping', 'AttributeMapping::Index');
$routes->get('attributemapping', 'AttributeMapping::Index');

$routes->get('AttributeMapping/Index', 'AttributeMapping::Index');
$routes->get('attributemapping/index', 'AttributeMapping::Index');
$routes->get('attributemapping/Index', 'AttributeMapping::Index');
$routes->get('attributemapping/Index', 'AttributeMapping::Index');

$routes->get('AttributeMapping/List/(:num)', 'AttributeMapping::List/$1');
$routes->get('attributemapping/list/(:num)', 'AttributeMapping::List/$1');
$routes->get('attributemapping/List/(:num)', 'AttributeMapping::List/$1');
$routes->get('attributemapping/list/(:num)', 'AttributeMapping::List/$1');

$routes->get('AttributeMapping/Create/(:num)', 'AttributeMapping::Create/$1');
$routes->get('attributemapping/create/(:num)', 'AttributeMapping::Create/$1');
$routes->get('attributemapping/Create/(:num)', 'AttributeMapping::Create/$1');
$routes->get('attributemapping/create/(:num)', 'AttributeMapping::Create/$1');

$routes->post('AttributeMapping/Create/(:num)', 'AttributeMapping::Create/$1');
$routes->post('attributemapping/create/(:num)', 'AttributeMapping::Create/$1');
$routes->post('attributemapping/Create/(:num)', 'AttributeMapping::Create/$1');
$routes->post('attributemapping/create/(:num)', 'AttributeMapping::Create/$1');

$routes->get('AttributeMapping/Delete/(:num)', 'AttributeMapping::Delete/$1');
$routes->get('attributemapping/delete/(:num)', 'AttributeMapping::Delete/$1');
$routes->get('attributemapping/Delete/(:num)', 'AttributeMapping::Delete/$1');
$routes->get('attributemapping/delete/(:num)', 'AttributeMapping::Delete/$1');

$routes->post('AttributeMapping/Delete/(:num)', 'AttributeMapping::Delete/$1');
$routes->post('attributemapping/delete/(:num)', 'AttributeMapping::Delete/$1');
$routes->post('attributemapping/Delete/(:num)', 'AttributeMapping::Delete/$1');
$routes->post('attributemapping/delete/(:num)', 'AttributeMapping::Delete/$1');

$routes->get('Auth', 'Auth::Index');
$routes->get('Auth/Index', 'Auth::Index');
$routes->get('Auth/Login', 'Auth::Login');
$routes->get('auth/login', 'Auth::Login');
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
$routes->get('contentapi/hpoquery', 'ContentAPI::hpoQuery');

$routes->get('ContentAPI/buildHPOTree', 'ContentAPI::buildHPOTree');
$routes->get('contentapi/buildhpotree', 'ContentAPI::buildHPOTree');

$routes->get('ContentAPI/loadOrpha', 'ContentAPI::loadOrpha');
$routes->get('contentapi/loadorpha', 'ContentAPI::loadOrpha');

$routes->get('ContentAPI/SingleSignOnIcon/(:num)', 'ContentAPI::SingleSignOnIcon/$1');
$routes->get('contentapi/singlesignonicon/(:num)', 'ContentAPI::SingleSignOnIcon/$1');

$routes->get('Credential', 'Credential::Index');
$routes->get('credential', 'Credential::Index');

$routes->get('Credential/Index', 'Credential::Index');
$routes->get('Credential/index', 'Credential::Index');
$routes->get('credential/index', 'Credential::Index');
$routes->get('credential/Index', 'Credential::Index');

$routes->get('Credential/Create', 'Credential::Create');
$routes->get('Credential/create', 'Credential::Create');
$routes->get('credential/create', 'Credential::Create');
$routes->get('credential/Create', 'Credential::Create');

$routes->post('Credential/Create', 'Credential::Create');
$routes->post('Credential/create', 'Credential::Create');
$routes->post('credential/create', 'Credential::Create');
$routes->post('credential/Create', 'Credential::Create');

$routes->get('Credential/Delete/(:num)', 'Credential::Delete/$1');
$routes->get('Credential/delete/(:num)', 'Credential::Delete/$1');
$routes->get('credential/delete/(:num)', 'Credential::Delete/$1');
$routes->get('credential/Delete/(:num)', 'Credential::Delete/$1');

$routes->post('Credential/Delete/(:num)', 'Credential::Delete/$1');
$routes->post('Credential/delete/(:num)', 'Credential::Delete/$1');
$routes->post('credential/delete/(:num)', 'Credential::Delete/$1');
$routes->post('credential/Delete/(:num)', 'Credential::Delete/$1');

$routes->get('Credential/Details/(:num)', 'Credential::Details/$1');
$routes->get('Credential/details/(:num)', 'Credential::Details/$1');
$routes->get('credential/details/(:num)', 'Credential::Details/$1');
$routes->get('credential/Details/(:num)', 'Credential::Details/$1');

$routes->get('Credential/List', 'Credential::List');
$routes->get('Credential/list', 'Credential::List');
$routes->get('credential/list', 'Credential::List');
$routes->get('credential/List', 'Credential::List');

$routes->get('Credential/Update/(:num)', 'Credential::Update/$1');
$routes->get('Credential/update/(:num)', 'Credential::Update/$1');
$routes->get('credential/update/(:num)', 'Credential::Update/$1');
$routes->get('credential/Update/(:num)', 'Credential::Update/$1');

$routes->post('Credential/Update/(:num)', 'Credential::Update/$1');
$routes->post('Credential/update/(:num)', 'Credential::Update/$1');
$routes->post('credential/update/(:num)', 'Credential::Update/$1');
$routes->post('credential/Update/(:num)', 'Credential::Update/$1');

$routes->get('DataFile', 'DataFile::Index');
$routes->get('datafile', 'DataFile::Index');

$routes->get('DataFile/Index', 'DataFile::Index');
$routes->get('DataFile/index', 'DataFile::Index');
$routes->get('datafile/index', 'DataFile::Index');
$routes->get('datafile/Index', 'DataFile::Index');

$routes->get('DataFile/Delete/(:num)', 'DataFile::Delete/$1');
$routes->get('DataFile/delete/(:num)', 'DataFile::Delete/$1');
$routes->get('datafile/delete/(:num)', 'DataFile::Delete/$1');
$routes->get('datafile/Delete/(:num)', 'DataFile::Delete/$1');

$routes->post('DataFile/Delete/(:num)', 'DataFile::Delete/$1');
$routes->post('DataFile/delete/(:num)', 'DataFile::Delete/$1');
$routes->post('datafile/delete/(:num)', 'DataFile::Delete/$1');
$routes->post('datafile/Delete/(:num)', 'DataFile::Delete/$1');

$routes->get('DataFile/DeleteRecords/(:num)', 'DataFile::DeleteRecords/$1');
$routes->get('DataFile/deleteRecords/(:num)', 'DataFile::DeleteRecords/$1');
$routes->get('datafile/deleteRecords/(:num)', 'DataFile::DeleteRecords/$1');
$routes->get('datafile/DeleteRecords/(:num)', 'DataFile::DeleteRecords/$1');

$routes->post('DataFile/DeleteRecords/(:num)', 'DataFile::DeleteRecords/$1');
$routes->post('DataFile/deleteRecords/(:num)', 'DataFile::DeleteRecords/$1');
$routes->post('datafile/deleteRecords/(:num)', 'DataFile::DeleteRecords/$1');
$routes->post('datafile/DeleteRecords/(:num)', 'DataFile::DeleteRecords/$1');

$routes->get('DataFile/Import/(:num)', 'DataFile::Import/$1');
$routes->get('DataFile/import/(:num)', 'DataFile::Import/$1');
$routes->get('datafile/import/(:num)', 'DataFile::Import/$1');
$routes->get('datafile/Import/(:num)', 'DataFile::Import/$1');

$routes->get('DataFile/List/(:num)', 'DataFile::List/$1');
$routes->get('DataFile/list/(:num)', 'DataFile::List/$1');
$routes->get('datafile/list/(:num)', 'DataFile::List/$1');
$routes->get('datafile/List/(:num)', 'DataFile::List/$1');

$routes->get('DataFile/Tasks/(:num)', 'DataFile::Tasks/$1');
$routes->get('DataFile/tasks/(:num)', 'DataFile::Tasks/$1');
$routes->get('datafile/tasks/(:num)', 'DataFile::Tasks/$1');
$routes->get('datafile/Tasks/(:num)', 'DataFile::Tasks/$1');

$routes->get('DataFile/Upload/(:num)', 'DataFile::Upload/$1');
$routes->get('DataFile/upload/(:num)', 'DataFile::Upload/$1');
$routes->get('datafile/upload/(:num)', 'DataFile::Upload/$1');
$routes->get('datafile/Upload/(:num)', 'DataFile::Upload/$1');

$routes->post('DataFile/Upload/(:num)', 'DataFile::Upload/$1');
$routes->post('DataFile/upload/(:num)', 'DataFile::Upload/$1');
$routes->post('datafile/upload/(:num)', 'DataFile::Upload/$1');
$routes->post('datafile/Upload/(:num)', 'DataFile::Upload/$1');

$routes->get('Discover', 'Discover::Index');
$routes->get('discover', 'Discover::Index');

$routes->get('Discover/Index', 'Discover::Index');
$routes->get('Discover/index', 'Discover::Index');
$routes->get('discover/index', 'Discover::Index');
$routes->get('discover/Index', 'Discover::Index');

$routes->get('Discover/SelectNetwork', 'Discover::SelectNetwork');
$routes->get('Discover/selectNetwork', 'Discover::SelectNetwork');
$routes->get('discover/selectNetwork', 'Discover::SelectNetwork');
$routes->get('discover/SelectNetwork', 'Discover::SelectNetwork');

$routes->get('Discover/QueryBuilder/(:num)', 'Discover::QueryBuilder/$1');
$routes->get('Discover/queryBuilder/(:num)', 'Discover::QueryBuilder/$1');
$routes->get('discover/queryBuilder/(:num)', 'Discover::QueryBuilder/$1');
$routes->get('discover/QueryBuilder/(:num)', 'Discover::QueryBuilder/$1');

$routes->get('DiscoveryGroup', 'DiscoveryGroup::Index');
$routes->get('discoverygroup', 'DiscoveryGroup::Index');

$routes->get('DiscoveryGroup/Index', 'DiscoveryGroup::Index');
$routes->get('DiscoveryGroup/index', 'DiscoveryGroup::Index');
$routes->get('discoverygroup/index', 'DiscoveryGroup::Index');
$routes->get('discoverygroup/Index', 'DiscoveryGroup::Index');

$routes->get('DiscoveryGroup/Create', 'DiscoveryGroup::Create');
$routes->get('DiscoveryGroup/create', 'DiscoveryGroup::Create');
$routes->get('discoverygroup/create', 'DiscoveryGroup::Create');
$routes->get('discoverygroup/Create', 'DiscoveryGroup::Create');

$routes->post('DiscoveryGroup/Create', 'DiscoveryGroup::Create');
$routes->post('DiscoveryGroup/create', 'DiscoveryGroup::Create');
$routes->post('discoverygroup/create', 'DiscoveryGroup::Create');
$routes->post('discoverygroup/Create', 'DiscoveryGroup::Create');

$routes->get('DiscoveryGroup/List', 'DiscoveryGroup::List');
$routes->get('DiscoveryGroup/list', 'DiscoveryGroup::List');
$routes->get('discoverygroup/list', 'DiscoveryGroup::List');
$routes->get('discoverygroup/List', 'DiscoveryGroup::List');

$routes->get('DiscoveryGroup/Update/(:num)', 'DiscoveryGroup::Update/$1');
$routes->get('DiscoveryGroup/update/(:num)', 'DiscoveryGroup::Update/$1');
$routes->get('discoverygroup/update/(:num)', 'DiscoveryGroup::Update/$1');
$routes->get('discoverygroup/Update/(:num)', 'DiscoveryGroup::Update/$1');

$routes->post('DiscoveryGroup/Update/(:num)', 'DiscoveryGroup::Update/$1');
$routes->post('DiscoveryGroup/update/(:num)', 'DiscoveryGroup::Update/$1');
$routes->post('discoverygroup/update/(:num)', 'DiscoveryGroup::Update/$1');
$routes->post('discoverygroup/Update/(:num)', 'DiscoveryGroup::Update/$1');

$routes->get('DiscoveryGroup/Delete/(:num)', 'DiscoveryGroup::Delete/$1');
$routes->get('DiscoveryGroup/delete/(:num)', 'DiscoveryGroup::Delete/$1');
$routes->get('discoverygroup/delete/(:num)', 'DiscoveryGroup::Delete/$1');
$routes->get('discoverygroup/Delete/(:num)', 'DiscoveryGroup::Delete/$1');

$routes->post('DiscoveryGroup/Delete/(:num)', 'DiscoveryGroup::Delete/$1');
$routes->post('DiscoveryGroup/delete/(:num)', 'DiscoveryGroup::Delete/$1');
$routes->post('discoverygroup/delete/(:num)', 'DiscoveryGroup::Delete/$1');
$routes->post('discoverygroup/Delete/(:num)', 'DiscoveryGroup::Delete/$1');

$routes->get('DiscoveryGroup/Details/(:num)', 'DiscoveryGroup::Details/$1');
$routes->get('DiscoveryGroup/details/(:num)', 'DiscoveryGroup::Details/$1');
$routes->get('discoverygroup/details/(:num)', 'DiscoveryGroup::Details/$1');
$routes->get('discoverygroup/Details/(:num)', 'DiscoveryGroup::Details/$1');

$routes->get('Home', 'Home::Index');
$routes->get('home', 'Home::Index');

$routes->get('Home/Index', 'Home::Index');
$routes->get('Home/index', 'Home::Index');
$routes->get('home/Index', 'Home::Index');
$routes->get('home/index', 'Home::Index');

$routes->get('Home/Portal', 'Home::Portal');
$routes->get('Home/portal', 'Home::Portal');
$routes->get('home/Portal', 'Home::Portal');
$routes->get('home/portal', 'Home::Portal');

$routes->get('home/index/(:num)', 'Home::Index/$1');
$routes->get('Home/Index/(:num)', 'Home::Index/$1');
$routes->get('Home/index/(:num)', 'Home::Index/$1');
$routes->get('home/Index/(:num)', 'Home::Index/$1');

$routes->get('Network', 'Network::Index');
$routes->get('network', 'Network::Index');

$routes->get('Network/Create', 'Network::Create');
$routes->get('Network/create', 'Network::Create');
$routes->get('network/create', 'Network::Create');
$routes->get('network/Create', 'Network::Create');

$routes->post('Network/Create', 'Network::Create');
$routes->post('Network/create', 'Network::Create');
$routes->post('network/create', 'Network::Create');
$routes->post('network/Create', 'Network::Create');

$routes->get('Network/List', 'Network::List');
$routes->get('Network/list', 'Network::List');
$routes->get('network/list', 'Network::List');
$routes->get('network/List', 'Network::List');

$routes->get('Network/Join', 'Network::Join');
$routes->get('Network/join', 'Network::Join');
$routes->get('network/join', 'Network::Join');
$routes->get('network/Join', 'Network::Join');

$routes->post('Network/Join', 'Network::Join');
$routes->post('Network/join', 'Network::Join');
$routes->post('network/join', 'Network::Join');
$routes->post('network/Join', 'Network::Join');

$routes->get('Network/Leave/(:num)', 'Network::Leave/$1');
$routes->get('Network/leave/(:num)', 'Network::Leave/$1');
$routes->get('network/leave/(:num)', 'Network::Leave/$1');
$routes->get('network/Leave/(:num)', 'Network::Leave/$1');

$routes->post('Network/Leave/(:num)', 'Network::Leave/$1');
$routes->post('Network/leave/(:num)', 'Network::Leave/$1');
$routes->post('network/leave/(:num)', 'Network::Leave/$1');
$routes->post('network/Leave/(:num)', 'Network::Leave/$1');

$routes->post('NetworkAPI/requestToJoinNetwork', 'NetworkAPI::requestToJoinNetwork');
$routes->post('NetworkApi/requestToJoinNetwork', 'NetworkAPI::requestToJoinNetwork');

$routes->get('NetworkRequest', 'NetworkRequest::Index');
$routes->get('networkrequest', 'NetworkRequest::Index');

$routes->get('NetworkRequest/Index', 'NetworkRequest::Index');
$routes->get('NetworkRequest/index', 'NetworkRequest::Index');
$routes->get('networkrequest/index', 'NetworkRequest::Index');
$routes->get('networkrequest/Index', 'NetworkRequest::Index');

$routes->get('NetworkRequest/List', 'NetworkRequest::List');
$routes->get('NetworkRequest/list', 'NetworkRequest::List');
$routes->get('networkrequest/list', 'NetworkRequest::List');
$routes->get('networkrequest/List', 'NetworkRequest::List');

$routes->get('NetworkRequest/Accept/(:num)', 'NetworkRequest::Accept/$1');
$routes->get('NetworkRequest/accept/(:num)', 'NetworkRequest::Accept/$1');
$routes->get('networkrequest/accept/(:num)', 'NetworkRequest::Accept/$1');
$routes->get('networkrequest/Accept/(:num)', 'NetworkRequest::Accept/$1');

$routes->get('NetworkRequest/Reject/(:num)', 'NetworkRequest::Reject/$1');
$routes->get('NetworkRequest/reject/(:num)', 'NetworkRequest::Reject/$1');
$routes->get('networkrequest/reject/(:num)', 'NetworkRequest::Reject/$1');
$routes->get('networkrequest/Reject/(:num)', 'NetworkRequest::Reject/$1');

$routes->get('Ontology', 'Ontology::Index');
$routes->get('ontology', 'Ontology::Index');

$routes->get('Ontology/Index', 'Ontology::Index');
$routes->get('Ontology/index', 'Ontology::Index');
$routes->get('ontology/index', 'Ontology::Index');
$routes->get('ontology/Index', 'Ontology::Index');

$routes->get('Ontology/Create', 'Ontology::Create');
$routes->get('Ontology/create', 'Ontology::Create');
$routes->get('ontology/create', 'Ontology::Create');
$routes->get('ontology/Create', 'Ontology::Create');

$routes->post('Ontology/Create', 'Ontology::Create');
$routes->post('Ontology/create', 'Ontology::Create');
$routes->post('ontology/create', 'Ontology::Create');
$routes->post('ontology/Create', 'Ontology::Create');

$routes->get('Ontology/List', 'Ontology::List');
$routes->get('Ontology/list', 'Ontology::List');
$routes->get('ontology/list', 'Ontology::List');
$routes->get('ontology/List', 'Ontology::List');

$routes->get('Ontology/Update/(:num)', 'Ontology::Update/$1');
$routes->get('Ontology/update/(:num)', 'Ontology::Update/$1');
$routes->get('ontology/update/(:num)', 'Ontology::Update/$1');
$routes->get('ontology/Update/(:num)', 'Ontology::Update/$1');

$routes->post('Ontology/Update/(:num)', 'Ontology::Update/$1');
$routes->post('Ontology/update/(:num)', 'Ontology::Update/$1');
$routes->post('ontology/update/(:num)', 'Ontology::Update/$1');
$routes->post('ontology/Update/(:num)', 'Ontology::Update/$1');

$routes->get('Ontology/Delete/(:num)', 'Ontology::Delete/$1');
$routes->get('Ontology/delete/(:num)', 'Ontology::Delete/$1');
$routes->get('ontology/delete/(:num)', 'Ontology::Delete/$1');
$routes->get('ontology/Delete/(:num)', 'Ontology::Delete/$1');

$routes->post('Ontology/Delete/(:num)', 'Ontology::Delete/$1');
$routes->post('Ontology/delete/(:num)', 'Ontology::Delete/$1');
$routes->post('ontology/delete/(:num)', 'Ontology::Delete/$1');
$routes->post('ontology/Delete/(:num)', 'Ontology::Delete/$1');

$routes->get('Ontology/Details/(:num)', 'Ontology::Details/$1');
$routes->get('Ontology/details/(:num)', 'Ontology::Details/$1');
$routes->get('ontology/details/(:num)', 'Ontology::Details/$1');
$routes->get('ontology/Details/(:num)', 'Ontology::Details/$1');

$routes->get('OntologyPrefix', 'OntologyPrefix::Index');
$routes->get('ontologyprefix', 'OntologyPrefix::Index');

$routes->get('OntologyPrefix/Index', 'OntologyPrefix::Index');
$routes->get('OntologyPrefix/index', 'OntologyPrefix::Index');
$routes->get('ontologyprefix/index', 'OntologyPrefix::Index');
$routes->get('ontologyprefix/Index', 'OntologyPrefix::Index');

$routes->get('OntologyPrefix/List/(:num)', 'OntologyPrefix::List/$1');
$routes->get('OntologyPrefix/list/(:num)', 'OntologyPrefix::List/$1');
$routes->get('ontologyprefix/list/(:num)', 'OntologyPrefix::List/$1');
$routes->get('ontologyprefix/List/(:num)', 'OntologyPrefix::List/$1');

$routes->get('OntologyPrefix/Create/(:num)', 'OntologyPrefix::Create/$1');
$routes->get('OntologyPrefix/create/(:num)', 'OntologyPrefix::Create/$1');
$routes->get('ontologyprefix/create/(:num)', 'OntologyPrefix::Create/$1');
$routes->get('ontologyprefix/Create/(:num)', 'OntologyPrefix::Create/$1');

$routes->post('OntologyPrefix/Create/(:num)', 'OntologyPrefix::Create/$1');
$routes->post('OntologyPrefix/create/(:num)', 'OntologyPrefix::Create/$1');
$routes->post('ontologyprefix/create/(:num)', 'OntologyPrefix::Create/$1');
$routes->post('ontologyprefix/Create/(:num)', 'OntologyPrefix::Create/$1');

$routes->get('OntologyPrefix/Update/(:num)', 'OntologyPrefix::Update/$1');
$routes->get('OntologyPrefix/update/(:num)', 'OntologyPrefix::Update/$1');
$routes->get('ontologyprefix/update/(:num)', 'OntologyPrefix::Update/$1');
$routes->get('ontologyprefix/Update/(:num)', 'OntologyPrefix::Update/$1');

$routes->post('OntologyPrefix/Update/(:num)', 'OntologyPrefix::Update/$1');
$routes->post('OntologyPrefix/update/(:num)', 'OntologyPrefix::Update/$1');
$routes->post('ontologyprefix/update/(:num)', 'OntologyPrefix::Update/$1');
$routes->post('ontologyprefix/Update/(:num)', 'OntologyPrefix::Update/$1');

$routes->get('OntologyPrefix/Delete/(:num)', 'OntologyPrefix::Delete/$1');
$routes->get('OntologyPrefix/delete/(:num)', 'OntologyPrefix::Delete/$1');
$routes->get('ontologyprefix/delete/(:num)', 'OntologyPrefix::Delete/$1');
$routes->get('ontologyprefix/Delete/(:num)', 'OntologyPrefix::Delete/$1');

$routes->post('OntologyPrefix/Delete/(:num)', 'OntologyPrefix::Delete/$1');
$routes->post('OntologyPrefix/delete/(:num)', 'OntologyPrefix::Delete/$1');
$routes->post('ontologyprefix/delete/(:num)', 'OntologyPrefix::Delete/$1');
$routes->post('ontologyprefix/Delete/(:num)', 'OntologyPrefix::Delete/$1');

$routes->get('OntologyRelationship', 'OntologyRelationship::Index');
$routes->get('ontologyRelationship', 'OntologyRelationship::Index');
$routes->get('ontologyrelationship', 'OntologyRelationship::Index');

$routes->get('OntologyRelationship/Index', 'OntologyRelationship::Index');
$routes->get('OntologyRelationship/index', 'OntologyRelationship::Index');
$routes->get('ontologyrelationship/index', 'OntologyRelationship::Index');
$routes->get('ontologyrelationship/Index', 'OntologyRelationship::Index');

$routes->get('OntologyRelationship/List/(:num)', 'OntologyRelationship::List/$1');
$routes->get('OntologyRelationship/list/(:num)', 'OntologyRelationship::List/$1');
$routes->get('ontologyrelationship/list/(:num)', 'OntologyRelationship::List/$1');
$routes->get('ontologyrelationship/List/(:num)', 'OntologyRelationship::List/$1');

$routes->get('OntologyRelationship/Create/(:num)', 'OntologyRelationship::Create/$1');
$routes->get('OntologyRelationship/create/(:num)', 'OntologyRelationship::Create/$1');
$routes->get('ontologyrelationship/create/(:num)', 'OntologyRelationship::Create/$1');
$routes->get('ontologyrelationship/Create/(:num)', 'OntologyRelationship::Create/$1');

$routes->post('OntologyRelationship/Create/(:num)', 'OntologyRelationship::Create/$1');
$routes->post('OntologyRelationship/create/(:num)', 'OntologyRelationship::Create/$1');
$routes->post('ontologyrelationship/create/(:num)', 'OntologyRelationship::Create/$1');
$routes->post('ontologyrelationship/Create/(:num)', 'OntologyRelationship::Create/$1');

$routes->get('OntologyRelationship/Update/(:num)', 'OntologyRelationship::Update/$1');
$routes->get('OntologyRelationship/update/(:num)', 'OntologyRelationship::Update/$1');
$routes->get('ontologyrelationship/update/(:num)', 'OntologyRelationship::Update/$1');
$routes->get('ontologyrelationship/Update/(:num)', 'OntologyRelationship::Update/$1');

$routes->post('OntologyRelationship/Update/(:num)', 'OntologyRelationship::Update/$1');
$routes->post('OntologyRelationship/update/(:num)', 'OntologyRelationship::Update/$1');
$routes->post('ontologyrelationship/update/(:num)', 'OntologyRelationship::Update/$1');
$routes->post('ontologyrelationship/Update/(:num)', 'OntologyRelationship::Update/$1');

$routes->get('OntologyRelationship/Delete/(:num)', 'OntologyRelationship::Delete/$1');
$routes->get('OntologyRelationship/delete/(:num)', 'OntologyRelationship::Delete/$1');
$routes->get('ontologyrelationship/delete/(:num)', 'OntologyRelationship::Delete/$1');
$routes->get('ontologyrelationship/Delete/(:num)', 'OntologyRelationship::Delete/$1');

$routes->post('OntologyRelationship/Delete/(:num)', 'OntologyRelationship::Delete/$1');
$routes->post('OntologyRelationship/delete/(:num)', 'OntologyRelationship::Delete/$1');
$routes->post('ontologyrelationship/delete/(:num)', 'OntologyRelationship::Delete/$1');
$routes->post('ontologyrelationship/Delete/(:num)', 'OntologyRelationship::Delete/$1');


$routes->get('Page', 'Page::Index');
$routes->get('page', 'Page::Index');

$routes->get('Page/Index', 'Page::Index');
$routes->get('Page/index', 'Page::Index');
$routes->get('page/index', 'Page::Index');
$routes->get('page/Index', 'Page::Index');

$routes->get('Page/List', 'Page::List');
$routes->get('Page/list', 'Page::List');
$routes->get('page/list', 'Page::List');
$routes->get('page/List', 'Page::List');

$routes->get('Page/Create', 'Page::Create');
$routes->get('Page/create', 'Page::Create');
$routes->get('page/create', 'Page::Create');
$routes->get('page/Create', 'Page::Create');

$routes->post('Page/Create', 'Page::Create');
$routes->post('Page/create', 'Page::Create');
$routes->post('page/create', 'Page::Create');
$routes->post('page/Create', 'Page::Create');

$routes->get('Page/Update/(:num)', 'Page::Update/$1');
$routes->get('Page/update/(:num)', 'Page::Update/$1');
$routes->get('page/update/(:num)', 'Page::Update/$1');
$routes->get('page/Update/(:num)', 'Page::Update/$1');

$routes->post('Page/Update/(:num)', 'Page::Update/$1');
$routes->post('Page/update/(:num)', 'Page::Update/$1');
$routes->post('page/update/(:num)', 'Page::Update/$1');
$routes->post('page/Update/(:num)', 'Page::Update/$1');

$routes->get('Page/Activate/(:num)', 'Page::Activate/$1');
$routes->get('Page/activate/(:num)', 'Page::Activate/$1');
$routes->get('page/activate/(:num)', 'Page::Activate/$1');
$routes->get('page/Activate/(:num)', 'Page::Activate/$1');

$routes->get('Page/Deactivate/(:num)', 'Page::Deactivate/$1');
$routes->get('Page/deactivate/(:num)', 'Page::Deactivate/$1');
$routes->get('page/deactivate/(:num)', 'Page::Deactivate/$1');
$routes->get('page/Deactivate/(:num)', 'Page::Deactivate/$1');

$routes->get('Page/Delete/(:num)', 'Page::Delete/$1');
$routes->get('Page/delete/(:num)', 'Page::Delete/$1');
$routes->get('page/delete/(:num)', 'Page::Delete/$1');
$routes->get('page/Delete/(:num)', 'Page::Delete/$1');

$routes->post('Page/Delete/(:num)', 'Page::Delete/$1');
$routes->post('Page/delete/(:num)', 'Page::Delete/$1');
$routes->post('page/delete/(:num)', 'Page::Delete/$1');
$routes->post('page/Delete/(:num)', 'Page::Delete/$1');

$routes->get('Pipeline', 'Pipeline::Index');
$routes->get('pipeline', 'Pipeline::Index');

$routes->get('Pipeline/Index', 'Pipeline::Index');
$routes->get('Pipeline/index', 'Pipeline::Index');
$routes->get('pipeline/index', 'Pipeline::Index');
$routes->get('pipeline/Index', 'Pipeline::Index');

$routes->get('Pipeline/List', 'Pipeline::List');
$routes->get('Pipeline/list', 'Pipeline::List');
$routes->get('pipeline/list', 'Pipeline::List');
$routes->get('pipeline/List', 'Pipeline::List');

$routes->get('Pipeline/Delete/(:num)', 'Pipeline::Delete/$1');
$routes->get('Pipeline/delete/(:num)', 'Pipeline::Delete/$1');
$routes->get('pipeline/delete/(:num)', 'Pipeline::Delete/$1');
$routes->get('pipeline/Delete/(:num)', 'Pipeline::Delete/$1');

$routes->post('Pipeline/Delete/(:num)', 'Pipeline::Delete/$1');
$routes->post('Pipeline/delete/(:num)', 'Pipeline::Delete/$1');
$routes->post('pipeline/delete/(:num)', 'Pipeline::Delete/$1');
$routes->post('pipeline/Delete/(:num)', 'Pipeline::Delete/$1');

$routes->get('Pipeline/Create', 'Pipeline::Create');
$routes->get('Pipeline/create', 'Pipeline::Create');
$routes->get('pipeline/create', 'Pipeline::Create');
$routes->get('pipeline/Create', 'Pipeline::Create');

$routes->post('Pipeline/Create', 'Pipeline::Create');
$routes->post('Pipeline/create', 'Pipeline::Create');
$routes->post('pipeline/create', 'Pipeline::Create');
$routes->post('pipeline/Create', 'Pipeline::Create');

$routes->get('Pipeline/Details/(:num)', 'Pipeline::Details/$1');
$routes->get('Pipeline/details/(:num)', 'Pipeline::Details/$1');
$routes->get('pipeline/details/(:num)', 'Pipeline::Details/$1');
$routes->get('pipeline/Details/(:num)', 'Pipeline::Details/$1');

$routes->get('Pipeline/Update/(:num)', 'Pipeline::Update/$1');
$routes->get('Pipeline/update/(:num)', 'Pipeline::Update/$1');
$routes->get('pipeline/update/(:num)', 'Pipeline::Update/$1');
$routes->get('pipeline/Update/(:num)', 'Pipeline::Update/$1');

$routes->post('Pipeline/Update/(:num)', 'Pipeline::Update/$1');
$routes->post('Pipeline/update/(:num)', 'Pipeline::Update/$1');
$routes->post('pipeline/update/(:num)', 'Pipeline::Update/$1');
$routes->post('pipeline/Update/(:num)', 'Pipeline::Update/$1');

$routes->get('ProxyServer', 'ProxyServer::Index');
$routes->get('proxyServer', 'ProxyServer::Index');
$routes->get('proxyserver', 'ProxyServer::Index');
$routes->get('Proxyserver', 'ProxyServer::Index');

$routes->get('ProxyServer/Index', 'ProxyServer::Index');
$routes->get('ProxyServer/index', 'ProxyServer::Index');
$routes->get('proxyserver/index', 'ProxyServer::Index');
$routes->get('proxyserver/Index', 'ProxyServer::Index');

$routes->get('ProxyServer/List', 'ProxyServer::List');
$routes->get('ProxyServer/list', 'ProxyServer::List');
$routes->get('proxyserver/list', 'ProxyServer::List');
$routes->get('proxyserver/List', 'ProxyServer::List');

$routes->get('ProxyServer/Create', 'ProxyServer::Create');
$routes->get('ProxyServer/create', 'ProxyServer::Create');
$routes->get('proxyserver/create', 'ProxyServer::Create');
$routes->get('proxyserver/Create', 'ProxyServer::Create');

$routes->post('ProxyServer/Create', 'ProxyServer::Create');
$routes->post('ProxyServer/create', 'ProxyServer::Create');
$routes->post('proxyserver/create', 'ProxyServer::Create');
$routes->post('proxyserver/Create', 'ProxyServer::Create');

$routes->get('ProxyServer/Update/(:num)', 'ProxyServer::Update/$1');
$routes->get('ProxyServer/update/(:num)', 'ProxyServer::Update/$1');
$routes->get('proxyserver/update/(:num)', 'ProxyServer::Update/$1');
$routes->get('proxyserver/Update/(:num)', 'ProxyServer::Update/$1');

$routes->post('ProxyServer/Update/(:num)', 'ProxyServer::Update/$1');
$routes->post('ProxyServer/update/(:num)', 'ProxyServer::Update/$1');
$routes->post('proxyserver/update/(:num)', 'ProxyServer::Update/$1');
$routes->post('proxyserver/Update/(:num)', 'ProxyServer::Update/$1');

$routes->get('ProxyServer/Details/(:num)', 'ProxyServer::Details/$1');
$routes->get('ProxyServer/details/(:num)', 'ProxyServer::Details/$1');
$routes->get('proxyserver/details/(:num)', 'ProxyServer::Details/$1');
$routes->get('proxyserver/Details/(:num)', 'ProxyServer::Details/$1');

$routes->get('ProxyServer/Delete/(:num)', 'ProxyServer::Delete/$1');
$routes->get('ProxyServer/delete/(:num)', 'ProxyServer::Delete/$1');
$routes->get('proxyserver/delete/(:num)', 'ProxyServer::Delete/$1');
$routes->get('proxyserver/Delete/(:num)', 'ProxyServer::Delete/$1');

$routes->post('ProxyServer/Delete/(:num)', 'ProxyServer::Delete/$1');
$routes->post('ProxyServer/delete/(:num)', 'ProxyServer::Delete/$1');
$routes->post('proxyserver/delete/(:num)', 'ProxyServer::Delete/$1');
$routes->post('proxyserver/Delete/(:num)', 'ProxyServer::Delete/$1');

$routes->post('QueryApi/Query', 'QueryApi::Query');
$routes->post('QueryApi/query', 'QueryApi::Query');
$routes->post('queryapi/query', 'QueryApi::Query');
$routes->post('queryapi/Query', 'QueryApi::Query');

$routes->post('QueryAPI/Query', 'QueryApi::Query');
$routes->post('QueryAPI/query', 'QueryApi::Query');
$routes->post('queryapi/query', 'QueryApi::Query');
$routes->post('queryapi/Query', 'QueryApi::Query');

$routes->post('QueryApi/getJSONDataModificationTime', 'QueryApi::getJSONDataModificationTime');
$routes->post('QueryApi/getjsondatamodificationtime', 'QueryApi::getJSONDataModificationTime');
$routes->post('queryapi/getjsondatamodificationtime', 'QueryApi::getJSONDataModificationTime');
$routes->post('queryapi/getJSONDataModificationTime', 'QueryApi::getJSONDataModificationTime');

$routes->post('QueryApi/getEAVJSON', 'QueryApi::getEAVJSON');
$routes->post('QueryApi/geteavjson', 'QueryApi::getEAVJSON');
$routes->post('queryapi/geteavjson', 'QueryApi::getEAVJSON');
$routes->post('queryapi/getEAVJSON', 'QueryApi::getEAVJSON');

$routes->post('QueryApi/getHPOJSON', 'QueryApi::getHPOJSON');
$routes->post('QueryApi/gethpojson', 'QueryApi::getHPOJSON');
$routes->post('queryapi/gethpojson', 'QueryApi::getHPOJSON');
$routes->post('queryapi/getHPOJSON', 'QueryApi::getHPOJSON');

$routes->get('Server', 'Server::Index');
$routes->get('server', 'Server::Index');

$routes->get('Server/Index', 'Server::Index');
$routes->get('Server/index', 'Server::Index');
$routes->get('server/index', 'Server::Index');
$routes->get('server/Index', 'Server::Index');

$routes->get('Server/List', 'Server::List');
$routes->get('Server/list', 'Server::List');
$routes->get('server/list', 'Server::List');
$routes->get('server/List', 'Server::List');

$routes->get('Server/Create', 'Server::Create');
$routes->get('Server/create', 'Server::Create');
$routes->get('server/create', 'Server::Create');
$routes->get('server/Create', 'Server::Create');

$routes->post('Server/Create', 'Server::Create');
$routes->post('Server/create', 'Server::Create');
$routes->post('server/create', 'Server::Create');
$routes->post('server/Create', 'Server::Create');

$routes->get('Server/Update/(:num)', 'Server::Update/$1');
$routes->get('Server/update/(:num)', 'Server::Update/$1');
$routes->get('server/update/(:num)', 'Server::Update/$1');
$routes->get('server/Update/(:num)', 'Server::Update/$1');

$routes->post('Server/Update/(:num)', 'Server::Update/$1');
$routes->post('Server/update/(:num)', 'Server::Update/$1');
$routes->post('server/update/(:num)', 'Server::Update/$1');
$routes->post('server/Update/(:num)', 'Server::Update/$1');

$routes->get('Server/Details/(:num)', 'Server::Details/$1');
$routes->get('Server/details/(:num)', 'Server::Details/$1');
$routes->get('server/details/(:num)', 'Server::Details/$1');
$routes->get('server/Details/(:num)', 'Server::Details/$1');

$routes->get('Server/Delete/(:num)', 'Server::Delete/$1');
$routes->get('Server/delete/(:num)', 'Server::Delete/$1');
$routes->get('server/delete/(:num)', 'Server::Delete/$1');
$routes->get('server/Delete/(:num)', 'Server::Delete/$1');

$routes->post('Server/Delete/(:num)', 'Server::Delete/$1');
$routes->post('Server/delete/(:num)', 'Server::Delete/$1');
$routes->post('server/delete/(:num)', 'Server::Delete/$1');
$routes->post('server/Delete/(:num)', 'Server::Delete/$1');

$routes->get('ServiceApi/PollTasks', 'ServiceAPI::PollTasks');
$routes->get('ServiceApi/pollTasks', 'ServiceAPI::PollTasks');
$routes->get('serviceApi/pollTasks', 'ServiceAPI::PollTasks');
$routes->get('serviceAPI/pollTasks', 'ServiceAPI::PollTasks');

$routes->get('ServiceAPI/PollTasks', 'ServiceAPI::PollTasks');
$routes->get('ServiceAPI/pollTasks', 'ServiceAPI::PollTasks');
$routes->get('serviceAPI/pollTasks', 'ServiceAPI::PollTasks');
$routes->get('serviceApi/pollTasks', 'ServiceAPI::PollTasks');

$routes->post('ServiceApi/PollTask', 'ServiceAPI::PollTask');
$routes->post('ServiceApi/pollTask', 'ServiceAPI::PollTask');
$routes->post('serviceApi/pollTask', 'ServiceAPI::PollTask');
$routes->post('serviceAPI/pollTask', 'ServiceAPI::PollTask');

$routes->post('ServiceAPI/PollTask', 'ServiceAPI::PollTask');
$routes->post('ServiceAPI/pollTask', 'ServiceAPI::PollTask');
$routes->post('serviceAPI/pollTask', 'ServiceAPI::PollTask');
$routes->post('serviceApi/pollTask', 'ServiceAPI::PollTask');

$routes->get('Setting/Discovery', 'Setting::Discovery');
$routes->get('Setting/discovery', 'Setting::Discovery');
$routes->get('setting/discovery', 'Setting::Discovery');
$routes->get('setting/Discovery', 'Setting::Discovery');

$routes->post('Setting/Discovery', 'Setting::Discovery');
$routes->post('Setting/discovery', 'Setting::Discovery');
$routes->post('setting/discovery', 'Setting::Discovery');
$routes->post('setting/Discovery', 'Setting::Discovery');

$routes->get('Setting/Endpoint', 'Setting::Endpoint');
$routes->get('Setting/endpoint', 'Setting::Endpoint');
$routes->get('setting/endpoint', 'Setting::Endpoint');
$routes->get('setting/Endpoint', 'Setting::Endpoint');

$routes->post('Setting/Endpoint', 'Setting::Endpoint');
$routes->post('Setting/endpoint', 'Setting::Endpoint');
$routes->post('setting/endpoint', 'Setting::Endpoint');
$routes->post('setting/Endpoint', 'Setting::Endpoint');

$routes->get('Setting/Main', 'Setting::Main');
$routes->get('Setting/main', 'Setting::Main');
$routes->get('setting/main', 'Setting::Main');
$routes->get('setting/Main', 'Setting::Main');

$routes->post('Setting/Main', 'Setting::Main');
$routes->post('Setting/main', 'Setting::Main');
$routes->post('setting/main', 'Setting::Main');
$routes->post('setting/Main', 'Setting::Main');

$routes->get('Setting/Elasticsearch', 'Setting::Elasticsearch');
$routes->get('Setting/elasticsearch', 'Setting::Elasticsearch');
$routes->get('setting/elasticsearch', 'Setting::Elasticsearch');
$routes->get('setting/Elasticsearch', 'Setting::Elasticsearch');

$routes->post('Setting/Elasticsearch', 'Setting::Elasticsearch');
$routes->post('Setting/elasticsearch', 'Setting::Elasticsearch');
$routes->post('setting/elasticsearch', 'Setting::Elasticsearch');
$routes->post('setting/Elasticsearch', 'Setting::Elasticsearch');

$routes->get('Setting/Neo4J', 'Setting::Neo4J');
$routes->get('Setting/neo4J', 'Setting::Neo4J');
$routes->get('setting/neo4J', 'Setting::Neo4J');
$routes->get('setting/Neo4J', 'Setting::Neo4J');

$routes->post('Setting/Neo4J', 'Setting::Neo4J');
$routes->post('Setting/neo4J', 'Setting::Neo4J');
$routes->post('setting/neo4J', 'Setting::Neo4J');
$routes->post('setting/Neo4J', 'Setting::Neo4J');

$routes->get('SingleSignOnProvider', 'SingleSignOnProvider::Index');
$routes->get('singlesignonprovider', 'SingleSignOnProvider::Index');

$routes->get('SingleSignOnProvider/Index', 'SingleSignOnProvider::Index');
$routes->get('SingleSignOnProvider/index', 'SingleSignOnProvider::Index');
$routes->get('singlesignonprovider/Index', 'SingleSignOnProvider::Index');
$routes->get('singlesignonprovider/index', 'SingleSignOnProvider::Index');

$routes->get('SingleSignOnProvider/List', 'SingleSignOnProvider::List');
$routes->get('SingleSignOnProvider/list', 'SingleSignOnProvider::List');
$routes->get('singlesignonprovider/List', 'SingleSignOnProvider::List');
$routes->get('singlesignonprovider/list', 'SingleSignOnProvider::List');

$routes->get('SingleSignOnProvider/Create', 'SingleSignOnProvider::Create');
$routes->get('SingleSignOnProvider/create', 'SingleSignOnProvider::Create');
$routes->get('singlesignonprovider/Create', 'SingleSignOnProvider::Create');
$routes->get('singlesignonprovider/create', 'SingleSignOnProvider::Create');

$routes->post('SingleSignOnProvider/Create', 'SingleSignOnProvider::Create');
$routes->post('SingleSignOnProvider/create', 'SingleSignOnProvider::Create');
$routes->post('singlesignonprovider/Create', 'SingleSignOnProvider::Create');
$routes->post('singlesignonprovider/create', 'SingleSignOnProvider::Create');

$routes->get('SingleSignOnProvider/Update/(:num)', 'SingleSignOnProvider::Update/$1');
$routes->get('SingleSignOnProvider/update/(:num)', 'SingleSignOnProvider::Update/$1');
$routes->get('singlesignonprovider/Update/(:num)', 'SingleSignOnProvider::Update/$1');
$routes->get('singlesignonprovider/update/(:num)', 'SingleSignOnProvider::Update/$1');

$routes->post('SingleSignOnProvider/Update/(:num)', 'SingleSignOnProvider::Update/$1');
$routes->post('SingleSignOnProvider/update/(:num)', 'SingleSignOnProvider::Update/$1');
$routes->post('singlesignonprovider/Update/(:num)', 'SingleSignOnProvider::Update/$1');
$routes->post('singlesignonprovider/update/(:num)', 'SingleSignOnProvider::Update/$1');

$routes->get('SingleSignOnProvider/Details/(:num)', 'SingleSignOnProvider::Details/$1');
$routes->get('SingleSignOnProvider/details/(:num)', 'SingleSignOnProvider::Details/$1');
$routes->get('singlesignonprovider/Details/(:num)', 'SingleSignOnProvider::Details/$1');
$routes->get('singlesignonprovider/details/(:num)', 'SingleSignOnProvider::Details/$1');

$routes->get('SingleSignOnProvider/Delete/(:num)', 'SingleSignOnProvider::Delete/$1');
$routes->get('SingleSignOnProvider/delete/(:num)', 'SingleSignOnProvider::Delete/$1');
$routes->get('singlesignonprovider/Delete/(:num)', 'SingleSignOnProvider::Delete/$1');
$routes->get('singlesignonprovider/delete/(:num)', 'SingleSignOnProvider::Delete/$1');

$routes->post('SingleSignOnProvider/Delete/(:num)', 'SingleSignOnProvider::Delete/$1');
$routes->post('SingleSignOnProvider/delete/(:num)', 'SingleSignOnProvider::Delete/$1');
$routes->post('singlesignonprovider/Delete/(:num)', 'SingleSignOnProvider::Delete/$1');
$routes->post('singlesignonprovider/delete/(:num)', 'SingleSignOnProvider::Delete/$1');

$routes->get('Source', 'Source::Index');
$routes->get('source', 'Source::Index');

$routes->get('Source/Index', 'Source::Index');
$routes->get('Source/index', 'Source::Index');
$routes->get('source/Index', 'Source::Index');
$routes->get('source/index', 'Source::Index');

$routes->get('Source/List', 'Source::List');
$routes->get('Source/list', 'Source::List');
$routes->get('source/List', 'Source::List');
$routes->get('source/list', 'Source::List');

$routes->get('Source/Create', 'Source::Create');
$routes->get('Source/create', 'Source::Create');
$routes->get('source/Create', 'Source::Create');
$routes->get('source/create', 'Source::Create');

$routes->post('Source/Create', 'Source::Create');
$routes->post('Source/create', 'Source::Create');
$routes->post('source/Create', 'Source::Create');
$routes->post('source/create', 'Source::Create');

$routes->get('Source/Update/(:num)', 'Source::Update/$1');
$routes->get('Source/update/(:num)', 'Source::Update/$1');
$routes->get('source/Update/(:num)', 'Source::Update/$1');
$routes->get('source/update/(:num)', 'Source::Update/$1');

$routes->post('Source/Update/(:num)', 'Source::Update/$1');
$routes->post('Source/update/(:num)', 'Source::Update/$1');
$routes->post('source/Update/(:num)', 'Source::Update/$1');
$routes->post('source/update/(:num)', 'Source::Update/$1');

$routes->get('Source/Delete/(:num)', 'Source::Delete/$1');
$routes->get('Source/delete/(:num)', 'Source::Delete/$1');
$routes->get('source/Delete/(:num)', 'Source::Delete/$1');
$routes->get('source/delete/(:num)', 'Source::Delete/$1');

$routes->post('Source/Delete/(:num)', 'Source::Delete/$1');
$routes->post('Source/delete/(:num)', 'Source::Delete/$1');
$routes->post('source/Delete/(:num)', 'Source::Delete/$1');
$routes->post('source/delete/(:num)', 'Source::Delete/$1');

$routes->get('Source/Elasticsearch/(:num)', 'Source::Elasticsearch/$1');
$routes->get('Source/elasticsearch/(:num)', 'Source::Elasticsearch/$1');
$routes->get('source/Elasticsearch/(:num)', 'Source::Elasticsearch/$1');
$routes->get('source/elasticsearch/(:num)', 'Source::Elasticsearch/$1');

$routes->get('Source/Neo4J/(:num)', 'Source::Neo4J/$1');
$routes->get('Source/neo4j/(:num)', 'Source::Neo4J/$1');
$routes->get('source/Neo4J/(:num)', 'Source::Neo4J/$1');
$routes->get('source/neo4j/(:num)', 'Source::Neo4J/$1');

$routes->get('Source/UserInterface/(:num)', 'Source::UserInterface/$1');
$routes->get('Source/userinterface/(:num)', 'Source::UserInterface/$1');
$routes->get('source/UserInterface/(:num)', 'Source::UserInterface/$1');
$routes->get('source/userinterface/(:num)', 'Source::UserInterface/$1');

$routes->cli('Task/Start/(:num)', 'Task::Start/$1');
$routes->cli('Task/CreateBatchTasksForDataFiles/(:any)', 'Task::CreateBatchTasksForDataFiles/$1');
$routes->cli('Task/StartService', 'Task::StartService');

$routes->get('User', 'User::Index');
$routes->get('user', 'User::Index');

$routes->get('User/Index', 'User::Index');
$routes->get('User/index', 'User::Index');
$routes->get('user/Index', 'User::Index');
$routes->get('user/index', 'User::Index');

$routes->get('User/List', 'User::List');
$routes->get('User/list', 'User::List');
$routes->get('user/List', 'User::List');
$routes->get('user/list', 'User::List');

$routes->get('User/Create', 'User::Create');
$routes->get('User/create', 'User::Create');
$routes->get('user/Create', 'User::Create');
$routes->get('user/create', 'User::Create');

$routes->post('User/Create', 'User::Create');
$routes->post('User/create', 'User::Create');
$routes->post('user/Create', 'User::Create');
$routes->post('user/create', 'User::Create');

$routes->get('User/Update/(:num)', 'User::Update/$1');
$routes->get('User/update/(:num)', 'User::Update/$1');
$routes->get('user/Update/(:num)', 'User::Update/$1');
$routes->get('user/update/(:num)', 'User::Update/$1');

$routes->post('User/Update/(:num)', 'User::Update/$1');
$routes->post('User/update/(:num)', 'User::Update/$1');
$routes->post('user/Update/(:num)', 'User::Update/$1');
$routes->post('user/update/(:num)', 'User::Update/$1');

$routes->get('User/Details/(:num)', 'User::Details/$1');
$routes->get('User/details/(:num)', 'User::Details/$1');
$routes->get('user/Details/(:num)', 'User::Details/$1');
$routes->get('user/details/(:num)', 'User::Details/$1');

$routes->get('User/Delete/(:num)', 'User::Delete/$1');
$routes->get('User/delete/(:num)', 'User::Delete/$1');
$routes->get('user/Delete/(:num)', 'User::Delete/$1');
$routes->get('user/delete/(:num)', 'User::Delete/$1');

$routes->post('User/Delete/(:num)', 'User::Delete/$1');
$routes->post('User/delete/(:num)', 'User::Delete/$1');
$routes->post('user/Delete/(:num)', 'User::Delete/$1');
$routes->post('user/delete/(:num)', 'User::Delete/$1');

$routes->get('UserInterfaceAPI/GetUIConstants', 'UserInterfaceAPI::GetUIConstants');
$routes->get('UserInterfaceApi/GetUIConstants', 'UserInterfaceAPI::GetUIConstants');

$routes->get('Value', 'Value::Index');
$routes->get('value', 'Value::Index');

$routes->get('Value/Index', 'Value::Index');
$routes->get('Value/index', 'Value::Index');
$routes->get('value/Index', 'Value::Index');
$routes->get('value/index', 'Value::Index');

$routes->get('Value/List/(:num)', 'Value::List/$1');
$routes->get('Value/list/(:num)', 'Value::List/$1');
$routes->get('value/List/(:num)', 'Value::List/$1');
$routes->get('value/list/(:num)', 'Value::List/$1');

$routes->get('Value/Details/(:num)', 'Value::Details/$1');
$routes->get('Value/details/(:num)', 'Value::Details/$1');
$routes->get('value/Details/(:num)', 'Value::Details/$1');
$routes->get('value/details/(:num)', 'Value::Details/$1');

$routes->get('Value/Update/(:num)', 'Value::Update/$1');
$routes->get('Value/update/(:num)', 'Value::Update/$1');
$routes->get('value/Update/(:num)', 'Value::Update/$1');
$routes->get('value/update/(:num)', 'Value::Update/$1');

$routes->post('Value/Update/(:num)', 'Value::Update/$1');
$routes->post('Value/update/(:num)', 'Value::Update/$1');
$routes->post('value/Update/(:num)', 'Value::Update/$1');
$routes->post('value/update/(:num)', 'Value::Update/$1');

$routes->get('ValueMapping', 'ValueMapping::Index');
$routes->get('valueMapping', 'ValueMapping::Index');
$routes->get('Valuemapping', 'ValueMapping::Index');
$routes->get('valuemapping', 'ValueMapping::Index');

$routes->get('ValueMapping/Index', 'ValueMapping::Index');
$routes->get('ValueMapping/index', 'ValueMapping::Index');
$routes->get('valuemapping/Index', 'ValueMapping::Index');
$routes->get('valuemapping/index', 'ValueMapping::Index');

$routes->get('ValueMapping/List/(:num)', 'ValueMapping::List/$1');
$routes->get('ValueMapping/list/(:num)', 'ValueMapping::List/$1');
$routes->get('valuemapping/List/(:num)', 'ValueMapping::List/$1');
$routes->get('valuemapping/list/(:num)', 'ValueMapping::List/$1');

$routes->get('ValueMapping/Create/(:num)', 'ValueMapping::Create/$1');
$routes->get('ValueMapping/create/(:num)', 'ValueMapping::Create/$1');
$routes->get('valuemapping/Create/(:num)', 'ValueMapping::Create/$1');
$routes->get('valuemapping/create/(:num)', 'ValueMapping::Create/$1');

$routes->post('ValueMapping/Create/(:num)', 'ValueMapping::Create/$1');
$routes->post('ValueMapping/create/(:num)', 'ValueMapping::Create/$1');
$routes->post('valuemapping/Create/(:num)', 'ValueMapping::Create/$1');
$routes->post('valuemapping/create/(:num)', 'ValueMapping::Create/$1');

$routes->get('ValueMapping/Delete/(:num)', 'ValueMapping::Delete/$1');
$routes->get('ValueMapping/delete/(:num)', 'ValueMapping::Delete/$1');
$routes->get('valuemapping/Delete/(:num)', 'ValueMapping::Delete/$1');
$routes->get('valuemapping/delete/(:num)', 'ValueMapping::Delete/$1');

$routes->post('ValueMapping/Delete/(:num)', 'ValueMapping::Delete/$1');
$routes->post('ValueMapping/delete/(:num)', 'ValueMapping::Delete/$1');
$routes->post('valuemapping/Delete/(:num)', 'ValueMapping::Delete/$1');
$routes->post('valuemapping/delete/(:num)', 'ValueMapping::Delete/$1');

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
