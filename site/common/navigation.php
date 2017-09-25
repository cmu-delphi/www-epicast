<?php
function showRegion($r) {
   if($r['completed']) {
      $class = 'box_region_nav_complete';
      $icon = 'fa-check';
   } else {
      $class = 'box_region_nav_incomplete';
      $icon = 'fa-question';
   }
   ?>
   <div class="box_region_nav <?= $class ?>" onClick="submit('forecast_<?= $r['id'] ?>')" onmouseover="colorMap(<?= $r['id'] - 1 ?>)">
      <div class="box_region_nav_content">
         <div class="box_region_nav_content_stack" style="top: 20px;">
            <img class="img_flag_large" src="images/flags/icon_<?= sprintf('%02d', $r['id']) ?>.png"></img>
            <div style="margin-top: -8px;">
               <?= htmlspecialchars($r['name']) ?>
            </div>
         </div>
         <div class="box_region_nav_content_stack" style="top: 2px;">
            <span style="font-size: 1.3em; opacity: 0.2;"><i class="fa <?= $icon ?> fa-5x"></i></span>
         </div>
      </div>
   </div>
   <?php
}
function showNavigation($output, $regionID=-1) {
   $missing = 0;
   $submitted = 0;
   foreach($output['regions'] as $r) {
      if($r['completed']) {
         $submitted++;
      } else {
         $missing++;
      }
   }
   ?>
   <div class="box_section">
      <div class="bot_stat_value centered">
         <i class="fa fa-check"></i> Submitted: <?= $submitted ?>&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-question"></i> Still Missing: <?= $missing ?>
      </div>
      <?php
      foreach($output['regions'] as $r) {
         createForm('forecast_' . $r['id'], 'forecast.php#top', array('region_id', $r['id']));
      }
      ?>
      <div class="centered">
         <?php
         foreach($output['regions'] as $r) {
            showRegion($r);
         }
         ?>
      </div>
      
      
      <div class="bot_stat_value centered">
         <i class="fa fa-check"></i> Submitted: <?= $submitted ?>&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-question"></i> Still Missing: <?= $missing ?>
      </div>
      <?php
      foreach($output['regions'] as $r) {
         createForm('forecast_' . $r['id'], 'forecast.php#top', array('region_id', $r['id']));
      }
      ?>
      <div class="centered">
         <?php
         foreach($output['regions'] as $r) {
            showRegion($r);
         }
         ?>
      </div>
      
      
      
      
   </div>
   <?php
}
?>
