<?php 
session_start(); 

// DB credentials.
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','unipalm_db');
// Establish database connection.
try
{
$conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS);
$conn -> exec("set names utf8");
}
catch (PDOException $e)
{
exit("Error: " . $e->getMessage());
}
?>