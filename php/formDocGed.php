<?php
/**
 * formDocGed.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011-2012
 * @date       14/08/2010
 * @version    0.9.8
 * @revision   $8$
 * @date revision    27/08/2010 -- Déplacement du fichier lié en cas de modification de la commission
 * @date revision    13/09/2010 -- Gestion du chrono
 * @date revision   22/11/2010 -- Gestion de la visibilité
 * @date revision   18/04/2011 -- Amélioration de l'envoi mail
 * @date revision   10/07/2011 -- Nouveau calcul du chemin du sous repertoire (acces photo)
 * @date revision   09/11/2011 -- parametrage de l'expéditeur du mail de notification
 * @date revision   10/11/2011 -- Correction du déplacement de fichier lors d'un changement de commission
 * @date revision  23/05/2012-- Gere le déplacement du fichier joint vers la corbeille
 * 
 * A Faire :
 * - Résolution du bug qui fait que le header revoyé correspond à HTML et non JSON
 * - gérer l'affichage des .wav et .txt
 * 
 * CRUD sur la table t_docged_doc
 * Gestion des téléchargement
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'selectIdent.php';
include 'foncMail.php';

include 'foncFichier.php';


// Lecture des parametres
$paramJson = json_decode( file_get_contents("param.json"), true );

// Faut-il echapper les apostrophes ?
$escApos = ($paramJson["esc_apostrophe"] > 0);
$mail_expediteurNotif = $paramJson["mail_expediteurNotification"];

$reqChrono = "INSERT INTO t_chrono_chr (CHR_NomTable, CHR_Ident, CHR_Action, CHR_User, CHR_Date)"
	. " VALUES ('{CHR_NomTable}', '{CHR_Ident}', '{CHR_Action}', '{CHR_User}', NOW( ) )";

$reqMail = "SELECT USR_Mail"
	." FROM t_docged_doc, tj_droit_com, ts_user_usr"
	." WHERE IdUSR = idUtilisateur AND com_droit & 4"
	." AND IdCOM = DOC_IdCOM AND IdDoc = {IdDoc}";
						
$reqSqlUpdate = "UPDATE t_docged_doc SET"
	. " DOC_Libelle = '{DOC_Libelle}',"
	. " DOC_Etat = '{DOC_Etat}',"
	. " DOC_IdCOM = {idCOM},"
	. " DOC_Fic = '{DOC_Fic}',"
	. " DOC_Descriptif = '{DOC_Descriptif}',"
	. " DOC_Nature = '{DOC_Nature}',"
	. " DOC_MotClef = '{DOC_MotClef}',"
	. " DOC_DateEcheance = IF(YEAR('{DOC_DateEcheance}'), '{DOC_DateEcheance}', NULL),"
	. " DOC_LibEcheance = '{DOC_LibEcheance}',"
	. " DOC_DateFinEcheance = IF(YEAR('{DOC_DateFinEcheance}'), '{DOC_DateFinEcheance}', NULL),"
	. " DOC_LibFinEcheance = '{DOC_LibFinEcheance}',"
	. " DOC_InfoComplementaires = '{DOC_InfoComplementaires}',"
	. " DOC_DateModif = NOW()"
	. " WHERE IdDoc = {IdDoc}";

$reqSqlUpdateVisibilite = "UPDATE t_docged_doc"
	. " SET DOC_Visibilite = {visibilite}"
	. " WHERE IdDoc = {ident}";

$reqSqlUpdateTrash = "UPDATE t_docged_doc"
	. " SET DOC_IdCOM = -1"
	. " WHERE IdDoc = {ident}";

$reqSqlSelect = "SELECT IdDoc, DOC_Libelle, DOC_IdCOM, COM_Libelle,"
	. " IFNULL( COM_Path, './ged/' ) AS COM_Path,"
	. " IF(com_droit & 8, DOC_Visibilite, NULL) AS Visibilite,"
	. " com_droit & 8 AS Superviseur,"
	. " DOC_Etat, DOC_Fic, COM_Repertoire, DOC_Descriptif, DOC_InfoComplementaires,"
	. " DOC_MotClef, DOC_Nature, DOC_DateEcheance, DOC_LibEcheance, DOC_DateFinEcheance, DOC_LibFinEcheance"
	. " FROM t_docged_doc"
	. " LEFT JOIN t_commission_com ON t_commission_com.IdCOM = DOC_IdCOM"
	. " LEFT JOIN tj_droit_com ON tj_droit_com.IdCOM = t_commission_com.IdCOM AND idUtilisateur = " . $arrUtilisateur["IdUSR"]
	. " WHERE IdDoc = ";

// Récupère le dossier d'origine, et les restrictions de droit sur le ou les commissions
$reqSelectNonDroit = "SELECT MAX( IF( IdDoc ={IdDoc}, DOC_IdCOM, 0 ) ) AS IdComOrigine, SUM( ! ( IFNULL( com_droit, 0 ) &{niveauDroit} ) ) AS NbRestrict"
	. " FROM ("
	. " t_commission_com"
	. " LEFT JOIN t_docged_doc ON DOC_IdCOM = t_commission_com.IdCOM"
	. " )"
	. " LEFT JOIN tj_droit_com ON tj_droit_com.IdCOM = t_commission_com.IdCOM AND idUtilisateur ={idUtilisateur}"
	. " WHERE IdDoc ={IdDoc}"
	. " OR t_commission_com.IdCOM = {IdCOM}";
	
// get command
$cmd = isset($_REQUEST["cmd"]) ? $_REQUEST["cmd"] : false;

// get identifiant
$identifiant = isset($_REQUEST["identifiant"]) ? $_REQUEST["identifiant"] : false;
 
// no command?
if(!$cmd) {
	echo '{"success":false,"error":"No command"}';
	exit;
}
$reqRep = null;
$repDest = null;

switch($cmd) {
	case "load":
		$requete = $reqSqlSelect . $identifiant;
		$result = $dbConnect->query($requete);
		$rec = $result->fetch(PDO::FETCH_ASSOC);
		foreach ($rec as &$value) { } // A supprimer
		
		// A faire : gestion erreur
		
		// Envoie de la réponse au client
		$o = array(
			"success"=>true,
			"data"=>$rec
		);

	break;
	 
	case "visibilite":
		$requete = str_replace( array('{ident}', '{visibilite}'),
								array($_REQUEST["ident"], $_REQUEST["visibilite"]),
								$reqSqlUpdateVisibilite
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
			$o = array(
				"success"=>true
			);
		}
	break;

	case "trash":
		// Gestion du document lié : déplacement dans la corbeille
		$rt = moveFicLie( $dbConnect, $_POST["ident"], -1 );
		$valRet = array(
			"success"=>true,
			"rt"=>$rt
		);
		// controle des droits utilisateurs
			$requete = str_replace( array('{ident}'),
									array($_REQUEST["ident"]),
									$reqSqlUpdateTrash
								   );
		
			$valRet["requete"] = $requete;
			$result = $dbConnect->exec($requete);
			if( $result == false ) {
				// A faire : gestion erreur
				$valRet["success"] = false;
			}
//		}
		$o = $valRet;
	break;

	case "save":
		$isMove = null; // Deplacement du fichier joint
		$mailEnvoi = null;
		$sujet = null;
		$message = null;
		
		// Controle des droits
		$reqDroit = str_replace( array( "{idUtilisateur}", "{IdDoc}", "{IdCOM}", "{niveauDroit}" ),
							array( $arrUtilisateur["IdUSR"], $_REQUEST["IdDoc"], $_REQUEST["idCOM"], "2" ),
							$reqSelectNonDroit );
		
		$result = $dbConnect->query($reqDroit);
		$rec = $result->fetch(PDO::FETCH_ASSOC);
		if($rec["NbRestrict"] > 0) {
			$success = false;
			$erreur = "Pas de Droits en Modification";
		} else {
			$idComOrigine = $rec["IdComOrigine"];
			$repOrigine = $_REQUEST["COM_Repertoire"];
			$ficOrigine = $_REQUEST["DOC_Fic"];
				
			$crud = $_REQUEST["IdDoc"] > 0 ? 'U' : 'C';
			// si l'identifiant de l'enregistrement est à 0 (zéro), on doit créer un nouvel enregistrement et récupèrer son identifiant
			if( $crud == 'C' ) {
				$success = $dbConnect->exec("INSERT INTO t_docged_doc (DOC_DateCreation, DOC_CreePar) VALUES (NOW(), '" . $arrUtilisateur["user"] . "')");
				if(!$success) {
					$tabErreur = $dbConnect->errorInfo();
					$o = array(
						"success"=>$success,
						"errors"=>$tabErreur[2]
					);
				}
				// On recupère l'identifiant generé pour le stocker dans le tableau $_REQUEST
				$_REQUEST["IdDoc"] = $dbConnect->lastInsertId();
			} 
			
			// Détection du répertoire des documents liés (destination)
			$reqRep = "SELECT COM_Repertoire, IFNULL(COM_Path, './ged/') AS Path FROM t_commission_com WHERE IdCOM = " . $_REQUEST["idCOM"];
			$result = $dbConnect->query($reqRep);
			$rec = $result->fetch(PDO::FETCH_ASSOC);
			$COM_Repertoire = $rec["COM_Repertoire"];
			$path = '../' . $rec["Path"];
			
			$repDest = $path . $COM_Repertoire;
			if( !is_dir( $repDest ) ) {
				mkdir( $repDest );
				copy( "index.html", $repDest . "/index.html" );
//				copy( "../ged/index.html", $repDest . "/index.html" );
			}
				
			// Gestion du fichier lié 
			$nomFicUpload = $_FILES["ficDocPapier"]['name'];
			if(strlen($nomFicUpload)) {
				if($crud != 'C') {
					$rt = moveFicLie( $dbConnect, $_POST["IdDoc"], -1 );
				}
				$isMove = move_uploaded_file( $_FILES['ficDocPapier']['tmp_name'], $repDest . "/" . $nomFicUpload );
			} else {
				if( strlen( $ficOrigine ) and $_REQUEST["idCOM"] != $idComOrigine and $crud != 'C' ) {
					$rt = moveFicLie( $dbConnect, $_POST["IdDoc"], $_REQUEST["idCOM"] );
					
					$isMove = $rt["isMove"];
				}
			}
			// Prepare les tableau de remplacement pour la requete Update
			$_REQUEST["DOC_DateEcheance"] = join( '-', array_reverse( explode( "/", $_REQUEST["DOC_DateEcheance"] ) ) );
			$_REQUEST["DOC_DateFinEcheance"] = join( '-', array_reverse( explode( "/", $_REQUEST["DOC_DateFinEcheance"] ) ) );
			
			foreach ($_REQUEST as $key => $value) {
				$recherche[] = "{".$key."}";
				$remplace[] = $escApos ? addslashes($value) : $value;
			}
			// Construction et execution de la requete
			$requete = str_replace($recherche, $remplace, $reqSqlUpdate);
			$success = ( $dbConnect->exec($requete) == 1 );
			if( $success ) {
				// Mise à jour du chrono
				$dbConnect->exec(str_replace(
					array('{CHR_NomTable}', '{CHR_Ident}', '{CHR_Action}', '{CHR_User}'), 
					array('t_docged_doc', $_REQUEST["IdDoc"], $crud, $arrUtilisateur["user"]),
					$reqChrono
				));
				
				// Gestion des mails de notification
				$parseUrl = parse_url($_SERVER['HTTP_REFERER']);
				$reqListMail = str_replace(array('{IdDoc}'), array($_REQUEST["IdDoc"]), $reqMail);
				$sujet = 'Mise à Jour GED';
				$msgMail = msgNotification($arrUtilisateur["user"],
					$crud,
					$_REQUEST["DOC_Libelle"],
					$parseUrl['scheme'] . "://" . $parseUrl['host'] . $parseUrl['path'] . "?tb=ged&id=" . $_REQUEST["IdDoc"]
				);
				
				/* Parcourt la liste de destinataire et maintient la liste de compte rendu d'envoi de mail */
				$mailEnvoi = array();
				$result = $dbConnect->query($reqListMail);
				while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {
					$destinataire = $rec["USR_Mail"];

					if( mailHtml($destinataire, $mail_expediteurNotif, $sujet, $msgMail) ) {
						$mailEnvoi[] = $destinataire . " : Succes";
					} else {
						$mailEnvoi[] = $destinataire . " : Erreur";
					}
				}
				$erreur = "Enregistrement effectué";
			} else {
				//  gestion erreur
				$tabErreur = $dbConnect->errorInfo();
				$erreur = $tabErreur[2];
			}
		}
		// Construit la réponse pour le client
		$o = array(
			"success"=>$success,
			"errors"=>$erreur,
			"mailEnvoi"=>$mailEnvoi,
//			"requete"=>$requete,
			"reqRep"=>$reqRep,
			"repDest"=>$repDest,
			"isMove"=>$isMove
		);
	break;
}

// return response to client
@header("Content-Type: text/html");
//header("Content-Type: application/json");
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
// eof
?>