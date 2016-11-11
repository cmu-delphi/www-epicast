<?php
session_start();
$_SESSION['hash_fluv'] = null;
//header('Location: http://delphi.midas.cs.cmu.edu/epicast-fluv/index.php');
//return;
$skipLogin = true;
$fullHeader = true;
require_once('common/header.php');
if($error) {
   return;
}
?>
<div class="box_article">
   <div class="centered">
      You have successfully logged out.
      <br />
      <a href="index.php">Login Again</a>
   </div>
</div>
<?php
require_once('common/footer.php');
?>