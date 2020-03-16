var DRAW_POINTS = true;
var TICK_SIZE = 5;
var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
var LABEL_X = 'Date (Epiweek, Month)';
var LABEL_Y = 'Flu Activity (wILI)';
var LABEL_Y_HOSP = 'Hospitlization Rate';
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
