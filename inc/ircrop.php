<?PHP
// --------------------------------------------------------------------
// FILE: slika_rcrop.php
// AUTHOR: Klemen Kenda
// DESCRIPTION: Photo from DB
// DATE: 27/11/2005
// HISTORY:
// Resize slik na pomoznem strezniku + crop na kvadrat.
// -------------------------------------------------------------------- 


// includes -----------------------------------------------------------
include("../inc/config.inc.php");
include("../inc/sql.inc.php");

// import variables
import_request_variables("gPC");

// check for hackers ;)
if (!is_numeric($id)) exit();
if (!is_numeric($w)) exit();
if (!is_numeric($h)) exit();
// if (!ctype_alnum($t)) exit();
//if (!ctype_alnum($f)) exit();

$result = mysql_query("SELECT * FROM $t WHERE id = $id");
$line = mysql_fetch_array($result);

// naredimo sliko iz baze
$image = ImageCreateFromString($line["$f"]);

// pogledamo dimenzije		
$iX = ImageSX($image);
$iY = ImageSY($image);
		
// sirino preberemo iz parametra size
// za uporabo kasneje - resize po sirini ali po min. stranici
$width = $w;		
		
// nastavimo fiksno sirino		
$y = ($width / $iX) * $iY;
$x = $width;

$src_x = 0;

// malo hekanja glede visine
$src_y = floor(($iY - $y) / 2);
if ($src_y < 0) $src_y = 0;


// ce je slika previsoka
if ($y < $h) {
  // nastavimo fiksno visino		
  $y = $h;
  $x = $iX/$iY * $h;
	
	$src_y = 0;
	$src_x = floor(($x - $h) / 2);
} 

//echo $x . $y . ImageSX($image) ."  ---  ";
		
$newImage = @ImageCreateTrueColor($x, $y);
$newImage2 = @ImageCreateTrueColor($w, $h);
// should be resampled -- server does not support!!!
ImageCopyResampled($newImage, $image, 0, 0, 0, 0, $x, $y, ImageSX($image), ImageSY($image));

ImageCopy($newImage2, $newImage, 0, 0, $src_x, $src_y, $w, $h);


header("Content-Type: image/jpeg");
//header("Content-Length: ".);		
ImageJPEG($newImage2, "", 90);
		
?>
