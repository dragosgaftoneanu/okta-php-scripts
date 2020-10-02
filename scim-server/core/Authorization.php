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

include "SCIMCore.php";

$scimCore = new SCIMCore();

class Authorization
{
	function get_auth_header()
	{
		return explode(" ", apache_request_headers()['Authorization'])[1];
	}
	
	function require_basic_auth($username, $password)
	{
		global $scimCore;
		
		if($this->get_auth_header() != base64_encode($username . ":" . $password))
		{
			header("Content-Type: text/json",true,401);
			die(json_encode($scimCore->createSCIMError("Authorization header provided is invalid.",401)));
		}
	}
	
	function require_header_auth($token)
	{
		global $scimCore;
		
		if($this->get_auth_header() != $token)
		{
			header("Content-Type: text/json",true,401);
			die(json_encode($scimCore->createSCIMError("Authorization header provided is invalid.",401)));
		}
	}
	
	/* This is just an example oauth bearer token verifier, 
	requiring the access token string instead of checking it's contents */
	function require_oauth_bearer_token($jwt)
	{
		global $scimCore;
		
		if($this->get_auth_header() != $token)
		{
			header("Content-Type: text/json",true,401);
			die(json_encode($scimCore->createSCIMError("Authorization header provided is invalid.",401)));
		}
	}
}