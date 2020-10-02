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
require_once "models/User.php";

$scimCore = new SCIMCore();
$db = new Database();
$user = new User();

class Users
{	
	function listUsers()
	{
		global $db;
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		$startIndex = $_GET['startIndex'];
		$count = $_GET['count'];
		$filter = $_GET['filter'];
		
		if($startIndex < 1)
			$startIndex=1;
		
		if($filter != "")
		{
			$attributeName = trim(explode("eq",$filter)[0]);
			$attributeValue = explode('"',trim(explode("eq",$filter)[1]))[1];
			
			$result = $db->getFilteredUsers($attributeName, $attributeValue, $startIndex, $count, $reqUrl);
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
			$result = $db->getAllUsers($startIndex, $count, $reqUrl);
			
			if (isset($result['status'])) 
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
	
	function getUser()
	{
		global $db;
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		$userId = explode("/",$reqUrl)[count(explode("/",$reqUrl))-1];
		
		$result = $db->getUser($userId, $reqUrl);
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
	
	function createUser()
	{
		global $db, $user;
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		
		$userModel = $user->parseFromSCIMResource(json_decode(file_get_contents('php://input'), true));
		$result = $db->createUser($userModel, $reqUrl);

		if(isset($result['status']))
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
	
	function patchUser()
	{
		global $db, $scimUser;
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		$userId = explode("/",$reqUrl)[count(explode("/",$reqUrl))-1];
		
		$jsonReqBody = json_decode(file_get_contents('php://input'), true);
		
		$operation = $jsonReqBody["Operations"][0]["op"];
        $value = $jsonReqBody["Operations"][0]["value"];
		
		if($operation == "replace")
		{
			$attribute = key($value);
			$attributeValue = $value[$attribute];
			if($attributeValue == false)
				$attributeValue = "0";
			elseif($attributeValue == true)
				$attributeValue = 1;

			$result = $db->patchUser($attribute, $attributeValue, $userId, $reqUrl);

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
		}else{
			header("Content-Type: text/json",true,403);
			echo json_encode($scimCore->createSCIMError("Operation Not Supported", "403"));
		}
	}
	
	function updateUser()
	{
		global $db, $scimUser, $user;
		
		$reqUrl = $_SERVER['REQUEST_URI'];
		$userId = explode("/",$reqUrl)[count(explode("/",$reqUrl))-1];
		$jsonReqBody = json_decode(file_get_contents('php://input'), true);
		
		$userModel = $user->parseFromSCIMResource($jsonReqBody);
		
		$result = $db->updateUser($userModel, $userId, $reqUrl);

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
}