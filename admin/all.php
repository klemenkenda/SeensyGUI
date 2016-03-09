<?php
// ----------------------------------------------------------------------
// HISTORY:
// 17/10/2005: Show uploaded image for any field - field argumend added
// 						 and img.php file changed (Klemen Kenda)
// 24/11/2005: upload_file option added (Klemen Kenda)
// 05/12/2005: richtextarea (with TinyMCE 2.0.1 added) (Klemen Kenda)
// 06/12/2005: Comments beautify. (Klemen Kenda)
// 12/12/2005: Multiple TinyMCE's bug fixed (multiplying RT 
//             editors) (Klemen Kenda)
// 01/02/2006: filename chooser field added - copied from Karma admin 
//             (Klemen Kenda)
// 01/02/2006: site structure javascript tree added/templates modified
//             (Klemen Kenda)
// 30/10/2008: filename_upload field added (Klemen Kenda)
// 30/10/2008: calendar - new JS calendar that work also in FF 
//             (Klemen Kenda)
// 27/03/2009: Upgrade to TinyMCE 3.2.2 (Klemen Kenda)
// 27/03/2009: TinyBrowser 1.4 implemented (Klemen Kenda)
// 20/03/2014: Migrated to Twitter Bootstrap 2.3.2.
// ----------------------------------------------------------------------


// settings for error reporting
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set("display_errors", 1);

// import parameters
extract($_GET); extract($_POST); extract($_COOKIE);

// ----------------------------------------------------------------------
// YOUR CONFIG FILE
// ----------------------------------------------------------------------
$cfg_file = "config.php";

// start of execution of the DAS
$mtime1 = explode(" ", microtime());
$DAS_starttime = $mtime1[0] + $mtime1[1];

// send headers
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

// ----------------------------------------------------------------------
//  SAVE DATA TO LOG
// ----------------------------------------------------------------------

function toLog($txt,$log="default.log")
{
    $f = fopen($log, "a");
    fputs($f, date("Y.m.d H:i:s -- ") . $txt . "\n");
    fclose($f);
}

// ----------------------------------------------------------------------
// REDIRECTING REQUEST
// ----------------------------------------------------------------------

function redir($url)
{
    header("Location: " . $url);
    exit;
}

// ----------------------------------------------------------------------
// PARSING TEMPLATE
// ----------------------------------------------------------------------

function template($templ,$mas="no",$autoshow=1)
{
    global $DAS_starttime;
    $txt=file("./templ/".$templ);
    $txt=join($txt,"");

    if(is_array($mas))
    {
        while(list($var,$val)=each($mas))
        {
            $txt=preg_replace("/<<$var>>/",$val,$txt);
        }
    }
    $txt=ereg_replace("<<[^>]+>>","",$txt);

    if($autoshow)
    {
       print $txt;
    }
    else return $txt;
}

// ----------------------------------------------------------------------
// CONVERT ARRAY TO STRING
// ----------------------------------------------------------------------

function to_line($arr)
{
    $rt="";
    if(!is_array($arr)) return $arr;
    while(list($id,$val) = each($arr))
    {
        if($rt!="") $rt.="_";
        $rt.=$val;
    }

    return $rt;
}

// ----------------------------------------------------------------------
// DISPLAY ERROR
// ----------------------------------------------------------------------

function errs($text="")
{
    global $language;
    if($text=="") $text=$language['error_default'];
?>
<div class="alert alert-info">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <strong>Obvestilo: </strong> <?php echo $text ?>
</div>
<?php
}

// ----------------------------------------------------------------------
// GENERATING PAGES TREE
// ----------------------------------------------------------------------
// AUTHOR: Klemen Kenda
// DATE: 2006/02/01
// DESCRIPTION: Generira drevo vsebin strani ...
// ----------------------------------------------------------------------

function adm_tree($menudata) {
		global $language;
		global $superuser;
		
		if ($superuser != 1) return "Dostop do urejanja ima samo super uporabnik!<br>";
		
    $text = "";
	  // added page tree, KK, 2006/02/01
		$text  = "<link rel=\"StyleSheet\" href=\"moduli/treeview/dtree.css\" type=\"text/css\">";
		$text .= "<script type=\"text/javascript\" src=\"moduli/treeview/dtree.js\"></script>";
		$text .= "<script type=\"text/javascript\">";
		$text .= "d = new dTree('d');";
		$text .= "d.add(0,-1,'<a style=\"font-weight: bold\" href=\"index.php?action=pages_list\">Struktura</a> [<a style=\"font-weight: bold\" href=\"index.php?action=pages_add\">dodaj</a>]');";		
		// $text .= "d.add(1,-1,'Glavna stran','index.php?action=pages_edit&id=1');";

		$SQL = "SELECT * FROM pages";
		$result = mysql_query($SQL);
		while ($line = mysql_fetch_array($result)) {
		  if ($line["id"] == 1) $line["pa_pid"] = 0;
			// if ($line["id"] != 1)		  
   	  $text .= "d.add(" . $line["id"] . ", " . $line["pa_pid"] . ", '" . $line["pa_title"] . "', 'index.php?action=pages_edit&id=" . $line["id"] . "');"; 
		}		

		$text .= "document.write(d);";

		$text .= "</script>";
		
		return $text;
}

// ----------------------------------------------------------------------
// GENERATING MAIN MENU
// ----------------------------------------------------------------------

