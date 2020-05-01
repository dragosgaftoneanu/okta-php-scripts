<?php
error_reporting(0);
?><!DOCTYPE html>
<html>
<head>
<title>Imported user with hashed password generator</title>
<style type="text/css">
.block{
	margin-bottom:25px;
}
label {
  display: inline-block;
  width: 150px;
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
    <label>Type</label>
    <select name="type" style="width:410px">
		<option value="1"<?php if($_POST['type'] == "1"){echo " SELECTED";} ?>>MD5</option>
		<option value="2"<?php if($_POST['type'] == "2"){echo " SELECTED";} ?>>SHA1</option>
		<option value="3"<?php if($_POST['type'] == "3"){echo " SELECTED";} ?>>SHA256</option>
		<option value="4"<?php if($_POST['type'] == "4"){echo " SELECTED";} ?>>SHA512</option>
		<option value="5"<?php if($_POST['type'] == "5"){echo " SELECTED";} ?>>BCRYPT</option>
	</select>
</div>
<div class="block">
    <label>Workfactor<br /><em>(BCRYPT only)</em></label>
    <input type="text" style="width:410px;" name="workfactor" value="<?php echo $_POST['workfactor']; ?>">
</div>
<div class="block">
    <label>Salt<br /><em>(MD5, SHA1, SHA256, SHA512)</em></label>
    <input type="text" style="width:410px;" name="salt" value="<?php echo $_POST['salt']; ?>">
</div>
<div class="block">
    <label>Salt order<br /><em>(MD5, SHA1, SHA256, SHA512)</em></label>
    <select name="order" style="width:410px">
		<option value="1"<?php if($_POST['order'] == "1"){echo " SELECTED";} ?>>Prefix</option>
		<option value="2"<?php if($_POST['order'] == "2"){echo " SELECTED";} ?>>Postfix</option>
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
		
	}elseif($_POST['type'] == "5")
	{
		$algo = "BCRYPT";
		
		$output=password_hash($_POST['password'], PASSWORD_BCRYPT, array("cost" => $_POST['workfactor']));
		
		$result = substr($output,29,31);
		$_POST['salt'] = substr($output, 7, 22);
	}
	
	echo "<strong>Password:</strong> $_POST[password]<br />
		<strong>Algorithm:</strong> $algo<br />
		<strong>Hashed Password:</strong> $result<br />";
	
	if($algo != "BCRYPT")
		echo "<strong>Salt:</strong> " . base64_encode($_POST[salt]) . "<br /><strong>Salt order:</strong> $order<br />";
	else
		echo "<strong>Salt:</strong> $_POST[salt]<br />";
}
?>
</body>
</html>