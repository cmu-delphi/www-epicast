<?php
$dbHost = getenv('EPICAST_DB_HOST') ?: 'localhost';
$dbPort = intval(getenv('EPICAST_DB_PORT') ?: 3306);
$dbUser = getenv('EPICAST_DB_USER') ?: 'user';
$dbPass = getenv('EPICAST_DB_PASSWORD') ?: 'pass';
$dbName = getenv('EPICAST_DB_NAME') ?: 'epicast2';

$epicastAdmin = array(
   "name" => "Delphi Support",
   "email" => "delphi-support+crowdcast@andrew.cmu.edu",
);
?>
