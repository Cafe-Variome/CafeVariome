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

$routes->get('DiscoveryGroup', 'DiscoveryGroup::Index');
$routes->get('DiscoveryGroup/Index', 'DiscoveryGroup::Index');
$routes->get('DiscoveryGroup/Create', 'DiscoveryGroup::Create');
$routes->post('DiscoveryGroup/Create', 'DiscoveryGroup::Create');
$routes->get('DiscoveryGroup/List', 'DiscoveryGroup::List');
$routes->get('DiscoveryGroup/Update/(:num)', 'DiscoveryGroup::Update/$1');
$routes->post('DiscoveryGroup/Update/(:num)', 'DiscoveryGroup::Update/$1');
$routes->get('DiscoveryGroup/Delete/(:num)', 'DiscoveryGroup::Delete/$1');
$routes->post('DiscoveryGroup/Delete/(:num)', 'DiscoveryGroup::Delete/$1');
$routes->get('DiscoveryGroup/Details/(:num)', 'DiscoveryGroup::Details/$1');

$routes->get('Home', 'Home::Index');
$routes->get('home', 'Home::Index');
$routes->get('Home/Index', 'Home::Index');
$routes->get('home/index', 'Home::Index');
$routes->get('home/index/(:num)', 'Home::Index/$1');
$routes->get('Home/Index/(:num)', 'Home::Index/$1');

$routes->get('Network', 'Network::Index');
$routes->get('Network/Create', 'Network::Create');
$routes->post('Network/Create', 'Network::Create');
$routes->get('Network/List', 'Network::List');
$routes->get('Network/Join', 'Network::Join');
$routes->post('Network/Join', 'Network::Join');
$routes->get('Network/Leave/(:num)', 'Network::Leave/$1');
$routes->post('Network/Leave/(:num)', 'Network::Leave/$1');

$routes->post('NetworkAPI/requestToJoinNetwork', 'NetworkAPI::requestToJoinNetwork');
$routes->post('NetworkApi/requestToJoinNetwork', 'NetworkAPI::requestToJoinNetwork');

$routes->get('NetworkRequest', 'NetworkRequest::Index');
$routes->get('NetworkRequest/Index', 'NetworkRequest::Index');
$routes->get('NetworkRequest/List', 'NetworkRequest::List');
$routes->get('NetworkRequest/Accept/(:num)', 'NetworkRequest::Accept/$1');
$routes->get('NetworkRequest/Reject/(:num)', 'NetworkRequest::Reject/$1');

$routes->get('Ontology', 'Ontology::Index');
$routes->get('Ontology/Index', 'Ontology::Index');
$routes->get('Ontology/Create', 'Ontology::Create');
$routes->post('Ontology/Create', 'Ontology::Create');
$routes->get('Ontology/List', 'Ontology::List');
$routes->get('Ontology/Update/(:num)', 'Ontology::Update/$1');
$routes->post('Ontology/Update/(:num)', 'Ontology::Update/$1');
$routes->get('Ontology/Delete/(:num)', 'Ontology::Delete/$1');
$routes->post('Ontology/Delete/(:num)', 'Ontology::Delete/$1');
$routes->get('Ontology/Details/(:num)', 'Ontology::Details/$1');

$routes->get('OntologyPrefix', 'OntologyPrefix::Index');
$routes->get('OntologyPrefix/Index', 'OntologyPrefix::Index');
$routes->get('OntologyPrefix/List/(:num)', 'OntologyPrefix::List/$1');
$routes->get('OntologyPrefix/Create/(:num)', 'OntologyPrefix::Create/$1');
$routes->post('OntologyPrefix/Create/(:num)', 'OntologyPrefix::Create/$1');
$routes->get('OntologyPrefix/Update/(:num)', 'OntologyPrefix::Update/$1');
$routes->post('OntologyPrefix/Update/(:num)', 'OntologyPrefix::Update/$1');
$routes->get('OntologyPrefix/Delete/(:num)', 'OntologyPrefix::Delete/$1');
$routes->post('OntologyPrefix/Delete/(:num)', 'OntologyPrefix::Delete/$1');

