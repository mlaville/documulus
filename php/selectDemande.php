<?php
/**
 * selectDemande.php
 * 
 * @auteur     marc laville
 * @Copyleft 2011
 * @date       01/05/2011
 * @version    0.1
 * @revision   $0$
 *
 * Enregistrement des demandes de login
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
include 'connect.inc.php';

// Lecture des parametres
$paramJson = json_decode( file_get_contents("param.json"), true );

// Faut-il echapper les apostrophes ?
$escApos = ($paramJson["esc_apostrophe"] > 0);

$reqSqlSelect = "SELECT DMD_Nom, DMD_Prenom, DMD_Mail, DMD_Tel, DMD_IdUSR_FK, DMD_Comment, DMD_DateCreation"
		. " FROM t_demande_dmd"
		. " WHERE IdDMD = {ident}";

// Construction et execution de la requete
$requete = str_replace('{ident}', $_POST["ident"], $reqSqlSelect);
$result = $dbConnect->query($requete);

if( $result == false ) {
	$o = array(
		"success"=>false,
		"requete"=>$requete
	);
} else {
	$rec = $result->fetch(PDO::FETCH_ASSOC);
	
	// A faire : gestion erreur
	
	// Envoie de la réponse au client
	$o = array(
		"success"=>true,
		"data"=>$rec,
		"requete"=>$requete
	);
}

// return response to client
header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
// eof
?>
