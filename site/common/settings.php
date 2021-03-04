<?php
$dbHost = getenv('EPICAST_DB_HOST') ?: 'localhost';
$dbPort = intval(getenv('EPICAST_DB_PORT') ?: 3306);
$dbUser = getenv('EPICAST_DB_USER') ?: 'user';
$dbPass = getenv('EPICAST_DB_PASSWORD') ?: 'pass';
$dbName = getenv('EPICAST_DB_NAME') ?: 'epicast2';

$epicastAdmin = array(
   "name" => "Brian Clark",
   "email" => "briancla@andrew.cmu.edu",
);
?>
