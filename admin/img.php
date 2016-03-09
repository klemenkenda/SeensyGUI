<?php
require("all.php");

$conf = read_cfg($cfg_file);
$sock=mysql_connect($conf['config']['sitedata']['SQLserver'],$conf['config']['sitedata']['DBlogin'],$conf['config']['sitedata']['DBpassword']);
mysql_select_db($conf['config']['sitedata']['DBname'],$sock);

header("Content-type: image/jpeg");

$R=mysql_query("SELECT * FROM $r WHERE id=$id;",$sock);
$T=mysql_fetch_array($R);

 echo $T[$field];
?>
