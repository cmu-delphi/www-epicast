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
	    
            <div class="box_status_line"><div style="margin:20px 180px 10px 180px; padding:5px 40px; background:white; font-weight:normal; font-size:12px; text-align:left"><p><b>Additional data for the 2019-2020 COVID-19 pandemic</b></p><p>The European Centre for Disease Control (ECDC) publishes ILI data for its member nations. COVID-19 reached Italy and several other EU nations ahead of the USA, and the ECDC ILI data for those countries may be useful to your forecasts. This is a rapidly changing situation and not all ECDC reporting countries seem to agree on whether COVID-19 encounters count as ILI activity. We have excluded counties whose ECDC reporting is suspiciously similar to their 2018-2019 season, which unfortunately includes Italy and Spain. Germany is also ahead of us, but does not report ILI data. The ECDC ILI units are not a percent of visits, so <b>while the shape of the curves is accurate, the y-values have been scaled to fit in the plot</b>. For more information on the ECDC data, see the methods section of <a href="https://www.ecdc.europa.eu/sites/default/files/documents/AER_for_2015-influenza-seasonal_0.pdf">the 2015 surveillance report on seasonal Influenza</a>.</p></div></div>
	    
          </div>
	  
          <div id="box_side_bar">
            <div id="box_histories">
              <div class="box_decision_title centered" style="width: 100%;">History</div>
	      
              <div class="any_bold any_cursor_pointer" onclick="toggleSeasonList(34)"><i id="checkbox_region_34" class="fa fa-plus-square-o"></i>&nbsp;South Carolina</div>
              <div>Seasons: </div>
              <div id="container_34_all" class="any_hidden any_cursor_pointer" onclick="toggleAllSeasons(34)">&nbsp;&nbsp;&nbsp;&nbsp;<i id="checkbox_34_all" class="fa fa-square-o"></i>&nbsp;<span class="effect_tiny effect_italics">Show all</span></div>

	      <!-- typical season entry -->
              <div id="container_34_2010" class="any_hidden any_cursor_pointer"
                   onclick="toggleSeason(34, 2010)">&nbsp;&nbsp;&nbsp;&nbsp;<i
									      id="checkbox_34_2010" class="fa fa-square-o"
									      style="color: #c0e"></i>
                <span class="effect_tiny">2010-11</span>
              </div>

	      <!-- current year entry -->
              <div id="container_34_2019" class="any_hidden any_cursor_pointer">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-check-square"></i>
                <span class="effect_tiny">current year</span>
              </div>
	      
	    </div>
	  </div>
	  <div id="box_canvas"><canvas id="canvas" width="800" height="400"></canvas></div>
        </div>
      </div>
      <div class="box_footer">
	Questions/Suggestions/Feedback? Send us an <a target="_blank" href="mailto:jiaxians@andrew.cmu.edu?Subject=Epicast">email</a>!
      </div>
      <script src="js/forecast.js"></script>
    <script src="js/forecast_plot.js"></script>
    <script src="js/delphi_epidata.js"></script>
      <script>
//globals
//var DEBUG = false;
//Axis range
var currentWeek = 202013;
var minWeek = 200936;
var numPastWeeks = 28;
var numFutureWeeks = 23;
var totalWeeks = (numPastWeeks + 1 + numFutureWeeks);
var xRange = [addEpiweeks(currentWeek, -numPastWeeks), addEpiweeks(currentWeek, +numFutureWeeks)];
var yRange = [0, 26.34948]; // 1.8, really? -kmm
var regionID = 34;
var seasonOffsets = [];
var seasonYears = [];
var seasonIndices = {};
var regionNames = [];
var pastWili = [];
var pastEpiweek = [];
var forecast = [];
var curveStyles = {};
regionNames[34] = 'South Carolina';
pastWili[34] = [];
pastEpiweek[34] = [];
forecast[34] = [];
curveStyles[34] = {};
curveStyles[34][2003] = {color: '#4e5', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2004] = {color: '#7e1', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2005] = {color: '#ab0', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2006] = {color: '#c80', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2007] = {color: '#e44', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2008] = {color: '#e18', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2009] = {color: '#e0c', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2010] = {color: '#c0e', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2011] = {color: '#a2e', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2012] = {color: '#75b', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2013] = {color: '#497', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2014] = {color: '#1c3', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2015] = {color: '#0e0', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2016] = {color: '#0e0', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2017] = {color: '#0d2', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2018] = {color: '#2a6', size: 1, dash: [], alpha: 0.4};
curveStyles[34][2019] = {color: '#000', size: 2, dash: [], alpha: 1};
curveStyles[34][8003] = {color: '#307', size: 1.5, dash: [], alpha: 0.4};
curveStyles[34][8004] = {color: '#603', size: 1.5, dash: [], alpha: 0.4};
curveStyles[34][8005] = {color: '#910', size: 1.5, dash: [], alpha: 0.4};
curveStyles[34][8006] = {color: '#b40', size: 1.5, dash: [], alpha: 0.4};
curveStyles[34][8007] = {color: '#d82', size: 1.5, dash: [], alpha: 0.4};
var selectedSeasons = [];
var showLastForecast = true;
var lastForecast = [];
var timeoutID;
var lastDrag = null;
var tooltip = null;
// nowcast
var showNowcast = false;
function loader(sidebarLabel) {
    return function(result, message, epidata) {
	console.log(sidebarLabel, result, message, epidata != null ? epidata.length : void 0);
	// add line to sidebar
	// add data to arrays
    };
}
 //main
$(document).ready(function() {
 var canvas = $('#canvas');
    Epidata.fluview(loader("South Carolina"), ['sc'], [Epidata.range(minWeek, currentWeek)]);
    Epidata.fluview(loader("Region HHS4"), ['hhs4'], [Epidata.range(200936,201035)]); // need to get region for state from epicast2 db
    // etc for ECDC data
    canvas.on('mousedown touchstart', function(e) { e.preventDefault(); mouseDown(mousePosition(e)); });
    canvas.on('mouseup mouseout touchend touchleave touchcancel', function(e) { e.preventDefault(); mouseUp(mousePosition(e)); });
    canvas.on('mousemove touchmove', function(e) { e.preventDefault(); mouseMove(mousePosition(e)); });
    $(window).resize(function() {
        resize();
    });
    toggleSeasonList(regionID);
    for (var i = 0; i < nseasons; i++) {
	toggleSeason(season[i]);
    }
});
</script>
</body>
<script>'use strict';'serviceWorker'in navigator&&navigator.serviceWorker.register('sw.js').then(function(){console.log('ServiceWorker registered sucessfully.')}).catch(function(a){console.log('Registration was not successful, further details:',a)});</script>
</html>
