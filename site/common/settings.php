<?php
require_once('/var/www/html/secrets.php');

$dbHost = 'delphi.midas.cs.cmu.edu';
$dbPort = 3306;
$dbUser = Secrets::$db['epi'][0];
$dbPass = Secrets::$db['epi'][1];
$dbName = 'epicast2';

$epicastAdmin = array(
   "name" => "Jiaxian Sheng",
   "email" => "jiaxians@andrew.cmu.edu",
);
?>
