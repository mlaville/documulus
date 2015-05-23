<?php
/**
 * readImage.php
 * 
 * @auteur     marc laville
 * @Copyleft 2012
 * @date       16/11/2012
 * @version    0.01
 * @revision   $0$
 *
 * Redimensionnement et chargement d'un fichier image
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';
	
$reqSelectDroit = "SELECT com_droit &1 AS DroitLecture, CONCAT( '../', IFNULL( COM_Path, './ged/' ) , COM_Repertoire, '/' ) AS pathDir"
	. " FROM t_commission_com, tj_droit_com"
	. " WHERE t_commission_com.IdCOM = {IdCOM}"
	. " AND tj_droit_com.IdCOM = t_commission_com.IdCOM"
	. " AND idUtilisateur ={idUtilisateur}";

$reqDroit = str_replace( array( "{idUtilisateur}", "{IdCOM}" ),
					array( $arrUtilisateur["IdUSR"], $_REQUEST["IdCOM"] ),
					$reqSelectDroit );

$result = $dbConnect->query($reqDroit);
$tabRec = $result->fetchAll(PDO::FETCH_ASSOC);

$droit = ( count($tabRec) == 1 );
if($droit) {
	$droit = $tabRec[0]["DroitLecture"] == 1;
}
if(!$droit) {
	die("Accès Interdit");
} else {
	$filename=$tabRec[0]["pathDir"] . $_REQUEST["nomFic"];
}

// Fichier et nouvelle taille
$h=220;
$l=300;

// Calcul des nouvelles dimensions
$size = getimagesize($filename);
list($width, $height) = $size;

$percent = ( ($width / $l) > ($height / $h) ) ? $l / $width : $h / $height;
if($percent > 1) $percent = 1;

$newwidth = $width * $percent;
$newheight = $height * $percent;

header("Pragma: public");
// Content type
header('Content-Type: image/jpeg');

// Chargement
$thumb = imagecreatetruecolor($newwidth, $newheight);
$source = @imagecreatefromstring( file_get_contents($filename) );

// Redimensionnement
imagecopyresized( $thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );

// Affichage
imagejpeg($thumb);
?> 