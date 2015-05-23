
	var gridPanelChrono = new Ext.grid.GridPanel({
		title: "Chronologie des Mises ‡ Jour",
		store: new Ext.data.JsonStore({
			url: 'php/selectList.php',
			baseParams : { nomListe : 'chrono', nomTable : 't_chrono_chr', recId : unId },
			root: 'results',
			totalProperty: 'total',
			fields: [ 
				{name: 'IdCHR', type: 'int'},
				{name: 'Action', type: 'string'},
				{name: 'CHR_User', type: 'string'},
				{name: 'CHR_Date', type: 'date', dateFormat: "Y-m-d H:i:s"},
				{name: 'CHR_Comment', type: 'string'}
			]}),
		autoFill:true,
		columns: [{
		 	header: "Ident.",
			xtype: 'numbercolumn',
			sortable: true,
			dataIndex: 'IdCHR',
			format : '0',
			align : 'right',
			width :48
		}, {
			header: "Nature Modif.",
			sortable: true,
			dataIndex: 'Action',
			width :64 
		}, {
			header: "Utilisateur",
			dataIndex: 'CHR_User',
			sortable: true,
			width :220 
		}, {
			header: "Date M‡J",
			dataIndex: 'CHR_Date',
			xtype: 'datecolumn', // use xtype instead of renderer
			sortable: true,
			width :128,
			format: 'd/m/Y ‡ H\\hi' // configuration property for Ext.grid.DateColumn
		}, {
			header: "Commentaire",
			dataIndex: 'CHR_Comment',
			sortable: true,
			editable: true,
            editor: new Ext.form.TextField({
                allowBlank: false
            }),
			width :220 
		}]
	});
	
