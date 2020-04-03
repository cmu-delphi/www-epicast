<?php
session_start();
require_once('common/settings.php');
require_once('common/utils.php');
require_once('common/database.php');

//Connect to the database
$dbh = databaseConnect($dbHost, $dbPort, $dbUser, $dbPass, $dbName);

//Create the output array
$output = array('result' => 0);
if(!$dbh) {
   //Couldn't connect to the database
   $output['result'] = -100;
} else {
   //Connected successfully
   if($_REQUEST['action'] == 'forecast' || $_REQUEST['action'] == 'autosave') {
      $output['action'] = $_REQUEST['action'];
      $hash = mysqli_real_escape_string($dbh, $_REQUEST['hash']);
      $temp = array();
      if(getUserByHash($dbh, $temp, $hash) == 1) {
         $forecast = array();
         foreach($_REQUEST['f'] as $f) {
            array_push($forecast, floatval(mysqli_real_escape_string($dbh, $f)));
         }
         if(getEpiweekInfo($dbh, $temp) == 1) {
            if(count($forecast) >= 1 && count($forecast) <= 53) {
               //Save the forecast
               $regionID = intval(mysqli_real_escape_string($dbh, $_REQUEST['region_id']));
               $commit = ($_REQUEST['action'] == 'forecast');
               if(saveForecast($dbh, $temp, $temp['user_id'], $regionID, $forecast, $commit) == 1) {
                  //Success
                  $output['result'] = 1;
               } else {
                  //Failed to save forecast
                  $output['result'] = -5;
               }
            } else {
               //Size of forecast array is wrong
               $output['result'] = -4;
            }
         } else {
            //Failed to get round info
            $output['result'] = -3;
         }
      } else {
         //Invalid user
         $output['result'] = -2;
      }
   } else {
      //Unknown action
      $output['result'] = -1;
   }
}
echo json_encode($output);
?>
