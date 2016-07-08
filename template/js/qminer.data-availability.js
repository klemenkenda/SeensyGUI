$(function () {
    //init-ing and setting a few starting variables    
    myAvailabilityChart = new AvailabilityChart();
});

$.ajax({
    //getting information about nodes
    url: '/api/get-nodes',
    success: loadedNodes
});

function searchStringInArray (str, strArray) {
    for (var j=0; j<strArray.length; j++) {
        if (str.match(strArray[j])) return j;
        // if (strArray[j].match(str)) return j;
    }
    return -1;
}

function filterNodes(data) {
    console.log(data);
    var filtered = [];
    
    var filter = [ 'FIO-Kne', 'FIO-Krom', 'FIO-Raz', 'virtual', 'FeaturesNode', 'High Tech Campus-BUILDING-FeaturesObject.2', 'HTC-3', 'HTC-2', 'HTC-0', 'HTC-1', 'Office-' ];
    
    for (i in data) {
        var nodeName = data[i].Name;
        console.log(nodeName);
        if (searchStringInArray(nodeName, filter) == -1) filtered.push(data[i]);
    }
    
    return filtered;
}

function loadedNodes(data) {
    //saving nodes ...
    myAvailabilityChart.nodes = filterNodes(JSON.parse(data));    
    myAvailabilityChart.draw();
}

function AvailabilityChart() {
    this.nodes = [];
	
    this.draw = function() {
    	calcHeight = 100;
    	console.log(calcHeight);
    	
        var settingsOrig = {

            chart: {
                type: 'columnrange',
                inverted: true,
                height: calcHeight
            },

            xAxis: {},

            yAxis: {
                type: 'datetime',
                dateTimeLabelFormats: { // don't display the dummy year
                    // month: '%e. %b',
                    // year: '%b'
                },
                title: {
                    text: 'Date'
                },
                opposite: true
            },

            title: '',
            
            tooltip: {
                //headerFormat: '<b>{series.xAxis}</b><br>',
                pointFormat: '{point.low:%e. %B %Y}: {point.high: %e. %b. %Y}'
            }, 

            plotOptions: {
                columnrange: {
                    dataLabels: {
                        enabled: false,
                        formatter: function () {
                            return this.y + '°C';
                        }
                    }
                }
            },

            legend: {
                enabled: false
            }, 
            
            series: []

        };
      	// fill x-axis
    	// categories: ['Jan', 'Feb']
    	sensors = [];
    	myData = [];
    	var settings = [];
    	settings[0] = jQuery.extend(true, {}, settingsOrig);
    	var n = 0;
    	for (var i in this.nodes) {    	  
    	  
	    sensorsNode = this.nodes[i].Sensors;
	    for (var s in sensorsNode) {
		sensors.push(sensorsNode[s].Name);
		calcHeight += 20;
		if (sensorsNode[s].StartDate != '0000-00-00')  
		    myData.push([toUTCDate(sensorsNode[s].StartDate), toUTCDate(sensorsNode[s].EndDate)]);
		else
		    myData.push([]);
	    }
    	  
    	
    	    if (((Number(i) + 1) % 200 == 0) || (i == this.nodes.length - 1)) {
		// console.log(myData);
		console.log(n);
		settings[n].chart.height = calcHeight;
		
		settings[n].xAxis = { opposite: true, categories: sensors };
		
		data = {
		    name: 'Availability', 
		    data: myData
		}
		settings[n].series.push(data);
			
		$('#container' + n).highcharts(settings[n]);
		console.log("Done" + i);
		console.log(settings);
		// new one
		sensors = [];
		myData = [];
		n = Number(n) + 1;
		settings[n] = jQuery.extend(true, {}, settingsOrig);	
		calcHeight = 100;
		
		$(".panel-body form").append('<div class="panel-body" id="container' + n + '">');
		
	    }
        }

    }
    
    return this;
}

function toUTCDate(date) {
    date = date.split("-");
    if (date.length != 3) {
        console.log("ERROR: provided date is in wrong format.")
    }
    return Date.UTC(date[0], date[1] - 1, date[2]);
}