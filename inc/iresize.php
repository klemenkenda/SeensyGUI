<?PHP
// --------------------------------------------------------------------
// FILE: slika_resize.php
// AUTHOR: Klemen Kenda
// DESCRIPTION: Photo from DB
// DATE: 27/11/2005
// HISTORY:
// -------------------------------------------------------------------- 

// includes -----------------------------------------------------------
include("../inc/config.inc.php");
include("../inc/sql.inc.php");

// import variables
import_request_variables("gPC");

if (strpos($f, "image") < 1) exit();
if (!is_numeric($id)) exit();

$result = mysql_query("SELECT * FROM $t WHERE id = $id");
$line = mysql_fetch_array($result);

// naredimo sliko iz baze
$image = ImageCreateFromString($line["$f"]);

// pogledamo dimenzije		
$iX = ImageSX($image);
$iY = ImageSY($image);
		
// sirino preberemo iz parametra size
// za uporabo kasneje - resize po sirini ali po max. stranici
$width = $w;		
		
// nastavimo fiksno sirino		
$y = ($width / $iX) * $iY;
$x = $width;

// maksimalna visina ali visina iz baze
$max_height = 1000;
if ($h != "") $max_height = $h;

$max_height = min($max_height, ImageSY($image));

// ce je slika previsoka
if ($y > $max_height) {
  // nastavimo fiksno sirino		
  $y = $max_height;
  $x = $iX/$iY * $max_height;
} 

//echo $x . $y . ImageSX($image) ."  ---  ";
// kreiramo sliko
$newImage = @ImageCreateTrueColor($x, $y);		
// should be resampled -- server does not support!!!
ImageCopyResampled($newImage, $image, 0, 0, 0, 0, $x, $y, ImageSX($image), ImageSY($image));

header("Content-Type: image/jpeg");
// header('Content-Disposition: attachment; filename="' . $t . $id . '.jpg"');
$myJPEG = ImageJPEG($newImage, "", 97);
header("Content-Length: " . sizeof($myJPEG) . "\n");
echo $myJPEG;		

		
?>
