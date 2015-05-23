<?php
/**
 * formUser.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010
 * @date       21/06/2010
 * @version    0.9.4
 * @revision   $5$
 *
 * @dateRevision 07/10/2010 Gestion des droits utilisateur
 * @dateRevision 20/11/2010 Droits superviseur + num adhérant + visu action
 * @dateRevision 04/10/2010 Gestion de l'identifiant de Commission associée
 * @dateRevision 07/10/2010 Gestion de la valeur NULL dans la rubrique Droit
 * @dateRevision 10/01/2011 Validation des droits sur les commissions pour les gestionnaire d'utilisateurs
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
include 'connect.inc.php';

// Lecture des parametres
$paramJson = json_decode( file_get_contents("param.json"), true );

// Faut-il echapper les apostrophes ?
$escApos = ($paramJson["esc_apostrophe"] > 0);

$reqSqlSelect = "SELECT"
	. " USR_Nom, USR_Prenom, USR_Mail, IFNULL(USR_NumAdherent, '') AS USR_NumAdherent, USR_Pwd, USR_Tel, USR_Fonction,"
	. " USR_Droits & 1 AS droit,"
	. " USR_ZoneGeo IS NOT NULL AS droitActivite,"
	. " USR_ZoneGeo, USR_Comment, USR_IdCom, COM_Libelle"
	. " FROM ts_user_usr LEFT JOIN t_commission_com ON IdCom = USR_IdCom"
	. " WHERE IdUSR = {ident}";
		
$reqSqlUpdateDroits = "UPDATE ts_user_usr SET"
	. " USR_Droits = ( (IFNULL(USR_Droits, 0) >> 1) << 1 ) + {droitGestUser}"
	. " WHERE IdUSR = {ident}";

// Validation des droits utilisateur pour les Dossiers adhérents
$reqSqlReplaceDroitsComm = "REPLACE INTO tj_droit_com (IdCOM, idUtilisateur, com_droit)"
	. " SELECT idCOM, {ident}, 11"
	. " FROM ts_user_usr, t_commission_com"
	. " WHERE ts_user_usr.USR_IdCom = t_commission_com.IdCOM";

$reqSqlUpdate = "UPDATE ts_user_usr SET"
	. " USR_Nom = '{USR_Nom}',"
	. " USR_Prenom = '{USR_Prenom}',"
	. " USR_NumAdherent = '{USR_NumAdherent}',"
	. " USR_Mail = '{USR_Mail}',"
	. " USR_Pwd = '{USR_Pwd}',"
	. " USR_Tel = '{USR_Tel}',"
	. " USR_Fonction = '{USR_Fonction}',"
	. " USR_ZoneGeo = IF('{droitActivite}' = 'on', '{USR_ZoneGeo}', NULL),"
	. " USR_Comment = '{USR_Comment}',"
	. " USR_DateModif = NOW()"
	. " WHERE IdUSR = {IdUSR}";

// get command
$cmd = isset($_REQUEST["cmd"]) ? $_REQUEST["cmd"] : false;

// get identifiant
$identifiant = isset($_REQUEST["identifiant"]) ? $_REQUEST["identifiant"] : false;
 
// no command?
if(!$cmd) {
	echo '{"success":false,"error":"No command"}';
	exit;
}
$o = array(
	"success"=>false,
	"cmd"=>$cmd
);

// load or save?
switch($cmd) {
	case "load":
		$requete = str_replace('{ident}', $_REQUEST["ident"], $reqSqlSelect);
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
	break;
	
	case "droit":
		$requete = str_replace( array('{ident}', '{droitGestUser}'),
								array($_REQUEST["ident"], $_REQUEST["droitGestUser"]),
								$reqSqlUpdateDroits
							   );
		$result = $dbConnect->exec($requete);
		
		if( $_REQUEST["droitGestUser"] > 0 ) {
			$requete = str_replace( '{ident}', $_REQUEST["ident"], $reqSqlReplaceDroitsComm);
			$result = $dbConnect->exec($requete);
		}
		if( $result == false ) {
			$o = array(
				"success"=>false,
				"requete"=>$requete
			);
		} else {
//			$rec = $result->fetch(PDO::FETCH_ASSOC);
			
			// A faire : gestion erreur
			
			// Envoie de la réponse au client
			$o = array(
				"success"=>true,
				"requete"=>$requete
			);
		}
	
	break;
	 
	case "save":
		// si l'identifiant de l'enregistrement est à 0 (zéro), on doit créer un nouvel enregistrement et récupèrer son identifiant
		if($_REQUEST["IdUSR"] == 0) {
			$result = $dbConnect->exec("INSERT INTO ts_user_usr (USR_DateCreation) VALUES (NOW())");
			$_REQUEST["IdUSR"] = $dbConnect->lastInsertId();
		}
		// Prepare les tableau de remplacement pour la requete Update
		foreach ($_REQUEST as $key => $value) {
			$recherche[] = "{".$key."}";
			$remplace[] = $escApos ? str_replace("'", "\'", $value) : $value;
		}
		// Construction et execution de la requete
		$requete = str_replace($recherche, $remplace, $reqSqlUpdate);
		$success = ( $dbConnect->exec($requete) == 1 );
		
		if( $success ) {
			// enregistrement des droits
			$strDroit = isset($_REQUEST["droits"]) ? $_REQUEST["droits"] : "";
			$IdUSR = $_REQUEST["IdUSR"];
			$dbConnect->exec("DELETE FROM tj_droit_com WHERE idUtilisateur = $IdUSR");
			
			if( strlen($strDroit) > 0 ) {
				$tabDroits = explode(';', $_REQUEST["droits"]);
				$arrTuple = array();
				foreach ($tabDroits as $chaine) {
					$couple = explode(',', $chaine);
					$arrTuple[] = "($couple[0], $IdUSR, $couple[1])";
				}
				$dbConnect->exec("INSERT INTO tj_droit_com (IdCOM, idUtilisateur, com_droit) VALUE " . implode ( ", " , $arrTuple ));
			}
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
		
	break;
}

// return response to client
header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
// eof
?>
