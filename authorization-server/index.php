<?php
/**
 * PHP simple authorization server
 * Author: Dragos Gaftoneanu <dragos.gaftoneanu@okta.com>
 * 
 * Disclaimer: This is a simple proof of concept authorization server created as an OpenID Connect Identity Provider for Okta. The script is provided AS IS 
 * without warranty of any kind. Okta disclaims all implied warranties including, without limitation, any implied warranties of fitness for a particular purpose. We highly
 * recommend testing scripts in a preview environment if possible.
 */
 
/* Modify the values below in order to reflect the settings in Okta */
$redirect_uri = "https://company.okta.com/oauth2/v1/authorize/callback"; // redirect URI used for sending the authorization code back to Okta
$issuer = "https://example.com"; // Issuer to be injected inside access token and ID token (eg. https://example.com)
$audience = "https://company.okta.com"; // Issuer to be injected inside access token and ID token (eg. https://company.okta.com)
$user = "user@example.com"; // The Okta email address of the user that will be logged in
$exp = 3600; // Access token and ID token lifetime (in seconds)
$client_id = "my-app"; // Client ID used for providing the ID token and access token
$client_secret = "my-s3cr3t-k3y"; // Client secret used for providing the access token and ID token
$scopes = array("openid", "profile", "email"); // scopes used to access the authorization server

/* Private and public key initialization */
$privkey = file_get_contents("cert.txt");
$pubkey = (openssl_pkey_get_details(openssl_pkey_get_private($privkey)));

/* Access token generation */
$access_token = str_replace('=', '', strtr(base64_encode(
	json_encode(array(
		"kid" => "simple-oidc-server", 
		"alg" => "RS256"
	)))
, '+/', '-_')) . "." . str_replace('=', '', strtr(base64_encode(
	json_encode(array(
		"ver" => 1,
		"iss" => $issuer,
		"aud" => $audience,
		"iat" => time(),
		"exp" => time() + $exp,
		"scp" => array($scopes)
	)))
, '+/', '-_'));
openssl_sign($access_token, $signature, $privkey, "SHA256");
$access_token = $access_token . "." . str_replace('=', '', strtr(base64_encode($signature), '+/', '-_'));

/* ID token generation */
$id_token = str_replace('=', '', strtr(base64_encode(
	json_encode(array(
		"kid" => "simple-oidc-server", 
		"alg" => "RS256"
	)))
, '+/', '-_')) . "." . str_replace('=', '', strtr(base64_encode(
	json_encode(array(
		"sub" => $user,
		"ver" => 1,
		"iss" => $issuer,
		"aud" => $audience,
		"iat" => time(),
		"exp" => time() + $exp,
		"email" => $user,
		"email_verified" => true
	)))
, '+/', '-_')) ;
openssl_sign($id_token, $signature, $privkey, "SHA256");
$id_token = $id_token . "." . str_replace('=', '', strtr(base64_encode($signature), '+/', '-_'));

/* Userinfo claims */
$userinfo = array(
	"sub" => $user,
	"email" => $user,
	"email_verified" => true
);

