//x-axis
function getChartWidth() {
    return canvas.width - marginLeft() - marginRight();
}
function getX(epiweek, season) {
    if (season) {
	epiweek = epiweek + 100*(currentSeason - season);
    }
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
function findPoint(region, epiweek, interp) {
    var year = Math.floor(epiweek/100);
    if (epiweek % 100 < seasonDefn[1]) { year--; }
    var season = curves[region].season[year];
    // we usually are finding something near the end, so start
    // there and work backwards
    // (if we ever need true random-access, convert this to binary search)
    for (var i = season.end; i >= season.start; i--) {
	var point =  curves[region].data[i];
	if (point.epiweek == epiweek) {
	    return point;
	}
	if (point.epiweek < epiweek && interp) {
	    return point;
	}
    }
    return false;
}
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
function drawPoints(xs, ys, style, g) {
    if (typeof g == 'undefined') {
        var g = getGraphics();
        g.strokeStyle = style.color;
        g.lineWidth = style.size * uiScale;
        g.setLineDash([]);
    }
    g.lineWidth = 3 * style.size * uiScale;
    for(var i = 0; i < xs.length; i++) {
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

function drawCurveData(curve, style, do_drawPoints, season, key) {
    if (typeof season == "undefined") {
	season = currentSeason;
    }
    if (typeof key == "undefined") {
	key = "wili";
    }
    var g = getGraphics();
    g.strokeStyle = style.color;
    g.lineWidth = style.size * uiScale;
    g.setLineDash(style.dash);
    g.beginPath();
    var first = true;
    for(var i = 0; i < curve.length; i++) {
	if (curve[i][key] < 0) { continue; }
	var x = getX(curve[i].epiweek, season);
	var y = getY(curve[i][key]);
	if(first) {
	    first = false;
	    g.moveTo(x, y);
	} else {
	    g.lineTo(x, y);
	}
	
    }
    g.stroke();
    if(do_drawPoints) {
	g.setLineDash([]);
	drawPoints(curve.map(function (item) { return item.epiweek; }),
		   curve.map(function (item) { return item[key]; }),
		   style, g);
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
function stitchCurves(rid, style, y2, xoffset) {
    if(forecast[0] < 0) {
        return;
    }
    if (typeof y2 == "undefined") {
        y2 = getY(forecast[0]);
    }
    if (typeof xoffset == "undefined") {
        xoffset = 1;
	
    }
    
    var seasonIndex = seasonIndices[2019];
    var seasonLength = seasonOffsets[seasonIndex+1] - seasonOffsets[seasonIndex];
    var x1 = getX(addEpiweeks(xRange[0], seasonLength - 1));
    var y1 = getY(pastWili[seasonOffsets[seasonIndex + 1] - 1]);
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
function getStyle(region, season) {
    var ret;
    
    if (region==regionID && season==currentSeason) {
	ret = {color: '#000', size: 2, dash: [], alpha: 1};
    } else if (region.startsWith("hhs")) {
	ret = {color: '#66f', size: 1, dash: [], alpha: 0.4};
    } else if (season==2009) { //pandemic
	ret = {color: '#666', size: 1, dash: [], alpha: 0.4};
    } else {
	ret = {color: '#aaa', size: 0.5, dash: [], alpha: 0.4};
    }
    
    if (hoverCurve(region,season)) {
	ret.size++;
    }
    
    return ret;
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
        drawText(g, LABEL_Y, row1 - 8 * uiScale, canvas.height / 2, -Math.PI / 2, Align.center, Align.center, 1.5, ['bold', 'Calibri']);
        //drawText(g, "(% of all doctors’ office visits that involve flu-like symptoms)", row1 + 7 * uiScale, canvas.height / 2, -Math.PI / 2, Align.center, Align.center, 1.5, ['', 'Calibri']);
	
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
	var decStart = epiweekToDecimal(xRange[0]-1);
	var decEnd = epiweekToDecimal(xRange[1]);
	// run through the year twice so we get everybody no matter how long our current season goes on
	for (var si = 0; si<2; si++) {
	    for (var li=0; li<MONTHS.length; li++) {
		var label = MONTHS[li];
		var epiDecimal = currentSeason + si + li/12.;		
		
		x1 = epiDecimal;
		x2 = epiDecimal + 1./12;
		
		// skip months that won't show up
		if (x1 > decEnd || x2 < decStart) {
		    continue;
		}
		x1 = decimalToFEpiweek(max(decStart,x1));
		x2 = decimalToFEpiweek(min(decEnd,x2));

		// skip truncated months that are too short for their label
		if (x2-x1<1.5) { continue; } 
		
		x1 = getX(x1);
		x2 = getX(x2);
		y1 = canvas.height - row3 + row2/4;
		oldFillStyle=g.fillStyle;
		g.fillStyle = on ? '#eee' : '#fff'; on = !on;
		g.fillRect(x1, y1, x2-x1, row2/2);
		g.fillStyle = oldFillStyle;
		
		drawText(g, label, 0.5*(x1 + x2), canvas.height - row2, 0, Align.center, Align.center);
            }
	}
        //label
        drawText(g, LABEL_X, canvas.width / 2, canvas.height - row1, 0, Align.center, Align.center, 1.5, ['bold', 'Calibri']);
    }

    //covid-19 benchmarks
    oldFillStyle=g.fillStyle;
    if (covidBenchmarks.first) {
	covid_1   = getX(decimalToFEpiweek(covidBenchmarks.first*1.)); // first cases
	drawLine(covid_1, getY(yRange[0]), covid_1, getY(yRange[1]), {color:"#F00", size:0.5, dash:[], alpha:0.4});
	g.fillStyle="#600";
	drawText(g, "First COVID-19 case in "+regionName+" ->", covid_1 - 10, marginTop() + 36*uiScale, 0, Align.right, Align.top);
    }
    if (covidBenchmarks.hundred) {
	covid_100 = getX(decimalToFEpiweek(covidBenchmarks.hundred*1.)); // 100 cases
	drawLine(covid_100, getY(yRange[0]), covid_100, getY(yRange[1]), {color:"#F00", size:1.5, dash:[], alpha:0.4});
	g.fillStyle="#600";
	drawText(g, "<- 100 cases in "+regionName, covid_100 + 10, marginTop() + 36*uiScale, 0, Align.left, Align.top);
    }
    g.fillStyle=oldFillStyle;
    
    //other regions or past seasons
    function repaintSeason(r, s, do_drawPoints) {
        if (typeof s == "undefined") {
            i = r;
            var r = selectedSeasons[i][0];
            var s = selectedSeasons[i][1];
	    do_drawPoints = false;
        } 
        var style = getStyle(r, s); //curveStyles[r][s];
	
	if (typeof curves[r] == "undefined") {
	    console.log("repaint:",r,"not yet available");
	    return;
	}
	drawCurveData(
	    curves[r].data.slice(
		curves[r].season[s].start,
		curves[r].season[s].end+1), style, do_drawPoints, s);
    }
    for(var i = 0; i < selectedSeasons.length; i++) {
        var isCurrentSeason = (selectedSeasons[i][1] == 2019);
        if(selectedSeasons[i][0] == regionID && isCurrentSeason) {
            // Skip the current region's latest season
	    // so it can be plotted on top of everything else
            continue;
	}
	repaintSeason(i);
    }
    
    var lfStyle = {color: '#aaa', size: 2, dash: DASH_STYLE};
    var style = {color: '#000', size: 2, dash: DASH_STYLE};
    if (regionID in curves) {
	//last forecast

	if(showLastForecast) {
	    // shift x axis by 30 weeks.
	    // drawCurve(lastForecast, 0, lastForecast.length, totalWeeks - lastForecast.length, lfStyle);
	    drawCurveData([findPoint(regionID, curves.lastForecast[0].epiweek -1)].concat(curves.lastForecast), lfStyle, true);
	    
	    //stitchCurves(regionID, lfStyle, getY(lastForecast[0]), 0);
	}
	
	//current region and latest season
	repaintSeason(regionID, 2019, true);
	//var start = seasonOffsets[seasonOffsets.length - 1];
	//var end = Math.min(pastWili.length, start + totalWeeks);
	//drawCurve(pastWili, start, end, 0, style);
	//drawCurve(forecast, 0, 52, numPastWeeks + 1, style);
	drawCurveData([findPoint(regionID, currentWeek, true)].concat(curves.forecast), style, true);
	//stitchCurves(regionID, style);
    }
    
    //nowcast
    if(showNowcast) {
	g.fillStyle = 'rgba(0, 0, 0, 0.5)';
	var epiweek = addEpiweeks(currentWeek, 1);
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
    var errors = {
	'nat':[-0.24705835, 0.26585897, -0.15209838, 0.19588030, -0.12080783, 0.14845500, -0.10822840, 0.13591350, -0.10105576, 0.11903400],
	'hhs1':[-0.37140890, 0.28183701, -0.22718089, 0.22283626, -0.17166020, 0.15932419, -0.15244192, 0.13857609, -0.13520489, 0.12653161],
	'hhs2':[-0.53510369, 0.89618800, -0.29194798, 0.65376200, -0.13691200, 0.53989966, -0.12287200, 0.46070700, -0.07438098, 0.41997600],
	'hhs3':[-0.37340794, 0.40633099, -0.28260333, 0.17494332, -0.22924145, 0.12111835, -0.18220829, 0.09744193, -0.15922900, 0.08408102],
	'hhs4':[-0.20515699, 0.30015400, -0.11709100, 0.25312400, -0.08401870, 0.22570893, -0.06906100, 0.20316300, -0.06395200, 0.17931200],
	'hhs5':[-0.25007300, 0.20134411, -0.13535207, 0.12399100, -0.13027507, 0.10968548, -0.12658071, 0.09060300, -0.12210600, 0.09081896],
	'hhs6':[-0.57142423, 0.64259200, -0.26681298, 0.44821271, -0.17997876, 0.42294960, -0.18924163, 0.40526105, -0.18486160, 0.41010436],
	'hhs7':[-0.31905190, 0.53929610, -0.28534067, 0.25807903, -0.18014395, 0.17609501, -0.09770261, 0.15003601, -0.06749161, 0.11253900],
	'hhs8':[-0.34997449, 0.16271156, -0.30672299, 0.11085698, -0.28115293, 0.08104906, -0.24976742, 0.07652170, -0.27224423, 0.07954395],
	'hhs9':[-1.35720500, 0.36575900, -0.83282601, 0.33934500, -0.57508135, 0.29297430, -0.25338298, 0.25961193, -0.22189758, 0.23839696],
	'hhs10':[-0.27577982, 0.67580001, -0.13440096, 0.51631755, -0.08888274, 0.42762205, -0.08109139, 0.37271498, -0.05693280, 0.26734400]
    };
    
    if (errors.hasOwnProperty(regionID) && curves.hasOwnProperty(regionID)) {
	var end = curves[regionID].season[currentSeason].end;
	// the first error bar is for the most recent data available on the current season
	var epiweek = curves[regionID].data[end].epiweek; // was: currentWeek;
	var error = errors[regionID];
	for (var i=0; i<9; i = i + 2) {
	    var currentSeasonIndex = end - i/2;
	    var above = -error[i]*scale;
	    var below = error[i+1]*scale;
	    var x_weekNumber = addEpiweeks(epiweek, -(i/2));
	    var x = getX(x_weekNumber);
	    var y = getY(curves[regionID].data[currentSeasonIndex].wili);
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
    drawText(g, regionName + ', ' + Math.round(xRange[0] / 100) + '+', x2 - 3, y, 0, Align.right, Align.center);
    style.dash = [];
    drawLine(x1, y - 3, x2, y + 3, style);
    
    //       error bar legend
    if (errors.hasOwnProperty(regionID)) {
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
function decimalToFEpiweek(yr) {
    yr += 0.5 / 52;
    var year = Math.floor(yr);
    var wk = yr - year;
    var week = wk * getNumWeeks(year);
    return year * 100 + week;
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
	var point = {'epiweek':epiweek, 'wili':wili};
	curves.forecast[getDeltaWeeks(currentWeek, epiweek) - 1] = point;
	if(lastDrag != null && epiweek != lastDrag.epiweek) {
            var direction = (epiweek > lastDrag.epiweek) ? 1 : -1;
	    for(var i = addEpiweeks(lastDrag.epiweek, direction); i != epiweek; i = addEpiweeks(i, direction)) {
		curves.forecast[getDeltaWeeks(currentWeek, i) - 1] = {'epiweek':i, 'wili':point.wili};
	    }
	}
	lastDrag = point;//{epiweek: epiweek, wili: wili};
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
	if (!curves.forecast[i]) { continue; }
	if (curves.forecast[i].wili == 0) {
	    foundZero = curves.forecast[i].epiweek;
	    break;
	}
        f[i] = curves.forecast[i].wili;
    }
    if(commit) {
        if(foundZero) {
            alert('Some points are still at zero (for '+Math.floor(foundZero/100)+' w'+foundZero%100+'; maybe others). Please double check your forecast and try again.');
            return;
        }
        timeoutID = setTimeout(submitTimeout, 10000);
        submitStatus = SubmitStatus.sent;
        updateStatus();
        $('#button_submit').addClass('box_button_disabled');
    }
    var params = {
        'action': commit ? 'forecast' : 'autosave',
        'hash': userHash,
        'region_id': regionNo,
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
	submit('forecast');
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
function hoverCurve(rid, season) {
    return curves[rid]
	&& curves[rid].season
	&& curves[rid].season[season]
	&& curves[rid].season[season].hover;
}
function hoverCurveOn(rid, season) {
    curves[rid].season[season].hover = true;
    repaint();
}
function hoverCurveOff(rid, season) {
    curves[rid].season[season].hover = false;
    repaint();
}
function toggleSeasonList(rid) {
    var closedClass = 'fa-plus-square-o';
    var openedClass = 'fa-minus-square-o';
    var checkbox = $('#checkbox_region_' + rid);
    if(checkbox.hasClass(closedClass)) {
        //Expand region
        checkbox.removeClass(closedClass);
        checkbox.addClass(openedClass);
        $('#container_' + rid + '_all').removeClass('any_hidden');
    } else {
        //Shrink region
        checkbox.removeClass(openedClass);
        checkbox.addClass(closedClass);
        $('#container_' + rid + '_all').addClass('any_hidden');
    }
    repaint();
}
function toggleAllSeasons(rid) {
    var uncheckedClass = 'fa-square-o';
    var checkedClass = 'fa-check-square-o';
    var checkbox = $('#checkbox_' + rid + '_all');
    if(checkbox.hasClass(uncheckedClass)) {
        //Enable history
        checkbox.removeClass(uncheckedClass);
        checkbox.addClass(checkedClass);
	for(var season in curves[rid].season) {
	    if($('#checkbox_' + rid + '_' + season).hasClass(uncheckedClass)) {
                toggleSeason(rid, season);
            }
        }
    } else {
        //Disable history
        checkbox.removeClass(checkedClass);
        checkbox.addClass(uncheckedClass);
	for(var season in curves[rid].season) {
            if($('#checkbox_' + rid + '_' + season).hasClass(checkedClass)) {
                toggleSeason(rid, season);
            }
        }
    }
    repaint();
}
function toggleSeason(rid, seasonID) {
    var uncheckedClass = 'fa-square-o';
    var checkedClass = 'fa-check-square-o';
    var checkbox = $('#checkbox_' + rid + '_' + seasonID);
    if(checkbox.hasClass(uncheckedClass)) {
        //Enable history
        checkbox.removeClass(uncheckedClass);
        checkbox.addClass(checkedClass);
        selectedSeasons.push([rid, seasonID]);
    } else {
        //Disable history
	checkbox.removeClass(checkedClass);
	checkbox.addClass(uncheckedClass);
	var index = -1;
        for(var i = 0; i < selectedSeasons.length; i++) {
            if(selectedSeasons[i][0] == rid && selectedSeasons[i][1] == seasonID) {
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
    var extra = curves.lastForecast.length - curves.forecast.length;
    for(var i = 0; i < Math.min(curves.forecast.length, curves.lastForecast.length - extra); i++) {
        curves.forecast[i] = curves.lastForecast[i + extra];
    }
    repaint();
    ++modifyCounter;
    setTimeout(submitForecastDelayed, AUTOSAVE_INTERVAL * 1000);
    modified = false;
}
    
    