$routes->get('OntologyRelationship', 'OntologyRelationship::Index');
$routes->get('OntologyRelationship/Index', 'OntologyRelationship::Index');
$routes->get('OntologyRelationship/List/(:num)', 'OntologyRelationship::List/$1');
$routes->get('OntologyRelationship/Create/(:num)', 'OntologyRelationship::Create/$1');
$routes->post('OntologyRelationship/Create/(:num)', 'OntologyRelationship::Create/$1');
$routes->get('OntologyRelationship/Update/(:num)', 'OntologyRelationship::Update/$1');
$routes->post('OntologyRelationship/Update/(:num)', 'OntologyRelationship::Update/$1');
$routes->get('OntologyRelationship/Delete/(:num)', 'OntologyRelationship::Delete/$1');
$routes->post('OntologyRelationship/Delete/(:num)', 'OntologyRelationship::Delete/$1');

$routes->get('Page', 'Page::Index');
$routes->get('Page/Index', 'Page::Index');
$routes->get('Page/List', 'Page::List');
$routes->get('Page/Create', 'Page::Create');
$routes->post('Page/Create', 'Page::Create');
$routes->get('Page/Update/(:num)', 'Page::Update/$1');
$routes->post('Page/Update/(:num)', 'Page::Update/$1');
$routes->get('Page/Activate/(:num)', 'Page::Activate/$1');
$routes->get('Page/Deactivate/(:num)', 'Page::Deactivate/$1');
$routes->get('Page/Delete/(:num)', 'Page::Delete/$1');
$routes->post('Page/Delete/(:num)', 'Page::Delete/$1');

$routes->get('Pipeline', 'Pipeline::Index');
$routes->get('Pipeline/Index', 'Pipeline::Index');
$routes->get('Pipeline/List', 'Pipeline::List');
$routes->get('Pipeline/Delete/(:num)', 'Pipeline::Delete/$1');
$routes->post('Pipeline/Delete/(:num)', 'Pipeline::Delete/$1');
$routes->get('Pipeline/Create', 'Pipeline::Create');
$routes->post('Pipeline/Create', 'Pipeline::Create');
$routes->get('Pipeline/Details/(:num)', 'Pipeline::Details/$1');
$routes->get('Pipeline/Update/(:num)', 'Pipeline::Update/$1');
$routes->post('Pipeline/Update/(:num)', 'Pipeline::Update/$1');

$routes->get('ProxyServer', 'ProxyServer::Index');
$routes->get('ProxyServer/Index', 'ProxyServer::Index');
$routes->get('ProxyServer/List', 'ProxyServer::List');
$routes->get('ProxyServer/Create', 'ProxyServer::Create');
$routes->post('ProxyServer/Create', 'ProxyServer::Create');
$routes->get('ProxyServer/Update/(:num)', 'ProxyServer::Update/$1');
$routes->post('ProxyServer/Update/(:num)', 'ProxyServer::Update/$1');
$routes->get('ProxyServer/Details/(:num)', 'ProxyServer::Details/$1');
$routes->get('ProxyServer/Delete/(:num)', 'ProxyServer::Delete/$1');
$routes->post('ProxyServer/Delete/(:num)', 'ProxyServer::Delete/$1');

$routes->post('QueryApi/Query', 'QueryApi::Query');
$routes->post('QueryAPI/Query', 'QueryApi::Query');
$routes->post('QueryApi/getJSONDataModificationTime', 'QueryApi::getJSONDataModificationTime');
$routes->post('QueryApi/getEAVJSON', 'QueryApi::getEAVJSON');
$routes->post('QueryApi/getHPOJSON', 'QueryApi::getHPOJSON');

$routes->get('Server', 'Server::Index');
$routes->get('Server/Index', 'Server::Index');
$routes->get('Server/List', 'Server::List');
$routes->get('Server/Create', 'Server::Create');
$routes->post('Server/Create', 'Server::Create');
$routes->get('Server/Update/(:num)', 'Server::Update/$1');
$routes->post('Server/Update/(:num)', 'Server::Update/$1');
$routes->get('Server/Details/(:num)', 'Server::Details/$1');
$routes->get('Server/Delete/(:num)', 'Server::Delete/$1');
$routes->post('Server/Delete/(:num)', 'Server::Delete/$1');

$routes->get('ServiceApi/PollTasks', 'ServiceAPI::PollTasks');
$routes->get('ServiceAPI/PollTasks', 'ServiceAPI::PollTasks');
$routes->post('ServiceApi/PollTask', 'ServiceAPI::PollTask');
$routes->post('ServiceAPI/PollTask', 'ServiceAPI::PollTask');

