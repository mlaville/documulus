<?php
/**
 * controlIdent.php
 * 
 * @auteur     marc laville
 * @Copyleft 2012-2013
 * @date       19/10/2012
 * @version    0.2
 * @revision   $0$
 *
 * @dateRevision  28/01/2013 Appel à selectUser
 *
 * Contrôle l'identite de l'utilisateur
 * Renvoie l'espace occupé
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'connect.inc.php';
include 'foncFichier.php';
include 'selectUser.php';

$success = isset($_SESSION['login'], $_SESSION['pass']);
$erreur = $success ? null : array("reason"=>"Identité non Définie");

if($success) {
//	$user = $success ? $_SESSION['login'] : null;
	$arrUtilisateur = selectUser( $dbConnect, $_SESSION['login'], $_SESSION['pass'] );
} else {
	$arrUtilisateur = selectUser( $dbConnect, null, null );
}

$arrUtilisateur["espaceOccupe"] = size_dir('../ged/');

header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($arrUtilisateur), ENT_QUOTES);
?>