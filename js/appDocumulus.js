/**
 * appDocumulus.js
 * 
 * @auteur     marc laville
 * @Copyleft 2015
 * @date       16/05/2015
 * @version    0.1
 * @revision   $4$
 *
 * @date_revision  12/11/2011 : Modification du titre de la fenêtre navigateur
 * @date_revision  24/01/2012 : Personnalisation du texte du bouton de demande de login (obj.paramJson.txt_demandeLogin)
 * @date_revision   marc laville 16/05/2012 : Transmet le nom de la société au panneau de login
 * @date_revision   marc laville 20/10/2012 : Affichage de l espace occupé
 * @date_revision   marc laville 21/05/2015 : Instance winUpload  pour les telechargement par lot
 *
 * Appel les composants de l'application
 * 
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

var appDocumulus = {
	appName: 'documulus',
	version : 0.9,
	fieldEspace : new Ext.form.DisplayField({
		name: 'espace',
		fieldLabel: 'Display field',
		value: 'Display field <span style="color:green;">value</span>',
		style: {
			fontFamily: 'arial,tahoma,helvetica,sans-serif',
			fontSize: '11px',
			padding: '0 8px',
			textAlign: 'right'
		}
	}),
	objLibelle: {},
	/**
	  * Gestion des uploads par lot
	  */
	divUpload: document.getElementById('upload'),
	winUpload: new Ext.Window({
		title:'upload',
		layout:'fit',
		width:420,
		height:480,
		closeAction:'hide',
		plain: true,
		items: {
			xtype:'panel',
			items:document.getElementById('upload')
		}
	}),
	showUpload : function( param ){
		var divUpload = this.divUpload,
			uploader = new qq.FileUploader({
				element: divUpload,
				action: './php/gedUpload.php?rep=Juralliance&idCommission=' + param.idCommission,
				/* A faire :
				 * - Gestion des erreurs
				 */
				onComplete: function(id, fileName, responseJSON){
					if(responseJSON.success) {
//						alert(fileName + '\nok');
					} else {
						alert(fileName + '\nerreur');
					}
				},
				fileTemplate: '<li>' +
						'<span class="qq-upload-file"></span>' +
						'<span class="qq-upload-spinner"></span>' +
						'<button class="qq-upload-cancel" type="button"></button>' +
						'<progress class="qq-upload-progress"></progress>' +
						'<div class="qq-upload-size"></div>' +
					'</li>',
				classes: {
					// used to get elements from templates
					button: 'qq-upload-button',
					drop: 'qq-upload-drop-area',
					dropActive: 'qq-upload-drop-area-active',
					list: 'qq-upload-list',
								
					file: 'qq-upload-file',
					spinner: 'qq-upload-spinner',
					size: 'qq-upload-size',
					progress: 'qq-upload-progress',
					cancel: 'qq-upload-cancel',

					// added to list item when upload completes
					// used in css to hide progress spinner
					success: 'qq-upload-success',
					fail: 'qq-upload-fail'
				}
			})
		
		this.winUpload.setTitle( 'upload vers ' + param.libelle );
		
		return this.winUpload.show( );
	}
	
};

/*
 * Chargement dynamique des libellés
 */

function libelleChamp( unClef ) {
	valretour = appDocumulus.objLibelle[unClef];
	
	return valretour == undefined ? unClef : valretour;
}

function initApp(nomUser, nomSociete) {
	Ext.Ajax.request({
		url:'php/libelles.php', 
		success: function(response){
			var obj = Ext.util.JSON.decode(response.responseText); 
			if(obj.success) {
				appDocumulus.objLibelle = obj.libellesJson;

				showViewPort(nomUser, nomSociete);
			}
		},
		failure: function(response, action){
			return Ext.Msg.alert('Status', 'Erreur de chargement des libellés !');
		}
	});

	return;
}

Ext.onReady(function(){
	Ext.QuickTips.init();
	Ext.Ajax.request({
		url:'php/controlIdent.php', 
		success:function(response){
			obj = Ext.util.JSON.decode(response.responseText);
			document.title = [ appDocumulus.appName, obj.societe].join(' - ');
			if( obj.style != undefined ) {
//				setActiveStyleSheet('accessibilite');
				setActiveStyleSheet( obj.style );
			}
			if(obj.success) {
				// Affichage du viewPort
				initApp(obj.user, obj.societe);
				appDocumulus.fieldEspace.setValue( obj.espaceOccupe.toByteSizeString() )
			} else {
				// Affichage Panneau de login 
				// Un transmet le texte du message de demande de log et le nom de la societe
				if( obj.errors != undefined ) {
					if( obj.user != null ) {
						alert( obj.errors.reason );
					}
				}
				PanelLogin(false, obj.paramJson.txt_demandeLogin, obj.societe);
			}
		},
		failure: function(response, action){
			Ext.Msg.alert('Status', 'Erreur d\'authentification !');
		}
	});

});