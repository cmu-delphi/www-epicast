
//globals
//var DEBUG = false;
//Axis range
var currentWeek = 202012;
var numPastWeeks = 28;
var numFutureWeeks = 23;
var totalWeeks = (numPastWeeks + 1 + numFutureWeeks);
var xRange = [addEpiweeks(currentWeek, -numPastWeeks), addEpiweeks(currentWeek, +numFutureWeeks)];
var yRange = [0, 26.34948]; // 1.8, really? -kmm
var regionID = 34;
var seasonOffsets = [0,6,58,110,162,214,267,319,371,423,475,527,580,632,684,736,788,817,839,861,883,905,];
var seasonYears = [2003,2004,2005,2006,2007,2008,2009,2010,2011,2012,2013,2014,2015,2016,2017,2018,2019,8003,8004,8005,8006,8007,];
var seasonIndices = {'2003':0,'2004':1,'2005':2,'2006':3,'2007':4,'2008':5,'2009':6,'2010':7,'2011':8,'2012':9,'2013':10,'2014':11,'2015':12,'2016':13,'2017':14,'2018':15,'2019':16,'8003':17,'8004':18,'8005':19,'8006':20,'8007':21,};
var regionNames = [];
var pastWili = [];
var pastEpiweek = [];
var forecast = [];
var curveStyles = {};
regionNames[34] = 'South Carolina';
pastWili[34] = [-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,-1.00,0.45,0.77,0.39,0.42,0.45,0.51,0.46,0.51,0.51,0.48,0.80,0.80,1.17,1.46,2.47,2.49,4.85,7.13,6.04,4.85,3.85,2.89,2.19,1.43,0.77,0.79,0.64,0.50,0.51,0.25,0.23,0.06,0.13,0.26,0.13,0.21,0.17,0.12,0.22,0.27,0.11,0.10,0.38,0.18,0.14,0.09,0.06,0.07,0.08,0.15,0.18,0.10,0.26,0.28,0.15,0.26,0.40,0.37,0.34,0.20,0.37,0.50,0.34,0.34,0.86,0.45,0.40,0.78,0.72,0.96,0.63,0.65,0.71,0.57,0.34,0.41,0.33,0.30,0.29,0.22,0.40,0.28,0.31,0.14,0.28,0.17,0.26,0.10,0.46,0.26,0.21,0.25,0.38,0.24,0.12,0.34,0.06,0.00,0.07,0.29,0.16,0.23,0.30,0.35,0.37,0.32,0.40,0.25,0.25,0.40,0.52,1.09,2.10,4.22,3.57,3.77,3.51,2.21,2.04,1.32,0.94,0.94,0.97,1.07,1.01,0.96,1.39,1.07,1.30,1.26,0.84,0.68,0.36,0.35,0.15,0.22,0.18,0.10,0.39,0.12,0.06,0.09,0.10,0.03,0.04,0.00,0.02,0.08,0.02,0.02,0.02,0.07,0.09,0.14,0.09,0.09,0.27,0.33,0.37,0.57,0.56,0.45,0.32,1.00,1.03,1.81,1.55,2.70,2.87,2.63,2.17,1.15,1.41,1.63,0.89,0.95,0.58,0.21,0.29,0.45,0.29,0.33,0.31,0.33,0.19,0.13,0.22,0.20,0.09,0.19,0.15,0.08,0.03,0.05,0.03,0.00,0.09,0.09,0.00,0.09,0.00,0.00,0.00,0.04,0.10,0.10,0.02,0.04,0.23,0.23,0.56,0.64,0.77,0.97,0.97,0.75,1.06,1.38,1.56,4.57,6.88,5.43,3.06,1.88,2.04,1.86,1.95,1.53,1.59,1.56,1.29,0.88,0.32,0.71,0.59,0.41,0.56,0.20,0.30,0.23,0.15,0.04,0.23,0.16,0.22,0.00,0.26,0.09,0.11,0.05,0.15,0.07,0.24,0.07,0.04,0.12,0.08,0.18,0.40,0.21,0.28,0.54,0.87,1.07,1.68,1.28,1.75,1.62,1.62,1.60,0.71,0.62,0.52,0.65,0.68,0.72,0.79,0.65,1.14,1.53,1.99,2.38,2.36,2.76,1.82,1.99,1.58,1.20,1.41,0.85,1.04,0.51,0.35,0.33,0.31,0.20,0.15,0.19,0.04,0.07,0.04,0.00,0.08,0.08,0.04,0.00,0.10,0.09,0.24,0.03,0.24,0.26,0.06,1.77,1.40,1.86,1.93,2.08,1.57,1.83,2.39,2.51,1.87,2.68,2.12,4.26,4.51,3.67,5.70,6.35,7.10,6.85,6.69,7.55,5.52,5.39,6.41,7.68,5.57,4.51,3.51,2.12,2.58,1.70,1.69,0.95,0.78,0.73,0.50,0.57,0.35,0.49,0.22,0.30,0.39,0.28,0.20,0.11,0.16,0.07,0.39,0.93,1.23,1.20,1.39,1.88,1.56,1.84,2.72,2.97,2.92,3.14,4.19,4.01,4.07,4.47,6.69,8.36,7.80,7.81,9.80,13.16,13.76,14.64,12.00,7.93,5.44,3.83,3.75,3.17,2.86,2.35,1.83,1.28,1.12,1.25,0.79,1.00,0.89,0.85,0.38,0.30,0.50,0.59,0.41,0.37,0.24,0.43,0.35,0.31,0.47,0.23,0.39,1.43,1.78,1.02,1.09,1.36,1.58,1.48,1.95,2.19,2.54,2.26,2.94,2.96,2.65,3.27,4.12,5.48,4.74,3.46,3.95,4.71,6.50,9.80,8.98,8.31,7.70,6.18,5.11,4.46,4.27,4.11,2.90,2.25,2.07,1.73,1.59,1.40,1.31,1.25,1.08,1.02,0.76,0.77,0.73,0.62,0.63,0.56,0.64,0.71,0.49,0.67,0.71,1.05,1.35,1.86,1.55,2.04,2.01,2.35,2.55,2.50,3.34,3.95,3.98,4.60,4.94,5.93,7.96,11.87,10.00,7.40,6.73,7.44,9.46,12.58,10.19,7.85,7.62,8.04,9.62,9.83,-1.00,-0.98,-0.91,-0.97,-0.89,-0.67,-0.58,-0.44,-0.32,-0.01,-0.17,-0.23,0.09,0.71,1.62,3.23,5.63,6.32,5.47,4.01,2.83,2.92,0.03,0.50,-0.52,0.17,-0.25,0.52,-0.07,0.50,1.00,2.15,1.55,-1.00,0.69,4.00,1.09,1.56,3.39,2.99,3.12,1.35,1.09,6.32,-1.00,-0.84,-0.88,-0.94,-0.73,-0.64,-0.46,0.28,1.43,2.62,6.32,4.62,4.69,3.45,0.98,0.76,0.51,0.26,0.11,-0.42,-0.51,-0.18,-0.07,-0.85,-1.00,0.63,-0.61,0.24,2.25,2.22,3.09,5.38,6.10,4.35,2.68,6.32,2.06,2.01,2.04,1.52,2.64,0.96,0.08,1.28,-1.14,-1.28,-1.27,-1.10,-1.00,-1.28,-1.02,-0.69,-0.96,-0.71,-0.86,-1.51,-1.00,0.04,0.34,1.93,6.30,6.32,5.29,4.11,2.07,3.28,];
pastEpiweek[34] = [200430,200431,200432,200433,200434,200435,200436,200437,200438,200439,200440,200441,200442,200443,200444,200445,200446,200447,200448,200449,200450,200451,200452,200501,200502,200503,200504,200505,200506,200507,200508,200509,200510,200511,200512,200513,200514,200515,200516,200517,200518,200519,200520,200521,200522,200523,200524,200525,200526,200527,200528,200529,200530,200531,200532,200533,200534,200535,200536,200537,200538,200539,200540,200541,200542,200543,200544,200545,200546,200547,200548,200549,200550,200551,200552,200601,200602,200603,200604,200605,200606,200607,200608,200609,200610,200611,200612,200613,200614,200615,200616,200617,200618,200619,200620,200621,200622,200623,200624,200625,200626,200627,200628,200629,200630,200631,200632,200633,200634,200635,200636,200637,200638,200639,200640,200641,200642,200643,200644,200645,200646,200647,200648,200649,200650,200651,200652,200701,200702,200703,200704,200705,200706,200707,200708,200709,200710,200711,200712,200713,200714,200715,200716,200717,200718,200719,200720,200721,200722,200723,200724,200725,200726,200727,200728,200729,200730,200731,200732,200733,200734,200735,200736,200737,200738,200739,200740,200741,200742,200743,200744,200745,200746,200747,200748,200749,200750,200751,200752,200801,200802,200803,200804,200805,200806,200807,200808,200809,200810,200811,200812,200813,200814,200815,200816,200817,200818,200819,200820,200821,200822,200823,200824,200825,200826,200827,200828,200829,200830,200831,200832,200833,200834,200835,200836,200837,200838,200839,200840,200841,200842,200843,200844,200845,200846,200847,200848,200849,200850,200851,200852,200853,200901,200902,200903,200904,200905,200906,200907,200908,200909,200910,200911,200912,200913,200914,200915,200916,200917,200918,200919,200920,200921,200922,200923,200924,200925,200926,200927,200928,200929,200930,200931,200932,200933,200934,200935,200936,200937,200938,200939,200940,200941,200942,200943,200944,200945,200946,200947,200948,200949,200950,200951,200952,201001,201002,201003,201004,201005,201006,201007,201008,201009,201010,201011,201012,201013,201014,201015,201016,201017,201018,201019,201020,201021,201022,201023,201024,201025,201026,201027,201028,201029,201030,201031,201032,201033,201034,201035,201036,201037,201038,201039,201040,201041,201042,201043,201044,201045,201046,201047,201048,201049,201050,201051,201052,201101,201102,201103,201104,201105,201106,201107,201108,201109,201110,201111,201112,201113,201114,201115,201116,201117,201118,201119,201120,201121,201122,201123,201124,201125,201126,201127,201128,201129,201130,201131,201132,201133,201134,201135,201136,201137,201138,201139,201140,201141,201142,201143,201144,201145,201146,201147,201148,201149,201150,201151,201152,201201,201202,201203,201204,201205,201206,201207,201208,201209,201210,201211,201212,201213,201214,201215,201216,201217,201218,201219,201220,201221,201222,201223,201224,201225,201226,201227,201228,201229,201230,201231,201232,201233,201234,201235,201236,201237,201238,201239,201240,201241,201242,201243,201244,201245,201246,201247,201248,201249,201250,201251,201252,201301,201302,201303,201304,201305,201306,201307,201308,201309,201310,201311,201312,201313,201314,201315,201316,201317,201318,201319,201320,201321,201322,201323,201324,201325,201326,201327,201328,201329,201330,201331,201332,201333,201334,201335,201336,201337,201338,201339,201340,201341,201342,201343,201344,201345,201346,201347,201348,201349,201350,201351,201352,201401,201402,201403,201404,201405,201406,201407,201408,201409,201410,201411,201412,201413,201414,201415,201416,201417,201418,201419,201420,201421,201422,201423,201424,201425,201426,201427,201428,201429,201430,201431,201432,201433,201434,201435,201436,201437,201438,201439,201440,201441,201442,201443,201444,201445,201446,201447,201448,201449,201450,201451,201452,201453,201501,201502,201503,201504,201505,201506,201507,201508,201509,201510,201511,201512,201513,201514,201515,201516,201517,201518,201519,201520,201521,201522,201523,201524,201525,201526,201527,201528,201529,201530,201531,201532,201533,201534,201535,201536,201537,201538,201539,201540,201541,201542,201543,201544,201545,201546,201547,201548,201549,201550,201551,201552,201601,201602,201603,201604,201605,201606,201607,201608,201609,201610,201611,201612,201613,201614,201615,201616,201617,201618,201619,201620,201621,201622,201623,201624,201625,201626,201627,201628,201629,201630,201631,201632,201633,201634,201635,201636,201637,201638,201639,201640,201641,201642,201643,201644,201645,201646,201647,201648,201649,201650,201651,201652,201701,201702,201703,201704,201705,201706,201707,201708,201709,201710,201711,201712,201713,201714,201715,201716,201717,201718,201719,201720,201721,201722,201723,201724,201725,201726,201727,201728,201729,201730,201731,201732,201733,201734,201735,201736,201737,201738,201739,201740,201741,201742,201743,201744,201745,201746,201747,201748,201749,201750,201751,201752,201801,201802,201803,201804,201805,201806,201807,201808,201809,201810,201811,201812,201813,201814,201815,201816,201817,201818,201819,201820,201821,201822,201823,201824,201825,201826,201827,201828,201829,201830,201831,201832,201833,201834,201835,201836,201837,201838,201839,201840,201841,201842,201843,201844,201845,201846,201847,201848,201849,201850,201851,201852,201901,201902,201903,201904,201905,201906,201907,201908,201909,201910,201911,201912,201913,201914,201915,201916,201917,201918,201919,201920,201921,201922,201923,201924,201925,201926,201927,201928,201929,201930,201931,201932,201933,201934,201935,201936,201937,201938,201939,201940,201941,201942,201943,201944,201945,201946,201947,201948,201949,201950,201951,201952,202001,202002,202003,202004,202005,202006,202007,202008,202009,202010,202011,202012,201941,201942,201943,201944,201945,201946,201947,201948,201949,201950,201951,201952,202001,202002,202003,202004,202005,202006,202007,202008,202009,202010,201941,201942,201943,201944,201945,201946,201947,201948,201949,201950,201951,201952,202001,202002,202003,202004,202005,202006,202007,202008,202009,202010,201941,201942,201943,201944,201945,201946,201947,201948,201949,201950,201951,201952,202001,202002,202003,202004,202005,202006,202007,202008,202009,202010,201941,201942,201943,201944,201945,201946,201947,201948,201949,201950,201951,201952,202001,202002,202003,202004,202005,202006,202007,202008,202009,202010,201941,201942,201943,201944,201945,201946,201947,201948,201949,201950,201951,201952,202001,202002,202003,202004,202005,202006,202007,202008,202009,202010,];
forecast[34] = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,];
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
            var x = getX(xs[i]);
            var y = getY(ys[i]);
            g.moveTo(x, y);
            g.lineTo(x + 1, y);
            g.stroke();
        }
    }
}

