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
      <?php
      if(isset($_REQUEST['name']) && isset($_REQUEST['email'])) {
         $name = mysql_real_escape_string($_REQUEST['name']);
         $email = mysql_real_escape_string($_REQUEST['email']);
         if(empty($name)) {
           $name = 'Anonymous Epicaster';
         }
         if(!isset($_REQUEST['check_terms']) || !isset($_REQUEST['check_age'])) {
            fail('Sorry, we need you to indicate your agreement with the research terms and verify that you are old enough to participate. Please try again.');
         } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            fail('Uh oh, that email address doesn\'t look right. Please try again.');
         } else if(substr(strtolower($email), -strlen('pitt.edu')) === 'pitt.edu') {
            ?>
            <p style="width: 400px; margin-left: auto; margin-right: auto; color: #800; font-style: italic; text-align: left;">
               It seems that we are unable to deliver email to pitt.edu addresses.
               We are working to resolve this issue, but in the meantime please use an alternate email if you have one.
               If you only have a pitt.edu email, please email dfarrow0@gmail.com directly to have an account created manually.
               Sorry for the inconvenience.
               <br />
               <?php button('fa-arrow-left', 'Back', "navigate('signup.php')"); ?>
            </p>
            <?php
         } else if(registerUser($output, $name, $email) != 1) {
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
               In this research study, being conducted by Carnegie Mellon University, we ask you each Friday during the 2016-2017 flu season to predict current and future flu activity within one or more Health and Human Services regions of the United States. You will be able to enter and submit your predictions online using any modern web browser, and we expect that the entire process will take no more than two minutes per region. There are no expected risks or benefits to participants in this study.
            </p><p style="text-align: left;">
               Your privacy and the confidentiality of your predictions will be strictly protected; we will not share your email address or your individual predictions without your prior written consent. The study is entirely voluntary, and you are free to stop participating or withdraw entirely at any time.
            </p><p style="text-align: left;">
               The primary contact is David Farrow (Email: dfarrow0@gmail.com), and the principal investigator is Roni Rosenfeld (Email: Roni.Rosenfeld@cs.cmu.edu; Office: GHC 8103). If you have questions pertaining to your rights as a research participant, or to report concerns with this study, you should contact the Carnegie Mellon University Office of Research Integrity and Compliance (Email: irb-review@andrew.cmu.edu).
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
