<?PHP
//---------------------------------------------------------------------
// FILE: plugins.inc.php
// AUTHOR: Klemen Kenda
// DESCRIPTION: plugins file
// DATE: 15/04/2010
// HISTORY:
//---------------------------------------------------------------------

// includes of other plugins in the end of file

//---------------------------------------------------------------------
// PLUGIN: Menu
//---------------------------------------------------------------------

function getLangPageLink($lang, $lang_suffix, $page) {
  // ce page v drugem jeziku ni prazen
	if ($page["pa_title$lang_suffix"] != "") {
    if ($page["pa_uri$lang_suffix"] != "") $link = "/$lang/" . $page["pa_uri$lang_suffix"] . ".html";
   	  else $link = "/$lang/" . $page["id"];
  } else {
	  $SQL = "SELECT * FROM pages WHERE id = " . $page["pa_pid"];
		$result = mysql_query($SQL);
		$page = mysql_fetch_array($result);
		$link = getLangPageLink($lang, $lang_suffix, $page);
	}
		
	return $link;	
}

function getNewsLink($title) {
	$title = trim($title, " \t.!?,-");
	$chars = array("L?", "ÄT", "Ll", "Ä‘", "Ä‡", "L ", "ÄS", "L?", "Ä", "Ä†", " ", "@",    ".", "!", "?", ",", "/", "&", "%", "=", "'"); 
	$newchars = array("s",  "c",  "z",  "d",  "c",  "S",  "C",  "Z",  "D",  "C",  "-", "-na-", "",  "",  "",  "", "-", "-", "", "-", "");   
	$title = str_replace($chars, $newchars, $title);
	$title = strtolower($title);
	
	return $title;
}

function getLangLink($lang, $page, $nid, $rid, $aid, $p) {
  // handle default language
  if ($lang != "sl") $lang_suffix = "_" . $lang;
	  else $lang_suffix = "";	
		
  $uri["news"]["sl"] = "novice";
	$uri["news"]["en"] = "news";

 	$link = getLangPageLink($lang, $lang_suffix, $page);
			
	// ce smo v novici
  if (isset($nid)) {	  
		$SQL = "SELECT * FROM news WHERE id = $nid";
		$result = mysql_query($SQL);
		$line = mysql_fetch_array($result);
		// ce je nastavljen novi naslov, vrnemo link z naslovom, sicer obicajni link na page z vsemi novicami
		if ($line["ne_title$lang_suffix"] != "") $link = "/$lang/" . $uri["news"][$lang] . "/" . $nid . "/" . $p . "/" . getNewsLink($line["ne_title$lang_suffix"]);		
	} elseif (isset($rid)) {
		$SQL = "SELECT * FROM clientreferences WHERE id = $rid";
		$result = mysql_query($SQL);
		$line = mysql_fetch_array($result);
		// ce je nastavljen novi naslov, vrnemo link z naslovom, sicer obicajni link na page z vsemi novicami
		if ($line["re_title$lang_suffix"] != "") $link = "/$lang/" . $uri["references"][$lang] . "/" . $rid . "/" . $p . "/" . getNewsLink($line["re_title$lang_suffix"]);			  
	} elseif (isset($aid)) {
		$SQL = "SELECT * FROM articles WHERE id = $aid";
		$result = mysql_query($SQL);
		$line = mysql_fetch_array($result);
		// ce je nastavljen novi naslov, vrnemo link z naslovom, sicer obicajni link na page z vsemi novicami
		if ($line["ar_title$lang_suffix"] != "") $link = "/$lang/" . $uri["articles"][$lang] . "/" . $aid . "/" . $p . "/" . getNewsLink($line["ar_title$lang_suffix"]);			  	
	}
		
	return $link;
}

function getLink($page) {
  global $lang;
	global $lang_suffix;
	
	if ($page["pa_uri$lang_suffix"] != "") $link = "/$lang/" . $page["pa_uri$lang_suffix"] . ".html";
	  else $link = "/$lang/" . $page["id"];
		
	return $link;
}

