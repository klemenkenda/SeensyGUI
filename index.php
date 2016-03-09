<?PHP
//---------------------------------------------------------------------
// FILE: index.php
// AUTHOR: Klemen Kenda
// DESCRIPTION: RutkaCMS main page
// DATE: 15/03/2014
// HISTORY:
//--------------------------------------------------------------------- 

// DEBUG
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);

// includes -----------------------------------------------------------
include("inc/config.inc.php");
include("inc/sql.inc.php");
include("inc/inputfilter.inc.php"); // XSS & SQLI check
include("inc/template.inc.php");
include("inc/plugins.inc.php");

// main program -------------------------------------------------------
extract($_GET); extract($_POST); extract($_COOKIE);

// default language
if (!isset($lang) || ($lang == "")) $lang = "en";
$lang_suffix = "";

// virtual file - URL rewriting
$vfile_main = $vfile;

// if there is a virtual file ...
if (isset($vfile_main)) {
	// vfile is filename_parameter.html
	list ($vfile_main, $parameter) = split("_", $vfile_main, 2);
  
  $SQL = "SELECT * FROM pages WHERE pa_uri$lang_suffix = '" . $vfile_main . "'";
	$result = mysql_query($SQL);
	if ($line = mysql_fetch_array($result)) {
  	$id = $line["id"];
	}	
}

// if there is no page id set,
// first page should be displayed
if (!isset($id)) {
	$id = 1;
	exit();
}

// check hackers
if (!is_numeric($id)) exit();

if ($id == 1) checkCredentials();

// load from dB
$SQL = "SELECT * FROM pages WHERE id = $id";
$result = mysql_query($SQL);
$page = mysql_fetch_array($result);

// load template file
if ($page["pa_template"] != "") $HTML = loadTemplate($page["pa_template"] . ".html");
else $HTML = loadTemplate("main.html");
if ($page["id"] == 1) $HTML = loadTemplate("login.html");
if ($page["pa_weight"] > 100000) $HTML = loadTemplate("blank.html");

// let's work on content
handleContent();

handlePlugins();
handleTitle();
handleVars();
filterEmails();
handleIncorrectLogin();

// display page
echo $HTML;

// end -----------------------------------------------------------------
exit();	
?>
