<?php
/**
 * connect.inc.php
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011
 * @date       19/10/2011
 * @version    0.1
 * @revision   $0$
 * 
 * @date revision   25/02/2013 Test du chargement de PDO
 *
 *  Connexion  à la base de données
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

session_start();

include 'config.inc.php';

if( !extension_loaded('pdo') ) {
	echo "{ success: false, errors: { reason: 'Pilote PDO NON Chargé' } }";
	die();
}

try {
	$dbConnect = new PDO('mysql:host=' . $loginServeur . ';dbname=' . $nomBase, $loginUsername, $loginPassword);
} catch (PDOException $e) {
	$raisonErreur = str_replace( "'", " ", $e->getMessage() );
	echo "{ success: false, errors: { reason: '" . $raisonErreur . "' } }";
	die();
}

$dbConnect->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
$dbConnect->exec("SET NAMES 'utf8'");

?>