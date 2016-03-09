$(function () {
    //init-ing and setting a few starting variables    
    myAvailabilityChart = new AvailabilityChart();
});

$.ajax({
    //getting information about nodes
    url: '/api/get-nodes',
    success: loadedNodes
});

function loadedNodes(data) {
    //saving nodes ...
    myAvailabilityChart.nodes = JSON.parse(data);
    myAvailabilityChart.draw();
}

function AvailabilityChart() {
    this.nodes = [];
	
    this.draw = function() {
    	calcHeight = 100;
    	console.log(calcHeight);
    	
        var settings = {

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
    	}
    	
    	console.log(myData);
    	settings.chart.height = calcHeight;
    	
    	settings.xAxis = { opposite: true, categories: sensors };
    	
    	data = {
    	    name: 'Availability', 
            data: myData
        }
        settings.series.push(data);
    	
    	// fill series
    	/*
    	{
                name: 'Temperatures',
                data: [
                    [Date.UTC(1971,  5, 12), Date.UTC(1971,  6, 12)],
                    [Date.UTC(1971,  5, 30), Date.UTC(1971,  12, 12)]
                ]
            }
            */
        
        console.log("Done");
        console.log(settings);
        $('#container').highcharts(settings);
        console.log("Done2");
    

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