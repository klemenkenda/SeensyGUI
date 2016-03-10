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
            t = mysql_string.split(/[- :]/);

            //when t[3], t[4] and t[5] are missing they defaults to zero
            result = new Date(t[0], t[1] - 1, t[2], t[3] || 0, t[4] || 0, t[5] || 0);
        }

        return result;
    }
})();


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
 * Object with Node/Sensor configuration.
 */
function SystemNodes() {
    this.sensorTable = [];
    
    var sn = this;
    
    this.init = function(callback) {        
        $.ajax({
            url: '/api/get-nodes',
            success: this.loadedNodes,            
			error: function (x, y, z) {alert(y);},
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
 * Predefined visualization object; visualizes predefined timeseries or numeric data
 */

function PredefinedVisualization(systemNodes) {
    this.config;
    this.chartNumber = 0;
    this.infoBoxNumber = 0;
    this.charts = [];
    this.infoBoxes = [];
    this.systemNodes = systemNodes;
    
    var pv = this;
    
    /**
     * Add new chart
     *
     * @param columnid {string} HTML id of the column, where we are adding the chart to.
     * @param chartTitle {string} HTML DIV id for the container of the highchart
     */
    this.addNewChart = function(columnid, chartTitle) {   
    
        this.chartNumber = this.charts.length + 1;
        $("#" + columnid).append(            
            '<div class="panel panel-inverse"> ' +
                '<div class="panel-heading ui-sortable"> ' +
                    '<div class="panel-heading-btn"> ' +
                        '<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"> <i class="fa fa-expand"> </i></a> ' +
                        '<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-success" data-click="panel-reload"> <i class="fa fa-repeat"> </i> </a> ' +
                        '<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-warning" data-click="panel-collapse"> <i class="fa fa-minus"> </i> </a> ' +
                        '<!-- ??? Catch remove and update counter/chart list. -->' +
                        '<a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-danger" data-click="panel-remove"><i class="fa fa-times"></i></a> ' +
                    '</div> ' +
                    '<h4 class="panel-title" id="loadingChart"' + this.chartNumber + ' class=>' + chartTitle + '</h4>' +
                '</div> ' +
                '<div class="panel-body"> ' +
                    '<form class="form-horizontal form-bordered"> ' +
                        '<div id="container'+ this.chartNumber + '" class="panel-body chart-container"> </div> ' +
                    '</form> ' +
                '</div> ' +                
            '</div>'
        );
        
        $('#ucontainer' + this.chartNumber).trigger('create');
        this.charts[this.chartNumber - 1] = new HighChart("container" + this.chartNumber, this.chartNumber, this.systemNodes);
        // $('#ucontainer' + this.chartNumber).render();
    };
    
    
    
    this.addNewInfobox = function(columnid, title, color, icon, value, status, link, infoBoxConfig) {
        this.infoBoxNumber = this.infoBoxes.length + 1;
        $("#" +  columnid).append(
            '<div class="widget widget-stats bg-' + color + '" id="widget-' + this.infoBoxNumber + '">' +
                '<div class="stats-icon"><i class="' + icon + '"></i></div>' +
                '<div class="stats-info">' +
                    '<h4>' + title + '</h4>' +
                    '<p>' + value + '</p>' +
                '</div>' +
                '<div class="stats-link">' +
                    '<a href="' +  link + '">' + status + "</a>" +
                '</div>' +
            '</div>'
        );
        this.infoBoxes[this.infoBoxNumber - 1] = new InfoBox("widget-" + this.infoBoxNumber, this.systemNodes, infoBoxConfig);
    };
    
    /**
     * Initialize predefined visualization widgets
     *
     * @param config {model:predefined~Config} Configuration data for the predefined visualization page
     */
    this.init = function(config) {
        this.config = config;
        
        // traverse config structure, create rows, columns, charts and load the data to the appropriate chart
        $.each(this.config, function(rid, row) {
            // create row
            $("#content").append('<div class="row" id="row-' + rid + '"></div>');            
            
            // traverse over columns
            $.each(row["columns"], function (cid, col) {
                // create column
                $("#row-" + rid).append('<div class="' + col["class"] + '" id="col-' + rid + '-' + cid + '"></div>');
                
                // traverse over charts
                if (col["charts"]) {
                    $.each(col["charts"], function(chid, chart) {
                        // create chart
                        pv.addNewChart("col-" + rid + "-" + cid, chart["title"]);
                        // set chart dates                        
                        if (chart["endDate"] == "now") var endDate = new Date();
                        else if (chart["endDate"] == "last") /* TODO */;
                        else {
                            var endDate = Date.createFromMysql(chart["endDate"]);
                        }
                        // correct date for QMiner limits
                        endDate.setDate(endDate.getDate() +  1);
                        var startDate = new Date(endDate.getTime());
                        startDate.setDate(endDate.getDate() - chart["timeSpan"]);                    

                        // load time series
                        $.each(chart["series"], function(seriesid, series) {
                            console.log(series);
                            pv.charts[pv.chartNumber - 1].addSeries(series["sensorId"], startDate.toYMD(), endDate.toYMD(), series["aggregate"], series["window"]);
                        });
                    });
                }
                // traverse over infoboxes
                else if (col["infoboxes"]) {
                    $.each(col["infoboxes"], function (iid, infobox) {
                        title = infobox["title"];
                        color = infobox["color"];
                        icon = infobox["icon"];                        
                        status = infobox["status"];
                        link = infobox["link"];
                        value = "N/A";
                                                
                        
                        // can value be determined directly?
                        if (infobox["value"]["type"] == "lastValue") {
                            value = pv.systemNodes.sensorTable[infobox["value"]["sensorId"]]["Val"] + " " +
                                    pv.systemNodes.sensorTable[infobox["value"]["sensorId"]]["UoM"];
                            // adding new infoBox
                            pv.addNewInfobox("col-" + rid + "-" + cid, title, color, icon, value, status, link, infobox);
                        } 
                        // else we will need to load additional data
                        else { 
                            // adding new infoBox
                            pv.addNewInfobox("col-" + rid + "-" + cid, title, color, icon, value, status, link, infobox);
                            
                            if (infobox["value"]["endDate"] == "now") var endDate = new Date();
                            // correct date for QMiner limits
                            endDate.setDate(endDate.getDate() + 1);
                            var startDate = new Date();
                            startDate.setDate(endDate.getDate() - infobox["value"]["timeSpan"]);      
                            
                            $.each(infobox["value"]["serie2s"], function(isid, series) {
                                pv.infoBoxes[pv.infoBoxNumber - 1].addSeries(series["sensorId"], startDate.toYMD(), endDate.toYMD(), series["aggregate"], series["window"]);    
                            });                            
                        }                                                
                    });
                };
            });
        });        
    };        
};


/**
 * Infobox object - value 
 */
function InfoBox(containerId, systemNodes, infoBoxConfig) {
    this.systemNodes = systemNodes;
    this.config = infoBoxConfig;
    this.series = [];
    this.containerId = containerId;
    
    ib = this;
    
    /**
     * Transforming value according to the config
     */
    this.transform = function(value, unit, config) {
        /*
         * config parameters
         *   formula - formula for transformation (value = x)
         *   UoM - string for overriding unit value
         *   decimals - number of decimal places (usitn toFixed(decimals))
         */
        
        console.log(config);
        
        // transform value
        if (config["formula"]) {
            var formula = config["formula"];
            formula = formula.replace("x", value);
            value = eval(formula);
        }
        
        // round value
        if (config["decimals"]) {
            var n = config["decimals"];
            value = value.toFixed(n);
        }
        
        // make correct unit of measurement
        if (config["uom"]) unit = config["uom"];
        
        // return results
        return value + " " + unit;        
    }
    
    /**
     * Starting new series load for infobox
     */
    this.addSeries = function (dataType, dateStart, dateEnd, aggregateType, timeInterval) {
        // collecting data        
        this.dataType = dataType;
        this.dateStart = dateStart;
        this.dateEnd = dateEnd;
        this.aggregateType = aggregateType;
        this.timeInterval = timeInterval;     
        
        // setting object
        var ib = this;
        
        
        if (this.timeInterval == "raw") {            
            this.raw = "Yes";
        } else {            
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
        console.log(contextThis);
        $.ajax({
            url: myUrl,
            success: ib.loadedSeries,
            context: contextThis,
			error: function (x, y, z) {alert(y);}
        });
    };
    
    /**
     * Starting new series load for infobox
     */
    this.loadedSeries = function(data) {
        data = JSON.parse(data);
        this.series[this.series.length] = data;
        
        // is all the data loaded?
        if (this.series.length == this.config.value.series.length) {
            switch (this.config.value.type) {
                case "tsValue":
                    // default is now
                    var ts = new Date();
                    var bestValue = 0;
                    var bestDist = Infinity;
                    
                    // simple linear search for best match
                    $.each(this.series[0], function(sid, point) {
                        pTs = new Date(point.Timestamp);
                        var dist = Math.abs((pTs - ts)/1000/60/60);
                        if (dist < bestDist) {
                            bestDist = dist;
                            bestValue = point.Val;
                        }
                    });
                    
                    bestValue = this.transform(bestValue, this.systemNodes.sensorTable[this.config["value"]["series"][0]["sensorId"]]["UoM"], this.config.value )
                                        
                    $("#" + containerId + " > .stats-info p").html(bestValue);
                    break;
                default: console.log("No method for extracting infobox value!");
            }
            
        };
    }
    
}


/**
 * Highchart object - graph and data management
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
    this.chartNumber = chartNumber;
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
            },
        });
    }
    
    this.chart = this.newChart(container);
    this.chart.yAxis[0].remove();
    
    this.deleteLastSeries = function () {
        $('#loadingChart' + this.chartNumber).text('Removing last added series ...');
        var titl;
        var titl1;
        //removing series
        if (this.chart.series.length > 0) {
            titl = this.chart.series[this.chart.series.length - 1].options.yAxis;
            titl1 = this.chart.series[this.chart.series.length - 1].options.name;
            this.chart.series[this.chart.series.length - 1].remove();
            console.log("Removed last added series: "+titl1+".");
        }
        else {
            console.log("No series left to remove.");
            alert("ERROR no series left to remove.");
        }
        //removing y axis if no longer needed
        found = false;
        counter = 0;
        while (!found && counter < this.chart.series.length) {
            if (this.chart.series[counter].options.yAxis == titl) {
                found = true;
            }
            counter++;
        }
        if (!found) {
            console.log("Removing unnecessary y axis: "+titl+".");
            found = false;
            counter = 1;
            while (!found && counter < this.chart.yAxis.length) {
                if (this.chart.options.yAxis[counter].id == titl) {
                    found = true;
                    this.chart.yAxis[counter].remove()
                }
                counter++;
            }
        }
        if (this.chart.series.length == 0 && this.chart.yAxis.length != 0) {
            this.chart.yAxis[0].remove();
        }
        $('#loadingChart' + this.chartNumber).text('Rendering chart ...');
        this.chart.redraw();
        $('#loadingChart' + this.chartNumber).text('Chart');
    };
    
    
    this.addSeries = function (dataType, dateStart, dateEnd, aggregateType, timeInterval) {
        // collecting data from form
        $('#loadingChart' + this.chartNumber).text('Adding new series ...');
        this.dataType = dataType;
        this.dateStart = dateStart;
        this.dateEnd = dateEnd;
        this.aggregateType = aggregateType;
        this.timeInterval = timeInterval;        
        
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
			error: function (x, y, z) {alert(y);}
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
            this.chart.addAxis({
                id: datayUnit + addedNumber,
                title: {
                    text: datayUnit + addedNumber
                },
                min: newMin,
                max: newMax
            });
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
            yAxis: datayUnit + addedNumber
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