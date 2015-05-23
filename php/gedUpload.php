<?php
/**
 * gedUpload.php
 * 
 * @auteur     marc laville
 * @Copyleft   2012
 * @date       30/10/2012
 * @version    0.1
 * @revision   $0$
 *  
 *
 * Gere les Upload de document liés
 * 
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';
include 'upload.php';

$nomUser = $arrUtilisateur["user"];
$nomSociete = $arrUtilisateur["societe"];

$stmt = $dbConnect->prepare( "SELECT COM_Repertoire, IFNULL( COM_Path, 'ged/') AS COM_Path"
		. " FROM t_commission_com"
		. " LEFT JOIN tj_droit_com ON tj_droit_com.IdCOM = t_commission_com.IdCOM AND idUtilisateur = ?"
		. " WHERE t_commission_com.IdCOM = ?" );

$stmt->execute( array( $arrUtilisateur["IdUSR"], $_GET["idCommission"] ) );

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$rec = $result[0];

// Calcul du $path
$repertoire = $rec["COM_Repertoire"];
$path = '../' . $rec["COM_Path"];

//Create dir
$succes=true;
if (!is_dir($path . $repertoire)) {
	if( mkdir($path . $repertoire, 0755) ) {
		$succes=false;
		copy( "index.html", $path . $repertoire . "/index.html" );
	}
}

// list of valid extensions, ex. array("jpeg", "xml", "bmp")
$allowedExtensions = array();
// max file size in bytes
$sizeLimit = 100 * 1024 * 1024;

$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
$result = $uploader->handleUpload($path . $repertoire . '/');

$result["succes"] = $succes;
$result["rep"] = $path . $repertoire . '/';

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
?>