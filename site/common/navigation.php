<script>
  function redirect(url) {
    window.location.href = url;
  }

  function onRegionInDropdownSelected(ev) {
    submit(ev.value);
  }
</script>


<?php
require_once('common/database.php');
?>


<?php
function showRegionButton($r) {
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

function showRegionsDropdownList($regions) {
  ?>
  <div>
    <select onchange="onRegionInDropdownSelected(this)">
      <option value="">All Other Historical Data </option>
      <?php
      foreach ($regions as $region) {
//         $completionStateStr = $r['completed'] ? "" : " (not submitted)";
        $optionName = htmlspecialchars($region['name']);
        ?>
        <option value="forecast_<?= $region['id'] ?>"><?= $optionName ?></option>
        <?php
      }
      ?>
    </select>
  </div>
  <?php
}

/**
 * Create buttons to navigate to per-age group hospitalization pages
 * @param $input Array of (flusurv_name, name, ages) tuples
 */
function showNavigation_hosp($output, $getUrl) {
  // Print container for per-age group buttons
  ?>
  <div class="box_section">

  <div class="bot_stat_value centered">
     Per Age Group Hospitalization
  </div>

  <?php
  foreach ($output['ageGroups'] as $ageGroup) {
    if($ageGroup['completed']) {
       $class = 'box_region_nav_complete';
       $icon = 'fa-check';
    } else {
       $class = 'box_region_nav_incomplete';
       $icon = 'fa-question';
    }
    ?>
      <!-- <button onclick="redirect('<?= ($getUrl . "?id=" . $ageGroup['id']) ?>')"><?= $ageGroup['ages'] ?></button>
      <br /> -->
      <div class="box_region_nav <?= $class ?>" onClick="redirect('<?= ($getUrl . "?id=" . $ageGroup['id']) ?>')">
         <div class="box_region_nav_content">
            <div class="box_region_nav_content_stack" style="top: 20px;">

               <img class="img_flag_large age_group_img" src="images/flags/ages_<?= sprintf('%01d', $ageGroup['id']) ?>.png"></img>

               <div style="margin-top: -8px;">
                  <?= htmlspecialchars($ageGroup['ages']) ?>
               </div>
            </div>
            <div class="box_region_nav_content_stack" style="top: 2px;">
               <span style="font-size: 1.3em; opacity: 0.2;"><i class="fa <?= $icon ?> fa-5x"></i></span>
            </div>
         </div>
      </div>    <?php
  }

  ?>
  </div>
  <?php
}

function showNavigation($output, $regionID=-1) {
    $missing = 0;
    $submitted = 0;
    $regionIDs = get_user_forecast_regions($output['user_id']);
    echo("3");
    echo($regionIDs);

   foreach ($regionIDs as $i) {
        $r = $output['regions'][$i];
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
       foreach ($regionIDs as $i) {
           $r = $output['regions'][$i];
           createForm('forecast_' . $r['id'], 'forecast.php#top', array('region_id', $r['id']));
       }
       ?>

       <div class="centered">
           <?php
           $regionsList = $output['regions'];
           foreach ($regionIDs as $i) {
               showRegionButton($regionsList[$i]);
           }
           ?>
       </div>

   </div>
   <?php
}
?>
