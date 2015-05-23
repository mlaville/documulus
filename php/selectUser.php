<?php
function selectUser( $conDb, $login, $pass ) {

//	$success = isset($_SESSION['login']) && isset($_SESSION['pass']);
	$success = ( $login != null );

	$user = $login;
	$erreur = $success ? null : array( "reason" => "Identité non Définie");
	$IdUSR = null;
	$droits = null;
	$nbDroitAction = null;
	$zoneGeo = null;

	if( $success ) {
		$sqlSelectUser = "SELECT IdUSR, USR_Nom, USR_Mail, IFNULL(USR_Droits, 0) * 2 + IF(USR_ZoneGeo IS NULL, 0, 1) AS USR_Droits, USR_ZoneGeo"
			. " FROM ts_user_usr"
			. " WHERE USR_Mail = ? AND ( USR_Pwd = ? OR USR_Pwd = PASSWORD( ? ) )";
		
		$stmt = $conDb->prepare($sqlSelectUser);
		$stmt->execute( array($user, $pass, $pass) );

		$arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		// Contrôle l'existance et l'unicité de l'identité
		$success = ( count($arr) == 1 );
		if( $success ) {
			$donnees = $arr[0];
			$IdUSR= $arr[0]["IdUSR"];
			$user= $arr[0]["USR_Mail"];
			$droits= $arr[0]["USR_Droits"];
			$zoneGeo= $arr[0]["USR_ZoneGeo"];
			
			$requete = "SELECT COUNT( * ) AS NbDroits"
				. " FROM tj_droit_com"
				. " WHERE com_droit &8"
				. " AND idUtilisateur = " . $IdUSR;
			
			$result = $conDb->query($requete);

			/* associative array */
			@$rec = $result->fetch(PDO::FETCH_ASSOC);
			$nbDroitAction = $rec["NbDroits"];
			
		} else {
			$erreur = "Erreur d'authentification";
		}
	}
	// Lecture des parametres
	$paramJson = json_decode( file_get_contents("param.json"), true );

	// Construit la réponse pour le client
	$arrUtilisateur = array(
		"success"=>$success,
		"errors"=>$erreur,
		"IdUSR"=>$IdUSR,
		"user"=>$user,
		"droits"=>$droits,
		"style"=>(isset($paramJson["style"])) ? $paramJson["style"] : null,
		"nbDroitAction"=>$nbDroitAction,
		"societe"=>$paramJson["societe"],
		"paramJson"=>$paramJson,
		"zoneGeo"=>$zoneGeo
	);

	return $arrUtilisateur;
}
?>