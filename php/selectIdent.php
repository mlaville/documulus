<?php
/**
 * selectIdent.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011-2012
 * @date       19/10/2011
 * @version    0.2
 * @revision   $1$
  * 
* @date revision    19/10/2011 -- Codage du password
* @date revision    19/05/2012 -- Passage du nom de la feuille de style
* @date revision    12/10/2012 -- Securisation acces base de données
 * 
 *  Contrôle l'dentitée avant chaque requete en bdd
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'connect.inc.php';
include 'selectUser.php';

$success = isset($_SESSION['login'], $_SESSION['pass']);

$user = $success ? $_SESSION['login'] : null;
$erreur = $success ? null : "Identité non Définie";

if($success) {
	$arrUtilisateur = selectUser( $dbConnect, $_SESSION['login'], $_SESSION['pass'] );
} else {
	$arrUtilisateur = selectUser( $dbConnect, null, null );
}
?>