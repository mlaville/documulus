/**
 * panelListCommission.js
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011
 * @date       06/08/2010
 * @version    0.5.1
 * @revision   $1$
 * @date revision   27/03/2011 Révision de la gestion des colonnes 
 *
 * Gestion des Activitées
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

 var jsonDSResultRequete = new Ext.data.JsonStore({
	url: 'php/selectListActivite.php',
	baseParams : { vbSelect : 'SELECT'
		+ ' IdActivite, ACT_nomCentre, ACT_Societe, ACT_Type, ACT_DescriptCourt,'
		+ ' ACT_LieuDit, ACT_Commune, ACT_CodePostal, ACT_NomDept, ACT_Bassin, ACT_DistCentre, ACT_CoordGeo,'
		+ ' ACT_TypePublic, ACT_Handicap, ACT_PeriodeOuverture, ACT_NomCorresp, ACT_NumTel, ACT_NumTel2, ACT_NumFax, ACT_Courriel,'
		+ ' ACT_Url, ACT_DocPapier, ACT_Langue'
		+ ' FROM activite' },
	root: 'results',
	totalProperty: 'total',
	fields: [ 
		{name: 'IdActivite', type: 'int'},
//			{name: 'ACT_couleur', type: 'string'},
		{name: 'ACT_nomCentre', type: 'string'},
		{name: 'ACT_Type', type: 'string'},
		{name: 'ACT_Societe', type: 'string'},
		{name: 'ACT_DescriptCourt', type: 'string'},
		{name: 'ACT_LieuDit', type: 'string'},
		{name: 'ACT_Commune', type: 'string'},
		{name: 'ACT_CodePostal', type: 'string'},
		{name: 'ACT_NomDept', type: 'string'},
		{name: 'ACT_Bassin', type: 'string'},
		{name: 'ACT_DistCentre', type: 'string'},
		{name: 'ACT_CoordGeo', type: 'string'},
		{name: 'ACT_TypePublic', type: 'string'},
		{name: 'ACT_Handicap', type: 'string'},
		{name: 'ACT_PeriodeOuverture', type: 'string'},
		{name: 'ACT_NomCorresp', type: 'string'},
		{name: 'ACT_NumTel', type: 'string'},
		{name: 'ACT_Courriel', type: 'string'}
	]
});

var afficheListeActivite = function() {

var myMask = new Ext.LoadMask(
	Ext.getBody(),
	{msg:"Chargement...",
	store: jsonDSResultRequete});
	
var gridPanelDesc = new Ext.grid.GridPanel({
//		title: "Liste des Activités",
		title: '<img alt="Activités" src="./img/sport_soccer.png"> Liste des Activités',
		store: jsonDSResultRequete,
		autoFill:true,
		colModel: new Ext.grid.ColumnModel({
			defaults: {	sortable: true },
			columns: [
				{ 	header: "Ident.",
					xtype: 'numbercolumn',
					dataIndex: 'IdActivite',
					format : '0',
					align : 'right',
					width :48
				},
				{ header: "Zone Géographique", dataIndex: 'ACT_nomCentre' },
				{ header: "Type", align: 'left', dataIndex: 'ACT_Type' },
				{ header: "Libellé", dataIndex: 'ACT_Societe' },
				{ header: "Descriptif", dataIndex: 'ACT_DescriptCourt' },
				{ header: "LieuDit", dataIndex: 'ACT_LieuDit' },
				{ header: "Commune", dataIndex: 'ACT_Commune' },
				{ header: "Code Postal", align: 'center', dataIndex: 'ACT_CodePostal' },
				{ header: "Dépt.", dataIndex: 'ACT_NomDept' },
				{ header: "Bassin.", dataIndex: 'ACT_Bassin' },
				{ header: "Handicap", dataIndex: 'ACT_Handicap' },
				{ header: "Dist. Centre", dataIndex: 'ACT_DistCentre', align : 'right' },
				{ header: "Période Ouverture", dataIndex: 'ACT_PeriodeOuverture' },
				{ header: "Correspondant", dataIndex: 'ACT_NomCorresp' },
				{ header: "Tél.", dataIndex: 'ACT_NumTel' },
				{ header: "Mail", dataIndex: 'ACT_Courriel' }
			]
		}),
		loadMask : true,
		layout: 'fit',
//        closable:true,
        stateful: true,
        stateId: 'Activite',      
		bbar: ['->', '-', { 
				text: 'Nouveau',
				iconCls:'icon-form-add',
				handler: function(){
					new formActivite(0);
				}
		}],
		listeners: {
			'rowdblclick': function(grid, rowIndex, e){
				new formActivite( grid.store.getAt( rowIndex ).get('IdActivite') );
			}
		}
	});

	gridPanelDesc.on('activate', function(grid, rowIndex, e) {
		Ext.getCmp('cardSelection').layout.setActiveItem( 0 );
		Ext.getCmp('cardSelection').layout.activeItem.doLayout( );
	});

	jsonDSResultRequete.load();
	
	return gridPanelDesc;
}
