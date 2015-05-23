<?php
/**
 * crudChrono.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010
 * @date       31/10/2010
 * @version    0.1.1
 * @revision   $1$
 *
 * @date revision   17/01/2011 Gestion des Erreurs de Chargement
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';

// Faut-il echapper les apostrophes ?
$escApos = ($paramJson["esc_apostrophe"] > 0);

$vbSelect = isset($_POST["vbSelect"]) ? $_POST["vbSelect"] : "";

$reqSqlUpdate = "UPDATE t_chrono_chr SET"
	. " CHR_Comment = '{CHR_Comment}'"
	. " WHERE IdCHR = {IdCHR}";

// get command
$cmd = isset($_REQUEST["cmd"]) ? $_REQUEST["cmd"] : false;

// get identifiant
$identifiant = isset($_REQUEST["identifiant"]) ? $_REQUEST["identifiant"] : false;
 
// no command?
if(!$cmd) {
	echo '{"success":false, "error":"No command"}';
	exit;
}

// load or save?
switch($cmd) {
	case "load":
		$requete = $vbSelect;
		$result = $dbConnect->query($requete);
		if($result == false) {
			$tabErreur = $dbConnect->errorInfo();
			$erreur = $tabErreur[2];
			$o = array(
				"success"=>false,
				"erreur"=>$erreur
			);
		} else {
			// A faire : gestion erreur
			$rec = $result->fetch(PDO::FETCH_ASSOC);
			foreach ($rec as &$value) { } // A supprimer
			
			// Envoie de la réponse au client
			$o = array(
				"success"=>true,
				"data"=>$rec
			);
		}
	break;
	 
	case "save":

		if( $arrUtilisateur["droits"] && 1 ) {
			// Prepare les tableau de remplacement pour la requete Update
			foreach ($_REQUEST as $key => $value) {
				$recherche[] = "{".$key."}";
				$remplace[] = $escApos ? str_replace("'", "\'", $value) : $value;
			}
			// Construction et execution de la requete
			$requete = str_replace($recherche, $remplace, $reqSqlUpdate);
			$success = ( $dbConnect->exec($requete) == 1 );
			if( $success ) {
				$erreur = "Enregistrement effectué";
			} else {
				//  gestion erreur
				$tabErreur = $dbConnect->errorInfo();
				$erreur = $tabErreur[2];
			}
			// Construit la réponse pour le client
			$o = array(
				"success"=>$success,
				"error"=>$erreur
			);
		} else {
			$o = array(
				"success"=>false,
				"error"=>"Problème de Droits"
			);
		}
		
	break;
}

// return response to client
header("Content-Type: application/json");
//if( $cmd == 'save' ) {
//	@header("Content-Type: text/html");
//}
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
// eof
?>
