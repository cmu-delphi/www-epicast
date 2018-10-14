<!-- spoof the value of the current epiweek. Normally that comes from the table
ec_fluv_round, but to pretend like it's some other week in the past you can
override the value from the database. But you'll also have to fetch unstable
wILI from a different season, and the number of weeks in the season depends on
what the year is (i.e. the 2014-2015 season has an extra week: 2014w53). I would 
start with overriding the round counter from the database and then
debugging/fixing until everything works. -->


<?php
require_once('common/header_mturk.php');
require_once('common/navigation.php');
// If mturkId is not provided, prompt to enter a name
if (!$_GET['mturkId']) {
?>
  <!-- <script type='text/javascript'>
    var answer = prompt('Please enter your full mturkID to start the survey. Please make sure to forecast for all of the 16 locations to get paid.');
    window.location.href = 'mturk_entry.php?mturkId=' + answer;
  </script> -->
  <form id="frm1" action="mturk_entry.php">
    Mturk ID: <input type="text" name="mturkId"><br>
    <input type="button" onclick="myFunction()" value="Submit">
  </form>

  <script>
    function myFunction() {
        document.getElementById("frm1").submit();
    }
  </script>

<?php
} else {
  // If provided, set session variable and redirect to forecast_mturk
  registerUser_mturk($_GET['mturkId']);
  $_SESSION['mturkId'] = $_GET['mturkId'];
?>
  <script type='text/javascript'>
    window.location.href = 'forecast_mturk.php?id=1';
  </script>
<?php
}
?>