function drawCurve(curve, start, end, epiweekOffset, style) {
    var g = getGraphics();
    g.strokeStyle = style.color;
    g.lineWidth = style.size * uiScale;
    g.setLineDash(style.dash);
    g.beginPath();
    var first = true;
    var epiweek = addEpiweeks(xRange[0], epiweekOffset);
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
        epiweek = addEpiweeks(epiweek, 1);
    }
    g.stroke();
    g.setLineDash([]);
    if(DRAW_POINTS) drawPoints(curve, start, end, epiweekOffset, style, g);
}
function drawCurveXY(xs, ys, start, end, style) {
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
    if(DRAW_POINTS) drawPoints(xs, ys, start, end, style, g);
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
    
    var seasonIndex = seasonIndices[2019];
    var seasonLength = seasonOffsets[seasonIndex+1] - seasonOffsets[seasonIndex];
    var x1 = getX(addEpiweeks(xRange[0], seasonLength - 1));
    var y1 = getY(pastWili[regionID][seasonOffsets[seasonIndex + 1] - 1]);
    var x2 = getX(addEpiweeks(currentWeek, xoffset));
    drawLine(x1, y1, x2, y2, style);
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
        drawText(g, "Flu Activity", row1 - 8 * uiScale, canvas.height / 2, -Math.PI / 2, Align.center, Align.center, 1.5, ['bold', 'Calibri']);
        drawText(g, "(% of all doctors’ office visits that involve flu-like symptoms)", row1 + 7 * uiScale, canvas.height / 2, -Math.PI / 2, Align.center, Align.center, 1.5, ['', 'Calibri']);
	
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
        var month = Math.floor((xRange[0] % 100 - 1) / getNumWeeks(Math.floor(xRange[0] / 100)) * MONTHS.length);
        var on = true;
        for(var epiweek = xRange[0]; epiweek <= xRange[1]; epiweek = addEpiweeks(epiweek, 4.35)) {
            var label = MONTHS[month];
            if(month == 0) {
                label += '\'' + (Math.floor(epiweek / 100) % 100);
            }
            oldFillStyle=g.fillStyle;
            g.fillStyle = on ? '#eee' : '#fff'; on = !on;
            x1 = max(xRange[0]-1, addEpiweeks(epiweek,-4.35/2));
            y1 = canvas.height - row3 + row2/4;
            x2 = min(addEpiweeks(x1, 4.35), xRange[1])
            g.fillRect(getX(x1), y1, getX(x2)-getX(x1), row2/2);
            console.log(label+":"+[getX(x1), y1, getX(x2)-getX(x1), row2/2]);
            g.fillStyle = oldFillStyle;
            
            drawText(g, label, getX(epiweek), canvas.height - row2, 0, Align.center, Align.center);
            month = (month + 1) % MONTHS.length;
        }
        //label
        drawText(g, "Date (weeks)", canvas.width / 2, canvas.height - row1, 0, Align.center, Align.center, 1.5, ['bold', 'Calibri']);
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
    function repaintSelection(r, s) {
        if (typeof s == "undefined") {
            i = r;
            var r = selectedSeasons[i][0];
            var s = selectedSeasons[i][1];   
        } 
        var style = curveStyles[r][s];
        var start = seasonOffsets[seasonIndices[s]];
        // var length = totalWeeks;
        
        
        // re the below: nah, we stored the dates for a reason.
        //if(start == 0) {
        //   var nextStart = seasonOffsets[seasonIndices[s + 1]];
        //   length = nextStart - start;
        //   //todo: that -1 at the end should only be there if current season has 53 weeks and past season has 52 weeks
        //   epiweekOffset = Math.max(0, totalWeeks - length -1);
        //}
        
        var end = seasonIndices[s]+1 < seasonOffsets.length ? seasonOffsets[seasonIndices[s]+1] : pastWili[r].length;
        drawCurveXY(pastEpiweek[r], pastWili[r], start, end, style);
	
        // forecast gets drawn later, no need to do it twice
        //if(s == 2019) {
        //   style = {color: style.color, size: style.size, dash: DASH_STYLE};
        //   drawCurve(forecast[r], 0, 52, numPastWeeks + 1, style);
        //   stitchCurves(r, style);
        //}
    }
    for(var i = 0; i < selectedSeasons.length; i++) {
        var isCurrentSeason = (selectedSeasons[i][1] == 2019);
        if(selectedSeasons[i][0] == regionID && isCurrentSeason) {
            //Skip the current region's latest season
            continue;
	}
	repaintSelection(i);
    }
    
    //last forecast
    var lfStyle = {color: '#aaa', size: 2, dash: DASH_STYLE};
    if(showLastForecast) {
	// shift x axis by 30 weeks.
	drawCurve(lastForecast, 0, lastForecast.length, totalWeeks - lastForecast.length, lfStyle);
	stitchCurves(regionID, lfStyle, getY(lastForecast[0]), 0);
    }
    
    //current region and latest season
    repaintSelection(regionID, 2019);
    var style = {color: '#000', size: 2, dash: DASH_STYLE};
    //var start = seasonOffsets[seasonOffsets.length - 1];
    //var end = Math.min(pastWili[regionID].length, start + totalWeeks);
    //drawCurve(pastWili[regionID], start, end, 0, style);
    drawCurve(forecast[regionID], 0, 52, numPastWeeks + 1, style);
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
	var end = seasonIndices[2019]+1 < seasonOffsets.length ? seasonOffsets[seasonIndices[2019]+1] : pastWili[regionId].length;
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
    //Drawing ecast
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
        'hash': 'c569b530c3d7361f2fcc73641a9b0f44',
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
	
	
	submit('forecast_48');
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
    if(dion != 'forecast') {
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
        checkbox.removeClass(checkedClaindex = -1;
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
    toggleSeason(regionID, 2003);toggleSeason(regionID, 2004);toggleSeason(regionID, 2005);toggleSeason(regionID, 2006);toggleSeason(regionID, 2007);toggleSeason(regionID, 2008);toggleSeason(regionID, 2009);toggleSeason(regionID, 2010);toggleSeason(regionID, 2011);toggleSeason(regionID, 2012);toggleSeason(regionID, 2013);toggleSeason(regionID, 2014);toggleSeason(regionID, 2015);toggleSeason(regionID, 2016);toggleSeason(regionID, 2017);toggleSeason(regionID, 2018);toggleSeason(regionID, 2019);toggleSeason(regionID, 8003);toggleSeason(regionID, 8004);toggleSeason(regionID, 8005);toggleSeason(regionID, 8006);toggleSeason(regionID, 8007);      resize();
});
    
    
