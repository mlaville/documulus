<?php
/**
 * loginExterieur.php
 * 
 * @auteur     marc laville
 * @Copyleft 2012
 * @date       05/09/2012
 * @version    0.1
 * @revision   $0$
 * 
 *  Contrôle l'dentitée saisie par le panneau de login d'un domaine extérieur
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'connect.inc.php';

$sqlSelectLog = "SELECT IdUSR, USR_Nom FROM ts_user_usr"
	. " WHERE USR_Mail = '{loginUsername}'"
	. " AND ( USR_Pwd = '{loginPassword}' OR USR_Pwd = PASSWORD('{loginPassword}') )";
	
$requete = str_replace(
				array('{loginUsername}', '{loginPassword}'), 
				array($_POST["loginUsername"], $_POST["loginPassword"]),
				$sqlSelectLog
			);
$result = $dbConnect->query($requete);
$arr = $result->fetchAll(PDO::FETCH_ASSOC);

// Enregistre le login utilisateur
$_SESSION['login'] = $_POST['loginUsername']; 
$_SESSION['pass'] = $_POST['loginPassword']; 

// Construit la réponse pour le client
$erreur = null;
$donnees = null;
$success = ( count($arr) == 1 );
if( $success ) {
	header("Location: ../");
	exit();
} else {
?>
Erreur d'authentification
<?php
}
?>