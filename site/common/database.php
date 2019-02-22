<?php
require_once('utils.php');
require_once('settings.php');

define("NUM_REGIONS", 60);
define("NUM_AGEGROUPS", 6);

function getResult(&$output) {
   return $output['result'][count($output['result']) - 1];
}

function setResult(&$output, $value) {
   if(!isset($output['result'])) {
      $output['result'] = array();
   }
   array_push($output['result'], $value);
}

/*
===== DatabaseConnect =====
Purpose:
   Connects to the database
Input:
   $dbHost - The hostname of the database server
   $dbPort - The TCP port to use
   $dbUser - The username
   $dbPass - The password for this user
   $dbName - The database (schema) to use
Output:
   A handle to the database connection
*/
function databaseConnect($dbHost, $dbPort, $dbUser, $dbPass, $dbName) {
//    $dbh = databaseConnect($dbHost, $dbPort, $dbUser, $dbPass, $dbName);
   if($dbh) {
      mysqli_select_db($dbh, $dbName);
   }
   return $dbh;
}

//Connect to the database
$dbh = databaseConnect($dbHost, $dbPort, $dbUser, $dbPass, $dbName);



function getTargetPreference(&$output, $hash){
    $userID = $output['user_id'];
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT `value` FROM ec_fluv_user_preferences WHERE `user_id` = '{$userID}' AND 'name' = 'targets'");
    if($row = mysqli_fetch_array($result)) {
        $list = explode(",",$result);
        return $list;
    } else {
        return null;
    }
}

/*
===== getUserByHash =====
Purpose:
   Finds a user by their (partial) hash
Input:
   $output - The array of return values (array reference)
   $hash - The user's hash (or at least the first 32 bits of it)
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
      3 - Failure
   $output['user_id'] - The user's id
   $output['user_hash'] - The user's hash
   $output['user_name'] - The user's name
   $output['user_email'] - The user's email
*/
function getUserByHash(&$output, $hash) {
   if(strlen($hash) >= 8) {
       $dbh = databaseConnect(null, null, null, null, 'epicast2');
       $result = mysqli_query($dbh,"SELECT `id`, `hash`, `name`, `email` FROM ec_fluv_users WHERE `hash` LIKE '{$hash}%'");
      if($row = mysqli_fetch_array($result)) {
         setResult($output, 1);
         $output['user_id'] = intval($row['id']);
         $output['user_hash'] = $row['hash'];
         $output['user_name'] = $row['name'];
         $output['user_email'] = $row['email'];
         mysqli_query($dbh,"UPDATE ec_fluv_users SET `last_seen` = now() WHERE `id` = {$row['id']}");
      } else {
         setResult($output, 2);
      }
   } else {
      setResult($output, 3);
   }
   return getResult($output);
}

/*
===== getUserByEmail =====
Purpose:
   Finds a user by their email address
Input:
   $output - The array of return values (array reference)
   $email - The user's email
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
      3 - Failure
   See getUserByHash
*/
function getUserByEmail(&$output, $email) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT `hash` FROM ec_fluv_users WHERE `email` = '{$email}'");
   if($row = mysqli_fetch_array($result)) {
      return getUserByHash($output, $row['hash']);
   } else {
      setResult($output, 2);
      return getResult($output);
   }
}

function getUserIDByMturkID($mturkID) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT `id` FROM ec_fluv_users_mturk WHERE `name` = '{$mturkID}'");
  if($row = mysqli_fetch_array($result)) {
     return $row['id'];
  } else {
     return -1;
  }

}

function userAlreadyExist($mturkID) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT `name` FROM ec_fluv_users_mturk_2019 WHERE `name` = '{$mturkID}'");
  if($row = mysqli_fetch_array($result)) {
     // echo ("old user");
     return 1;
  } else {
     return 0;
  }
}

/*
===== getUserStats =====
Purpose:
   Looks up user stats
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
   $epiweek - The epiweek
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['stat_completed'] - The number of regions completed on the given epiweek
*/
function getUserStats(&$output, $userID, $epiweek) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT count(1) `completed` FROM ec_fluv_submissions WHERE `user_id` = {$userID} AND `epiweek_now` = {$epiweek}");
   if($row = mysqli_fetch_array($result)) {
      $output['stat_completed'] = intval($row['completed']);
      setResult($output, 1);
   } else {
      setResult($output, 2);
   }
   return getResult($output);
}

/*
===== getUserStats_hosp =====
Purpose:
   Looks up user stats
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
   $epiweek - The epiweek
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['stat_completed'] - The number of regions completed on the given epiweek
*/
function getUserStats_hosp(&$output, $userID, $epiweek) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT count(1) `completed` FROM ec_fluv_submissions_hosp WHERE `user_id` = {$userID} AND `epiweek_now` = {$epiweek}");
   if($row = mysqli_fetch_array($result)) {
      $output['stat_completed'] = intval($row['completed']);
      setResult($output, 1);
   } else {
      setResult($output, 2);
   }
   return getResult($output);
}

/*
===== getEpiweekInfo =====
Purpose:
   Returns info for the current epiweek
Input:
   $output - The array of return values (array reference)
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure (table ec_fluv_round)
      3 - Failure (table epidata.fluview)
   $output['epiweek']['round_epiweek'] - This round's identifier (can be ahead of data_epiweek)
   $output['epiweek']['data_epiweek'] - The most recently published issue (from epidata.fluview)
   $output['epiweek']['deadline'] - Deadline timestamp as a string (YYYY-MM-DD HH:MM:SS)
   $output['epiweek']['deadline_timestamp'] - Unix timestamp of deadline
   $output['epiweek']['remaining'] - An array containing days/hours/minutes/seconds remaining
*/
function getEpiweekInfo(&$output) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
   $result = mysqli_query($dbh,'SELECT yearweek(now(), 6) `current_epiweek`, x.`round_epiweek`, x.`deadline`, unix_timestamp(x.`deadline`) `deadline_timestamp`, unix_timestamp(x.`deadline`) - unix_timestamp(now()) `remaining` FROM (SELECT `round_epiweek`, date_sub(`deadline`, INTERVAL 12 HOUR) `deadline` FROM ec_fluv_round) x');
   if($row = mysqli_fetch_array($result)) {
      $output['epiweek'] = array();
      $output['epiweek']['current_epiweek'] = intval($row['current_epiweek']);

      $current_year = intval($output['epiweek']['current_epiweek'] / 100);
      $current_week = intval($output['epiweek']['current_epiweek'] % 100);
      if($current_week >= 30) {
        $output['epiweek']['season'] = array(
          'year' => $current_year,
          'start' => $current_year * 100 + 40,
          'end' => ($current_year + 1) * 100 + 20,
        );
      } else {
        $output['epiweek']['season'] = array(
          'year' => $current_year - 1,
          'start' => ($current_year - 1) * 100 + 40,
          'end' => $current_year * 100 + 20,
        );
      }
      $output['epiweek']['round_epiweek'] = intval($row['round_epiweek']);
      $output['epiweek']['deadline'] = $row['deadline'];
      $output['epiweek']['deadline_timestamp'] = intval($row['deadline_timestamp']);
      $seconds = intval($row['remaining']);
      $days = 0;
      $hours = 0;
      $minutes = 0;
      if($seconds < 0) {
         $seconds = 0;
      } else {
         $days = intval($seconds / (60 * 60 * 24));
         $seconds -= $days * (60 * 60 * 24);
         $hours = intval($seconds / (60 * 60));
         $seconds -= $hours * (60 * 60);
         $minutes = intval($seconds / 60);
         $seconds -= $minutes * 60;
      }
      $output['epiweek']['remaining'] = array(
         'days' => $days,
         'hours' => $hours,
         'minutes' => $minutes,
         'seconds' => $seconds,
      );
      setResult($output, 1);
   } else {
      setResult($output, 2);
      return getResult($output);
   }
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
   $result = mysqli_query($dbh,'SELECT max(`issue`) AS `data_epiweek` FROM epidata.`fluview`');
   if($row = mysqli_fetch_array($result)) {
      $output['epiweek']['data_epiweek'] = intval($row['data_epiweek']);
      setResult($output, 1);
   } else {
      setResult($output, 3);
      return getResult($output);
   }
   return getResult($output);
}