function adm_menu($menudata,$act="")
{
    global $language;		
		global $superuser, $admin_super;
		
		$text = "";
		
    $text .= "<table cellpadding=0 cellspacing=0 border=0 width=100%>";

		$menu = array();
		
    while(list($vat,$val) = each($menudata))
    {
		  // check user
		  
		  if ($superuser) {
		  		$should_show = True;
		  }elseif ($admin_super == 2 && isset($val['main']['access']) && $val['main']['access'] == 2) {
		  		$should_show = True;
		  }elseif (isset($val['main']['access']) && $val['main']['access'] <= 1 && $admin_super != 2) {
		  		$should_show = True;
		  }else{
		  		$should_show = False;
		  }
		  
		  if ($should_show) {
		    if ($val['form'] == 'pages') continue;
        if(strlen($val['form'])<2) continue;
        $name = $val['main']['menu'];
        if($name == "") $name = $val['main']['name'];
        $table = $val['main']['table'];
        if($table == "") $table = $val['form'];
        if($val['main']['formtype'] == "auto" || $val['main']['formtype'] == "import")
        {
            if($val['main']['act']=="")
            {
                if($val['main']['menutype'] == 1)
                {
                    $menu[$name][]=array(but => $val['main']['name'].": ".$language['mainmenu_list'], link => $table."_list");
                    $menu[$name][]=array(but => $val['main']['name'].": ".$language['mainmenu_add'], link => $table."_add");
                }
                else
                {
                    $menu[$name][]=array(but => $language['mainmenu_list'], link => $table."_list");
                    $menu[$name][]=array(but => $language['mainmenu_add'], link => $table."_add");
                }
                $menu[$name][]=array(but => "", link => $table."_del");
                $menu[$name][]=array(but => "", link => $table."_edit");
            }
            else $menu[$name][]=array(but => $val['main']['name'], link => $val['form']."_".$val['main']['act']);
        }
        elseif($val['main']['formtype'] == "redirect")
        {
            $menu[$name][]=array(but => $val['main']['name'], link => $val['form']);
        }
        elseif($val[main][formtype] == "action")
        {
            $menu[$name][]=array(but => $val['main']['name'], link => $val['main']['act']);
        }
			} // check user
    }

    while(list($name,$but) = each($menu))
    {
        $razd = ereg_replace(" ","",get_sort($name));
        $buttext="";$displ="none";$cls="";
        while(list($bt,$link) = each($but))
        {
            if($link['link'] == $act)
            {
                $displ="block";
                $cls="class=adm_menu_selected";
            }
            if($link['but'] == "") continue;
						if (substr($link['link'],-3) == "add")  
						   $icon = '<i class="icon-plus-sign"></i>';
						if (substr($link['link'],-4) == "list") 
						   $icon = '<i class="icon-th-list"></i>';
            $buttext.="<a class=asubmenu href='index.php?action=".$link['link']."'>".$icon.$link['but']."</a> ";
        }
        // $text.="<tr><td><a href=\"javascript:ShowOL('".$razd."')\" ".$cls.">".$name."</a><table cellpadding=0 cellspacing=0 width='100%' border=0 id='".$razd."' style='display: ".$displ."'><tr><td>".$buttext."</td></tr></table></td></tr>";
				if ($name == "Log") $text .= "<li class='divider'></li>";
				$text .= "<li><a href=\"javascript:$('#" . $razd . "').toggle('slow');\">" . $name;
				$text .= "<div id=\"" . $razd . "\" style='display: ".$displ."'>&nbsp;&nbsp;" . $buttext . "</span>";
				$text .= "</li>";
    }

		$text .= "</table>";
		
    return $text;				
}

// ----------------------------------------------------------------------
// RETURNING ELEMENTS OF ARRAY
// ----------------------------------------------------------------------

function get_val($m,$i,$multi=0)
{
    $rt="";
    if($multi==1)
    {
        if($i=="") return $rt;
        $data = split("_", $i);
        $cnt=count($data);
        for($k=0;$k<$cnt;$k++)
        {
            if($k!=0) $rt.="<br>";
            $id=$data[$k];
            $rt.=$m[$id];
        }

    }
    else $rt=$m[$i];
    return $rt;
}


// ----------------------------------------------------------------------
// CHECKING FOR EXISTING ANY DATA
// ----------------------------------------------------------------------

function echo_check($val)
{
    global $language;

    if($val=="" || $val=="0")
    {
        return "<font color=red><b>".$language['list_showno']."</b></font>";
        
    }
    else return "<font color=00AA00><b>".$language['list_showyes']."</b></font>";
}

// ----------------------------------------------------------------------
// GENERATING DATA FOR LIST ACTION
// ----------------------------------------------------------------------

