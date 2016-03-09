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

if (strpos($f, "image") < 1) exit();
if (!is_numeric($id)) exit();

$result = mysql_query("SELECT * FROM $t WHERE id = $id");
$line = mysql_fetch_array($result);

// echo $line["ex_image"];

// echo "Vsebina: $f" . $line["f"];

// naredimo sliko iz baze
$image = ImageCreateFromString($line["$f"]);

// pogledamo dimenzije		
$iX = ImageSX($image);
$iY = ImageSY($image);
		
// sirino preberemo iz parametra size
// za uporabo kasneje - resize po sirini ali po min. stranici
$width = $w;		
		
// nastavimo fiksno sirino		
$y = $iY;
$x = $iX;

$src_x = floor(($x - $w) / 2);
$src_y = floor(($y - $w) / 2);


// ce je slika previsoka
if ($y < $w) {
  // nastavimo fiksno visino		
  $y = $w;
  $x = $iX/$iY * $w;
	
	$src_y = 0;
	$src_x = floor(($x - $w) / 2);
} 

//echo $x . $y . ImageSX($image) ."  ---  ";
		
$newImage = @ImageCreateTrueColor($x, $y);
$newImage2 = @ImageCreateTrueColor($w, $w);
// should be resampled -- server does not support!!!
ImageCopyResampled($newImage, $image, 0, 0, 0, 0, $x, $y, ImageSX($image), ImageSY($image));

//ImageCopy ($newImage2, $image, 0, 0, $src_x, $src_y, $w, $w )
ImageCopy($newImage2, $newImage, 0, 0, $src_x, $src_y, $w, $w);


header("Content-Type: image/jpeg");
//header("Content-Length: ".);		
ImageJPEG($newImage2, "", 90);
		
?>
