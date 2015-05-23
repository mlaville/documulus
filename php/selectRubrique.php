<?php
/**
 * selectRubrique.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010 - 2011
 * @date       30/03/2011
 * @version    0.1
 * @revision   $0$
 *
 * Renvoie les Rubriques d'une table
 * 
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';

if($arrUtilisateur["success"] == false) {
	die();
}

$aliasTable = isset($_POST["aliasTable"]) ? $_POST["aliasTable"] : "";
$arr = array();
$nomTable = null;
$erreur = null;

switch($aliasTable) {
	case "user":
		$nomTable = 'ts_user_usr';
		break;
		
	case "action":
		$nomTable = 't_commission_com';
		break;
		
	case "objet":
		$nomTable = 't_docged_doc';
		break;
		
	default:
		break;
}

$requete = 'SHOW COLUMNS FROM ' . $nomTable;
				
$success = is_string($requete);
if($success) {
	$result = $dbConnect->query($requete);

	if($result !== FALSE) {
		/* associative array */
		while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {
			$arr[] = $rec;
		}
	} else {
		$tabErreur = $dbConnect->errorInfo();
		$erreur = $tabErreur[2];
		$success = false;
	}
}

$o = array(
	"success"=>$success,
	"error"=>$erreur,
	"results"=>$arr
);
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
	
?> 