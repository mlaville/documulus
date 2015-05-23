<?php
/**
 * login.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2013
 * @date       19/10/2011
 * @version    0.3
 * @revision   $1$
 * 
 * @date revision    12/10/2012 -- Securisation acces base de données
 * @date revision    29/01/2013 -- Appel selectUser
 *
 *  Contrôle l'dentitée saisie par le panneau de login
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'connect.inc.php';
include 'foncFichier.php';
include 'selectUser.php';

$success = isset($_POST['loginUsername'], $_POST['loginPassword']);

if($success) {
	// Enregistre le login utilisateur
	$_SESSION['login'] = $_POST['loginUsername']; 
	$_SESSION['pass'] = $_POST['loginPassword']; 
}

$user = $success ? $_SESSION['login'] : null;
$erreur = $success ? null : array( 'reason' => "Identité non Définie");

if($success) {
	$arrUtilisateur = selectUser( $dbConnect, $_SESSION['login'], $_SESSION['pass'] );
} else {
	$arrUtilisateur = selectUser( $dbConnect, null, null );
}

$arrUtilisateur["espaceOccupe"] = size_dir('../ged/');

// Construit la réponse pour le client
$erreur = null;
$donnees = null;

$success = $arrUtilisateur["success"];
if( $success ) {

	$donnees = $arrUtilisateur;
} else {
	$erreur['reason'] = "Erreur d'authentification";
}

// Lecture des parametres
$paramJson = json_decode( file_get_contents("param.json"), true );

$reponse = array(
	"success"=>$success,
	"errors"=>$erreur,
	"user"=>$donnees,
	"societe"=>$paramJson["societe"]
);

header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($reponse), ENT_QUOTES);
?>