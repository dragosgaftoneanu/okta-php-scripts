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
 
class User
{
	function parseFromSCIMResource($userJsonData)
	{
		$user = array(
			'active' => false,
			'userName' => '',
			'givenName' => '',
			'middleName' => '',
			'familyName' => '',
			'email' => '',
			'groups' => array()
		);
		
        $user['active'] = $userJsonData['active'];
        $user['userName'] = $userJsonData['userName'];
		if(isset($userJsonData['name']['givenName']))
			$user['givenName'] = $userJsonData['name']['givenName'];
		
		if(isset($userJsonData['name']['middleName']))
			$user['middleName'] = $userJsonData['name']['middleName'];
	
		if(isset($userJsonData['name']['familyName']))
			$user['familyName'] = $userJsonData['name']['familyName'];
		
        $user['email'] = $userJsonData['emails'][0]['value'];
		
		$groups = array();
		
		for($i=0; $i < count($userJsonData['groups']); $i++)
			$groups[] = $this->parseGroups($userJsonData['groups'][$i]);
		
		$user['groups'] = $groups;
		
		return $user;
	}
	
	function parseGroups($userGroupJsonData)
	{
		$group = array(
			'value' => null,
			'$ref' => null,
			'display' => null
		);
		
		$group['value'] = $userGroupJsonData['value'];
		$group['$ref'] = $userGroupJsonData['$ref'];
		$group['display'] = $userGroupJsonData['display'];
		
		return $group;
	}
	
	function createGroup($groupId, $displayName)
	{
		$group = array(
			'value' => null,
			'$ref' => null,
			'display' => null
		);
		
		$group['value'] = $groupId;
		$group['$ref'] = '../Groups/' . $groupId;
		$group['display'] = $displayName;
		
		return $group;
	}
}