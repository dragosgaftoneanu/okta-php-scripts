<?php
require_once "core/SCIMCore.php";
require_once "core/Database.php";

$scimCore = new SCIMCore();

class ServiceProviderConfig
{
	function listConfig()
	{
		global $scimCore;
		
		echo json_encode($scimCore->returnServiceProviderConfig());
	}
}