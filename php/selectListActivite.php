<?php
/**
 * selectListActivite.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010
 * @date       08/09/2010
 * @version    0.1
 * @revision   $0$
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'connect.inc.php';

$vbSelect = isset($_POST["vbSelect"]) ? $_POST["vbSelect"] : "";

//
// Construction de la requete.
// Si aucne clause WHERE n'est passée, on limite aux 25 dernier enregistrement créés
//
$requete = $vbSelect;
$arrClausesWhere = array();
if(isset($_POST["ACT_nomCentre"])) {
//	$arrClausesWhere[] = "ACT_nomCentre = '" . utf8_decode($_POST["ACT_nomCentre"]) ."'";
	$arrClausesWhere[] = "ACT_nomCentre = '" . $_POST["ACT_nomCentre"] ."'";
}

if(isset($_POST["tfRecherche"])) {
	if(isset($_POST["rubRecherche"])) {
		$listChamps = $_POST["rubRecherche"];
	} else {
		$listChamps = " ACT_Societe, ACT_Type, ACT_DescriptCourt, ACT_DescriptLong, ACT_LieuDit, ACT_Commune, ACT_Pays,"
		. " ACT_CodePostal, ACT_Commune, ACT_Pays, ACT_CodePostal, ACT_NomDept, ACT_Bassin, ACT_DistCentre, ACT_TypePublic,"
		. " ACT_Handicap, ACT_PeriodeOuverture, ACT_InfoComplem, ACT_Adresse, ACT_NomCorresp, ACT_NumTel, ACT_NumTel2";
//		. " ACT_NumFax, ACT_Courriel, ACT_Url, ACT_Langue";
	}	
	$arrClausesWhere[] = "LCASE( CONCAT_WS( '\n', "
		. $listChamps
//		. " ) ) LIKE '%" . strtolower( $_POST["tfRecherche"] ) . "%'";
		. " ) ) LIKE LCASE('%" . $_POST["tfRecherche"] . "%')";
}

if( count($arrClausesWhere) > 0 ) {
	$requete .= (" WHERE " . implode(" AND ", $arrClausesWhere));
} else {
	$requete .= " ORDER BY ACT_DateCreation DESC LIMIT 25";
}

$result = $dbConnect->query($requete);

if($result !== FALSE) {
	$arr = array();
	/* associative array */
	while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {       
		 $arr[] = $rec;
	}

	$o = array(
		"success"=>TRUE,
		"total"=>count($arr),
		"results"=>$arr,
		"requete"=>$requete
		);
} else {
	$tabErreur = $dbConnect->errorInfo();
	$o = array(
		"success"=>FALSE,
		"error"=>$tabErreur[2],
		"_POST"=>$_POST,
		"requete"=>$requete
	);
}
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
	
?> 