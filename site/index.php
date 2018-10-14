<?php
$skipLogin = true;
$fullHeader = true;
require_once('common/header.php');
if($error) {
   return;
}
?>
<div class="box_article">
   <div class="centered">
      <h3>
         Check out the <a href="scores.php">leaderboards</a>!
      </h3>
      <?php
      if($hash == null) {
         ?>
         <div class="box_decision">
            <?php
            if(isset($_SESSION['fluv_login_fail']) && $_SESSION['fluv_login_fail']) {
               //User entered the wrong ID
               fail('Login failed.');
               ?>
               <p>
                  Please double-check your ID and try again.
                  <br />
                  Lost your ID? Click <a href="signup.php">here</a> to have it sent to you.
               </p>
               <?php
               $_SESSION['fluv_login_fail'] = false;
            }
            ?>
            <div class="box_decision_title">Existing Users</div>

            <form id="launch" method="POST" action="launch.php">
               <p>
                  Enter your User ID to continue:
                  <br />
                  <input type="text" name="user" value="" maxlength="8" size="8" />
               </p>
            </form>


            <?php button('fa-sign-in', 'Login', "submit('launch')"); ?>

            
            <p>&nbsp;</p><div class="box_decision_title">New Users</div><p><b><a href="signup.php">Sign up here!</a></b></p>
            <!--<p>&nbsp;</p><div class="box_decision_title">Lost User ID</div><p><b><a href="userid.php">Get it here!</a></b></p>-->
         </div>
      <?php
      }

      else {
      ?>
         <div class="box_decision">
            <div class="box_decision_title">Click to Continue</div>
            <p>
               Welcome back!
            </p>
            <?php button('fa-home', 'Return Home', "navigate('launch.php')"); ?>
            <script>
               //Attempt to redirect automatically
               navigate('launch.php');
            </script>
         </div>
      <?php
      }
      ?>
   </div>
</div>
<?php
require_once('common/footer.php');
?>
