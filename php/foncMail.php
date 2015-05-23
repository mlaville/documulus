<?php

function msgNotification($utilisateur, $crud, $DOC_Libelle, $lien) {

	$html = file_get_contents('../tpl/notificationBaO.html');
	
	$message = str_replace(
		array('{utilisateur}', '{crud}', '{DOC_Libelle}', '{lien}'),
		array($utilisateur, ( $crud == 'C' ) ? "créé" : "modifié", $DOC_Libelle, $lien),
		$html
		);

	return $message;
}
	
function mailHtml($destinataire, $expediteur, $sujet, $message) {

     // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
	$entete = "MIME-Version: 1.0\n";
	$entete .= "Content-type: text/html; charset=UTF-8\n";
     // En-têtes additionnels
	$entete .= "From: $expediteur\n";
//	$entete .= "X-Sender: <$_SERVER['HTTP_REFERER']>\n";
	$entete .= "X-Mailer: PHP\n";
	$entete .= "X-auth-smtp-user: $expediteur\n";
	$entete .= "X-abuse-contact: $expediteur\n";
	$entete .= "Reply-to:$expediteur ";

//     $headers .= 'To: Mary <marc.laville@polinux.net>, Kelly <vava.laville@voila.fr>' . "\r\n";
//     $headers .= 'Cc: anniversaire_archive@example.com' . "\r\n";
//     $headers .= 'Bcc: anniversaire_verif@example.com' . "\r\n";
	
	// Envoi
	return @mail($destinataire, $sujet, $message, $entete) ;
}
?>