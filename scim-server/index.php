<?php
/**
 * scimify
 * Author: Dragos Gaftoneanu <dragos.gaftoneanu@okta.com>
 *
 * Disclaimer: This SCIM server was built in order to simulate and troubleshoot different SCIM use-cases and not to be used in production. The script is provided AS IS
 * without warranty of any kind. Okta disclaims all implied warranties including, without limitation, any implied warranties of fitness for a particular purpose. We highly
 * recommend testing scripts in a preview environment if possible.
 */

/* Hide any warning or errors, remove this fields if you need to debug */
error_reporting(0);
ini_set('display_errors',0);

/* Require all necessary classes */
require_once 'core/Authorization.php';
require_once 'core/Database.php';
require_once 'components/Users.php';
require_once 'components/Groups.php';

/* Set authorization header */
$authorization = (new Authorization())->require_basic_auth("user","p@ss");

/* Retrieve URL components, needed to check the path on which the user goes */
$uComponents = (new SCIMCore())->getURL();

/* Initializing the objects */
$db = new Database();
$cUsers = new Users();
$cGroups = new Groups();

/* Creating the database tables if they are not already created */
$db->dbInit();

/* Initiating the parameters if they are not present */
if(!isset($_GET['startIndex']))
	$_GET['startIndex'] = 1;
if(!isset($_GET['count']))
	$_GET['count'] = 100;

/* /Users endpoint */
if($_SERVER['REQUEST_METHOD'] == "GET" && stristr($uComponents['path'],'/scim/v2/Users/'))
	$cUsers->getUser();
elseif($_SERVER['REQUEST_METHOD'] == "GET" && stristr($uComponents['path'],'/scim/v2/Users'))
	$cUsers->listUsers();
elseif($_SERVER['REQUEST_METHOD'] == "POST" && stristr($uComponents['path'],'/scim/v2/Users'))
	$cUsers->createUser();
elseif($_SERVER['REQUEST_METHOD'] == "PATCH" && stristr($uComponents['path'],'/scim/v2/Users/'))
	$cUsers->patchUser();
elseif($_SERVER['REQUEST_METHOD'] == "PUT"  && stristr($uComponents['path'],'/scim/v2/Users/'))
	$cUsers->updateUser();

/* /Groups endpoint */
elseif($_SERVER['REQUEST_METHOD'] == "GET" && stristr($uComponents['path'],'/scim/v2/Groups/'))
	$cGroups->getGroup();
elseif($_SERVER['REQUEST_METHOD'] == "GET" && stristr($uComponents['path'],'/scim/v2/Groups'))
	$cGroups->listGroups();
elseif($_SERVER['REQUEST_METHOD'] == "POST" && stristr($uComponents['path'],'/scim/v2/Groups'))
	$cGroups->createGroup();
elseif($_SERVER['REQUEST_METHOD'] == "PATCH" && stristr($uComponents['path'],'/scim/v2/Groups/'))
	$cGroups->patchGroup();
elseif($_SERVER['REQUEST_METHOD'] == "PUT"  && stristr($uComponents['path'],'/scim/v2/Groups/'))
	$cGroups->updateGroup();
elseif($_SERVER['REQUEST_METHOD'] == "DELETE" && stristr($uComponents['path'],'/scim/v2/Groups/'))
	$cGroups->deleteGroup();