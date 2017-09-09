<?php
require_once('common/header.php');
if($error) {
   return;
}
//Utils
function sortLinks($field) {
   printf(' <a href="?field=%s&dir=a"><i class="fa fa-sort-amount-asc"></i></a>', $field);
   printf(' <a href="?field=%s&dir=d"><i class="fa fa-sort-amount-desc"></i></a>', $field);
}
function sortBool(&$a, &$b) {
   global $sortDir, $sortKey;
   $value = 0;
   if($a[$sortKey] && !$b[$sortKey]) {
      $value = 1;
   }
   if(!$a[$sortKey] && $b[$sortKey]) {
      $value = -1;
   }
   return $value * ($sortDir === 'a' ? 1 : -1);
}
function sortInt(&$a, &$b) {
   global $sortDir, $sortKey;
   $value = $a[$sortKey] - $b[$sortKey];
   return $value * ($sortDir === 'a' ? 1 : -1);
}
if(isAdmin($output)) {
?>
<div class="box_article centered">
   <?php
   //Sorting
   $sortDir = 'a';
   $sortField = 'n';
   $sortKey = null;
   $sortFunc = null;
   if(allSet('field', 'dir')) {
      $sortDir = $_REQUEST['dir'];
      if($_REQUEST['field'] == 'o') {
         $sortKey = 'online';
         $sortFunc = 'sortInt';
      } else if($_REQUEST['field'] == 'e') {
         $sortKey = 'emails';
         $sortFunc = 'sortInt';
      } else if($_REQUEST['field'] == 's') {
         $sortKey = 'total_submissions';
         $sortFunc = 'sortInt';
      } else {
         //Database sort
         $sortField = $_REQUEST['field'];
      }
   }
   //Round info
   if(getEpiweekInfo($output) !== 1) {
      fail('Error loading round info');
   }
   if(isset($_REQUEST['x'])) {
      $output['epiweek']['round_epiweek'] = addEpiweeks($output['epiweek']['round_epiweek'], -1);
   }
   //Load the userbase
   if(getUserbase($output, $sortField, $sortDir) !== 1) {
      fail('Error loading userbase');
   }
   //Stats
   $emailTypes = array('alerts', 'notifications', 'receipts', 'reminders');
   $numActive = 0;
   $numOnline = 0;
   $numNew = 0;
   $numForecasts = 0;
   $numForecastusers = 0;
   foreach($output['userbase'] as &$u) {
      $debugUser = getPreference($u, '_debug', 'int') === 1;
      $u['emails'] = 0;
      $u['total_submissions'] = 0;
      if($u['online'] === 1) {
         $numOnline++;
      }
      if($debugUser) {
         continue;
      }
      if($u['active']) {
         $numActive++;
      }
      if($u['new']) {
         $numNew++;
      }
      foreach($u['submissions'] as &$s) {
         if($s[0] === $output['epiweek']['round_epiweek']) {
            $numForecasts += $s[1];
            if($s[1] > 0) {
               $numForecastusers++;
            }
         }
      }
      foreach($emailTypes as &$type) {
         if(getPreference($u, 'email_' . $type, 'int') === 1) {
            $u['emails']++;
         }
      }
      foreach($u['submissions'] as &$s) {
         $u['total_submissions'] += $s[1];
      }
   }
   //PHP sort
   if($sortFunc !== null) {
      usort($output['userbase'], $sortFunc);
   }
   ?>
   <div class="box_section">
      <div class="box_section_title">
         At a Glance
         <div class="box_section_subtitle">
            Some basic system stats.
         </div>
      </div>
      <div>
         <div class="box_stat">
            <div class="bot_stat_value"><?= formatEpiweek($output['epiweek']['data_epiweek']) ?></div>
            <div class="bot_stat_description">Most Recent Report</div>
         </div>
         <div class="box_stat">
            <div class="bot_stat_value"><?= count($output['userbase']) ?></div>
            <div class="bot_stat_description">Total Registered Users</div>
         </div>
         <div class="box_stat">
            <div class="bot_stat_value"><?= $numNew ?></div>
            <div class="bot_stat_description">New Users Last 7 Days</div>
         </div>
         <div class="box_stat">
            <div class="bot_stat_value"><?= $numActive ?></div>
            <div class="bot_stat_description">Users Active Last 7 Days</div>
         </div>
         <div class="box_stat">
            <div class="bot_stat_value"><?= $numOnline ?></div>
            <div class="bot_stat_description">Users Online Now</div>
         </div>
         <div class="box_stat">
            <div class="bot_stat_value"><?= $numForecasts ?></div>
            <div class="bot_stat_description">Forecasts Received This Round</div>
         </div>
         <div class="box_stat">
            <div class="bot_stat_value"><?= $numForecastusers ?></div>
            <div class="bot_stat_description">Users Participated This Round</div>
         </div>
      </div>
   </div>
   <div class="box_section">
      <div class="box_section_title">
         Userbase
         <div class="box_section_subtitle">
            Hopefully some day this will be too long to display on a single page.
         </div>
      </div>
      <div>
         <table cellspacing="0">
            <tr><th>Name <?= sortLinks('n') ?></th><th>Email</th><th>First Seen <?= sortLinks('fs') ?></th><th>Last Seen <?= sortLinks('ls') ?></th><th>Preferences</th><th>Presence <?= sortLinks('o') ?></th><th>Communication <?= sortLinks('e') ?></th><th>Submissions <?= sortLinks('s') ?></th></tr>
            <?php
            foreach($output['userbase'] as &$u) {
               ?>
               <tr>
                  <td><?= htmlspecialchars($u['name']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><?= implode('<br />', explode(' ', $u['first_seen'])) ?></td>
                  <td><?= implode('<br />', explode(' ', $u['last_seen'])) ?></td>
                  <td class="any_extrasmall"><?php
                     foreach(array_keys($output['default_preferences']) as $k) {
                        if(isset($u['user_preferences'][$k])) {
                           printf('%s: %s<br />', $k, $u['user_preferences'][$k]);
                        } else {
                           printf('<span style="color: #888;">%s: %s</span><br />', $k, $output['default_preferences'][$k]);
                        }
                     }
                     foreach(array_keys($u['user_preferences']) as $k) {
                        if(!isset($output['default_preferences'][$k])) {
                           printf('<span style="background-color: #fdb;">%s: %s</span><br />', $k, $u['user_preferences'][$k]);
                        }
                     }
                  ?></td>
                  <td><?php
                     if($u['online'] === 1) { ?>
                        <span style="font-weight: bold; background-color: #bfb;">Online Now</span>
                     <?php } else if($u['online'] === 0) { ?>
                        <span style="font-style: italic; color: #888;">Offline</span>
                     <?php } else { ?>
                        <span style="background-color: #fbb;">Missing</span>
                     <?php }
                  ?></td>
                  <td><?php
                     if($u['emails'] === count($emailTypes)) { ?>
                        <span style="font-weight: bold; background-color: #bfb;">All Emails</span>
                     <?php } else if ($u['emails'] > 0) { ?>
                        Some Emails
                     <?php } else { ?>
                        <span style="font-weight: bold; background-color: #fbb;">Do Not Contact</span>
                     <?php }
                  ?></td>
                  <td class="any_extrasmall"><?php
                     foreach($u['submissions'] as &$s) {
                        printf('%s: %d<br />', formatEpiweek($s[0]), $s[1]);
                     }
                     printf('Total: %d<br />', $u['total_submissions']);
                  ?></td>
               </tr>
               <?php
            }
            ?>
         </table>
      </div>
   </div>
</div>
<?php
}
require_once('common/footer.php');
?>
