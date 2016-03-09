<?PHP
// ----------------------------------------------------------------------
// FILE: save_data.php
// AUTHOR: Klemen Kenda
// DATE: 01/01/2004
// HISTORY:
// 24/11/2005: upload_file option added - NOT FINISHED (Klemen Kenda)
// 25/11/2005: Commenting the file (Klemen Kenda)
// 29/10/2008: Bugfix - SQL error with '
// ----------------------------------------------------------------------

// if this PHP is not called as include, we exit
if(!function_exists("template")) exit;

// all the data comes in the $lst variable and is stored in three's
// variable name|field name|field type; there are more such threes in 
// the $lst variable

// we split the data into pieces
$dat = split(";", $lst);
// we count all the pieces
$cnt = count($dat);
// we empty all the values
$ins=""; $upd=""; $flds="";

// we run this loop through all threes
for($i=0; $i < $cnt; $i+=3)
{
 		// field name
		// $$val is acctually field value
    $val = $dat[$i+1];
    
    echo $dat[$i+2]."<br>";
    
		// $dat[i+2] is field type
    if($dat[$i+2]=="checkbox")
    {
        $$val=$$val * 1;
    }
    elseif($dat[$i+2]=="multiselect_limited_list" || $dat[$i+2]=="multiselect_full_list")
    {
        $$val=to_line($$val);
    }
    elseif($dat[$i+2]=="upload_image")
    {
        eval("\$fff_del=\$".$val."_del;");
        eval("\$fff_name=\$".$val."_name;");
				print_r($fff_name);        
				$fff=$$val;
				
				// hack za Sinergise server
				$fff = $fff_name = $tmp_filename = $_FILES[$val]["tmp_name"];
				// print_r($_FILES);
				// echo $fff;
				
        if($fff_name!="")
        {
            $f=fopen($fff,"rb");
            $upload=fread($f,filesize($fff));
            fclose($f);
            $fff=addslashes($upload);
        }
        else $fff="";
        $$val=$fff;
    }
		elseif($dat[$i+2]=="filename_upload")
    {		
				// read the data and parameters from POST-ed values
				eval("\$fff_del=\$".$val."_del;");
        eval("\$fff_name=\$".$val."_name;");
        eval("\$fff_file=\$".$val."_file;");				        
				$fff=$$val;
				
				$filename = $_FILES[$val."_file"]["name"];
				$tmp_filename = $_FILES[$val."_file"]["tmp_name"];
				
				$path = $conf[$tbl][$val]["dir"];
				if ($path[strlen($path) - 1] != "/") $path .= "/";				
				
				if ($filename != "") {
  				if ($filename != urlencode($filename)) {
  				  $qt = -1;
  					// nova custom napaka
  					$qerr = 112;
  				} else {					
  				  // premaknimo uploadano datoteko
						// ali datoteka ze obstaja
						if (file_exists($path . $filename)) {
						  $qt = -1;
							$qerr = 113;
						} else {
    					move_uploaded_file($tmp_filename, $path . $filename);
    					// zapomnimo si stvari
  						$$val = $filename;
						}
					}
				} else {
  				// je ze kul ...
				}
    }
    elseif($dat[$i+2]=="to_translite")
    {
        $tmp=$dat[$i];
        $$val=get_sort($$tmp);
    }

    $flds.=",".$val;
    // bugfix - KK, 20081028
		if ($dat[$i+2] != "upload_image") {
  		// $ins.=",'".mysql_escape_string($$val)."'";
			// ze dobimo escapane stringe! posledica import_variables?
			$ins.=",'".$$val."'";			
		} else {
			$ins.=",'".$$val."'";
		}

    if(($dat[$i+2]!="upload_image")||($$val!="")||($fff_del=="on"))
    {
		    // bugfix - KK, 20060314 - mysql_escape_string
				// bugfix - KK, 20060329 - damaged photo upload
				// ze dobimo escapane stringe! posledica import_variables?
        if ($dat[$i+2]!="upload_image") $upd.=",".$val."='".$$val."'";
				else 
				$upd.=",".$val."='".$$val."'"; 
    }
}

// switching due to specific action stored in qt variable
// 3 - delete
// 2 - update
// 1 - insert
// qerr reports sucessful action with qt value or 111 for error 
if ($qt==3)
{
    if(mysql_query("delete from $tbl where id=$id;",$sock))
    {
        $qerr=3;
    }
    else
    {
        $qerr=111;
        toLog("SQL: delete from $tbl where id=$id","logs/errors.log");
    }
}
elseif ($qt==2)
{
    if(mysql_query("update $tbl SET id=$id $upd where id=$id;", $sock))
    {
        $qerr=2;	
				$thisid = $id;		
				if (substr($act, -4) == "edit") redir("index.php?action=$act&id=$thisid&qerr=1");	
    }
    else
    {
        $qerr=111;
        toLog("SQL: update $tbl SET id=$id $upd where id=$id","logs/errors.log");
    }
}
elseif ($qt==1)
{
//	die("insert into $tbl (id $flds) values(0 $ins);");
    if(mysql_query("insert into $tbl (id $flds) values(0 $ins);", $sock))
    {
        $qerr=1;
				$thisid = mysql_insert_id();
				//echo $act;
				if (substr($act, -4) == "edit") redir("index.php?action=$act&id=$thisid&qerr=1");
    }
    else
    {
        $qerr=111;
        toLog("SQL: insert into $tbl (id $flds) values(0 $ins)","logs/errors.log");
				toLog("   - " . mysql_error(),"logs/errors.log");
    }
}

// passing the argument to index.php file
redir("index.php?action=$act&qerr=$qerr");
?>