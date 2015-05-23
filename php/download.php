<?php
/**
 * download.php
 * 
 * @auteur     marc laville
 * @Copyleft 2012
 * @date       01/08/2012
 * @version    1.01
 * @revision   $1$
 *
 * @dateRevision  01/08/2012 Prise en charge de l'extention SQL
 *
 * Gere le téléchargement ou la visualisation du document lié
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';

$reqChrono = "INSERT INTO t_chrono_chr (CHR_NomTable, CHR_Ident, CHR_Action, CHR_User, CHR_Date)"
	. " VALUES ('{CHR_NomTable}', '{CHR_Ident}', '{CHR_Action}', '{CHR_User}', NOW( ) )";

$reqSelectDroit = "SELECT com_droit & 1 AS DroitLecture, CONCAT('../', IFNULL( COM_Path, './ged/' ) , COM_Repertoire, '/', DOC_Fic) AS File"
	. " FROM t_docged_doc, t_commission_com, tj_droit_com"
	. " WHERE IdDoc = {IdDoc} AND DOC_IdCOM = t_commission_com.IdCOM  AND tj_droit_com.IdCOM = t_commission_com.IdCOM AND idUtilisateur = {idUtilisateur}";
 
$reqDroit = str_replace( array( "{idUtilisateur}", "{IdDoc}", "{IdCOM}" ),
					array( $arrUtilisateur["IdUSR"], $_REQUEST["idDoc"] ),
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
	$file=$tabRec[0]["File"];
}
/* Validation dans le chrono */
// Mise à jour du chrono
$dbConnect->exec(str_replace(
	array('{CHR_NomTable}', '{CHR_Ident}', '{CHR_Action}', '{CHR_User}'), 
	array('t_docged_doc', $_REQUEST["idDoc"], 'J', $arrUtilisateur["user"]),
	$reqChrono
));

/* Contruction du Content-type */
// Parse Info / Get Extension
$fsize = filesize($file);
$path_parts = pathinfo($file);
$ext = strtolower($path_parts["extension"]);
	
$contentType = "image/png";
switch ($ext) {
    case 'pdf':
        $contentType = "application/pdf";
        break;
    case 'xml':
        $contentType = "application/xml";
        break;
    case 'rtf':
        $contentType = "application/rtf";
        break;
    case 'doc':
        $contentType = "application/msword";
        break;
    case 'js': ;
    case 'css': ;
    case 'sql': ;
    case 'txt':
        $contentType = "text/plain";
        break;
	case "bin": ;
	case "exe": $contentType="application/octet-stream"; break;
	case "zip": $contentType="application/zip"; break;
	case "xls": $contentType="application/vnd.ms-excel"; break;
	case "ppt": $contentType="application/vnd.ms-powerpoint"; break;
	case "gif": $contentType="image/gif"; break;
	case "tiff": ;
	case "tif": $contentType="image/tiff"; break;
	case "png": $contentType="image/png"; break;
	case "jpeg": ;
	case "jpe": ;
	case "jpg": $ctype="image/jpg"; break;
	default: $contentType="application/force-download";
}

$visu=isset( $_GET["visu"] ) ? $_GET["visu"] : 0;
/* Affichage */
header("Pragma: public");
// Gère le téléchargement
if( !$visu or $contentType == "application/force-download" ) {
	header('Content-disposition: attachment; filename='.$path_parts['basename']);
}
header("Content-type: " . $contentType);
header('Content-Transfer-Encoding: binary');

ob_clean();
flush();
readfile($file);
?> 