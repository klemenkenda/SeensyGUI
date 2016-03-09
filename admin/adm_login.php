<?PHP

if(!function_exists("template")) exit;

//||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
//   CHECKING AUTHORIZATION DATA
//||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

if (isset($logout)) {
  SetCookie("admin_login", $conf['config']['sitedata']['checkcode'], time() - 3600);
	SetCookie("admin_id", $line["id"], time() - 3600);
	SetCookie("admin_super", 0, time() - 3600);
	unset($admin_login);
}

if(isset($login_sub) && isset($login) && isset($password))
{
    // preverimo user/pass iz baze
		$SQL1 = "SELECT * FROM authors WHERE au_username = '$login' AND au_password = '$password'";
		
		$SQL2 = "SELECT * FROM trainers WHERE tr_username = '".mysql_real_escape_string($login). "' AND tr_password = '".mysql_real_escape_string($password)."'";
  
    if(($password==$conf['config']['sitedata']['password'])&&($login==$conf['config']['sitedata']['login']))
    {
        SetCookie("admin_login", $conf['config']['sitedata']['checkcode'], 0);
				SetCookie("admin_id", $conf['config']['sitedata']['supercheckcode'], 0);
        $admin_login=$conf['config']['sitedata']['checkcode'];
				$admin_id=$conf['config']['sitedata']['supercheckcode'];
        $pass_err=0;
    }
    elseif ($line = mysql_fetch_array(mysql_query($SQL1))) {
		  	SetCookie("admin_login", $conf['config']['sitedata']['checkcode'], 0);
				SetCookie("admin_id", $line["id"], 0);
				if ($line["au_super"] == 1) SetCookie("admin_super", 1);
				$admin_login=$conf['config']['sitedata']['checkcode'];
        $pass_err=0;
    }
    elseif ($line = mysql_fetch_array(mysql_query($SQL2))) {
    	SetCookie("admin_login", $conf['config']['sitedata']['checkcode'], 0);
    	SetCookie("admin_id", $line["id"], 0);
    	SetCookie("admin_super", 2);
		$admin_login = $conf['config']['sitedata']['checkcode'];
		$pass_err=0;
		$admin_super = 2;
    } else {
        toLog($language['login_errortolog'].": ".getenv("REMOTE_ADDR")." / ".$login." / ".$password,"logs/security.log");
        $pass_err=1;
    }
}


// preverimo userja
if ($conf['config']['sitedata']['supercheckcode'] == $admin_id) $superuser = 1;
		else $superuser = 0;

if(!isset($action)) $action="";

//||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
//   PRINTING AUTHORIZATION FORM
//||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||

if($admin_login!=$conf['config']['sitedata']['checkcode'])
{

    $var1['title']=$language['login_title'];
    $var1['css']=template("style.css",$cur_colors,0);
    $var1['sitename']=$conf['config']['sitedata']['name'];

    template("admin_htmlhead",$var1);

    if ($pass_err)
    {
        $var2['error']=$language['login_error'];
    }
    else $var2['error']="";

    $var2['login_name']=$language['login_name'];
    $var2['login_password']=$language['login_password'];
    $var2['login_button']=$language['login_button'];
    $var2['login_title']=$language['login_title'];
    template("admin_login",$var2);

    exit;
}
?>