<?php
require_once('common/header.php');
if($error) {
   return;
}
if(getYearForCurrentSeason($output) !== 1) {
   die('unable to get year for current season');
} else {
   $current_season = $output['season']['year'];
}
function createPreference(&$output, $label, $prefix, $name, $type) {
   ?>
   <tr>
      <td class="right"><?= $label ?></td>
      <td>
         <?php
         if($type === 'bool' || $type === 'bool+null') {
            $selected = getPreference($output, $prefix . $name, 'int');
            ?>
            <select name="<?= $name ?>">
               <option value="0" <?= ($selected == 0) ? 'selected="selected"' : '' ?>>No</option>
               <option value="1" <?= ($selected == 1) ? 'selected="selected"' : '' ?>>Yes</option>
               <?php
               if($type === 'bool+null') {
                  ?>
                  <option value="2" <?= ($selected == 2) ? 'selected="selected"' : '' ?>>No Answer</option>
                  <?php
               }
               ?>
            </select>
            <?php
         } else if($type === 'initials') {
            $text = getPreference($output, $prefix . $name, 'string');
            ?>
            <input type="text" maxlength="3" size="3" name="<?= $name ?>" value="<?= $text ?>" />
            <?php
         } else if($type === 'check') {
            $selected = getPreference($output, $prefix . $name, 'int');
            if($selected === 1) {
               $selected = 'selected="selected"';
            } else {
               $selected = '';
            }
            ?>
            <input type="checkbox" name="<?= $name ?>" <?= $selected ?> />
            <?php
         } else {
            printf('[unknown type: %s]', $type);
         }
         ?>
      </td>
   </tr>
   <?php
}
$survey_fields = array('flu', 'vir', 'epi', 'ph', 'sml');
$email_fields = array('reminders');
$advanced_fields = array('pandemic', 'leaderboard', 'initials', 'prior', 'hospitalization');
// $advanced_fields = array('pandemic', 'leaderboard', 'initials', 'prior');
$account_fields = array();
?>
<div class="box_article centered">
   <?php
   $updatedSurvey = null;
   $updatedEmail = null;
   $updatedAdvanced = null;
   $updatedAccount = null;
   if(allSet('action', $survey_fields) && $_REQUEST['action'] == 'survey') {
      $preferences = array();
      foreach($survey_fields as $f) {
         $value = intval(mysql_real_escape_string($_REQUEST[$f]));
         $preferences['survey_' . $f] = ($value === 0 || $value === 1) ? $value : null;
      }
      $updatedSurvey = (saveUserPreferences($output, $output['user_id'], $preferences) == 1);
   }
   if(allSet('action', $email_fields) && $_REQUEST['action'] == 'email') {
      $preferences = array();
      foreach($email_fields as $f) {
         $value = intval(mysql_real_escape_string($_REQUEST[$f]));
         $preferences['email_' . $f] = ($value === 1) ? $value : 0;
      }
      $updatedEmail = (saveUserPreferences($output, $output['user_id'], $preferences) == 1);
   }
   if(allSet('action', $advanced_fields) && $_REQUEST['action'] == 'advanced') {
      $preferences = array();
      foreach($advanced_fields as $f) {
         $value = intval(mysql_real_escape_string($_REQUEST[$f]));
         $preferences['advanced_' . $f] = ($value === 1) ? $value : 0;
      }

      // set or clear the "hide" bit for each season
      $changedPrior = intval($_REQUEST['prior']) !== getPreference($output, 'advanced_prior', 'int');
      $changedPandemic = intval($_REQUEST['pandemic']) !== getPreference($output, 'advanced_pandemic', 'int');
      $hiddenSeasons = getPreference($output, 'hidden_seasons');
      $seasonBit = 1;
      for($season = 1997; $season < $current_season; $season++) {
         $name = "season_{$season}";
         // hide the season if it's not checked
         $hide = !isset($_REQUEST[$name]);
         // unhide prior seasons if that setting changed
         if($season < 2004 && $changedPrior) {
            $hide = false;
         }
         // unhide pandemic seasons if that setting changed
         if($season === 2009 && $changedPandemic) {
            $hide = false;
         }
         // set or clear the bit for this season
         if($hide) {
            $preferences['hidden_seasons'] |= $seasonBit;
         } else {
            $preferences['hidden_seasons'] &= ~$seasonBit;
         }
         // move to the bit over to the next season
         $seasonBit <<= 1;
      }

      $initials = '';
      for($i = 0; $i < min(strlen($_REQUEST['initials']), 3); $i++) {
         $ch = $_REQUEST['initials'][$i];
         if(ctype_alpha($ch) || ctype_digit($ch)) {
            $initials .= $ch;
         } else {
            $initials .= '?';
         }
      }
      if(strlen($initials) == 0) {
         $initials = $output['default_preferences']['advanced_initials'];
      }
      $preferences['advanced_initials'] = strtoupper($initials);
      $updatedAdvanced = (saveUserPreferences($output, $output['user_id'], $preferences) == 1);
   }
   if(allSet('action', $account_fields) && $_REQUEST['action'] == 'deactivate_account') {
      $preferences = array();
      $preferences['email_notifications'] = 0;
      foreach($email_fields as $f) {
         $preferences['email_' . $f] = 0;
      }
      $updatedAccount = (saveUserPreferences($output, $output['user_id'], $preferences) == 1);
   }
   if(allSet('action', $account_fields) && $_REQUEST['action'] == 'reactivate_account') {
      $preferences = array();
      $preferences['email_notifications'] = 1;
      $updatedAccount = (saveUserPreferences($output, $output['user_id'], $preferences) == 1);
   }
   if(getPreference($output, 'skip_intro', 'int') != 1) {
      saveUserPreferences($output, $output['user_id'], array('skip_intro' => 1));
      ?>
      <div class="box_section">
         <div class="box_section_title">
            Welcome to Epicast
            <div class="box_section_subtitle">Thank you for joining us!</div>
         </div>
         <div class="left">
            <p>
               Hi, <?= $output['user_name'] ?>!
               Since this is your first time to login, we thought it might be helpful to take you to the preferences page.
               Below you will find a privacy statement, email settings, and a short survey.
               By default, we will notify you by email every week as soon as the new weekly flu data is available (this usually happens by Friday afternoon), and provide a convenient link to your forecasting page.
               If you like, we can also send an email reminder before each deadline.
               The survey is completely optional, but it would help us analyze the data.
               The next time you login you will be taken straight to your forecasting home page, but you can always come back to this page by clicking the "Preferences" link in the top right corner of any Epicast page.
            </p>
         </div>
      </div>
      <?php
   }
   ?>
   <div class="box_section">
      <div class="box_section_title">
         Your Privacy
         <div class="box_section_subtitle">We won't misuse your email address or publish your forecasts.</div>
      </div>
      <div class="left">
         <p class="text_title left">Email</p>
         <p class="text_body">
            We will only use your email to communicate with you about Epicast, and we will never share your email with anyone else.
         </p>
         <p class="text_title left">Forecasts</p>
         <p class="text_body">
            We will not release your individual forecasts (unless you tell us to).
            Instead, we combine together many such forecasts to create an aggregate forecast which will be compared to forecasts made using other techniques.
            We may also want to create aggregate forecasts based on specific subsets of forecasters.
         </p>
      </div>
   </div>
   <div class="box_section">
      <div class="box_section_title">
         Account Settings
         <div class="box_section_subtitle">Update your account information.</div>
      </div>
      <div class="centered">
         <form id="form_account" method="POST">
            <p class="text_title left">Account Status</p>
            <?php
            if(isActivated($output)) {
               ?>
               <input type="hidden" name="action" value="deactivate_account" />
               <p class="text_body left">
                  Should you no longer wish to participate, you have the option to completely deactivate your account.
                  Please note that we will not email you at all if your account has been deactivated, regardless of your email settings below.
               </p>
               <?php
               button('fa-times-circle-o', 'Deactivate My Account', "submit('form_account')");
            } else {
               ?>
               <input type="hidden" name="action" value="reactivate_account" />
               <p class="text_body left">
                  Your account has been deactivated.
                  If you want to participate again, just click the button below to reactive your account!
               </p>
               <?php
               button('fa-check-square-o', 'Reactivate My Account', "submit('form_account')");
            }
            ?>
         </form>
         <?php
         if($updatedAccount === true) {
            if(isActivated($output)) {
               success('Update was successful: Welcome back!');
               ?><script> $('#account_deactivated_warning').hide(); </script><?php
            } else {
               success('Update was successful: Remember, you can reactivate your account at any time!');
            }
         } else if($updatedEmail === false) {
            fail('Update failed.');
         }
         ?>
      </div>
   </div>
   <?php
   if(isActivated($output)) {
      ?>
      <div class="box_section">
         <div class="box_section_title">
            Email Settings
            <div class="box_section_subtitle">Select which emails you would like to receive.</div>
         </div>
         <div class="centered">
            <form id="form_email" method="POST">
               <input type="hidden" name="action" value="email" />
               <p class="text_title left">Email Types</p>
               <p class="text_body left">
                  Every Friday the CDC publishes new flu data, and every Monday the forecasting round ends.
                  We'll send you an email to notify you as soon as the new data is available, as long as your account is active.
                  In addition, there are a couple of additional emails you may want to receive which you can select below.
               </p>
               <table cellspacing="0">
                  <?php
                  $prefix = 'email_';
                  $type = 'bool';
                  //createPreference($output, 'A notification when new data is available [Friday]', $prefix, 'notifications', $type);
                  createPreference($output, 'If my forecasts are missing, send me a reminder before the deadline.', $prefix, 'reminders', $type);
                  ?>
               </table>
            </form>
            <?php
            button('fa-check-circle', 'Save Email Settings', "submit('form_email')");
            if($updatedEmail === true) {
               success('Update was successful!');
            } else if($updatedEmail === false) {
               fail('Update failed.');
            }
            ?>
         </div>
      </div>
      <div class="box_section">
         <div class="box_section_title">
            Advanced Settings
            <div class="box_section_subtitle">Indicate your preferences for advanced options.</div>
         </div>
         <div class="centered">
            <form id="form_advanced" method="POST">
               <input type="hidden" name="action" value="advanced" />

               <p class="text_title left">Leaderboards</p>
               <p class="text_body left">
                  Each week we'll give you two scores based on your forecasting performance:<br />
                  <br />
                  &nbsp;<i class="fa fa-angle-right"></i>&nbsp; A score between 0 and 1000 based on how well your forecast from last week matches the newly published value for this week<br />
                  &nbsp;<i class="fa fa-angle-right"></i>&nbsp; An overall, cumulative score based on how well all of your past forecasts match all of the values published so far<br />
                  <br />
                  We show these scores on the leaderboards, but to preserve your privacy we don't associate your name with the scores.
                  However we encourage you to identify yourself using your initials for fame and glory.
                  The initials you give here <i>will</i> appear on the leaderboard.
               </p>
               <table cellspacing="0">
                  <?php
                  $prefix = 'advanced_';
                  createPreference($output, 'Show my initials', $prefix, 'leaderboard', 'bool');
                  createPreference($output, 'My initials are', $prefix, 'initials', 'initials');
                  ?>
               </table>

               <p class="text_title left">2009 Pandemic</p>
               <p class="text_body left">
                  The 2009-2010 flu season was atypical because a rare event known as a <i>Pandemic</i> occurred.
                  This is different from an <i>Epidemic</i>, which is what characterizes most flu seasons.
                  In comparison to epidemics, pandemics generally have a higher attack rate, unpredictable timing, and other altered dynamics.
                  For these reasons, the 2009 pandemic season is hidden by default.
                  However, forecasters who have experience in influenza epidemiology may find it helpful to display this atypical season.
                  Here you have the option to display the 2009 pandemic season on the chart alongside the more typical epidemic seasons.
               </p>
               <table cellspacing="0">
                  <?php
                  $prefix = 'advanced_';
                  createPreference($output, 'Show the 2009 pandemic', $prefix, 'pandemic', 'bool');
                  ?>
               </table>

               <p class="text_title left">Additional Seasons --- for National and Regional Forecasts</p>
               <p class="text_body left">
                  The flu sentinel surveillance network (ILINet) has been growing and evolving since its inception in 1997.
                  However, due to the small size of the network initially, the earliest data for the U.S. nation and the 10 HHS regions is noisy and is not available during the summer months.
                  Starting around the 2004-2005 flu season, the data becomes much more stable and is available year-round.
                  For these reasons, seasons from 1997 through 2003 for national and regional level forecasts are hidden by default.
                  However, forecasters who have experience in influenza epidemiology may find it helpful to display these additional seasons.
                  Here you have the option to display the 1997-2003 seasons on the chart alongside the later seasons for which we have more reliable data.
               </p>
               <table cellspacing="0">
                  <?php
                  $prefix = 'advanced_';
                  createPreference($output, 'Show seasons prior to 2004 for U.S. nation and regions', $prefix, 'prior', 'bool');
                  ?>


               </table>
               <p class="text_title left">Default Seasons --- for National and Regional Forecasts</p>
               <p class="text_body left">
                  By default, for national and regional level forecasts, all seasons (respectful of your preferences above) are shown on the forecasting chart.
                  If you prefer to only show a certain subset of the available seasons, you can override the default selection here.
                  This may be useful, for example, if you only want to display seasons having a strain makeup similar to that of the current season.
                </p>
               <table cellspacing="0">
                  <?php
                  $prefix = 'season_';
                  $hiddenSeasons = getPreference($output, 'hidden_seasons', 'int');
                  for($season = 1997; $season < $current_season; $season++) {
                     $show = true;
                     if($season < 2004 && getPreference($output, 'advanced_prior', 'int') !== 1) {
                       $show = false;
                     }
                     if($season == 2009 && getPreference($output, 'advanced_pandemic', 'int') !== 1) {
                       $show = false;
                     }
                     if($show) {
                        if(($hiddenSeasons & 1) === 0) {
                           $selected = 'checked="checked"';
                        } else {
                           $selected = '';
                        }
                        $name = strval($season);
                        ?>
                        <div style="display: inline-block; margin-right: 8px;"><label><?= $name ?><input type="checkbox" name="<?= $prefix . $name ?>" <?= $selected ?> /></label></div>
                        <?php
                     }
                     $hiddenSeasons >>= 1;
                  }
                  ?>
               </table>


               <p class="text_title left">Turn On Hospitalization Forecast</p>
               <p class="text_body left">
                  By selecting yes in this option, you can input your forecast for the hospitalization prediction!
               </p>
               <table cellspacing="0">
                  <?php
                  $prefix = 'advanced_';
                  createPreference($output, 'Forecast Hospitalization', $prefix, 'hospitalization', 'bool');
                  ?>
               </table>


            </form>

            <div style="margin-bottom: 8px;">&nbsp;</div>
            <?php
            button('fa-check-circle', 'Save Advanced Settings', "submit('form_advanced')");
            if($updatedAdvanced === true) {
               success('Update was successful!');
            } else if($updatedAdvanced === false) {
               fail('Update failed.');
            }
            ?>
         </div>
      </div>
      <div class="box_section">
         <div class="box_section_title">
            Optional Survey
            <div class="box_section_subtitle">It would help us if you could self-classify yourself below.</div>
         </div>
         <div class="centered">
            <form id="form_survey" method="POST">
               <input type="hidden" name="action" value="survey" />
               I have background in:<br />
               <table cellspacing="0">
                  <?php
                  $prefix = 'survey_';
                  $type = 'bool+null';
                  createPreference($output, 'Influenza', $prefix, 'flu', $type);
                  createPreference($output, 'Virology', $prefix, 'vir', $type);
                  createPreference($output, 'Epidemiology', $prefix, 'epi', $type);
                  createPreference($output, 'Public Health', $prefix, 'ph', $type);
                  createPreference($output, 'Statistics or Machine Learning', $prefix, 'sml', $type);
                  ?>
               </table><br />
            </form>
            <?php
            button('fa-check-circle', 'Save Survey Answers', "submit('form_survey')");
            if($updatedSurvey === true) {
               success('Update was successful!');
            } else if($updatedSurvey === false) {
               fail('Update failed.');
            }
            ?>
         </div>
      </div>
      <div class="box_section">
         <div class="centered" style="border-top: 1px solid #444; padding: 20px">
            Click below to continue to your home page. Happy forecasting!
            <br />
            <?php button('fa-arrow-circle-right', 'Continue', "navigate('home.php')"); ?>
         </div>
      </div>
      <?php
   }
   ?>
</div>
<?php
require_once('common/footer.php');
?>
