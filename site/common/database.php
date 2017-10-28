<?php
require_once('utils.php');

define("NUM_REGIONS", 14);

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
   $dbh = mysql_connect("{$dbHost}:{$dbPort}", $dbUser, $dbPass);
   if($dbh) {
      mysql_select_db($dbName, $dbh);
   }
   return $dbh;
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
      $result = mysql_query("SELECT `id`, `hash`, `name`, `email` FROM ec_fluv_users WHERE `hash` LIKE '{$hash}%'");
      if($row = mysql_fetch_array($result)) {
         setResult($output, 1);
         $output['user_id'] = intval($row['id']);
         $output['user_hash'] = $row['hash'];
         $output['user_name'] = $row['name'];
         $output['user_email'] = $row['email'];
         mysql_query("UPDATE ec_fluv_users SET `last_seen` = now() WHERE `id` = {$row['id']}");
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
   $result = mysql_query("SELECT `hash` FROM ec_fluv_users WHERE `email` = '{$email}'");
   if($row = mysql_fetch_array($result)) {
      return getUserByHash($output, $row['hash']);
   } else {
      setResult($output, 2);
      return getResult($output);
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
   $result = mysql_query("SELECT count(1) `completed` FROM ec_fluv_submissions WHERE `user_id` = {$userID} AND `epiweek_now` = {$epiweek}");
   if($row = mysql_fetch_array($result)) {
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
   $result = mysql_query('SELECT yearweek(now(), 6) `current_epiweek`, x.`round_epiweek`, x.`deadline`, unix_timestamp(x.`deadline`) `deadline_timestamp`, unix_timestamp(x.`deadline`) - unix_timestamp(now()) `remaining` FROM (SELECT `round_epiweek`, date_sub(`deadline`, INTERVAL 12 HOUR) `deadline` FROM ec_fluv_round) x');
   if($row = mysql_fetch_array($result)) {
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
   $result = mysql_query('SELECT max(`issue`) AS `data_epiweek` FROM epidata.`fluview`');
   if($row = mysql_fetch_array($result)) {
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
   $result = mysql_query("SELECT r.`id`, r.`name`, r.`states`, r.`population`, CASE WHEN s.`user_id` IS NULL THEN FALSE ELSE TRUE END `completed` FROM ec_fluv_regions r LEFT JOIN ec_fluv_submissions s ON s.`user_id` = {$userID} AND s.`region_id` = r.`id` AND s.`epiweek_now` = {$temp['epiweek']['round_epiweek']} ORDER BY r.`id` ASC");
   $regions = array();
   while($row = mysql_fetch_array($result)) {
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
   $result = mysql_query("SELECT fv.`epiweek`, fv.`wili` FROM epidata.`fluview` AS fv JOIN ( SELECT `epiweek`, max(`issue`) AS `latest` FROM epidata.`fluview` AS fv JOIN ec_fluv_regions AS reg ON reg.`fluview_name` = fv.`region` WHERE reg.`id` = {$regionID} AND fv.`epiweek` >= {$firstWeek} GROUP BY fv.`epiweek` ) AS issues ON fv.`epiweek` = issues.`epiweek` AND fv.`issue` = issues.`latest` JOIN ec_fluv_regions AS reg ON reg.`fluview_name` = fv.`region` WHERE reg.`id` = {$regionID} AND fv.`epiweek` >= {$firstWeek} ORDER BY fv.`epiweek` ASC");
   $date = array();
   $wili = array();
   while($row = mysql_fetch_array($result)) {
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
function getHistory_Hosp($ageGroup, $firstWeek) {
   $result = mysql_query("SELECT `epidata`.`flusurv`.`issue`, `epidata`.`flusurv`.`epiweek`, `epidata`.`flusurv`.`rate_age_3` AS `rate` " .
   "FROM (SELECT `epiweek`, max(`issue`) AS `latest` " .
   "FROM `epidata`.`flusurv` WHERE `location` = 'network_all' AND `epiweek` >= 201710 GROUP BY `epiweek`) AS `issues` " .
   "JOIN `epidata`.`flusurv` ON `epidata`.`flusurv`.`issue` = `issues`.`latest` AND `epidata`.`flusurv`.`epiweek` = `issues`.`epiweek` " .
   "WHERE `location` = 'network_all' ORDER BY `epidata`.`flusurv`.`epiweek` ASC");

   $dateArr = array();
   $rateArr = array();

   $currentWeek = $firstWeek;
   while ($row = mysql_fetch_array($result)) {
      $currentEpiweek = intval($row['epiweek']);

      // Push -1 for all weeks with no data
      while ($currentWeek < $currentEpiweek) {
        array_push($dateArr, $currentWeek);
        array_push($rateArr, -1);
        $currentWeek = addEpiweeks($currentWeek, 1);
      }
      array_push($dateArr, $currentEpiweek);
      array_push($rateArr, floatval($row['rate']));
      $currentWeek = addEpiweeks($currentWeek, 1);
   }
   $history = array('date' => $dateArr, 'rate' => $rateArr);

   return $history;
}

/**
 * Returns an array of age groups in the form of (flusurv_name, name, ages) where
 * - flusurv_name is the name by which db identifies the age group,
 * - name is a succinct name of the age group (such as age group #2), and
 * - ages is the more detailed description of the age group (such as "18-30 years old")
 */
function listAgeGroups() {
  $returnAgeGroups = array();
  $result = mysql_query("SELECT * FROM ec_fluv_age_groups");

  while ($row = mysql_fetch_assoc($result)) {
    $returnAgeGroups[] = $row;
  }
  return $returnAgeGroups;
}

/**
 * Get hospitalization data for the given age group.
 * Each age group is identified by the flusurv_name field in ec_fluv_age_groups table.
 */
function getHospitalizationForAgeGroup($ageGroup) {
  $returnAgeGroupHosp = array();
  // $result = mysql_query("SELECT * FROM epidata.flusurv WHERE issue = 201710 and epiweek = issue and location = 'network_all';");

//   $result = mysql_query("SELECT `epidata.flusurv`.`issue`,
//                           `epidata.flusurv`.`epiweek`,
//                           `flusurv`.`rate_age_3` AS `rate` FROM
//                           (SELECT `epiweek`, max(`issue`) AS `latest` FROM `epidata.flusurv` WHERE
//                            `location` = 'network_all' AND `epiweek` >= 201710 GROUP BY `epiweek`)
//                           AS `issues` JOIN `epidata.flusurv` ON `epidata.flusurv`.`issue` = `issues`.`latest`
//                           AND `epidata.flusurv`.`epiweek` = `issues`.`epiweek` WHERE `location` = 'network_all'
//                           ORDER BY `epidata.flusurv`.`epiweek` ASC");

  $result = mysql_query("SELECT * FROM epidata.flusurv WHERE issue >= 201710 and epiweek = issue and location = 'network_all'");

  while ($row = mysql_fetch_assoc($result)) {
    $returnAgeGroupHosp[] = $row;
  }
  return $returnAgeGroupHosp;
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
   $temp = array();
   if(getEpiweekInfo($temp) !== 1) {
      return getResult($temp);
   }
   $epiweek = $temp['epiweek']['round_epiweek'];
   foreach($forecast as $wili) {
      $epiweek = addEpiweeks($epiweek, 1);
      mysql_query("INSERT INTO ec_fluv_forecast (`user_id`, `region_id`, `epiweek_now`, `epiweek`, `wili`, `date`) VALUES ({$userID}, {$regionID}, {$temp['epiweek']['round_epiweek']}, {$epiweek}, {$wili}, now()) ON DUPLICATE KEY UPDATE `wili` = {$wili}, `date` = now()");
   }
   if($commit) {
      mysql_query("INSERT INTO ec_fluv_submissions (`user_id`, `region_id`, `epiweek_now`, `date`) VALUES ({$userID}, {$regionID}, {$temp['epiweek']['round_epiweek']}, now())");
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
   if($submitted) {
      $temp = array();
      if(getEpiweekInfo($temp) !== 1) {
         return getResult($temp);
      }
      $result = mysql_query("SELECT coalesce(max(`epiweek_now`), 0) `epiweek` FROM ec_fluv_submissions WHERE `user_id` = {$userID} AND `region_id` = {$regionID} AND `epiweek_now` < {$temp['epiweek']['round_epiweek']}");
   } else {
      $result = mysql_query("SELECT coalesce(max(`epiweek_now`), 0) `epiweek` FROM ec_fluv_forecast WHERE `user_id` = {$userID} AND `region_id` = {$regionID}");
   }
   if($row = mysql_fetch_array($result)) {
      $epiweek = intval($row['epiweek']);
   } else {
      setResult($output, 2);
      return getResult($output);
   }
   $date = array();
   $wili = array();
   $result = mysql_query("SELECT `epiweek_now`, `epiweek`, `wili` FROM ec_fluv_forecast f WHERE `user_id` = {$userID} AND `region_id` = {$regionID} AND `epiweek_now` = {$epiweek} ORDER BY f.`epiweek` ASC");
   while($row = mysql_fetch_array($result)) {
      array_push($date, intval($row['epiweek']));
      array_push($wili, floatval($row['wili']));
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
function registerUser(&$output, $name, $email, $adminEmail) {
   //Find, or create, the user
   if(getUserByEmail($output, $email) === 1) {
      $output['user_new'] = false;
   } else {
      mysql_query("INSERT INTO ec_fluv_users (`hash`, `name`, `email`, `first_seen`, `last_seen`) VALUES (md5(rand()), '{$name}', '{$email}', now(), now())");
      $output['user_new'] = true;
      if(getUserByEmail($output, $email) !== 1) {
         return getResult($output);
      }
   }
   //Send an email to the user
   $hash = strtoupper(substr($output['user_hash'], 0, 8));
   $subject = mysql_real_escape_string('Welcome to Epicast');
   $body = mysql_real_escape_string(sprintf("Hi %s,\r\n\r\nWelcome to Epicast! Here's your User ID: %s\r\nYou can login and begin forecasting here: https://epicast.org/launch.php?user=%s\r\n\r\nThank you,\r\nThe Delphi Team\r\n\r\n[This is an automated message. Please direct all replies to: %s. Unsubscribe: https://epicast.org/preferences.php?user=%s]", $name, $hash, $hash, $adminEmail, $hash));
   mysql_query("INSERT INTO automation.email_queue (`from`, `to`, `subject`, `body`) VALUES ('delphi@epicast.net', '{$email}', '{$subject}', '{$body}')");
   mysql_query("CALL automation.RunStep(2)");
   setResult($output, 1);
   return getResult($output);
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
   $result = mysql_query("SELECT `name`, `value` FROM ec_fluv_defaults ORDER BY `name` ASC");
   while($row = mysql_fetch_array($result)) {
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
   $output['user_preferences'] = array();
   $result = mysql_query("SELECT `name`, `value` FROM ec_fluv_user_preferences WHERE `user_id` = {$userID} ORDER BY `name` ASC");
   while($row = mysql_fetch_array($result)) {
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
   foreach(array_keys($preferences) as $name) {
      $value = $preferences[$name];
      if($value === null) {
         mysql_query("DELETE FROM ec_fluv_user_preferences WHERE `user_id` = {$userID} AND `name` = '{$name}'");
      } else {
         mysql_query("INSERT INTO ec_fluv_user_preferences (`user_id`, `name`, `value`, `date`) VALUES ({$userID}, '{$name}', '{$value}', now()) ON DUPLICATE KEY UPDATE `value` = '{$value}', `date` = now()");
      }
   }
   return loadUserPreferences($output, $userID);
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
   $result = mysql_query("SELECT `id`, `hash`, `name`, `email`, `first_seen`, `last_seen`, CASE WHEN `last_seen` >= date_sub(now(), INTERVAL 7 DAY) THEN 1 ELSE 0 END `active`, CASE WHEN `last_seen` = `first_seen` THEN -1 WHEN `last_seen` >= date_sub(now(), INTERVAL 10 MINUTE) THEN 1 ELSE 0 END `online`, CASE WHEN `first_seen` >= date_sub(now(), INTERVAL 7 DAY) THEN 1 ELSE 0 END `new` FROM ec_fluv_users ORDER BY `{$fields[$sortField]}` {$dirs[$sortDir]}");
   while($row = mysql_fetch_array($result)) {
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
         'submissions' => array()
      );
      if(loadUserPreferences($user, $user['id']) !== 1) {
         return getResult($user);
      }
      $result2 = mysql_query("SELECT epiweek_now, count(region_id) num FROM ec_fluv_submissions WHERE user_id = {$user['id']} GROUP BY epiweek_now ORDER BY epiweek_now ASC");
      while($row2 = mysql_fetch_array($result2)) {
         array_push($user['submissions'], array(intval($row2['epiweek_now']), intval($row2['num'])));
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
   $leaderboard = array();
   if($type === 'total' || $type === 'last') {
      $field = $type;
   } else {
      setResult($output, 2);
      return getResult($output);
   }
   $result = mysql_query("SELECT u.`id`, u.`hash`, coalesce(CASE WHEN coalesce(p1.`value`, d1.`value`) = '1' THEN p2.`value` ELSE NULL END, d2.`value`) `name`, s.`{$field}` `score`, coalesce(p3.`value`, d3.`value`) `delphi` FROM ec_fluv_users u JOIN ec_fluv_scores s ON s.`user_id` = u.`id` JOIN ec_fluv_defaults d1 ON d1.`name` = 'advanced_leaderboard' LEFT JOIN ec_fluv_user_preferences p1 ON p1.`name` = d1.`name` AND p1.`user_id` = u.`id` JOIN ec_fluv_defaults d2 ON d2.`name` = 'advanced_initials' LEFT JOIN ec_fluv_user_preferences p2 ON p2.`name` = d2.`name` AND p2.`user_id` = u.`id` JOIN ec_fluv_defaults d3 ON d3.`name` = '_delphi' LEFT JOIN ec_fluv_user_preferences p3 ON p3.`name` = d3.`name` AND p3.`user_id` = u.`id` ORDER BY s.`{$field}` DESC, u.`id` DESC LIMIT {$limit}");
   $lastScore = -1;
   $rank = 0;
   $rownum = 0;
   while($row = mysql_fetch_array($result)) {
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
   $result = mysql_query("SELECT value, std FROM epidata.`nowcasts` WHERE `epiweek` = {$epiweek} AND `location` = '{$region}'");
   if($row = mysql_fetch_array($result)) {
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
   $result = mysql_query("SELECT `year`, `first_round_epiweek`, `last_round_epiweek` FROM `ec_fluv_season`");
   if($row = mysql_fetch_array($result)) {
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
   $result = mysql_query("SELECT `date` FROM `automation`.`tasks` WHERE `id` = {$taskId}");
   if($row = mysql_fetch_array($result)) {
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
   mysql_query("UPDATE `ec_fluv_season` SET `first_round_epiweek` = {$firstWeek}, `last_round_epiweek` = {$lastWeek}");
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
   mysql_query("UPDATE `ec_fluv_round` SET `round_epiweek` = {$epiweek}, `deadline` = '{$deadline}'");
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
   mysql_query("UPDATE `automation`.`tasks` SET `date` = '{$date}' WHERE `id` = {$taskId}");
   setResult($output, 1);
   return getResult($output);
}

/*
===== resetEpicast =====
Purpose:
   Resets Epicast for a new forecasing season.
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
   $tbl_old = ($year - 1) . "_ec_fluv_";
   $tbl_new = 'ec_fluv_';
   $tables = array('defaults', 'forecast', 'regions', 'round', 'scores', 'season', 'submissions', 'user_preferences', 'users', 'age_groups', 'forecast_hosp', 'submissions_hosp');
   foreach($tables as $name) {
      mysql_query("CREATE TABLE {$tbl_old}{$name} AS SELECT * FROM {$tbl_new}{$name}");
   }
   $tables = array('forecast', 'scores', 'submissions', 'user_preferences', 'users', 'forecast_hosp', 'submissions_hosp');
   foreach($tables as $name) {
      mysql_query("TRUNCATE TABLE {$tbl_new}{$name}");
   }
   mysql_query("UPDATE `ec_fluv_season` SET `year` = {$year}, `first_round_epiweek` = {$firstEpiweek}, `last_round_epiweek` = {$lastEpiweek}");
   mysql_query("UPDATE `ec_fluv_round` SET `round_epiweek` = {$firstEpiweek}, `deadline` = '{$deadline}'");
   $temp = array();
   registerUser($temp, $admin['name'], $admin['email'], $admin['email']);
   $preferences = array('_admin' => 1, '_delphi' => 1);
   saveUserPreferences($temp, $temp['user_id'], $preferences);
   setResult($output, 1);
   return getResult($output);
}

?>
