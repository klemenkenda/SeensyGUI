<?PHP
//---------------------------------------------------------------------
// FILE: plugin.api.inc.php
// AUTHOR: Klemen Kenda
// DESCRIPTION: XML plugins file
// DATE: 20/11/2012
// HISTORY:
//---------------------------------------------------------------------

//---------------------------------------------------------------------
// FUNCTION: apiError
// Description: Function generates error message
//---------------------------------------------------------------------

function apiError($e) {
	ini_set('error_reporting', 1);
	
	header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1 
	header('Cache-Control: pre-check=0, post-check=0, max-age=0');    // HTTP/1.1 
	header("Pragma: no-cache"); 
	header("Expires: 0"); 
	header("Content-Type: text/xml");
		
	$XML = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<errors><error>" . $e . "</error></errors>";	
	
	return $XML;
}

//---------------------------------------------------------------------
// FUNCTION: getURL
// DESCRIPTION: complete a post request
//---------------------------------------------------------------------
function getURL ($url) {
  	//open connection
    $ch = curl_init();
    
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded'));
	
	
	// $fields_string = urlencode($fields_string);
	//  print_r($fields_string);
	// exit();
	
    // curl_setopt($ch, CURLOPT_POST, count($fields));
	// curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 45); //timeout in seconds
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	
		
	//execute post
    $html = curl_exec($ch);
    
	if (curl_error($ch)) return -1;
		
    //close connection
    curl_close($ch);
	return $html;
}

//---------------------------------------------------------------------
// FUNCTION: getURLPost
// DESCRIPTION: complete a post request
//---------------------------------------------------------------------
function getURLPost ($url, $fields, $raw = 1) {
  	//url-ify the data for the POST
	$fields_string = $fields;
    if ($raw == 0) {
		$fields_string = "";
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		$fields_string = rtrim($fields_string, '&');
	}
    
    //open connection
    $ch = curl_init();
    
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded'));
	
	
	// $fields_string = urlencode($fields_string);
	//  print_r($fields_string);
	// exit();
	
    curl_setopt($ch, CURLOPT_POST, count($fields));
	// curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
	curl_setopt($ch, CURLOPT_TIMEOUT, 45); //timeout in seconds
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	
		
	//execute post
    $html = curl_exec($ch);
    
	if (curl_error($ch)) return -1;
		
    //close connection
    curl_close($ch);
	return $html;
}

//---------------------------------------------------------------------
// function: addJSON
// Description: Data adapter for data from standard JSON
//---------------------------------------------------------------------
function addJSON($log = TRUE) {
	global $data;
	global $miner;
	
	$JSON = $data;
	
	// debug - create a QMiner garbage collector flush
	$url = $miner["url"] . ":" . $miner["port"] . "/gs_gc";	
	$HTML = getURL($url);	
	
	// create request to EnStreaM
	$url = $miner["url"] . ":" . $miner["port"] . "/data/add-measurement?data=" . urlencode($JSON);
	// $HTML = passthruHTTP($url);
	$HTML = getURL($url);
	
	if ($log) {
		// add request to the log file
		date_default_timezone_set('UTC');
		$logname = "log-" . date("Ymd") . ".txt";
		$fp = fopen("logs/" . $logname, "a+");
		fwrite($fp, $data . "\n");
		fclose($fp);
	}
	
	// save to disk
	// sleep(1);
	return $HTML;	
}

//---------------------------------------------------------------------
// function: addJSONUpdate
// Description: Data adapter for data from standard JSON
//---------------------------------------------------------------------
function addJSONUpdate($log = FALSE) {
	global $data;
	global $miner;
	
	$JSON = $data;        
		
	// create request to EnStreaM
	$url = $miner["url"] . ":" . $miner["port"] . "/data/add-measurement-update?data=" . urlencode($JSON);
	// $HTML = passthruHTTP($url);
	$HTML = getURL($url);
    
    // return $miner["port"]; // . $url;
	
	if ($log) {
		// add request to the log file
		date_default_timezone_set('UTC');
		$logname = "log-" . date("Ymd") . ".txt";
		$fp = fopen("logs/" . $logname, "a+");
		fwrite($fp, $data . "\n");
		fclose($fp);
	}
	
	// save to disk
	// sleep(1);
	return $HTML;	
}

