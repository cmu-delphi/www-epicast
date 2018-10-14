<?php
//Required files
require_once('common/settings.php');
require_once('common/database.php');
require_once('common/utils.php');

//Connect to the database
$dbh = databaseConnect($dbHost, $dbPort, $dbUser, $dbPass, $dbName);

//The header
session_start();
$output = array();
$hash = attemptLogin($output);
// print($output);
// print ($hash);
// $hash = "147D9191";
// $location = 'forecast_mturk.php?id=1';

if($hash !== null) {
   if(getPreference($output, 'skip_intro', 'int') === 1) {
   $location = 'home.php';
   } else {
      $location = 'preferences.php';
   }
} else {
   $location = 'index.php';
}

$_SESSION['fluv_login_fail'] = ($hash === null);
$path = dirname($_SERVER['REQUEST_URI']);
if($path !== '/') {
   $path .= '/';
}
$location = 'http://' . $_SERVER['HTTP_HOST'] . $path . $location;

header("Location: {$location}");
?>
<html>
   <body>
      <h1>
         Please click <a href="<?= $location ?>">here</a> to continue.
      </h1>
   </body>
</html>
