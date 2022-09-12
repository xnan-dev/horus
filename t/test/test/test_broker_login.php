<?php

$user=array_key_exists("user",$_POST) ? $_POST["user"] : "";
$password=array_key_exists("password",$_POST) ? $_POST["password"] : "";
$submit=array_key_exists("submit",$_POST) ? $_POST["submit"] : "";

if ($user=="miusuario" && $password=="mipassword" && $submit=="enviar") {
	print "USUARIO LOGUEADO";
} else {
?>

<html>
<body>
  <label for="user">First name:</label>
  <input type="text" name="user" value="<?php echo $user; ?>" type="text"/>
  <label for="password">password:</label>
  <input type="text" name="password" value="<?php echo $password; ?>" type="text"/>
  <input type="button" method="post" name="submit" value="<?php echo $submit; ?>" type="text"/>

</html>

<?php } ?>