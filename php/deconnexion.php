<?php
// On démarre la session  
session_start();  

// On détruit les variables de notre session  
session_unset();

// Détruit toutes les variables de session
$_SESSION = array();

// On détruit notre session  
session_destroy();  

// On redirige le visiteur vers la page d'accueil  
//header ('location: index.htm');  
?> 