function getEpiweekInfo_mturk(&$output) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,'SELECT yearweek(now(), 6) `current_epiweek`, x.`round_epiweek`, x.`deadline`, unix_timestamp(x.`deadline`) `deadline_timestamp`, unix_timestamp(x.`deadline`) - unix_timestamp(now()) `remaining` FROM (SELECT `round_epiweek`, date_sub(`deadline`, INTERVAL 12 HOUR) `deadline` FROM ec_fluv_round) x');
   if($row = mysqli_fetch_array($result)) {
      $output['epiweek'] = array();
       $output['epiweek']['current_epiweek'] = intval($row['current_epiweek']);

      $current_year = intval($output['epiweek']['current_epiweek'] / 100);
      $current_week = intval($output['epiweek']['current_epiweek'] % 100);
      if($current_week >= 30) {
        $output['epiweek']['season'] = array(
          'year' => $current_year,
          'start' => $current_year * 100 + 40,
          'end' => ($current_year + 1) * 100 + 20,
        );
      } else {
        $output['epiweek']['season'] = array(
          'year' => $current_year - 1,
          'start' => ($current_year - 1) * 100 + 40,
          'end' => $current_year * 100 + 20,
        );
      }

       $output['epiweek']['round_epiweek'] = intval($row['round_epiweek']);
      $output['epiweek']['deadline'] = $row['deadline'];
      $output['epiweek']['deadline_timestamp'] = intval($row['deadline_timestamp']);
      $seconds = intval($row['remaining']);
      $days = 0;
      $hours = 0;
      $minutes = 0;
      if($seconds < 0) {
         $seconds = 0;
      } else {
         $days = intval($seconds / (60 * 60 * 24));
         $seconds -= $days * (60 * 60 * 24);
         $hours = intval($seconds / (60 * 60));
         $seconds -= $hours * (60 * 60);
         $minutes = intval($seconds / 60);
         $seconds -= $minutes * 60;
      }
      $output['epiweek']['remaining'] = array(
         'days' => $days,
         'hours' => $hours,
         'minutes' => $minutes,
         'seconds' => $seconds,
      );
      setResult($output, 1);
   } else {
      setResult($output, 2);
      return getResult($output);
   }
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
   $result = mysqli_query($dbh,'SELECT max(`issue`) AS `data_epiweek` FROM epidata.`fluview`');
   if($row = mysqli_fetch_array($result)) {
      $output['epiweek']['data_epiweek'] = intval($row['data_epiweek']);
      setResult($output, 1);
   } else {
      setResult($output, 3);
      return getResult($output);
   }
   return getResult($output);
}

/*
===== getRegions =====
Purpose:
   Returns all regions
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['regions'] - An array of regions, indexed by region ID
*/
function getRegions(&$output, $userID) {
   $temp = array();
   if(getEpiweekInfo($temp) !== 1) {
      return getResult($temp);
   }
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
   $result = mysqli_query($dbh,"SELECT r.`id`, r.`name`, r.`states`, r.`population`, CASE WHEN s.`user_id` IS NULL THEN FALSE ELSE TRUE END `completed` FROM ec_fluv_regions r LEFT JOIN ec_fluv_submissions s ON s.`user_id` = {$userID} AND s.`region_id` = r.`id` AND s.`epiweek_now` = {$temp['epiweek']['round_epiweek']} ORDER BY r.`id` ASC");
   $regions = array();
   while($row = mysqli_fetch_array($result)) {
      $region = array(
         'id' => intval($row['id']),
         'name' => $row['name'],
         'states' => $row['states'],
         'population' => intval($row['population']),
         'completed' => intval($row['completed']) === 1,
      );
      $regions[$region['id']] = $region;
   }
   $output['regions'] = &$regions;
   setResult($output, count($regions) == NUM_REGIONS ? 1 : 2);
   return getResult($output);
}

function getRegions_mturk(&$output, $userID) {
   $temp = array();
   if(getEpiweekInfo_mturk($temp) !== 1) {
      return getResult($temp);
   }
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
   $result = mysqli_query($dbh,"SELECT r.`id`, r.`name`, r.`states`, r.`population`, CASE WHEN s.`user_id` IS NULL THEN FALSE ELSE TRUE END `completed` FROM ec_fluv_regions r LEFT JOIN ec_fluv_submissions_mturk s ON s.`user_id` = {$userID} AND s.`region_id` = r.`id` AND s.`epiweek_now` = {$temp['epiweek']['round_epiweek']} ORDER BY r.`id` ASC");
   $regions = array();
   while($row = mysqli_fetch_array($result)) {
      $region = array(
         'id' => intval($row['id']),
         'name' => $row['name'],
         'states' => $row['states'],
         'population' => intval($row['population']),
         'completed' => intval($row['completed']) === 1,
      );
      $regions[$region['id']] = $region;
   }
   $output['regions'] = &$regions;
   setResult($output, count($regions) == NUM_REGIONS ? 1 : 2);
   return getResult($output);
}

/*
===== getAgeGroups =====
Purpose:
   Returns all age groups
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['ageGroups'] - An array of regions, indexed by age group ID
*/
function getAgeGroups(&$output, $userID) {
   $temp = array();
   if(getEpiweekInfo($temp) !== 1) {
      return getResult($temp);
   }
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
   $result = mysqli_query($dbh,"SELECT r.`id`, r.`flusurv_name`, r.`name`, r.`ages`, CASE WHEN s.`user_id` IS NULL THEN FALSE ELSE TRUE END `completed` FROM ec_fluv_age_groups r LEFT JOIN ec_fluv_submissions_hosp s ON s.`user_id` = {$userID} AND s.`group_id` = r.`id` AND s.`epiweek_now` = {$temp['epiweek']['round_epiweek']} ORDER BY r.`id` ASC");
   $ageGroups = array();
   while($row = mysqli_fetch_array($result)) {
      $ageGroup = array(
         'id' => intval($row['id']),
         'flusurv_name' => $row['flusurv_name'],
         'name' => $row['name'],
         'ages' => $row['ages'],
         'completed' => intval($row['completed']) === 1,
      );
      $ageGroups[$ageGroup['id']] = $ageGroup;
   }
   $output['ageGroups'] = &$ageGroups;
   setResult($output, count($ageGroups) == NUM_AGEGROUPS ? 1 : 2);
   return getResult($output);
}

