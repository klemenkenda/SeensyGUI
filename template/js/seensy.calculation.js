// HACKS
(function() {
    Date.prototype.toYMD = Date_toYMD;
    function Date_toYMD() {
        var year, month, day;
        year = String(this.getFullYear());
        month = String(this.getMonth() + 1);
        if (month.length == 1) {
            month = "0" + month;
        }
        day = String(this.getDate());
        if (day.length == 1) {
            day = "0" + day;
        }
        return year + "-" + month + "-" + day;
    }

    Date.createFromMysql = function (mysql_string) {
        var t, result = null;

        if (typeof mysql_string === 'string') {
            t = mysql_string.split(/[- T:]/);

            //when t[3], t[4] and t[5] are missing they defaults to zero
            result = new Date(t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0);
        }

        return result;
    }
})();

$('#loading1').text('Loading available sensors ...');
$('#loading2').text('Loading available sensors ...');
$('#loading3').text('Loading available sensors ...');

$(function () {
    //init-ing and setting a few starting variables
    $.fn.datepicker.defaults.format = "yyyy-mm-dd";
    $('#datepicker').datepicker({
        // inputs: {},
        /*
        forceParse: false,
        autoclose: true,
        weekStart: 1,
        keyboardNavigation: false,
        assumeNearbyYear: true,
        immediateUpdates: true
        */
    });
    
    // $("#end").keypress(function(event) {event.preventDefault();});
    
    myNodes = [];
    namesToLocations = {} //maps dataType names to their places in myNodes
});

$.ajax({
    //getting information about nodes
    url: '/api/get-nodes',
    success: loadedNodes
});

function loadedNodes(data) {
    //saving nodes...we will need them to get unit types and such when drawing charts
    myNodes = JSON.parse(data);
    loadedSensorsFromNodes();
    updateDate(); //displays proper dates also when page starts
    $('#loading1').text('Light sensor');
    $('#loading2').text('Cloud cover');
    $('#loading3').text('Presence');
}

function loadedSensorsFromNodes() {
    mySensors = [];
    for (n in myNodes) {
        for (s in myNodes[n].Sensors) {
            mySensors.push(myNodes[n].Sensors[s].Name);
        }        
    }
    updateSensors(mySensors);
}

function updateDate() {        
    dataTyp = $('#ch-sensor option:selected').val();
    found = false;
    counter = 0;
    counter2 = 0;
    while (!found && counter < myNodes.length) {
        counter2 = 0;
        while (!found && counter2 < myNodes[counter].Sensors.length) {
            if (myNodes[counter].Sensors[counter2].Name == dataTyp) {
                found = true;
            }
            counter2++;
        }
        counter++;
    }
    counter--;
    counter2--;
    endDate = myNodes[counter].Sensors[counter2].EndDate.split("-");
    startDate = myNodes[counter].Sensors[counter2].StartDate.split("-");
    $('#start').datepicker('setDate', new Date(endDate[0], endDate[1] - 1, endDate[2] - 7));
    $('#end').datepicker('setDate', new Date(endDate[0], endDate[1] - 1, endDate[2]));
    //??? why doesnt setting min/max work?
    //$('#start').datepicker('option', {'minDate': new Date(startDate[0], startDate[1]-1, startDate[2]), 'maxDate': new Date(endDate[0], endDate[1] - 1, endDate[2])});
    //$('#end').datepicker('option', {'minDate': new Date(startDate[0], startDate[1]-1, startDate[2]), 'maxDate': new Date(endDate[0], endDate[1] - 1, endDate[2])});
    $('#start').datepicker('render');
    $('#end').datepicker('render');
    if (found) {
        namesToLocations[dataTyp] = { "fst": counter, "snd": counter2 };
    }
    console.log($("#end"));
}

