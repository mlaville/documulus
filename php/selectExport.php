<?php
/**
 * selectExport.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011
 * @date       31/05/2011
 * @version    0.1
 * @revision   $0$
 * 
 *
 * Construction des Exports generiques : Users, Commission
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';

if($arrUtilisateur["success"] == false) {
	die( '{"success": false, "errors": "Erreur d\'authentification"}' );
}
if($arrUtilisateur["droits"] <= 3) {
	die( '{"success": false, "errors": "Pas de Droits Export"}' );
}

$table = ( isset($_POST["table"]) ) ? $_POST["table"] : null;
$format = ( isset($_POST["format"]) ) ? $_POST["format"] : null;
$colonnes = ( isset($_POST["colonnes"]) ) ? $_POST["colonnes"] : null;
//
// Construction de la requete.
//
$nomTable = null;
$ident = null;
switch( $table ) {
	case 'users' : 
		$nomTable = 'ts_user_usr';
		$ident = 'IdUSR';
	break;
	
	case 'actions' : 
		$nomTable = 't_commission_com';
		$ident = 'IdCOM';
	break;
	
	default : 
	break;
}

$requete = "SELECT " . $colonnes . ' FROM ' . $nomTable;

// Construction de la clause WHERE		
if(isset($_POST["selection"])) {
	$arrClausesWhere[] = $ident . " IN ( " . $_POST["selection"] . " )";
}

// Ajout la clause WHERE à la requète
$requete .= ( " WHERE " . implode(" AND ", $arrClausesWhere) );

// Execution de la requete
$result = $dbConnect->query($requete);

// Parcourt les résultats de la requete
if($result !== FALSE) {
	
	if( $format == 'xml' ) {
		$oXMLout = new XMLWriter();
		$oXMLout->openURI(dirname( dirname( $_SERVER['SCRIPT_FILENAME'] ) ) . '/ged/tmp/export.xml');
		$oXMLout->startDocument("1.0", "utf-8");
		$oXMLout->setIndent( true );
		$oXMLout->startElement("users");
	} else {
		$tmpfname = tempnam("../ged/tmp", "Export");
		$handle = fopen($tmpfname, "w");
		$boolEntete = true;
	}
	$uri = isset($_POST["uri"]) ? $_POST["uri"] : null;

	/* associative array */
	while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {
		if( $format == 'xml' ) {
			$oXMLout->startElement("item");
			foreach ($rec as $key=>&$value) {
				$oXMLout->writeElement( $key, $value );
			}
			if($uri) {
				$oXMLout->writeElement( 'uri', $uri . $rec[$ident] );
			}
			$oXMLout->endElement();
		} else {
			if($boolEntete) {
				$arr = array_keys($rec) ;
				array_pop($arr);
				fwrite( $handle, '"' . implode('","', $arr) . '"' );
				fwrite( $handle, "\n");
				$boolEntete = false;
			}
			$arr = array_values($rec) ;
			array_pop($arr);
			fwrite( $handle, '"' . str_replace( "\r\n", '\n', implode('","', $arr) ) . '"' );
			fwrite( $handle, "\n");
		}
	}
	if( $format == 'xml' ) {
		$oXMLout->endElement();
		$oXMLout->outputMemory();
	} else {
		fclose($handle);
		$nomFic = basename( $tmpfname );
		rename("../ged/tmp/$nomFic", "../ged/tmp/export.csv");
	}
	echo htmlspecialchars_decode(json_encode( array( "success"=>TRUE ) ), ENT_QUOTES);
}
?>