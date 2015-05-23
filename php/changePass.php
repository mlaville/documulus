<?php
/**
 * changePass.php
 * 
 * @auteur     marc laville
 * @Copyleft 2011
 * @date       30/11/2011
 * @version    0.1
 * @revision   $0$
 * 
 *  Validation du changement de mot de passe
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

$reqUpdatePwd = "UPDATE ts_user_usr"
	. " SET USR_Pwd = PASSWORD('{loginPassword}')"
	. " WHERE IdUSR = {IdUSR}";
	
include 'selectIdent.php';

$reponse	= array("success" => ($arrUtilisateur["success"] != false) );
$arrErreurs	= array();

if( $reponse["success"] ) {

	$reponse["success"] = ( $_SESSION['pass'] == $_POST['loginPassword'] );
	if( $reponse["success"] ) {
	
		$reponse["success"] = ( $_POST['nouveauPassword'] == $_POST['confirmPassword'] );
		if( $reponse["success"] ) {
			$requete = str_replace( array('{IdUSR}', '{loginPassword}' ),
							array( $arrUtilisateur["IdUSR"], $_POST['nouveauPassword'] ),
							$reqUpdatePwd
						);
		
			$dbConnect->query($requete); // Gestion erreur
			$_SESSION['pass'] = $_POST['nouveauPassword']; 
		} else {
			$arrErreurs["reason"] = 'Saisie Invalide';
		}
	} else {
		$arrErreurs["reason"] = "Erreur d'identification";
	}
} else {
	$arrErreurs["reason"] = "Erreur d'identification";
}

header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($reponse), ENT_QUOTES);
?>