//---------------------------------------------------------------------
// FUNCTION: getSensors
// Description: Get list of sensors in JSON
//---------------------------------------------------------------------

function getSensors() {
    global $miner;
	$url = $miner["url"] . ":" . $miner["port"] . "/qm_wordvoc?keyid=2";
	$HTML = getURL($url);
	return $HTML;
}

//---------------------------------------------------------------------
// FUNCTION: getNodes
// Description: Get list of nodes in JSON
//---------------------------------------------------------------------

function getNodes() {
    global $miner;    
	$url = $miner["url"] . ":" . $miner["port"] . "/data/get-nodes";
	$HTML = getURL($url);
	return $HTML;
}

//---------------------------------------------------------------------
// FUNCTION: getMeasurements
// Description: Get list of measurements for a sensor in JSON
//---------------------------------------------------------------------

function getMeasurements($name, $date, $enddate) {
	global $miner;
	$url = $miner["url"] . ":" . $miner["port"] . "/data/get-measurement?sensorName=" . urlencode($name) . "&startDate=" . $date . "&endDate=" . $enddate;
	$HTML = getURL($url);
	header("Content-Type: application/javascript");
	return $HTML;
}

function getMeasurementsJSONP($name, $date, $enddate) {
  $JSON = getMeasurements($name, $date, $enddate);
  return "measurements(" . $JSON . ");";
}

//---------------------------------------------------------------------
// FUNCTION: getNMeasurements
// Description: Get list of N sensor measurements for a sensor in JSON
//---------------------------------------------------------------------

function getNMeasurements($name, $date, $enddate) {
	global $miner;
	$url = $miner["url"] . ":" . $miner["port"] . "/data/n-get-measurement?sensorNames=" . urlencode($name) . "&startDate=" . $date . "&endDate=" . $enddate;
	$HTML = getURL($url);
	header("Content-Type: application/javascript");
	return $HTML;
}

//---------------------------------------------------------------------
// FUNCTION: getAggregates
// Description: Get list of aggregates with filters in JSON
//---------------------------------------------------------------------

function getAggregates($name, $type, $window, $date, $enddate) {
	global $miner;
	$url = $miner["url"] . ":" . $miner["port"] . "/data/get-aggregate?sensorName=" . urlencode($name) . "&type=" . $type . "&window=" . $window . "&startDate=" . $date . "&endDate=" . $enddate;
	$HTML = getURL($url);
	header("Content-Type: application/javascript");
	return $HTML;
}

function getAggregatesJSONP($name, $type, $window, $date, $enddate) {
  $JSON = getAggregates($name, $type, $window, $date, $enddate);
  return "aggregates(" . $JSON . ");";
}


//---------------------------------------------------------------------
// FUNCTION: getNAggregates
// Description: Get list of N sensor aggregates with filters in JSON
//---------------------------------------------------------------------

function getNAggregates($name, $type, $window, $date, $enddate) {
	global $miner;
	$url = $miner["url"] . ":" . $miner["port"] . "/data/n-get-aggregate?sensorNames=" . urlencode($name) . "&type=" . $type . "&window=" . $window . "&startDate=" . $date . "&endDate=" . $enddate;
	$HTML = getURL($url);
	return $HTML;
}

//---------------------------------------------------------------------
// FUNCTION: getAllAggregates
// Description: Get list of aggregates with filters in JSON
//---------------------------------------------------------------------

function getAllAggregates($name, $date, $enddate) {
	global $miner;
	$url = $miner["url"] . ":" . $miner["port"] . "/data/get-aggregates?sensorName=" . urlencode($name) . "&startDate=" . $date . "&endDate=" . $enddate;
	$HTML = getURL($url);
	return $HTML;
}

//---------------------------------------------------------------------
// FUNCTION: getNAllAggregates
// Description: Get list of N sensor aggregates with filters in JSON
//---------------------------------------------------------------------

