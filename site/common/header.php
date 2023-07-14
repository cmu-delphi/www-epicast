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
      <title>Delphi Crowdcast COVID-19</title>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
      <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
      <script src="js/utils.js"></script>
      <script src="js/rAF.js"></script>
      <script src="js/delphi_epidata.js"></script>
      <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" media="none" onload="if(media!='all')media='all'" />
      <noscript>
      			<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" />
      </noscript>
      <link href="//fonts.googleapis.com/css?family=Yanone+Kaffeesatz:700|Alegreya+SC:700" rel="stylesheet" media="none" onload="if(media!='all')media='all'" />
      <noscript>
      			<link rel="stylesheet" href="//fonts.googleapis.com/css?family=Yanone+Kaffeesatz:700|Alegreya+SC:700" />
      </noscript>
      <link href="css/style.php" rel="stylesheet" />
   </head>
   <body>
      <a name="top"></a>
<?php
$error = false;
if($dbh->connect_errno) {
   //Couldn't connect to the database
   fail("Couldn't connect to the database: (".$dbh->connect_errno.") ".$dbh->connect_error);
   require_once('footer.php');
   $error = true;
} else {
   $hash = attemptLogin($dbh, $output);
   if($hash == null || (isset($fullHeader) && $fullHeader)) {
      //Big header
      ?>
      <div class="box_header">
         <div class="box_title box_title_mega">
            <a href="/" style="text-decoration: none; color: inherit;">
               <span class="effect_delphi">Crowdcast</span>
               <br />
               <span class="effect_fluv">&lt;COVID-19 Edition&gt;</span>
            </a>
         </div>
         <div class="box_subtitle">Epidemiological Forecasting by <span class="effect_delphi"><a class="delphi" target="_blank" rel="noopener" href="https://delphi.cmu.edu/">DELPHI</a></span>
             <br /> 
             <span class="effect_archive_index"> [ This site is in ARCHIVE mode and is not being updaed regularly ]</span>
         </div>
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
   } else {
      //Mini header
      ?>
      <div class="box_header box_header_narrow box_header_fixed0"></div>
      <div class="box_header box_header_narrow box_header_fixed1">
         <div class="box_title box_title_mini"><span class="effect_delphi">Crowdcast</span>&nbsp;<span class="effect_fluv">COVID-19</span>&nbsp;<span class="effect_archive_header"> [ This site is in ARCHIVE mode and is not being updaed regularly ]</span></div>
         <div class="box_mininav">
            <span class="effect_tiny_header">Crowdcaster: <?= $output['user_name'] ?> [<?= $hash ?>]<br /></span>
            <?php
            createLink('Home', 'home.php#top');
            createDivider('&middot;');
            createLink('Preferences', 'preferences.php');
            createDivider('&middot;');
            createLink('Leaderboards', 'scores.php');
            createDivider('&middot;');
            createLink('Logout', 'logout.php');
            createDivider('|');
            if(isAdmin($output)) {
               createLink('<i>Admin</i>', 'admin.php');
               createDivider('|');
            }
            print('<span class="effect_delphi">');
            createLink('DELPHI', 'https://delphi.cmu.edu/', true, 'delphi');
            print('</span>');
            ?>
         </div>
         <div class="box_miniclear"></div>
     </div>

     <!-- content -->
     <div class="box_content">
      <?php
      //Account status
      if(!isActivated($output)) {
         fail('Uh oh, your account is currently deactivated. Please visit the <a href="preferences.php">preferences</a> page to reactivate it!', 'account_deactivated_warning');
      }
   }
}
?>
