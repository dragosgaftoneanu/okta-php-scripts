<?php
/*
	Copyright (c) 2019 Neuman Vong, Dragos Gaftoneanu

	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

		* Redistributions of source code must retain the above copyright
		  notice, this list of conditions and the following disclaimer.

		* Redistributions in binary form must reproduce the above
		  copyright notice, this list of conditions and the following
		  disclaimer in the documentation and/or other materials provided
		  with the distribution.

		* Neither the name of Neuman Vong nor the names of other
		  contributors may be used to endorse or promote products derived
		  from this software without specific prior written permission.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
	"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
	LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
	A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
	OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
	SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
	LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
	DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
	THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
	OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

function createPemFromModulusAndExponent($n, $e)
{
	$modulus = urlsafeB64Decode($n);
	$publicExponent = urlsafeB64Decode($e);
	$components = array(
		'modulus' => pack('Ca*a*', 2, encodeLength(strlen($modulus)), $modulus),
		'publicExponent' => pack('Ca*a*', 2, encodeLength(strlen($publicExponent)), $publicExponent)
	);
	$RSAPublicKey = pack(
		'Ca*a*a*',
		48,
		encodeLength(strlen($components['modulus']) + strlen($components['publicExponent'])),
		$components['modulus'],
		$components['publicExponent']
	);

	$rsaOID = pack('H*', '300d06092a864886f70d0101010500'); 
	$RSAPublicKey = chr(0) . $RSAPublicKey;
	$RSAPublicKey = chr(3) . encodeLength(strlen($RSAPublicKey)) . $RSAPublicKey;
	$RSAPublicKey = pack(
		'Ca*a*',
		48,
		encodeLength(strlen($rsaOID . $RSAPublicKey)),
		$rsaOID . $RSAPublicKey
	);
	$RSAPublicKey = "-----BEGIN PUBLIC KEY-----\r\n" .
		chunk_split(base64_encode($RSAPublicKey), 64) .
		'-----END PUBLIC KEY-----';
	return $RSAPublicKey;
}

function urlsafeB64Decode($input)
{
	$remainder = strlen($input) % 4;
	if ($remainder) {
		$padlen = 4 - $remainder;
		$input .= str_repeat('=', $padlen);
	}
	return base64_decode(strtr($input, '-_', '+/'));
}
	
function encodeLength($length)
{
	if ($length <= 0x7F) {
		return chr($length);
	}
	$temp = ltrim(pack('N', $length), chr(0));
	return pack('Ca*', 0x80 | strlen($temp), $temp);
}

error_reporting(0);
?><!DOCTYPE html>
<html>
<head>
<title>PEM Generator</title>
<style type="text/css">
label {
  display: inline-block;
  width: 90px;
  text-align: right;
}​
</style>
</head>
<body>
<form action="" method="post">
<div class="block">
    <label>Modulus</label>
    <input type="text" style="width:410px;" name="modulus" value="<?php echo $_POST['modulus']; //Don't use this in production environment, as the data retrieved is not filtered (more info at https://owasp.org/www-community/attacks/xss/).  ?>">
</div>
<div class="block">
    <label>Exponent</label>
    <input type="text" style="width:410px;" name="exponent"  value="<?php echo $_POST['exponent']; //Don't use this in production environment, as the data retrieved is not filtered (more info at https://owasp.org/www-community/attacks/xss/).  ?>">
</div>
<input type="submit" value="Generate public key">
</form>

<?php 
if($_POST['modulus'] != "" && $_POST['exponent'] != "")
{
	echo "<br /><br /><textarea style='width:500px;height:200px;'>".createPemFromModulusAndExponent($_POST['modulus'], $_POST['exponent'])."</textarea>";
}
?>
</body>
</html>