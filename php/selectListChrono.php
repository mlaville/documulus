<?php
/**
 * selectListChrono.php
 * 
 * @auteur     marc laville
 * @Copyleft   2011-2012
 * @date       27/11/2011
 * @version    0.2
 * @revision   $3$
 * 
 * @date revision   16/05/2012 -- Corrige erreur de séléction sur la date supérieure
 * @date revision   13/06/2012 -- Gere la zone de recherche
 * @date revision   01/08/2012 -- Affiche les consultation de doc joints
 * 
 * Renvoi une liste de chrono
 * 
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
 
include 'selectIdent.php';

//
// Construction de la requete.
//
$requete = "SELECT IdCHR, CHR_Ident,"
			. " CHR_Action,"
			. " CASE CHR_Action WHEN 'C' THEN 'Création' WHEN 'U' THEN 'Mise à Jour' WHEN 'J' THEN 'Consultation Doc Joint' ELSE '???' END AS Action,"
			. " CHR_User, CHR_Date, CHR_Comment, IdDoc, DOC_Libelle, DOC_IdCOM, COM_Libelle,"
			. " IFNULL( COM_Path, './ged/' ) AS COM_Path,"
			. " IF( com_droit &8, DOC_Visibilite, NULL ) AS Visibilite, com_droit &8 AS Superviseur, DOC_Etat, DOC_Fic, COM_Repertoire, DOC_Descriptif"
			. " FROM t_chrono_chr, t_docged_doc"
			. " LEFT JOIN t_commission_com ON t_commission_com.IdCOM = DOC_IdCOM"
			. " LEFT JOIN tj_droit_com ON tj_droit_com.IdCOM = t_commission_com.IdCOM AND idUtilisateur = " . $arrUtilisateur["IdUSR"]
			. " WHERE CHR_NomTable = 't_docged_doc' AND IdDoc = CHR_Ident AND IF( com_droit &8, DOC_Visibilite, NULL ) = 1"
			. " AND {clauseWhere}"
			. " ORDER BY CHR_Date DESC"
			. " LIMIT 100";
// clauseWhere
$arrClausesWhere = array();

// Sélection sur dates échéance
if(isset($_POST["dateInf"])) {
	$arrClausesWhere[] = "DATE(CHR_Date) >= '" . $_POST["dateInf"] ."'";
}
if(isset($_POST["dateSup"])) {
	$arrClausesWhere[] = "DATE(CHR_Date) <= '" . $_POST["dateSup"] ."'";
}

// Sélection sur la valeur tfRecherche
$tfRecherche = isset($_POST["tfRecherche"]) ? $_POST["tfRecherche"] : "";
if(strlen($tfRecherche)) {
	if(isset($_POST["rubRecherche"])) {
		// Sélection suivant les tubriques cochées
		$listChamps = $_POST["rubRecherche"];
	} else {
		$listChamps = " CHR_User, DOC_Libelle, DOC_Fic, DOC_Etat, DOC_Descriptif, DOC_Nature, DOC_MotClef, DOC_LibEcheance, DOC_LibFinEcheance, DOC_InfoComplementaires";
	}
	$arrClausesWhere[] = "LCASE( CONCAT_WS( '\n', "
		. $listChamps
		. " ) ) LIKE LCASE('%" . $tfRecherche . "%')";
}

$clauseWhere = ( count($arrClausesWhere) ) ? implode( " AND ", $arrClausesWhere ) : "1";
// Execution de la requete
$result = $dbConnect->query( str_replace('{clauseWhere}', $clauseWhere, $requete) );

if($result !== FALSE) {
	$arr = array();
	
	/* associative array */
	while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {
		$arr[] = $rec;
	}

	$o = array(
		"success"=>$success,
		"error"=>$erreur,
		"total"=>count($arr),
		"requete"=>str_replace('{clauseWhere}', $clauseWhere, $requete),
		"results"=>$arr
	);
} else {
	$tabErreur = $dbConnect->errorInfo();
	$o = array(
		"success"=>FALSE,
		"error"=>$tabErreur[2],
		"requete"=>str_replace('{clauseWhere}', $clauseWhere, $requete)
	);
}

header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
?>