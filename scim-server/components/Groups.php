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
 
require_once "core/SCIMCore.php";
require_once "core/Database.php";
require_once "models/Group.php";

$scimCore = new SCIMCore();
$db = new Database();
$group = new Group();

class Groups
{	
	function listGroups()
	{
		global $db;
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		$startIndex = $_GET['startIndex'];
		$count = $_GET['count'];
		$filter = $_GET['filter'];
		
		if($filter != "")
		{
			$attributeName = trim(explode("eq",$filter)[0]);
			$attributeValue = explode('"',trim(explode("eq",$filter)[1]))[1];
			
			$result = $db->getFilteredGroups($attributeName, $attributeValue, $startIndex, $count, $reqUrl);
			if($result['status'] != "")
			{
				if($result['status'] == 400)
					header("Content-Type: text/json",true,400);
				elseif($result['status'] == 409)
					header("Content-Type: text/json",true,409);
			}else{
				header("Content-Type: text/json",true,200);
			}
			
			echo json_encode($result);
		}else{
			$result = $db->getAllGroups($startIndex, $count, $reqUrl);
			
			if($result['status'] != "")
			{
				if($result['status'] == 400)
					header("Content-Type: text/json",true,400);
				elseif($result['status'] == 409)
					header("Content-Type: text/json",true,409);
			}else{
				header("Content-Type: text/json",true,200);
			}
			
			echo json_encode($result);
		}
	}
	
	function getGroup()
	{
		global $db;
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		$groupId = explode("/",$reqUrl)[count(explode("/",$reqUrl))-1];
		
		$result = $db->getGroup($groupId, $reqUrl);
		if($result['status'] != "")
		{
			if($result['status'] == 400)
				header("Content-Type: text/json",true,400);
			elseif($result['status'] == 409)
				header("Content-Type: text/json",true,409);
		}else{
			header("Content-Type: text/json",true,200);
		}
		
		echo json_encode($result);
	}
	
	function createGroup()
	{
		global $db, $group;
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		
		$groupModel = $group->parseFromSCIMResource(json_decode(file_get_contents('php://input')));
		
		$result = $db->createGroup($groupModel, $reqUrl);

		if($result['status'] != "")
		{
			if($result['status'] == 400)
				header("Content-Type: text/json",true,400);
			elseif($result['status'] == 409)
				header("Content-Type: text/json",true,409);
		}else{
			header("Content-Type: text/json",true,201);
		}
		
		echo json_encode($result);
	}
	
	function patchGroup()
	{
		global $db, $scimUser, $scimCore;
		
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		$groupId = explode("/",$reqUrl)[count(explode("/",$reqUrl))-1];
		
		$jsonReqBody = json_decode(file_get_contents('php://input'));
		
		$operation = $jsonReqBody->Operations[0]->op;
        $value = $jsonReqBody->Operations[0]->value;
		
		if($operation == "replace" && $jsonReqBody->Operations[0]->path == "")
		{
			foreach($value as $key => $val)
			{
				$attribute = $key;
				$attributeValue = $val;
				$result = $db->patchGroup($attribute, $attributeValue, $groupId, $reqUrl);

				if($result['status'] != "")
				{
					if($result['status'] == 400)
						header("Content-Type: text/json",true,400);
					elseif($result['status'] == 409)
						header("Content-Type: text/json",true,409);
					elseif($result['status'] == 404)
						header("Content-Type: text/json",true,404);	
				}
			}

			$this->getGroup();
		}elseif($operation == "add" && $jsonReqBody->Operations[0]->path == "members"){
			foreach($value as $user)
			{
				$userId = $user->value;
				
				$db->addGroupMembership($userId,$groupId);

				if($result['status'] != "")
				{
					if($result['status'] == 400)
						header("Content-Type: text/json",true,400);
				}
			}
			
			$this->getGroup();
		}elseif($operation == "replace" && $jsonReqBody->Operations[0]->path == "members"){
			$ids = array();
			
			foreach($value as $user)
				$ids[] = $user->value;
			
			$db->removeMembersFromGroup($groupId, $ids);
				
			$this->getGroup();
		}else{
			header("Content-Type: text/json",true,403);
			echo json_encode($scimCore->createSCIMError("Operation Not Supported", "403"));
		}
	}
	
	function updateGroup()
	{
		global $db, $scimUser, $group;
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		$groupId = explode("/",$reqUrl)[count(explode("/",$reqUrl))-1];
		$jsonReqBody = json_decode(file_get_contents('php://input'));
		
		$groupModel = $group->parseFromSCIMResource($jsonReqBody);
		
		$result = $db->updateGroup($groupModel, $groupId, $reqUrl);

		if($result['status'] != "")
		{
			if($result['status'] == 400)
				header("Content-Type: text/json",true,400);
			elseif($result['status'] == 409)
				header("Content-Type: text/json",true,409);
			elseif($result['status'] == 404)
				header("Content-Type: text/json",true,404);
		}else{
			header("Content-Type: text/json",true,200);
		}
		
		echo json_encode($result);
	}
	
	function deleteGroup()
	{
		global $db;
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		$groupId = explode("/",$reqUrl)[count(explode("/",$reqUrl))-1];
		
		$db->deleteGroup($groupId);
		
		header("Content-Type: text/json",true,204);
	}
}