/* Endpoints */
if($_SERVER['REQUEST_METHOD'] == "GET" && preg_match('/^(.*)\/authorize$/', explode("?", @$_SERVER['REQUEST_URI'])[0]))
{
	if($_GET['client_id'] == $client_id && $_GET['redirect_uri'] == $redirect_uri)
		header("Location: " . $redirect_uri ."?code=" . md5($_SERVER['REMOTE_ADDR'] . "rand0mn0m") . "&state=" . $_GET['state'], true, 302);
	else{
		header("Content-Type: application/json", true, 400);
		if($_GET['client_id'] != $client_id)
			echo json_encode(array("error" => "invalid_client", "error_description" => "The client ID specified is invalid."));
		else
			echo json_encode(array("error" => "invalid_redirect_uri", "error_description" => "The redirect URI provided is invalid."));
	}
}elseif($_SERVER['REQUEST_METHOD'] == "POST" && preg_match('/^(.*)\/token$/', explode("?", @$_SERVER['REQUEST_URI'])[0]))
{
	if($_POST['client_id'] == $client_id && $_POST['client_secret'] == $client_secret && $_POST['code'] == md5($_SERVER['REMOTE_ADDR'] . "rand0mn0m") && $_POST['grant_type'] == "authorization_code" && $_POST['redirect_uri'] == $redirect_uri ."")
	{
		header('Content-Type: application/json', true, 200);	
		echo json_encode(array(
			"access_token" => $access_token,
			"id_token" => $id_token,
			"expires_in" => $exp,
			"scope" => "openid",
			"token_type" => "bearer"
		));
	}else{
		header("Content-Type: application/json", true, 400);
		if($_POST['client_id'] != $client_id)
			echo json_encode(array("error" => "invalid_client", "error_description" => "The client ID specified is invalid."));
		elseif($_POST['client_secret'] != $client_secret)
			echo json_encode(array("error" => "invalid_client", "error_description" => "The client secret specified is invalid."));
		elseif($_POST['code'] != md5($_SERVER['REMOTE_ADDR'] . "rand0mn0m"))
			echo json_encode(array("error" => "invalid_code", "error_description" => "The code specified is invalid."));
		elseif($_POST['grant_type'] != "authorization_code")
			echo json_encode(array("error" => "invalid_grant", "error_description" => "The grant specified is invalid."));
		elseif($_POST['redirect_uri'] != $redirect_uri)
			echo json_encode(array("error" => "invalid_redirect_uri", "error_description" => "The redirect URI provided is invalid."));
	}
}elseif($_SERVER['REQUEST_METHOD'] == "GET" && preg_match('/^(.*)\/keys$/', explode("?", @$_SERVER['REQUEST_URI'])[0]))
{
	header('Content-Type: application/json', true, 200);
	echo json_encode(
		array(
			"keys" => array(
				array(
					"kty" => "RSA",
					"alg" => "RS256",
					"kid" => "simple-oidc-server",
					"use" => "sig",
					"e" => str_replace('=', '', strtr(base64_encode($pubkey['rsa']['e']), '+/', '-_')),
					"n" => str_replace('=', '', strtr(base64_encode($pubkey['rsa']['n']), '+/', '-_'))
				)
			)
		)
	);
}elseif($_SERVER['REQUEST_METHOD'] == "GET" && preg_match('/^(.*)\/userinfo$/', explode("?", @$_SERVER['REQUEST_URI'])[0]))
{
	header('Content-Type: application/json', true, 200);
	echo json_encode($userinfo);
	
/* Admin endpoints */
}elseif($_SERVER['REQUEST_METHOD'] == "GET" && preg_match('/^(.*)\/admin-genprivkey$/', explode("?", @$_SERVER['REQUEST_URI'])[0]))
{
	$config = array(
		"digest_alg" => "sha256",
		"private_key_bits" => 2048,
		"private_key_type" => OPENSSL_KEYTYPE_RSA,
	);

	$res = openssl_pkey_new($config);
	openssl_pkey_export($res, $privKey);
	$fh = fopen("cert.txt", "w");
	fwrite($fh, $privKey);
	fclose($fh);
	
	header('Content-Type: text/plain', true, 200);
	echo "A new private key was successfully generated.";
}elseif($_SERVER['REQUEST_METHOD'] == "GET" && preg_match('/^(.*)\/admin-getall$/', explode("?", @$_SERVER['REQUEST_URI'])[0]))
{
	header('Content-Type: text/plain', true, 200);
	echo $privkey . "\n\n";
	echo $pubkey['key'] . "\n\n";
	echo "e=" . str_replace('=', '', strtr(base64_encode($pubkey['rsa']['e']), '+/', '-_')) . "\n\n";
	echo "n=" . str_replace('=', '', strtr(base64_encode($pubkey['rsa']['n']), '+/', '-_')) . "\n\n";
	echo "access_token=" . $access_token . "\n\n";
	echo "id_token=" . $id_token;
}