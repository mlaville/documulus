<?php

// Chargement du paramêtrage des libellés
$libellesJson = json_decode( file_get_contents("libelles.json"), true );
$success = ($libellesJson != NULL);

if( $success ) {
	$erreur = NULL;
} else {
	$erreur["num"] = json_last_error();
	switch ( $erreur["num"] ) {
        case JSON_ERROR_NONE:
            $erreur["raison"] = ' - Aucune erreur';
        break;
        case JSON_ERROR_DEPTH:
            $erreur["raison"] = ' - Profondeur maximale atteinte';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            $$erreur["raison"] = ' - Inadéquation des modes ou underflow';
        break;
        case JSON_ERROR_CTRL_CHAR:
            $erreur["raison"] = ' - Erreur lors du contrôle des caractères';
        break;
        case JSON_ERROR_SYNTAX:
            $erreur["raison"] = ' - Erreur de syntaxe ; JSON malformé';
        break;
        case JSON_ERROR_UTF8:
            $erreur["raison"] = ' - Caractères UTF-8 malformés, probablement une erreur d\'encodage';
        break;
        default:
            $erreur["raison"] = ' - Erreur inconnue';
        break;
    }

}

// Construit la réponse pour le client
$arrLibelles = array(
	"success"=>$success,
	"errors"=>$erreur,
	"libellesJson"=>$libellesJson
);

echo htmlspecialchars_decode(json_encode($arrLibelles), ENT_QUOTES);
	
?>