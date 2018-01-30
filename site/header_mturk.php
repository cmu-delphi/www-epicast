<?php
//Required files
require_once('settings.php');
require_once('database.php');
require_once('utils.php');

//Connect to the database
$dbh = databaseConnect($dbHost, $dbPort, $dbUser, $dbPass, $dbName);

//The header
session_start();
$output = array("user_id" => 1);
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
   $hash = "ilovechris";
   if($hash == null || (isset($fullHeader) && $fullHeader)) {
      //Big header
      ?>
      <div class="box_header">
         <div class="box_title box_title_mega">
            <a href="/" style="text-decoration: none; color: inherit;">
               <span class="effect_delphi">Epicast</span>
               <br />
               <span class="effect_fluv">&lt;Influenza Edition&gt;</span>
            </a>
         </div>
         <div class="box_subtitle">Epidemiological Forecasting by <span class="effect_delphi"><a class="delphi" target="_blank" href="https://delphi.midas.cs.cmu.edu/">DELPHI</a></span></div>
      </div>
      <?php
      if(!isset($skipLogin) || !$skipLogin) {
         //Uh oh, login was required
         ?>
         <div class="box_content centered">
            <p>
               Oops, we need you to login again!<br />
               (Either your user ID was wrong or your session has expired.)
            </p>
            <?php button('fa-arrow-circle-right', 'Continue', "navigate('index.php')"); ?>
         </div>
         <?php
         require_once('footer.php');
         $error = true;
      } else {
         //No login necessary
         ?>
         <div class="box_content">
         <?php
      }
   } else {
      //Mini header
      ?>
      <div class="box_header box_header_narrow box_header_fixed0"></div>
      <div class="box_header box_header_narrow box_header_fixed1">
         <div class="box_title box_title_mini"><span class="effect_delphi">Epicast</span>&nbsp;<span class="effect_fluv">FLUV</span></div>
         <!-- <div class="box_mininav">
            <span class="effect_tiny_header">Epicaster: <?= $output['user_name'] ?> [<?= $hash ?>]<br /></span>
         </div> -->
         <div class="box_miniclear"></div>
      </div>
      <div class="box_content">
      <?php
      //Account status
   }
}
?>
