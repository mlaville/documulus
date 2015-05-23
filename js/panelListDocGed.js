/**
 * panelListDocGed.js
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011-2012
 * @date       25/10/2010
 * @version    0.9.8
 * @revision   $8$
 *
 * @date revision   28/01/2011 -- gestion des sélection
 * @date revision   19/02/2011 -- gestion des Exports CSV et XML
 * @date revision   20/02/2011 -- Passage de la sélection en Commission (cmd : createFromSelection)
 * @date revision   21/02/2011 -- Passage de la sélection en Commission : alert sur sélection vide
 * @date revision   05/03/2011 -- Exportations csv et XML
 * @date revision   24/04/2011 -- Sélections des colonnes à l'export
 * @date revision   18/09/2011 -- Ajout colonne lien valide
 * @date revision   24/11/2011 -- Reactivite colonne lien valide (ouverture du document joint
 * @date revision   28/09/2012 -- Gestion de l'affichage du fichier joint
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

 var formCtrlCopySelect = new Ext.form.FormPanel({
	bodyStyle:'padding:5px',
	defaultType: 'textfield',
	defaults:{ border:false, anchor: '95%' },
	items:[{
		name:'action',
		disabled:true,
		fieldLabel:'Ident. Action'
	}, {
		name:'rep',
		disabled:true,
		fieldLabel:'Répertoire'
	}, {
		name:'nbCopies',
		disabled:true,
		fieldLabel:'Documents Copiés'
	}, {
		xtype: 'checkbox',
		name:'videSelect',
		fieldLabel: 'Vider la Sélection',
	}, {
		xtype: 'checkbox',
		name:'selectAction',
		fieldLabel: 'Sélectionner l\'action créée',
	}], 
	buttons: [{
		text:'OK',
		handler: function() {
			var	val = formCtrlCopySelect.form.getFieldValues();
			if(val.selectAction) {
				comboCommission.store.reload({
					params : { },
					callback  : function() {
						comboCommission.setValue( val.action );
						lanceSelectDocGed();
					}
				})
			}
			if(val.videSelect) {
				Ext.Ajax.request({
					url:'./php/selectUpdate.php',
					params : { cmd:"vide" },
					success:function(response){
						obj = Ext.util.JSON.decode(response.responseText);
						labelSelection.setText( 'Sélection (' + obj.compte +') :' );
					},
					failure: function(response, action){
						Ext.Msg.alert('Status', 'Erreur d\'authentification !');
					}
				});
			}
			this.ownerCt.ownerCt.ownerCt.hide(); // Accès à win
		}
	}]
});

/*
 * winCopy est utilisée pour le retour d'info lors de la transformration de la sélection en Action
 */
var winCopy = new Ext.Window({
	title : "Copie ...",
	layout:'fit',
	width:280,
	height:240,
	closeAction:'hide',
	plain: true,
	collapsible: true,
	maximizable: false,
	items: formCtrlCopySelect
});

// Pour adresser l'affichage du nombre de docs sélectionnés
var labelSelection = new Ext.Toolbar.TextItem({text: 'Sélection : '});

var jsonDSResultReqListDoc = new Ext.data.JsonStore({
		url: 'php/selectListDocGed.php',
		root: 'results',
		totalProperty: 'total',
		fields: [ 
			{name: 'IdDoc', type: 'int'},
			{name: 'DOC_Libelle', type: 'string'},
			{name: 'COM_Libelle', type: 'string'},
			{name: 'IdCOM', type: 'int'},
			{name: 'COM_Path', type: 'string'},
			{name: 'COM_Repertoire', type: 'string'},
			{name: 'DOC_Fic', type: 'string'},
			{name: 'lien', type: 'string'},
			{name: 'DOC_Etat', type: 'string'},
			{name: 'DOC_Descriptif', type: 'string'},
			{name: 'DOC_DateEcheance', type: 'date', dateFormat: "Y-m-d"},
			{name: 'DOC_LibEcheance', type: 'string'},
			{name: 'DOC_DateFinEcheance', type: 'date', dateFormat: "Y-m-d"},
			{name: 'DOC_LibFinEcheance', type: 'string'},
			{name: 'DOC_Nature', type: 'string'},
			{name: 'DOC_MotClef', type: 'string'},
			{name: 'DOC_CreePar', type: 'string'},
			{name: 'selection', type: 'boolean'}
		]
	});

var arrSelectionObjet = new Array();

var formControleSelection;

/**
 * function gerant le rendu specifique de la colonne lien
 * @param {Object} val
 */
