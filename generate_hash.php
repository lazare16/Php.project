<?php
$password = 'adminUser'; // the plain text password
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
?>
