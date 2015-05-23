<?php
/**
 * selectUpdate.php
 * 
 * @auteur     marc laville
 * @Copyleft 2011
 * @date       26/01/2011
 * @version    0.2
 * @revision   $2$
 * 
 * Gestion de la selection d'objets de la BàO
 *
 * @date revision   20/02/2011 -- vide la sélection ( cmd : "vide")
 * @date revision   22/09/2011 -- Ne compte que les documents visible (renvoi NbVisible et NbSelect)
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';

// Lecture des parametres
$paramJson = json_decode( file_get_contents("param.json"), true );

// Faut-il echapper les apostrophes ?
$escApos = ($paramJson["esc_apostrophe"] > 0);

$reqCountSelection = "SELECT COUNT(*) AS NbSelect, SUM( com_droit & 1 AND (DOC_Visibilite OR com_droit & 8) ) AS NbVisible"
				. " FROM tj_selection_doc, t_docged_doc"
				. " LEFT JOIN t_commission_com ON t_commission_com.IdCOM = DOC_IdCOM"
				. " LEFT JOIN tj_droit_com ON idUtilisateur = " . $arrUtilisateur["IdUSR"] . " AND tj_droit_com.IdCOM = t_commission_com.IdCOM"
				. " WHERE tj_selection_doc.IdDOC = t_docged_doc.IdDOC AND IdUSR = " . $arrUtilisateur["IdUSR"];
		
$reqReplaceSelection = "REPLACE INTO tj_selection_doc ( IdDOC, IdUSR, SEL_DateCreation)"
				. " VALUES ";
					
$reqDeleteSelection = "DELETE FROM tj_selection_doc"
				. " WHERE IdUSR = {IdUSR} AND IdDOC IN ({arrId})";

// get command
$cmd = isset($_REQUEST["cmd"]) ? $_REQUEST["cmd"] : false;

// no command?
if(!$cmd) {
	echo '{"success":false,"error":"No command"}';
	exit;
}
// controle des droits utilisateurs
// load or save?
$IdUSR = $arrUtilisateur["IdUSR"];
switch($cmd) {
	case "ajout":
		$values = array();
		$arrId = explode( ",", $_REQUEST["arrId"] );
		
		foreach( $arrId as $ident ) {
			$values[] = "( $ident, $IdUSR, NOW( ) )";
		}
		$requete = $reqReplaceSelection . implode(",", $values);
		$result = $dbConnect->exec($requete);
		
		if( $result == false ) {
			// A faire : gestion erreur
			$o = array(
				"success"=>false,
				"requete"=>$requete
			);
		} else {
			// Envoie de la réponse au client
			$requete = $reqCountSelection;
			$result = $dbConnect->query($requete);
			$rec = $result->fetch(PDO::FETCH_ASSOC);
			$o = array(
				"success"=>true,
				"compte"=>$rec["NbVisible"],
				"total"=>$rec["NbSelect"]
			);
		}
	break;
	 
	case "supp":
		$requete = str_replace( array('{IdUSR}', '{arrId}'),
								array($IdUSR, $_REQUEST["arrId"]),
								$reqDeleteSelection
							   );
		$result = $dbConnect->exec($requete);
		
		if( $result == false ) {
			// A faire : gestion erreur
			$o = array(
				"success"=>false,
				"requete"=>$requete
			);
		} else {
			// Envoie de la réponse au client
			$requete = $reqCountSelection;
			$result = $dbConnect->query($requete);
			$rec = $result->fetch(PDO::FETCH_ASSOC);
			$o = array(
				"success"=>true,
				"compte"=>$rec["NbVisible"],
				"total"=>$rec["NbSelect"]
			);
		}
	break;
	 
	case "vide":
		$requete = "DELETE FROM tj_selection_doc  WHERE IdUSR = " . $IdUSR;
		$result = $dbConnect->exec($requete);
		
		if( $result == false ) {
			// A faire : gestion erreur
			$o = array(
				"success"=>false
			);
		} else {
			// Envoie de la réponse au client
			$requete = $reqCountSelection;
			$result = $dbConnect->query($requete);
			$rec = $result->fetch(PDO::FETCH_ASSOC);
			$o = array(
				"success"=>true,
				"compte"=>$rec["NbVisible"],
				"total"=>$rec["NbSelect"]
			);
		}
	
	break;
	 
	case "compte":
		$requete = $reqCountSelection;
		$result = $dbConnect->query($requete);
		$rec = $result->fetch(PDO::FETCH_ASSOC);

		if( $result == false ) {
			// A faire : gestion erreur
			$o = array(
				"success"=>false,
				"requete"=>$requete
			);
		} else {
			$o = array(
				"success"=>true,
				"compte"=>$rec["NbVisible"],
				"total"=>$rec["NbSelect"]
			);
		}
		break;
}

// return response to client
header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
// eof
?>