<?php
$skipLogin = true;
$fullHeader = true;
require_once('common/header.php');
require_once('/var/www/html/secrets.php');
if($error) {
   return;
}
function checkCaptcha($captcha, $ip) {
   $key = Secrets::$epicast['captcha_key'];
   $resp = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $key . "&response=" . $captcha . "&remoteip=" . $ip);
   $obj = json_decode($resp);
   return $obj->success === true ? 1 : 0;
}
?>
<script src='https://www.google.com/recaptcha/api.js'></script>
<div class="box_article">
   <div class="centered">
      <?php
      if(isset($_REQUEST['name']) && isset($_REQUEST['email'])) {
         $name = mysqli_real_escape_string($_REQUEST['name']);
         $email = mysqli_real_escape_string($_REQUEST['email']);
         if(empty($name)) {
           $name = 'Anonymous Epicaster';
         }
         if(!isset($_REQUEST['check_terms']) || !isset($_REQUEST['check_age'])) {
            fail('Sorry, we need you to indicate your agreement with the research terms and verify that you are old enough to participate. Please try again.');
         } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            fail('Uh oh, that email address doesn\'t look right. Please try again.');
         } else if(checkCaptcha($_REQUEST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']) != 1) {
            fail('Uh oh, could not verify captcha. Please try again later.');
         } else if(registerUser($output, $name, $email, $epicastAdmin['email']) != 1) {
            fail('Uh oh, something went wrong on our end. Please try again later.');
         } else {
            ?>
            <div class="box_decision">
               <div class="box_decision_title">Email Sent</div>
               <p>
                  Good news, your User ID is being emailed to you now!<br />
                  (Please remember to check your junk/spam folder!)<br /><br />
                  <b><a href="index.php">Login Here</a></b>
               </p>
            </div>
            <?php
         }
      } else {
      ?>
         <div class="box_decision" style="width: 80%;">
            <div class="box_decision_title">Research Overview</div>
            <p style="text-align: left;">
               In this research study, being conducted by Carnegie Mellon University, we ask you each Friday during the flu season to predict current and future flu activity within one or more Health and Human Services regions of the United States. You will be able to enter and submit your predictions online using any modern web browser, and we expect that the entire process will take no more than two minutes per region. There are no expected risks or benefits to participants in this study.
            </p><p style="text-align: left;">
               Your privacy and the confidentiality of your predictions will be strictly protected; we will not share your email address or your individual predictions without your prior written consent. The study is entirely voluntary, and you are free to stop participating or withdraw entirely at any time.
            </p><p style="text-align: left;">
               The primary contact is <?= $epicastAdmin['name'] ?> (Email: <?= $epicastAdmin['email'] ?>), and the principal investigator is Roni Rosenfeld (Email: Roni.Rosenfeld@cs.cmu.edu; Office: GHC 8103). If you have questions pertaining to your rights as a research participant, or to report concerns with this study, you should contact the Carnegie Mellon University Office of Research Integrity and Compliance (Email: irb-review@andrew.cmu.edu).
            </p>
            <div class="box_decision_title">New User Signup</div>
            <form id="signup" method="POST" action="signup.php">
               <div style="display: inline-block; text-align: right;">
                  <div style="height: 32px; padding-right: 4px;">I have read and understood the above:</div>
                  <div style="height: 32px; padding-right: 4px;">I am 18 years old or older:</div>
                  <div style="height: 32px; padding-right: 4px;">Your email address:</div>
                  <div style="height: 32px; padding-right: 4px;">Your nickname (optional):</div>
               </div>
               <div style="display: inline-block; text-align: left;">
                  <div style="height: 32px;"><input type="checkbox" name="check_terms" /></div>
                  <div style="height: 32px;"><input type="checkbox" name="check_age" /></div>
                  <div style="height: 32px;"><input type="text" name="email" value="" maxlength="64" /></div>
                  <div style="height: 32px;"><input type="text" name="name" value="" maxlength="32" /></div>
               </div>
               <div style="height: 1px;"></div>
               <div class="g-recaptcha" data-sitekey="6Lc4fBgUAAAAACYFwGhajRyg6LPwC03A5BdwJ_vF" style="display: inline-block;"></div>
            </form>
            <?php button('fa-arrow-circle-right', 'Email My User ID', "submit('signup')"); ?>
         </div>
         <?php
      }
      ?>
   </div>
</div>
<?php
require_once('common/footer.php');
?>
