/**
 * panelListChrono.js
 * 
 * @auteur     marc laville
 * @Copyleft 2011-2012
 * @date       27/11/2011
 * @version    0.2
 * @revision   $1$
 *
 * @date revision   13/06/2012 -- Gere la zone de recherche
 * @date  revision 25/06/2012 Calcul du bouton "Lancer la Recherche"
 * @date  revision 01/07/2012 Calcul de la toolbar "Lancer la Recherche" (remplace le bouton)
 *
 * Affichage une liste de chrono
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

var jsonDSResultReqListChrono = new Ext.data.JsonStore({
		url: './php/selectListChrono.php',
		root: 'results',
		totalProperty: 'total',
		fields: [ 
			{name: 'IdCHR', type: 'int'},
			{name: 'Action', type: 'string'},
			{name: 'CHR_Comment', type: 'string'},
			{name: 'CHR_User', type: 'string'},
			{name: 'CHR_Date', type: 'date', dateFormat: "Y-m-d H:i:s"},
			{name: 'IdDoc', type: 'int'},
			{name: 'DOC_Libelle', type: 'string'},
			{name: 'DOC_IdCOM', type: 'int'},
			{name: 'COM_Libelle', type: 'string'},
			{name: 'DOC_Fic', type: 'string'}
		]
	});

var afficheListeChrono = function() {

	var gridPanelDesc = new Ext.grid.GridPanel({
		title: '<img alt="Chrono" src="./img/clock.png"> Chrono',
		store: jsonDSResultReqListChrono,
//		bodyCssClass : 'custom-class-grid',
		autoFill:true,
		colModel: new Ext.grid.ColumnModel({
			defaults: {	sortable: true },
			columns: [
				{ 	header: "Ident.",
					xtype: 'numbercolumn',
					dataIndex: 'IdCHR',
					format : '0',
					align : 'right',
					width :48
				},
				{ header: "type", dataIndex: 'Action' },
				{ header: "Commentaire", dataIndex: 'CHR_Comment' },
				{ header: "Utilisateur", dataIndex: 'CHR_User' },
				{ header: "Action", dataIndex: 'COM_Libelle' },
				{ 
					header: "Date",
					dataIndex: 'CHR_Date',
					xtype: 'datecolumn', // use xtype instead of renderer
					format: 'd/m/Y à H\\hi' // configuration property for Ext.grid.DateColumn
				},
				{ header: "IdDoc", hidden : true, dataIndex: 'IdDoc' },
				{ header: "Libellé", dataIndex: 'DOC_Libelle' },
				{ header: "Fichier", dataIndex: 'DOC_Fic' }
			]
		}),
		loadMask : true,
		layout: 'fit',
        stateful: true,
        stateId: 'Chrono',      
		listeners: {
			'rowdblclick': function(grid, rowIndex, e){
				rec = grid.store.getAt( rowIndex );
				new formDocGed( {
					id : rec.get('IdDoc'), 
					ficDoc : rec.get('DOC_Fic'), 
					idCom : rec.get('DOC_IdCOM') 
				} );
			}
		}
	});
	
	jsonDSResultReqListChrono.load();
	
	gridPanelDesc.on('activate', function(grid, rowIndex, e) {
		Ext.getCmp('cardSelection').layout.setActiveItem( 4 );
	});
	return gridPanelDesc;
}

var fpChrono = new  Ext.form.FormPanel({
	layout:'form',
	autoScroll: true,
	labelAlign : 'top',
	border:false,
	defaults:{anchor:'95%'},
	items: [{
		fieldLabel: 'du',
		xtype: 'datefield',
		format:'d/m/Y',
		altFormats:'Y-m-d',
		name: 'DateInf'
	}, {
		xtype: 'datefield',
		fieldLabel: 'au',
		format:'d/m/Y',
		altFormats:'Y-m-d',
		name: 'DateSup'
	}, new Ext.form.TextField({
		name:'recherche',
		selectOnFocus: true,
		fieldLabel:'Rechercher',
		emptyText: 'Texte Recherché',
	})],
 

	tbar: [ tbSelection( function() {
			// Calcul les parametre de la recherche
			params = {};
			var fv = fpChrono.getForm().getFieldValues();
			
			// Récupération des critère de date
			dtInf = fv.DateInf;
			if( Ext.isDate(dtInf) ) {
				params.dateInf = dtInf.format("Y-m-d");
			}
			dtSup = fv.DateSup;
			if( Ext.isDate(dtSup) ) {
				params.dateSup = dtSup.format("Y-m-d");
			}

			if(fv.recherche.length) {
				params.tfRecherche = fv.recherche;
			}
			
			jsonDSResultReqListChrono.reload({ "params" : params });
		}
	)]
})