function getNAllAggregates($name, $date, $enddate) {
	global $miner;
	$url = $miner["url"] . ":" . $miner["port"] . "/data/n-get-aggregates?sensorNames=" . urlencode($name) . "&startDate=" . $date . "&endDate=" . $enddate;
	$HTML = getURL($url);
	return $HTML;
}

//---------------------------------------------------------------------
// FUNCTION: getCurrentAggregates
// Description: Get list of current aggregates
//---------------------------------------------------------------------

function getCurrentAggregates($name) {
	global $miner;
	$url = $miner["url"] . ":" . $miner["port"] . "/data/get-current-aggregates?sensorName=" . urlencode($name);
	$HTML = getURL($url);
	return $HTML;
}

//---------------------------------------------------------------------
// FUNCTION: getNCurrentAggregates
// Description: Get list of N sensor current aggregates
//---------------------------------------------------------------------

function getNCurrentAggregates($name) {
	global $miner;
	$url = $miner["url"] . ":" . $miner["port"] . "/data/n-get-current-aggregates?sensorNames=" . urlencode($name);
	$HTML = getURL($url);
	return $HTML;
}

//---------------------------------------------------------------------
// FUNCTION: exportDataCleaning
// Description: Exports data for Data Cleaning NRG4Cast D2.3
//---------------------------------------------------------------------

function exportDataCleaning() {
	global $miner;
	global $sensorid;
	global $parameters;
	global $gap;
	
	// explode parameters
	$lines = explode(",", $parameters);
	$lines[count($lines)] = $gap;
	// write them into a file
	$fp = fopen("cleaning/parameters.txt", "w");
	for($i = 0; $i < count($lines); $i++) {
		fwrite($fp, $lines[$i] . "\n");
	}
	fclose($fp);
	
	// get data sample	
	$url = $miner["url"] . ":" . $miner["port"] . "/data/get-cleaning-sample?sensorid=" . $sensorid;
	$contents = getURL($url);
	
	$fp = fopen("cleaning/sample.csv", "w");
	fwrite($fp, $contents);
	fclose($fp);
	
	$HTML = "OK";
	
	return $HTML;
}

function exportAllMeasurements($sensorid) {
  global $miner;
  
  // get data sample	
	$url = $miner["url"] . ":" . $miner["port"] . "/data/get-all-measurements?sensorid=" . $sensorid;
	// return $url;
	
	$contents = getURL($url);
	
	return $contents;
}

function exportAllAggregates($sensorid, $aggrname, $windowlen) {
  global $miner;
  
  // get data sample	
	$url = $miner["url"] . ":" . $miner["port"] . "/data/get-all-aggregates?sensorid=" . $sensorid . "&aggrtype=" . $aggrname . "&windowlen=" . $windowlen;
	// return $url;
	
	$contents = getURL($url);
	
	return $contents;
}

//---------------------------------------------------------------------
// FUNCTION: getAlarms
// Description: Gets watchdog alarms + checks master
//---------------------------------------------------------------------

