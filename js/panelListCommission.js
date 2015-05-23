/**
 * panelListCommission.js
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011-2012
 * @date       04/08/2010
 * @version    0.9
 * @revision   $4$
 *
 * @date revision   25/08/2010
 * @date revision   08/02/2011 Ajout colonne répertoire 
 * @date revision   31/05/2011 -- Exportations XML
 * @date revision   30/10/2012 -- Affichage Icone titre
 *
 * - A Faire : Gestion dynamique de l'icone
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

var jsonDSResultReqListCom = new Ext.data.JsonStore({
		url: 'php/selectList.php',
		baseParams : { nomListe : 'commission' },
		root: 'results',
		totalProperty: 'total',
		fields: [ 
			{name: 'IdCOM', type: 'int'},
			{name: 'COM_Libelle', type: 'string'},
			{name: 'COM_Repertoire', type: 'string'},
			{name: 'COM_Comment', type: 'string'}
		]
	});

var afficheListeCom = function() {

	var myMask = new Ext.LoadMask(
		Ext.getBody(),
		{ msg:"Chargement...", store: jsonDSResultReqListCom }
	);
	
	var cbgCol = checkBoxGroupColonne('action');

	var gridPanelDesc = new Ext.grid.GridPanel({
		title: '<img alt="Actions" src="./img/folder.png"> Liste des Actions',
//		title: "Liste des Actions",
		store: jsonDSResultReqListCom,
		autoFill:true,
		colModel: new Ext.grid.ColumnModel({
			defaults: {	sortable: true },
			columns: [{ 	
				header: "Ident.",
				xtype: 'numbercolumn',
				dataIndex: 'IdCOM',
				format : '0',
				align : 'right',
				width :48
			}, {
				header: "Libellé",
				dataIndex: 'COM_Libelle',
				width :256 
			}, {
				header: "Répertoire",
				dataIndex: 'COM_Repertoire',
				width :256 
			}, {
				header: "Commentaire",
				dataIndex: 'COM_Comment',
				width :256 
			}]
		}),
		loadMask : true,
		layout: 'fit',
        stateful: true,
        stateId: 'Commission',      
		bbar: ['->',  { 	
			xtype: 'tbtext', 
//			hidden: true, 
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
				tabIndex = jsonDSResultReqListCom.collect('IdCOM');
				listCol = cbgCol.getValue() ;
				arrNomCol = new Array();
				uri = null;
				Ext.each( listCol, function(item) {
					if(item.name == 'URI') {
						uri = ( window.location.href.split('?') )[0] + "?tb=com&id=";
					} else {
						arrNomCol.push( item.name );
					}
				});
				paramsExport = {
						table: 'actions',
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
						obj = Ext.util.JSON.decode(response.responseText); 
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
				win = winUpload('actions');
				win.show();
			}
		}, '-', {xtype: 'tbspacer', width: 50},{ 
				text: 'Nouveau',
				iconCls:'icon-form-add',
				handler: function(){
					new formCommission( 0 );
				}
		}],
		listeners: {
			'rowdblclick': function(grid, rowIndex, e) {
				new formCommission( grid.store.getAt( rowIndex ).get('IdCOM') );
 			}
		}
	});

	gridPanelDesc.on('activate', function(grid, rowIndex, e) {
		Ext.getCmp('cardSelection').layout.setActiveItem( 3 );
	});
	jsonDSResultReqListCom.load();
	
	return gridPanelDesc;
}
