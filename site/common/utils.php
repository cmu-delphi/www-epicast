<?php

require_once('settings.php');
require_once('database.php');

function fail($message, $id=null, $hidden=false) {
   $id = ($id === null) ? '' : sprintf('id="%s"', $id);
   $style = $hidden ? 'style="display: none"' : '';
   $class = $hidden ? 'any_hidden' : '';
   echo("<div {$id} class=\"{$class} box_message any_failure\"><i class=\"fa fa-exclamation-triangle\"></i> {$message}</div>");
}

function success($message, $id=null, $hidden=false) {
   $id = ($id === null) ? '' : sprintf('id="%s"', $id);
   $class = $hidden ? 'any_hidden' : '';
   echo("<div {$id} class=\"{$class} box_message any_success\"><i class=\"fa fa-check-circle\"></i> {$message}</div>");
}

function button($icon, $text, $onClick, $classes='', $id=null, $code='') {
   $id = ($id === null) ? '' : sprintf('id="%s"', $id);
   printf('<div %s class="box_button %s" onClick="%s" %s><i class="fa %s"></i>&nbsp;&nbsp;%s</div>', $id, $classes, $onClick, $code, $icon, $text);
}



function createForm($id, $action, $values) {
   ?>
   <form id="<?= $id ?>" method="POST" action="<?= $action ?>">
      <?php
      for($i = 0; $i < count($values); $i += 2) {
         ?>
         <input type="hidden" name="<?= $values[$i] ?>" value="<?= $values[$i + 1] ?>" />
         <?php
      }
      ?>
   </form>
   <?php
}

function createLink($text, $url, $newTab=false, $classes='') {
   printf('<a class="%s" %s href="%s">%s</a>', $classes, $newTab ? 'target="_blank"' : '', $url, $text);
}

function createDivider($text) {
   printf('&nbsp;&nbsp;%s&nbsp;&nbsp;', $text);
}

function getMiniHash($hash) {
   return strtoupper(substr($hash, 0, 8));
}

function attemptLogin(&$output) {
    $dbh = databaseConnect($dbHost, $dbPort, $dbUser, $dbPass, $dbName);
   $hash = null;
   if(isset($_REQUEST['user']) || isset($_SESSION['hash_fluv'])) {
      if(isset($_REQUEST['user'])) {
         $hash = mysqli_real_escape_string($dbh, $_REQUEST['user']);
      } else {
         $hash = mysqli_real_escape_string($dbh, $_SESSION['hash_fluv']);
      }
      if(getUserByHash($output, $hash) === 1) {
         $hash = getMiniHash($output['user_hash']);
         $_SESSION['hash_fluv'] = $hash;
         if(loadDefaultPreferences($output) !== 1) {
            fail('Error loading default preferences');
         }
         if(loadUserPreferences($output, $output['user_id']) !== 1) {
            fail('Error loading user preferences');
         }
      } else {
         $hash = null;
      }
   }
   // print("returning $hash (not set)");
   return $hash;
}

function getPreference(&$output, $name, $type='string') {
   $value = null;
   if(isset($output['default_preferences'][$name])) {
      $value = $output['default_preferences'][$name];
   }
   if(isset($output['user_preferences'][$name])) {
      $value = $output['user_preferences'][$name];
   }
   if($value !== null) {
      if($type === 'int') {
         $value = intval($value);
      } else if($type === 'float') {
         $value = floatval($value);
      }
   }
   return $value;
}

function isActivated(&$output) {
   return getPreference($output, 'email_alerts', 'int') === 1 || getPreference($output, 'email_notifications', 'int') === 1 || getPreference($output, 'email_reminders', 'int') === 1 || getPreference($output, 'email_receipts', 'int') === 1;
}

function isAdmin(&$output) {
   return getPreference($output, '_admin', 'int') === 1;
}

function allSet() {
   foreach(func_get_args() as $arg) {
      if(is_array($arg)) {
         foreach($arg as $name) {
            if(!isset($_REQUEST[$name])) {
               return false;
            }
         }
      } else if(!isset($_REQUEST[$arg])) {
         return false;
      }
   }
   return true;
}

function formatEpiweek($epiweek) {
   return sprintf('%04dw%02d', floor($epiweek / 100), $epiweek % 100);
}

function getNumWeeks($year) {
   return ($year == 1997 || $year == 2003 || $year == 2008 || $year == 2014) ? 53 : 52;
}

function getDeltaWeeks($start, $end) {
   $x = ($end > $start) ? 1 : -1;
   $num = 0;
   while($start != $end && $num < 1e3) {
      $start = addEpiweeks($start, $x);
      $num += $x;
   }
   return $num;
}

function addEpiweeks($ew, $i) {
   $year = intval($ew / 100);
   $week = $ew % 100;
   $week += $i;
   $limit = getNumWeeks($year);
   if($week >= $limit + 1) {
      $week -= $limit;
      $year += 1;
   } else if($week < 1) {
      $week += getNumWeeks($year - 1);
      $year -= 1;
   }
   return $year * 100 + $week;
}

?>