function lienvalid(val) {
	if (val == 'true') {
		return '<img src="./img/icons/accept.png"/>';
	} else if (val == 'false') {
		return '<img src="./img/icons/link_break.png"/>';
	}
	return val;
}

var afficheListeDoc = function() {

	var myMask = new Ext.LoadMask(
		Ext.getBody(),
		{ msg:"Chargement...", store: jsonDSResultReqListDoc }
	);
	
	var cbgCol = checkBoxGroupColonne('objet');
	
	var gridPanelDesc = new Ext.grid.GridPanel({
		title: '<img alt="Objets" src="./img/brick.png"> Liste des Objets',
		store: jsonDSResultReqListDoc,
		autoFill:true,
		bodyCssClass: 'custom-class-grid',

		colModel: new Ext.grid.ColumnModel({
			defaults: {	sortable: true },
			columns: [ /*{
					header   : 'Sél.', 
					width    : 32, 
					sortable : true, 
					renderer : function lienvalid(val) { return (val) ? '<img src="./img/icons/icon_checked.gif"/>' : null; }, 
					dataIndex: 'selection',
					hidden : true,
					align : 'center'
				},*/ {
					header: "Ident.",
					dataIndex: 'IdDoc',
					renderer: function(value, metaData, record, rowIndex, colIndex, store) {
						return (value > 0) ? value : "";
					},
					align : 'right',
					width :48
				}, { header: "Libellé", dataIndex: 'DOC_Libelle', width :256 },
				{ header: "Action", dataIndex: 'COM_Libelle' },
				{ header: "Répertoire", dataIndex: 'COM_Repertoire', hidden: true },
				{ header: "Etat", dataIndex: 'DOC_Etat' },
				{ header: "Document", dataIndex: 'DOC_Fic' },
				{
					header   : 'lien', 
					width    : 32, 
					sortable : true, 
					renderer : lienvalid,
					dataIndex: 'lien',
					align : 'center'
				}, { header: "Descriptif", dataIndex: 'DOC_Descriptif' },
				{ header: "Nature", dataIndex: 'DOC_Nature' },
				{
					header	: "Date Echéance",
					xtype	: 'datecolumn', // use xtype instead of renderer
					dataIndex: 'DOC_DateEcheance',
					format: 'd/m/Y', // configuration property for Ext.grid.DateColumn
					align : 'center'
				}, { header: "Lib. Echéance", dataIndex: 'DOC_LibEcheance' },
				{
					header: "Date Fin Echéance",
					xtype: 'datecolumn', // use xtype instead of renderer
					dataIndex: 'DOC_DateFinEcheance',
					format: 'd/m/Y', // configuration property for Ext.grid.DateColumn
					align : 'center'
				}, { header: "Lib. Fin Echéance", sortable: true, dataIndex: 'DOC_LibFinEcheance' },
				{ header: "Mots-Clef", dataIndex: 'DOC_MotClef' },
				{ header: "Créé Par", dataIndex: 'DOC_CreePar' }
			]
		}),
		loadMask : true,
		layout: 'fit',
        stateful: true,
        stateId: 'GridObjets',      
		listeners: {
//			'rowdblclick': function(grid, rowIndex, e) {
			'celldblclick' : function( grid, rowIndex, columnIndex, e ) {
				rec = grid.getStore().getAt(rowIndex);
				fieldName = grid.getColumnModel().getDataIndex(columnIndex);
				if(fieldName == 'lien' && rec.get("lien") == "true") {
					window.open( "php/download.php?visu=1&idDoc=" + rec.get("IdDoc"), 'windoc' );
				} else {
					new formDocGed( {
						id : rec.get('IdDoc'), 
						ficDoc : rec.get('DOC_Fic'), 
						idCom : rec.get('IdCOM'), 
						comLib : rec.get('COM_Libelle'), 
						comRep : rec.get('COM_Rep') 
					} );
				}
			}
		},
		bbar: [ labelSelection, {
			text: 'Ajouter',
			iconCls:'icon-form-add',
			tooltip:'Ajoute les Objets Sélectionnés à la Sélection en Cours',
			handler: function(){
				var tabLigne = new Array();
				
				gridPanelDesc.getSelectionModel().each( function(rec){
					tabLigne.push( rec.get( 'IdDoc' ) );
				});
				Ext.Ajax.request({
					url:'./php/selectUpdate.php',
					params : { cmd:"ajout", arrId:tabLigne.join(',') },
					success:function(response){
						obj = Ext.util.JSON.decode(response.responseText);
						labelSelection.setText( 'Sélection (' + obj.compte +') :' ) 
					},
					failure: function(response, action){
						Ext.Msg.alert('Status', 'Erreur d\'authentification !');
					}
				});
			}
		}, {
			text: 'Extraire',
			iconCls:'icon-formulaire-supprime',
			handler: function(){
				// Récupère tous les ids des lignes sélectionnées
				var tabLigne = new Array();	
				gridPanelDesc.getSelectionModel().each( function(rec){
					tabLigne.push( rec.get( 'IdDoc' ) );
				});
				Ext.Ajax.request({
					url:'./php/selectUpdate.php',
					params : { cmd:"supp", arrId:tabLigne.join(',') },
					success:function(response){
						obj = Ext.util.JSON.decode(response.responseText);
						labelSelection.setText( 'Sélection (' + obj.compte +') :' );
					},
					failure: function(response, action){
						Ext.Msg.alert('Status', 'Erreur d\'authentification !');
					}
				});
			}
		}, {
			text: 'Voir',
			iconCls:'icon-selection-voir',
			handler: function(){
				jsonDSResultReqListDoc.reload({ params : { selection : 1} });
			}
		}, {
			text: 'Rassembler dans une Action',
			iconCls:'icon-folder-page',
			handler: function(){
				
				Ext.Msg.confirm(
					'Créer une Action ...',
					'Les Objets de la Sélection en Cours vont être Dupliqués dans une nouvelle Action.',
					function(r){
						if(r =='yes') {
							winCopy.show();
							Ext.Ajax.request({
								url:'./php/formCommission.php',
								params : { cmd: 'createFromSelection' },
								success:function(response){
									obj = Ext.util.JSON.decode(response.responseText);
									
									if(obj.success == true) {

										formCtrlCopySelect.form.setValues({
											action : obj.idCOM,
											rep : obj.repDest,
											nbCopies : obj.nbCopies
										})
									} else {
										winCopy.hide();
										Ext.Msg.alert('Erreur', obj.reason);
									}
								}
							});
						}
				});
			}
		},'-','->', '-',
		{ 	
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
			text: 'CSV',
			hidden:true,
			iconCls:'icon-table-export',
			handler: function(){
				tabIndex = jsonDSResultReqListDoc.collect( "IdDoc" );
				Ext.Ajax.request({
					url:'./php/selectExportDocGed.php',
					params : { selection:tabIndex.join(', ') },
					success:function(response){
						window.open("./php/forceDownload.php?download_dir=tmp/&download_file=export.csv", 'winUpload');
					}
				});
			}
		}, {
			text: 'XML',
			iconCls:'icon-table-export',
			handler: function(){
				tabIndex = jsonDSResultReqListDoc.collect( "IdDoc" );
				listCol = cbgCol.getValue() ;
				arrNomCol = new Array();
				uri = null;
				Ext.each( listCol, function(item) {
					if(item.name == 'URI') {
						uri = ( window.location.href.split('?') )[0] + "?tb=ged&id=";
					} else {
						arrNomCol.push( item.name );
					}
				});
				paramsExport = {
						selection:tabIndex.join(', '),
						colonnes:arrNomCol.join(', '),
						format : 'xml',
					};
				if( uri != null ) {
					paramsExport.uri = uri;
				};
				Ext.Ajax.request({
					url:'./php/selectExportDocGed.php',
					params : paramsExport,
					success:function(response){
						window.open("./php/forceDownload.php?download_dir=tmp/&download_file=export.xml");
					}
				});
			}
		}, '-', {
			text: '  Import', //'Sélection ...',
			handler: function(){
				win = winUpload('objets');
				win.show();
			}
		}, '-', {xtype: 'tbspacer', width: 50},
		{
			text: 'Nouveau',
			iconCls:'icon-form-add',
			handler: function(){
				var idCommission = 0;
				if(comboCommission.getValue() != 0) { // prise en  compte de la corbeille (-1)
					idCommission = comboCommission.getValue();
				}
				new formDocGed( { id : 0, ficDoc : "", idCom : idCommission, comRep : "" } );
			}
		}]
	});

	gridPanelDesc.on('activate', function(grid, rowIndex, e) {
		Ext.getCmp('cardSelection').layout.setActiveItem( 1 );
		Ext.Ajax.request({
			url:'./php/selectUpdate.php',
			params : { cmd:"compte" },
			success:function(response){
				obj = Ext.util.JSON.decode(response.responseText);
				labelSelection.setText( 'Sélection (' + obj.compte +') :' ) 
			},
			failure: function(response, action){
				Ext.Msg.alert('Status', 'Erreur d\'authentification !');
			}
		});
	});

	jsonDSResultReqListDoc.load();
	
	return gridPanelDesc;
}