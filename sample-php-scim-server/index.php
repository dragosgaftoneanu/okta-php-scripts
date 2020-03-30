<?php
/** Copyright © 2018-2019, Okta, Inc.
 *
 *  Licensed under the Apache License, Version 2.0 (the 'License');
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an 'AS IS' BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/* Hide any warning or errors, remove this fields if you need to debug */
error_reporting(0);
ini_set('display_errors',0);

/* Require all necessary classes */
require_once 'core/Database.php';
require_once 'components/Users.php';
require_once 'components/Groups.php';
require_once 'components/ServiceProviderConfig.php';

/* Retrieve URL components, needed to check the path on which the user goes */
$uComponents = (new SCIMCore())->getURL();

/* Initializing the objects */
$db = new Database();
$cUsers = new Users();
$cGroups = new Groups();
$cSPconfig = new ServiceProviderConfig();

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
	
/* /ServiceProviderConfig endpoint */
elseif($_SERVER['REQUEST_METHOD'] == "GET" && stristr($uComponents['path'],'/scim/v2/ServiceProviderConfig'))
	$cSPconfig->listConfig();