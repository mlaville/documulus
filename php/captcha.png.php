<?php
/**
 * captcha.png.php
 * 
 * @auteur     marc laville
 * @Copyleft 2012
 * @date       14/10/2012
 * @version    0.1
 * @revision   $0$
 *
 * Calcul l'image du Captcha
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
session_start();

header("Content-type: image/png");

$img = imagecreate (50,15) or die ("Problème de création GD");
$background_color = imagecolorallocate ($img, 200, 200, 200);
$ecriture_color = imagecolorallocate($img, 0, 0, 0);
imagestring ($img, 20, 4, 0, $_SESSION['Captcha'] , $ecriture_color);
imagepng($img);

?>