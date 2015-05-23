/*
 * winUpload.js
 * @auteur     marc laville
 * @Copyleft 2011
 * @date       05/06/2011
 * @version    0.1
 * @revision   $0$
 * 
 * @date revision    
 * 
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

function winUpload() {
	var panelUpload = new Ext.form.FormPanel({
        width: 380,
 //       frame: true,
        title: 'File Upload Form',
        bodyPadding: '10 10 0',

        defaults: {
            anchor: '100%',
            allowBlank: false,
            msgTarget: 'side',
            labelWidth: 50
        },
        items: [{
            xtype: 'textfield',
            fieldLabel: 'Name'
        } ,{
            xtype: 'fileuploadfield',
            id: 'form-file',
            emptyText: 'Select an image',
            fieldLabel: 'Photo',
            name: 'photo-path',
            buttonText: '',
            buttonConfig: {
                iconCls: 'upload-icon'
            }
        }],
/*
        buttons: [{
            text: 'Save',
            handler: function(){
                var form = this.up('form').getForm();
                if(form.isValid()){
                    form.submit({
                        url: 'file-upload.php',
                        waitMsg: 'Uploading your photo...',
                        success: function(fp, o) {
                            msg('Success', 'Processed file "' + o.result.file + '" on the server');
                        }
                    });
                }
            }
        },{
            text: 'Reset',
            handler: function() {
                this.up('form').getForm().reset();
            }
        }]
 */
   });
	return new Ext.Window({
		title : "Upload",
		layout: 'fit',
		width: 420,
		height: 220,
		closeAction:'hide',
		plain: true,
		collapsible: true,
		maximizable: true,
		items: panelUpload	
//		items: []	
    });
}

Ext.onReady(function(){

	win = winUpload();
	win.show();

});
