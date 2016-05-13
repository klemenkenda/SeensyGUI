// main part of the program
$.ajax({
    //reading available
    url: '/api/get-alarms',
    success: loadedAlarms
});

function timeDiffAgoFromMs(ts, now) {
    
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

function timeAgoFromMs(ts) {
    var tm = new Date;
    var now = tm.getTime();
    
    return timeDiffAgoFromMs(ts, now);
}

function loadedAlarms(data) {
    var alarms = JSON.parse(data);
    
    // MasterPing SWatchDog
    var mp = alarms.masterping;    
    var tm = new Date();
    
    beforeS = (tm.getTime() - mp.LastTs) / 1000;    
        
    var styleA = [ 'alert-success', 'alert-warning', 'alert-danger' ];
    var notA = [ '', 'not ', 'not '];
    var titleA = [ 'OK!', 'Warning!', 'Alarm!'];    
    
    $("#container").append('<div class="alert ' + styleA[mp.AlarmID]  + ' fade in m-b-15">' +
				           '    <strong>' + titleA[mp.AlarmID] + '</strong>' +
				           '    Seensy WatchDog is ' + notA[mp.AlarmID] + 'running! (last check: ' + timeDiffAgoFromMs(0, mp.DiffTs) + ')' +
				           '    <span class="close" data-dismiss="alert">×</span>' +
				           '</div>');
    
    // ping components
    var pi = alarms.ping;
    
    for(var i in pi) {
        var pialarm = pi[i];
        var componentName = pialarm.Name.replace('ping', '');        
        
        console.log(pialarm);
        
        $("#container").append('<div class="alert ' + styleA[pialarm.AlarmID]  + ' fade in m-b-15">' +
				           '    <strong>' + titleA[pialarm.AlarmID] + '</strong>' +
				           '    ' + componentName + ' is ' + notA[pialarm.AlarmID] + 'running! (last check: ' + timeDiffAgoFromMs(0, pialarm.DiffTs) + ')' +
				           '    <span class="close" data-dismiss="alert">×</span>' +
				           '</div>');
        
    }
    
    // ping check in components
    var pci = alarms.pingcheckin;
    
    for(var i in pci) {
        var pcialarm = pci[i];
        var componentName = pcialarm.Name.replace('ping', '');        
        
        console.log(pcialarm);
        console.log(titleA[pcialarm.AlarmID]);
        console.log(titleA);
        console.log(pcialarm.AlarmID);
        
        $("#container").append('<div class="alert ' + styleA[pcialarm.AlarmID]  + ' fade in m-b-15">' +
				           '    <strong>' + titleA[pcialarm.AlarmID] + '</strong>' +
				           '    ' + componentName + ' is ' + notA[pcialarm.AlarmID] + 'running! (last component check-in: ' + timeDiffAgoFromMs(0, pcialarm.DiffTs) + ')' +
				           '    <span class="close" data-dismiss="alert">×</span>' +
				           '</div>');
        
    }
    
    // Seensy Sensors
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