<?php
/**
 * listCentre.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010
 * @date       07/07/2010
 * @version    0.9
 * @revision   $0$
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'connect.inc.php';

$requete = "SELECT DISTINCT DOC_Etat AS etat FROM t_docged_doc ORDER BY DOC_Etat";
$result = $dbConnect->query($requete);

/* associative array */
while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {       
	 $arr[] = $rec;
}

header("Content-Type: application/json");
echo htmlspecialchars_decode( json_encode(array("etats" => $arr)) , ENT_QUOTES);
	
?> 