function adm_list($dat, $razd=0)
{
    global $sock,$pg,$action,$PHP_SELF,$language;
		global $superuser;
		global $admin_id, $admin_super;
?>
<h2><?php echo$dat['main']['name'] ?>: <?php echo $language['action_list']; ?></h2>

<table class="table table-hover">
<?php
    
    if(!isset($pg)) $pg=0;
    $titl="<th class=ln>ID</th>";$data="<td>\$T[id]</td>";
    if($razd!=0) $titl="<th class=ln>".$language['list_actionname']."</th>".$titl;
    while(list($t1,$fld) = each($dat))
    {
        if(strlen($fld['field'])<2) continue;
        if(!isset($fld['lst_key'])) continue;

        $ar=$fld['field']."_arr";
        if(ereg("^array\(",$fld['data']))
        {
            eval("\$".$ar."=".$fld['data'].";");
        }
        elseif($fld['data']!="")
        {
            $R=mysql_query($fld['data'],$sock);
            while(is_array($T=mysql_fetch_array($R)))
            {
                $val=$T[0];
                eval("\$".$ar."['".$val."']=\"".$T[1]."\";");
            }
        }

        $titl.="<th class=ln><nobr>".$fld['name']."</nobr></th>";
        if($fld['lst_key'] == "text_without_breaks")
        {
            $data.="<td><nobr>\$T[".$fld['field']."]</nobr></td>";
        }
        elseif ($fld['lst_key'] == "single_value_list")
        {
            $data.="<td>\".get_val(\$".$ar.", \$T[".$fld['field']."]).\"</td>";
        }
        elseif ($fld['lst_key'] == "multiple_value_list")
        {
            $data.="<td>\".get_val(\$".$ar.", \$T[".$fld['field']."], 1).\"</td>";
        }
        elseif($fld['lst_key'] == "yes_or_no")
        {
            $data.="<td>\".echo_check(\$T[".$fld['field']."]).\"</td>";
        }
        else $data.="<td>\$T[".$fld['field']."]</td>";
    }

    echo "<thead><tr align=center>".$titl."</tr></thead>";
    if($dat['main']['listorder'] == "") $dat['main']['listorder'] = "id desc";
    if($dat['main']['listperpage'] == "") $dat['main']['listperpage'] = 100;
    $nn=0;
		
		// ali sploh obstaja uporabniško polje?
  	// najdimo uporabniško polje
  	foreach($dat as $value) {
  	  if ($value["type"] == "user" || $value["type"] == "trainer") $field = $value["field"];
  	}				
		
		if ($admin_super == 1) $superuser = 1;
		
		// ali smo omejeni uporabniki
		if ((($superuser == 0) || $admin_super == 2) && ($field != "")) {			
  			$where = "WHERE $field = " . $admin_id;
		  // $where = "";
		} elseif ($admin_super == 2) {
			$where = "WHERE id = ".$admin_id;
		}else{
		  $where = "";
		};
		
    $R=mysql_query("select count(id) from ".$dat['main']['table']." " . $where . " order by ".$dat['main']['listorder'].";",$sock);
    $T=mysql_fetch_array($R);
    $maxlist=$T[0];
    $nav="";
    if($maxlist > $dat['main']['listperpage'])
    {
        $i=0;
        $nav=$language['list_position']." <select size=1 name=nav class=form onchange=\"window.location.href='".$PHP_SELF."?action=".$action."&pg='+this.value;\">";
        while(($i*$dat['main']['listperpage'])<$maxlist)
        {
            if($dat['main']['listshow']!="")
            {
                $R=mysql_query("select ".$dat['main']['listshow']." from ".$dat['main']['table']." " . $where . " order by ".$dat['main']['listorder']." limit ".($i*$dat['main']['listperpage']).",1;",$sock);
                $T1=mysql_fetch_array($R);
                $T1="(".$T1[0].") ";
                if((($i+1)*$dat['main']['listperpage'])<$maxlist)
                {
                    $R=mysql_query("select ".$dat['main']['listshow']." from ".$dat['main']['table']." " . $where . " order by ".$dat['main']['listorder']." limit ".(($i+1)*$dat['main']['listperpage']-1).",1;",$sock);
                    $T2=mysql_fetch_array($R);
                    $T2=" (".$T2[0].")";
    
                }
                else
                {
                    $R=mysql_query("select ".$dat['main']['listshow']." from ".$dat['main']['table']." " . $where . " order by ".$dat['main']['listorder']." limit ".($maxlist-1).",1;",$sock);
                    $T2=mysql_fetch_array($R);
                    $T2=" (".$T2[0].")";
                }
            }
            else {$T1="";$T2="";}

            $nav.="<option value=".$i." ".(($i==$pg)?"selected":"").">".$T1.($i*$dat['main']['listperpage'])." - ".(($i+1)*$dat['main']['listperpage']).$T2;
            $i++;
        }
        $nav.="</select>";
    }
		
		// create pagination in bootstrap				
    $R=mysql_query("select count(id) from ".$dat['main']['table']." " . $where . " order by ".$dat['main']['listorder'].";",$sock);
    $T=mysql_fetch_array($R);
    $maxlist=$T[0];		
		$pages = ceil($maxlist / $dat['main']['listperpage']);
				
		if($maxlist > $dat['main']['listperpage']) {		
  		$nav ='<div class="pagination">';
      $nav.='<ul>';
  		$i = $pg - 4;
			if ($i < 0) $i = 0;
			if ($i > $pages - 10) $i = $pages - 10;
			if ($i == 0)
			  $nav .= "<li class='disabled'><a href='#'>&laquo;</a></li>";
			else 
		    $nav .= "<li><a href='" . $PHP_SELF. "?action=".$action."&pg=" . ($pg - 1) . "'>&laquo;</a></li>";
			$count = 0;
  		while($count < 10) {
        if ($i == $pg)
				  $nav .= "<li class='disabled'><a href='#'>" . ($i + 1) . "</a></li>";
				else
				  $nav .= "<li><a href='" . $PHP_SELF. "?action=".$action."&pg=" . $i . "'>" . ($i + 1) . "</a></li>";			
        // <li><a href="#">1</a></li>
        // <li><a href="#">&raquo;</a></li>
				$i++;
				$count++;
  		}
			if ($i == $pg + 1)
			  $nav .= "<li class='disabled'><a href='#'>&raquo;</a></li>";
			else 
			  $nav .= "<li><a href='" . $PHP_SELF. "?action=".$action."&pg=" . ($pg + 1) . "'>&raquo;</a></li>";
      $nav .= '</ul>';
  		$nav .= '</div>';
		}		
		
    echo $nav;
    
    $R=mysql_query("select * from ".$dat['main']['table']." " . $where . " order by ".$dat['main']['listorder']." limit ".($pg*$dat['main']['listperpage']).",".$dat['main']['listperpage'].";",$sock);
    while(is_array($T=mysql_fetch_array($R)))
    {
        $nn++;
        if($cls=="ln1") {$cls="ln2";} else $cls="ln1";
        echo "<tr class=".$cls." id=dl_".$nn." onMouseOver=\"line_setcolor('dl_".$nn."');\">";
        if($razd!=0) echo "<td><nobr><a href='index.php?action=".$dat['main']['table']."_edit&id=".$T[id]."'>".$language['list_actionedit']."</a> | <a href='index.php?action=".$dat['main']['table']."_del&id=".$T['id']."'>".$language['list_actiondelete']."</a></nobr></td>";
        eval("\$out=\"".$data."\";");
        echo $out."</tr>";
    }

?>
</table>
<br>
<?php
}

