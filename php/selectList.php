<?php
/**
 * selectList.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010 - 2011 - 2012
 * @date       02/08/2010
 * @version    0.4.3
 * @revision   $8$
 *
 * @date revision    12/09/2010 -- Liste chrono
 * @date revision    29/10/2010 -- Commentaire chrono
 * @date revision    31/10/2010 -- Commentaire sur Commision et Users
 * @dateRevision    20/11/2010 -- Droits superviseur 
 * @dateRevision    25/11/2010 -- Droits sur visu des commissions 
 * @dateRevision    28/03/2011 -- Affichages des commissions pour tous les utilisateurs
 * @date revision   23/10/2012 -- Affichage de la colonne n° adhérent dans la liste de droits sur les commisions
 * @date revision   17/11/2012 -- Calcul de chrono "J"
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';

if($arrUtilisateur["success"] == false) {
	die();
}

$nomListe = isset($_POST["nomListe"]) ? $_POST["nomListe"] : "";
$arr = array();
$requete = null;
$erreur = null;

switch($nomListe) {
	case "commission":
		$arrClausesWhere = array();

		// Sélection sur la valeur tfRecherche
		if( !($arrUtilisateur["droits"] > 2) ) {
			$arrClausesWhere[] = "com_droit &2";
		}
		if(isset($_POST["tfRecherche"])) {
			$listChamps = " COM_Libelle, COM_Repertoire, COM_Comment";
			$arrClausesWhere[] = " LCASE( CONCAT_WS( '\n', "
				. $listChamps
				. " ) ) LIKE '%" . strtolower( $_POST["tfRecherche"] ) . "%'";
		}

		$clauseWhere = ( count($arrClausesWhere) > 0 ) ? ( " WHERE " . implode(" AND ", $arrClausesWhere) ) : "";
		
		// Ajout la clause WHERE
		$requete = 'SELECT t_commission_com.IdCOM, COM_Libelle, COM_Repertoire, COM_Comment'
			. ' FROM t_commission_com'
			. ' LEFT JOIN tj_droit_com ON t_commission_com.IdCOM = tj_droit_com.IdCOM AND idUtilisateur = ' . $arrUtilisateur["IdUSR"]
			. $clauseWhere
			. ' ORDER BY COM_Libelle';
		break;
		
	case "utilisateur":
		if( $arrUtilisateur["droits"] & 2 ) {
		
			// Sélection sur la valeur tfRecherche
			$clauseWhere = "";
			if(isset($_POST["tfRecherche"])) {
				$listChamps = " USR_Nom, USR_Prenom, USR_NumAdherent, USR_Mail, USR_Tel, USR_Fonction, USR_ZoneGeo, USR_Comment";
				$clauseWhere = " WHERE LCASE( CONCAT_WS( '\n', "
					. $listChamps
					. " ) ) LIKE '%" . strtolower( $_POST["tfRecherche"] ) . "%'";
			}

			// Ajout la clause WHERE
			$requete = "SELECT"
				. " IdUSR, USR_Nom, USR_Prenom, IFNULL(USR_NumAdherent, '') AS USR_NumAdherent, USR_Mail, USR_Tel, USR_Fonction, USR_ZoneGeo,"
				. " IFNULL(USR_Comment, '') AS USR_Comment"
				. " FROM ts_user_usr"
				. $clauseWhere
				. " ORDER BY USR_Mail";
		} else {
			$erreur = "accès refusé";
		}
		break;
		
	case "droits":
		if( $arrUtilisateur["droits"] > 3 ) {
			$requete = 'SELECT'
				. ' IdUSR, USR_Nom, USR_Prenom, USR_Mail, USR_NumAdherent,'
				. ' IF((com_droit & 1) > 0, TRUE, NULL) AS droitLecture,'
				. ' IF((com_droit & 2) > 0, TRUE, NULL) AS droitModif,'
				. ' IF((com_droit & 4) > 0, TRUE, NULL) AS notification,'
				. ' IF((com_droit & 8) > 0, TRUE, NULL) AS superviseur'
				. ' FROM ts_user_usr'
				. ' LEFT JOIN tj_droit_com ON idUtilisateur = IdUSR AND IdCOM = ' . $_POST["IdCOM"]
				. ' ORDER BY USR_Mail';
		} else {
			$erreur = "accès refusé";
		}
		break;
		
	case "droitsUtilisateur":
		if( $arrUtilisateur["droits"] > 3 ) {
			$requete = 'SELECT t_commission_com.IdCOM AS IdCOM, COM_Libelle,'
				. ' IF( (com_droit &1) >0, true, NULL ) AS droitLecture,'
				. ' IF( (com_droit &2) >0, true, NULL ) AS droitModif,'
				. ' IF( (com_droit &4) >0, true, NULL ) AS notification,'
				. ' IF((com_droit & 8) > 0, true, null) AS superviseur'
				. ' FROM t_commission_com'
				. ' LEFT JOIN tj_droit_com ON tj_droit_com.IdCOM = t_commission_com.IdCOM'
				. ' AND idUtilisateur = ' . $_POST["IdURS"]
				. ' ORDER BY COM_Libelle';
		} else {
			$erreur = "accès refusé";
		}
		break;
		
	case "chrono":
		$requete = 'SELECT'
			. ' IdCHR, CASE CHR_Action WHEN "C" THEN "Création" WHEN "U" THEN "Mise à Jour" WHEN "J" THEN "Consult. doc. joint"'
			. ' ELSE "???" END AS Action, CHR_User, CHR_Date, CHR_Comment'
			. ' FROM t_chrono_chr'
			. ' WHERE CHR_Ident = ' . $_POST["recId"]
			. ' ORDER BY IdCHR DESC';
		break;
		
	case "repertoire":
		$requete = "SELECT COM_Repertoire, IFNULL( COM_Path, './ged/' ) AS COM_Path FROM t_commission_com WHERE IdCOM = " . $_POST["idCom"];
		break;
		
	case "mots_clef":
		$requete = 'SELECT DISTINCT DOC_MotClef AS mot_clef'
				. ' FROM t_docged_doc'
				. ' WHERE DOC_MotClef IS NOT NULL AND DOC_IdCOM = ' . $_POST["idCom"]
				. ' ORDER BY DOC_MotClef';
		break;
}

$success = is_string($requete);
if($success) {
	$result = $dbConnect->query($requete);

	if($result !== FALSE) {
		/* associative array */
		while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {
			$arr[] = $rec;
		}
	} else {
		$tabErreur = $dbConnect->errorInfo();
		$erreur = $tabErreur[2];
		$success = false;
	}
}

$o = array(
	"success"=>$success,
	"error"=>$erreur,
	"total"=>count($arr),
	"results"=>$arr
);

header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
?>