/*
===== getRegionsExtended =====
Purpose:
   Returns all regions, including historical counts and user forecasts for each region
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure (other)
      3 - Failure (history)
      4 - Failure (forecast)
   $output['regions'] - An array of regions
*/
function getRegionsExtended(&$output, $userID) {
   $temp = array();
   if(getEpiweekInfo($temp) !== 1) {
      return getResult($temp);
   }
   //Basic region information
   if(getRegions($output, $userID) !== 1) {
      return getResult($output);
   }
   //History and forecast for every region
   foreach($output['regions'] as &$r) {
      if(getPreference($output, 'advanced_prior', 'int') === 1) {
         $firstWeek = 199730;
      } else {
         $firstWeek = 200430;
      }
      if(getHistory($output, $r['id'], $firstWeek) !== 1) {
         return getResult($output);
      }
      $r['history'] = $output['history'];
      if(loadForecast($output, $userID, $r['id']) !== 1) {
         return getResult($output);
      }
      $r['forecast'] = $output['forecast'];
   }
   setResult($output, 1);
   return getResult($output);
}

function getRegionsExtended_mturk(&$output, $userID) {
   $temp = array();
   if(getEpiweekInfo_mturk($temp) !== 1) {
      return getResult($temp);
   }
   //Basic region information
   if(getRegions_mturk($output, $userID) !== 1) {
      return getResult($output);
   }
   //History and forecast for every region
   foreach($output['regions'] as &$r) {
      if(getPreference($output, 'advanced_prior', 'int') === 1) {
         $firstWeek = 199730;
      } else {
         $firstWeek = 200430;
      }
      if(getHistory_mturk($output, $r['id'], $firstWeek) !== 1) {
         return getResult($output);
      }
      $r['history'] = $output['history'];
      if(loadForecast_mturk($output, $userID, $r['id']) !== 1) {
         return getResult($output);
      }
      $r['forecast'] = $output['forecast'];
   }
   setResult($output, 1);
   return getResult($output);
}


/*
===== getAgeGroupsExtended =====
Purpose:
   Returns all age groups, including historical counts and user forecasts for each age group
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure (other)
      3 - Failure (history)
      4 - Failure (forecast)
   $output['ageGroups'] - An array of age groups
*/
function getAgeGroupsExtended(&$output, $userID, $location) {
   $temp = array();
   if(getEpiweekInfo($temp) !== 1) {
      return getResult($temp);
   }
   //Basic region information
   if(getAgeGroups($output, $userID) !== 1) {
      return getResult($output);
   }
   $firstWeek = 200940;
   //History and forecast for every region
   foreach($output['ageGroups'] as &$g) {

      if(getHistory_Hosp($output, $g['flusurv_name'], $firstWeek, $location) !== 1) {
         return getResult($output);
      }
      $g['history'] = $output['history'];
      if(loadForecast_hosp($output, $userID, $g['id']) !== 1) {
         return getResult($output);
      }
      $g['forecast'] = $output['forecast'];
   }
   setResult($output, 1);
   return getResult($output);
}


/*
===== getHistory =====
Purpose:
   Returns history for a region
Input:
   $output - The array of return values (array reference)
   $regionID - The region's ID
   $firstWeek - The first epiweek
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['history'] - Arrays of epiweeks and historical incidence (wILI) for the region
*/
function getHistory(&$output, $regionID, $firstWeek) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT fv.`epiweek`, fv.`wili` FROM epidata.`fluview` AS fv JOIN ( SELECT `epiweek`, max(`issue`) AS `latest` FROM epidata.`fluview` AS fv JOIN ec_fluv_regions AS reg ON reg.`fluview_name` = fv.`region` WHERE reg.`id` = {$regionID} AND fv.`epiweek` >= {$firstWeek} GROUP BY fv.`epiweek` ) AS issues ON fv.`epiweek` = issues.`epiweek` AND fv.`issue` = issues.`latest` JOIN ec_fluv_regions AS reg ON reg.`fluview_name` = fv.`region` WHERE reg.`id` = {$regionID} AND fv.`epiweek` >= {$firstWeek} ORDER BY fv.`epiweek` ASC");
   $date = array();
   $wili = array();
   while($row = mysqli_fetch_array($result)) {
      $ew = intval($row['epiweek']);
      while($firstWeek < $ew) {
        array_push($date, $firstWeek);
        array_push($wili, -1);
        $firstWeek = addEpiweeks($firstWeek, 1);
      }
      array_push($date, $ew);
      array_push($wili, floatval($row['wili']));
      $firstWeek = addEpiweeks($firstWeek, 1);
   }
   $output['history'] = array('date' => &$date, 'wili' => &$wili);
   setResult($output, 1);
   return getResult($output);
}

function getHistory_mturk(&$output, $regionID, $firstWeek) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT fv.`epiweek`, fv.`wili` FROM epidata.`fluview` AS fv JOIN ( SELECT `epiweek`, max(`issue`) AS `latest` FROM epidata.`fluview` AS fv JOIN ec_fluv_regions AS reg ON reg.`fluview_name` = fv.`region` WHERE reg.`id` = {$regionID} AND fv.`epiweek` >= {$firstWeek} GROUP BY fv.`epiweek` ) AS issues ON fv.`epiweek` = issues.`epiweek` AND fv.`issue` = issues.`latest` JOIN ec_fluv_regions AS reg ON reg.`fluview_name` = fv.`region` WHERE reg.`id` = {$regionID} AND fv.`epiweek` >= {$firstWeek} ORDER BY fv.`epiweek` ASC");
   $date = array();
   $wili = array();
   $maxEW = 201920;
   while($row = mysqli_fetch_array($result)) {
      $ew = intval($row['epiweek']);
      if($ew<$maxEW) {
        while($firstWeek < $ew) {
          array_push($date, $firstWeek);
          array_push($wili, -1);
          $firstWeek = addEpiweeks($firstWeek, 1);
        }
        array_push($date, $ew);
        array_push($wili, floatval($row['wili']));
        $firstWeek = addEpiweeks($firstWeek, 1);
      }
   }
   $output['history'] = array('date' => &$date, 'wili' => &$wili);
   setResult($output, 1);
   return getResult($output);
}

/*
===== getHistory_Hosp =====
Purpose:
   Returns history for an ageGroup
Input:
   $ageGroup - The age group's ID
   $firstWeek - The first epiweek
Output:
   $output['history'] - Arrays of epiweeks and historical incidence (wILI) for the region
*/
function getHistory_Hosp(&$output, $flusurv_name, $firstWeek, $location) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT `epidata`.`flusurv`.`issue`, `epidata`.`flusurv`.`epiweek`, `epidata`.`flusurv`.`{$flusurv_name}` AS `rate` " .
   "FROM (SELECT `epiweek`, max(`issue`) AS `latest` " .
   "FROM `epidata`.`flusurv` WHERE `location` = '{$location}' AND `epiweek` >= {$firstWeek} GROUP BY `epiweek`) AS `issues` " .
   "JOIN `epidata`.`flusurv` ON `epidata`.`flusurv`.`issue` = `issues`.`latest` AND `epidata`.`flusurv`.`epiweek` = `issues`.`epiweek` " .
   "WHERE `location` = '{$location}' ORDER BY `epidata`.`flusurv`.`epiweek` ASC");

   $dateArr = array();
   $rateArr = array();

   $currentWeek = $firstWeek;
   while ($row = mysqli_fetch_array($result)) {
      $currentEpiweek = intval($row['epiweek']);

      // Push -1 for all weeks with no data
      while ($currentWeek < $currentEpiweek) {
        array_push($dateArr, $currentWeek);
        array_push($rateArr, -1);
        $currentWeek = addEpiweeks($currentWeek, 1);
      }
      array_push($dateArr, $currentEpiweek);
      array_push($rateArr, floatval($row['rate']));
      // print($currentEpiweek);
      // print(floatval($row['rate']));
      $currentWeek = addEpiweeks($currentWeek, 1);
   }
   $output['history'] = array('date' => $dateArr, 'rate' => $rateArr);
   setResult($output, 1);
   return getResult($output);
}