// ----------------------------------------------------------------------
// GENERATING MAIN FORMS
// ----------------------------------------------------------------------

function adm_form($dat)
{
    global $sock,$PHP_SELF,$id,$language;
		global $superuser, $admin_id, $admin_super;

    if($dat['main']['act'] == "list")
    {
        adm_list($dat,1);
        return;
    }

    if($dat['main']['id']>0) $id=$dat['main']['id'];

    if ($dat['main']['formtype']=="auto" || $dat['main']['formtype']=="import")
    {
        $act="save_data";
    }
    elseif($dat['main']['formtype']=="action")
    {
        $act=$dat['main']['redir'];
    }
    else
    {
        $act=$dat['main']['redir'];
    }
    
    $enc="";
    if($dat['main']['formtype'] == "import") $enc="ENCTYPE='multipart/form-data'";

?>
<h2><?php echo $dat['main']['name'].": ".$language['action_'.$dat['main']['act']] ?></h2>

<FORM name=f_add ACTION="<?php echo $PHP_SELF ?>" <?php echo $enc ?> METHOD=POST>
<INPUT TYPE="hidden" name="action" value="<?php echo $act ?>">
<table class="table table-hover">
<?php

    if($dat['main']['act']=="edit" || $dat['main']['act']=="del")
    {
        $R=mysql_query("select * from ".$dat['main']['table']." where id=".$id.";", $sock);
//        die("select * from ".$dat['main']['table']." where id=".$id.";");
        $T=mysql_fetch_array($R);
    }
    $lst = "";
    while(list($t1,$fld) = each($dat))
    {
        if(strlen($fld['field'])<2) continue;

        if($lst != "") $lst.=";";
        $lst.=$fld['name'].";".$fld['field'].";".$fld['type'];

        if($fld['type']=="to_translite") continue;

        if($dat['main']['act']=="del")
        {
            if(isset($fld['del_key']))
            {
                $fld['type']=$fld['del_key'];
            }
            else continue;
        }
				
				// TYPE: user
				// DESCRIPTION: Uporabnik sistema
				// AUTHOR: Klemen Kenda
				if ($fld['type'] == "user") {				  		
					
					$vars="";
          $ps=$fld['field'];
          if($dat['main']['act'] == "add") {
            $T[$ps]=$fld['start'];
          }					
					
					if ($admin_super == 1) $superuser = 1;
					
					if (($superuser == 1)) {					
  					// izberemo bazo
  					// mysql_select_db("users", $sock);
  					$SQL = "select id,CONCAT(us_name, ' ', us_surname) AS name from users order by name;";					       
  			 
            $RR=mysql_query($SQL, $sock);
  					
            while(is_array($TT = mysql_fetch_array($RR)))
            {
              if($vars!="") $vars.=";";
              if($TT[0] == $T[$fld['field']]) $vars.=$TT[0].";".$TT[1].";selected";
							else $vars.=$TT[0].";".$TT[1].";";
            }   
  					   
  					// nazaj na prvotno stanje
  					// mysql_select_db($conf['config']['sitedata']['DBname'], $sock);
					} else {
					  $SQL = "SELECT id, au_name FROM authors WHERE id = $admin_id";
						$result = mysql_query($SQL);
						$user = mysql_fetch_array($result);
					  $vars = $user["id"] . ";" . $user["au_name"] . ";selected";
					} 
					                  // echo $vars;
          cell($fld['name'].":", $ps, $fld['type'],$vars);
				} else 
				// added by Swizec, 15/07/2011
				if ($fld['type'] == 'trainer') {
					$vars="";
          $ps=$fld['field'];
          if($dat['main']['act'] == "add") {
            $T[$ps]=$fld['start'];
          }					
					
					if ($admin_super == 1) $superuser = 1;
					
					if (($superuser == 1)) {					
  					// izberemo bazo
  					// mysql_select_db("users", $sock);
  					$SQL = "select id,tr_name from trainers order by tr_name;";					       
  			 
            $RR=mysql_query($SQL, $sock);
  					
  					echo mysql_error();
						
            while(is_array($TT = mysql_fetch_array($RR)))
            {
              if($vars!="") $vars.=";";
              if($TT[0] == $T[$fld['field']]) $vars.=$TT[0].";".$TT[1].";selected";
							else $vars.=$TT[0].";".$TT[1].";";
            }   
  					   
  					// nazaj na prvotno stanje
  					// mysql_select_db($conf['config']['sitedata']['DBname'], $sock);
					} else {
					  $SQL = "SELECT id, tr_name FROM trainers WHERE id = $admin_id";
						$result = mysql_query($SQL);
						$user = mysql_fetch_array($result);
					  $vars = $user["id"] . ";" . $user["tr_name"] . ";selected";
					} 
					                  
        		  cell($fld['name'].":", $ps, $fld['type'],$vars);
				} else
				
				// added by Swizec, 16/07/2011
				if ($fld['type'] == 'dropdown') {
					$vars = array();
					
					foreach ( explode(';', str_replace(array('(', ')'), '', $fld['choices'])) as $choice) {
						$choice = explode(",", $choice);
						$selected = ($choice[0] == $T[$fld['field']]) ? 'selected' : '';
						$vars[] = trim($choice[0]).";".trim($choice[1]).';'.$selected;
					}
					
					$vars = implode(",", $vars);
					cell($fld['name'].":", $fld['field'], $fld['type'],$vars);
					
				} else
				
				// added by Klemen Kenda, 16/01/2006, modified 20/12/2007
				if (($fld['type'] == "filename") || ($fld['type'] == "filename_upload")){
					// v $fld so vsi potrebni podatki					
					
					// branje datotek v direktoriju
					$directory = $fld['dir'];
					$d = dir($directory);
   			  $i = 0;
					
					while($entry=$d->read()) {
					  if (($entry != ".") && ($entry != "..")) {
  						$myT[$i] = $entry;
  					  // povecamo stevec
  					  $i++;
						}
					}
										
					// $myT = array("test.pdf", "ena.xml", "dva.mpi");
					$myT[$i] = $T[$fld['field']];					
				  cell($fld['name'].":", $fld['field'], $fld['type'], $myT, "text", $fld['comment']);
				} else
        // added by Klemen Kenda, 16/01/2006
				if ($fld['type'] == "filename") {
					// v $fld so vsi potrebni podatki					
					
					// branje datotek v direktoriju
					$directory = $fld['dir'];
					$d = dir($directory);
   			  $i = 0;
					
					while($entry=$d->read()) {
					  if (($entry != ".") && ($entry != "..")) {
  						$myT[$i] = $entry;
  					  // povecamo stevec
  					  $i++;
						}
					}
										
					// $myT = array("test.pdf", "ena.xml", "dva.mpi");
					$myT = array_reverse($myT);
					$myT[$i] = $T[$fld['field']];					
				  cell($fld['name'].":",$fld['field'],$fld['type'],$myT);
				} else
				// added by Swizec
				if ($fld['type'] == "multiselect_limited_list" && isset($fld['choices'])) {
					$vars = array();
					
					foreach ( explode(';', str_replace(array('(', ')'), '', $fld['choices'])) as $choice) {
						$choice = explode(",", $choice);
						$selected = (in_array($choice[0], explode("_", $T[$fld['field']]))) ? 'selected' : '';
						$vars[] = trim($choice[0]).";".trim($choice[1]).';'.$selected;
					}
					
					$vars = implode(",", $vars);
					cell($fld['name'].":", $fld['field'], '_multiselect_limited_list',$vars);
				} else
				if($fld['type'] == "dropdown_list" || $fld['type'] == "multiselect_limited_list" || $fld['type'] == "multiselect_full_list")
        {
            $vars="";
            $ps=$fld['field'];
            if($dat['main']['act'] == "add")
            {
                $T[$ps]=$fld['start'];
            }

            if(ereg("^array\(",$fld['data']))
            {
                eval("\$fld[data]=".$fld['data'].";");
                while(list($vr, $vl) = each($fld['data']))
                {
                    if($vars!="") $vars.=";";
                    $vars.=$vr.";".$vl.";";
                    if($fld['type'] == "dropdown_list")
                    {
                        if($vr == $T[$ps]) $vars.="selected";
                    }
                    else
                    {
                        if(ereg("(^|[^0-9])".$vr."($|[^0-9])",$T[$ps])) $vars.="selected";
                    }
                }
            }
            else
            {
                $RR=mysql_query($fld['data'],$sock);
                while(is_array($TT=mysql_fetch_array($RR)))
                {
                    if($vars!="") $vars.=";";
                    $vars.=$TT[0].";".$TT[1].";";
                    if($fld['type'] == "dropdown_list")
                    {
                        if($TT[0] == $T[$ps]) $vars.="selected";
                    }
                    else
                    {
                        if(ereg("(^|[^0-9])".$TT[0]."($|[^0-9])",$T[$ps])) $vars.="selected";
                    }
                }

            }
            
            // cell($fld['name'].":", $ps,$fld['type'], $vars, "", "text", $fld['comment']);
						cell($fld['name'].":", $ps,$fld['type'], $vars, "text", $fld['comment']);
        }
        else
        {
            if($fld['type'] == "upload_image") $T['table_name']=$dat['main']['table'];
							// added by KK, 20081030
            if($fld['type'] == "filename_upload") $T['table_name']=$dat['main']['table'];
            if($fld['type'] == "date")
            {
                $calendar="ok";
                if($fld['start'] == "NOW") $fld['start']=date("Y-m-d");
            }
            if($fld['type'] == "datetime")
            {
                $calendar="ok";
                if($fld['start'] == "NOW") $fld['start']=date("Y-m-d h:i:s");
            }
            if($fld['start']!="" && $dat['main']['act']=="add")
            {
                cell($fld['name'].":", $fld['field'], $fld['type'], $fld['start'], "text", $fld['comment']);
            }
            else cell($fld['name'].":", $fld['field'], $fld['type'], $T, "text", $fld['comment']);
        }				
    }

    if ($dat['main']['formtype'] == "action")
    {                   
        $but=$language['form_button_done'];
    }
    elseif ($dat['main']['formtype'] == "show")
    {
        $but=$language['form_button_show'];
    }
    elseif ($dat['main']['act'] == "del")
    {
        $but=$language['form_button_delete'];
        $qt=3;
    }
    elseif ($dat['main']['act'] == "edit")
    {
        $but=$language['form_button_save'];
        $qt=2;
    }
    else 
    {
        $but=$language['form_button_addlist'];
        $qt=1;
    }
    
?>
<INPUT TYPE=hidden name='qt' value="<?php echo $qt ?>">
<INPUT TYPE=hidden name='id' value="<?php echo $id ?>">
<INPUT TYPE=hidden name='tbl' value="<?php echo $dat['main']['table'] ?>">
<INPUT TYPE=hidden name='act' value="<?php echo $dat['main']['redir'] ?>">
<INPUT TYPE=hidden name='lst' value="<?php echo $lst ?>">
<tr><td colspan=2 class=but_field>
<button class="btn btn-primary" type=submit value="<?php echo $but ?>"><?php echo $but ?></button>
<?php 

    // add
    if ($qt==1)
    {		    
        echo " <button class='btn' type='submit' value='".$language['form_button_addmore']."' onclick=\"f_add.act.value='".$dat['main']['table']."_".$dat['main']['act']."';f_add.submit();\">".$language['form_button_addmore']."</button>";
				echo " <button class='btn' type='submit' value='".$language['form_button_addedit']."' onclick=\"f_add.act.value='".$dat['main']['table']."_edit';f_add.submit();\">".$language['form_button_addedit']."</button>";								
    }				
		
		// edit
		if ($qt==2) {
			echo " <button class='btn' type=button value='".$language['form_button_saveedit']."' onclick=\"f_add.act.value='".$dat['main']['table']."_edit';f_add.submit();\">" . $language['form_button_saveedit'] . "</button>";		
		}

		

?>
</td></tr></form></table></TD></TR></TABLE><br>
<?php 
    if($calendar=="ok") template("admin_calendar", array(monthslist => $language['monthslist'], dayofweek1 => $language['dayofweek1'], dayofweek2 => $language['dayofweek2'], dayofweek3 => $language['dayofweek3'], dayofweek4 => $language['dayofweek4'], dayofweek5 => $language['dayofweek5'], dayofweek6 => $language['dayofweek6'], dayofweek7 => $language['dayofweek7']));
}

