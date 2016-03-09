<?php
// settings for error reporting
ini_set('display_errors','On'); 
ini_set('error_reporting', E_ALL);

require("all.php");

$conf = read_cfg($cfg_file);
$clrs = read_cfg("colors.cfg");
$cur_colors=$clrs['colors']['blue'];


if(isset($set_lang) && $set_lang!="")
{
    SetCookie("current_lang", $set_lang, time()+60*60*24);
    $current_lang=$set_lang;
}

if(isset($current_lang) && $current_lang!="")
{
    read_lang($current_lang);
}
else read_lang($conf['config']['sitedata']['lang']);

if(isset($conf['config']['sitedata']['colorscheme']))
{
    $scheme = $conf['config']['sitedata']['colorscheme'];
    $cur_colors = $clrs['colors'][$scheme];
}
$cur_colors = array_merge($cur_colors,$conf['config']['colors']);

$sock=mysql_connect($conf['config']['sitedata']['SQLserver'],$conf['config']['sitedata']['DBlogin'],$conf['config']['sitedata']['DBpassword']);
mysql_select_db($conf['config']['sitedata']['DBname'],$sock);

require("adm_login.php");

if($conf[$action]['main']['formtype'] == "redirect") redir($conf[$action]['main']['redir']);

if($action=="save_data") require_once("save_data.php");

template("admin_htmlhead",
    array(
           sitename => $conf['config']['sitedata']['name'],
           title => $language['htmltitle'],
           css => template("style.css",$cur_colors,0)
         ));

$langs_show="";
if($conf['config']['sitedata']['langshow'] != "")
{
    $langs_array=split(" ",$conf['config']['sitedata']['langshow']);
    for($lang_num=0;$lang_num<count($langs_array);$lang_num++)
    {
        $langs_show.="<a href='index.php?set_lang=".$langs_array[$lang_num]."'><img border=0 src='img/flags/".$langs_array[$lang_num].".png' width=14 height=14 alt='".$langs_array[$lang_num]."'></a> ";
    }
}

template("admin_top",array(langs => $langs_show, systemname => $language['systemname'],mainmenu => $language['mainmenu_name'],main_menu => adm_menu($conf,$action),tree => adm_tree($conf)));

if($qerr > 0) errs($language['error_num'.$qerr]);

if($action == "") template("admin_body", array( razdel => $language['htmltitle'], body_text => "" ));

if($conf[$action]['main']['formtype']=="action")
{
    require($conf['config']['sitedata']['loadmodul']);
}
elseif(ereg("^(.+)_(.+)$",$action,$reg))
{
  $razd=$reg[1];
  $param=$conf[$razd];
  if($param['main']['act']=="") $param['main']['act']=$reg[2];
  if($param['main']['redir']=="") $param['main']['redir']=$razd."_list";
  if($param['main']['table']=="") $param['main']['table']=$param['form'];
  adm_form($param);
}

$mtime2 = explode(" ", microtime());
$DAS_endtime = $mtime2[0] + $mtime2[1];
$totaltime = $DAS_endtime - $DAS_starttime;
$totaltime = number_format($totaltime, 5);

template("admin_bottom", array(bottom_text => "<center>".$language['execute_text']." ".$totaltime." ".$language['execute_second']."</center>"));
?>