$routes->get('Setting/Discovery', 'Setting::Discovery');
$routes->post('Setting/Discovery', 'Setting::Discovery');
$routes->get('Setting/Endpoint', 'Setting::Endpoint');
$routes->post('Setting/Endpoint', 'Setting::Endpoint');
$routes->get('Setting/Main', 'Setting::Main');
$routes->post('Setting/Main', 'Setting::Main');
$routes->get('Setting/Elasticsearch', 'Setting::Elasticsearch');
$routes->post('Setting/Elasticsearch', 'Setting::Elasticsearch');
$routes->get('Setting/Neo4J', 'Setting::Neo4J');
$routes->post('Setting/Neo4J', 'Setting::Neo4J');

$routes->get('SingleSignOnProvider', 'SingleSignOnProvider::Index');
$routes->get('SingleSignOnProvider/Index', 'SingleSignOnProvider::Index');
$routes->get('SingleSignOnProvider/List', 'SingleSignOnProvider::List');
$routes->get('SingleSignOnProvider/Create', 'SingleSignOnProvider::Create');
$routes->post('SingleSignOnProvider/Create', 'SingleSignOnProvider::Create');
$routes->get('SingleSignOnProvider/Update/(:num)', 'SingleSignOnProvider::Update/$1');
$routes->post('SingleSignOnProvider/Update/(:num)', 'SingleSignOnProvider::Update/$1');
$routes->get('SingleSignOnProvider/Details/(:num)', 'SingleSignOnProvider::Details/$1');
$routes->get('SingleSignOnProvider/Delete/(:num)', 'SingleSignOnProvider::Delete/$1');
$routes->post('SingleSignOnProvider/Delete/(:num)', 'SingleSignOnProvider::Delete/$1');

$routes->get('Source', 'Source::Index');
$routes->get('Source/Index', 'Source::Index');
$routes->get('Source/List', 'Source::List');
$routes->get('Source/Create', 'Source::Create');
$routes->post('Source/Create', 'Source::Create');
$routes->get('Source/Update/(:num)', 'Source::Update/$1');
$routes->post('Source/Update/(:num)', 'Source::Update/$1');
$routes->get('Source/Delete/(:num)', 'Source::Delete/$1');
$routes->post('Source/Delete/(:num)', 'Source::Delete/$1');
$routes->get('Source/Elasticsearch/(:num)', 'Source::Elasticsearch/$1');
$routes->get('Source/Neo4J/(:num)', 'Source::Neo4J/$1');
$routes->get('Source/UserInterface/(:num)', 'Source::UserInterface/$1');

$routes->cli('Task/Start/(:num)', 'Task::Start/$1');
$routes->cli('Task/CreateBatchTasksForDataFiles/(:any)', 'Task::CreateBatchTasksForDataFiles/$1');
$routes->cli('Task/StartService', 'Task::StartService');

$routes->get('User', 'User::Index');
$routes->get('User/Index', 'User::Index');
$routes->get('User/List', 'User::List');
$routes->get('User/Create', 'User::Create');
$routes->post('User/Create', 'User::Create');
$routes->get('User/Update/(:num)', 'User::Update/$1');
$routes->post('User/Update/(:num)', 'User::Update/$1');
$routes->get('User/Details/(:num)', 'User::Details/$1');
$routes->get('User/Delete/(:num)', 'User::Delete/$1');
$routes->post('User/Delete/(:num)', 'User::Delete/$1');

$routes->get('UserInterfaceAPI/GetUIConstants', 'UserInterfaceAPI::GetUIConstants');

$routes->get('Value', 'Value::Index');
$routes->get('Value/Index', 'Value::Index');
$routes->get('Value/List/(:num)', 'Value::List/$1');
$routes->get('Value/Details/(:num)', 'Value::Details/$1');
$routes->get('Value/Update/(:num)', 'Value::Update/$1');
$routes->post('Value/Update/(:num)', 'Value::Update/$1');

$routes->get('ValueMapping', 'ValueMapping::Index');
$routes->get('ValueMapping/Index', 'ValueMapping::Index');
$routes->get('ValueMapping/List/(:num)', 'ValueMapping::List/$1');
$routes->get('ValueMapping/Create/(:num)', 'ValueMapping::Create/$1');
$routes->post('ValueMapping/Create/(:num)', 'ValueMapping::Create/$1');
$routes->get('ValueMapping/Delete/(:num)', 'ValueMapping::Delete/$1');
$routes->post('ValueMapping/Delete/(:num)', 'ValueMapping::Delete/$1');

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
