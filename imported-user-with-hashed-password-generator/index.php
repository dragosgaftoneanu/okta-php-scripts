<?php
error_reporting(0);
?><!DOCTYPE html>
<html>
<head>
<title>Imported user with hashed password generator</title>
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
    <label>Password</label>
    <input type="text" style="width:410px;" name="password" value="<?php echo $_POST['password']; ?>">
</div>
<div class="block">
    <label>Salt</label>
    <input type="text" style="width:410px;" name="salt" value="<?php echo $_POST['salt']; ?>">
</div>
<div class="block">
    <label>Salt order</label>
    <select name="order" style="width:410px">
		<option value="1"<?php if($_POST['order'] == "1"){echo " SELECTED";} ?>>Prefix</option>
		<option value="2"<?php if($_POST['order'] == "2"){echo " SELECTED";} ?>>Postfix</option>
	</select>
</div>
<div class="block">
    <label>Type</label>
    <select name="type" style="width:410px">
		<option value="1"<?php if($_POST['type'] == "1"){echo " SELECTED";} ?>>MD5</option>
		<option value="2"<?php if($_POST['type'] == "2"){echo " SELECTED";} ?>>SHA1</option>
		<option value="3"<?php if($_POST['type'] == "3"){echo " SELECTED";} ?>>SHA256</option>
		<option value="4"<?php if($_POST['type'] == "4"){echo " SELECTED";} ?>>SHA512</option>
	</select>
</div>
<input type="submit" value="Generate hashed password">
</form>

<?php 
if($_POST['password'] != "")
{
	if($_POST['order'] == "1")
		$order = "prefix";
	else
		$order = "postfix";

	if($_POST['type'] == "1")
	{
		$algo = "MD5";
		
		if($_POST['order'] == "1")
			$result = base64_encode(hash("md5", $_POST['salt'] . $_POST['password'], true));
		else
			$result = base64_encode(hash("md5", $_POST['password'] . $_POST['salt'], true));
		
	}elseif($_POST['type'] == "2")
	{
		$algo = "SHA-1";
		
		if($_POST['order'] == "1")
			$result = base64_encode(hash("sha1", $_POST['salt'] . $_POST['password'], true));
		else
			$result = base64_encode(hash("sha1", $_POST['password'] . $_POST['salt'], true));
		
	}elseif($_POST['type'] == "3")
	{
		$algo = "SHA-256";
		
		if($_POST['order'] == "1")
			$result = base64_encode(hash("sha256", $_POST['salt'] . $_POST['password'], true));
		else
			$result = base64_encode(hash("sha256", $_POST['password'] . $_POST['salt'], true));
		
	}elseif($_POST['type'] == "4")
	{
		$algo = "SHA-512";
		
		if($_POST['order'] == "1")
			$result = base64_encode(hash("sha512", $_POST['salt'] . $_POST['password'], true));
		else
			$result = base64_encode(hash("sha512", $_POST['password'] . $_POST['salt'], true));
		
	}
	
	echo "<br /><br />
		<strong>Password:</strong> $_POST[password]<br />
		<strong>Algorithm:</strong> $algo<br />
		<strong>Salt:</strong> $_POST[salt]<br />
		<strong>Salt order:</strong> $order<br />
		<strong>Hashed Password:</strong> $result";
}
?>
</body>
</html>