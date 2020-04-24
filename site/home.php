<?php
require('common/header.php');
require('common/navigation.php');
if($error) {
   return;
}
$_offline = false;
if($_offline) {
   print("<h2 style=\"text-align: center\">Crowdcast is temporarily unavailable. Please check back soon.</h2>");
} else {
?>
<script src="js/state_colors.js"></script>
<script src="js/us-map/lib/raphael.js"></script>
<script src="js/us-map/jquery.usmap.js"></script>
<div class="box_article centered">
   <?php
   //Round info
   if(getEpiweekInfo($dbh, $output) != 1) {
      fail('Error loading epiweek info');
   }
   //User stats
   
//    echo "calling getUserStats";
   if(getUserStats($dbh, $output, $output['user_id'], $output['epiweek']['round_epiweek']) != 1) {
      fail('Error loading user info');
   }
   
// Hospitalization forecasts: turned off for this season.
//    if(getUserStats_hosp($dbh, $output, $output['user_id'], $output['epiweek']['round_epiweek']) != 1) {
//       fail('Error loading user info');
//    }
   
   if(getRegions($dbh, $output, $output['user_id']) != 1) {
      fail('Error loading region info');
   }
   
   ?>


    <div class="box_section">
        <div class="box_section_title">
            Post <?= formatEpiweek($output['epiweek']['round_epiweek']) ?> Forecast
            
            <div class="box_section_subtitle">
                Due by 10:00 AM (ET) on <?= date('l, M j, Y', $output['epiweek']['deadline_timestamp']) ?>.
            </div>
            
    <?php
    createLink('FAQ', 'FAQ.php#top');
    ?>
            
        </div>
    </div>




    <div>
         <?php
         $time = $output['epiweek']['remaining'];
         $value = '';
         $unit = ' until due';
         if($time['days'] < 2) {
            $time['hours'] += 24 * $time['days'];
            $time['days'] = 0;
         }
         if($time['hours'] < 2) {
            $time['minutes'] += 60 * $time['hours'];
            $time['hours'] = 0;
         }
         if($time['minutes'] < 2) {
            $time['seconds'] += 60 * $time['minutes'];
            $time['minutes'] = 0;
         }
         if($time['days'] > 0) {
            $value = $time['days'];
            $unit = 'days' . $unit;
         } else if ($time['hours'] > 0) {
            $value = $time['days'] * 24 + $time['hours'];
            $unit = 'hours' . $unit;
         } else if ($time['minutes'] > 0) {
            $value = '<i class="fa fa-exclamation-triangle"></i>';
            $unit = 'due very soon';
//             $value = $time['hours'] * 60 + $time['minutes'];
//             $unit = 'minutes' . $unit;
//          } else if ($time['seconds'] > 0) {
//             $value = $time['minutes'] * 60 + $time['seconds'];
//             $unit = 'seconds' . $unit;
         } else {
            $value = '<i class="fa fa-exclamation-triangle"></i>';
            $unit = 'past due';
         }
         ?>
        <div class="box_stat">
            <div class="bot_stat_value"><?= $value ?></div>
            <div class="bot_stat_description"><?= $unit ?></div>
        </div>
        
        <div class="box_stat">
            <div class="bot_stat_value"><?= formatEpiweek($output['epiweek']['data_epiweek']) ?></div>
            <div class="bot_stat_description">latest available data</div>
        </div>
    </div>
   <?php
   // This approach will work next week, but not for w12, because
   // stat_competed counts regions the user may have already submitted
   // but aren't assigned anymore :(
   //$numRegion=11;
   //if($output['stat_completed'] >= $numRegion) {
   $missing = 0;
   foreach (get_user_forecast_regions($dbh, $output['user_id']) as $ri) {
      if (!$output['regions'][$ri]['completed']) { $missing++; }
   }
   if ($missing == 0) {
      ?>
      <!-- <?= $output['stat_completed'] ?> stat_completed -->
      
      <div class="box_section">
         <div class="box_section_title">
            Nice job, you're finished!
            <div class="box_section_subtitle">
               But you can still edit your forecasts below!
            </div>
         </div>
      </div>
      <?php
   }
   ?>
   <?php showNavigation($dbh,$output); ?>


   <p class="centered"><i>Hover a button above to see which states are in that region or the location of the state in the map below.</i></p>
   <div id="map_container"></div>
   
   <!-- news -->
   <div class="box_section">
       <div class="box_section_title">
           COVID-19 in the News
           <div class="box_section_subtitle">
               Use the latest COVID-19 news to make better forecasts!
           </div>
       </div>
       <div id="news" class="center" style="width: 75%; margin-left: auto; margin-right: auto;">
           <div class="box_list left">
               <ul>
                   <li><a target="_blank" href="https://www.cdc.gov/media/dpk/diseases-and-conditions/coronavirus/coronavirus-2020.html">CDC Newsroom: COVID-19</a></li>
                   <li><a target="_blank" href="https://news.google.com/topics/CAAqBwgKMJy5lwswj-KuAw">Google News: COVID-19</a></li>
                   <li><a target="_blank" href="https://news.yahoo.com/coronavirus">Yahoo News: Coronavirus</a></li>
                   <li><a target="_blank" href="https://www.msn.com/en-us/news/coronavirus">MSN: Coronavirus</a></li>
                   <li><a target="_blank" href="https://www.reuters.com/live-events/coronavirus-6-id2921484">Reuters Live: Coronavirus</a></li>
                   <li><a target="_blank" href="https://apnews.com/VirusOutbreak">AP News: Virus Outbreak</a></li>
               </ul>
           </div>
           <div class="box_list left">
               <ul>
                   <li><a target="_blank" href="https://graphics.reuters.com/HEALTH-CORONAVIRUS-USA/0100B5K8423/index.html">Reuters Infographic: COVID-19 case map</a></li>
                   <li><a target="_blank" href="https://arcg.is/0fHmTX">Johns Hopkins COVID-19 Case Tracker</a></li>
                   <li><a target="_blank" href="https://www.ft.com/coronavirus-latest">Financial Times: Coronavirus Tracker</a></li>
                   <li><a target="_blank" href="https://ncov2019.live/">nCov2019.live COVID-19 Aggregator</a></li>
                   <li><a target="_blank" href="https://healthweather.us/">Kinsa US Health Weather Map</a></li>
               </ul>
           </div>
       </div>
   </div>
   <!-- /news -->

   <!-- social -->
   <div id="share" class="box_section">
     <div class="box_section_title">
       Help Spread The Word
       <div class="box_section_subtitle">
         Please share Crowdcast with your colleagues, friends, and family!
       </div>
     </div>
     <div class="social">
       <!-- Twitter: https://about.twitter.com/resources/buttons#tweet -->
       <a href="https://twitter.com/share" class="twitter-share-button" data-url="https://epicast.org" data-text="Your help is needed for COVID-19! Submit forecasts for science, and be featured on the leaderboard!" data-hashtags="covid19,forecast">Tweet</a>
       <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
     </div>
     <div class="social">
       <!-- Facebook: https://developers.facebook.com/docs/plugins/like-button -->
       <iframe id="share_fb" src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fepicast.org&amp;width&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;share=false&amp;height=21&amp;appId=2243057286" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px;" allowTransparency="true"></iframe>
       <script> document.getElementById("share_fb").style.width = "73px"; </script>
     </div>
     <div class="social">
       <!-- Google+: https://developers.google.com/+/web/+1button/ -->
       <div class="g-plusone" data-href="https://epicast.org"></div>
       <script type="text/javascript">
         (function() {
         var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
         po.src = 'https://apis.google.com/js/platform.js';
         var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
         })();
       </script>
     </div>
     <div class="social">
       <!-- https://www.addthis.com/dashboard -->
       <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-546288ed7b70846a" async="async"></script>
       <div class="addthis_sharing_toolbox"></div>
     </div>
   </div>
   <!-- /social -->
                
   <!-- external resources -->
   <div class="box_section">
     <div class="box_section_title">
       External Resources
       <div class="box_section_subtitle">
         COVID-19 Information and Data
       </div>
     </div>
     
     <p><a href="https://docs.google.com/spreadsheets/d/16Nn_3ZvSLnpxRyA2DkoMMzyrd11-AlGJXasS0owln88/edit#gid=0">DELPHI COVID-19 and ILI Data Sources</a> - spreadsheet updated continually</p>
   </div>
   <!-- /external resources -->
                
</div><!-- /article -->
</div><!-- /content -->
<script>
var mapStyle = {
   showLabels: false,
   labelTextStyles: {'color': 'black', 'font-weight': 'bold'},
   stateStyles: {'stroke-width': 2},
   stateSpecificStyles: {},
   stateSpecificLabelBackingStyles: {},
   stateHoverStyles: {},
};
function resetMap() {
   $('#map_container').empty();
   $('#map_container').append('<div id="map" style="width: 350px; height: 250px; margin-left: auto; margin-right: auto;">');
}
function colorMap(region) {
   resetMap();
   mapStyle.stateSpecificStyles = getStates(region);
   mapStyle.stateSpecificLabelBackingStyles = getStates(region);
   $('#map').usmap(mapStyle);
}
function clearMap() {
   colorMap(0);
}
$(document).ready(function() {
   clearMap();
});
</script>
<?php
}require('common/footer.php');
?>
