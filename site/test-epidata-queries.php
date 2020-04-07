<!DOCTYPE html>
<html>
    <head>
        <title>Delphi Crowdcast-FLUV</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="js/utils.js"></script>
        <script src="js/rAF.js"></script>
        <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" media="none" onload="if(media!='all')media='all'" />
        <noscript>
          <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" />
        </noscript>
        <link href="//fonts.googleapis.com/css?family=Yanone+Kaffeesatz:700|Alegreya+SC:700" rel="stylesheet" media="none" onload="if(media!='all')media='all'" />
        <noscript>
          <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Yanone+Kaffeesatz:700|Alegreya+SC:700" />
        </noscript>
        <link href="css/style.php" rel="stylesheet" />
    </head>
    <body>
      <a name="top"></a>
      <div class="box_header box_header_narrow box_header_fixed0"></div>
      <div class="box_header box_header_narrow box_header_fixed1">
        <div class="box_title box_title_mini"><span class="effect_delphi">Crowdcast</span>&nbsp;<span class="effect_fluv">FLUV</span></div>
        <div class="box_mininav">
          <span class="effect_tiny_header">Crowdcaster: Anonymous Epicaster [C569B530]<br /></span>
          <a class=""  href="home.php#top">Home</a>&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a class=""  href="preferences.php">Preferences</a>&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a class=""  href="scores.php">Leaderboards</a>&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a class=""  href="logout.php">Logout</a>&nbsp;&nbsp;|&nbsp;&nbsp;<span class="effect_delphi"><a class="delphi" target="_blank" href="https://delphi.midas.cs.cmu.edu/">DELPHI</a></span>         </div>
        <div class="box_miniclear"></div>
      </div>
      <div class="box_content">
        <script>
          function redirect(url) {
          window.location.href = url;
          }
	  
          function onRegionInDropdownSelected(ev) {
          submit(ev.value);
          }	
        </script>
	
        <form id="forecast_1" method="POST" action="forecast.php#top">
          <input type="hidden" name="region_id" value="1" />
        </form>
	
        <div id="box_nocanvas" class="any_hidden box_message any_failure"><i class="fa fa-exclamation-triangle"></i> Whoa, your screen is too small! Please visit this site on a non-mobile device, or try to expand your browser window. Sorry about that!</div>

	<div id="box_main_ui">
          <div id="box_status" class="box_status any_neutral right">
            <div class="box_status_line">
              <div class="box_region_label">
                South Carolina            <span style="font-size: 0.67em;">
                  [SC]
                </span>
              </div>
              <div style="float: right;">
                <div id="button_submit" class="box_button " onClick="submitForecast(true)" ><i class="fa fa-upload"></i>&nbsp;&nbsp;Save &amp; Continue</div>
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

<!--               <div class="any_bold any_cursor_pointer" onclick="toggleSeasonList(34)"><i id="checkbox_region_34" class="fa fa-plus-square-o"></i>&nbsp;South Carolina</div>
              <div>Seasons: </div>
              <div id="container_34_all" class="any_hidden any_cursor_pointer" onclick="toggleAllSeasons(34)">&nbsp;&nbsp;&nbsp;&nbsp;<i id="checkbox_34_all" class="fa fa-square-o"></i>&nbsp;<span class="effect_tiny effect_italics">Show all</span></div>


              <div id="container_34_2010" class="any_hidden any_cursor_pointer"
                   onclick="toggleSeason(34, 2010)">&nbsp;&nbsp;&nbsp;&nbsp;<i
									      id="checkbox_34_2010" class="fa fa-square-o"
									      style="color: #c0e"></i>
                <span class="effect_tiny">2010-11</span>
              </div>


              <div id="container_34_2019" class="any_hidden any_cursor_pointer">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-check-square"></i>
                <span class="effect_tiny">current year</span>
              </div>
 -->

	    </div>
	  </div>
	  <div id="box_canvas"><canvas id="canvas" width="800" height="400"></canvas></div>
        </div>
      </div>
      <div class="box_footer">
	Questions/Suggestions/Feedback? Send us an <a target="_blank" href="mailto:jiaxians@andrew.cmu.edu?Subject=Epicast">email</a>!
      </div>
    <script id="sidebar_template" type="x-tmpl-mustache">
    <div class="any_bold any_cursor_pointer sidebar_entry" onclick="toggleSeasonList('{{rid}}')"><i id="checkbox_region_{{rid}}" class="fa fa-minus-square-o"></i>{{title}}</div>
