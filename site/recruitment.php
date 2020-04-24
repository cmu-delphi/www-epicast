 <?php
require('common/headerR.php');
require('common/navigationR.php');
require_once('common/settings.php');

if($error) {
    return;
}

$_offline = false;
if($_offline) {
    print("<h2 style=\"text-align: center\">Crowdcast is temporarily unavailable. Please check back soon.</h2>");
} else {
?>

<?php
if (count($_REQUEST) > 0) {$location = ($_REQUEST['location']);}
else {$location = 'CA';}
$map = array(
        'CA' => [1, 10, 56], // [nat, hhs9, ca]
        'GA' => []
);
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
    //List of all regions
    if(getRegions($dbh, $output, 1) != 1) {
        fail('Error loading region info');
    }
    //List of all age groups
    if(getAgeGroups($dbh, $output, 1) != 1) {
        fail('Error loading age group info');
    }
    ?>
    <div class="box_section">
        <div class="box_section_title">
            Post <?= formatEpiweek($output['epiweek']['round_epiweek']) ?> Forecast
            <div class="box_section_subtitle">
                Due by 10:00 AM (ET) on <?= date('l, M j, Y', $output['epiweek']['deadline_timestamp']) ?>.
            </div>
        </div>
        <div>
            <?php
            $time = $output['epiweek']['remaining'];
            $time['days'] = $time['days'];
            $value = '';
            $unit = ' until due';
            if($time['days'] < 2) {
                $time['hours'] += 24 * $time['days']+10;
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
                $value = $time['hours'] * 60 + $time['minutes'];
                $unit = 'minutes' . $unit;
            } else if ($time['seconds'] > 0) {
                $value = $time['minutes'] * 60 + $time['seconds'];
                $unit = 'seconds' . $unit;
            } else {
                $value = '<i class="fa fa-exclamation-triangle"></i>';
                //$unit = 'past due';
                $unit = 'due very soon';
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
            <div class="box_stat">
                <div class="bot_stat_value"><?= getDeltaWeeks($output['epiweek']['round_epiweek'], $output['epiweek']['season']['end']) ?></div>
                <div class="bot_stat_description">weeks remaining in season</div>
            </div>
        </div>
    </div>


<!--    <div>-->
<!--        <a target="_blank" href="images/tutorial.gif">Click here for a BRIEF TUTORIAL on how to enter your forecast</a>-->
<!--    </div>-->


    <?php
    $targets = array();
    foreach($output['regions'] as $r){
        if (in_array($r['id'], $map[$location])) {
            array_push($targets, $r);
        }
    }
    showNavigation($dbh,$targets);
    ?>

<!--    <p class="centered"><i>Hover a button above to see which states are in that region or the location of the state in the map below.</i></p>-->
<!--    <div id="map_container"></div>-->

    <div class="bot_stat_value centered">
       ~~ Coming Soon ~~
    </div>
    <div class="bot_stat_value centered">
       Per Age Group Hospitalization Forecast for <?php echo $location ?>
    </div>

    <?php
    $getUrl = 'forecast_hosp_recruitment.php';
    showNavigation_hosp($output, $getUrl);
    ?>


    <div class="box_section">
        <div class="box_section">
            <div class="box_section_title">
                Flu in the News
                <div class="box_section_subtitle">
                    Use the latest flu news to make better forecasts!
                </div>
            </div>
            <!-- news -->
            <div class="center" style="width: 75%; margin-left: auto; margin-right: auto;">
                <a target="_blank" href="https://www.google.com/search?hl=en&gl=us&tbm=nws&q=flu+news">Google News</a>
            </div>
            <!-- /news -->
        </div>

        <div class="box_section">
            <div class="box_section_title">
                Help Spread The Word
                <div class="box_section_subtitle">
                    Please share Crowdcast with your colleagues, friends, and family!
                </div>
            </div>
            <div>
                <!-- Twitter: https://about.twitter.com/resources/buttons#tweet -->
                <a href="https://twitter.com/share" class="twitter-share-button" data-url="https://epicast.org" data-text="Your help is needed this flu season! Submit forecasts for science, and be featured on the leaderboard!" data-hashtags="flu,forecast">Tweet</a>
                <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
            </div>
            <div>
                <!-- Facebook: https://developers.facebook.com/docs/plugins/like-button -->
                <iframe id="share_fb" src="//www.facebook.com/plugins/like.php?href=http%3A%2F%2Fepicast.org&amp;width&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;share=false&amp;height=21&amp;appId=2243057286" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px;" allowTransparency="true"></iframe>
                <script> document.getElementById("share_fb").style.width = "101px"; </script>
            </div>
            <div>
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
            <div>
                <!-- https://www.addthis.com/dashboard -->
                <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-546288ed7b70846a" async="async"></script>
                <div class="addthis_sharing_toolbox"></div>
            </div>
        </div>
        <div class="box_section">
            <div class="box_section_title">
                External Resources
                <div class="box_section_subtitle">
                    Flu Information and Data
                </div>
            </div>
            <div class="box_list left">
                <div class="box_decision_title centered" style="width: 100%"><i class="fa fa-info-circle"></i> Information</div>
                <div>
                    <i class="fa fa-external-link"></i>
                    <a target="_blank" href="https://www.cdc.gov/flu/index.htm">CDC's Flu Portal</a>
                </div>
                <div>
                    <i class="fa fa-external-link"></i>
                    <a target="_blank" href="https://en.wikipedia.org/wiki/Influenza">Influenza on Wikipedia</a>
                </div>
                <div>
                    <i class="fa fa-external-link"></i>
                    <a target="_blank" href="https://www.cdc.gov/flu/weekly/overview.htm">CDC's definition of ILI</a>
                </div>
                <div>
                    <i class="fa fa-external-link"></i>
                    <a target="_blank" href="http://www.kdheks.gov/flu/surveillance.htm">Kansas defintion of ILI</a>
                </div>
            </div>
            <div class="box_list left">
                <div class="box_decision_title centered" style="width: 100%"><i class="fa fa-line-chart"></i> Data</div>
                <div>
                    <i class="fa fa-external-link"></i>
                    <a target="_blank" href="https://gis.cdc.gov/grasp/fluview/fluportaldashboard.html">CDC's FluView WebApp</a>
                </div>
                <div>
                    <i class="fa fa-external-link"></i>
                    <a target="_blank" href="https://www.ncdc.noaa.gov/">NOAA's Climate Normals</a>
                </div>
                <div>
                    <i class="fa fa-external-link"></i>
                    <a target="_blank" href="https://www.google.org/flutrends/us/#US">Google Flu Trends</a>
                </div>
                <div>
                    <i class="fa fa-external-link"></i>
                    <a target="_blank" href="http://www.healthtweets.org/">Flu Nowcasting using Twitter</a>
                </div>
            </div>
            <div class="box_list left">
                <div class="box_decision_title centered" style="width: 100%"><i class="fa fa-book"></i> Literature</div>
                <div>
                    <i class="fa fa-external-link"></i>
                    <a target="_blank" href="https://delphi.midas.cs.cmu.edu/bibliography.html">The Delphi Bibliography</a>
                </div>
            </div>
        </div>
    </div>
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
