<?PHP
//---------------------------------------------------------------------
// FILE: config.inc.php
// AUTHOR: Klemen Kenda
// DESCRIPTION: Seensy
// DATE: 29/01/2016
// HISTORY:
//---------------------------------------------------------------------

// mysql config for WWW -----------------------------------------------
$mysql_user = "dbuser";
$mysql_pass = "dbpass";
$mysql_host = "localhost";
$mysql_dbase = "dbname";

// mysql config for SWatchDog -----------------------------------------
$swd["mysql_user"] = "dbuser";
$swd["mysql_pass"] = "dbpass";
$swd["mysql_host"] = "localhost";
$swd["mysql_dbase"] = "swddbname";

// mail config --------------------------------------------------------
$webmaster_mail = "klemen.kenda@ijs.si";

// filesystem config --------------------------------------------------
$filesystem_root = "D:\\root_folder_www";

// qminer config -------------------------------------------------------
$miner["url"] = "http://127.0.0.1";
$miner["port"] = 9201;
$miner["stream_timeout"] = 20;
$miner["socket_timeout"] = 10;

?>