function getAlarms() {
    global $swd;
    $alarmCode = array("nothing", "warning", "alarm");
    $alarms = array();
    
    $conn = new mysqli($swd["mysql_host"], $swd["mysql_user"], $swd["mysql_pass"], $swd["mysql_dbase"]);
    
    // check master ping
    $sql = "SELECT *, (NOW() - ping.ts) as diff, UNIX_TIMESTAMP(ping.ts) AS unixts, (NOW() - ping.ts) AS unixdiffts FROM source, ping WHERE source.id = pi_source";
    
    $result = $conn->query($sql);
    echo $conn->error;
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $secs = $row["diff"];
        $config = json_decode($row["so_config"]);
        $alarmT = $config->general->alarmTreshold;
        $warningT = $config->general->warningTreshold;
        
        $alarm = 0;
        if ($row["diff"] > $warningT / 1000) $alarm = 1;
        if ($row["diff"] > $alarmT / 1000) $alarm = 2;
        
        $masterAlarm["Type"] = "masterping";
        $masterAlarm["Sensor"] = "SWatchDog ping";
        $masterAlarm["AlarmID"] = $alarm;
        $masterAlarm["AlarmIDName"] = $alarmCode[$alarm];
        $masterAlarm["LastTs"] = $row["unixts"] . "000";
        $masterAlarm["DiffTs"] = $row["unixdiffts"] . "000";
    };
    
    $alarms["masterping"] = $masterAlarm;    
    
    // check seensysensors last alarms records in the DB
    $sql = "SELECT m1.* FROM alarms m1 LEFT JOIN alarms m2 ON (m1.al_sourceid = m2.al_sourceid AND m1.id < m2.id), source, type WHERE m2.id IS NULL AND m1.al_sourceid = source.id AND source.so_typeid = type.id AND type.id = 2";
    
    $result = $conn->query($sql);
    echo $conn->error;
    $ssalarms = array();
    
    while ($row = $result->fetch_assoc()) {
        $recAlarmsJSON = $row["al_description"];
        $recAlarms = json_decode($recAlarmsJSON, true);
        foreach($recAlarms as $recAlarm) {
            // $alarms = array_merge($alarms, $recAlarm);
            array_push($ssalarms, $recAlarm);
        }
    }
    
    $alarms["seensysensors"] = $ssalarms;
    
    // check ping last alarms records in the DB
    $sql = "SELECT m1.*,  UNIX_TIMESTAMP(m1.ts) AS unixts, (NOW() - source.so_last) AS unixdiffts FROM alarms m1 LEFT JOIN alarms m2 ON (m1.al_sourceid = m2.al_sourceid AND m1.id < m2.id), source, type WHERE m2.id IS NULL AND m1.al_sourceid = source.id AND source.so_typeid = type.id AND type.id = 1";
    
    $result = $conn->query($sql);
    echo $conn->error;
    $palarms = array();
    
    while ($row = $result->fetch_assoc()) {
        $recAlarmsJSON = $row["al_description"];
        $recAlarms = json_decode($recAlarmsJSON, true);
        if ($recAlarms == array()) {
            $recAlarms["Type"] = "ping";
            $recAlarms["AlarmID"] = 0;
            $recAlarms["AlarmIDName"] = $alarmCode[0];
        }
        $recAlarms["Name"] = $row["al_name"];
        $recAlarms["LastTs"] = $row["unixts"] . "000";
        $recAlarms["DiffTs"] = $row["unixdiffts"] . "000";
        array_push($palarms, $recAlarms);        
    }
    
    $alarms["ping"] = $palarms;
    
    
    // check ping checki in last alarms record in the DB     
    $sql = "SELECT m1.*,  UNIX_TIMESTAMP(m1.ts) AS unixts, (NOW() - ping.ts) AS unixdiffts FROM alarms m1 LEFT JOIN alarms m2 ON (m1.al_sourceid = m2.al_sourceid AND m1.id < m2.id), source, type, ping WHERE m2.id IS NULL AND m1.al_sourceid = source.id AND source.so_typeid = type.id AND type.id = 4 AND pi_source = source.id";
    
    $result = $conn->query($sql);
    echo $conn->error;
    $palarms = array();
    
    while ($row = $result->fetch_assoc()) {
        $recAlarmsJSON = $row["al_description"];
        $recAlarms = json_decode($recAlarmsJSON, true);
        if ($recAlarms == array()) {
            $recAlarms["Type"] = "ping";
            $recAlarms["AlarmID"] = 0;
            $recAlarms["AlarmIDName"] = $alarmCode[0];
        }
        $recAlarms["Name"] = $row["al_name"];
        $recAlarms["LastTs"] = $row["unixts"] . "000";
        $recAlarms["DiffTs"] = $row["unixdiffts"] . "000";
        array_push($palarms, $recAlarms);        
    }
    
    $alarms["pingcheckin"] = $palarms;
    
    $conn->close();
    
    // connect to SWatchDog DB
	return json_encode($alarms);
}