/**
 * Returns an array of age groups in the form of (flusurv_name, name, ages) where
 * - flusurv_name is the name by which db identifies the age group,
 * - name is a succinct name of the age group (such as age group #2), and
 * - ages is the more detailed description of the age group (such as "18-30 years old")
 */
function listAgeGroups() {
  $returnAgeGroups = array();
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT * FROM ec_fluv_age_groups");

  while ($row = mysqli_fetch_assoc($result)) {
    $returnAgeGroups[] = $row;
  }
  // $returnAgeGroups = getAgeGroupsExtended(&$output, $userID);
  return $returnAgeGroups;
}

/*
===== saveForecast =====
Purpose:
   Saves the user's forecast
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
   $regionID - The region ID
   $forecast - The forecast (array of values)
   $commit - Whether or not to flag the forecast as a final submission
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
*/
function saveForecast(&$output, $userID, $regionID, $forecast, $commit) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $temp = array();
   if(getEpiweekInfo($temp) !== 1) {
      return getResult($temp);
   }
   $epiweek = $temp['epiweek']['round_epiweek'];
   foreach($forecast as $wili) {
      $epiweek = addEpiweeks($epiweek, 1);
      mysqli_query($dbh,"INSERT INTO ec_fluv_forecast (`user_id`, `region_id`, `epiweek_now`, `epiweek`, `wili`, `date`) VALUES ({$userID}, {$regionID}, {$temp['epiweek']['round_epiweek']}, {$epiweek}, {$wili}, now()) ON DUPLICATE KEY UPDATE `wili` = {$wili}, `date` = now()");
   }
   if($commit) {
      mysqli_query($dbh,"INSERT INTO ec_fluv_submissions (`user_id`, `region_id`, `epiweek_now`, `date`) VALUES ({$userID}, {$regionID}, {$temp['epiweek']['round_epiweek']}, now())");
   }
   setResult($output, 1);
   return getResult($output);
}

/*
===== saveForecast_mturk =====rtt
Purpose:
   Saves the user's forecast
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
   $regionID - The region ID
   $forecast - The forecast (array of values)
   $commit - Whether or not to flag the forecast as a final submission
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
*/
function saveForecast_mturk(&$output, $userID, $regionID, $forecast, $commit) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $temp = array();
  if(getEpiweekInfo_mturk($temp) !== 1) {
     return getResult($temp);
  }
  $epiweek = $temp['epiweek']['round_epiweek'];
  foreach($forecast as $wili) {
     $epiweek = addEpiweeks($epiweek, 1);
     mysqli_query($dbh,"INSERT INTO ec_fluv_forecast_mturk (`user_id`, `region_id`, `epiweek_now`, `epiweek`, `wili`, `date`) VALUES ({$userID}, {$regionID}, {$temp['epiweek']['round_epiweek']}, {$epiweek}, {$wili}, now()) ON DUPLICATE KEY UPDATE `wili` = {$wili}, `date` = now()");
  }
  if($commit) {
     mysqli_query($dbh,"INSERT INTO ec_fluv_submissions_mturk (`user_id`, `region_id`, `epiweek_now`, `date`) VALUES ({$userID}, {$regionID}, {$temp['epiweek']['round_epiweek']}, now())");
  }
  setResult($output, 1);
  return getResult($output);
}


/*
===== saveForecast_hosp =====
Purpose:
   Saves the user's forecast
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
   $group_id - The age group ID
   $forecast - The forecast (array of values)
   $commit - Whether or not to flag the forecast as a final submission
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
*/

function saveForecast_hosp(&$output, $userID, $group_id, $forecast, $commit) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $temp = array();
   if(getEpiweekInfo($temp) !== 1) {
      return getResult($temp);
   }
   $epiweek = $temp['epiweek']['round_epiweek'];
   foreach($forecast as $wili) {
      $epiweek = addEpiweeks($epiweek, 1);
      mysqli_query($dbh,"INSERT INTO ec_fluv_forecast_hosp (`user_id`, `group_id`, `epiweek_now`, `epiweek`, `value`, `date`) VALUES ({$userID}, {$group_id}, {$temp['epiweek']['round_epiweek']}, {$epiweek}, {$wili}, now()) ON DUPLICATE KEY UPDATE `value` = {$wili}, `date` = now()");
   }
   if($commit) {
      mysqli_query($dbh,"INSERT INTO ec_fluv_submissions_hosp (`user_id`, `group_id`, `epiweek_now`, `date`) VALUES ({$userID}, {$group_id}, {$temp['epiweek']['round_epiweek']}, now())");
   }
   setResult($output, 1);
   return getResult($output);
}

/*
===== loadForecast =====
Purpose:
   Loads the user's forecast
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
   $regionID - The region ID
   $submitted - Whether or not to only load submitted forecasts (default false)
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['forecast'] - Arrays of epiweeks and forecast (wILI) for the region made by the user
*/
function loadForecast(&$output, $userID, $regionID, $submitted=false) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    // echo "loadForecast";
  if($submitted) {
     $temp = array();
     if(getEpiweekInfo($temp) !== 1) {
        return getResult($temp);
     }
     $result = mysqli_query($dbh,"SELECT coalesce(max(`epiweek_now`), 0) `epiweek` FROM ec_fluv_submissions WHERE `user_id` = {$userID} AND `region_id` = {$regionID} AND `epiweek_now` < {$temp['epiweek']['round_epiweek']}");
     // echo "ec_fluv_submissions";
  } else {
     $result = mysqli_query($dbh,"SELECT coalesce(max(`epiweek_now`), 0) `epiweek` FROM ec_fluv_forecast WHERE `user_id` = {$userID} AND `region_id` = {$regionID}");
     // echo "ec_fluv_forecast";
  }
  if($row = mysqli_fetch_array($result)) {
     // echo "result exist";
     $epiweek = intval($row['epiweek']);
  } else {
     // echo "null result";
     setResult($output, 2);
     return getResult($output);
  }
  $date = array();
  $wili = array();
  $result = mysqli_query($dbh,"SELECT `epiweek_now`, `epiweek`, `wili` FROM ec_fluv_forecast f WHERE `user_id` = {$userID} AND `region_id` = {$regionID} AND `epiweek_now` = {$epiweek} ORDER BY f.`epiweek` ASC");
  while($row = mysqli_fetch_array($result)) {
     // echo ($row['wili']);
     array_push($date, intval($row['epiweek']));
     array_push($wili, floatval($row['wili']));
  }
  $output['forecast'] = array('date' => &$date, 'wili' => &$wili);
  // foreach ($output['forecast']['wili'] as $key) {
  //   echo ($key);
  // }

  setResult($output, 1);
  return getResult($output);
}


