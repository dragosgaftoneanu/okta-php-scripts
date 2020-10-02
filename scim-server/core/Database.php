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

require_once "configuration.php";
require_once "SCIMCore.php";

$scimCore = new SCIMCore();
$mysql = mysqli_connect($database_server, $database_username, $database_password, $database_name) or die("Connection to database failed.");

class Database
{	
	function dbInit()
	{
		global $mysql, $database_name;
		
		if($mysql->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME='Users' and TABLE_SCHEMA='$database_name'")->num_rows == 0)
			$mysql->query("CREATE TABLE IF NOT EXISTS `Users` (`id` varchar(255) COLLATE utf8_bin NOT NULL,`active` int(11) NOT NULL,`userName` varchar(255) COLLATE utf8_bin NOT NULL,`givenName` varchar(255) COLLATE utf8_bin NOT NULL,`middleName` varchar(255) COLLATE utf8_bin NOT NULL,`familyName` varchar(255) COLLATE utf8_bin NOT NULL,`email` varchar(255) COLLATE utf8_bin NOT NULL,PRIMARY KEY (`id`)) ENGINE = MyISAM;");
		
		if($mysql->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME='Groups' and TABLE_SCHEMA='$database_name'")->num_rows == 0)
			$mysql->query("CREATE TABLE IF NOT EXISTS `Groups` ( `id` VARCHAR(255) NOT NULL , `displayName` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;");

		if($mysql->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME='GroupMemberships' and TABLE_SCHEMA='$database_name'")->num_rows == 0)
			$mysql->query("CREATE TABLE IF NOT EXISTS `GroupMemberships` (`Id` varchar(255) COLLATE utf8_bin NOT NULL,`groupId` varchar(255) COLLATE utf8_bin NOT NULL,`userId` varchar(255) COLLATE utf8_bin NOT NULL, PRIMARY KEY (`Id`)) ENGINE=MyISAM;");
	}
	
	function getFilteredUsers($filterAttribute, $filterValue, $startIndex, $count, $reqUrl)
	{
		global $mysql, $scimCore;
		
		$filterAttribute = htmlentities($filterAttribute, ENT_QUOTES);
		$filterValue = htmlentities($filterValue, ENT_QUOTES);
		$startIndex = (int) $startIndex;
		$count = (int) $count;
		
		$query = $mysql->query("SELECT * FROM Users WHERE " . $filterAttribute . "='" . $filterValue . "' LIMIT " . ($startIndex - 1) . "," . $count);
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		elseif($query->num_rows == 0)
			return $scimCore->createSCIMError("User not found",404);
				
		$rows = array();
		
		while($row = $query->fetch_array(MYSQLI_ASSOC))
			$rows[] = $row;
			
		for($i=0; $i< count($rows); $i++)
			$rows[$i]['groups'] = $this->getGroupsForUser($rows[$i]['id']);
		
		return $scimCore->createSCIMUserList($rows, $startIndex, $count, $reqUrl, $mysql->query("SELECT * FROM Users WHERE " . $filterAttribute . "='" . $filterValue . "'")->num_rows);
	}
	
	function getFilteredGroups($filterAttribute, $filterValue, $startIndex, $count, $reqUrl)
	{
		global $mysql, $scimCore;
		
		$filterAttribute = htmlentities($filterAttribute, ENT_QUOTES);
		$filterValue = htmlentities($filterValue, ENT_QUOTES);
		$startIndex = (int) $startIndex;
		$count = (int) $count;
		
		$query = $mysql->query("SELECT * FROM Groups WHERE " . $filterAttribute . "='" . $filterValue . "' LIMIT " . ($startIndex - 1) . "," . $count);
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		elseif($query->num_rows == 0)
			return $scimCore->createSCIMError("Group not found",404);
				
		$rows = array();
		
		while($row = $query->fetch_array())
			$rows[] = $row;
		
		for($i=0; $i< count($rows); $i++)
			$rows[$i]['members'] = $this->getUsersForGroup($rows[$i]['id']);
		
		return $scimCore->createSCIMGroupList($rows, $startIndex, $count, $reqUrl, $mysql->query("SELECT * FROM Groups WHERE " . $filterAttribute . "='" . $filterValue . "'")->num_rows);
	}
	
	function getAllUsers($startIndex, $count, $reqUrl)
	{
		global $mysql, $scimCore;
		$startIndex = (int) $startIndex;
		$count = (int) $count;
		
		$query = $mysql->query("SELECT * FROM Users LIMIT " . ($startIndex - 1) . "," . $count);
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		elseif($query->num_rows == 0)
			return $scimCore->createSCIMUserList(array(), $startIndex, $count, $reqUrl, $count);
				
		$rows = array();
		
		while($row = $query->fetch_array())
			$rows[] = $row;
		
		for($i=0; $i< count($rows); $i++)
			$rows[$i]['groups'] = $this->getGroupsForUser($rows[$i]['id']);
		
		return $scimCore->createSCIMUserList($rows, $startIndex, $count, $reqUrl, $mysql->query("SELECT * FROM Users")->num_rows);
	}
	
	function getAllGroups($startIndex, $count, $reqUrl)
	{
		global $mysql, $scimCore;
		$startIndex = (int) $startIndex;
		$count = (int) $count;
		
		$query = $mysql->query("SELECT * FROM Groups LIMIT " .  ($startIndex - 1) . "," . $count);
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
				
		$rows = array();
		
		while($row = $query->fetch_array(MYSQLI_ASSOC))
			$rows[] = $row;
		
		for($i=0; $i< count($rows); $i++)
			$rows[$i]['members'] = $this->getUsersForGroup($rows[$i]['id']);
		
		return $scimCore->createSCIMGroupList($rows, $startIndex, $count, $reqUrl, $mysql->query("SELECT * FROM Groups")->num_rows);
	}
	
	function getUser($userId, $reqUrl)
	{
		global $mysql, $scimCore;
		$userId = htmlentities($userId,ENT_QUOTES);
		
		$query = $mysql->query("SELECT * FROM Users WHERE id='" . $userId . "'");
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		elseif($query->num_rows == 0)
			return $scimCore->createSCIMError("User not found",404);
				
		$rows = array();
		
		$rows = $query->fetch_array(MYSQLI_ASSOC);
		$rows['groups'] = $this->getGroupsForUser($rows['id']);
		
		return $scimCore->parseSCIMUser($rows, $reqUrl);
	}
	
	function getGroup($groupId, $reqUrl)
	{
		global $mysql, $scimCore;
		$groupId = htmlentities($groupId,ENT_QUOTES);
		
		$query = $mysql->query("SELECT * FROM Groups WHERE id='" . $groupId . "'");
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		elseif($query->num_rows == 0)
			return $scimCore->createSCIMError("Group not found",404);
			
		if($query->num_rows < $count)
			$count = $query->num_rows;
				
		$rows = array();

		$rows = $query->fetch_array(MYSQLI_ASSOC);
		$rows['groups'] = $this->getUsersForGroup($rows['id']);
		
		return $scimCore->parseSCIMGroup($rows, $reqUrl);
	}
	
	function createUser($userModel, $reqUrl)
	{
		global $mysql, $scimCore;
		
		$query = $mysql->query("SELECT * FROM Users WHERE userName='" . htmlentities($userModel['userName'],ENT_QUOTES) . "'");
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		elseif($query->num_rows > 0)
			return $scimCore->createSCIMError("Conflict - User already exists",409);
		else
		{
			$userId = $this->gen_uuid();
			
			$query = $mysql->query("INSERT INTO users(`id`, `active`, `userName`, `givenName`, `middleName`, `familyName`, `email`) VALUES ('" . htmlentities($userId,ENT_QUOTES) . "', '" . ((int) $userModel['active']) . "', '" . htmlentities($userModel['userName'],ENT_QUOTES) . "', '" . htmlentities($userModel['givenName'],ENT_QUOTES) . "', '" . htmlentities($userModel['middleName'],ENT_QUOTES) . "', '" . htmlentities($userModel['familyName'],ENT_QUOTES) . "', '" . htmlentities($userModel['email'],ENT_QUOTES) . "')");
			
			if($mysql->error)
				return $scimCore->createSCIMError($mysql->error,400);
			
			$groups = $userModel['groups'];
			
			if(count($groups) == 0)
				return $scimCore->createSCIMUser($userId, true, $userModel['userName'], $userModel['givenName'], $userModel['middleName'], $userModel['familyName'], $userModel['email'], array(), $reqUrl);
			else
			{				
				for($i=0; $i < count($groups); $i++)
				{
					$membershipId = $this->gen_uuid();
					
					$mysql->query("INSERT INTO GroupMemberships (id, groupId, userId) VALUES ('" . htmlentities($membershipId,ENT_QUOTES) . "','" . htmlentities($groups[$i]["value"],ENT_QUOTES) . "','" . htmlentities($userId,ENT_QUOTES) . "')");
					
					if($mysql->error)
						return $scimCore->createSCIMError($mysql->error,400);
				}
				
				return $scimCore->createSCIMUser($userId, true, $userModel['userName'], $userModel['givenName'], $userModel['middleName'], $userModel['familyName'], $userModel['email'], $groups, $reqUrl);
			}
		}
	}
	
	function createGroup($groupModel, $reqUrl)
	{
		global $mysql, $scimCore;

		$query = $mysql->query("SELECT * FROM Groups WHERE displayName='".htmlentities($userModel['display_name'],ENT_QUOTES)."'");
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		elseif($query->num_rows > 0)
			return $scimCore->createSCIMError("Conflict - Group already exists",409);
		else
		{
			$groupId = $this->gen_uuid();
			
			$mysql->query("INSERT INTO Groups (id, displayName) VALUES ('" . htmlentities($groupId,ENT_QUOTES) . "','" . htmlentities($groupModel['displayName'],ENT_QUOTES) . "')");
			
			if($mysql->error)
				return $scimCore->createSCIMError($mysql->error,400);
			
			$members = $groupModel['members'];
			
			if(count($members) == 0)
				return $scimCore->createSCIMGroup($groupId, $groupModel["displayName"], array(), $reqUrl);
			else
			{				
				for($i=0; $i < count($groups); $i++)
				{
					$membershipId = $this->gen_uuid();
					
					$mysql->query("INSERT INTO GroupMemberships (id, groupId, userId) VALUES ('" . htmlentities($membershipId,ENT_QUOTES) . "','" . htmlentities($groupId,ENT_QUOTES) . "','" . htmlentities($members[i]["value"],ENT_QUOTES) . "')");
					
					if($mysql->error)
						return $scimCore->createSCIMError($mysql->error,400);
				}
				
				$query = $mysql->query($query);
				
				if($mysql->error)
					return $scimCore->createSCIMError($mysql->error,400);
				else
					return $scimCore->createSCIMGroup($groupId, $groupModel["displayName"], $members, $reqUrl);
			}
		}
	}
	
	function patchUser($attributeName, $attributeValue, $userId, $reqUrl)
	{
		global $mysql, $scimCore;
		
		$userId = htmlentities($userId, ENT_QUOTES);
		$attributeName = htmlentities($attributeName, ENT_QUOTES);
		$attributeValue = htmlentities($attributeValue, ENT_QUOTES);
		
		$query = $mysql->query("UPDATE Users SET " . $attributeName . " = " . $attributeValue . " WHERE id = '" . $userId . "'");

		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		else
		{
			$query = $mysql->query("SELECT * FROM Users WHERE id = '" . $userId . "'");
			if($mysql->error)
				return $scimCore->createSCIMError($mysql->error,400);
			else
			{
				$rows = $query->fetch_array();
				
				$rows["groups"] = $this->getGroupsForUser($rows['id']);
				
				return $scimCore->parseSCIMUser($rows, $reqUrl);
			}
		}
	}
	
	function patchGroup($attributeName, $attributeValue, $groupId, $reqUrl)
	{
		global $mysql, $scimCore;
		
		$groupId = htmlentities($groupId, ENT_QUOTES);
		$attributeName = htmlentities($attributeName, ENT_QUOTES);
		$attributeValue = htmlentities($attributeValue, ENT_QUOTES);
		
		$query = $mysql->query("UPDATE Groups SET " . $attributeName . " = '" . $attributeValue . "' WHERE id = '" . $groupId . "'");
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		elseif($query->num_rows == 0)
			return $scimCore->createSCIMError("User not found",404);
		else
		{
			$query = $mysql->query("SELECT * FROM Groups WHERE id = '" . $groupId . "'");
			if($mysql->error)
				return $scimCore->createSCIMError($mysql->error,400);
			else
			{
				$rows = $query->fetch_array();
				
				$rows["groups"] = $this->getUsersForGroup($rows['id']);
				
				return $scimCore->parseSCIMGroup($rows, $reqUrl);
			}
		}
	}
	
	function UpdateUser($userModel, $userId, $reqUrl)
	{
		global $mysql, $scimCore;
		
		$query = $mysql->query("SELECT * FROM Users WHERE id = '" . htmlentities($userId,ENT_QUOTES) . "'");

		$userModel['active'] = (int) $userModel['active'];
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		elseif($query->num_rows == 0)
			return $scimCore->createSCIMError("User not found",404);
		else
		{
			$query = $mysql->query("UPDATE Users SET userName='" . htmlentities($userModel['userName'], ENT_QUOTES) . "', givenName='" . htmlentities($userModel['givenName'], ENT_QUOTES) . "', middleName='" . htmlentities($userModel['middleName'], ENT_QUOTES) . "', familyName='" . htmlentities($userModel['familyName'], ENT_QUOTES) . "', email='" . htmlentities($userModel['email'], ENT_QUOTES) . "', active='".$userModel['active']."' where id='" . htmlentities($userId, ENT_QUOTES) . "'");
			
			if($mysql->error)
				return $scimCore->createSCIMError($mysql->error,400);
			else
			{
				$groups = $userModel['groups'];
				
				if(count($groups) == 0)
					return $scimCore->createSCIMUser($userId, $userModel['active'], $userModel['userName'], $userModel['givenName'], $userModel['middleName'], $userModel['familyName'], $userModel['email'], null, $reqUrl);
				else
				{
					$membershipId = null;
					
					$query = "INSERT INTO GroupMemberships (id, groupId, userId) VALUES ";
					for ($i = 0; $i < count($groups); $i++)
					{
						if ($i > 0)
							$query = $query + ",";

						$membershipId = $this->gen_uuid();

						$query .= " ('" + $membershipId + "', '" + $groups[$i]["value"] + "', '" + $userId + "')";
					}
					
					$query = $mysql->query($query);
					if($mysql->error)
						return $scimCore->createSCIMError($mysql->error,400);
					else
					{
						return $scimCore->createSCIMUser($userId, $userModel['active'], $userModel['userName'], $userModel['givenName'], $userModel['middleName'], $userModel['familyName'], $userModel['email'], $groups, $reqUrl);
					}
				}
			}
		}
	}
	
	function updateGroup($groupModel, $groupId, $reqUrl)
	{
		global $mysql, $scimCore;

		$query = $mysql->query("SELECT * FROM Groups WHERE id = '" . htmlentities($groupId, ENT_QUOTES) . "'");
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		elseif($query->num_rows == 0)
			return $scimCore->createSCIMError("Group not found",404);
		else
		{
			$mysql->query("UPDATE Groups SET displayName='" . htmlentities($groupModel['displayName'],ENT_QUOTES) . "' WHERE id='" . htmlentities($groupId,ENT_QUOTES) . "'");
			
			if($mysql->error)
				return $scimCore->createSCIMError($mysql->error,400);
			else
			{
				$members = $groupModel['members'];
			
				if(count($members) == 0)
					return $scimCore->createSCIMGroup($groupId, $groupModel["displayName"], null, $reqUrl);
				else
				{
					$membershipId = null;
					
					$query = "INSERT INTO GroupMemberships (id, groupId, userId) VALUES";
					
					for($i=0; $i < count($groups); $i++)
					{
						if($i > 0)
							$query .= ", ";
						
						$membershipId = $this->gen_uuid();
						
						$query .= " ('" +  $membershipId + "', '" + $members[i]["value"] + "', '" + $groupId + "')";
					}
					
					$query = $mysql->query($query);
					
					if($mysql->error)
						return $scimCore->createSCIMError($mysql->error,400);
					else
						return $scimCore->createSCIMGroup($groupId, $groupModel["displayName"], $members, $reqUrl);
				}
			}
		}
	}
	
	function getGroupsForUser($userId)
	{
		global $mysql, $scimCore;
		
		$userGroups = array();
		
		$query = $mysql->query("SELECT * FROM GroupMemberships WHERE userId='". htmlentities($userId,ENT_QUOTES) ."'");
		
		
		while($f = $query->fetch_array(MYSQLI_ASSOC))
		{
			$userGroups[] = array(
				'groupId' => $f['groupId'],
				'groupDisplay' => $mysql->query("SELECT displayName FROM Groups WHERE id='" . $f['groupId'] . "'")->fetch_array(MYSQLI_ASSOC)['displayName']
			);
		}
		
		$query->close();
		
		return $userGroups;
	}
	
	function getUsersForGroup($groupId)
	{
		global $mysql, $scimCore;
		
		$userGroups = array();
		
		$query = $mysql->query("SELECT * FROM GroupMemberships WHERE groupId='". htmlentities($groupId,ENT_QUOTES) ."'");
		
		while($f = $query->fetch_array(MYSQLI_ASSOC))
		{
			$userGroups[] = array(
				'userId' => $f['userId'],
				'userName' => $mysql->query("SELECT userName FROM Users WHERE id='" . $f['userId'] . "'")->fetch_array(MYSQLI_ASSOC)['userName']
			);
		}
		
		return $userGroups;
	}
	
	function addGroupMembership($userId,$groupId)
	{
		global $mysql, $scimCore;
		
		$query = $mysql->query("SELECT * FROM GroupMemberships WHERE groupId='" . htmlentities($groupId) . "' AND userId='" . htmlentities($userId) . "'");
		
		if($mysql->error)
			return $scimCore->createSCIMError($mysql->error,400);
		
		if($query->num_rows == 0)
		{
			$mysql->query("INSERT INTO GroupMemberships(Id, groupId, userId) VALUES ('" . $this->gen_uuid() . "','" . htmlentities($groupId,ENT_QUOTES) . "','" . htmlentities($userId,ENT_QUOTES) . "')");
		}
	}
	
	function deleteGroup($groupId)
	{
		global $mysql;
		
		$mysql->query("DELETE FROM Groups WHERE id='".htmlentities($groupId,ENT_QUOTES)."'");
		$mysql->query("DELETE FROM GroupMemberships WHERE groupId='".htmlentities($groupId,ENT_QUOTES)."'");
	}
	
	function removeMembersFromGroup($groupId, $list)
	{
		global $mysql;
		
		$query = $mysql->query("SELECT * from GroupMemberships WHERE groupId='" . htmlentities($groupId,ENT_QUOTES) . "'");
		while($f = $query->fetch_array(MYSQLI_ASSOC))
			if(!in_array($f['userId'],$list))
				$mysql->query("DELETE from GroupMemberships WHERE ID='" . $f['Id'] . "'");
	}
	
	function gen_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
}