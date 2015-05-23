<?php
/**
 * foncFichier.php
 * 
 * @auteur     marc laville
 * @Copyleft 2012
 * @date       23/05/2012
 * @version    0.1
 * @revision   $0$
 * 
 * @dateRevision 18/07/2012 Gestion des droits (755) à la création des repertoires
 * @dateRevision 20/10/2012 Calcul de l'espace occupé
 * 
 * Gere les deplacement de fichiers joint
 * Contrôle si le fichier joint est lié à une autre fiche (même commission)
 * A faire
 * - Gerer les fichiers de même nom dans le répertoire de destination
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
function moveFicLie( $bdConn, $idDoc, $idCommission ) {
	$pathRoot = "../";
	$rapport = array();
	
	$reqRep = "SELECT"
		. " IdCOM, CONCAT( IFNULL( COM_Path, './ged/' ) , COM_Repertoire ) AS Chemin,"
		. " t_docged_doc.DOC_Fic,"
		. " t_docged_doc.IdDoc"
		. " FROM t_commission_com"
		. " LEFT JOIN t_docged_doc ON t_commission_com.IdCOM = t_docged_doc.DOC_IdCOM"
		. " AND IdDoc = " . $idDoc
		. " WHERE IdDoc = " . $idDoc
		. " OR IdCOM = " . $idCommission
		. " ORDER BY t_docged_doc.DOC_IdCOM IS NULL";
	
	$reqFic = "SELECT SUM( DOC_IdCOM = {DOC_IdCOM} ) AS NbFicOrig, SUM( DOC_IdCOM = {idCommission} ) AS NbFicDest"
		. " FROM t_docged_doc"
		. " WHERE DOC_Fic = '{ficOrigine}'";

	$result = $bdConn->query($reqRep);
	$tab = $result->fetchAll();
	
	if( count($tab) > 1) {
		$repDest = $pathRoot . $tab[1]["Chemin"];
		if( !is_dir( $repDest ) ) {
			mkdir($repDest, '0755');
			copy( "index.html", $repDest . "/index.html" );
		}

		$ficOrigine = $tab[0]["DOC_Fic"];
		
		$sqlFic = str_replace(
					array('{ficOrigine}', '{DOC_IdCOM}', '{idCommission}'), 
					array($ficOrigine, $tab[0]["IdCOM"], $idCommission),
					$reqFic
				);
		$rapport["sqlFic"] = $sqlFic;
		$resultFic = $bdConn->query($sqlFic);
				
		$nb = $resultFic->fetch(PDO::FETCH_ASSOC);
		$rapport["nb"] = $nb;
		
		if( $nb["NbFicOrig"] == 1 ) {
			$isMove = @rename( $pathRoot . $tab[0]["Chemin"] . "/" . $ficOrigine, $repDest . "/" . $ficOrigine );
			$rapport["isMove"] = $isMove;
			$rapport["origin"] = $pathRoot . $tab[0]["Chemin"] . "/" . $ficOrigine;
			$rapport["dest"] = $pathRoot . $tab[1]["Chemin"] . "/" . $ficOrigine;
		} else {
			$rapport["isMove"] = null;
		}
	} else {
		$rapport["isMove"] = false;
	}
	
	return $rapport;
}
// eof

function size_dir($dir, $errLect="") {
	$taille = -1;
	
	if(is_dir($dir)) {
		$taille = 0;
	
		if($dh = @opendir($dir)) {
			while( ($file = readdir($dh)) !== false ) {
				if($file != "." && $file != "..") {
					$taille += (is_dir($dir."/".$file)) ? size_dir($dir."/".$file, $errLect) : filesize($dir."/".$file);
				}
			}
			closedir($dh);
		} else {
			$errLect .= "Erreur ouverture $dir\n";
		}
	}
	return $taille;
}

?>