<?php
/**
 * selectListDocGed.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011
 * @date       19/07/2010
 * @version    0.5.0
 * @revision   $5$
 * @date _revision      06/08/2010
 * @date revision    19/10/2010 -- Ajout colonnes Echéances
 * @date revision    27/11/2010 -- Sélection sur dates d'Echéances
 * @date revision    15/12/2010 -- Débug liste fichiers d'1 répertoire (pb d'encodage) ; gestion du paramêtre paramJson.os_serveur
 * @date revision    15/02/2011 -- Remonte le nom du répertoire de la commission
 * @date revision    27/02/2011 -- Filtre sur le nom de répertoire en fonction des critères de recherche
 * @date revision    18/09/2011 -- Teste la validité des liens vers le doc lié - teste la sélection du document
 * 
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';

//
// Construction de la requete.
//
$requete = 'SELECT t_docged_doc.IdDoc, DOC_Libelle, t_commission_com.IdCOM AS IdCOM, COM_Libelle,'
				. " COM_Repertoire, IFNULL(COM_Path, './ged/') AS COM_Path,"
				. ' DOC_Etat,'
				. ' DOC_Fic, DOC_IdCOM, DOC_Descriptif, DOC_DateEcheance, DOC_LibEcheance, DOC_DateFinEcheance, DOC_LibFinEcheance,'
				. ' DOC_Nature, DOC_MotClef, DOC_CreePar,'
				. ' ( DOC_Visibilite OR com_droit & 8 ) AS Visibilite,'
				. ' tj_selection_doc.IdDOC IS NOT NULL AS selection'
			. ' FROM t_docged_doc'
			. ' LEFT JOIN t_commission_com ON IdCOM = DOC_IdCOM'
			. ' LEFT JOIN tj_selection_doc ON t_docged_doc.IdDOC = tj_selection_doc.IdDOC AND IdUSR = ' . $arrUtilisateur["IdUSR"] 	
			. ' LEFT JOIN tj_droit_com ON idUtilisateur = ' . $arrUtilisateur["IdUSR"] . ' AND tj_droit_com.IdCOM = t_commission_com.IdCOM';

// Construction de la clause WHERE		
$arrClausesWhere = array( " com_droit & 1 " );

// Sélection sur la Commission : rubrique DOC_IdCOM
$arrDocs = array();
$COM_Repertoire = null;
$COM_Libelle = null;

// Gere la selection sur une commission
if(isset($_POST["idCommission"])) {
	$arrClausesWhere[] = "DOC_IdCOM = '" . $_POST["idCommission"] ."'";

	$reqAction =  "SELECT COM_Libelle, COM_Repertoire,"
				. " IF(COM_Path IS NULL, './ged/', COM_Path) AS COM_Path"
				. " FROM t_commission_com "
				. " WHERE IdCOM = '" . $_POST["idCommission"] ."'";

	$result = $dbConnect->query( $reqAction );
	$rec = $result->fetch(PDO::FETCH_ASSOC);
	$COM_Libelle = $rec["COM_Libelle"];
	$COM_Repertoire = $rec["COM_Repertoire"];
	$COM_Path = '../' . $rec["COM_Path"];
	
	// Liste les fichiers présents dans le repertoire lié à la commission
	if( $handle = @opendir( $COM_Path . $COM_Repertoire ) ) {
		while( false !== ( $file = readdir( $handle ) ) ) {
			// Ajuste l'encodage des fichiers en fonction de l'OS Serveur
			if( $arrUtilisateur["paramJson"]["os_serveur"] == 'win' ) {
				$file = utf8_encode($file);
			}
			if( $file != "index.html" and is_file($COM_Path . $COM_Repertoire . '/' . $file) ) {
				$arrDocs[] = $file;
			}
		}
		closedir($handle);
	}
}

// Sélection sur la valeur tfRecherche
$tfRecherche = isset($_POST["tfRecherche"]) ? $_POST["tfRecherche"] : "";
if(strlen($tfRecherche)) {
	if(isset($_POST["rubRecherche"])) {
		// Sélection suivant les tubriques cochées
		$listChamps = $_POST["rubRecherche"];
	} else {
		$listChamps = " DOC_Libelle, DOC_Fic, DOC_Etat, DOC_Descriptif, DOC_Nature, DOC_MotClef, DOC_LibEcheance, DOC_LibFinEcheance, DOC_InfoComplementaires";
	}
	$arrClausesWhere[] = "LCASE( CONCAT_WS( '\n', "
		. $listChamps
		. " ) ) LIKE LCASE('%" . $tfRecherche . "%')";
}

// Sélection sur dates échéance
if(isset($_POST["echeanceInf"])) {
	$arrClausesWhere[] = "DOC_DateEcheance > '" . $_POST["echeanceInf"] ."'";
}
if(isset($_POST["echeanceSup"])) {
	$arrClausesWhere[] = "DOC_DateEcheance < '" . $_POST["echeanceSup"] ."'";
}
if(isset($_POST["finEcheanceInf"])) {
	$arrClausesWhere[] = "DOC_DateFinEcheance > '" . $_POST["finEcheanceInf"] ."'";
}
if(isset($_POST["finEcheanceSup"])) {
	$arrClausesWhere[] = "DOC_DateFinEcheance < '" . $_POST["finEcheanceSup"] ."'";
}

if(isset($_POST["selection"])) {
	$arrClausesWhere[] = "tj_selection_doc.IdDOC IS NOT NULL";
}

// Faut-il afficher les documents orphelins ?
$afficherOrphelins = !(
	   isset($_POST["echeanceInf"])
	|| isset($_POST["echeanceSup"])
	|| isset($_POST["finEcheanceInf"])
	|| isset($_POST["finEcheanceSup"])
	|| isset($_POST["selection"]) );
$rechercheSurLibelle = !isset($_POST["rubRecherche"]) || in_array("DOC_Libelle", explode(",", $_POST["rubRecherche"]));
$afficherOrphelins = $afficherOrphelins && ( strlen($tfRecherche) == 0 || $rechercheSurLibelle);

// Ajout la clause WHERE à la requète
$requete .= ( " WHERE " . implode(" AND ", $arrClausesWhere) );
$requete .= " ORDER BY DOC_DateCreation DESC";
if( count($arrClausesWhere) == 1 ) {
	$requete .= " LIMIT 16" ;
}
// Execution de la requete
$result = $dbConnect->query($requete);

if($result !== FALSE) {
	$arr = array();

	/* associative array */
	while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {
		 // Supprime le nom de fichier du catalogue si il correspond au fichier lié
		$ficLie = $rec["DOC_Fic"];
		$rec["lien"] = (strlen($ficLie) > 0) ? false : null;
		$key = strlen($ficLie) ? array_search($ficLie, $arrDocs) : false;
			
		 // Supprime le doc de la liste répertorié
		 if(!( $key === false )) {
			unset($arrDocs[$key]);
			$rec["lien"] = true;
		 } else {
			if( is_file( '../' . $rec["COM_Path"] . $rec["COM_Repertoire"] . '/' . $ficLie) ) {
				$rec["lien"] = true;
			} else {
//				$rec["lien"] = $rec["COM_Path"] . $rec["COM_Repertoire"] . '/' . $ficLie;
			}
		 }
		 if($rec["Visibilite"]) {
			$arr[] = $rec;
		 }
	}
	// Ajoute le reste du catalogue dans la réponse client
	if($afficherOrphelins) {
		foreach ($arrDocs as $key => $value) {
			if( strlen($tfRecherche) > 0 ? strstr($value, $tfRecherche) != false : true ) {
				$arr[] = array("DOC_Libelle" => $value,
								"COM_Libelle" => $COM_Libelle,
								"IdCOM" => ( isset($_POST["idCommission"]) ) ? $_POST["idCommission"] : 0,
								"COM_Repertoire" => $COM_Repertoire,
								"DOC_Fic" => $value
							);
			}
		}
	}
	
	$o = array(
		"success"=>TRUE,
		"total"=>count($arr),
		"results"=>$arr
		);
} else {
	$tabErreur = $dbConnect->errorInfo();
	$o = array(
		"success"=>FALSE,
		"error"=>$tabErreur[2],
		"requete"=>$requete
	);
}

header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
?> 