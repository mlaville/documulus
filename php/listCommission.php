<?php
/**
 * listCommission.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010
 * @date       19/07/2010
 * @version    0.9.2
 * @revision   $1$
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

//include 'connect.inc.php';
include 'selectIdent.php';

$arr = array( );
$requete = "SELECT t_commission_com.IdCOM, COM_Libelle"
		. " FROM t_commission_com"
		. " LEFT JOIN tj_droit_com ON tj_droit_com.IdCOM = t_commission_com.IdCOM AND idUtilisateur = " . $arrUtilisateur["IdUSR"]
		. " WHERE " . ($arrUtilisateur["droits"] > 3 ? "1" : "com_droit & 1")
		. " ORDER BY COM_Libelle";
$result = $dbConnect->query($requete);

/* associative array */
while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {       
	 $arr[] = $rec;
}

//header("Content-Type: application/json");
echo htmlspecialchars_decode( json_encode(array("commissions" => $arr, "requete" => $requete)), ENT_QUOTES);
?> 