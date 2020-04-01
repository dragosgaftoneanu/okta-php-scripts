<?php
$privkey = file_get_contents("cert.txt");
$pubkey = (openssl_pkey_get_details(openssl_pkey_get_private($privkey)));

$access_token = str_replace('=', '', strtr(base64_encode(
	json_encode(array(
		"kid" => "key1", 
		"alg" => "RS256"
	)))
, '+/', '-_')) . "." . str_replace('=', '', strtr(base64_encode(
	json_encode(array(
		"iss" => "0oa6ozuvvcbxzpGIK2p7",
		"sub" => "0oa6ozuvvcbxzpGIK2p7",
		"aud" => "https://dragos.okta.com/oauth2/v1/token",
		"iat" => time(),
		"exp" => time() + 3600
	)))
, '+/', '-_'));
openssl_sign($access_token, $signature, $privkey, "SHA256");
$access_token = $access_token . "." . str_replace('=', '', strtr(base64_encode($signature), '+/', '-_'));

echo json_encode(array(
		"e" => str_replace('=', '', strtr(base64_encode($pubkey['rsa']['e']), '+/', '-_')),
		"n" => str_replace('=', '', strtr(base64_encode($pubkey['rsa']['n']), '+/', '-_')),
		"access_token" => $access_token
	));