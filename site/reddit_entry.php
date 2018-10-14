<?php
require_once('common/header_mturk.php');
require_once('common/navigation.php');
// If mturkId is not provided, prompt to enter a name
if (!$_GET['redditName']) {
?>
  <script type='text/javascript'>
    var answer = prompt('Please enter your user name to start the survey.');
    window.location.href = 'reddit_entry.php?redditName=' + answer;
  </script>
<?php
} else {
  // If provided, set session variable and redirect to forecast_mturk
  registerUser_mturk($_GET['redditName']);
  $_SESSION['redditName'] = $_GET['redditName'];
?>
  <script type='text/javascript'>
    window.location.href = 'forecast_reddit.php?id=1';
  </script>
<?php
}
?>