// ----------------------------------------------------------------------
// GENERATING CELL FOR MAIN FORM
// ----------------------------------------------------------------------

function cell($name, $data, $input=0, $text="", $type="text", $comment="", $size=80)
{
    global $language;
		// Added by KK, 12/12/2005
		global $richtexton;

    if($input=="hidden")
    {
        if(is_array($text)) $text=$text[$data];
        echo "<input type='hidden' name='".$data."' value='".$text."'>";
        return true;
    }

?>
<tr><td valign=middle class=name_field><?php echo $name ?></td><td valign=middle class=data_field>
<?php 

    if($input=="flat_text")
    {
        if(is_array($text)) $text=$text[$data];
        echo $text;
        echo "<input type='hidden' name='".$data."' value='".$text."'>";
    }
    elseif ($input=="text_field")
    {
        if(is_array($text)) $text=$text[$data];
				// opuscaji v navednice
        echo "<input class=form type='".$type."' name=\"".$data."\" size='".$size."' value=\"".$text."\">";
    }
    elseif ($input=="checkbox")
    {
        if(is_array($text)) $text=$text[$data];
        if($text=="1")
        {
            $text="checked";
        }
        else $text="";
        echo "<input class=form type='checkbox' value=1 name='".$data."' ".$text.">";
    }	// added by KK, 20081030
		elseif ($input=="filename_upload") {
		  // v $text so podatki o trenutnih vrednostih
			// v $data je ime trenutnega polja
			// v $type je "text" 
			// $name - ime za izpis
			// input - tip za input
			
			$cnt = count($text);
			// zadnja vrednost v tabeli je trenutna vrednost
			$cnt--;			
			$value = $text[$cnt];						
			
			$opts = "<option value=''>brez</option>";
			
			for ($i = 0; $i < $cnt; $i++) {
			  $selected = "";				
				
				if (strcmp($value, $text[$i]) == 0) $selected = "SELECTED";
			  $opts .= "<option value='" . $text[$i] . "' " . $selected . ">" . $text[$i] . "</option>";
			}
			
			echo "<SELECT CLASS='form' NAME='$data'>" . $opts . "</SELECT>";
			echo "<br>";
      echo "<input class=form style=\"margin-top: 3px\" type='file' name='".$data."_file' size='60'>";			
			
		} elseif ($input == "user" || $input == "trainer") {
        $dat = split(";", $text);
        $cnt=count($dat);
        $kol=0;$opts="";
				// echo "<pre>"; print_r($dat); echo "</pre>";echo $text;
        for($i=0;$i<$cnt;$i+=3)
        {
            $kol++;
            $opts.="<option value='".$dat[$i]."' ".$dat[$i+2]."> ".$dat[$i+1] . "</option>";
        }
        $siz=0;$ms1=""; $ms2="";
        echo "<SELECT class=form NAME='".$data.$ms1."' SIZE='".$siz."' ".$ms2.">".$opts."</select>";		
		} 
		elseif ($input == 'dropdown' || ($input == '_multiselect_limited_list')) {
			$opts = '';
			foreach(split(",", $text) as $row) {
				$row = split(";", $row);
				$opts .= "<option value=\"{$row[0]}\" {$row[2]}>{$row[1]}</option>";
			}
			if ($input == 'dropdown') {
				echo "<select class=form name=\"$data\">$opts</select>";
			}else{
				echo "<select class=form name=\"${data}[]\" multiple=multiple>$opts</select>";
			}
		} 	
		elseif ($input=="filename") {
		  // v $text so podatki o trenutnih vrednostih
			// v $data je ime trenutnega polja
			// v $type je "text" 
			// $name - ime za izpis
			// input - tip za input
			
			$cnt = count($text);
			// zadnja vrednost v tabeli je trenutna vrednost
			$cnt--;			
			$value = $text[$cnt];						
			
			$opts = "<option value=''>brez</option>";
			
			for ($i = 0; $i < $cnt; $i++) {
			  $selected = "";				
				
				if (strcmp($value, $text[$i]) == 0) $selected = "SELECTED";
			  $opts .= "<option value='" . $text[$i] . "' " . $selected . ">" . $text[$i] . "</option>";
			}
			
			echo "<SELECT CLASS='form' NAME='$data'>" . $opts . "</SELECT>";
			
		  // print_r($text[$data]);
			// print_r($text);
			// print_r($input);
		}
    elseif ($input=="dropdown_list" || $input=="multiselect_limited_list" || $input=="multiselect_full_list")
    {
        $dat = split(";", $text);
        $cnt=count($dat);
        $kol=0;$opts="";
        for($i=0;$i<$cnt;$i+=3)
        {
            $kol++;
            $opts.="<option value='".$dat[$i]."' ".$dat[$i+2]."> ".$dat[$i+1];
        }
        if($input=="multiselect_full_list") { $siz=$kol;$ms1="[]"; $ms2="MULTIPLE";}
        elseif($input=="multiselect_limited_list") { $siz=5;$ms1="[]"; $ms2="MULTIPLE";}
        else { $siz=0;$ms1=""; $ms2="";}
        echo "<SELECT class=form NAME='".$data.$ms1."' SIZE='".$siz."' ".$ms2.">".$opts."</select>";
    }
    elseif ($input=="textarea")
    {
        if(is_array($text)) $text=$text[$data];
        echo "<textarea class=form name='".$data."' cols=80 rows=20>".ereg_replace("&","&amp;",$text)."</textarea>";
    }
    elseif ($input=="textarea_nowrap")
    {
        if(is_array($text)) $text=$text[$data];
        echo "<textarea class=form name='".$data."' cols=80 rows=20 wrap=off>".ereg_replace("&","&amp;",$text)."</textarea>";
    }
		    elseif ($input=="richtextarea")
    {
        if(is_array($text)) $text=$text[$data];								
				
				echo "<!-- tinyMCE -->";
				// samo en import richtexta
				if (!isset($richtexton)) {
  				echo "<script language=\"javascript\" type=\"text/javascript\" src=\"/admin/moduli/tiny_mce/tiny_mce.js\"></script>";
	  			echo "<script language=\"javascript\" type=\"text/javascript\" src=\"/admin/moduli/tiny_mce/tiny_mce_config.js\"></script>";
					// TinyBrowser support
					echo "<script type=\"text/javascript\" src=\"/admin/moduli/tiny_mce/plugins/tinybrowser/tb_tinymce.js.php\"></script>";
					$richtexton = 1;
				}					
				echo "<textarea name=\"".$data."\" style=\"WIDTH: 100%; HEIGHT: 340px;\" class=\"mceEditor\">".ereg_replace("&","&amp;",$text)."</textarea>";								        				
    }
    elseif ($input=="upload_image")
    {
		 	  // CHANGE: Klemen Kenda, 17/10/2005
				//         Show uploaded image for any field - field argument added in URL of img.php
				//				 See also changes in img.php
        echo "<input class=form type='file' name='".$data."' size='60'>";
        if($text[$data]!="") echo "<br><a target=_blank href='img.php?r=".$text['table_name']."&id=".$text['id']."&field=".$data."'>".$language['image_actionshow']."</a> &nbsp; <input type='checkbox' name='".$data."_del'>".$language['image_actiondelete'];
    }
    elseif ($input=="upload_file")
    {
		 		// CHANGE: Klemen Kenda, 24/11/2005
				// 				 Added this option ...
        echo "<input class=form type='file' name='".$data."' size='60'>";
        if($text[$data]!="") echo "<br><a target=\"_blank\" href='file.php?r=".$text['table_name']."&id=".$text['id']."&field=".$data."'>".$language['image_actionshow']."</a> &nbsp; <input type='checkbox' name='".$data."_del'>".$language['image_actiondelete'];
    }
    elseif ($input=="date")
    {
        if(is_array($text)) $text=$text[$data];
        // echo "<input class=form type='".$type."' name='".$data."' size='".$size."' value='".$text."' onFocus='getCalendarFor(this);'>";
				echo "<input class=form type='".$type."' name='".$data."' size='20' value='".$text."' onFocus=\"cal1x.select(document.forms[0].$data,'anchor1x_$data','yyyy-MM-dd'); return false;\">";
				echo "<A HREF=\"#\" onClick=\"cal1x.select(document.forms[0].$data,'anchor1x_$data','yyyy-MM-dd'); return false;\" TITLE=\"notitle\" NAME=\"anchor1x_$data\" ID=\"anchor1x_$data\">izberi</A>";
				
    } elseif ($input=="datetime") {
        if(is_array($text)) $text=$text[$data];
        // echo "<input class=form type='".$type."' name='".$data."' size='".$size."' value='".$text."' onFocus='getCalendarFor(this);'>";
				echo "<input class=form type='".$type."' name='".$data."' size='20' value='".$text."' onFocus=\"cal1x.select(document.forms[0].$data,'anchor1x_$data','yyyy-MM-dd'); return false;\">";
				echo "<A HREF=\"#\" onClick=\"cal1x.select(document.forms[0].$data,'anchor1x_$data','yyyy-MM-dd'); return false;\" TITLE=\"notitle\" NAME=\"anchor1x_$data\" ID=\"anchor1x_$data\">izberi</A>";
		}
		
		// CHANGE: Klemen Kenda, 30/10/2008
		// display commnet
		if ($comment != "") echo "<br><div style=\"width: 490px; font-size: 9px;\">$comment</div>";
		
    echo "</td></tr>";
}

