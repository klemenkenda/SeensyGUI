// main part of the program
$.ajax({
    //reading available
    url: '/api/get-alarms',
    success: loadedAlarms
});

function timeAgoFromMs(ts) {
    var tm = new Date;
    var now = tm.getTime();
    
    var diff = now - ts;
    var unit = "ms";
    
    if (diff > 1000) {
        diff = Math.round(diff / 1000);
        unit = "s";
   
    
        if (diff > 60) {
            diff = Math.round(diff / 60);
            unit = "min";
        

            if (diff > 60) {
                diff = Math.round(diff / 60);
                unit = "h";
         
                if (diff > 48) {
                    diff = Math.round(diff / 24);
                    unit = "d";
                    
                    if (diff > 14) {
                        diff = Math.round(diff / 7);
                        unit = "w";                            

                        if (diff > 8) {
                            diff = Math.round(diff / 4.34571428);
                            unit = " months";
                        }
                    }
                }
            }
        }
    }
    
    return diff + unit + " ago";
}

function loadedAlarms(data) {
    var alarms = JSON.parse(data);
    
    // MasterPing SWatchDog
    var mp = alarms.masterping;    
    var tm = new Date();
    
    beforeS = (tm.getTime() - mp.LastTs) / 1000;    
        
    var styleA = [ 'alert-success', 'alert-warning', 'alert-danger' ];
    
    $("#container").append('<div class="alert ' + styleA[mp.AlarmID]  + ' fade in m-b-15">' +
				           '    <strong>OK!</strong>' +
				           '    Seensy WatchDog is running!' +
				           '    <span class="close" data-dismiss="alert">×</span>' +
				           '</div>');
    
    var ss = alarms.seensysensors;
    
    var html = "<h3>Seensy Sensor streams</h3>";
    html += "<table class=\"table table-hover table-condensed\">";
    
    html += "<thead><tr><th>#</th><th>Sensor</th><th>Status</th><th>Last</th></tr></tread>";
    
    html += "<tbody>";
    
    var styleL = ["label-primary", "label-warning", "label-danger"];
    var textL = ["Success", "Warning", "Alarm"];
    
    for (var i in ss) {
        html += "<tr>";
        html += "<td>" + i + "</td>";
        html += "<td>" + ss[i].Sensor + "</td>";
        html += '<td><span class="label ' + styleL[ss[i].AlarmID] + '">' + textL[ss[i].AlarmID] + '</span></td>';
        
        // last TS
        html += '<td>' + timeAgoFromMs(ss[i].LastTs) + '</td>';
        
        html += "</tr>";
    }
    
    html += "</tbody>";    
    html += "</table>";
    
    $("#container").append(html);
                               
}