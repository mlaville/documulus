/**
 * panelListUser.js
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011
 * @date       19/07/2010
 * @version    0.6
 * @revision   $1$
 *
 * @date revision   24/04/2011 -- Exportations XML - Sélections des colonnes à l'export
 * @date revision   22/06/2011 -- Ajout bouton Import
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

var jsonDSResultReqListUser = new Ext.data.JsonStore({
		url: 'php/selectList.php',
		baseParams : { nomListe : 'utilisateur' },
		root: 'results',
		totalProperty: 'total',
		fields: [ 
			{name: 'IdUSR', type: 'int'},
			{name: 'USR_Nom', type: 'string'},
			{name: 'USR_Prenom', type: 'string'},
			{name: 'USR_Mail', type: 'string'},
			{name: 'USR_Tel', type: 'string'},
			{name: 'USR_NumAdherent', type: 'string'},
			{name: 'USR_Fonction', type: 'string'},
			{name: 'USR_ZoneGeo', type: 'string'},
			{name: 'USR_Comment', type: 'string'}
		]
	});
	
var afficheListeUser = function() {

	var myMask = new Ext.LoadMask(
		Ext.getBody(),
		{msg:"Chargement...",
		store: jsonDSResultReqListUser});
		
	var cbgCol = checkBoxGroupColonne('user');
		
	var gridPanelDesc = new Ext.grid.GridPanel({
		title: '<img alt="user" src="./img/user_gray.png"> Liste des Utilisateurs',
		store: jsonDSResultReqListUser,
		autoFill:true,
		colModel: new Ext.grid.ColumnModel({
			defaults: {	sortable: true },
			columns: [
				{ 	header: "Ident.",
					xtype: 'numbercolumn',
					dataIndex: 'IdUSR',
					format : '0',
					align : 'right',
					width :48
				},
				{ header: "Nom", dataIndex: 'USR_Nom' },
				{ header: "Prénom", dataIndex: 'USR_Prenom' },
				{ header: "Mail", dataIndex: 'USR_Mail' },
				{ header: "Téléphone", dataIndex: 'USR_Tel' },
				{ header: "Fonction", dataIndex: 'USR_Fonction' },
				{ header: "N° Adhérent", dataIndex: 'USR_NumAdherent' },
				{ header: "Zone Géographique", dataIndex: 'USR_ZoneGeo' },
				{ header: "Commentaire", dataIndex: 'USR_Comment' }
			]
		}),
		loadMask : true,
		layout: 'fit',
        stateful: true,
        stateId: 'Utilisateurs',      
		listeners: {
			'rowdblclick': function(grid, rowIndex, e){
				new formUser( grid.store.getAt( rowIndex ).get('IdUSR') );
			}
		},
		bbar: [ '->', { 	
			xtype: 'tbtext', 
			text: 'Export :'
		}, {
            text: 'Colonnes',
			menu: new Ext.menu.Menu({
				plain: true,
				items: {
					xtype: 'buttongroup',
					title: 'Sélection des Colonnes',
					autoWidth: true,
					columns: 1,
					items: cbgCol	
				}
			})
        }, {
			text: 'XML',
			iconCls:'icon-table-export',
			handler: function(){
				tabIndex = jsonDSResultReqListUser.collect('IdUSR');
				listCol = cbgCol.getValue() ;
				arrNomCol = new Array();
				uri = null;
				Ext.each( listCol, function(item) {
					if(item.name == 'URI') {
						uri = ( window.location.href.split('?') )[0] + "?tb=usr&id=";
					} else {
						arrNomCol.push( item.name );
					}
				});
				paramsExport = {
						table: 'users',
						selection: tabIndex.join(', '),
						colonnes: arrNomCol.join(', '),
						format: 'xml',
					};
				if( uri != null ) {
					paramsExport.uri = uri;
				};
				Ext.Ajax.request({
					url:'./php/selectExport.php',
					params : paramsExport,
					success:function(response){
						if(obj.success==true) {
							window.open("./php/forceDownload.php?download_dir=tmp/&download_file=export.xml");
						} else {
							alert("Opération Annulée : " + obj.errors);
						}
					}
				});
			}
		}, '-', {
			text: '  Import', //'Sélection ...',
			handler: function(){
				win = winUpload('users');
				win.show();
			}
		}, '-', {xtype: 'tbspacer', width: 50},
		{
			text: 'Nouveau',
			iconCls:'icon-user-add',
			handler: function(){
				new formUser(0);
			}
		}]
	});

	gridPanelDesc.on('activate', function(grid, rowIndex, e) {
		Ext.getCmp('cardSelection').layout.setActiveItem( 2 );
	});

	jsonDSResultReqListUser.load();
	
	return gridPanelDesc;
}
