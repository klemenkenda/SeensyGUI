<?PHP

function pluginUser() {
    global $id;
    if ($id <= 2) return;
    
    $template = loadTemplate("user/navigation.html");
    
    $username = mysql_escape_string($_COOKIE["username"]);
    $password = mysql_escape_string($_COOKIE["password"]);
    
    $SQL = "SELECT * FROM users WHERE us_username = '$username' AND us_password = '" . md5($password) . "'";
    $result = mysql_query($SQL);
    
    if (mysql_num_rows($result) != 1) {
        setCookie("username", "", time() - 24 * 60 * 60);
        setCookie("password", "", time() - 24 * 60 * 60);   
        unset($_COOKIE["username"]);
        unset($_COOKIE["password"]);
        header("Location: /en/index.html \n");
        exit();
    }
        
    $line = mysql_fetch_array($result);
    $user = $line["us_name"];
    $image = $line["us_image"];
    
    $HTML = fillTemplate($template, array(
        "user" => $user,
        "userimg" => $image
    ));
    return $HTML;
}

function pluginPilot() {
    global $pilot;
    
    // hack, as we only have one pilot
    $SQL = "SELECT * FROM pilots";
    $result = mysql_query($SQL);
    $line = mysql_fetch_array($result);
    
    $name = $line["pi_name"];
    $subtitle = $line["pi_subtitle"];
    $image = $line["pi_image"];
    
    $template = loadTemplate("user/pilotnav.html");
    
    $HTML = fillTemplate($template, array(
        "pilot" => $name,
        "pilotsub" => $subtitle,
        "pilotimg" => $image
    ));
    
    return $HTML;
}

// check credentials --------------------------------------------------
function checkCredentials() {
    global $id;
    // are we accessing API page?
    if ($id == 2) return;
    
    global $loginerror;
    
    // do we have logout?
    if ($_GET["logout"] == "true") {
        setCookie("username", "", time() - 24 * 60 * 60);
        setCookie("password", "", time() - 24 * 60 * 60);   
        unset($_COOKIE["username"]);
        unset($_COOKIE["password"]);
    }
    
    // are we logged in?
    if ($_COOKIE["username"] != "") header("Location: /en/dashboard.html \n");
    
    $username = mysql_escape_string($_POST["username"]);
    $password = mysql_escape_string($_POST["password"]);
    $remember = mysql_escape_string($_POST["remember"]);
        
    // new login
    if ($username != "") {
        // we are already logged in
        if ($_COOKIE["username"] != "") exit();
        $SQL = "SELECT * FROM users WHERE us_username = '$username' AND us_password = '" . md5($password) . "'";
            
        $result = mysql_query($SQL);
        
        if (mysql_num_rows($result) == 1) {
            // normal user
            $duration = strtotime('+365 days');
            if ($rembember == "on") $duration = strtotime('+1 hours');
            setCookie("username", $username, $duration);
            setCookie("password", $password, $duration); 
            header("Location: /en/dashboard.html \n");
        } else {
            // wrong password or something else wrong
            $loginerror = "Username/password incorrect! Try again!<br><br>";
        }
    }
}

function handleIncorrectLogin() {
    global $loginerror;
    global $HTML;
    
    $HTML = str_replace("%loginerror%", $loginerror, $HTML);
}
?>