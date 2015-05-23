<?php
/**
 * formDemande.php
 * 
 * @auteur     marc laville
 * @Copyleft 2011-2012
 * @date       03/05/2011
 * @version    0.3
 * @revision   $2$
 *
 * @date_revision  24/01/2012 : marc laville -  ajout rubrique GSM
 * @date_revision  14/10/2012 : marc laville -  Contrôle la saisie du captcha
 *
 * Enregistrement des demandes de login
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
include 'connect.inc.php';

if( $_SESSION['Captcha'] == $_POST['captcha'] ) {
	// Lecture des parametres
	$paramJson = json_decode( file_get_contents("param.json"), true );

	// Faut-il echapper les apostrophes ?
	$escApos = ($paramJson["esc_apostrophe"] > 0);

	$reqSqlInsert = "INSERT INTO t_demande_dmd"
		. " ( DMD_Nom, DMD_Prenom, DMD_Mail, DMD_Tel, DMD_GSM, DMD_Comment, DMD_DateCreation )"
		. " VALUES ( '{DMD_Nom}', '{DMD_Prenom}', '{DMD_Mail}', '{DMD_Tel}', '{DMD_GSM}', '{DMD_Comment}', NOW( ) )";

	// Prepare les tableau de remplacement pour la requete Update
	foreach ($_POST as $key => $value) {
		$recherche[] = "{".$key."}";
	//	$remplace[] = $escApos ? str_replace("'", "\'", $value) : $value;
		$remplace[] = $escApos ? addslashes($value) : $value;
	}

	// Construction et execution de la requete
	$requete = str_replace($recherche, $remplace, $reqSqlInsert);
	$success = ( $dbConnect->exec($requete) == 1 );

	$IdDMD = NULL;
	$erreur = NULL;
	$mailEnvoi = NULL;

	if( $success ) {
		$IdDMD = $dbConnect->lastInsertId();
		
		$parseUrl = parse_url($_SERVER['HTTP_REFERER']);
		$message = "Nouvelle Demande : " . $_POST['DMD_Mail'] . " - "
			. $parseUrl['scheme'] . "://" . $parseUrl['host'] . $parseUrl['path'] . "?tb=dmd&id=" . $IdDMD;
		$destinataire = $paramJson["mail_demandeLogin"];
		$sujet = 'Demande de Login BàO';
		
		if( @mail( $destinataire, $sujet, $message ) ) {
			$mailEnvoi = $destinataire . " : Succes";
		} else {
			$mailEnvoi = $destinataire . " : Erreur";
		}

	} else {
		// Gestion erreur
		$erreur = $requete;
	}

	$reponse = array(
		"success"=>$success,
		"mailEnvoi"=>$mailEnvoi,
		"error"=>$erreur
	);
} else {
	$reponse = array(
		"success"=>false,
		"error"=>"CAPTCHA"
	);
}
// return response to client
header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($reponse), ENT_QUOTES);
// eof
?>