<div id="container_{{rid}}_all" class="sidebar_region">
<div class="any_cursor_pointer {{^seasons.5}}any_hidden{{/seasons.5}}" onclick="toggleAllSeasons('{{rid}}')"><i id="checkbox_{{rid}}_all" class="fa fa-square-o"></i><span class="effect_tiny effect_italics">Show all</span></div>
    {{#seasons}}
    <div id="container_{{rid}}_{{year}}" {{^current}}class="any_cursor_pointer" onclick="toggleSeason('{{rid}}', {{year}})"{{/current}}><i id="checkbox_{{rid}}_{{year}}" class="fa {{#current}}fa-check-square{{/current}}{{^current}}fa-square-o{{/current}}" style="color: {{color}}"></i><span class="effect_tiny">{{label}} {{#current}} current year{{/current}}</span></div>
{{/seasons}}
</div>
    </script>
    <script src="js/forecast.js?w=202014"></script>
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
//var DEBUG = false;
//Axis range

//var numPastWeeks = 28;
//var numFutureWeeks = 23;
//var totalWeeks = (numPastWeeks + 1 + numFutureWeeks);
//var xRange = [addEpiweeks(currentWeek, -numPastWeeks), addEpiweeks(currentWeek, +numFutureWeeks)];




//var seasonOffsets = [];
//var seasonYears = [];
//var seasonIndices = {};
//var regionNames = [];
//var pastWili = [];
//var pastEpiweek = [];
//var forecast = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,];
//var curveStyles = {};
regionName = 'South Carolina';
var selectedSeasons = [];
var showLastForecast = true;
//var lastForecast = [];
var timeoutID;
var lastDrag = null;
var tooltip = null;
// nowcast
var showNowcast = false;


var minWeek = 200936;
var maxWeek = 202040;

var currentWeek = 202013;
var currentSeason = 2019;
var regionID = 'sc'; // was: 34

var xRange = [currentSeason*100+36, (currentSeason+1)*100+36];
var yRange = [0, 26.34948]; // 1.8, really? -kmm
var curves = {
    lastForecast: [],
    forecast: []
};
function loader(sidebarTitle,rid,parent) {
    return function(result, message, epidata) {
	console.log(sidebarTitle, result, message, epidata != null ? epidata.length : void 0);
	var module = {}
	module.title = sidebarTitle;
	module.rid = rid;
	module.data = epidata;
	module.seasons = [];  var mi=0;
	module.season = {};
	var season = -1;
	// CDC seasons run from week 36 to week 35 of the following year
	for (var i=0; i<epidata.length; i++) {
	    var si = Math.floor(epidata[i].epiweek / 100);
	    if (epidata[i].epiweek % 100 < 36) si = si - 1;
	    if (season<0 || season != si) {
		if (season>=0) module.season[season].end = i-1;
		season = si;
		module.season[season] = {start:i,year:season,label:season==2009?(season+" pandemic"):season,color:getStyle(rid,season).color,current:season==currentSeason};
		module.seasons[mi++] = module.season[season]; // ugh
	    }
	}
	console.log(module);
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

    Epidata.fluview(loader("South Carolina",'sc',"#current_region"), ['sc'], [Epidata.range(minWeek, currentWeek)]);
    Epidata.fluview(loader("Region HHS4",'hhs4',"#regional_pandemic"), ['hhs4'], [Epidata.range(200936,201035)]); // need to get region for state from epicast2 db
});
</script>
</body>
<script>'use strict';'serviceWorker'in navigator&&navigator.serviceWorker.register('sw.js').then(function(){console.log('ServiceWorker registered sucessfully.')}).catch(function(a){console.log('Registration was not successful, further details:',a)});</script>
</html>