function pluginMenu() {
	global $page;
	global $lang;
	global $id;
	
	// $SQL = "SELECT * FROM pages WHERE pa_title <> '' AND pa_pid = " . getAbel($page["id"]) . " AND id <> 1 AND pa_weight < 10000 ORDER BY pa_weight";
	$SQL = "SELECT * FROM pages WHERE pa_title <> '' AND pa_pid = 3 AND id <> 1 AND pa_weight < 10000 ORDER BY pa_weight";
	
	$result = mysql_query($SQL);			
	
	if ($page["id"] == getAbel($page["id"])) $active = "active"; else $active = "";
	$link = "/en/dashboard.html";
	$icon = '<i class="fa fa-dashboard"></i>';
	$HTML .= "<li class=\"$active\"><a href=\"$link\">$icon<span>Dashboard</span></a></li>";
	
	
	while ($line = mysql_fetch_array($result)) {
	  $HTML .= subMenu($line, 1);
	}
	
	return $HTML;
}

function subMenu($page, $level = 2) {
  global $lang_suffix;
  global $id;
	
  $SQL = "SELECT * FROM pages WHERE pa_pid = " . $page["id"] . " AND pa_weight < 10000 ORDER BY pa_weight";	
  $result = mysql_query($SQL); 

  $link = getLink($page);
  
  // icons
  if ($level == 1) {
    switch($page["pa_title"]) {
    	case "Alarms": $icon = '<i class="fa fa-bell-o"></i>'; break;
    	case "Map":  $icon = '<i class="fa fa-map-marker"></i>'; break;
    	case "Exploratory Analysis":  $icon = '<i class="fa fa-th"></i>'; break;
    	case "Predefined Visualizations":  $icon = '<i class="fa fa-area-chart"></i>'; break;
    	case "State Graph": $icon = '<i class="fa fa-sitemap"></i>'; break;
    	case "Custom Visualizations": $icon = '<i class="fa fa-bar-chart"></i>'; break;
    	default: $icon = '<i class="fa fa-calculator"></i>'; break;
    		
    }
  } else $icon = "";
  
	if (mysql_num_rows($result) > 0) {
		$icon .= '<b class="caret pull-right"></b>';
		if (getEnos($page["id"]) == getEnos($id)) $active = "active"; 
		else $active = "";
		$HTML .= "<li class=\"has-sub $active\"><a href=\"javascript:;\">$icon<span>" . $page["pa_title$lang_suffix"] . "</span></a>";
		$HTML .= "<ul class=\"sub-menu\">";
   	
		while ($line = mysql_fetch_array($result)) {
		  $HTML .= subMenu($line, $level + 1);
		}

		$HTML .= "</ul>";
		$HTML .= "</li>";
	} else {
		if ($id == $page["id"]) $active = "active";
		else $active = "";
		// predefined visualizations for Seensy
		if ($page["id"] == 10) {
			$HTML .= "<li class=\"has-sub $active\"><a href=\"javascript:;\">$icon<span>" . $page["pa_title$lang_suffix"] . "</span></a>";
			$HTML .= "<ul class=\"sub-menu\">";
		
			$HTML .= subMenuVisualizations();			

			$HTML .= "</ul>";
			$HTML .= "</li>";
		} else {
			$HTML .= "<li class=\"$active\"><a href=\"$link\">$icon<span>" . $page["pa_title$lang_suffix"] . "</span></a></li>";		
		}
		  
	}
		
	
	return $HTML;
}

function subMenuVisualizations() {
	$SQL = "SELECT * FROM visualizations ORDER BY vi_weight";
	$result = mysql_query($SQL);
	$HTML = "";
	while ($line = mysql_fetch_array($result)) {
		$link = "/en/predefined/" . $line["id"] . "/" . getNewsLink($line["vi_name"]);
		$HTML .= "<li><a href=\"$link\">" . $line["vi_name"] . "</a></li>";
	}
	
	return $HTML;
}

function fillTemplate($template, $args) {
  foreach($args as $key => $val) {
	  $template = str_replace("%" . $key . "%", $val, $template); 
	}	
	
  return $template;
}

// -----------------------------------------------
// PLUGIN: SiteMap
// DESCRIPTION: Izpise sitemap
// -----------------------------------------------

