<?php
/**
 * captcha.php
 * 
 * @auteur     marc laville
 * @Copyleft 2012
 * @date       14/10/2012
 * @version    0.1
 * @revision   $0$
 *
 * Renvoie l'image du Captcha
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

session_start();

function chaineAleatoire($nbcar)
{
	$chaine = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnopqrstuvwxyz23456789';
	srand((double)microtime()*1000000);
	$variable='';
        
	for($i=0; $i<$nbcar; $i++) $variable .= $chaine{rand()%strlen($chaine)};
	
	return $variable;
}

$_SESSION['Captcha'] = chaineAleatoire(5);

//echo '<b>test</b><img style="margin-left:128px" src="./php/captcha.png.php?PHPSESSID='.session_id().'" alt="Code à Recopiez/>';
echo '<img style="margin:8px 0 0 128px" src="./php/captcha.png.php" alt="Code à Recopier"/>';
//echo '';
?>

