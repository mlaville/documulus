/**
 * file-upload.js
 * 
 * @auteur     marc laville
 * @Copyleft 2011
 * @date       20/06/2011
 * @version    0.1
 * @revision   $0$
 *
 * Upload de fichier
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

 function winUpload(type) {

    var panelUpload = new Ext.FormPanel({
        fileUpload: true,
        width: 420,
        autoHeight: true,
        bodyStyle: 'padding: 10px 10px 0 10px;',
        labelWidth: 50,
        defaults: {
            anchor: '95%',
            allowBlank: false,
            msgTarget: 'side'
        },
        items: [{
            xtype:	'hidden',
            name:	'table',
			value:	type
		}, {
            xtype: 'fileuploadfield',
            id: 'form-file',
            emptyText: 'Selectionnez un fichier XML',
            fieldLabel: 'Chemin',
            name: 'xml-path',
            buttonText: '',
			buttonCfg: {
				iconCls: 'icon-explore'
			}
        }],
        buttons: [{
            text: 'Importer',
            handler: function(){
                if(panelUpload.getForm().isValid()){
	                panelUpload.getForm().submit({
	                    url: './php/importXml.php',
	                    waitMsg: 'Transfert en Cours ...',
	                    success: function(panelUpload, o){
//	                        Ext.Msg.alert('Success', 'Processed file "'+o.result.file+'" on the server');
	                        Ext.Msg.alert('Succès', 'Données traitées par le Serveur');
	                    },
						failure: function(form, action){
							if( action.failureType === Ext.form.Action.CONNECT_FAILURE ) {
								Ext.Msg.alert('Error',
									'Status:'+action.response.status+': '+
									action.response.statusText);
							}
							if( action.failureType === Ext.form.Action.SERVER_INVALID ){
								// server responded with success = false
								Ext.Msg.alert('Invalid', action.result.errormsg);
							}
						}

	                });
                }
            }
        },{
            text: 'Annuler',
            handler: function(){
                panelUpload.getForm().reset();
            }
        }]
    });
	
	return  new Ext.Window({
		title : "Sélection",
		layout: 'fit',
		plain: true,
		items: panelUpload	
    });
}
