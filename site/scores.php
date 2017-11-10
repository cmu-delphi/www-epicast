<?php
$skipLogin = true;
require('common/header.php');
if($error) {
   return;
}
function createTable(&$leaderboard) {
   global $hash;
   $found = false;
   ?>
   <table cellspacing="0">
      <tr class="leaderboard"><th>Rank</th><th>User</th><th>Score</th></tr>
      <?php
      foreach($leaderboard as &$entry) {
         $found |= ($entry['hash'] === $hash);
         ?>
         <tr class="leaderboard">
            <td>#<?= $entry['rank'] ?></td>
            <td><?= $entry['name'] . (($entry['hash'] === $hash) ? '<b>*</b>' : '') ?></td>
            <td><?= $entry['score'] ?></td>
         </tr>
         <?php
      }
      ?>
   </table>
   <?php
   if($found) {
      ?><p class="center"><i><b>*</b>That's you - way to go!</i></p><?php
   }
   return $found;
}
$limit = 10;
?>
<div class="box_article centered">
   <div class="box_section">
      <div class="box_section_title">
         Leaderboards
         <div class="box_section_subtitle">
            The best of the best.
         </div>
      </div>
      <div>
         <div class="box_leaderboard">
            <?php
            if(getLeaderboard($output, 'total', $limit) !== 1) {
               fail('Error loading total scores');
            } else {
               ?>
               <h1 style="margin-bottom: 0px;">Overall Score</h1>
               <?php
               createTable($output['leaderboard']);
            }
            ?>
         </div>
         <div class="box_leaderboard">
            <?php
            if(getLeaderboard($output, 'last', $limit) !== 1) {
               fail('Error loading last scores');
            } else if(getEpiweekInfo($output) !== 1) {
               fail('Error loading epiweek info');
            } else {
               ?>
               <h1 style="margin-bottom: 0px;"><?= formatEpiweek(addEpiweeks($output['epiweek']['data_epiweek'],-1)) ?> Score</h1>
               <?php
               createTable($output['leaderboard']);
            }
            ?>
         </div>
      </div>
      
      
<!--       <div class="box_section_title">
         Get to Know Your Contributions To the World!
      </div> -->
      
   </div>
</div>
<?php
require('common/footer.php');
?>
