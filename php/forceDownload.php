<?php
/**
 * forceDownload.php
 * 
 * @auteur     marc laville
 * @Copyleft 2011
 * @date       30/01/2011
 * @version    0.1
 * @revision   $0$
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

if( !empty ($_GET["download_dir"]) and !empty ($_GET["download_file"]) ) {
	$nomFichier = $_GET ["download_file"];
	$chemin = '../ged/' . $_GET["download_dir"];

	if( file_exists( $chemin . $nomFichier ) ) {
		header ("Content-type: application/force-download");
		header ("Content-Disposition: attachment; filename=" . $nomFichier);
		readfile( $chemin . $nomFichier );
	}
}
?>
