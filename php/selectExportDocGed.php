<?php
/**
 * selectExportDocGed.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011
 * @date       19/01/2011
 * @version    0.1
 * @revision   $0$
 * 
 * @date revision   05/03/2011 -- Export des URI pour chaque enregistrement
 * @date revision   24/04/2011 -- Gère la séléction de colonnes (passée en parametre)
 *
 * Construction des Exports d'Objets
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';

if($arrUtilisateur["success"] == false) {
	die();
}

$format = ( isset($_POST["format"]) ) ? $_POST["format"] : null;
$colonnes = ( isset($_POST["colonnes"]) ) ? $_POST["colonnes"] : null;
//
// Construction de la requete.
//
$requete = "SELECT " . $colonnes . ", "
			. " ( DOC_Visibilite OR com_droit & 8 ) AS Visibilite"
			. ' FROM t_docged_doc'
			. ' LEFT JOIN t_commission_com ON IdCOM = DOC_IdCOM'
			. ' LEFT JOIN tj_droit_com ON idUtilisateur = ' . $arrUtilisateur["IdUSR"] . ' AND tj_droit_com.IdCOM = t_commission_com.IdCOM';

// Construction de la clause WHERE		
$arrClausesWhere = array( " com_droit & 1 " );
if(isset($_POST["selection"])) {
	$arrClausesWhere[] = "IdDoc IN ( " . $_POST["selection"] . " )";
}

// Ajout la clause WHERE à la requète
$requete .= ( " WHERE " . implode(" AND ", $arrClausesWhere) );
$requete .= " ORDER BY DOC_DateCreation DESC";

// Execution de la requete
$result = $dbConnect->query($requete);

// Parcourt les résultats de la requete
if($result !== FALSE) {
	
	if( $format == 'xml' ) {
		$oXMLout = new XMLWriter();
		$oXMLout->openURI(dirname( dirname( $_SERVER['SCRIPT_FILENAME'] ) ) . '/ged/tmp/export.xml');
		$oXMLout->startDocument("1.0", "utf-8");
		$oXMLout->setIndent( true );
		$oXMLout->startElement("objets");
	} else {
		$tmpfname = tempnam("../ged/tmp", "Export");
		$handle = fopen($tmpfname, "w");
		$boolEntete = true;
	}
	$uri = isset($_POST["uri"]) ? $_POST["uri"] : null;

	/* associative array */
	while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {
		if($rec["Visibilite"]) {
			if( $format == 'xml' ) {
				$oXMLout->startElement("item");
				foreach ($rec as $key=>&$value) {
					if( $key != "Visibilite" ) {
						$oXMLout->writeElement( $key, $value );
					}
				}
				if($uri) {
					$oXMLout->writeElement( 'uri', $uri . $rec["IdDoc"] );
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