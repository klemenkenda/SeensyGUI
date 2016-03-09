<?PHP

function passthruHTTP($url) {	
	global $cache;
	
	if ($url == "") return "";
		
  $old = ini_set('default_socket_timeout', $cache["socket_timeout"]);
	ini_set('error_reporting', NULL);
	
  if ($fp = fopen($url, "r")) {
  	stream_set_timeout($fp, $cache["stream_timeout"]);
  	
  	ob_start();	
  	fpassthru($fp);
  	$buffer = ob_get_contents();
  	$size = ob_get_length();
  	ob_end_clean();
  
  	$info = stream_get_meta_data($fp);
  	
    fclose($fp);
  
    if ($info['timed_out']) {
      $buffer = "<error>%VAR:SERVER_TIMEOUT%</error>";
  		$size = sizeof($buffer);
    };
	} else {	
	  $buffer = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<errors><error>%VAR:SERVER_NO_CONNECTION%</error></errors>";
		$size = sizeof($buffer);
	}
	
	ini_set('default_socket_timeout', $old);   
  ini_set('error_reporting', 1);
		
	$HTML = $buffer;	
	
	return $HTML;
}

function passthruCache($url) {
  global $cache;
	
	$SQL = "SELECT * FROM webcache WHERE wc_url = '$url' AND ((NOW() - ts) < " . $cache["refresh_time"] . ")";
	$result = mysql_query($SQL);
	
	if ($line = mysql_fetch_array($result)) {
	  $content = $line["wc_result"];
	} else {
	  $content = passthruHTTP($url);
		$SQL = "INSERT INTO webcache (wc_url, wc_result) VALUES ('$url', '" . mysql_escape_string($content) . "')";
		$result = mysql_query($SQL);
	}	
	
	return $content;
}

?>
