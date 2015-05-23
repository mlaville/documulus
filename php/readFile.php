<?php
/**
 * readFile.php
 * 
 * @auteur     marc laville
 * @Copyleft 2012
 * @date       16/11/2012
 * @version    0.01
 * @revision   $0$
 *
 * Chargement d'un fichier
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

$path_parts = pathinfo($filename);
$extension = strtolower($path_parts["extension"]);

switch( $extension )
{
   case "pdf": $ctype="application/pdf"; break;
   case "exe": $ctype="application/octet-stream"; break;
   case "zip": $ctype="application/zip"; break;
   case "doc": $ctype="application/msword"; break;
   case "xls": $ctype="application/vnd.ms-excel"; break;
   case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
   case "gif": $ctype="image/gif"; break;
   case "png": $ctype="image/png"; break;
   case "jpeg":
   case "jpg": $ctype="image/jpg"; break;
   case "mp3": $ctype="audio/mpeg"; break;
   case "wav": $ctype="audio/x-wav"; break;
   case "mpeg":
   case "mpg":
   case "mpe": $ctype="video/mpeg"; break;
   case "mov": $ctype="video/quicktime"; break;
   case "avi": $ctype="video/x-msvideo"; break;
}
	  
//Création des headers, pour indiquer au navigateur qu'il s'agit d'un fichier à télécharger
header("Content-Transfer-Encoding: binary"); //Transfert en binaire (fichier)
//                header("Content-Length: $taille"); //Taille du fichier
header("Content-type: $ctype");
//header("Content-Disposition: attachment; filename= $nom_fichier"); //Nom du fichier
				 
readfile($filename);
?>