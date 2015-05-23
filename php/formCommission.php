<?php
/**
 * formCommission.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011-2012
 * @date       08/07/2010
 * @version    0.9.5
 * @revision   $7$
 *
 * @date_revision   27/10/2010 Gestion du champ commentaire
 * @date_revision   04/12/2010 Création à partir des données d'un adhérent (déclenché depuis la fiche user)
 * @date_revision   09/01/2011 Validation des droits sur l'ensemble des gestionnaires d'utilisateur, pour les dossiers adhérents
 * @date revision   20/02/2011 -- Passage de la sélection en Commission
 * @date revision   02/07/2011 -- Validation de la coche "photo"
 * @date revision   29/07/2011 -- contrôle l'existance de $_POST["droits"] avant de valider les droits
 * @date revision   30/10/2012 -- Calcul l'affichage espace occupé
 *
 * - A Faire : controle des droits
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
include 'selectIdent.php';

include 'foncFichier.php';

if($arrUtilisateur["success"] == false) {
	die('{"success":false,"error":"Erreur Identification"}');
}

function remove_accent($str) {
  $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î',
             'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß',
             'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î',
             'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā',
             'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď',
             'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ',
             'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ',
             'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ',
             'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ',
             'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ',
             'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ',
             'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż',
             'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ',
             'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
             
  $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I',
             'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's',
             'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i',
             'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a',
             'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd',
             'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g',
             'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
             'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l',
             'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R',
             'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't',
             'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y',
             'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I',
             'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
  return str_replace($a, $b, $str);
}

// Lecture des parametres
$paramJson = json_decode( file_get_contents("param.json"), true );
// Faut-il echapper les apostrophes ?
 $escApos = ($paramJson["esc_apostrophe"] > 0);
 $repPhoto = $paramJson["rep_photo"];

$reqSqlSelect = "SELECT"
	. " IdCOM, COM_Libelle, COM_Repertoire, COM_Path IS NOT NULL AS photo, COM_Comment,"
	. " CONCAT('../', IFNULL( COM_Path, './ged/' ) , COM_Repertoire) AS Path"
	. " FROM t_commission_com"
	. " WHERE IdCOM = {IdCOM}";
	
$reqSqlSelectAdher = "SELECT USR_Nom, USR_NumAdherent FROM ts_user_usr WHERE IdUSR = {IdUSR}";

$reqSqlUpdate = "UPDATE t_commission_com SET"
	. " COM_Libelle = '{COM_Libelle}',"
	. " COM_Repertoire = '{COM_Repertoire}',"
	. " COM_Comment = '{COM_Comment}',"
	. " COM_Path = IF('{photo}' = 'on', '" . $repPhoto . "', NULL),"
	. " COM_dateModif = NOW()"
	. " WHERE IdCOM = {IdCOM}";

$sqlInsertComm = "INSERT INTO t_commission_com (COM_DateCreation, COM_CreePar) VALUES (NOW(), '{COM_CreePar}')";

$sqlInsertCommAdh = "INSERT INTO t_commission_com"
	. " (COM_Libelle, COM_Repertoire, COM_DateCreation)"
	. " VALUES ('{adherent}', '{repAdherant}', NOW())";

$sqlInsertCommSelection = "INSERT INTO t_commission_com( COM_Libelle, COM_Repertoire, COM_dateCreation, COM_CreePar )"
	. " SELECT CONCAT( 'SEL_', LPAD( IFNULL( MAX( SUBSTRING_INDEX( COM_Libelle, 'SEL_', -1 ) ) , 0 ) +1, 5, '0' ) ) ,"
 	. " CONCAT( 'SEL_', LPAD( IFNULL( MAX( SUBSTRING_INDEX( COM_Libelle, 'SEL_', -1 ) ) , 0 ) +1, 5, '0' ) ) ,"
 	. " NOW( ), '{COM_CreePar}'"
	. " FROM t_commission_com"
	. " WHERE COM_Libelle LIKE 'SEL_%'";
	
$sqlInsertDocSelection = "INSERT INTO t_docged_doc ("
	. " DOC_Libelle, DOC_Fic,"
	. " DOC_Etat, DOC_Visibilite, DOC_IdCOM, DOC_Descriptif, DOC_MotClef, DOC_Nature, DOC_Tiers,"
	. " DOC_DateEcheance, DOC_LibEcheance, DOC_DateFinEcheance, DOC_LibFinEcheance,"
	. " DOC_InfoComplementaires, DOC_DateCreation, DOC_CreePar, DOC_DateModif"
	. " )"
	. " SELECT DOC_Libelle, DOC_Fic,"
	. " DOC_Etat, 1 AS DOC_Visibilite,"
	. " {DOC_IdCOM},"
	. " DOC_Descriptif, DOC_MotClef , DOC_Nature, DOC_Tiers ,"
	. " DOC_DateEcheance, DOC_LibEcheance, DOC_DateFinEcheance, DOC_LibFinEcheance,"
	. " DOC_InfoComplementaires,"
	. " DOC_DateCreation, DOC_CreePar, NOW() AS DOC_DateModif"
	. " FROM t_docged_doc"
	. " WHERE IdDoc = {IdDoc}";

$sqlInsertChronoSelection = "INSERT INTO t_chrono_chr ( CHR_NomTable, CHR_Ident, CHR_Action, CHR_User, CHR_Date, CHR_Comment )"
	. " VALUES ( 't_docged_doc', {CHR_Ident}, 'D', '{CHR_User}', NOW(), '{CHR_Comment}')";

// get command
$cmd = isset($_REQUEST["cmd"]) ? $_REQUEST["cmd"] : false;

// no command?
if(!$cmd) {
	echo '{"success":false,"error":"No command"}';
	exit;
}

// load or save?
switch($cmd) {
	/*
	  * Lecture d'un enregistrement à partir de son identifiant
	  */
	case "load":
		$requete = str_replace("{IdCOM}", $_REQUEST["Ident"], $reqSqlSelect);

		$result = $dbConnect->query($requete);
		if( $result == false ) {
			// A faire : gestion erreur
		} else {
			$rec = $result->fetch(PDO::FETCH_ASSOC);
			$rec["taille"] = size_dir($rec["Path"] . "/");
			
			// Envoie de la réponse au client
			$retour = array(
				"success"=>true,
				"txtMail"=>"\n" . $_SERVER['HTTP_HOST'] . "?tb=ged&id=" . $_REQUEST["Ident"],
				"data"=>$rec
			);
		}
	break;
	 
	/*
	  * Creation d'un enregistrement à partir d'une fiche adhérent
	  */
	case "createFromUser":
		// Contrôle la validité de l'identifiant utilisateur
		$identUser = isset($_REQUEST["identUser"]) ? $_REQUEST["identUser"] : 0;
		if($identUser) {
			// Lecture des données de l'adhérent
			$requete = str_replace("{IdUSR}", $identUser . '', $reqSqlSelectAdher);
			$result = $dbConnect->query($requete);
			if( $result == false ) {
				// A faire : gestion erreur
				$retour = array(
					"success"=>false,
					"identUser"=>$identUser,
					"requete"=>$requete
				);

			} else {
				$rec = $result->fetch(PDO::FETCH_ASSOC);
				$nomAdh = $rec["USR_Nom"];
				$numAdh = $rec["USR_NumAdherent"];
				
				if(strlen($numAdh)) {
					$libCommission = $numAdh . '_' . $nomAdh;
					$reqSqlInsertCommAdh = str_replace(array('{adherent}', '{repAdherant}'),
										   array( $libCommission, str_replace( ' ', '_', remove_accent($libCommission) ) ),
										   $sqlInsertCommAdh);
				
					$success = ( $dbConnect->exec($reqSqlInsertCommAdh) == 1 );
					if($success) {
						//  Enregistrement des droits utilisateurs sur la commission
						$idCOM = $dbConnect->lastInsertId();
						$utilisateur = $arrUtilisateur['IdUSR'];
						$reqSqlInsertDroit= "INSERT INTO tj_droit_com (IdCOM, idUtilisateur, com_droit) VALUES ($idCOM, $identUser, 4)";
						$countInsertDroit =  $dbConnect->exec($reqSqlInsertDroit);
						$success = ( $countInsertDroit > 0 );
						if($success) {
							$reqSqlInsertDroit= "REPLACE INTO tj_droit_com (IdCOM, idUtilisateur, com_droit)"
										. " SELECT $idCOM, IdUSR, 11"
										. " FROM ts_user_usr"
										. " WHERE USR_Droits &1";
							$success = ( $dbConnect->exec($reqSqlInsertDroit) !== false );
						}
						if($success) {
							$reqSqlUpdateAdh = "UPDATE ts_user_usr SET USR_IdCom = $idCOM WHERE IdUSR = $identUser";
							$success = ( $dbConnect->exec($reqSqlUpdateAdh) == 1 );
						} else {
							
						}
					}
				
					// Envoie de la réponse au client
					$retour = array(
						"success"=>$success,
						"data"=>$libCommission,
						"reqSqlUpdateAdh"=>$reqSqlUpdateAdh,
						"reqSqlInsertDroit"=>$reqSqlInsertDroit
					);
				} else {
					// Envoie de la réponse au client
					$retour = array(
						"success"=>false,
						"numAdh"=>$numAdh
					);
				}
			}
		} else {
			$retour = array(
				"success"=>false,
				"reason"=>"Utilisateur Invalide"
			);
		}
	
	break;
	 
	case "createFromSelection":
		/* Vérifie le Compte de la sélection */
		$sqlSelection = "SELECT tj_selection_doc.IdDOC, COM_Repertoire, DOC_Fic"
			. " FROM tj_selection_doc, t_docged_doc LEFT JOIN t_commission_com ON t_commission_com.IdCOM = DOC_IdCOM"
			. " WHERE tj_selection_doc.IdDOC = t_docged_doc.IdDOC AND IdUSR = " . $arrUtilisateur["IdUSR"];
		$resultId = $dbConnect->query( $sqlSelection );
		$tabObj = $resultId->fetchAll(PDO::FETCH_ASSOC);
		
		if(count($tabObj)) {
			/* Cree la Commision */
			$result = $dbConnect->exec( str_replace("{COM_CreePar}", $arrUtilisateur["user"], $sqlInsertCommSelection ) );
			$idCOM = $dbConnect->lastInsertId();
			
			$utilisateur = $arrUtilisateur['IdUSR'];
			$reqSqlInsertDroit= "INSERT INTO tj_droit_com (IdCOM, idUtilisateur, com_droit) VALUES ($idCOM, $utilisateur, 11)";
			$countInsertDroit =  $dbConnect->exec($reqSqlInsertDroit);
			$successDroit = ( $countInsertDroit > 0 );
						
			/* Recupere le nom du repertoire et creation */
			$resultRep = $dbConnect->query( "SELECT COM_Repertoire FROM t_commission_com WHERE IdCOM = " . $idCOM );
			$repCom = $resultRep->fetchAll(PDO::FETCH_COLUMN, 0);
			$repDest = "../ged/" . $repCom[0];
			if( !is_dir( $repDest ) ) {
//				mkdir( $repDest );
				mkdir( $repDest, '0755' );

			}
				
			/* transfere les Objets dans la nouvelle Commission */
			$nbCopies = 0;
			while (list($key, $val) = each($tabObj)) {
				$requeteDoc = str_replace(array( "{IdDoc}", "{DOC_IdCOM}" ),
										  array( $val["IdDOC"], $idCOM ),
										  $sqlInsertDocSelection);
				$dbConnect->exec( $requeteDoc );
				$idDoc = $dbConnect->lastInsertId();
				
				// Duplication du doc joint
				$ficOrigine = $val["DOC_Fic"];
				$nbCopies += ( @copy( "../ged/" . $val["COM_Repertoire"] . "/" . $ficOrigine, $repDest . "/" . $ficOrigine ) ) ? 1 : 0;;
				
				// Enregistrement du chrono
				$uri = explode ( 'php/' , $_SERVER['REQUEST_URI'] );
				$requeteChrono = str_replace( array( "{CHR_Ident}", "{CHR_User}" , "{CHR_Comment}" ),
										  array(
											$idDoc,
											$arrUtilisateur["user"],
											'Origine : http://' . $_SERVER['HTTP_HOST'] . $uri[0] . '?tb=ged&id=' . $val["IdDOC"] 
										  ),
										  $sqlInsertChronoSelection) ;
				$dbConnect->exec( $requeteChrono );
							
//				$referer =  $parseUrl['host'] . $parseUrl['path'];
//				@mail($paramJson["courriel"], 'Création Action ',  "\nidCOM : $idCOM\n" . "$_SERVER['HTTP_HOST']$uri[0]?tb=ged&id=$idCOM"  );
			}
			$retour = array( 
				"success"=>true, 
				"idCOM"=>$idCOM,
				"txtMail"=>"\nidCOM : $idCOM\n" . $_SERVER['HTTP_HOST'] . $uri[0] . "?tb=ged&id=$idCOM",
//				"HTTP_HOST"=>$_SERVER['HTTP_HOST'],
				"successDroit"=>$successDroit,
				"nbCopies"=>$nbCopies,
				"repDest"=>$repDest
			);

		} else {
			$retour = array(
				"success"=>false, 
				"reason"=>"sélection vide"
			);
		}

	break;
	 
	case "save":
		// si l'identifiant de l'enregistrement est à 0 (zéro), on doit créer un nouvel enregistrement et récupèrer son identifiant
		if($_REQUEST["IdCOM"] == 0) {
			$result = $dbConnect->exec( str_replace("{COM_CreePar}", $arrUtilisateur["user"], $sqlInsertComm ) );
			$_REQUEST["IdCOM"] = $dbConnect->lastInsertId();
		}
		// Prepare les tableau de remplacement pour la requete Update
		foreach ($_REQUEST as $key => $value) {
			$recherche[] = "{".$key."}";
			$remplace[] = $escApos ? addslashes($value) : $value;
		}
		// Construction et execution de la requete
		$requete = str_replace($recherche, $remplace, $reqSqlUpdate);
		$success = ( $dbConnect->exec($requete) == 1 );
		
		if( $success ) {
			$erreur = "Enregistrement effectué";
			
			// On contrôle l'existance de $_POST["droits"] avant de valider les droits
			if( isset($_POST["droits"]) ) {
				// enregistrement des droits
				$idCOM = $_REQUEST["IdCOM"];
				$dbConnect->exec("DELETE FROM tj_droit_com WHERE IdCOM = $idCOM");
				
				if( strlen($_POST["droits"]) ) {
					$tabDroits = explode(';', $_POST["droits"]);
					for($i=0 ; $i < count($tabDroits) ; $i++) {
						$couple = explode(',', $tabDroits[$i]);
						$dbConnect->exec("INSERT INTO tj_droit_com (IdCOM, idUtilisateur, com_droit) VALUES ($idCOM, $couple[0], $couple[1])");
					}
				}
			}
		} else {
			//  gestion erreur
			$tabErreur = $dbConnect->errorInfo();
			$erreur = $tabErreur[2];
		}
		// Construit la réponse pour le client
		$retour = array(
			"success"=>$success,
			"error"=>$erreur
		);
		
	break;
}

// return response to client
@header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($retour), ENT_QUOTES);
// eof
?>