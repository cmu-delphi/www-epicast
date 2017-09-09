<?php
require_once('/var/www/html/secrets.php');

$dbHost = 'localhost';
$dbPort = 3306;
$dbUser = Secrets::$db['epi'][0];
$dbPass = Secrets::$db['epi'][1];
$dbName = 'epicast2';

$epicastAdmin = array(
   "name" => "David Farrow",
   "email" => "dfarrow0@gmail.com",
);
?>
