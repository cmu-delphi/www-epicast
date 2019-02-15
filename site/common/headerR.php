<?php
//Required files
require_once('settings.php');
require_once('database.php');
require_once('utils.php');
$skipLogin = true;

//Connect to the database
$dbh = databaseConnect($dbHost, $dbPort, $dbUser, $dbPass, $dbName);

//The header
//session_start();
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
   $hash = attemptLogin($output);
      ?>
      <div class="box_header">
         <div class="box_title box_title_mega">
               <span class="effect_delphi">Welcome to Epicast</span>
               <br />
               <span class="effect_fluv">&lt;Influenza Edition - California&gt;</span>
         </div>

         <div class="box_subtitle">Epidemiological Forecasting by <span class="effect_delphi"><a class="delphi" target="_blank" href="https://delphi.midas.cs.cmu.edu/">DELPHI</a></span></div>

          <div style='text-align:right'>
              <?php
              createLink('Home', 'recruitment.php?location=CA');
              createDivider('&middot;');
              createLink('Preferences', 'preferences_recruitment.php');
              createDivider('&middot;');
              createLink('Leaderboards', 'scores_recruitment.php');
              createDivider('|');
              print('<span class="effect_delphi">');
              createLink('DELPHI', 'https://delphi.midas.cs.cmu.edu/', true, 'delphi');
              print('</span>');
              ?>
          </div>

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
}
?>
