/**
 * panelListUser.js
 * 
 * @auteur     marc laville
 * @Copyleft 2010
 * @date       19/07/2010
 * @version    0.5
 * @revision   $0$
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

var afficheListeUser = function() {

    // the check column is created using a custom plugin
    var checkColumnLect = new Ext.grid.CheckColumn({
       header: 'Lecture',
       dataIndex: 'droitLecture',
       width: 64
    });
    var checkColumnModif = new Ext.grid.CheckColumn({
       header: 'Modif.',
       dataIndex: 'droitModif',
       width: 64
    });

	var gridPanelDesc = new Ext.grid.GridPanel({
		title: "Droits Utilisateurs",
		store: new Ext.data.JsonStore({
			url: 'listUsers.json',
			root: 'results',
			totalProperty: 'total',
			fields: [ 
				{name: 'IdUSR', type: 'int'},
				{name: 'USR_Nom', type: 'string'},
				{name: 'USR_Prenom', type: 'string'},
				{name: 'USR_Mail', type: 'string'},
				{name: 'droitLecture', type: 'string'},
				{name: 'droitModif', type: 'string'}
			]
		}),
//		autoFill:true,
		viewConfig: { forceFit: true },
        width: 620, height: 300,
        plugins: [checkColumnLect, checkColumnModif],
		columns: [
			{ 	header: "Ident.",
				xtype: 'numbercolumn',
				sortable: true,
				dataIndex: 'IdUSR',
				format : '0',
				align : 'right',
				width :48
			},
			{ header: "Nom", sortable: true, dataIndex: 'USR_Nom', width :180 },
			{ header: "Prénom", sortable: true, dataIndex: 'USR_Prenom', width :180 },
			{ header: "Mail", sortable: true, dataIndex: 'USR_Mail', width :180 },
			checkColumnLect,
			checkColumnModif
		],
//		loadMask : true,
		buttons: [{ 
			text: 'Saisie',
			handler: function(){
				check=[];
				jsonDSResultReqListUser.each(function(r){
					ligne = r.data;
					if(ligne.droitLecture || ligne.droitModif) {
						IdUSR = r.data.IdUSR;
						check.push( new Array( ligne.IdUSR, ( ligne.droitModif ? 3 : 1 ) ) );
					}
				});
				alert(check.join("; "));
			}
		}],
        renderTo: 'editor-grid'
	});

	var myMask = new Ext.LoadMask(
		Ext.getBody(),
		{
			msg:"Chargement...",
			store: gridPanelDesc.store
		});
		
	gridPanelDesc.store.load();
	
	return gridPanelDesc;
}
