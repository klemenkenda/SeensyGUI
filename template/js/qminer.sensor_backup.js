$.ajax({
    url: '/api/get-sensors',
    success: loadedSensors
});

function loadedSensors(data) {
    // check errors
    var sensors = JSON.parse(data);
    var str;
    console.log("Number of sensors: "+sensors.length);
    for (i = 0; i < sensors.length; i++) {
        str = sensors[i].str;
        if (str.indexOf("alarm") == -1) $("<option value=\"" + sensors[i].str + "\">" + str + "</option>").appendTo("#ch-sensor");
    }
    handleSelectpicker();
};

var handleSelectpicker = function () {
    $('.selectpicker').selectpicker('render');
};

$(function () {
    $('#datepicker-autoClose').datepicker();
    $('#datepicker-autoClose1').datepicker();
    $('#datepicker').datepicker({
        forceParse: false,
        autoclose: true
    });
    $('#start').datepicker("setDate", "01/09/2014");
    $('#end').datepicker("setDate", "02/09/2014");
    myChart = new Chart();
});

function Chart() {
    this.data = [];
    this.timeInterval;
    this.dateStart;
    this.dateEnd;
    this.aggregateType;
    this.dataType;
    this.raw;

    this.showChart = function () {
        drawHighChart(this.data);
    };
    this.deleteLastSeries = function () {
        sth = this.data.pop();
        if (sth != null) {
            console.log("Removed last added series.");
        }
        else {
            console.log("No series left to remove.");
        }
    };
    this.addSeries = function () {
        this.timeInterval = $('#time-interval option:selected').val();
        this.dateStart = correctDate($('#start').val());
        this.dateEnd = correctDate($('#end').val());
        this.aggregateType = $('#aggregate-type option:selected').val().toLowerCase();
        this.dataType = $('#ch-sensor option:selected').val();
        this.raw = $('#raw option:selected').val();
        var myUrl;
        if (this.raw == "No") {
            myUrl = '/api/get-aggregates?p=' + escape(this.dataType) + ':' + this.aggregateType + ':' + this.timeInterval + ':' + this.dateStart + ':' + this.dateEnd;
        }
        else if (this.raw == "Yes"){
            myUrl = '/api/get-measurements?p=' + escape(this.dataType) + ':' + this.dateStart + ':' + this.dateEnd;
        }
        else {
            console.log("ERROR in checking equality!")
        }
        $.ajax({
            url: myUrl,
            success: this.loadedAggregates,
            context: this
        });
    };
    this.loadedAggregates = function (data1) {
        var data2 = JSON.parse(data1);
        console.log("Added data length: " + data2.length);
        this.data[this.data.length] = data2;
    };
    
};

function correctDate(date) {
    date = date.split("/");
    if (date.length != 3) {
        console.log("ERROR: provided date is in wrong format.")
    }
    return date[2] + "-" + date[0] + "-" + date[1];
};

function drawHighChart(data) {
    var data1;
    var data2;
    var date;
    var timedate;
    var highchart = new Highcharts.Chart({
        chart: {
            zoomType: 'x',
            renderTo: 'container'
        },
        title: {
            text: 'Showing added series'
        },
        subtitle: {
            text: document.ontouchstart === undefined ?
                    'Click and drag in the plot area to zoom in' :
                    'Pinch the chart to zoom in'
        },
        xAxis: {
            type: 'datetime',
        },
        yAxis: {
            title: {
                text: 'Values'
            }
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
    console.log(data.length);
    for (i = 0; i < data.length; i++) {
        console.log("Here!");
        data1 = data[i];
        data2 = [];
        
        for (j = 0; j < data1.length; j++){
            timedate = data1[j].Timestamp.split("T");
            date = timedate[0].split("-");
            data2[data2.length] = [Date.UTC(date[0],date[1]-1,date[2]), data1[j].Val]
        }
        
        highchart.addSeries({
            type: 'line',
            data: data2
            // name: data[i][0], ???dodaj ime, pazi ker so requesti asinhroni, treba bo podati imena kot argumente
        });
        console.log("Adding series.");
    };
    highchart.redraw();
};