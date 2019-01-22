<?php
//Required files
require_once('settings.php');
require_once('database.php');
require_once('utils.php');

//Connect to the database
$dbh = databaseConnect($dbHost, $dbPort, $dbUser, $dbPass, $dbName);

//The header
session_start();
$output = array();
?>
<!DOCTYPE html>
<html>
   <head>
      <title>Delphi Epicast-FLUV</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
      <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
      <script src="js/utils.js"></script>
      <script src="js/rAF.js"></script>
      <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
      <link href='//fonts.googleapis.com/css?family=Yanone+Kaffeesatz:700|Alegreya+SC:700' rel='stylesheet' type='text/css'>
      <link href="css/style.css" rel="stylesheet" />
   </head>


   <body>
      <a name="top"></a>
<?php
$error = false;
if(!$dbh) {
   //Couldn't connect to the database
   fail("Couldn't connect to the database.");
   require_once('footer.php');
   $error = true;
} else {
      ?>
      <div class="box_header box_header_narrow box_header_fixed0"></div>
      <div class="box_header box_header_narrow box_header_fixed1">
         <div class="box_title box_title_mini"><span class="effect_delphi">Epicast</span>&nbsp;<span class="effect_fluv">FLUV</span></div>
         <div class="box_miniclear"></div>
      </div>
      <div class="box_content">
      <?php

}
?>