function loadForecast_mturk(&$output, $userID, $regionID, $submitted=false) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    if($submitted) {
      $temp = array();
      if(getEpiweekInfo_mturk($temp) !== 1) {
         return getResult($temp);
      }
      $result = mysqli_query($dbh,"SELECT coalesce(max(`epiweek_now`), 0) `epiweek` FROM ec_fluv_submissions_mturk WHERE `user_id` = {$userID} AND `region_id` = {$regionID} AND `epiweek_now` < {$temp['epiweek']['round_epiweek']}");
   } else {
      $result = mysqli_query($dbh,"SELECT coalesce(max(`epiweek_now`), 0) `epiweek` FROM ec_fluv_forecast_mturk WHERE `user_id` = {$userID} AND `region_id` = {$regionID}");
   }
   if($row = mysqli_fetch_array($result)) {
      $epiweek = intval($row['epiweek']);
   } else {
      setResult($output, 2);
      return getResult($output);
   }

   $date = array();
   $wili = array();
   $query = "SELECT `epiweek_now`, `epiweek`, `wili` FROM ec_fluv_forecast_mturk f WHERE `user_id` = {$userID} AND `region_id` = {$regionID} AND `epiweek_now` = {$epiweek} ORDER BY f.`epiweek` ASC";
   $result = mysqli_query($dbh, $query);
   while($row = mysqli_fetch_array($result)) {
      array_push($date, intval($row['epiweek']));
      array_push($wili, floatval($row['wili']));
   }
   $output['forecast'] = array('date' => &$date, 'wili' => &$wili);
   setResult($output, 1);
   return getResult($output);
}



/*
===== loadForecast_hosp =====
Purpose:
   Loads the user's forecast
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
   $group_id - The age group ID
   $submitted - Whether or not to only load submitted forecasts (default false)
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['forecast'] - Arrays of epiweeks and forecast (value) for the region made by the user
*/
function loadForecast_hosp(&$output, $userID, $group_id, $submitted=false) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    if($submitted) {
      $temp = array();
      if(getEpiweekInfo($temp) !== 1) {
         return getResult($temp);
      }
      $q = "SELECT coalesce(max(`epiweek_now`), 0) `epiweek` FROM ec_fluv_submissions_hosp WHERE `user_id` = {$userID} AND `group_id` = {$group_id} AND `epiweek_now` < {$temp['epiweek']['round_epiweek']}";
      $result = mysqli_query($dbh, $q) or die($q."<br/><br/>".mysqli_error($dbh));
   } else {
      $q = "SELECT coalesce(max(`epiweek_now`), 0) `epiweek` FROM ec_fluv_forecast_hosp WHERE `user_id` = {$userID} AND `group_id` = {$group_id}";
      $result = mysqli_query($dbh, $q) or die($q."<br/><br/>".mysqli_error($dbh));
   }
   if($row = mysqli_fetch_array($result)) {
      $epiweek = intval($row['epiweek']);
   } else {
      setResult($output, 2);
      return getResult($output);
   }
   $date = array();
   $wili = array();
   $result = mysqli_query($dbh,"SELECT `epiweek_now`, `epiweek`, `value` FROM ec_fluv_forecast_hosp f WHERE `user_id` = {$userID} AND `group_id` = {$group_id} AND `epiweek_now` = {$epiweek} ORDER BY f.`epiweek` ASC");
   while($row = mysqli_fetch_array($result)) {
      array_push($date, intval($row['epiweek']));
      array_push($wili, floatval($row['value']));
   }
   $output['forecast'] = array('date' => &$date, 'wili' => &$wili);
   setResult($output, 1);
   return getResult($output);
}


/*
===== registerUser =====
*** THIS WILL SEND AN EMAIL TO THE USER ***
Purpose:
   Registers a new user
Input:
   $output - The array of return values (array reference)
   $name - The user's (nick)name
   $email - The user's email
   $adminEmail - The email to which replies should be directed
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['user_new'] - Whether or not the user is a new user (as determined by email address)
   $output['user_id'] - The user's ID, whether nascent or pre-existing
*/
function registerUser(&$output, $name, $email, $instance, $adminEmail) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    //Find, or create, the user
   if(getUserByEmail($output, $email) === 1) {
      $output['user_new'] = false;
   } else {
      mysqli_query($dbh,"INSERT INTO ec_fluv_users (`hash`, `name`, `email`, `instance`, `first_seen`, `last_seen`) VALUES (md5(rand()), '{$name}', '{$email}', '{$instance}', now(), now())");
      $output['user_new'] = true;
      if(getUserByEmail($output, $email) !== 1) {
         return getResult($output);
      }
   }
   //Send an email to the user
   $hash = strtoupper(substr($output['user_hash'], 0, 8));
   $subject = mysqli_real_escape_string($dbh, 'Welcome to Epicast');
   $body = mysqli_real_escape_string($dbh, sprintf("Hi %s,\r\n\r\nWelcome to Epicast! Here's your User ID: %s\r\nYou can login and begin forecasting here: https://epicast.org/launch.php?user=%s\r\n\r\nThank you,\r\nThe Delphi Team\r\n\r\n[This is an automated message. Please direct all replies to: %s. Unsubscribe: https://epicast.org/preferences.php?user=%s]", $name, $hash, $hash, $adminEmail, $hash));
   mysqli_query($dbh,"INSERT INTO automation.email_queue (`from`, `to`, `subject`, `body`) VALUES ('delphi@epicast.net', '{$email}', '{$subject}', '{$body}')");
   mysqli_query($dbh,"CALL automation.RunStep(2)");
   setResult($output, 1);
   return getResult($output);
}

/*
===== getUserByMturkID =====
Purpose:
   Finds a user by their mturk ID address
Input:
   $mturkID - The user's mturk ID
Output:
   1 if user is in the database, 0 if it's a new user
*/
// function getUserByMturkID($mturkID) {
//    $result = mysqli_query("SELECT `name` FROM ec_fluv_users_mturk WHERE `name` = '{$mturkID}'");
//    printf($result);
//    if($row = mysqli_fetch_array($result)) {
//       return 1;
//    } else {
//       return 0;
//    }
// }

/*
===== registerUser_mturk =====
*** THIS WILL REGISTER A MTURK USER BASED ON THEIR ID ***
Purpose:
   Registers a new mturk user
Input:
   $output - The array of return values (array reference)
   $name - The user's (nick)name
   $email - The user's email
   $adminEmail - The email to which replies should be directed
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['user_new'] - Whether or not the user is a new user (as determined by email address)
   $output['user_id'] - The user's ID, whether nascent or pre-existing
*/
function registerUser_mturk($mturkID) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    //Find, or create, the user
  if (userAlreadyExist($mturkID) === 1) {
    return;
  } else {
    $email = md5(rand());
    $hash = md5(rand());
    $escapedInput = mysqli_real_escape_string($dbh, $mturkID);
    // echo("ecaped string: $escapedInput\n");
    $query = "INSERT INTO ec_fluv_users_mturk (`hash`, `name`, `email`, `first_seen`, `last_seen`)
              VALUES ('{$hash}', '{$escapedInput}', '{$email}', now(), now())";
    $result = mysqli_query($dbh, $query);
    // if ($result == FALSE) {
    //   echo("failed to insert");
    //   echo(mysqli_error($dbh));
    // }
    // else {echo("success");}
  }
  return;
}