function updateDate1() {
    dataTyp = $('#ch-sensor option:selected').val();
    found = false;
    counter = 0;
    counter2 = 0;
    while (!found && counter < myNodes.length) {
        counter2 = 0;
        while (!found && counter2 < myNodes[counter].Sensors.length) {
            if (myNodes[counter].Sensors[counter2].Name == dataTyp) {
                found = true;
            }
            counter2++;
        }
        counter++;
    }
    counter--;
    counter2--;
    if (found) {
        namesToLocations[dataTyp] = { "fst": counter, "snd": counter2 };
    }
    endDate = myNodes[counter].Sensors[counter2].EndDate.split("-");
    startDate = myNodes[counter].Sensors[counter2].StartDate.split("-");
    endDate1 = $('#end').val();    
    if (compareDates($('#start').val(), myNodes[counter].Sensors[counter2].StartDate))
    {
        if (compareDates(endDate1, myNodes[counter].Sensors[counter2].StartDate)) {
            //if we do not do this we cannot set start date, because end would be smaller than start
            $('#end').datepicker('setDate', new Date(startDate[0], startDate[1] - 1, startDate[2] + 1));
        }
        $('#start').datepicker('setDate', new Date(startDate[0], startDate[1] - 1, startDate[2]));
    }
    if (compareDates(myNodes[counter].Sensors[counter2].EndDate, endDate1))
    {
        $('#end').datepicker('setDate', new Date(endDate[0], endDate[1] - 1, endDate[2]));
    }
    //??? why doesnt setting min/max work?
    //$('#start').datepicker('option', {'minDate': new Date(startDate[0], startDate[1]-1, startDate[2]), 'maxDate': new Date(endDate[0], endDate[1] - 1, endDate[2])});
    //$('#end').datepicker('option', {'minDate': new Date(startDate[0], startDate[1]-1, startDate[2]), 'maxDate': new Date(endDate[0], endDate[1] - 1, endDate[2])});
    if (!compareDates(endDate, startDate) && !compareDates(startDate, endDate)) alert("Date interval is uncompatible with selected data.");
    //??? preverjaj ce je interval prazen, preden ga nastavis
}

function compareDates(date1, date2) {
    //returns true if date1 is before date2 and false otherwise
    date1 = date1.split("-");
    date2 = date2.split("-");
    if (date1[0] < date2[0]) {
        return true;
    }
    else {
        return false;
    }
    if (date1[1] < date2[1]) {
        return true;
    }
    else {
        return false;
    }
    return date1[2] < date2[2];
}

var handleSelectpicker = function () {
    $('.selectpicker').selectpicker('render');
};

function updateSensors(sensors) {
    // loading sensors into html
	var str;
    console.log("Number of sensors: "+sensors.length);
    for (i = 0; i < sensors.length; i++) { 
        if (sensors[i].search("Light") > 0) 
            $("<option value=\"" + sensors[i] + "\">" + sensors[i] + "</option>").appendTo("#ch-sensor");
        if (sensors[i].search("cloudCover") > 0) 
            $("<option value=\"" + sensors[i] + "\">" + sensors[i] + "</option>").appendTo("#ch-sensor-cloudcover");
        if (sensors[i].search("Tripped") > 0) 
            $("<option value=\"" + sensors[i] + "\">" + sensors[i] + "</option>").appendTo("#ch-sensor-presence");
        
    }
    
    // handle selected options
    $("#ch-sensor").val("Office1-Light");
    $("#ch-sensor-presence").val("Office1-Occupancy-Tripped");
    
    
    handleSelectpicker();
    $('#loading1').text('Data type');
};

function correctDate(date) {
    //corrects date because it is in wrong format
    date = date.split("-");
    if (date.length != 3) {
        console.log("ERROR: provided date is in wrong format.")
    }
    return date[2] + "-" + date[1] + "-" + date[0];
};

function correctTimeInterval(ti) {
    //time intervals map: Raw -> Raw, 1h -> 1h, 6h -> 6h, 1 day -> 1d, 1 week -> 1w, 1 month -> 1m, 1 year -> 1y
    ti = ti.split(" ");
    if (ti.length < 2) {
        return ti[0];
    }
    else {
        return ti[0] + ti[1][0];
    }
}

function dateDiff(d1, d2) {
    //in days, d1 must be before d2, month = 30 days, year = 365 days
    minusOne = false
    if (d2[2] - d1[2] >= 0) {
        daysDiff = d2[2] - d1[2];
        minusOne = false;
    }
    else {
        daysDiff = 30 - d1[2] - (-d2[2]);
        minusOne = true;
    }
    if (d2[1] - d1[1] >= 0) {
        monthsDiff = d2[1] - d1[1];
        if (minusOne) monthsDiff -= 1;
        minusOne = false;
    }
    else {
        monthsDiff = 12 - d1[1] - (-d2[1]);
        if (minusOne) monthsDiff -= 1;
        minusOne = true;
    }
    yearsDiff = d2[0] - d1[0];
    if (minusOne) yearsDiff -= 1;
    diff = 365 * yearsDiff - (-30 * monthsDiff - daysDiff);
    if (diff < 0) {
        console.log("ERROR date diff not supposed to be < 0.");
    }
    return diff;
}


