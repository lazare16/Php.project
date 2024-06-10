<?php
$password = 'adminUser'; 
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
?>