// ----------------------------------------------------------------------
// FUNCTION FOR TRANSLITE (ONLY FOR RUSSIAN)
// ----------------------------------------------------------------------

function get_sort($st)
{
    $st=ereg_replace("[Àà]","1",$st);
    $st=ereg_replace("[Áá]","2",$st);
    $st=ereg_replace("[Ââ]","3",$st);
    $st=ereg_replace("[Ãã]","4",$st);
    $st=ereg_replace("[Ää]","5",$st);
    $st=ereg_replace("[Åå]","6",$st);
    $st=ereg_replace("[¨¸]","7",$st);
    $st=ereg_replace("[Ææ]","8",$st);
    $st=ereg_replace("[Çç]","9",$st);
    $st=ereg_replace("[Èè]","a",$st);
    $st=ereg_replace("[Éé]","b",$st);
    $st=ereg_replace("[Êê]","c",$st);
    $st=ereg_replace("[Ëë]","d",$st);
    $st=ereg_replace("[Ìì]","e",$st);
    $st=ereg_replace("[Íí]","f",$st);
    $st=ereg_replace("[Îî]","g",$st);
    $st=ereg_replace("[Ïï]","h",$st);
    $st=ereg_replace("[Ðð]","i",$st);
    $st=ereg_replace("[Ññ]","j",$st);
    $st=ereg_replace("[Òò]","k",$st);
    $st=ereg_replace("[Óó]","l",$st);
    $st=ereg_replace("[Ôô]","m",$st);
    $st=ereg_replace("[Õõ]","n",$st);
    $st=ereg_replace("[×÷]","o",$st);
    $st=ereg_replace("[Öö]","p",$st);
    $st=ereg_replace("[Øø]","q",$st);
    $st=ereg_replace("[Ùù]","r",$st);
    $st=ereg_replace("[Ûû]","s",$st);
    $st=ereg_replace("[Úú]","t",$st);
    $st=ereg_replace("[Üü]","u",$st);
    $st=ereg_replace("[Ýý]","v",$st);
    $st=ereg_replace("[Þþ]","w",$st);
    $st=ereg_replace("[ßÿ]","x",$st);
    return $st;

}