function registerUser_mturk_2019($mturkID, $taskID) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    //Find, or create, the user
    if (userAlreadyExist($mturkID) === 1) {
        return;
    } else {
        $email = md5(rand());
        $hash = md5(rand());
        $escapedInput = mysqli_real_escape_string($dbh, $mturkID);
        $query = "INSERT INTO ec_fluv_users_mturk_2019 (`hash`, `name`, `email`, `first_seen`, `last_seen`, `taskID`)
              VALUES ('{$hash}', '{$escapedInput}', '{$email}', now(), now(), {$taskID})";
        mysqli_query($dbh, $query);
        $query = "UPDATE ec_fluv_mturk_tasks SET numWorker = numWorker + 1 WHERE taskID = {$taskID}";
        mysqli_query($dbh, $query);
    }
    return;
}

function readSqlResult($query, $dest) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($dest, $row);
    }
    return $dest;

}

function getAvailableTaskSets() {
    $query = "select taskID, states from ec_fluv_mturk_tasks where numWorker < 50";
    $availableTasks = array();
    $availableTasks = readSqlResult($query, $availableTasks);
    return $availableTasks;
}


function getNextLocation($mturkID, $regionID) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');

    if ($regionID === -1 && !userAlreadyExist($mturkID)) {
        $availableTasks = getAvailableTaskSets();
        $task = $availableTasks[array_rand($availableTasks)];
        $taskID = $task['taskID'];
        registerUser_mturk_2019($mturkID, $taskID);

        $states = $task['states'];
        $regionIDs = explode(",",$states);
        $regionID = array(min($regionIDs));
        return $regionID;

    } else {
        $escapedInput = mysqli_real_escape_string($dbh, $mturkID);
        $query = "select taskID from ec_fluv_users_mturk_2019 where name = '{$escapedInput}'";
        $result = mysqli_query($dbh, $query);
        $taskID = intval(mysqli_fetch_assoc($result));

        $query = "select states from ec_fluv_mturk_tasks where taskID = {$taskID}";
        $states = array();
        $states = readSqlResult($query, $states);
        $states = $states[0]['states'];
        $states = explode(",", $states);
        return $states;
    }
}


function save_random_code_mturk($userID, $code) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"INSERT INTO ec_fluv_mturk_code_match (`user_id`, `code`) VALUES ({$userID}, {$code})");
  if ($result == FALSE) {
     echo("failed to insert");
     echo(mysqli_error($dbh));
   }
  else {echo("success");}
  return;
}

/*
===== loadDefaultPreferences =====
Purpose:
   Gets default preferences
Input:
   $output - The array of return values (array reference)
Output:
   $output['result'] will contain the following values:
      1 - Success
   $output['default_preferences'] - An array of preferences in the form of (name, value) pairs
*/
function loadDefaultPreferences(&$output) {
   $output['default_preferences'] = array();
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT `name`, `value` FROM ec_fluv_defaults ORDER BY `name` ASC");
   while($row = mysqli_fetch_array($result)) {
      $output['default_preferences'][$row['name']] = $row['value'];
   }
   setResult($output, 1);
   return getResult($output);
}

/*
===== loadUserPreferences =====
Purpose:
   Gets user preferences
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
Output:
   $output['result'] will contain the following values:
      1 - Success
   $output['user_preferences'] - An array of preferences in the form of (name, value) pairs
*/
function loadUserPreferences(&$output, $userID) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $output['user_preferences'] = array();
   $result = mysqli_query($dbh,"SELECT `name`, `value` FROM ec_fluv_user_preferences WHERE `user_id` = {$userID} ORDER BY `name` ASC");
   while($row = mysqli_fetch_array($result)) {
      $output['user_preferences'][$row['name']] = $row['value'];
   }
   setResult($output, 1);
   return getResult($output);
}

function loadUserPreferences_mturk(&$output, $userID) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $output['user_preferences'] = array();
   $result = mysqli_query($dbh, "SELECT `name`, `value` FROM ec_fluv_user_preferences_mturk WHERE `user_id` = {$userID} ORDER BY `name` ASC");
   while($row = mysqli_fetch_array($result)) {
      $output['user_preferences'][$row['name']] = $row['value'];
   }
   setResult($output, 1);
   return getResult($output);
}

/*
===== saveUserPreferences =====
Purpose:
   Gets, merges, and stores user preferences
Input:
   $output - The array of return values (array reference)
   $userID - The user's ID
   $preferences - The preferences to save
Output:
   See loadUserPreferences
*/
function saveUserPreferences(&$output, $userID, $preferences) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    foreach(array_keys($preferences) as $name) {
      $value = $preferences[$name];
      if($value === null) {
         mysqli_query($dbh,"DELETE FROM ec_fluv_user_preferences WHERE `user_id` = {$userID} AND `name` = '{$name}'");
      } else {
         mysqli_query($dbh,"INSERT INTO ec_fluv_user_preferences (`user_id`, `name`, `value`, `date`) VALUES ({$userID}, '{$name}', '{$value}', now()) ON DUPLICATE KEY UPDATE `value` = '{$value}', `date` = now()");
      }
   }
   return loadUserPreferences($output, $userID);
}

function saveUserPreferences_mturk(&$output, $userID, $preferences) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    foreach(array_keys($preferences) as $name) {
      $value = $preferences[$name];
      if($value === null) {
         mysqli_query($dbh,"DELETE FROM ec_fluv_user_preferences_mturk WHERE `user_id` = {$userID} AND `name` = '{$name}'");
      } else {
         mysqli_query($dbh,"INSERT INTO ec_fluv_user_preferences_mturk (`user_id`, `name`, `value`, `date`) VALUES ({$userID}, '{$name}', '{$value}', now()) ON DUPLICATE KEY UPDATE `value` = '{$value}', `date` = now()");
      }
   }
   return loadUserPreferences_mturk($output, $userID);
}

