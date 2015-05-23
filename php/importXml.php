<?php
/*
 * importXml.php
 * @auteur     marc laville
 * @Copyleft 2011
 * @date       22/06/2011
 * @version    0.1
 * @revision   $0$
 * 
 * @date revision    
 * 
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';

if($arrUtilisateur["success"] == false) {
	die();
}
if($arrUtilisateur["droits"] < 3) {
	die();
}

function xml2array( $xmlObject, $out = array () ) {
	foreach ( (array) $xmlObject as $index => $node )
		$out[$index] = ( is_object( $node ) ) ? xml2array( $node ) : $node;

	return $out;
}

function colonneTable( $unNomTable, $connect ) {

	$result = $connect->query("SHOW COLUMNS FROM $unNomTable");

	$arr = array();
	/* associative array */
	while( @$rec = $result->fetch(PDO::FETCH_ASSOC) ) {
		 $arr[] = $rec["Field"];
	}

	return $arr;
}

$repDest = "../ged/tmp";
if( !is_dir( $repDest ) ) {
	mkdir( $repDest );
}

// Place le doc télécharge dans le répertoire Docs
$nomFic = $_FILES["xml-path"]['name'];
if(strlen($nomFic)) {
	$isMove = move_uploaded_file( $_FILES['xml-path']['tmp_name'], $repDest . "/" . $nomFic );
} 

$success = false;
$erreur = null;
$retour = array();

$table = ( isset($_POST["table"]) ) ? $_POST["table"] : null;
//
//
$nomTable = null;
$ident = null;
switch( $table ) {
	case 'objets' : 
		$nomTable = 't_docged_doc';
		$ident = 'IdDoc';
	break;

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

if (file_exists($repDest . "/" . $nomFic)) {

	$arrCol = colonneTable( $nomTable, $dbConnect);
	
    $xmlObject = simplexml_load_file($repDest . "/" . $nomFic);
	
	$tab = xml2array( $xmlObject );
	$list = $tab['item'];
	foreach ($list as $rec) {
		$tabAttrib = array();
		$tabVal = array();
		foreach ($rec as $key => $value) {
			if( in_array($key, $arrCol) ) {
				if( $key != $ident OR strlen($value) ) {
					$tabAttrib[] = $key;
					$tabVal[] = "'$value'";
				}
			}
		}
		$dbConnect->query("REPLACE INTO " . $nomTable . " ( " . implode( ', ' , $tabAttrib ) . ' ) VALUES ( ' . implode( ', ' , $tabVal ) . " )");
		$retour[] = "REPLACE INTO " . $nomTable . " ( " . implode( ', ' , $tabAttrib ) . ' ) VALUES ( ' . implode( ', ' , $tabVal ) . " )";
	}
	$success = true;

} else {
    $erreur = "Failed to open " . $_FILES["xml-path"]['name'];
}

$o = array(
	"success"=>$success,
	"error"=>$erreur,
	"retour"=>$retour	
);

echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);

?>