<?php

require_once('common/header.php');
require_once('common/navigation.php');
if($error) {
   return;
}

if(getYearForCurrentSeason($dbh, $output) !== 1) {
   die('unable to get year for current season');
} else {
   $current_season = $output['season']['year'];
}

//Epiweek info
if(getEpiweekInfo($dbh, $output) !== 1) {
   fail('Error loading epiweek info');
}

//List of all regions
if(getRegions($dbh, $output, $output['user_id']) !== 1) {
   fail('Error loading region details');
}

if(isset($_REQUEST['skip_instructions'])) {
   $output['user_preferences']['skip_instructions'] = 1;
   if(saveUserPreferences($dbh, $output, $output['user_id'], $output['user_preferences']) !== 1) {
      fail('Error updating preferences');
   }
}

if(isset($_REQUEST['region_id'])) {
   $regionID = intval(mysqli_real_escape_string($dbh, $_REQUEST['region_id']));
} else {
   //Default to USA National
   $regionID = 1;
}

//Specific region
if(!isset($output['regions'][$regionID])) {
   fail('Invalid region_id '.$regionID);
}

//Forecast for this round
if (loadForecast($dbh, $output, $output['user_id'], $regionID) !== 1) {
    fail('Error loading current forecast');
}
$output['regions'][$regionID]['forecast'] = $output['forecast'];

//Forecast from last round
if(loadForecast($dbh, $output, $output['user_id'], $regionID, true) !== 1) {
   fail('Error loading last week forecast');
}

$lastForecast = $output['forecast'];
$region = &$output['regions'][$regionID]; // reference because having two copies is not great
$num = count($output['regions']);

// We only want history back to 2009
$minEpiweek = 200936;

// ...and we're going forward to wk 41
$maxEpiweek = 202041;

//User's previous forecast for this region
$output['forecast'] = &$output['regions'][$regionID]['forecast'];
//Settings
// Crowdcast: always show pandemic
$showPandemic = 1; //getPreference($output, 'advanced_pandemic', 'int');

//Calculate a few helpful stats
$firstWeekOfChart = 35;
$currentWeek = $output['epiweek']['round_epiweek'];
if(($currentWeek % 100) >= $firstWeekOfChart) {
   $yearStart = intval($currentWeek / 100);
} else {
   $yearStart = intval($currentWeek / 100) - 1;
}
$seasonStart = 201936;
$seasonEnd = 202035;

//Nowcast (may or may not be available)
getNowcast($dbh, $output, addEpiweeks($currentWeek, 1), $regionID);