/*
===== getUserbase =====
*** ADMIN ONLY ***
Purpose:
   Return information on all registered users
Input:
   $output - The array of return values (array reference)
   $sortField - Sort field: 'n', 'fs', 'ls'
   $sortDir - Sort direction: 'a', 'd'
Output:
   $output['result'] will contain the following values:
      1 - Success
      2, 3, 4 - Failure
   $output['userbase'] - An array of users
*/
function getUserbase(&$output, $sortField, $sortDir) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    if(!isAdmin($output)) {
      setResult($output, 3);
      return getResult($output);
   }
   $fields = array('n' => 'name', 'fs' => 'first_seen', 'ls' => 'last_seen');
   $dirs = array('a' => 'ASC', 'd' => 'DESC');
   if(!in_array($sortField, array_keys($fields)) || !in_array($sortDir, array_keys($dirs))) {
      setResult($output, 4);
      return getResult($output);
   }
   $users = array();
   $result = mysqli_query($dbh,"SELECT `id`, `hash`, `name`, `email`, `first_seen`, `last_seen`, CASE WHEN `last_seen` >= date_sub(now(), INTERVAL 7 DAY) THEN 1 ELSE 0 END `active`, CASE WHEN `last_seen` = `first_seen` THEN -1 WHEN `last_seen` >= date_sub(now(), INTERVAL 10 MINUTE) THEN 1 ELSE 0 END `online`, CASE WHEN `first_seen` >= date_sub(now(), INTERVAL 7 DAY) THEN 1 ELSE 0 END `new` FROM ec_fluv_users ORDER BY `{$fields[$sortField]}` {$dirs[$sortDir]}");
   while($row = mysqli_fetch_array($result)) {
      $user = array(
         'id' => intval($row['id']),
         'hash' => $row['hash'],
         'name' => $row['name'],
         'email' => $row['email'],
         'first_seen' => $row['first_seen'],
         'last_seen' => $row['last_seen'],
         'active' => intval($row['active']) === 1,
         'online' => intval($row['online']),
         'new' => intval($row['new']) === 1,
         'default_preferences' => &$output['default_preferences'],
         'submissions' => array(),
         'submissions_hosp' => array()
      );

      if(loadUserPreferences($user, $user['id']) !== 1) {
         return getResult($user);
      }

      $result2 = mysqli_query($dbh,"SELECT epiweek_now, count(region_id) num FROM ec_fluv_submissions WHERE user_id = {$user['id']} GROUP BY epiweek_now ORDER BY epiweek_now ASC");
      while($row2 = mysqli_fetch_array($result2)) {
         array_push($user['submissions'], array(intval($row2['epiweek_now']), intval($row2['num'])));
      }

      $result3 = mysqli_query($dbh,"SELECT epiweek_now, count(group_id) num FROM ec_fluv_submissions_hosp WHERE user_id = {$user['id']} GROUP BY epiweek_now ORDER BY epiweek_now ASC");
      while($row3 = mysqli_fetch_array($result3)) {
         array_push($user['submissions_hosp'], array(intval($row3['epiweek_now']), intval($row3['num'])));
      }

      array_push($users, $user);

   }
   $output['userbase'] = &$users;
   setResult($output, 1);
   return getResult($output);
}

/*
===== getLeaderboard =====
Purpose:
   Return leaderboard data
Input:
   $output - The array of return values (array reference)
   $type - 'total' or 'last'
   $limit - Number of results to return, default 25
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['leaderboard'] - The leaderboard array, sorted by score
*/
function getLeaderboard(&$output, $type, $limit=25) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $leaderboard = array();
   if($type === 'total' || $type === 'last') {
      $field = $type;
   } else {
      setResult($output, 2);
      return getResult($output);
   }
   $result = mysqli_query($dbh,"SELECT u.`id`, u.`hash`, coalesce(CASE WHEN coalesce(p1.`value`, d1.`value`) = '1' THEN p2.`value` ELSE NULL END, d2.`value`) `name`, s.`{$field}` `score`, coalesce(p3.`value`, d3.`value`) `delphi` FROM ec_fluv_users u JOIN ec_fluv_scores s ON s.`user_id` = u.`id` JOIN ec_fluv_defaults d1 ON d1.`name` = 'advanced_leaderboard' LEFT JOIN ec_fluv_user_preferences p1 ON p1.`name` = d1.`name` AND p1.`user_id` = u.`id` JOIN ec_fluv_defaults d2 ON d2.`name` = 'advanced_initials' LEFT JOIN ec_fluv_user_preferences p2 ON p2.`name` = d2.`name` AND p2.`user_id` = u.`id` JOIN ec_fluv_defaults d3 ON d3.`name` = '_delphi' LEFT JOIN ec_fluv_user_preferences p3 ON p3.`name` = d3.`name` AND p3.`user_id` = u.`id` ORDER BY s.`{$field}` DESC, u.`id` DESC LIMIT {$limit}");
   $lastScore = -1;
   $rank = 0;
   $rownum = 0;
   while($row = mysqli_fetch_array($result)) {
      $rownum++;
      $entry = array(
         'hash' => getMiniHash($row['hash']),
         'name' => $row['name'],
         'score' => intval($row['score']),
      );
      if($entry['score'] !== $lastScore) {
         $lastScore = $entry['score'];
         $rank = $rownum;
      }
      $entry['rank'] = $rank;
      array_push($leaderboard, $entry);
   }
   $output['leaderboard'] = &$leaderboard;
   setResult($output, 1);
   return getResult($output);
}

/*
===== getNowcast =====
Purpose:
   Return nowcast for a given region
Input:
   $output - The array of return values (array reference)
   $epiweek - The epiweek to predict
   $region - The region name
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['nowcast'] - Array containing point prediction and standard deviation
*/
function getNowcast(&$output, $epiweek, $region) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $epiweek = $epiweek;
   $regions = array(
      1 => 'nat',
      2 => 'hhs1',
      3 => 'hhs2',
      4 => 'hhs3',
      5 => 'hhs4',
      6 => 'hhs5',
      7 => 'hhs6',
      8 => 'hhs7',
      9 => 'hhs8',
      10 => 'hhs9',
      11 => 'hhs10',
   );
   $region = $regions[$region];
   $result = mysqli_query($dbh,"SELECT value, std FROM epidata.`nowcasts` WHERE `epiweek` = {$epiweek} AND `location` = '{$region}'");
   if($row = mysqli_fetch_array($result)) {
      $output['nowcast'] = array(
         'value' => floatval($row['value']),
         'std' => floatval($row['std']),
      );
      setResult($output, 1);
   } else {
     setResult($output, 2);
   }
   return getResult($output);
}

/*
===== getYearForCurrentSeason =====
Purpose:
   Return the year number for the current flu season (e.g. 2016 for the
   2016--2017 flu season)
Input:
   $output - The array of return values (array reference)
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['season']['year'] - The year of the season start
   $output['season']['first_epiweek'] - The first epiweek of the contest
   $output['season']['last_epiweek'] - The last epiweek of the contest
*/
function getYearForCurrentSeason(&$output) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh, "SELECT `year`, `first_round_epiweek`, `last_round_epiweek` FROM `ec_fluv_season`");
   if($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
      $output['season'] = array(
         'year' => intval($row['year']),
         'first_epiweek' => intval($row['first_round_epiweek']),
         'last_epiweek' => intval($row['last_round_epiweek'])
      );
      setResult($output, 1);
   } else {
      setResult($output, 2);
   }
   return getResult($output);
}

/*
===== getTaskDate =====
Purpose:
   Get the datetime when the given task is scheduled to run next.
Input:
   $output - The array of return values (array reference)
   $taskId - The ID of the task
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
   $output['task'][<$taskId>] - The datetime when the task will be executed
*/
function getTaskDate(&$output, $taskId) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $result = mysqli_query($dbh,"SELECT `date` FROM `automation`.`tasks` WHERE `id` = {$taskId}");
   if($row = mysqli_fetch_array($result)) {
      if(!isset($output['task'])) {
         $output['task'] = array();
      }
      $output['task'][$taskId] = $row['date'];
      setResult($output, 1);
   } else {
      setResult($output, 2);
   }
   return getResult($output);
}

/*
===== updateSeason =====
Purpose:
   Update epiweek range of the current flu contest.
Input:
   $output - The array of return values (array reference)
   $firstWeek - The first epiweek of the contest
   $lastWeek - The last epiweek of the contest
Output:
   $output['result'] will contain the following values:
      1 - Success
*/
function updateSeason(&$output, $firstWeek, $lastWeek) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    mysqli_query($dbh,"UPDATE `ec_fluv_season` SET `first_round_epiweek` = {$firstWeek}, `last_round_epiweek` = {$lastWeek}");
   setResult($output, 1);
   return getResult($output);
}

