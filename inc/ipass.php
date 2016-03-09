<?PHP
// --------------------------------------------------------------------
// FILE: slika_resize.php
// AUTHOR: Klemen Kenda
// DESCRIPTION: Photo from DB
// DATE: 27/11/2013
// HISTORY:
// -------------------------------------------------------------------- 

// includes -----------------------------------------------------------
include("../inc/config.inc.php");
include("../inc/sql.inc.php");

// import variables
import_request_variables("gPC");

if (strpos($f, "image") < 1) exit();
if (!is_numeric($id)) exit();

$result = mysql_query("SELECT * FROM $t WHERE id = $id");
$line = mysql_fetch_array($result);

// naredimo sliko iz baze
header("Content-Type: image/jpeg");
header("Content-Length: " . sizeof($line["$f"]) . "");
echo $line["$f"];
exit();		
		
?>
