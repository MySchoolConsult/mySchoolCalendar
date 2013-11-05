<?php
/**
 * Created by PhpStorm.
 * User: fwolbring
 * Date: 05.11.13
 * Time: 14:59
 */

\OCP\App::checkAppEnabled($app);

$RUNTIME_APPTYPES=array('authentication');
OC_App::loadApps($RUNTIME_APPTYPES);

$authBackend = new OC_Connector_Sabre_Auth();
$principalBackend = new OC_Connector_Sabre_Principal();
$caldavBackend = new OC_Connector_Sabre_CalDAV_Backend_MySc();
$requestBackend = new OC_Connector_Sabre_Request();

$rootNode = new Sabre_CalDAV_Principal_Collection($principalBackend);
//$rootNode->disableListing = true;

$calendarRoot = new Sabre_CalDAV_CalendarRootNode($principalBackend, $caldavBackend);

$nodes = array(
    $rootNode,
    $calendarRoot
);

$server = new Sabre_DAV_Server($nodes);
$server->setBaseUri($baseuri);
$server->addPlugin(new Sabre_DAV_Auth_Plugin($authBackend,'ownCloud'));
$server->addPlugin(new Sabre_CalDAV_Plugin());
$server->addPlugin(new Sabre_DAVACL_Plugin());
$server->addPlugin(new Sabre_DAV_Browser_Plugin(false)); // Show something in the Browser, but no upload
$server->addPlugin(new Sabre_CalDAV_ICSExportPlugin());

$server->exec();