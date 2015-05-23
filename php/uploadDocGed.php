<?php
include 'selectIdent.php';

$nomUser = $arrUtilisateur["user"];
$nomSociete = $arrUtilisateur["societe"];

$reqSqlSelect = "SELECT t_commission_com.IdCOM, COM_Libelle, COM_Repertoire, COM_Path, COM_Comment, (com_droit && 2) > 0 AS droitEcriture"
	. " FROM t_commission_com"
	. " LEFT JOIN tj_droit_com ON tj_droit_com.IdCOM = t_commission_com.IdCOM AND idUtilisateur = " . $arrUtilisateur["IdUSR"]
	. " WHERE t_commission_com.IdCOM = " . $_GET["idAction"];

$result = $dbConnect->query($reqSqlSelect);
if( $result == false ) {
	// A faire : gestion erreur
} else {
	$rec = $result->fetch(PDO::FETCH_ASSOC);

	$droitEcriture = $rec['droitEcriture'];
	$COM_Libelle = $rec['COM_Libelle'];
	$COM_Repertoire = $rec["COM_Repertoire"];
	$COM_Path = $rec["COM_Path"];
}

$max_size = ini_get('upload_max_filesize');
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="../css/custom-class.css" />
	<link href="../css/file-uploader.css" rel="stylesheet" type="text/css">	
    <style>    	
		body {
		background-color: rgb(223, 232, 246); font-size:13px; font-family:arial, sans-serif; width:700px; margin:100px auto;
		}
		h1 {
		border: 1px solid rgb(153, 187, 232); font-size: 1.2em; color: rgb(21, 66, 139);
		width: 100%; padding-top: 2px; margin-right: 4px; padding-left: 8px; padding-right: 11px; 
		background-color: rgb(184, 207, 238); font-weight: bold;
		}
    </style>	
</head>
<body>

<div style="background-color: rgb(204, 255, 255);">
	<div class="custom-class-titre" style="float: left; width: 50%; padding-left: 64px;"><?php echo $nomSociete; ?></div>
	<div style="text-align: right;"><?php echo $nomUser; ?></div>
</div>
<h1>Téléchargement</h1>		
<p>Action : <?php echo $COM_Libelle ; ?></p>
<!--  <p>Droits : <?php echo $droitEcriture ; ?></p> -->
<p>max_size : <?php echo $max_size ; ?></p>
<div id="file-uploader-demo1">		
	<noscript>			
		<p>Please enable JavaScript to use file uploader.</p>
		<!-- or put a simple form for upload here -->
	</noscript>         
</div>

<?php if($droitEcriture) { ?>
<script src="../js/fileuploader.js" type="text/javascript"></script>
<script>        
	function createUploader(){       
		var uploader = new qq.FileUploader({
			element: document.getElementById('file-uploader-demo1'),
			action: '../php/gedUpload.php?rep=<?php echo $COM_Repertoire ; ?>&path=<?php echo $COM_Path ; ?>',
//			action: '../php/gedUpload.php?rep=<?php echo $COM_Repertoire ; ?>&path=COM_Path',
			onComplete: function(id, fileName, responseJSON){
				r = responseJSON;
			},
		});           
	}
	
	// in your app create uploader as soon as the DOM is ready
	// don't wait for the window to load  
	window.onload = createUploader;     
</script>
<?php } else { ?>
<script type="text/javascript">
	alert("Pas de droits en Ecriture");
</script>
<?php } ?>

</body>
</html>