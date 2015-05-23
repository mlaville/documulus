<?php
/**
 * formActivite.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010
 * @date       02/08/2010
 * @version    0.9.1
 * @revision   $2$
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
//include 'connect.inc.php';
include 'selectIdent.php';

// Lecture des parametres
$paramJson = json_decode( file_get_contents("param.json"), true );

// Faut-il echapper les apostrophes ?
 $escApos = ($paramJson["esc_apostrophe"] > 0);
//$escApos = (1 == get_magic_quotes_gpc());

$vbSelect = isset($_POST["vbSelect"]) ? $_POST["vbSelect"] : "";

$reqSqlUpdate = "UPDATE activite SET"
	. " ACT_nomCentre = '{ACT_nomCentre}',"
	. " ACT_Societe = '{ACT_Societe}',"
	. " ACT_Type = '{ACT_Type}',"
	. " ACT_DescriptCourt = '{ACT_DescriptCourt}',"
	. " ACT_DescriptLong = '{ACT_DescriptLong}',"
	. " ACT_LieuDit = '{ACT_LieuDit}',"
	. " ACT_Commune = '{ACT_Commune}',"
	. " ACT_CodePostal = '{ACT_CodePostal}',"
	. " ACT_NomDept = '{ACT_NomDept}',"
	. " ACT_Adresse = '{ACT_Adresse}',"
	. " ACT_Bassin = '{ACT_Bassin}',"
	. " ACT_DistCentre = '{ACT_DistCentre}',"
	. " ACT_NumTel = '{ACT_NumTel}',"
	. " ACT_NomCorresp = '{ACT_NomCorresp}',"
	. " ACT_NumTel2 = '{ACT_NumTel2}',"
	. " ACT_NumFax = '{ACT_NumFax}',"
	. " ACT_Courriel = '{ACT_Courriel}',"
	. " ACT_CoordGeo = '{ACT_CoordGeo}',"
	. " ACT_Url = '{ACT_Url}',"
	. " ACT_DocPapier = '{ACT_DocPapier}',"
	. " ACT_TypePublic = '{ACT_TypePublic}',"
	. " ACT_Handicap = '{ACT_Handicap}',"
	. " ACT_PeriodeOuverture = '{ACT_PeriodeOuverture}',"
	. " ACT_Langue = '{ACT_Langue}',"
	. " ACT_InfoComplem = '{ACT_InfoComplem}',"
	. " ACT_DateModif = NOW()"
	. " WHERE IdActivite = {IdActivite}";

// get command
$cmd = isset($_REQUEST["cmd"]) ? $_REQUEST["cmd"] : false;

// get identifiant
$identifiant = isset($_REQUEST["identifiant"]) ? $_REQUEST["identifiant"] : false;
 
// no command?
if(!$cmd) {
	echo '{"success":false, "error":"No command"}';
	exit;
}

// load or save?
switch($cmd) {
	case "load":
		$requete = $vbSelect;
		$result = $dbConnect->query($requete);
		$rec = $result->fetch(PDO::FETCH_ASSOC);
		foreach ($rec as &$value) { } // A supprimer
		
		// A faire : gestion erreur
		
		// Envoie de la réponse au client
		$o = array(
			"success"=>true,
			"data"=>$rec,
			"requete"=>$requete
		);

	break;
	 
	case "save":
		$droitZoneGeo = ($arrUtilisateur["zoneGeo"] == "*" or $arrUtilisateur["zoneGeo"] == $_REQUEST["ACT_nomCentre"]);
		// Controle des droits de l'utilisateur
		if($droitZoneGeo && $_REQUEST["IdActivite"] > 0 && $arrUtilisateur["zoneGeo"] != "*") {
			$result = $dbConnect->query("SELECT ACT_nomCentre AS ZoneGeo FROM activite WHERE IdActivite = " . $_REQUEST["IdActivite"]);
			$rec = $result->fetch(PDO::FETCH_ASSOC);
			$droitZoneGeo = ($rec["ZoneGeo"] == $arrUtilisateur["zoneGeo"]);
		}
		
		if($droitZoneGeo == true) {
			// si l'identifiant de l'enregistrement est à 0 (zéro), on doit créer un nouvel enregistrement et récupèrer son identifiant
			if($_REQUEST["IdActivite"] == 0) {
				$result = $dbConnect->exec("INSERT INTO activite (ACT_DateCreation, ACT_CreePar) VALUES (NOW(), '" . $arrUtilisateur["user"] . "')");
				$result = $dbConnect->query("SELECT LAST_INSERT_ID() AS Id");
				$rec = $result->fetch(PDO::FETCH_ASSOC);
				$_REQUEST["IdActivite"] = $rec["Id"];
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
				// Place le doc télécharge dans le répertoire Docs
				$nomFic = $_FILES["ficDocPapier"]['name'];
				$isMove = move_uploaded_file ($_FILES['ficDocPapier']['tmp_name'],"../docs/".$nomFic);
				// Envoie le mail avec l'identifiant de la fiche modifiée
				$parseUrl = parse_url($_SERVER['HTTP_REFERER']);
				$referer =  $parseUrl['host'] . $parseUrl['path'];
				@mail($paramJson["courriel"], 'Mise à Jour Activité', $referer . '?id=' . $_REQUEST["IdActivite"] );
				$erreur = "Enregistrement effectué";
			} else {
				//  gestion erreur
				$tabErreur = $dbConnect->errorInfo();
				$erreur = $tabErreur[2];
			}
			// Construit la réponse pour le client
			$o = array(
				"success"=>$success,
				"error"=>$erreur,
				"requete"=>$requete,
				"isMove"=>isset($isMove) ? $isMove : null
			);
		} else {
			$o = array(
				"success"=>false,
				"error"=>"Problème de Droits",
				"user"=>$arrUtilisateur["zoneGeo"],
				"ACT_nomCentre"=>$_REQUEST["ACT_nomCentre"],
				"droitZoneGeo"=>$droitZoneGeo
			);
		}
		
	break;
}

// return response to client
//header("Content-Type: application/json");
if( $cmd == 'save' ) {
	@header("Content-Type: text/html");
}
echo htmlspecialchars_decode(json_encode($o), ENT_QUOTES);
// eof
?>