/**
 * Model calculation object; calculates model values for different scenarios
 */
function Model() {
    
    this.series = [];
    this.seriesN = [];
    this.lat = 48.1636;
    this.lng = 16.3377;
    this.dataType;
    
    var md = this;

    this.calculate = function(type) {
        // cleanup from previous calculations
        $("#container1").html("");
        $("#container-results").html("");
        $("#container-chart1").html("");
        this.series = [];
        this.seriesN = [];
        
        if (type == "lr") alert("Not yet implemented!");        

        // sunrise: sunrise - goldenHourEnd
        // day: goldenHourEnd - goldenHour
        // sunset: goldenHour - sunset
        // night: sunset - sunrise

        var startDate = $("#start").val();
        var endDate = $("#end").val();
        console.log(startDate);

        var HTML = this.generateSunTable("Start", startDate, this.lat, this.lng);    
        HTML += this.generateSunTable("End", endDate, this.lat, this.lng);

        $("#container1").append(HTML);
        
        // loading data series
        // sensor
        this.dataType = $('#ch-sensor option:selected').val();
        this.dataTypeCloudcover = $('#ch-sensor-cloudcover option:selected').val();
        this.dataTypePresence = $('#ch-sensor-presence option:selected').val();
        
        this.addSeries(this.dataType, startDate, endDate, 0);
        this.addSeries(this.dataTypeCloudcover, startDate, endDate, 1);
        this.addSeries(this.dataTypePresence, startDate, endDate, 2);                
    }

    this.generateSunTable = function(preString, myDate, lat, lng) {
        var jDate = Date.createFromMysql(myDate);

        var times = SunCalc.getTimes(jDate, lat, lng)
        var HTML;

        HTML = "<h4>" + preString + ": " + myDate + "</h4>";
        HTML += "<ul>";
        HTML += "  <li><b>Sunrise:</b> " + times.sunrise;
        HTML += "  <li><b>Morning twilight:</b> " + times.goldenHourEnd;
        HTML += "  <li><b>Evening twilight:</b> " + times.goldenHour;
        HTML += "  <li><b>Sunset:</b> " + times.sunset;
        HTML += "</ul>";

        return HTML;

    }

        /**
     * Starting new series load for infobox
     */
    this.addSeries = function (dataType, dateStart, dateEnd, seriesNum) {
        // collecting data        
        this.dataType = dataType;
        this.dateStart = dateStart;
        this.dateEnd = dateEnd;
        this.seriesNum = seriesNum;
        
        // setting object
        var md = this;
        
        
        var myUrl;		
        myUrl = '/api/get-measurements?p=' + escape(this.dataType) + ':' + this.dateStart + ':' + this.dateEnd;			
		console.log(myUrl);
        // making a copy of this object, so that we preserve data within multiple async calls
        var contextThis = jQuery.extend({}, this);
        console.log(contextThis);
        $.ajax({
            url: myUrl,
            success: md.loadedSeries,
            context: contextThis,
			error: function (x, y, z) {console.log(y);}
        });
    };
    
    /**
     * Starting new series load for infobox
     */
    this.loadedSeries = function(data) {
        data = JSON.parse(data);
        this.series[this.series.length] = data;
        this.seriesN[this.series.length - 1] = this.seriesNum;
        
        var md = this;
        
        // is all the data loaded?
        if (this.series.length == 3) {
            this.handleChart(this);
            this.calculateTable();
            console.log(this.seriesN);
        }
    };
    
    /**
     * Calculating lighting model table
     */
    this.calculateTable = function() {
        // presence = 0
        
        // ----------------------------------------------
        //         | 0-20%  | 20-80%        | 80-100%   |
        //         | clear  | light cloudy  | cloudy    |
        // ----------------------------------------------
        // sunrise |        |               |           |
        // day     |        |               |           |
        // sunset  |        |               |           |
        // night   |        |               |           |
        // ----------------------------------------------
        
        // define variables for series
        var lightSeries;
        var cloudcoverSeries;
        var presenceSeries;
        
        var avg = new Array(3 * 4).fill(0);
        var num = new Array(3 * 4).fill(0);       
        
        // handle which series is which
        for (var i = 0; i < 3; i++) {
            if (this.seriesN[i] == 0) lightSeries = this.series[i];
            if (this.seriesN[i] == 1) cloudcoverSeries = this.series[i];
            if (this.seriesN[i] == 2) presenceSeries = this.series[i];
        }
        
        // transverse lightSeries
        
        var ci = 0;
        var pi = 0;
        
        var badDataPoints = 0;
        
        for (var i = 0; i < lightSeries.length; i++) {        
            
            var validDataPoint = true;
            // find cloudcover and presence
            while ((cloudcoverSeries[ci].Timestamp <= lightSeries[i].Timestamp) && (ci < cloudcoverSeries.length - 1)) {   
                ci++;                                
            }
            if (ci == 0) validDataPoint = false;
            ci--;
            
            while ((presenceSeries[pi].Timestamp <= lightSeries[i].Timestamp) && (pi < presenceSeries.length - 1)) {                
                pi++;                   
            }
            if (pi == 0) validDataPoint = false;
            pi--;
            
            // console.log(pi, presenceSeries.length);
            // making sure, we are not returning errors if metadata does not exist for
            // real data
            if (pi == -1) pi = 0;
            if (ci == -1) ci = 0;
            
            
            // convert timestamps to JS timestamps
            lTs = Date.createFromMysql(lightSeries[i].Timestamp);
            pTs = Date.createFromMysql(presenceSeries[pi].Timestamp);
            cTs = Date.createFromMysql(cloudcoverSeries[ci].Timestamp);
            
            lV = lightSeries[i].Val;
            pV = presenceSeries[pi].Val;
            cV = cloudcoverSeries[ci].Val;
            
            // check timestamps
            dPL = Math.abs(pTs - lTs) / 1000 / 60 / 60;
            dCL = Math.abs(cTs - lTs) / 1000 / 60 / 60;
            
            if ((dPL > 1) || (dCL > 1)) validDataPoint = false;
            
            // fill in the table
            if (validDataPoint) {
                // handle cloudcover
                var cBin = 2;                
                if (cV < 0.20) cBin = 0;
                else if (cV < 0.70) cBin = 1;                
                
                // handle time                
                var times = SunCalc.getTimes(lTs, this.lat, this.lng)
                // console.log(lTs, times.sunrise, times.goldenHourEnd, times.goldenHour, times.sunset);
        
                var tBin = 3;
                if (lTs < times.sunrise) tBin = 3;              // night
                else if (lTs < times.goldenHourEnd) tBin = 0;   // morning
                else if (lTs < times.goldenHour) tBin = 1;          // day
                else if (lTs < times.sunset) tBin = 2;      // evening
                    
                var tIndex = cBin + tBin * 3;
                
                // update numerus
                num[tIndex]++;
                // update average
                avg[tIndex] = (avg[tIndex] * (num[tIndex] - 1) + lV)/num[tIndex];                
            } else {
                badDataPoints++;
            }            
        }
        
        // generate table
        var title = ["morning", "day", "evening", "night"];
        var HTML = "<table class='table'>";
        // add header
        HTML += "<thead><tr><td></td><td><b>clear</b></td><td><b>partly cloudy</b></td><td><b>cloudy</b></td></tr></thead>";
        HTML += "<tbody>";
        for (var j = 0; j < 4; j++) {
            HTML += "<tr>"
            for (var i = 0; i < 3; i++) {
                if (i == 0) HTML += "<td><b>" + title[j] + "</b></td>"
                var tIndex = j * 3 + i;
                HTML += "<td>";
                HTML += avg[tIndex].toFixed(2) + " (" + num[tIndex] + ")";
                HTML += "</td>";
            }
            HTML += "</tr>";
        }
        HTML += "</tbody></table>";
        
        HTML += "Bad/all data points: " + badDataPoints + "/" + lightSeries.length + " (" + Math.round(badDataPoints/lightSeries.length * 10000)/100 + "%)";        
        
        $("#container-results").append(HTML);
    };
    
    /**
     * Chart the data
     */
    this.handleChart = function() {
        var hc = new HighChart("container-chart1", 0, systemNodes);
        // lets not worry about the context ... 
        this.dataType = $('#ch-sensor option:selected').val();
        this.dataTypeCloudcover = $('#ch-sensor-cloudcover option:selected').val();
        this.dataTypePresence = $('#ch-sensor-presence option:selected').val();
        
        var typeArray = [this.dataType, this.dataTypeCloudcover, this.dataTypePresence];
        console.log("handleChart");
        console.log(this.seriesN);
        console.log(typeArray);
        console.log(this.series);        
        hc.loadedAggregates(JSON.stringify(this.series[0]), typeArray[this.seriesN[0]], "raw", "raw");
        hc.loadedAggregates(JSON.stringify(this.series[1]), typeArray[this.seriesN[1]], "raw", "raw");
        hc.loadedAggregates(JSON.stringify(this.series[2]), typeArray[this.seriesN[2]], "raw", "raw");
    };
    
}


