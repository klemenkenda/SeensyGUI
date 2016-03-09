<?PHP
//---------------------------------------------------------------------
// FILE: upload_module.php
// AUTHOR: Klemen Kenda
// DESCRIPTION: Upload module for RutkaCMS 
// DATE: 19/10/2005
// HISTORY:
//   20/12/2005: Modified for CS Ljubljana-Crnuce
//--------------------------------------------------------------------- 

// --------------------------------------------------------------------
// MODULE: upload file - moduleact1
// --------------------------------------------------------------------
if($action == "moduleact1")
{

if (!isset($upload)) {
?>
<TABLE cellSpacing=0 cellPadding=0 width=99% border=0>
  <TR valign=bottom>
	  <TD align=left class=main_body><B>MODULE: file upload</B></TD>
		<TD align=right width="1%"><IMG height=14 src="img/endcap-grey.gif" width=14></TD></TR>
	<TR vAlign=bottom><TD colspan=2 bgColor=#cecece><IMG height=1 src="img/pixel.gif" width=1></TD></TR>
	<TR vAlign=bottom>
	  <TD colspan=2><br>
	    <FORM name=f_add ACTION="index.php" ENCTYPE="multipart/form-data" METHOD="POST">
  		<INPUT TYPE="hidden" name="action" value="moduleact1">
			<INPUT TYPE="hidden" name="upload" value="yes">
      <table align=center border=1 cellspacing=0 cellpadding=4>
        <tr><td valign=middle class=name_field>Filename:</td><td valign=middle class=data_field>
				<input class=form type='file' name='file' size='80' value=''></td></tr><tr><td valign=middle class=name_field>Title:</td><td valign=middle class=data_field>
				<SELECT class=form NAME='fi_type' SIZE='0'><option value='docs'>dokument</option></select></td></tr><INPUT TYPE=hidden name='qt' value="1">
				<tr><td colspan=2 class=but_field>
				<input class=but type=submit value="Upload">				
			</td></tr></form>
			</table>
		</td>
	</tr>
</table>	
<?PHP
} else {
?>
<TABLE cellSpacing=0 cellPadding=0 width=99% border=0>
  <TR valign=bottom>
	  <TD align=left class=main_body><B>MODULE: file upload</B></TD>
		<TD align=right width="1%"><IMG height=14 src="img/endcap-grey.gif" width=14></TD></TR>
	<TR vAlign=bottom><TD colspan=2 bgColor=#cecece><IMG height=1 src="img/pixel.gif" width=1></TD></TR>
</TABLE>
<br>
<?
  $uploaddir = substr($_SERVER["SCRIPT_NAME"], 0, strlen($_SERVER["SCRIPT_NAME"]) - 15);
	$uploaddir = '../';
	switch ($fi_type) {
	  case 'pics':
		  $uploaddir .= 'datoteke/slike/';
		  break;
		case 'docs':
		  $uploaddir .= 'datoteke/dokumenti/';		
		  break;
	}
		
  $uploadfile = $uploaddir . basename($_FILES['file']['name']);
  
	// echo $uploadfile;
	echo "Uploading: $uploadfile<br><br>";
	
  if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
    echo "File is valid, and was successfully uploaded.\n";		
  } else {  	
    echo "File upload failed!\n";
		echo "<pre>";
		echo 'Here is some more debugging info:<br>';
		print_r($_FILES);

		echo "</pre>";
  }

  echo "<br><br>";	
}
		echo "<br>";
		echo "Uploading files to the server's filesystem.<br>";
		echo "Please notice server's PHP max upload filesize restrictions (2Mb usually). Use FTP for larger files!";
}

// --------------------------------------------------------------------
// MODULE: list files - moduleact2, 3
// --------------------------------------------------------------------
function listFiles($type) {
?>
<TABLE cellSpacing=0 cellPadding=0 width=99% border=0>
  <TR valign=bottom>
	  <TD align=left class=main_body><B>MODULE: list files - <?PHP echo $type; ?></B></TD>
		<TD align=right width="1%"><IMG height=14 src="img/endcap-grey.gif" width=14></TD></TR>
	<TR vAlign=bottom><TD colspan=2 bgColor=#cecece><IMG height=1 src="img/pixel.gif" width=1></TD></TR>
</TABLE>
<br>
<script language="javascript">
var old=0;var clss="";
function line_setcolor(num)
{
 if(num!=old)
 {
  if(old!=0) {document.all[old].className=clss;}
  if(num!=0) {clss=document.all[num].className;document.all[num].className='ln_sel';}
  old=num;
 }
}
</script>
<?PHP 
				 $myfont = "<font>";				
				 
				 $directory = "../$type/";
				 $d = dir($directory);
				 $i = 0;
				 echo "<table>";
				 echo "<tr class=\"ln\"><td width=\"250\">Filename</td><td>Size</td><td>Time</td></tr>";
				 while($entry=$d->read()) {
				   if (($entry != "index.php") && ($entry != ".") && ($entry != "..")) {
				 	   $i++;
				     if (($i % 2) == 0) echo "<TR class=\"ln1\" id=\"$entry\" onMouseOver=\"line_setcolor('$entry');\">"; else echo "<TR class=\"ln2\" id=\"$entry\" onMouseOver=\"line_setcolor('$entry');\">";
						 echo "<TD align=\"left\">$myfont<A HREF=\"$directory$entry\">".$entry."</A></TD>";
						 $velikost = filesize($directory . $entry);
						 if ($velikost < 1024) $velikost .= " B "; else $velikost = round($velikost/1024) . " kB ";
						 
						 if (filetype($directory . $entry) != "dir") echo "<TD align=\"right\">$myfont".$velikost."</TD>"; else echo "<TD>" . $myfont ."<FONT COLOR=\"#dddddd\">&lt;dir&gt;</TD>";
						 echo "<TD>". $myfont . date("d-m-y / H:i", filemtime($directory . $entry))."</TD>";
						 echo "</TR>\n"; 
				   }
				 }
				 $d->close(); 
				 echo "</table>";
				 // echo "<TR><TD colspan=\"3\"><hr style=\"height: 1px\" color=\"black\">" . $myfont . "<FONT style=\"font-size: 8pt\" COLOR=\"#dddddd\">IndexDir v1.1 (C) <A HREF=\"mailto:bubi@rutka.net\">Bubi</A>, 2001</TD></TR>"; 
}

if($action == "moduleact2")
{
  listFiles("datoteke/slike"); 
}

if($action == "moduleact3")
{
  listFiles("datoteke/dokumenti"); 
}

?>