//---------------------------------------------------------------------
// PLUGIN: APIGET
// Description: Switch for API requests
//---------------------------------------------------------------------
function pluginAPIGET() {
  global $cmd; // command
	global $p; 	 // parameters
		
	$par = explode(":", $p);	// tokenize the parameters
  $pars = sizeof($par);			// get number of parameters

	// filter ":" is changed with "\colon;"
	for ($i = 0; $i < $pars; $i++) {
	  $par[$i] = str_replace("\colon;", ":", $par[$i]);
	}  	
			
	// cross site scripting
	header('Access-Control: allow <*>');	
	
	switch ($cmd) {
		// GET METADATA
		case "get-sensors":
			$HTML = getSensors();
			break;		
		case "get-nodes": 
			$HTML = getNodes();
			break;
			
		// GET MEASUEREMENTS AND AGGREGATES
		case "get-measurements": 
			if ($pars == 3) { 
				$HTML = getMeasurements($par[0], $par[1], $par[2]);				
			} else {
				$HTML = apiError("Wrong parameter count!");
			}
			break;
		case "get-measurements-jsonp": 
			if ($pars == 3) { 
				$HTML = getMeasurementsJSONP($par[0], $par[1], $par[2]);				
			} else {
				$HTML = apiError("Wrong parameter count!");
			}
			break;			
		case "n-get-measurements": 
			if ($pars == 3) { 
				$HTML = getNMeasurements($par[0], $par[1], $par[2]);				
			} else {
				$HTML = apiError("Wrong parameter count!");
			}
			break;
		case "get-aggregates": 
			if ($pars == 5) { 
				$HTML = getAggregates($par[0], $par[1], $par[2], $par[3], $par[4]);				
			} else {
				$HTML = apiError("Wrong parameter count!");
			}
			break;	
		case "get-aggregates-jsonp": 
			if ($pars == 5) { 
				$HTML = getAggregatesJSONP($par[0], $par[1], $par[2], $par[3], $par[4]);				
			} else {
				$HTML = apiError("Wrong parameter count!");
			}
			break;	
		case "n-get-aggregates": 
			if ($pars == 5) { 
				$HTML = getNAggregates($par[0], $par[1], $par[2], $par[3], $par[4]);				
			} else {
				$HTML = apiError("Wrong parameter count!");
			}
			break;				
		case "get-all-aggregates": 
			if ($pars == 3) { 
				$HTML = getAllAggregates($par[0], $par[1], $par[2]);				
			} else {
				$HTML = apiError("Wrong parameter count!");
			}
			break;		
		case "n-get-all-aggregates": 
			if ($pars == 3) { 
				$HTML = getNAllAggregates($par[0], $par[1], $par[2]);				
			} else {
				$HTML = apiError("Wrong parameter count!");
			}
			break;				
		case "get-current-aggregates":
			if ($pars == 1) {
			$HTML = getCurrentAggregates($par[0]);			
			} else {
			$HTML = apiError("Wrong parameter count!");
			}
			break;
		case "n-get-current-aggregates":
			if ($pars == 1) {
				$HTML = getNCurrentAggregates($par[0]);			
			} else {
				$HTML = apiError("Wrong parameter count!");
			}
			break;		  
			
		// ADDING DATA
		case "add-json":
		      $HTML = addJSON();
		      break;
		case "add-json-no-log":
		      $HTML = addJSON(FALSE);
		      break;
		case "add-json-update":
		      $HTML = addJSONUpdate();
		      break;
	  	
		// EXPORTING DATA			
		case "export-all-measurements":
		  if ($pars == 1) {
		    $HTML = exportAllMeasurements($par[0]);
		    // $HTML = "test";
		  } else {
		    $HTML = apiError("Wrong parameter count!");
		  }
		  break;
		case "export-all-aggregates":
		  if ($pars == 3) {
			$HTML = exportAllAggregates($par[0], $par[1], $par[2]);			
		  } else {
			$HTML = apiError("Wrong parameter count!");
		  }
		  break;	
		  
		// ALARMS & WARNINGS
        case "get-alarms":
            $HTML = getAlarms();
            break;
		
		// DEFAULT RESPONSE
		default:
			$HTML = apiError("Command not correct!");
			break;		
	}
	
	return $HTML;
}

?>
