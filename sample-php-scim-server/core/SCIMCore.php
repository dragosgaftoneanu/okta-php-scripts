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
 
class SCIMCore 
{
	function returnServiceProviderConfig()
	{
		return array(
			'schemas' => array(array("urn:ietf:params:scim:schemas:core:2.0:ServiceProviderConfig")),
			'patch' => array('supported' => true),
			'bulk' => array('supported' => false),
			'filter' => array('supported' => true),
			'changePassword' => array('supported' => false),
			'sort'=>array('supported' => false),
			'etag'=>array('supported' => false),
			'authenticationSchemes'=>array(
				array('name'=>'HTTP Basic','type'=>'httpbasic')
			)
		);
	}
	
	function createSCIMUserList($rows, $startIndex, $count, $reqUrl, $totalResults)
	{
		if(stristr($reqUrl,'?'))
			$reqUrl = explode("?",$reqUrl)[0];
		
		$scimResource = array(
			'Resources' => array(),
			'itemsPerPage' => 0,
			'schemas' => array("urn:ietf:params:scim:api:messages:2.0:ListResponse"),
			'startIndex' => 0
		);
		
		$resources = array();
		$location = '';
		
		if(empty($rows))
		{
			$scimResource['Resources'] = $resources;
		}else{
			foreach($rows as $row)
			{
				if(!empty($row))
				{
					$location = $reqUrl . "/" . $row['id'];
					
					$resources[] = $this->parseSCIMUser($row, $location);
					$location = '';
				}
			}
			
			$scimResource['Resources'] = $resources;
			$scimResource['startIndex'] = $startIndex;
			$scimResource['itemsPerPage'] = $count;
			$scimResource['totalResults'] = $totalResults;
		}
		return $scimResource;
	}
	
	function createSCIMGroupList($rows, $startIndex, $count, $reqUrl, $totalResults)
	{
		if(stristr($reqUrl,'?'))
			$reqUrl = explode("?",$reqUrl)[0];
		
		$scimResource = array(
			'Resources' => array(),
			'itemsPerPage' => 0,
			'schemas' => array("urn:ietf:params:scim:api:messages:2.0:ListResponse"),
			'startIndex' => 0
		);
		
		$resources = array();
		$location = "";
		
		if(empty($rows))
		{
			$scimResource['Resources'] = $resources;
			$scimResource['startIndex'] = $startIndex;
			$scimResource['itemsPerPage'] = $count;
		}else{
			foreach($rows as $row)
			{
				if(!empty($row))
				{
					$location = $reqUrl . "/" . $row['id'];
					
					$resources[] = $this->parseSCIMGroup($row, $location);
					$location = '';
				}
			}
		}
		
		$scimResource['Resources'] = $resources;
		$scimResource['startIndex'] = $startIndex;
		$scimResource['itemsPerPage'] = $count;
		$scimResource['totalResults'] = $totalResults;
		
		return $scimResource;
	}
	
	function parseSCIMUser($row, $reqUrl)
	{
		return $this->createSCIMUser($row['id'], $row['active'], $row['userName'], $row['givenName'], $row['middleName'], $row['familyName'], $row['email'], $row['groups'], $reqUrl);
	}
	
	function createSCIMUser($userId, $active, $userName, $givenName, $middleName, $familyName, $email, $groups, $reqUrl)
	{
		if(stristr($reqUrl,'?'))
			$reqUrl = explode("?",$reqUrl)[0];
		
		$scimUser = array(
			'schemas' => array("urn:ietf:params:scim:schemas:core:2.0:User"),
			'id' => null,
			'userName' => null,
			'name' => array(
				'givenName' => null,
				'middleName' => null,
				'familyName' => null
			),
			'emails' => array(
				array(
					'primary' => true,
					'value' => null,
					'type' => 'work',
					'display' => null
				)
			),
			'active' => false,
			'groups' => array(),
			'meta' => array(
				'resourceType' => 'User',
				'location' => null
			)
		);
		
        $scimUser['meta']['location'] = $this->getURL()['scheme'] . "://" . $this->getURL()['host'] . $reqUrl;
        $scimUser['id'] = $userId;
		
		if($active == 1)
			$scimUser['active'] = true;
		else
			$scimUser['active'] = false;
		
		
        $scimUser['userName'] = $userName;
        $scimUser['name']['givenName'] = $givenName;
        $scimUser['name']['middleName'] = $middleName;
        $scimUser['name']['familyName'] = $familyName;
        $scimUser['emails'][0]['value'] = $email;
		$scimUser['emails'][0]['display'] = $email;
        $scimUser['groups'] = $groups;

        return $scimUser;	
	}
	
	function parseSCIMGroup($row, $reqUrl)
	{
		return $this->createSCIMGroup($row['id'], $row['displayName'], $row['members'], $reqUrl);
	}
	
	function createSCIMGroup($groupId, $displayName, $members, $reqUrl)
	{
		if(stristr($reqUrl,'?'))
			$reqUrl = explode("?",$reqUrl)[0];
	
		$scimGroup = array(
			'schemas' => array("urn:ietf:params:scim:schemas:core:2.0:Group"),
			'id' => null,
			'displayName' => null,
			'members' => array(),
			'meta' => array(
				'resourceType' => 'Group',
				'location' => null
			)
		);
		
		$scimGroup['id'] = $groupId;
        $scimGroup['displayName'] = $displayName;
        $scimGroup['members'] = $members;
        $scimGroup['meta']['location'] = $this->getURL()['scheme'] . "://" . $this->getURL()['host'] . $reqUrl;

        return $scimGroup;
	}
	
	function createSCIMError($errorMessage, $statusCode)
	{
		$scimError = array(
			'schemas' => array("urn:ietf:params:scim:api:messages:2.0:Error"),
			'detail' => null,
			'status' => null
		);
		
		$scimError['detail'] = $errorMessage;
		$scimError['status'] = $statusCode;
		
		return $scimError;
	}
	
	function getURL()
	{
		return parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
	}
}