if(getPreference($output, 'skip_instructions', 'int') !== 1) {
   ?>
   <div class="box_section">
      <div class="box_section_title">
         How to Enter Your Forecast
         <div class="box_section_subtitle">Draw your forecast curve across the chart by clicking and dragging.</div>
      </div>
      <div class="centered">
         <p>
            <b></b><br />
            &nbsp;<i class="fa fa-angle-right"></i>&nbsp; You can draw <i>in one motion</i> the entire trajectory.<br />
            &nbsp;<i class="fa fa-angle-right"></i>&nbsp; You can edit any part of your forecast by redrawing just that part.<br />
            &nbsp;<i class="fa fa-angle-right"></i>&nbsp; You can adjust a single point by dragging it up or down.<br />
            The animation below demonstrates these actions.
            (If you don't see the animation, click <a target="_blank" href="images/tutorial.gif">here</a>.)
         </p>
         <video width="1112" height="480" controls autoplay loop>
            <source src="images/tutorial.mp4" type="video/mp4">
            Your browser does not support the video tag.
         </video>
         <p>
            <?php
            createForm('reload', 'forecast.php#top', array('region_id', $regionID, 'skip_instructions', '1'));
            button('fa-check', 'I Understand', "submit('reload')");
            ?>
         </p>
      </div>
   </div>
   <?php
} else { 
?>
<?php
// Get the next missing region for this user, or go home
$next = null;

$regionIDs = get_user_forecast_regions($dbh, $output['user_id']);
$currentID = $region['id'];

foreach($regionIDs as &$i) {
    $otherRegion = $output['regions'][$i];
    if($i != $currentID && !$otherRegion['completed'] && $next === null) {
        $next = $otherRegion['id'];
    }
}
    if($next !== null) {

createForm('forecast', 'forecast.php#top', array('region_id', $next));
} else {
createForm('forecast', 'home.php', array());
}

?>
<?php fail('Whoa, your screen is too small! Please visit this site on a non-mobile device, or try to expand your browser window. Sorry about that!', 'box_nocanvas', true); ?>
<div id="box_main_ui">
   <div id="box_status" class="box_status any_neutral right">
      <div class="box_status_line">
         <div class="box_region_label">
            <?= htmlspecialchars($region['name']) ?>
            <span style="font-size: 0.67em;">
               [<?= htmlspecialchars($region['states']) ?>]
            </span>
         </div>
         <div style="float: right;">
            <?php button('fa-upload', 'Save &amp; Continue', "submitForecast(true)", '', 'button_submit'); ?>
         </div>
         <div class="box_status_info">
            <span id="status_message">Draw your forecast by clicking and dragging across the chart below.</span>
            <span id="status_icon"><i class="fa fa-info-circle"></i></span>
         </div>
         <div style="clear: both;"></div>
      </div>

   </div>
   <div id="box_side_bar">
      <div id="box_histories">
          <div class="box_decision_title centered" style="width: 100%;">History</div>
          
    <div id="current_region"></div>
    <div id="regional_pandemic"></div>
    <div id="ecdc"></div>

      </div></div><div id="box_canvas"><canvas id="canvas" width="800" height="400"></canvas></div>
</div>

    <script id="sidebar_template" type="x-tmpl-mustache">
    <div class="any_bold any_cursor_pointer sidebar_entry" onclick="toggleSeasonList('{{rid}}')"><i id="checkbox_region_{{rid}}" class="fa fa-minus-square-o"></i>{{title}}</div>
<div id="container_{{rid}}_all" class="sidebar_region">
<div class="any_cursor_pointer {{^seasons.5}}any_hidden{{/seasons.5}}" onclick="toggleAllSeasons('{{rid}}')"><i id="checkbox_{{rid}}_all" class="fa fa-square-o"></i><span class="effect_tiny effect_italics">Show all</span></div>
    {{#seasons}}
    <div id="container_{{rid}}_{{year}}" {{^current}}class="any_cursor_pointer" onclick="toggleSeason('{{rid}}', {{year}})"{{/current}} onmouseenter="hoverCurveOn('{{rid}}', {{year}})" onmouseleave="hoverCurveOff('{{rid}}', {{year}})"><i id="checkbox_{{rid}}_{{year}}" class="fa {{#current}}fa-check-square{{/current}}{{^current}}fa-square-o{{/current}}" style="color: {{color}}"></i><span class="effect_tiny">{{label}} {{#current}} current year{{/current}}</span></div>
{{/seasons}}
</div>
    </script>
    <script src="js/forecast.js?w=202014.f"></script>
  <script src="js/delphi_epidata.js"></script>
  <script src="https://unpkg.com/mustache@4.0.1"></script>
    <script>
    // was: forecast.js
var DRAW_POINTS = true;
var TICK_SIZE = 5;
var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
var LABEL_X = '';
var LABEL_Y = 'ILI Activity (% outpatient visits)';
var MARGIN_LEFT = 60;
var MARGIN_BOTTOM = 60;
var MARGIN_RIGHT = 12;
var MARGIN_TOP = 12;
var DASH_STYLE = [1, 5];
var AUTOSAVE_INTERVAL = 1;
var AXIS_STYLE = {color: '#000', size: 1, dash: []};
var GRID_STYLE = {color: '#bbb', size: 1, dash: DASH_STYLE};
var ZOOM_AMOUNT = 1.1;
var BUTTON_SIZE = 25;
var WILI_MAX = 26;
var WILI_MIN = 3;
var NSEASONS_SHOW_ALL = 5;

//Number of axis tick marks
var xInterval = 2;
var yInterval = 1;
var uiScale = 1;
var canvas = $('#canvas')[0];
var dragging = false;
var hoveringButton = null;

var modifyCounter = 0;
var submitCounter = 0;
var modified = false;
var zoomDownBounds;
var zoomUpBounds;
var showLastBounds;
var snapLastBounds;
var SubmitStatus = {
   init: 0,
   sent: 1,
   success: 2,
   failure: 3
};
var submitStatus = SubmitStatus.init;

//chart bounds
function marginLeft() { return MARGIN_LEFT * uiScale; }
function marginRight() { return MARGIN_RIGHT * uiScale; }
function marginTop() { return MARGIN_TOP * uiScale; }
function marginBottom() { return MARGIN_BOTTOM * uiScale; }
function max(x1,x2) { if (x1>x2) { return x1; } return x2; }
function min(x1,x2) { if (x1<x2) { return x1; } return x2; }

</script>
<script>
//globals
var regionName = '<?= $region['name'] ?>';
var regionID = '<?= $region['fluview_name'] ?>'; // needed for epidata
var regionNo = <?= $regionID ?>; // needed for saving forecasts

var selectedSeasons = [];

var timeoutID;
var lastDrag = null;
var tooltip = null;
// nowcast
var showNowcast = false;

var minWeek = <?= $minEpiweek ?>;
var maxWeek = <?= $maxEpiweek ?>;

var currentWeek = <?= $currentWeek ?>;
var currentSeason = <?= $current_season ?>;
var seasonDefn = [ <?= $minEpiweek ?> % 100, <?= $maxEpiweek ?> % 100];

var curves = {
    lastForecast: [<?php
    $maxILI = 0; $maxILIfactor = 1.3;
    $n = count($lastForecast['date']);
    for ($i=0; $i<$n; $i++) {
        printf('{epiweek:%d, wili:%.3f},',
            $lastForecast['date'][$i],
            $lastForecast['wili'][$i]);
        $maxILI = max($maxILI, $maxILIfactor*$lastForecast['wili'][$i]);
        }

    ?>],
    forecast: [<?php
    $n = count($region['forecast']['date']);
    for ($i=0; $i<$n; $i++) {
        if ($region['forecast']['date'][$i] < $currentWeek+1) { continue; }
        printf('{epiweek:%d, wili:%.3f},',
            $region['forecast']['date'][$i],
            $region['forecast']['wili'][$i]);
        $maxILI = max($maxILI, $maxILIfactor*$region['forecast']['wili'][$i]);

    }
    // NB this will need to be fixed before we start showing 2021 in the display
    $start = ($n > 0) ? $region['forecast']['date'][$n-1] + 1 : $currentWeek + 1;
    for ($w=$start; $w<$maxEpiweek; $w++) {
        printf('{epiweek:%d, wili:0},',$w);
    }
    ?>],
};
var showLastForecast = curves.lastForecast.length>0;

var xRange = [currentSeason*100+seasonDefn[0], (currentSeason+1)*100+seasonDefn[1]];
var yRange = [0, <?= $maxILI ?>];

function loader(sidebarTitle,rid,parent,whitelist) {
    return function(result, message, epidata) {
	var module = {}
	module.title = sidebarTitle;
	module.rid = rid;
	module.data = epidata;
	module.seasons = [];  var mi=0;
	module.season = {};
	var season = -1;

	// mark start and end of each season found in the data
	for (var i=0; i<epidata.length; i++) {
        yRange[1] = max(yRange[1], 1.5*epidata[i].wili); // Fit plot to curves
        var modweek = epidata[i].epiweek % 100;

        // end of last season overlaps with this one
        if (modweek > seasonDefn[1] && module.season[season-1] && !module.season[season-1].end) {
            module.season[season-1].end = i-1;
        }

        // check for new season
	    var si = Math.floor(epidata[i].epiweek / 100);        
	    if (modweek < seasonDefn[0]) si = si - 1;
	    // whitelist: don't create an extra new season for 2009-2010 week 201036-40
	    if (season<0 || (season != si && (!whitelist || si in whitelist))) {

		season = si;
		module.season[season] = {start:i,year:season,label:season==2009?(season+" pandemic"):season,color:getStyle(rid,season).color,current:season==currentSeason};
        // the templating engine needs a list, so we make a list
		module.seasons[mi++] = module.season[season]; 
	    }
	}
    module.season[season].end = epidata.length-1;
    
	curves[rid] = module;
	$(parent).append($(Mustache.render(document.getElementById('sidebar_template').innerHTML, module)));
	toggleAllSeasons(rid);
    };
}
 //main
$(document).ready(function() {
    var canvas = $('#canvas');
    canvas.on('mousedown touchstart', function(e) { e.preventDefault(); mouseDown(mousePosition(e)); });
    canvas.on('mouseup mouseout touchend touchleave touchcancel', function(e) { e.preventDefault(); mouseUp(mousePosition(e)); });
    canvas.on('mousemove touchmove', function(e) { e.preventDefault(); mouseMove(mousePosition(e)); });
    $(window).resize(function() {
        resize();
    });
    resize();

    Epidata.fluview(loader(regionName,regionID,"#current_region"), [regionID], [Epidata.range(minWeek, currentWeek)]);
    <?php
    if ($region['id'] >= 11) {
        $regionForState = 1;
        foreach ($output['regions'] as $r) {
            printf("<!-- region %d states: %s -->\n",$r['id'],$r['states']);
            $pos = strpos($r['states'],strtoupper($region['fluview_name']));
            printf("<!-- %s pos: %s -->\n",strtoupper($region['fluview_name']),$pos);
            if ($pos or $pos === 0) {
                $regionForState = $r['id'];
                break;
            }
        }
        ?>
        Epidata.fluview(loader('<?= $output['regions'][$regionForState]['name'] ?>','<?= $output['regions'][$regionForState]['fluview_name'] ?>',"#regional_pandemic",[2009]), ['<?= $output['regions'][$regionForState]['fluview_name'] ?>'], [Epidata.range(200936,201000+(maxWeek%100))]);
        <?php
    } // end if $region['id'] >= 11
    ?>
});
</script>
<?php
}
require_once('common/footer.php');
?>
