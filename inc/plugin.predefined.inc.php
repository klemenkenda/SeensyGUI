<?php

function pluginPredefined() {
    global $vid;
    
    $template = loadTemplate("predefined/index.html");
    
    $SQL = "SELECT * FROM visualizations WHERE id = $vid";
    $result = mysql_query($SQL);
    $line = mysql_fetch_array($result);
    
    $HTML = fillTemplate($template, array(
        "configJSON" => $line["vi_config"]
    ));
    
    return $HTML;
}


function pluginPredefinedMain() {
    $template = loadTemplate("predefined/index.html");
    
    $SQL = "SELECT * FROM visualizations WHERE id = 22";
    $result = mysql_query($SQL);
    $line = mysql_fetch_array($result);
    
    $HTML = fillTemplate($template, array(
        "configJSON" => $line["vi_config"]
    ));
    
    return $HTML;
}
?>