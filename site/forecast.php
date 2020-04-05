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

function getColor($regionID, $seasonID) {
   $r = intval((sin(($seasonID - 2004) * 0.4 + 0) + 1) / 2 * 15);
   $g = intval((sin(($seasonID - 2004) * 0.5 + 2) + 1) / 2 * 15);
   $b = intval((sin(($seasonID - 2004) * 0.6 + 4) + 1) / 2 * 15);
   return sprintf('#%x%x%x', $r, $g, $b);
}

//Epiweek info
if(getEpiweekInfo($dbh, $output) !== 1) {
   fail('Error loading epiweek info');
}

//List of all regions
if(getRegionsExtended($dbh, $output, $output['user_id']) !== 1) {
   fail('Error loading region details, history, or forecast');
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

//Forecast from last round
if(loadForecast($dbh, $output, $output['user_id'], $regionID, true) !== 1) {
   fail('Error loading last week forecast');
}

$lastForecast = $output['forecast'];
$region = &$output['regions'][$regionID]; // reference because having two copies is not great
$num = count($output['regions']);
//History for this region
$output['history'] = &$output['regions'][$regionID]['history'];

// We only want history back to 2009
$minEpiweek = 200936;

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
$numPastWeeks = getDeltaWeeks($seasonStart, $currentWeek);
$numFutureWeeks = getDeltaWeeks($currentWeek, $seasonEnd);
$maxRegionalWILI = 0;
$minRegionalWILI = 100;
for($i = 0; $i < count($region['history']['wili']); $i++) {
   $epiweek = $region['history']['date'][$i];
   if ($epiweek < $minEpiweek) { continue; }
   if($showPandemic || $epiweek < 200940 || $epiweek > 201020) {
      $maxRegionalWILI = max($maxRegionalWILI, $region['history']['wili'][$i]);
      $minRegionalWILI = min($minRegionalWILI, $region['history']['wili'][$i]);
   }
}
max($region['history']['wili']); // what is this for? -kmm
$target = $seasonStart;
$seasonOffsets = array();
$seasonYears = array();
for($i = count($region['history']['date']) - 1; $i >= 0; $i--) {
   if($region['history']['date'][$i] <= $target) {
      array_push($seasonOffsets, $i);
      array_push($seasonYears, intval($target / 100));
      $target -= 100;
   }
}
if($seasonOffsets[count($seasonOffsets) - 1] != 0) {
   array_push($seasonOffsets, 0);
   array_push($seasonYears, intval($target / 100));
}
$seasonOffsets = array_reverse($seasonOffsets);
$seasonYears = array_reverse($seasonYears);
// okay for international and COVID-19 data, we're going to treat them
// as magic additional seasons with years that start with some absurd
// number (maybe we have a limited number of other sources and each
// source can get its own absurd prefix e.g. 9xxx=COVID-19, 8xxx=ECDC,
// etc). These will be added after the year-based calculations from
// the block above.
//
// We also want to make sure the not-actually-WILI data shakes out in
// the correct order for when we fill the `pastWili` javascript array
// with it in a minute.
//
// Maybe we want to make the set of additional data selectable in the
// future, but for now it has to be hard-coded.

// A rough sketch:
// sources = {
//    "COVID-19":{"Italy":9001, "Spain":9002, "France":9003, "USA":9010},
//    "ECDC":{"Italy":80012019, "Spain":80022019, "France":80032019}, #ECDC publishes the last two seasons, for now we just want 2019-2020 but maybe we'll want both later? Will SK and UK have multiple seasons of ILI data too?
//    "SKorea":{"South Korea":70002019},
//    "UK":{"UK":60002019}
// }


 
$sources = array(
    "ECDC" => array(
	"fn" => "getECDCILI",
	"key" => "ecdc",
	"members" => array( 
	    //"Italy" => 8001,
	    //"Spain" => 8002,
	    //"France" => 8003,
        //"Netherlands" => 8004,
        //"Ireland" => 8005,
        //"UK - Scotland" => 8006,
        //"Belgium" => 8007,
    )));
$sourceIDs = array();
foreach($sources["ECDC"]["members"] as $country => $cid) {
    $sourceIDs[$cid] = $country.", ECDC";
} 


// $lastOffset = $seasonOffsets[end]
// for $src,$map in $sources {
//   for $name,$rid in $map {
//      push $output['regions'][$rid]['history'] onto the end of $region['history']
//      push $lastOffset + count($output['regions'][$rid]['history']['date'] onto the end of $seasonOffsets
//      push $rid onto the end of $seasonYears
//   }
// }


$lastOffset_i = count($seasonOffsets);
$currentYear = $seasonYears[$lastOffset_i-1];
$lastHistory_i = count($region['history']['date']);
$nextOffset = $lastHistory_i;
foreach ($sources as $src => $meta) {
    $fn = $meta["fn"];
    foreach ($meta["members"] as $name => $rid) {
	    $fn($dbh, $output, $rid, $seasonStart+5); // hard-coded for now; ECDC counts seasons from epiweek 40
        
        $n = count($output[$meta["key"]][$rid]["date"]);
?>
<!-- <?= $name ?>: <?= $n ?> results -->
<?php

        // none of the international data have the same units as us.
        // to cope, first we'll scale it from 0 to 1, then shift and scale it
        // up to look more like the other curves in the plot.
        $unit_offset = -1;
        $unit_scale = 1;
	    for($i=0;$i<$n;$i++) {
            $wili = $output[$meta["key"]][$rid]["wili"][$i];
            if ($unit_offset < 0 or $wili < $unit_offset) {
                $unit_offset = $wili;
            }
            if ($unit_scale < $wili) {
                $unit_scale = $wili;
            }
        }
        $unit_scale = $unit_scale - $unit_offset;
	    $seasonYears[$lastOffset_i] = $rid;
        $seasonOffsets[$lastOffset_i] = $nextOffset;
	    $lastOffset_i++;
	    for($i=0;$i<$n;$i++) {
            $wili = $output[$meta["key"]][$rid]["wili"][$i];
            $wili = ($wili - $unit_offset) / $unit_scale * $maxRegionalWILI/2 + $minRegionalWILI;
	        $region['history']['date'][$lastHistory_i] = $output[$meta["key"]][$rid]["date"][$i];
	        $region['history']['wili'][$lastHistory_i] = $wili;
	        $lastHistory_i++;
	    }
	    $nextOffset = $nextOffset + $n;
    }
}

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
            (If you don't see the animation, click <a target="_blank" href="images/tutorial.gif">here</a>.)<!-- ' -->
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
foreach($output['regions'] as $r) {
   createForm('forecast_' . $r['id'], 'forecast.php#top', array('region_id', $r['id']));
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

<!--       <div class="box_status_line"><div style="margin:20px 180px 10px 180px; padding:5px 40px; background:white; font-weight:normal; font-size:12px; text-align:left"><p><b>Additional data for the 2019-2020 COVID-19 pandemic</b></p><p>The European Centre for Disease Control (ECDC) publishes ILI data for its member nations. COVID-19 reached Italy and several other EU nations ahead of the USA, and the ECDC ILI data for those countries may be useful to your forecasts. This is a rapidly changing situation and not all ECDC reporting countries seem to agree on whether COVID-19 encounters count as ILI activity. We have excluded counties whose ECDC reporting is suspiciously similar to their 2018-2019 season, which unfortunately includes Italy and Spain. Germany is also ahead of us, but does not report ILI data. The ECDC ILI units are not a percent of visits, so <b>while the shape of the curves is accurate, the y-values have been scaled to fit in the plot</b>. For more information on the ECDC data, see the methods section of <a href="https://www.ecdc.europa.eu/sites/default/files/documents/AER_for_2015-influenza-seasonal_0.pdf">the 2015 surveillance report on seasonal Influenza</a>.</p></div></div> -->

   </div>
   <div id="box_side_bar">
      <div id="box_histories">
         <div class="box_decision_title centered" style="width: 100%;">History</div>
         <?php foreach($output['regions'] as $r) {
            if($r['id'] !== $regionID) continue;
            ?>

            <div class="any_bold any_cursor_pointer" onclick="toggleSeasonList(<?= $r['id'] ?>)"><i id="checkbox_region_<?= $r['id'] ?>" class="fa fa-plus-square-o"></i>&nbsp;<?= htmlspecialchars($r['name']) ?></div>
            <div>Seasons: </div>
            <div id="container_<?= $r['id'] ?>_all" class="any_hidden any_cursor_pointer" onclick="toggleAllSeasons(<?= $r['id'] ?>)">&nbsp;&nbsp;&nbsp;&nbsp;<i id="checkbox_<?= $r['id'] ?>_all" class="fa fa-square-o"></i>&nbsp;<span class="effect_tiny effect_italics">Show all</span></div>
            <?php
            $numHHS = 11;
        foreach($seasonYears as $year) {
	        if(($year*100+36) < $minEpiweek) { continue; }
            if($year == 2009) {
                if ($showPandemic !== 1 or ($regionID > $numHHS)) {
                    continue;
                }
            }
            
            if($r['id'] == $regionID && $year == $currentYear) { // does this ever actually happen? -kmm
                ?>
                    <div id="container_<?= $r['id'] ?>_<?= $year ?>" class="any_hidden any_cursor_pointer">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-check-square"></i>
                        <span class="effect_tiny"><?= sprintf('current year') ?></span>
                    </div>
                <?php
            } else {
                ?>
                    <div id="container_<?= $r['id'] ?>_<?= $year ?>" class="any_hidden any_cursor_pointer"
                        onclick="toggleSeason(<?= $r['id'] ?>, <?= $year ?>)">&nbsp;&nbsp;&nbsp;&nbsp;<i
                            id="checkbox_<?= $r['id'] ?>_<?= $year ?>" class="fa fa-square-o"
                            style="color: <?= getColor($r['id'], $year) ?>"></i>
                <?php
                if ($year == $currentYear) {
                    ?>
                        <span class="effect_tiny"><?= sprintf('current year') ?><?= ($year == 2009 ? ' pandemic' : '') ?></span>
                    </div>
                    <?php
                } elseif ($year > 3000) {
                    // this indicates an international dataset. See preamble for setup.
                    ?>
                        <span class="effect_tiny"><?= sprintf('%s',$sourceIDs[$year]) ?></span>
                    </div>
                    <?php
                } else {
                    ?>
                    <span class="effect_tiny"><?= sprintf('%d-%s', $year, substr((string)($year + 1), 2, 2)) ?><?= ($year == 2009 ? ' pandemic' : '') ?></span>
                </div>
                    <?php
                }
            }
        } // end $seasonYears as $year
    }
    ?>
      </div></div><div id="box_canvas"><canvas id="canvas" width="800" height="400"></canvas></div>
</div>
<script>
<!-- was: forecast.js -->
var DRAW_POINTS = true;
var TICK_SIZE = 5;
var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
var LABEL_X = '';
var LABEL_Y = 'ILI Activity (% outpatient visits)';
var LABEL_Y_HOSP = 'Hospitalization Rate';
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
   //var DEBUG = <?= $output['user_id'] == 9 ? 'true' : 'false' ?>;




   //Axis range
   var currentWeek = <?= $currentWeek ?>;
   var numPastWeeks = <?= $numPastWeeks ?>;
   var numFutureWeeks = <?= $numFutureWeeks ?>;
   var totalWeeks = (numPastWeeks + 1 + numFutureWeeks);
   var xRange = [addEpiweeks(currentWeek, -numPastWeeks), addEpiweeks(currentWeek, +numFutureWeeks)];
   var yRange = [0, <?= ($maxRegionalWILI * 1.8) ?>]; // 1.8, really? -kmm
   var regionID = <?= $regionID ?>;
   var seasonOffsets = [<?php foreach($seasonOffsets as $o){printf('%d,',$o);} ?>];
   var seasonYears = [<?php foreach($seasonYears as $y){printf('%d,',$y);} ?>];
   var seasonIndices = {<?php for($i = 0; $i < count($seasonYears); $i++){printf('\'%d\':%d,',$seasonYears[$i],$i);} ?>};
   var regionNames = [];
   var pastWili = [];
   var pastEpiweek = [];
   var forecast = [];
   var curveStyles = {};
   <?php
   foreach($output['regions'] as $r) { // why are we doing all the regions instead of just the one being forecast? -kmm
      if($r['id'] !== $regionID) continue; // what happens if we don't?
      ?>
      regionNames[<?= $r['id'] ?>] = '<?= $r['name'] ?>';
      pastWili[<?= $r['id'] ?>] = [<?php foreach($r['history']['wili'] as $v){printf('%.2f,',$v);} ?>];
      pastEpiweek[<?= $r['id'] ?>] = [<?php foreach($r['history']['date'] as $v){printf('%s,',$v);}?>];
      forecast[<?= $r['id'] ?>] = [<?php
         $offset = count($r['forecast']['date']);
         foreach($r['forecast']['date'] as $d) {
            if($d > $currentWeek) {
               $offset -= 1;
            }
         }
         $start = 0;
         $middle = min(count($r['forecast']['wili']) - $offset, $numFutureWeeks);
         $end = $numFutureWeeks;
         for($i = $start; $i < $middle; $i++){printf('%.3f,',$r['forecast']['wili'][$offset + $i]);}
         for($i = $middle; $i < $end; $i++){printf('0,');}
      ?>];
      curveStyles[<?= $r['id'] ?>] = {};
      <?php
      foreach($seasonYears as $year) {
          ?>
            curveStyles[<?= $r['id'] ?>][<?= $year ?>] = {color: '<?= $year == $currentYear ? "#000" : getColor($r['id'], $year) ?>', size: <?= $year == $currentYear ? 2 : $year > 3000 ? 1.5 : 1 ?>, dash: [], alpha: <?= $year == $currentYear ? 1 : 0.4 ?>};
            <?php
        }
    } // end $output['regions'] as $r
   ?>
   var selectedSeasons = [];
    var showLastForecast = <?= $lastForecast['wili'] ? "true" : "false" ?>;
    var lastForecast = [<?php foreach($lastForecast['wili'] as $v){printf('%.3f,', $v);} ?>];
    var lastForecastEpiweek = <?= $lastForecast['date'] ? $lastForecast['date'][0] : $currentWeek ?>;
   var timeoutID;
   var lastDrag = null;
   var tooltip = null;
   // nowcast
   var showNowcast = <?= (getPreference($output, '_delphi', 'int') == 1 && isset($output['nowcast'])) ? 'true' : 'false' ?>;
   <?php
   if(isset($output['nowcast'])) {
      printf('var nowcast = [%.5f, %.5f];', $output['nowcast']['value'], $output['nowcast']['std']);
   }
   ?>
   //x-axis
   function getChartWidth() {
      return canvas.width - marginLeft() - marginRight();
   }
   function getX(epiweek) {
      var ew = epiweekToDecimal(epiweek);
      var left = epiweekToDecimal(xRange[0]);
      var right = epiweekToDecimal(xRange[1]);
      return marginLeft() + (getChartWidth() / (right - left)) * (ew - left);
   }
   function getEpiweek(x) {
      var left = epiweekToDecimal(xRange[0]);
      var right = epiweekToDecimal(xRange[1]);
      var ew = (x - marginLeft()) * ((right - left) / getChartWidth()) + left;
      return decimalToEpiweek(ew);
   }
   //y-axis
   function getChartHeight() {
      return canvas.height - marginTop() - marginBottom();
   }
   function getY(i) {
      return (canvas.height - marginBottom()) - (getChartHeight() / (yRange[1] - yRange[0])) * (i - yRange[0]);
   }
   function getIncidence(y) {
      return (-y + (canvas.height - marginBottom())) * ((yRange[1] - yRange[0]) / getChartHeight()) + yRange[0];
   }
   //utilities
   function getGraphics() {
      var g = $('#canvas')[0].getContext('2d');
      //some browsers don't support dashed lines - see http://www.rgraph.net/blog/2013/january/html5-canvas-dashed-lines.html#examples
      if(!g.setLineDash){g.setLineDash = function(x){}}
      return g;
   }
   var Align = {
      left: 0,
      right: 1,
      bottom: 2,
      top: 3,
      center: 4
   };
   function drawText(g, str, x, y, angle, alignX, alignY, scale, font) {
      scale = typeof scale !== 'undefined' ? scale : 1;
      font = typeof font !== 'undefined' ? font : ['', 'Calibri'];
      var size = Math.round(12 * scale * uiScale);
      g.font = font[0] + ' ' + size + 'px ' + font[1];
      var w = g.measureText(str).width;
      var h = size;
      var dx = 0;
      var dy = 0;
      if(alignX == Align.left) {
         dx = 0;
      } else if(alignX == Align.right) {
         dx = -w;
      } else if(alignX == Align.center) {
         dx = -w / 2;
      } else {
         g.strokeStyle = '#ff0000';
      }
      if(alignY == Align.bottom) {
         dy = 0;
      } else if(alignY == Align.top) {
         dy = h;
      } else if(alignY == Align.center) {
         dy = h / 2;
      } else {
         g.strokeStyle = '#ff0000';
      }
      g.save();
      g.translate(x, y);
      g.rotate(angle);
      g.fillText(str, dx, dy);
      g.restore();
      return {x: x + dx, y: y + dy - h, w: w, h: h};
   }
   function drawLine(x1, y1, x2, y2, style) {
      var g = getGraphics();
      g.strokeStyle = style.color;
      g.lineWidth = style.size * uiScale;
      g.setLineDash(style.dash);
      g.beginPath();
      g.moveTo(x1, y1);
      g.lineTo(x2, y2);
      g.stroke();
      g.setLineDash([]);
   }
    function drawPoints(xs, ys, start, end, style, g) {
	if (typeof g == 'undefined') {
	    var g = getGraphics();
	    g.strokeStyle = style.color;
	    g.lineWidth = style.size * uiScale;
	    g.setLineDash([]);
	}
	g.lineWidth = 3 * style.size * uiScale;
         for(var i = start; i < end; i++) {
            if(ys[i] >= 0) {
               g.beginPath();
		var x = getX(modulusEpiweek(xs[i]));
               var y = getY(ys[i]);
               g.moveTo(x, y);
               g.lineTo(x + 1, y);
               g.stroke();
            }
         }
    }
    
    function drawCurve(curve, start, end, epiweekStart, style, do_drawPoints) {
	if (typeof do_drawPoints == "undefined") {
	    do_drawPoints = DRAW_POINTS;
	}
      var g = getGraphics();
      g.strokeStyle = style.color;
      g.lineWidth = style.size * uiScale;
      g.setLineDash(style.dash);
      g.beginPath();
       var first = true;
       var epiweek = epiweekStart; //addEpiweeks(xRange[0], epiweekOffset);
       var xs = []; 
      for(var i = start; i < end; i++) {
         if(curve[i] >= 0) {
            var x = getX(epiweek);
            var y = getY(curve[i]);
            if(first) {
               first = false;
               g.moveTo(x, y);
            } else {
               g.lineTo(x, y);
            }
         }
	  xs[i] = epiweek;
         epiweek = addEpiweeks(epiweek, 1);
      }
      g.stroke();
      g.setLineDash([]);
       if(do_drawPoints)
	   drawPoints(xs, curve, start, end, style, g);
   }
    function drawCurveXY(xs, ys, start, end, style, do_drawPoints) {
	if (typeof do_drawPoints == "undefined") {
	    do_drawPoints = DRAW_POINTS;
	}
      var g = getGraphics();
      g.strokeStyle = style.color;
      g.lineWidth = style.size * uiScale;
      g.setLineDash(style.dash);
      g.beginPath();
      var first = true;
      for(var i = start; i < end; i++) {
         if(ys[i] >= 0) {
             var x = getX(modulusEpiweek(xs[i]));
            var y = getY(ys[i]);
            if(first) {
               first = false;
               g.moveTo(x, y);
            } else {
               g.lineTo(x, y);
            }
         }
      }
      g.stroke();
      g.setLineDash([]);
	if(do_drawPoints) drawPoints(xs, ys, start, end, style, g);
   }
    function stitchCurves(regionID, style, y2, xoffset) {
	if(forecast[regionID][0] < 0) {
            return;
	}
	if (typeof y2 == "undefined") {
	    y2 = getY(forecast[regionID][0]);
	}
	if (typeof xoffset == "undefined") {
	    xoffset = 1;
	}

	var seasonIndex = seasonIndices[<?= $currentYear ?>];
	var end = ((seasonIndex+1)<seasonOffsets.length) ? seasonOffsets[seasonIndex+1] : (pastWili[regionID].length-1);
	var seasonLength = end - seasonOffsets[seasonIndex];
	var x1 = (addEpiweeks(xRange[0], seasonLength));
	var y1 = getY(pastWili[regionID][end]);
	var x2 = (addEpiweeks(currentWeek, xoffset));
	//console.log("stitch curve:",seasonIndex,end,seasonLength,x1,y1,x2,y2);
	drawLine(getX(x1), y1, getX(x2), y2, style);
   }
   function drawTooltip(g, str) {
      str = ' ' + str;
      var cx = getChartWidth() / 2;
      var cy = getChartHeight() / 2;
      var bt = drawText(g, str, cx, cy, 0, Align.center, Align.center, 1.5);
      var bi = drawText(g, "\uf05a", bt.x, cy, 0, Align.right, Align.center, 1.5, ['', 'FontAwesome']);
      var padding = 6;
      var border = 3;
      g.fillStyle = '#000';
      g.fillRect(bi.x - padding - border, bt.y - padding - border, bi.w + bt.w + 2 * (padding + border), bt.h + 2 * (padding + border));
      g.fillStyle = '#fff';
      g.fillRect(bi.x - padding, bt.y - padding, bi.w + bt.w + 2 * padding, bt.h + 2 * padding);
      g.fillStyle = '#000';
      drawText(g, str, cx, cy, 0, Align.center, Align.center, 1.5);
      drawText(g, "\uf05a", bt.x, cy, 0, Align.right, Align.center, 1.5, ['', 'FontAwesome']);
   }
   function repaint() {
      var g = getGraphics();
      //clear the canvas
      g.clearRect(0, 0, canvas.width, canvas.height);
      g.fillStyle = '#fff';
      g.fillRect(0, 0, canvas.width, canvas.height);
      //past/future
      var weekX = getX(currentWeek + 0.5);
      var x1 = getX(xRange[0]);
      var x2 = getX(xRange[1]);
      var y1 = getY(yRange[0]);
      var y2 = getY(yRange[1]);
      var scale_y0 = 0;
      var scale_y1 = 0;
      //past
      g.fillStyle = '#eee';
      g.fillRect(x1, y2, weekX - x1, y1 - y2);
      g.fillStyle = '#888';
      drawText(g, '< past', weekX - 15, y2, 0, Align.right, Align.top);
      //future
      g.fillStyle = '#fff';
      g.fillRect(weekX, y2, x2 - weekX, y1 - y2);
      g.fillStyle = '#888';
      drawText(g, 'future >', weekX + 15, y2, 0, Align.left, Align.top);
      //axis styles
      g.lineCap = 'round';
      g.fillStyle = '#000';
      g.lineWidth = 1 * uiScale;
      //y-axis
      {
         var row1 = 12.5 * uiScale;
         var row2 = marginLeft() - 12.5 * uiScale;
         scale_y0 = getY(yRange[0]);
         scale_y1 = getY(yRange[0]+yInterval);
         var scale = scale_y0 - scale_y1;
         //ticks and lines
         for(var incidence = yRange[0]; incidence <= yRange[1]; incidence += yInterval) {
            var y = getY(incidence);
            drawText(g, '' + incidence, row2, y, 0, Align.right, Align.center);
            drawLine(marginLeft() - TICK_SIZE, y, marginLeft() - 1, y, AXIS_STYLE);
            drawLine(getX(xRange[0]), y, getX(xRange[1]), y, GRID_STYLE);
         }
         //label
         drawText(g, "ILI Activity", row1 - 8 * uiScale, canvas.height / 2, -Math.PI / 2, Align.center, Align.center, 1.5, ['bold', 'Calibri']);
          drawText(g, "(% outpatient visits)", row1 + 7 * uiScale, canvas.height / 2, -Math.PI / 2, Align.center, Align.center, 1.5, ['', 'Calibri']);
	  

         //zoom controls
         var x = 16 * uiScale;
         var dy = BUTTON_SIZE * uiScale;
         zoomUpBounds = drawText(g, "\uf151", x, y2, 0, Align.center, Align.top, 2, ['', 'FontAwesome']);
         zoomDownBounds = drawText(g, "\uf150", x, y2 + dy, 0, Align.center, Align.top, 2, ['', 'FontAwesome']);
      }
      //x-axis
      {
         var row1 = 0.75 * (marginBottom() / 3);
         var row2 = 1.75 * (marginBottom() / 3);
         var row3 = 2.5 * (marginBottom() / 3);
         var axisY = canvas.height - marginBottom();
         //flu season
         //ticks
         var skip = 0;
         for(var epiweek = xRange[0]; epiweek <= xRange[1]; epiweek = addEpiweeks(epiweek, 1)) {
            var x = getX(epiweek);
            if(skip == 0) {
               drawText(g, 'w' + (epiweek % 100), x, canvas.height - row3, 0, Align.center, Align.center);
            }
            skip = (skip + 1) % xInterval;
            drawLine(x, axisY + TICK_SIZE, x, axisY + 1, AXIS_STYLE);
         }
         //months
	 var on = true;
         var month = Math.floor((xRange[0] % 100 - 1) / getNumWeeks(Math.floor(xRange[0] / 100)) * MONTHS.length);
         for(var epiweek = xRange[0]; epiweek <= xRange[1]; epiweek = addEpiweeks(epiweek, 4.35)) {
            var label = MONTHS[month];
            if(month == 0) {
               label += '\'' + (Math.floor(epiweek / 100) % 100);
            }

	    // shade alternate months to show disjoint with weeks
            oldFillStyle=g.fillStyle;
            g.fillStyle = on ? '#eee' : '#fff'; on = !on;
            x1 = max(xRange[0]-1, addEpiweeks(epiweek,-4.35/2));
            y1 = canvas.height - row3 + row2/4;
            x2 = min(addEpiweeks(x1, 4.35), xRange[1])
            g.fillRect(getX(x1), y1, getX(x2)-getX(x1), row2/2);
            g.fillStyle = oldFillStyle;

            drawText(g, label, getX(epiweek), canvas.height - row2, 0, Align.center, Align.center);
            month = (month + 1) % MONTHS.length;
         }
         //label
         drawText(g, LABEL_X, canvas.width / 2, canvas.height - row1, 0, Align.center, Align.center, 1.5, ['bold', 'Calibri']);
      }
       // COVID-19 benchmarks
       covid_us_1   = getX(202005); // first cases
       covid_us_100 = getX(202010); // 100 cases
       drawLine(covid_us_100, getY(yRange[0]), covid_us_100, getY(yRange[1]), {color:"#F00", size:2, dash:[], alpha:1});
       drawLine(covid_us_1, getY(yRange[0]), covid_us_1, getY(yRange[1]), {color:"#F00", size:1, dash:[], alpha:1});
       oldFillStyle=g.fillStyle;
       g.fillStyle="#600";
       drawText(g, "<- 100 cases in USA", covid_us_100 + 10, marginTop() + 36*uiScale, 0, Align.left, Align.top);
       drawText(g, "First COVID-19 case in USA ->", covid_us_1 - 10, marginTop() + 36*uiScale, 0, Align.right, Align.top);
       g.fillStyle=oldFillStyle;
       
      //other regions or past seasons
       function repaintSelection(r, s, withPoints) {
	   if (typeof s == "undefined") {
	       i = r;
               var r = selectedSeasons[i][0];
               var s = selectedSeasons[i][1];   
	   } 
         var style = curveStyles[r][s];
         var start = seasonOffsets[seasonIndices[s]];
         // var length = totalWeeks;
         <?php if(!$showPandemic) { ?>if(s == 2008) { length -= 12; }<?php } ?>
         
           var end = seasonIndices[s]+1 < seasonOffsets.length ? seasonOffsets[seasonIndices[s]+1] : pastWili[r].length;
           drawCurveXY(pastEpiweek[r], pastWili[r], start, end, style, withPoints);
      }
      for(var i = 0; i < selectedSeasons.length; i++) {
         var isCurrentSeason = (selectedSeasons[i][1] == <?= $currentYear ?>);
         if(selectedSeasons[i][0] == regionID && isCurrentSeason) {
             //Skip the current region's latest season
            continue;
         }
	  repaintSelection(i, undefined, false);
      }

      //last forecast
      var lfStyle = {color: '#aaa', size: 2, dash: DASH_STYLE};
      if(showLastForecast) {
          drawCurve(lastForecast, 0, lastForecast.length, lastForecastEpiweek, lfStyle);
	  stitchCurves(regionID, lfStyle, getY(lastForecast[0]), 0);
      }

       //current region and latest season
       repaintSelection(regionID, <?= $currentYear ?>, true);
      var style = {color: '#000', size: 2, dash: DASH_STYLE};
      drawCurve(forecast[regionID], 0, 52, currentWeek+1, style);
      stitchCurves(regionID, style);
      
      //nowcast
      if(showNowcast) {
         g.fillStyle = 'rgba(0, 0, 0, 0.5)';
         var epiweek = addEpiweeks(xRange[0], numPastWeeks + 1);
         var x = getX(epiweek);
         var y1 = getY(nowcast[0] - 2 * nowcast[1]);
         var y2 = getY(nowcast[0] + 2 * nowcast[1]);
         g.fillRect(x - 2, y1, 5, y2 - y1);
         y1 = getY(nowcast[0] - 1 * nowcast[1]);
         y2 = getY(nowcast[0] + 1 * nowcast[1]);
         g.fillRect(x - 4, y1, 9, y2 - y1);
         y1 = getY(nowcast[0]);
         g.fillRect(x - 5, y1, 11, 1);
      }
      
      //error bars // what the actual what is this??? -kmm
      var errors = [[-0.24705835, 0.26585897, -0.15209838, 0.19588030, -0.12080783, 0.14845500, -0.10822840, 0.13591350, -0.10105576, 0.11903400],
                     [-0.37140890, 0.28183701, -0.22718089, 0.22283626, -0.17166020, 0.15932419, -0.15244192, 0.13857609, -0.13520489, 0.12653161],
                     [-0.53510369, 0.89618800, -0.29194798, 0.65376200, -0.13691200, 0.53989966, -0.12287200, 0.46070700, -0.07438098, 0.41997600],
                     [-0.37340794, 0.40633099, -0.28260333, 0.17494332, -0.22924145, 0.12111835, -0.18220829, 0.09744193, -0.15922900, 0.08408102],
                     [-0.20515699, 0.30015400, -0.11709100, 0.25312400, -0.08401870, 0.22570893, -0.06906100, 0.20316300, -0.06395200, 0.17931200],
                     [-0.25007300, 0.20134411, -0.13535207, 0.12399100, -0.13027507, 0.10968548, -0.12658071, 0.09060300, -0.12210600, 0.09081896],
                     [-0.57142423, 0.64259200, -0.26681298, 0.44821271, -0.17997876, 0.42294960, -0.18924163, 0.40526105, -0.18486160, 0.41010436],
                     [-0.31905190, 0.53929610, -0.28534067, 0.25807903, -0.18014395, 0.17609501, -0.09770261, 0.15003601, -0.06749161, 0.11253900],
                     [-0.34997449, 0.16271156, -0.30672299, 0.11085698, -0.28115293, 0.08104906, -0.24976742, 0.07652170, -0.27224423, 0.07954395],
                     [-1.35720500, 0.36575900, -0.83282601, 0.33934500, -0.57508135, 0.29297430, -0.25338298, 0.25961193, -0.22189758, 0.23839696],
                     [-0.27577982, 0.67580001, -0.13440096, 0.51631755, -0.08888274, 0.42762205, -0.08109139, 0.37271498, -0.05693280, 0.26734400]];

      if (regionID <= 11) {
         var epiweek = addEpiweeks(xRange[0], numPastWeeks);
     var error = errors[regionID-1];
     var end = seasonIndices[<?= $currentYear ?>]+1 < seasonOffsets.length ? seasonOffsets[seasonIndices[<?= $currentYear ?>]+1] : pastWili[regionID].length;
         for (var i=0; i<9; i = i + 2) {
             var currentSeasonIndex = end - i/2 - 1;
            var above = -error[i]*scale;
            var below = error[i+1]*scale;
            var x_weekNumber = addEpiweeks(epiweek, -(i/2)-1);
            var x = getX(x_weekNumber);
            var y = getY(pastWili[regionID][currentSeasonIndex]);
            g.fillStyle = 'rgba(0, 0, 0, 0.5)';
            var bar_width = 5;
            g.fillRect(x-(bar_width/2.), y-above, bar_width, above);
            g.fillRect(x-(bar_width/2.), y,       bar_width, below);
         }
      }
      
      //legend
      var x1 = canvas.width - marginRight();
      var x2 = canvas.width - marginRight() - (15 * uiScale);
      var dy = 12 * uiScale;
      var y = marginTop() + dy;
      var labelBounds = drawText(g, 'Your Forecast, Last Week', x2 - 3, y, 0, Align.right, Align.center);
      drawLine(x1, y - 3, x2, y + 3, lfStyle);
      g.fillStyle = '#000';
      showLastBounds = drawText(g, showLastForecast ? "\uf046" : "\uf096", labelBounds.x - 5 * uiScale, y, 0, Align.right, Align.center, 1.25, ['', 'FontAwesome']);
      snapLastBounds = drawText(g, "\uf08d", showLastBounds.x - 5 * uiScale, y, 0, Align.right, Align.center, 1.25, ['', 'FontAwesome']);
      y += dy;
      drawText(g, 'Your Forecast, This Week', x2 - 3, y, 0, Align.right, Align.center);
      drawLine(x1, y - 3, x2, y + 3, style); // NB style still contains DASH STYLE from drawing the forecast curve above
      y += dy;
      drawText(g, regionNames[regionID] + ', ' + Math.round(xRange[0] / 100) + '+', x2 - 3, y, 0, Align.right, Align.center);
      style.dash = [];
      drawLine(x1, y - 3, x2, y + 3, style);      
      
//       error bar legend
      if (regionID <= 11) {
         // error bar legend
         drawText(g, '90% Confidence Interval', x2 - 3, y+25, 0, Align.right, Align.center);
         g.fillStyle = 'rgba(0, 0, 0, 0.5)';
         g.fillRect(x2+5, y+10, 5, 35);
      }
      
      //tooltip
      if(tooltip != null) {
         drawTooltip(g, tooltip);
      }
   }
   //more utilities
   function getNumWeeks(year) {
      return (year == 1997 || year == 2003 || year == 2008 || year == 2014) ? 53 : 52;
   }
   function getDeltaWeeks(start, end) {
      var x = (end > start) ? 1 : -1;
      var num = 0;
      while(start != end && num < 1e3) {
         start = addEpiweeks(start, x);
         num += x;
      }
      return num;
   }
   function addEpiweeks(ew, i) {
      var year = Math.floor(ew / 100);
      var week = ew % 100;
      week += i;
      var limit = getNumWeeks(year);
      if(week >= limit + 1) {
         week -= limit;
         year += 1;
      } else if(week < 1) {
         week += getNumWeeks(year - 1);
         year -= 1;
      }
      return year * 100 + week;
   }
    function modulusEpiweek(ew) {
	var startingWeek = xRange[0] % 100;
	var weekOffset = (ew % 100) - startingWeek;
	if (weekOffset < 0) weekOffset = weekOffset + 100;
	return xRange[0] + weekOffset;
    }
   function epiweekToDecimal(ew) {
      var year = Math.floor(ew / 100);
      var week = ew % 100;
      return year + (week - 1) / getNumWeeks(year);
   }
   function decimalToEpiweek(yr) {
      yr += 0.5 / 52;
      var year = Math.floor(yr);
      var wk = yr - year;
      var week = Math.floor(wk * getNumWeeks(year)) + 1;
      return year * 100 + week;
   }
   function animate() {
      repaint();
      if(dragging) {
         requestAnimationFrame(animate);
      } else {
         repaint();
      }
   }
   function adjustForecast(x, y) {
      var epiweek = getEpiweek(x);
      if(epiweek > currentWeek && epiweek <= xRange[1]) {
         var wili = Math.min(yRange[1], Math.max(yRange[0], getIncidence(y)));
         forecast[regionID][getDeltaWeeks(currentWeek, epiweek) - 1] = wili;
         if(lastDrag != null && epiweek != lastDrag.epiweek) {
            var direction = (epiweek > lastDrag.epiweek) ? 1 : -1;
            for(var i = addEpiweeks(lastDrag.epiweek, direction); i != epiweek; i = addEpiweeks(i, direction)) {
               forecast[regionID][getDeltaWeeks(currentWeek, i) - 1] = wili;
            }
         }
         lastDrag = {epiweek: epiweek, wili: wili};
         modified = true;
      } else {
         lastDrag = null;
      }
   }
   function contains(bounds, point) {
      var x1 = bounds.x;
      var x2 = bounds.x + bounds.w;
      var y1 = bounds.y;
      var y2 = bounds.y + bounds.h;
      return (point.x >= x1 && point.x <= x2 && point.y >= y1 && point.y <= y2);
   }
   //user interaction
   function mouseDown(m) {
      tooltip = null;
      if(contains(zoomUpBounds, m)) {
         zoom(1 / ZOOM_AMOUNT);
      } else if(contains(zoomDownBounds, m)) {
         zoom(ZOOM_AMOUNT);
      //} else if(contains(undoBounds, m)) {
      //   undo();
      //} else if(contains(redoBounds, m)) {
      //   redo();
      } else if(contains(showLastBounds, m)) {
         showLastForecast = !showLastForecast;
         repaint();
      } else if(contains(snapLastBounds, m)) {
         if(confirm('Are you sure you want to reset your current forecast to your previous forecast?')) {
            snapToLastForecast();
         }
      } else {
         $('#canvas').addClass('canvas_drag');
         adjustForecast(m.x, m.y);
         dragging = true;
         animate();
      }
   }
   function mouseUp(m) {
      $('#canvas').removeClass('canvas_drag');
      if(dragging) {
         dragging = false;
         lastDrag = null;
         if(modified) {
            ++modifyCounter;
            setTimeout(submitForecastDelayed, AUTOSAVE_INTERVAL * 1000);
         }
         modified = false;
      }
   }
   function mouseMove(m) {
      //Drawing a forecast
      if(dragging) {
         adjustForecast(m.x, m.y);
         return;
      }
      //Interacting with a button
      var buttons = [
         {
            bounds: zoomUpBounds,
            tooltip: 'Decrease the scale of the Y axis. (Zoom in.)',
         },{
            bounds: zoomDownBounds,
            tooltip: 'Increase the scale of the Y axis. (Zoom out.)',
         },{
            bounds: showLastBounds,
            tooltip: 'Show or hide your forecast from last week.',
         },{
            bounds: snapLastBounds,
            tooltip: 'Pin your current forecast to your forecast from last week.',
         },
      ];
      //Find out which button (if any)
      var hb = null;
      tooltip = null;
      for(var i = 0; i < buttons.length; i++) {
         if(contains(buttons[i].bounds, m)) {
            hb = buttons[i].bounds;
            tooltip = buttons[i].tooltip;
            break;
         }
      }
      //Update if the hovered button has changed
      if(hoveringButton != hb) {
         if(hoveringButton != null && hb == null) {
            //back to the normal cursor
            $('#canvas').removeClass('canvas_button');
         } else if(hoveringButton == null && hb != null) {
            //use the button cursor
            $('#canvas').addClass('canvas_button');
         }
         hoveringButton = hb;
         repaint();
      }
   }
   function mousePosition(e) {
      if(e.type.toLowerCase().indexOf('touch') == 0) {
         e = e.originalEvent.changedTouches[0];
      }
      var canvas = $('#canvas');
      return {
         x: e.pageX - canvas.offset().left,
         y: e.pageY - canvas.offset().top
      };
   }
   function zoom(scale) {
      yRange[1] = Math.min(WILI_MAX, Math.max(WILI_MIN, yRange[1] * scale));
      repaint();
   }
   function submitForecastDelayed() {
      ++submitCounter;
      if(modifyCounter == submitCounter && !dragging) {
         //No modifications in the last AUTOSAVE_INTERVAL seconds
         submitForecast(false);
      }
   }
   function submitForecast(commit) {
      if(commit && $('#button_submit').hasClass('box_button_disabled')) {
         return;
      }
      var foundZero = false;
      var f = [];
      for(var i = 0; i < 52; i++) {
         f[i] = forecast[regionID][i];
         foundZero |= f[i] == 0;
      }
      if(commit) {
         if(foundZero) {
            alert('Some points are still at zero. Please double check your forecast and try again.');
            return;
         }
         timeoutID = setTimeout(submitTimeout, 10000);
         submitStatus = SubmitStatus.sent;
         updateStatus();
         $('#button_submit').addClass('box_button_disabled');
      }
      var params = {
         'action': commit ? 'forecast' : 'autosave',
         'hash': '<?= $output['user_hash'] ?>',
         'region_id': regionID,
         'f[]': f,
      };
      $.get("api.php", params, handleResponse, 'json');
   }
   function updateStatus() {
      $('#box_status').removeClass('any_success any_failure any_neutral');
      if(submitStatus == SubmitStatus.sent) {
         $('#status_icon').html('<i class="fa fa-cog fa-spin"></i>');
         $('#status_message').html('Uploading forecast...');
         $('#box_status').addClass('any_neutral');
      } else if(submitStatus == SubmitStatus.success) {
         $('#status_icon').html('<i class="fa fa-check-circle"></i>');
         $('#status_message').html('Forecast submitted successfully!');
         $('#box_status').addClass('any_success');
         //Move to the next missing region, or go home
         
         
         <?php
          $next = null;

          $regionIDs = get_user_forecast_regions($dbh, $output['user_id']);
          $currentID = $region['id'];

          // why twice? -kmm
	      // once for regions will larger ids and once for regions with smaller ids - CS

          foreach($regionIDs as &$i) {
              $otherRegion = $output['regions'][$i];
              if($i < $currentID && !$otherRegion['completed'] && $next === null) {
                      $next = $otherRegion['id'];
                  }
          }

          foreach($regionIDs as &$i) {
              $otherRegion = $output['regions'][$i];
              if($i > $currentID && !$otherRegion['completed'] && $next === null) {
                      $next = $otherRegion['id'];
                  }
          }

   
         if($next !== null) {
            ?>
            submit('forecast_<?= $next ?>');
            <?php
         } else {
            ?>
            navigate('home.php');
            <?php
         }
         ?>
      } else if(submitStatus == SubmitStatus.failure) {
         $('#status_icon').html('<i class="fa fa-times-circle"></i>');
         $('#status_message').html('Uh oh, something went wrong. Please try again later.');
         $('#box_status').addClass('any_failure');
      }
   }
   //other events
   function submitTimeout() {
      handleResponse({result: 0, action: 'forecast'});
   }
   function handleResponse(data) {
      if(data.action != 'forecast') {
         //don't really care what the result was unless it has to do with the submit forecast button
         return;
      }
      clearTimeout(timeoutID);
      //$('#stat_completed').removeClass();
      $('#button_submit').removeClass('box_button_disabled');
      if(data.result == 1) {
         //$('#stat_completed').addClass('any_success');
         //$('#stat_completed').html('Submitted');
         submitStatus = SubmitStatus.success;
      } else {
         submitStatus = SubmitStatus.failure;
      }
      updateStatus();
   }
   function resize() {
      //Find the right fit for the canvas
      var w = $('body').innerWidth() - $('#box_histories').width() - 48;
      var h = $(window).height();
      w = Math.floor(w - 24);
      h = Math.floor((h - (56 + 24 + 47 + 24 + 33)) * 0.98);
      //Get the drawing scale
      uiScale = ((w * 2 + h * 1) / 3) / 1000;
      //Apply the resize
      canvas.width = w;
      canvas.height = h;
      $('#box_canvas').width(w);
      $('#box_canvas').height(h);
      $('#box_side_bar').height(h);
      $('#box_histories').height(h - 8);
      //Finally, repaint the canvas
      repaint();
   }
   function toggleSeasonList(regionID) {
      var closedClass = 'fa-plus-square-o';
      var openedClass = 'fa-minus-square-o';
      var checkbox = $('#checkbox_region_' + regionID);
      if(checkbox.hasClass(closedClass)) {
         //Expand region
         checkbox.removeClass(closedClass);
         checkbox.addClass(openedClass);
         $('#container_' + regionID + '_all').removeClass('any_hidden');
         for(var i = 0; i < seasonYears.length; i++) {
            $('#container_' + regionID + '_' + seasonYears[i]).removeClass('any_hidden');
         }
      } else {
         //Shrink region
         checkbox.removeClass(openedClass);
         checkbox.addClass(closedClass);
         $('#container_' + regionID + '_all').addClass('any_hidden');
         for(var i = 0; i < seasonYears.length; i++) {
            $('#container_' + regionID + '_' + seasonYears[i]).addClass('any_hidden');
         }
      }
      repaint();
   }
   function toggleAllSeasons(regionID) {
      var uncheckedClass = 'fa-square-o';
      var checkedClass = 'fa-check-square-o';
      var checkbox = $('#checkbox_' + regionID + '_all');
      if(checkbox.hasClass(uncheckedClass)) {
         //Enable history
         checkbox.removeClass(uncheckedClass);
         checkbox.addClass(checkedClass);
         for(var i = 0; i < seasonYears.length; i++) {
            if($('#checkbox_' + regionID + '_' + seasonYears[i]).hasClass(uncheckedClass)) {
               toggleSeason(regionID, seasonYears[i]);
            }
         }
      } else {
         //Disable history
         checkbox.removeClass(checkedClass);
         checkbox.addClass(uncheckedClass);
         for(var i = 0; i < seasonYears.length; i++) {
            if($('#checkbox_' + regionID + '_' + seasonYears[i]).hasClass(checkedClass)) {
               toggleSeason(regionID, seasonYears[i]);
            }
         }
      }
      repaint();
   }
   function toggleSeason(regionID, seasonID) {
      var uncheckedClass = 'fa-square-o';
      var checkedClass = 'fa-check-square-o';
      var checkbox = $('#checkbox_' + regionID + '_' + seasonID);
      if(checkbox.hasClass(uncheckedClass)) {
         //Enable history
         checkbox.removeClass(uncheckedClass);
         checkbox.addClass(checkedClass);
         selectedSeasons.push([regionID, seasonID]);
      } else {
         //Disable history
         checkbox.removeClass(checkedClass);
         checkbox.addClass(uncheckedClass);
         var index = -1;
         for(var i = 0; i < selectedSeasons.length; i++) {
            if(selectedSeasons[i][0] == regionID && selectedSeasons[i][1] == seasonID) {
               index = i;
               break;
            }
         }
         if(index > -1) {
            selectedSeasons.splice(index, 1);
         }
      }
      repaint();
   }
   function snapToLastForecast() {
      var extra = lastForecast.length - forecast[regionID].length;
      for(var i = 0; i < Math.min(forecast[regionID].length, lastForecast.length - extra); i++) {
         forecast[regionID][i] = lastForecast[i + extra];
      }
      repaint();
      ++modifyCounter;
      setTimeout(submitForecastDelayed, AUTOSAVE_INTERVAL * 1000);
      modified = false;
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
      toggleSeasonList(regionID);
      <?php
    //show all seasons that are not hidden
    $hiddenSeasons = getPreference($output, 'hidden_seasons', 'int');
    foreach ($seasonYears as $season) {
        if($season == 2009 || ($hiddenSeasons & 1) === 0) { // intl "seasons" may break hidden seasons, but that's okay for now -kmm
            ?>toggleSeason(regionID, <?= $season ?>);<?php
        }
        $hiddenSeasons >>= 1;
    }
      ?>
      resize();
   });
</script>
<?php
}
require_once('common/footer.php');
?>
