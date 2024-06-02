<?php
 $servername = "localhost";
 $username = "root"; 
 $password = ""; 
 $dbname = "pdf";
 
 $conn = new mysqli($servername, $username, $password, $dbname);
 
// checking connection, if there will be an error when code will exit with error
 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
 }


