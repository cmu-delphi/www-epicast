<?php
require_once('common/header.php');
if($error) {
   return;
}

// helper functions
?>
<script type="text/javascript">
function confirmSubmit() {
   return confirm('Are you sure?');
}
</script>
<?php
function createSubmit($name) {
   printf('<input type="submit" name="submit_%s" value="Apply" onclick="return confirmSubmit()" class="padded" />', $name);
}
function createInput($label, $name, $value='') {
   printf('<label>%s <input type="text" name="input_%s" value="%s"/></label><br />', $label, $name, $value);
}
function hasSubmission($name) {
   return isset($_REQUEST['submit_' . $name]);
}
function getSafeValue($name) {
   return mysql_real_escape_string($_REQUEST['input_' . $name]);
}

// Automation constants
$TASK_SEND_REMINDER_EMAILS = 5;
$TASK_DRY_RUN_FORECAST = 19;
$TASK_SUBMIT_FORECAST = 8;

if(isAdmin($output)) {
?>
<div class="box_article centered">
   <div class="box_section">
      <div class="box_section_title">
         &#9762; Warning &#9762;
         <div class="box_section_subtitle">
            These are dangerous settings.
         </div>
      </div>
      <div class="any_warning">
         This page allows you to modify Epicast's database and to control Epicast's scheduled execution.
         Invalid inputs can break Epicast, <em>or worse</em>.
         Be extra cautious.
         When in doubt, ask!
      </div>
   </div>
   <div class="box_section">
      <div class="box_section_title">
         User Preferences
         <div class="box_section_subtitle">
            Override user preferences, e.g. admin status.
         </div>
      </div>
      <?php
      if(hasSubmission('user_preferences')) {
         $email = getSafeValue('user_email');
         $prefName = getSafeValue('pref_name');
         $prefValue = getSafeValue('pref_value');
         $temp = array();
         // TODO - getUserByEmail updates the "last seen" value for the user; normally that's ok, but here we don't want that
         if(getUserByEmail($temp, $email) !== 1) {
            fail('Unable to load user preferences for that email.');
         } else {
            $preferences = array($prefName => $prefValue);
            if(saveUserPreferences($temp, $temp['user_id'], $preferences) !== 1) {
               fail('User found, but unable to save preferences.');
            } else {
               success('User preferences have been updated.');
            }
         }
      }
      ?>
      <div>
         <form method="POST"><p>
            You can view all emails and preference names on the
            <a href="admin.php">admin</a> page. There are a few special
            preferences that users don't set directly but are very important.
            <span class="any_warning">The "_admin" bit determines who can access
            the "Admin" and "Control" pages.</span> The "_debug" bit determines
            who is excluded from the aggregate forecast (i.e. test accounts or
            spammers). The "_delphi" bit is unused, but can be helpful during
            post-season analysis. <span class="any_warning">All other
            preferences should only be set by the user, unless they
            specifically ask us to do so on their behalf.</span>
            Don't disable your own admin bit unless you really want to lock
            yourself out.
         </p><?php
            createInput('User\'s Email', 'user_email');
            createInput('Preference Name', 'pref_name');
            createInput('Preference Value', 'pref_value');
            createSubmit('user_preferences');
         ?></form>
      </div>
   </div>
   <div class="box_section">
      <div class="box_section_title">
         Epicast Settings
         <div class="box_section_subtitle">
            Update season info, round info, and timing of emails and submission.
         </div>
      </div>
      <?php
      // get season year for sanity checking
      if(getYearForCurrentSeason($output) !== 1) {
         fail('Unable to get season info.');
      }
      $minEpiweek = $output['season']['year'] * 100 + 30;
      $maxEpiweek = ($output['season']['year'] + 1) * 100 + 29;
      // attempt to update
      if(hasSubmission('update_season')) {
         $firstEpiweek = intval(getSafeValue('first_contest_round'));
         $lastEpiweek = intval(getSafeValue('last_contest_round'));
         $ok = true;
         if($firstEpiweek < $minEpiweek || $firstEpiweek > $maxEpiweek) {
            fail('First epiweek was not in the season.');
            $ok = false;
         }
         if($lastEpiweek < $minEpiweek || $lastEpiweek > $maxEpiweek) {
            fail('Last epiweek was not in the season.');
            $ok = false;
         }
         if($lastEpiweek < $firstEpiweek) {
            fail('First epiweek was after last epiweek.');
            $ok = false;
         }
         $temp = array();
         if(!$ok || updateSeason($temp, $firstEpiweek, $lastEpiweek) !== 1) {
            fail('Unable to update season info.');
         } else {
            success('Updated season info.');
         }
      }
      if(hasSubmission('update_round')) {
         $currentEpiweek = intval(getSafeValue('current_contest_round'));
         $deadline = getSafeValue('round_deadline');
         $ok = true;
         if($currentEpiweek < $minEpiweek || $currentEpiweek > $maxEpiweek) {
            fail('Current epiweek was not in the season.');
            $ok = false;
         }
         $temp = array();
         if(!$ok || updateRound($temp, $currentEpiweek, $deadline) !== 1) {
            fail('Unable to update round info.');
         } else {
            success('Updated round info.');
         }
      }
      if(hasSubmission('update_tasks')) {
         $emailReminders = getSafeValue('email_reminders');
         $dryRunForeast = getSafeValue('dry_run_forecast');
         $submitForecast = getSafeValue('submit_forecast');
         $temp = array();
         if(setTaskDate($temp, $TASK_SEND_REMINDER_EMAILS, $emailReminders) !== 1 ||
            setTaskDate($temp, $TASK_DRY_RUN_FORECAST, $dryRunForeast) !== 1 ||
            setTaskDate($temp, $TASK_SUBMIT_FORECAST, $submitForecast) !== 1
         ) {
            fail('Unable to update task info.');
         } else {
            success('Updated task info.');
         }
      }
      // get fresh values
      if(getEpiweekInfo($output) !== 1) {
         fail('Unable to get round info.');
      }
      if(getYearForCurrentSeason($output) !== 1) {
         fail('Unable to get season info.');
      }
      if(getTaskDate($output, $TASK_SEND_REMINDER_EMAILS) !== 1 ||
         getTaskDate($output, $TASK_DRY_RUN_FORECAST) !== 1 ||
         getTaskDate($output, $TASK_SUBMIT_FORECAST) !== 1
      ) {
         fail('Unable to get task info.');
      }
      ?>
      <div>
         <form method="POST"><p>
               When new FluView data is scraped, these values determine whether
               notification emails are sent and whether scores are generated for
               the previous week. See <a href="https://github.com/cmu-delphi/flu-contest/blob/master/src/epicast/fluv_updater.py">fluv_updater.py</a>.
            </p><?php
            createInput('First Contest Round', 'first_contest_round', $output['season']['first_epiweek']);
            createInput('Last Contest Round', 'last_contest_round', $output['season']['last_epiweek']);
            createSubmit('update_season');
         ?></form>
         <form method="POST"><p>
            The round number (data as-of this issue) determines how user
            predictions are saved. Submissions are stored separately each week,
            depending on what data was available at the time. This is
            incremented automatically when the forecast is submitted.
            The deadline is only used in communication with the user and doesn't
            have any real effect. The deadline shared with users is actually
            this value minus 12 hours (i.e. is this is midnight, then we tell
            users the preceding noon).
         </p><?php
            createInput('Current Contest Round', 'current_contest_round', $output['epiweek']['round_epiweek']);
            createInput('Current Round Deadline (ET)', 'round_deadline', $output['epiweek']['deadline']);
            createSubmit('update_round');
         ?></form>
         <form method="POST"><p>
            These values are the date/time of next execution for the indicated
            task. The tasks repeat on an exact interval of
            604,800 seconds, or one week&mdash;except when daylight savings time
            starts or ends, then one week plus or minus an hour. To effectively
            disable Epicast, set these far into the future (e.g. 2030).
            <span class="any_warning">If you set these values in the past, the
            corresponding tasks will be executed immediately.</span>
         </p><?php
            createInput('Send Email Reminders (ET)', 'email_reminders', $output['task'][$TASK_SEND_REMINDER_EMAILS]);
            createInput('Dry Run Forecast (ET)', 'dry_run_forecast', $output['task'][$TASK_DRY_RUN_FORECAST]);
            createInput('Submit Forecast (ET)', 'submit_forecast', $output['task'][$TASK_SUBMIT_FORECAST]);
            createSubmit('update_tasks');
         ?></form>
      </div>
   </div>
   <div class="box_section">
      <div class="box_section_title">
         Reset for New Season
         <div class="box_section_subtitle">
            Archive the database for the past season, and create a new database for the coming season.
         </div>
      </div>
      <?php
      if(hasSubmission('reset_epicast')) {
         fail('Not implemented.');
      }
      ?>
      <div>
         <form method="POST"><p>
            <span class="any_warning">This effectively wipes the database. All
            forecasts and users will be lost. This should only be done once, at
            the start of each season.</span> A single user account will be
            created&mdash;yours&mdash;and it'll have the admin bit set. Note
            that your user ID will change, so you'll have to wait for your
            activation email before you can login again.
            The existing database will be backed up, and a new one will be
            created. It is not easy to restore this backup, so make sure you
            really, really want to do this. Good luck!
         </p><p>
            <?php
            $year1 = $output['season']['year'] + 1;
            $year2 = $output['season']['year'] + 2;
            ?>
            This will reset Epicast for the <?php printf('%d&ndash;%d', $year1, $year2); ?> season.
         </p><?php
            createInput('First Contest Round', 'first_contest_round', $year1 . '??');
            createInput('Last Contest Round', 'last_contest_round', $year2 . '??');
            createInput('First Round Deadline (ET)', 'round_deadline', $year1 . '-??-?? 23:59:59');
            createSubmit('reset_epicast');
         ?></form>
      </div>
   </div>
</div>
<?php
}
require_once('common/footer.php');
?>