// --------------------------------------------------------------
// READING CONFIGURATION FILE
// --------------------------------------------------------------

function read_cfg($cfgfile)
{
    $f = file($cfgfile);
    while(list($var,$val)=each($f))
    {
        $line = ereg_replace("[\n\r]","",$val);

        if($line == "" || $line[0] == "#") continue;
        if($line == "END_CONFIG") break;
        
        if(preg_match("/^\[([^ ]+): (.+)\]$/",$line,$reg))
        {
            $type = $reg[1];
            $mainname = $reg[2];
            $cfg[$mainname] = array();
            $cfg[$mainname][$type] = $mainname;
        }
        elseif(preg_match("/^<([^ ]+): (.+)>$/",$line,$reg))
        {
            $type = $reg[1];
            $subname = $reg[2];
            $cfg[$mainname][$subname] = array();
            $cfg[$mainname][$subname][$type] = $subname;
        }
        elseif($reg = preg_split("/ = /",$line,2))
        {
            $cfg[$mainname][$subname][$reg[0]] = $reg[1];
        }
    }
    return $cfg;
}

// --------------------------------------------------------------
// READING LANGUAGE FILE
// --------------------------------------------------------------

function read_lang($lang)
{
    global $language;
    $language = array();
    $f = file("./lang/".$lang.".lang");
    while(list($var,$val)=each($f))
    {
        $line = ereg_replace("[\n\r]","",$val);

        if($line == "" || $line[0] == "#") continue;

        if($reg = preg_split("/ = /",$line,2))
        {
            $language[$reg[0]] = $reg[1];
        }
    }
}

?>