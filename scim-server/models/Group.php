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
 
 class Group
 {
	function parseFromSCIMResource($groupJsonData)
	{
		$group = array(
			'id' => null,
			'displayName' => null,
			'members' => array()
		);
		
		$group['id'] = $groupJsonData->id;
		$group['displayName'] = $groupJsonData->displayName;
		
		$members = array();
		
		for($i = 0; $i < count($groupJsonData->members); $i++)
			$members[] = $this->parseMemberships($groupJsonData->members[$i]);

		$groups['members'] = $members;

        return $group;
	}
	
	function parseMemberships($groupMembersJsonData)
	{
		$member = array(
			'value' => null,
			'ref' => null,
			'display' => null
		);
		
		$member['value'] = $groupMembersJsonData->value;
		$member['$ref'] = $groupMembersJsonData->ref;
		$member['display'] = $groupMembersJsonData->display;
		
		return $member;
	}
	
	function createUser($userId, $displayName)
	{
		$user = array(
			'value' => null,
			'$ref' => null,
			'display' => null
		);
		
		$user['value'] = $userId;
		$user['$ref'] = '../Users/' . $userId;
		$user['display'] = $displayName;
		
		return $user; 
	}
 }