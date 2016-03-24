<?PHP
//---------------------------------------------------------------------
// FILE: config.inc.php
// AUTHOR: Klemen Kenda
// DESCRIPTION: Seensy
// DATE: 29/01/2016
// HISTORY:
//---------------------------------------------------------------------

// mysql config for Web -----------------------------------------------
$mysql_user = "root";
$mysql_pass = "";
$mysql_host = "localhost";
$mysql_dbase = "seensy";

// mysql config for SWatchDog -----------------------------------------
$swd["mysql_user"] = "root";
$swd["mysql_pass"] = "";
$swd["mysql_host"] = "localhost";
$swd["mysql_dbase"] = "swatchdog";

// mail config --------------------------------------------------------
$webmaster_mail = "klemen.kenda@ijs.si";

// filesystem config --------------------------------------------------
$filesystem_root = "D:\\Seensy\\www\\";

// miner config -------------------------------------------------------
$miner["url"] = "http://127.0.0.1";
$miner["port"] = 9201;
$miner["stream_timeout"] = 20;
$miner["socket_timeout"] = 10;

?>