function sitemapRecursion($pid) {
  global $lang;
	global $lang_suffix;
	
  $SQL = "SELECT * FROM pages WHERE pa_title$lang_suffix <> '' AND pa_pid = $pid AND id <> 1 ORDER BY pa_weight";
  $result = mysql_query($SQL);

	$i = 0;
	while($line = mysql_fetch_array($result)) {
    $i++;
		if ($pid == 1) {
		  $b = "<b>";
			$nb = "</b>";
		} else {
		  $b = ""; 
			$nb = "";
		}
		$link = getLangPageLink($lang, $lang_suffix, $line);
		$wholelink = "<a href=\"$link\">";
		$nwholelink = "</a>";
		if (($line["pa_content$lang_suffix"] == "") && ($line["pa_plugin1"] == "") && ($line["pa_plugin2"] == "") && ($line["pa_plugin3"] == "")) {
		  $wholelink = "";
			$nwholelink = "";
		}
  	$lsitemap .= "<li>$b$wholelink" . $line["pa_title$lang_suffix"] . "$nb$nwholelink";					
	  if ($line["id"] != $pid) $lsitemap .= sitemapRecursion($line["id"]);			
		$lsitemap .= "</li>\n";					
	}

	if ($i > 0) {
  	return "<ul class=\"ul-blue\">" . $lsitemap . "</ul>";
	} else {
	  return "";
	}						
}

function pluginSitemap() {
  global $lang;
  $start = 1;
	if ($lang == "en") $start = 74;
	//$sitemap = "<br>";		
	$sitemap .= sitemapRecursion($start);		
  return $sitemap;
}

// -----------------------------------------------
// PLUGIN: Breadcrumb
// DESCRIPTION: Doda breadcrumb
// -----------------------------------------------

function breadCrumb($id, $crumb) {
  global $lang_suffix;
	global $lang;
	global $breadcrumb_next;
	
	if ($id == 3) return "<li><a href=\"/$lang/index.html\">Home</a></li>" . $crumb;	
	
	$SQL = "SELECT * FROM pages WHERE id = $id";
	$result = mysql_query($SQL);
	$line = mysql_fetch_array($result);
	
 	$link_before = "<li><a href=\"" . getLink($line) . "\">";
	$link_after = "</a></li>";	
	
	$crumb = breadCrumb($line["pa_pid"], $breadcrumb_next . $link_before . $line["pa_title$lang_suffix"] . $link_after . $crumb);	
	return $crumb;		
}

function pluginBreadcrumb() {
	global $page;
	global $id;
	global $lang_suffix;
	
	if ($id <= 2) return;
	
	return breadCrumb($page["id"], $crumb);	
}

// -----------------------------------------------
// PLUGIN: TranslateJS
// DESCRIPTION: Doda Google JS API za prevajanje v head
// -----------------------------------------------

function pluginTranslateJS() {
  global $page;
	global $lang;
	
	$HTML = <<<EOJS
	 <script type="text/javascript" src="http://www.google.com/jsapi">
   </script>
   <script type="text/javascript">
    google.load("language", "1");

    function initialize() {
      var text = document.getElementById("div-content").innerHTML;
      google.language.detect(text, function(result) {
        if (!result.error && result.language) {
          google.language.translate(text, result.language, "$lang",
                                    function(result) {
            var translated = document.getElementById("div-content");
            if (result.translation) {
              translated.innerHTML = result.translation;
            }
          });
        }
      });
    }
    google.setOnLoadCallback(initialize);

    </script>
	
EOJS;

  if (($lang == "sl") || ($lang == "en")) $HTML = "";
	return $HTML;
}

// -----------------------------------------------
// PLUGIN: Lang
// DESCRIPTION: Vrne kodo jezika
// -----------------------------------------------

function pluginLang() {
  global $lang;
	return $lang;
}
					
// -----------------------------------------------
// PLUGIN: Keywords
// DESCRIPTION: Doda kljuène besede v HTML
// -----------------------------------------------

function pluginKeywords() {
  global $page;
	return $page["pa_keywords"];
}

// -----------------------------------------------
// PLUGIN: Description
// DESCRIPTION: Doda opis v stran
// -----------------------------------------------

function pluginDescription() {
  global $page;
	return $page["pa_description"];
}

// include ------------------------------------------------------------
include("plugin.api.inc.php");
include("plugin.pilot.inc.php");
include("plugin.maps.inc.php");
include("plugin.user.inc.php");
include("plugin.predefined.inc.php");
?>