/*
===== updateRound =====
Purpose:
   Update epiweek and deadline of the current forecasting round.
Input:
   $output - The array of return values (array reference)
   $epiweek - The epiweek of the current round
   $deadline - The deadline for the current round
Output:
   $output['result'] will contain the following values:
      1 - Success
*/
function updateRound(&$output, $epiweek, $deadline) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    mysqli_query($dbh,"UPDATE `ec_fluv_round` SET `round_epiweek` = {$epiweek}, `deadline` = '{$deadline}'");
   setResult($output, 1);
   return getResult($output);
}

/*
===== setTaskDate =====
Purpose:
   Sets the datetime when the given task is scheduled to run next.
Input:
   $output - The array of return values (array reference)
   $taskId - The ID of the task
   $date - The datetime when the task should be executed
Output:
   $output['result'] will contain the following values:
      1 - Success
*/
function setTaskDate(&$output, $taskId, $date) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    mysqli_query($dbh,"UPDATE `automation`.`tasks` SET `date` = '{$date}' WHERE `id` = {$taskId}");
   setResult($output, 1);
   return getResult($output);
}

/*
===== resetEpicast =====
Purpose:
   Resets Epicast for a new forecasting season.
Input:
   $output - The array of return values (array reference)
   $year - The year of the *new* season (e.g. 2017 for 2017--2018)
   $firstEpiweek - The first epiweek of the contest
   $lastEpiweek - The last epiweek of the contest
   $deadline - The first deadline
   $admin - The admin name and email, as an array with those keys
Output:
   $output['result'] will contain the following values:
      1 - Success
      2 - Failure
*/
function resetEpicast(&$output, $year, $firstEpiweek, $lastEpiweek, $deadline, $admin) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    $tbl_old = ($year - 1) . "_ec_fluv_";
   $tbl_new = 'ec_fluv_';
   $tables = array('defaults', 'forecast', 'regions', 'round', 'scores', 'season', 'submissions', 'user_preferences', 'users', 'age_groups', 'forecast_hosp', 'submissions_hosp');
   foreach($tables as $name) {
      mysqli_query($dbh,"CREATE TABLE {$tbl_old}{$name} AS SELECT * FROM {$tbl_new}{$name}");
   }
   $tables = array('forecast', 'scores', 'submissions', 'user_preferences', 'users', 'forecast_hosp', 'submissions_hosp');
   foreach($tables as $name) {
      mysqli_query($dbh,"TRUNCATE TABLE {$tbl_new}{$name}");
   }
   mysqli_query($dbh,"UPDATE `ec_fluv_season` SET `year` = {$year}, `first_round_epiweek` = {$firstEpiweek}, `last_round_epiweek` = {$lastEpiweek}");
   mysqli_query($dbh,"UPDATE `ec_fluv_round` SET `round_epiweek` = {$firstEpiweek}, `deadline` = '{$deadline}'");
   $temp = array();
   registerUser($temp, $admin['name'], $admin['email'], 'all', $admin['email']);
   $preferences = array('_admin' => 1, '_delphi' => 1);
   saveUserPreferences($temp, $temp['user_id'], $preferences);
   setResult($output, 1);
   return getResult($output);
}


function debugForecasts(&$output, $regionID, $epiweek_now, $epiweek, $regionOrHosp) {
    $dbh = databaseConnect(null, null, null, null, 'epicast2');
    if ($regionOrHosp == "regions") {
    $Users = mysqli_query($dbh,"select `user_id` from ec_fluv_forecast where `epiweek_now` = {$epiweek_now} and `region_id` = {$regionID} and `epiweek` = {$epiweek}");
  }
  else {
    $Users = mysqli_query($dbh,"select `user_id` from ec_fluv_forecast_hosp where `epiweek_now` = {$epiweek_now} and `group_id` = {$regionID} and `epiweek` = {$epiweek}");
  }
   $allUsers = array();
   while($row = mysqli_fetch_array($Users)) {
        array_push($allUsers, $row['user_id']);
      }

  $allWeeks = array();
  $weeks = mysqli_query($dbh,"select `epiweek` from ec_fluv_forecast where `epiweek_now` = {$epiweek_now} and `region_id` = {$regionID} and `user_id` = 1");
  while($row = mysqli_fetch_array($weeks)) {
    // print ($row['epiweek']);
    array_push($allWeeks, $row['epiweek']);
     }

   $fourWeekAhead = array();
   if ($regionOrHosp == "regions") {
     $monthAhead = mysqli_query($dbh,"select `wili` from ec_fluv_forecast where `epiweek_now` = {$epiweek_now} and `region_id` = {$regionID} and `epiweek` = {$epiweek}");
     while($row = mysqli_fetch_array($monthAhead)) {
          array_push($fourWeekAhead, $row['wili']);
     }
   }
   else {
     $monthAhead = mysqli_query($dbh,"select `value` from ec_fluv_forecast_hosp where `epiweek_now` = {$epiweek_now} and `group_id` = {$regionID} and `epiweek` = {$epiweek}");
     while($row = mysqli_fetch_array($monthAhead)) {
          array_push($fourWeekAhead, $row['value']);
     }
   }

   $peakWeek = array();
   $peakHeight = array();
   $allForecasts = array();
   foreach ($allUsers as $u) {
     $allForecasts[$u] = array();
     if ($regionOrHosp == "regions") {
       $allSeasonForecast = mysqli_query($dbh,"select `wili` from ec_fluv_forecast where `epiweek_now` = {$epiweek_now} and `region_id` = {$regionID} and `user_id` = {$u}");
       while ($row = mysqli_fetch_array($allSeasonForecast)) {
         array_push($allForecasts[$u], $row['wili']);
       }
     } else {
       $allSeasonForecast = mysqli_query($dbh,"select `value` from ec_fluv_forecast_hosp where `epiweek_now` = {$epiweek_now} and `group_id` = {$regionID} and `user_id` = {$u}");
       while ($row = mysqli_fetch_array($allSeasonForecast)) {
         array_push($allForecasts[$u], $row['value']);
       }
     }

     $maxForecast = 0;
     $maxWeek = 0;
     foreach ($allWeeks as $week) {
       if ($regionOrHosp == "regions") {
         $forecast = mysqli_query($dbh,"select `epiweek`, `wili` from ec_fluv_forecast where `epiweek_now` = {$epiweek_now} and `region_id` = {$regionID} and `user_id` = {$u} and `epiweek` = {$week}");
         $row = mysqli_fetch_array($forecast);
         if ($row['wili'] > $maxForecast) {
           $maxForecast = $row['wili'];
           $maxWeek = $week;
         }
       }
       else {
         $forecast = mysqli_query($dbh,"select `epiweek`, `value` from ec_fluv_forecast_hosp where `epiweek_now` = {$epiweek_now} and `group_id` = {$regionID} and `user_id` = {$u} and `epiweek` = {$week}");
         $row = mysqli_fetch_array($forecast);
         if ($row['value'] > $maxForecast) {
           $maxForecast = $row['value'];
           $maxWeek = $week;
         }
       }
     }
     array_push($peakHeight, $maxForecast);
     array_push($peakWeek, $maxWeek);
  }

   $output['debug'] = array('fourWeeksAhead' => &$fourWeekAhead, 'peakHeight' => &$peakHeight, 'peakWeek' => &$peakWeek, 'allForecasts' => &$allForecasts);
   setResult($output, 1);
   return getResult($output);
}

?>
