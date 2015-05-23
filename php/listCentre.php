<?php
/**
 * listCentre.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010
 * @date       21/06/2010
 * @version    0.9
 * @revision   $0$
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

include 'connect.inc.php';

$requete = "SELECT DISTINCT ACT_nomCentre AS nomCentre FROM activite ORDER BY ACT_nomCentre";
$result = $dbConnect->query($requete);

$arr = array( array("nomCentre" => "*") );
/* associative array */
while( $rec = $result->fetch(PDO::FETCH_ASSOC) ) {       
//	 foreach ($rec as &$value) { $value=utf8_encode($value); }
	 $arr[] = $rec;
}

header("Content-Type: application/json");
echo htmlspecialchars_decode( json_encode(array("centres" => $arr)) , ENT_QUOTES);
	
?> 