/**
 * Highchart object - graph and data management for model calculation
 */
function HighChart(container, chartNumber, systemNodes) {
    //chart class, draws charts
    this.data = [];
    this.timeInterval;
    this.dateStart;
    this.dateEnd;
    this.aggregateType;
    this.dataType;
    this.raw;        
    this.systemNodes = systemNodes;        

    this.newChart = function(container) {
        //inits an empty highchart
        return new Highcharts.Chart({
            chart: {
                zoomType: 'x',
                renderTo: container,
                height: '500'
            },
            title: {
                text: ''
            },
            subtitle: {
                text: document.ontouchstart === undefined ?
                        'Click and drag in the plot area to zoom in' :
                        'Pinch the chart to zoom in'
            },
            xAxis: {
                type: 'datetime',
            },
            legend: {
                enabled: true
            },
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                        ]
                    },
                    marker: {
                        radius: 2
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: null
                }
            }
        });
    }
    
    this.chart = this.newChart(container);
    this.chart.yAxis[0].remove();
    
    this.addSeries = function (dataType, dateStart, dateEnd, aggregateType, timeInterval, lineConf) {
        // collecting data from form        
        $('#loadingChart' + this.chartNumber).text('Adding new series ...');
        this.dataType = dataType;
        this.dateStart = dateStart;
        this.dateEnd = dateEnd;
        this.aggregateType = aggregateType;
        this.timeInterval = timeInterval;  
        this.lineConf = lineConf;
        
        if (this.timeInterval == "raw") {
            if (dateDiff(this.dateStart.split("-"), this.dateEnd.split("-")) > 500) {
                alert("Too much data. Please select a smaller interval.");
                return;
            }
            this.raw = "Yes";
        }
        else {
            if (this.timeInterval == "1h") {
                if (dateDiff(this.dateStart.split("-"), this.dateEnd.split("-")) > 62) {
                    alert("Too much data. Please select a smaller interval (less than 2 months), or a bigger sampling interval.");
                    $('#loadingChart' + this.chartNumber).text('Chart');
                    return;
                }
            }
            else if (this.timeInterval == "6h") {
                if (dateDiff(this.dateStart.split("-"), this.dateEnd.split("-")) > 300) {
                    alert("Too much data. Please select a smaller interval (less than 7 months), or a bigger sampling interval.");
                    $('#loadingChart' + this.chartNumber).text('Chart');
                    return;
                }
            }
            else if (this.timeInterval == "1d") {
                if (dateDiff(this.dateStart.split("-"), this.dateEnd.split("-")) > 1000) {
                    alert("Too much data. Please select a smaller interval (less than 2.5 years), or a bigger sampling interval.");
                    $('#loadingChart' + this.chartNumber).text('Chart');
                    return;
                }
            }
            else if (this.timeInterval == "1w") {
                if (dateDiff(this.dateStart.split("-"), this.dateEnd.split("-")) > 4000) {
                    alert("Too much data. Please select a smaller interval (less than 10 years), or a bigger sampling interval.");
                    $('#loadingChart' + this.chartNumber).text('Chart');
                    return;
                }
            }
            else if (this.timeInterval == "1m") {
                if (dateDiff(this.dateStart.split("-"), this.dateEnd.split("-")) > 13000) {
                    alert("Too much data. Please select a smaller interval (less than 30 years), or a bigger sampling interval.");
                    $('#loadingChart' + this.chartNumber).text('Chart');
                    return;
                }
            }
            else if (this.timeInterval == "1y") {
                if (dateDiff(this.dateStart.split("-"), this.dateEnd.split("-")) > 100000) {
                    alert("Too much data. Please select a smaller interval (less than 200 years), or a bigger sampling interval.");
                    $('#loadingChart' + this.chartNumber).text('Chart');
                    return;
                }
            }
            this.raw = "No";
        }
        var myUrl;
		
		if (this.aggregateType == "prediction") {
			if (typeof this[this.dataType] == 'undefined') {
				alert("Prediction not available for this sensor.");
			}
			else {
				myUrl = "/proxy.php?cmd=/AggregateService/services/prediction-api/get-predictions?p=" + this.dataType + ":" + this[this.dataType] + ":"+ this.dateStart + ":" + this.dateEnd;
			}
		}
        else if (this.raw == "No") {
            myUrl = '/api/get-aggregates?p=' + escape(this.dataType) + ':' + this.aggregateType + ':' + this.timeInterval + ':' + this.dateStart + ':' + this.dateEnd;
        }
        else if (this.raw == "Yes") {
            myUrl = '/api/get-measurements?p=' + escape(this.dataType) + ':' + this.dateStart + ':' + this.dateEnd;			
        }
        else {
            console.log("ERROR in checking equality!")
        }
		console.log(myUrl);
        // making a copy of this object, so that we preserve data within multiple async calls
        var contextThis = jQuery.extend({}, this);
        $.ajax({
            url: myUrl,
            success: function (data1) { this.loadedAggregates(data1, this.dataType, this.aggregateType, this.timeInterval) },
            context: contextThis,
			error: function (x, y, z) {console.log(y);}
        });
    };
    
    this.loadedAggregates = function (data1, dataType, aggregateType, timeInterval) {        
        //extracting info about data
		data1 = JSON.parse(data1);
		if (!(typeof data1[0] == 'undefined') && typeof data1[0]["Val"] == 'undefined') {
			var data2 = [];
			for (var i = 0; i < data1.length; i++) {
				data2.push({"Val" : data1[i]["value"], "Timestamp" : data1[i]["timestamp"]});
			}
			// console.log(data2);
		}
		else {
			data2 = data1;
		}
		//console.log("TUTU: ", data2);
        datayDescription = "N/A";
        datayUnit = "N/A";
        
        console.log(this.systemNodes);
        
        if (this.systemNodes.sensorTable[dataType]) {
            found = true;
            /*
            counter = namesToLocations[dataType].fst;
            counter2 = namesToLocations[dataType].snd;
            datayDescription = myNodes[counter].Sensors[counter2].Phenomenon;
            datayUnit = myNodes[counter].Sensors[counter2].UoM;
            */
            datayDescription = this.systemNodes.sensorTable[dataType].Phenomenon;
            datayUnit = unescape(this.systemNodes.sensorTable[dataType].UoM);
        }
        else {
            found = false;
        }

        if (found == false) {
            console.log("ERROR: Data info not found in nodes.");
        }
        if (datayDescription == "" || datayDescription == "-") datayDescription = "N/A";
        if (datayUnit == "" || datayUnit == "-") datayUnit = "N/A";

        //checking if additional y axis needed because unit does not exist yet or scales are too different
        //find all y axis with same unit
        var axisData = [];
        for (i = 0; i < this.chart.yAxis.length; i++) {
            if (this.chart.options.yAxis[i].title.text == datayUnit) {
                axisData[axisData.length] = {
                    "index": i,
                    "min": this.chart.options.yAxis[i].min,
                    "max": this.chart.options.yAxis[i].max
                };
            }
        }
        //outputing collected data (& after finding min/max deciding if adding new axis)

        console.log("Added data length: " + data2.length);
        this.data[this.data.length] = data2;
        
        var data1 = [];
        var date;
        var timedate;
        var min = Infinity;
        var max = -Infinity;
        for (j = 0; j < data2.length; j++) {
            timedate = data2[j].Timestamp.split("T");
            date = timedate[0].split("-");
            time = timedate[1].split(":");
			if (typeof time[2] == 'undefined') { time[2] = 0; }
            data1[data1.length] = [Date.UTC(date[0], date[1] - 1, date[2], time[0], time[1], time[2]), data2[j].Val]
            if (data2[j].Val < min) {
                min = data2[j].Val;
            }
            else if (data2[j].Val > max) {
                max = data2[j].Val;
            }
        }
		
        //adding y axis if needed
        addedNumber = ""; //number added to axis id so we can seperate axis with same unit
        i = 0;
        axisIndex = -1;
        addAxis = true;
        while (addAxis && i < axisData.length) {
            // /3 gives us effectively maximum 6x the scale size (but only maximum 3x up and 3x down)
            if (axisData[i].max - axisData[i].min > Math.abs(axisData[i].max - max) / 3 &&
                axisData[i].max - axisData[i].min > Math.abs(axisData[i].min - min) / 3  ) {
                //in this case we already have an appropriate axis
                addAxis = false;
                axisIndex = axisData[i].index;
            }
            i++;
        }
        newMin = min - (max - min) * 0.1;
        newMax = max + (max - min) * 0.1;
        if (addAxis) {
            console.log("Adding new axis: " + datayUnit + addedNumber + ".")
            if (axisData.length != 0) {
                addedNumber = axisData.length;
            }
            var axisConf = {
                id: datayUnit + addedNumber,
                title: {
                    text: datayUnit + addedNumber
                },                
                min: newMin,
                max: newMax
            };
                        
            if (this.lineConf !== undefined) {
                var plotLines = [{
                    value: this.lineConf.value,
                    color: this.lineConf.color,
                    width: this.lineConf.width,
                    label: {
                        text: this.lineConf.title,
                        align: 'center',
                        style: {
                            color: 'gray'
                        }
                    }
                }];
            }
            
            axisConf.plotLines = plotLines;
            
            this.chart.addAxis(axisConf);
        }
        else {
            if (i - 1 != 0) {
                addedNumber = i - 1; //i-1 is where we found the right axis to add data on
            }
        }
        //adding data
        $('#loadingChart' + chartNumber).text('Rendering chart ...');
        this.chart.addSeries({
            type: 'line',
            data: data1,
            name: dataType + "(" + timeInterval + "-" + aggregateType + ")" + ": " + datayDescription + " - " + datayUnit + addedNumber,
            yAxis: datayUnit + addedNumber,
        });
        if (axisIndex != -1) {
            this.chart.options.yAxis[axisIndex].min = Math.min(newMin, this.chart.options.yAxis[axisIndex].min);
            this.chart.options.yAxis[axisIndex].max = Math.max(newMax, this.chart.options.yAxis[axisIndex].max);
            this.chart.yAxis[axisIndex].min = Math.min(newMin, this.chart.options.yAxis[axisIndex].min);
            this.chart.yAxis[axisIndex].max = Math.max(newMax, this.chart.options.yAxis[axisIndex].max);
            this.chart.yAxis[axisIndex].options.startOnTick = true;
            this.chart.yAxis[axisIndex].options.endOnTick = true;
            this.chart.yAxis[axisIndex].setExtremes(this.chart.options.yAxis[axisIndex].min, this.chart.options.yAxis[axisIndex].max);            
        }
        
        this.chart.redraw();
        $('#loadingChart' + chartNumber).text('Chart');
        console.log("Added series: " + dataType + "(" + timeInterval + "-" + aggregateType + ")" + ": " + datayDescription + " - " + datayUnit + addedNumber + ".");
    };
};

/**
 * Object with Node/Sensor configuration.
 */
function SystemNodes() {
    this.sensorTable = [];
    
    var sn = this;
    
    this.init = function(callback) {        
        $.ajax({
            url: '/api/get-nodes',
            success: this.loadedNodes,            
			error: function (x, y, z) {console.log(y);},
            complete: callback
        });
    }
    
    this.loadedNodes = function(data) {
        data = JSON.parse(data);
        // make inverse sensor table
        $.each(data, function(did, node) {
            $.each(node["Sensors"], function(sid, sensor) {
                var sensorData = {};
                sensorData = sensor;
                sensorData["UoM"] = sensorData["UoM"].replace("deg+C", "&deg;C");
                sn.sensorTable[sensor.Name] = sensorData;
            });
        })
    }
}



/**
 * Main part of the code.
 */
var model; // create global model variable
var systemNodes; // create global systemNodes variable

$(document).ready(function() {
    systemNodes = new SystemNodes();
    systemNodes.init();
    model = new Model();    
})