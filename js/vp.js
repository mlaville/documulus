/**
 * vp.js
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011-2012
 * @date       04/08/2010
 * @version    0.8.3
 * @revision   $10$
 *
 * @date   revision    21/08/2010 Custom du titre
 * @date   revision    23/11/2010 Elements de recherche sur les docs Ged en variables globales
 * @date   revision    27/11/2010 Gestion des dates échéance comme critères de sélection
 * @date  revision 02/05/2011 Gestion des demandes de Login
 * @date  revision 25/11/2011 Personnalisation de l'entete (./tpl/entete.html)
 * @date  revision 27/11/2011 Ajout liste chrono
 * @date  revision 25/06/2012 Calcul du bouton "Lancer la Recherche"
 * @date  revision 01/07/2012 Calcul de la toolbar "Lancer la Recherche" (remplace le bouton)
 * @date_revision   marc laville 20/10/2012 : Affichage de l espace occupé
 * @date_revision   marc laville 18/11/2012 : Modification du comportement du combo Action (filtre)
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

/*
 * Definition du panel de sélection des docs Ged
 */
	var tfRechercheDocGed = new Ext.form.TextField({
		name:'tfRechercheDocGed',
		selectOnFocus: true,
		fieldLabel:'Rechercher',
		emptyText: 'Texte Recherché',
	});

	var comboCommission = new Ext.form.ComboBox({
		fieldLabel:'Action',
		name:'DOC_IdCOM',
		store: storeAction,
/*		store: new Ext.data.JsonStore({
			url: 'php/listCommission.php',
			root: 'commissions',
			fields: ['IdCOM', 'COM_Libelle']
		}),*/
		displayField: 'COM_Libelle',
		valueField:'IdCOM',
//		mode: 'remote',
		mode: 'local',
		triggerAction: 'all',
		emptyText: 'Sélection de l Action',
		selectOnFocus: true,
		listeners:{
			'select': function() {
				lanceSelectDocGed();
			}
		},
		forceSelection: true,
		selectOnFocus: true
	});

	var dateEcheance = new Ext.form.CompositeField({
		fieldLabel: 'Echéance',
		name: 'Echeance',
		items: [{
			xtype: 'label',
			text : 'du'
		}, {
			xtype: 'datefield',
			width: 92,
			format:'d/m/Y',
			altFormats:'Y-m-d',
			name: 'DateInfEcheance'
		}, {
			xtype: 'label',
			text : 'au'
		}, {
			xtype: 'datefield',
			width: 92,
			format:'d/m/Y',
			altFormats:'Y-m-d',
			name: 'DateSupEcheance'
		}]
	});

	var dateFinEcheance = new Ext.form.CompositeField({
		fieldLabel: 'Fin Echéance',
		name: 'FinEcheance',
		items: [{
			xtype: 'label',
			text : 'du'
		}, {
			xtype: 'datefield',
			width: 92,
			format:'d/m/Y',
			altFormats:'Y-m-d',
			name: 'DateInfFinEcheance'
		}, {
			xtype: 'label',
			text : 'au'
		}, {
			xtype: 'datefield',
			width: 92,
			format:'d/m/Y',
			altFormats:'Y-m-d',
			name: 'DateSupFinEcheance'
		}]
	});
				
	var fpDocGed = new Ext.form.FormPanel({
		layout:'form',
		autoScroll: true,
		labelAlign : 'top',
		border:false,
		defaults:{anchor:'95%'},
		items: [
			comboCommission,
			tfRechercheDocGed,
			{
				xtype: 'checkboxgroup',
				fieldLabel: 'Rubrique',
				// Put all controls in a single column with width 100%
				columns: 1,
				items: [
					{boxLabel: 'Libellé', name: 'DOC_Libelle'}, //, checked: true
					{boxLabel: 'Etat', name: 'DOC_Etat'},
					{boxLabel: 'Mot Clef', name: 'DOC_MotClef'},
					{boxLabel: 'Nature', name: 'DOC_Nature'},
					{boxLabel: 'Descriptif', name: 'DOC_Descriptif'}
				]
			}, dateEcheance, dateFinEcheance
			],
		tbar: [ tbSelection( function() {
				lanceSelectDocGed();
			}
		)]
	})
	
	var lanceSelectDocGed = function() {
		params = {};
		var arrEntrees = fpDocGed.getForm().getValues(true).split("&");
		var textRecheche = tfRechercheDocGed.getValue();

		if(comboCommission.getValue() != 0) { // prise en  compte de la corbeille (-1)
			params.idCommission = comboCommission.getValue();
		}
		
		// Récupération des critère de date
		valEcheance = dateEcheance.items;
		dtInf = valEcheance.items[0].getValue();
		if( Ext.isDate(dtInf) ) {
			params.echeanceInf = dtInf.format("Y-m-d");
		}

		dtSup = valEcheance.items[1].getValue();
		if( Ext.isDate(dtSup) ) {
			params.echeanceSup = dtSup.format("Y-m-d");
		}
		
		valEcheance = dateFinEcheance.items;
		dtInf = valEcheance.items[0].getValue();
		if( Ext.isDate(dtInf) ) {
			params.finEcheanceInf = dtInf.format("Y-m-d");
		}

		dtSup = valEcheance.items[1].getValue();
		if( Ext.isDate(dtSup) ) {
			params.finEcheanceSup = dtSup.format("Y-m-d");
		}
		
		// Contruction du tableau des Checkbox cochées
		var arrCheck = new Array();
		for( i = 0 ; i < arrEntrees.length ; i++ ) {
			entree = arrEntrees[i].split("=")[0];
			if( entree != "COM_Libelle" && entree != "tfRechercheDocGed" && entree != "DOC_IdCOM" &&  entree.substr(0, 4) != "Date" ) {
				arrCheck.push(entree);
			}
		}
		
		if(textRecheche.length) {
			params.tfRecherche = textRecheche;
			if(arrCheck.length) {
				params.rubRecherche = arrCheck.join();
			}
		}
		
		jsonDSResultReqListDoc.reload({ "params" : params });
	}

var vp;
	
function showViewPort(nomUser, nomSociete) {

	storeAction.load();

	Ext.state.Manager.setProvider(new Ext.state.CookieProvider({
		expires: new Date(new Date().getTime()+(1000*60*60*24*7)), //7 days from now
	}));
	
/*
 * Definition du panel de sélection des activités
 */
	var tfRecherche = new Ext.form.TextField({
		name:'tfRecherche',
		selectOnFocus: true,
		fieldLabel:'Rechercher',
		emptyText: 'Texte Recherché',
	});

	var comboZoneGeo = new Ext.form.ComboBox({
		name:'ACT_nomCentre',
		fieldLabel:'Zone Géographique',
		store: new Ext.data.JsonStore({
			url: 'php/listCentre.php',
			root: 'centres',
			fields: ['nomCentre']
		}),
		displayField: 'nomCentre',
		valueField:'nomCentre',
		setReadOnly: true,
		typeAhead: true,
		mode: 'remote',
		triggerAction: 'all',
		emptyText: 'Sélection de la Zone Géographique',
		forceSelection: true,
		listeners:{
			'select': function() {
				lanceSelectActi();
			}
		},
		selectOnFocus: true
	});
	
	var checkboxGroupRubrique = new Ext.form.CheckboxGroup({
		xtype: 'checkboxgroup',
		fieldLabel: 'Rubrique',
		itemCls: 'x-check-group-alt',
		// Put all controls in a single column with width 100%
		columns: 1,
		items: [
			{boxLabel: 'Libellé', name: 'ACT_Societe'}, //, checked: true
			{boxLabel: 'Type', name: 'ACT_Type'},
			{boxLabel: 'Descriptif Court', name: 'ACT_DescriptCourt'},
			{boxLabel: 'Descriptif Long', name: 'ACT_DescriptLong'},
			{boxLabel: 'Info. Complémentaires', name: 'ACT_InfoComplem'}
		]
	});

	var fpActivite = new  Ext.form.FormPanel({
			layout:'form',
			autoScroll: true,
			labelAlign : 'top',
			border:false,
			defaults:{anchor:'95%'},
			items: [
				comboZoneGeo,
				tfRecherche,
				checkboxGroupRubrique
			],
		tbar: [ tbSelection( function() {
				lanceSelectActi();
			}
		)]
	})

	var lanceSelectActi = function() {
		// Calcul les parametre de la recherche
		params = {};
		// Selection sur la zone géo
		if(comboZoneGeo.getValue().length > 1) {
			params.ACT_nomCentre = comboZoneGeo.getValue();
		}
		if(tfRecherche.getValue().length) {
			params.tfRecherche = tfRecherche.getValue();

			// Contruction du tableau des Checkbox cochées
			var arrEntrees = fpActivite.getForm().getValues(true).split("&");
			var arrCheck = new Array();
			for( i in arrEntrees )	{
				try {
					entree = (arrEntrees[i].split("="))[0];
				}
				catch(err) { break; }
				// Exclu les champs qui ne sont pas des Checkbox
				if( entree != "ACT_nomCentre" && entree != "tfRecherche" ) {
					arrCheck.push(entree);
				}
			}
			if(arrCheck.length) {
				params.rubRecherche = arrCheck.join();
			}
		}

		jsonDSResultRequete.reload({ "params" : params });
	}

/*
 * Definition du panel de sélection des Utilisateurs
 */
	var tfRechercheUser = new Ext.form.TextField({
		name:'tfRechercheUser',
		selectOnFocus: true,
		fieldLabel:'Rechercher',
		emptyText: 'Texte Recherché',
	});
	
	var fpUser = new  Ext.form.FormPanel({
		layout:'form',
		autoScroll: true,
		labelAlign : 'top',
		border:false,
		defaults:{anchor:'95%'},
		items: [
			tfRechercheUser
		],
//		buttons: [ boutonSelection( function() {
		tbar: [ tbSelection( function() {
			// Calcul les parametre de la recherche
			params = {};
			if(tfRechercheUser.getValue().length) {
				params.tfRecherche = tfRechercheUser.getValue();
			}
			jsonDSResultReqListUser.reload({ "params" : params });
			}
		)]
	})

/*
 * Definition du panel de sélection des Commissions
 */
	var tfRechercheCom = new Ext.form.TextField({
		name:'tfRechercheCom',
		selectOnFocus: true,
		fieldLabel:'Rechercher',
		emptyText: 'Texte Recherché'
	});
	
	var fpCom = new  Ext.form.FormPanel({
		layout:'form',
		autoScroll: true,
		labelAlign : 'top',
		border:false,
		defaults:{anchor:'95%'},
		items: [
			tfRechercheCom
		],
//		buttons: [ boutonSelection( function() {
		tbar: [ tbSelection( function() {
			// Calcul les parametre de la recherche
				params = {};
				if(tfRechercheCom.getValue().length) {
					params.tfRecherche = tfRechercheCom.getValue();
				}
				jsonDSResultReqListCom.reload({ "params" : params });
			}
		)]
	})

/*
 * Affichage de l'environnement
 */
	vp = new Ext.Viewport({
		layout: 'border',
		items: [{
			region: 'north',
			height: 28,
			layout: 'hbox',
			layoutConfig: {
				align: 'stretch'
			},
			defaults: {
				padding : 2,
				flex: .8
			},
			items: [{
				html:nomSociete,
				bodyCssClass: 'custom-class-titre'
			},{
				autoLoad: './tpl/entete.html',
				style: { textAlign: 'center' }
			}, {
				html:nomUser,
				style: {
					fontFamily: 'arial,tahoma,helvetica,sans-serif',
					textAlign: 'right'
				}
			}, {
				xtype	: 'button',
				iconCls	: 'icon-lock-edit',
				text	: 'mot de passe',
				width 	: 120,
				menu: new Ext.menu.Menu({
					plain: true,
					items: [formPass()]
				})
			}, {
				xtype	: 'button',
				text	: 'déconnexion',
				width 	: 120,
				iconCls	: 'icon-database-deconnect',
				handler	: function(){
					Ext.Ajax.request({
						url: 'php/deconnexion.php',
						callback : function(){ window.location.reload(); }
					});
				}
			}]
		}, {
			region: 'south',
			height: 16,
			border : false,
			layout: 'hbox',
			layoutConfig: {
				align: 'stretch'
			},
			defaults: {
				flex: 1,
				border : false
			},
			fieldDefaults: {
				labelAlign: 'left',
				labelWidth: 90,
				anchor: '100%'
			},
			items: [{
				html: '<a href="http://www.polinux.net" target="_blank"><img style="border-style: none;" alt="www.polinux.net" src="./img/polinux-micro.gif" /></a>'
				}, appDocumulus.fieldEspace
			]
		}, {
			region: 'west',
//			title: 'Recherche',
//			iconCls: 'icon-loupe',
			width: 180,
			layout:'card',
			id  : 'cardSelection',
			activeItem: 0, // make sure the active item is set on the container config!
			collapsible: true,
			bodyStyle:'padding:5px',
			split: true,
			items: [ fpActivite, fpDocGed, fpUser, fpCom, fpChrono ]
		} , {
			region: 'center',
			xtype : 'tabpanel',
			id  : 'tabPanel',
			items: [ afficheListeChrono(), afficheListeDoc() ]
		}]
	});
	
	droits = 0;
	Ext.Ajax.request({
		url:'php/controlIdent.php', 
		success:function(response){
			var panelCentre = Ext.getCmp('tabPanel');
			obj = Ext.util.JSON.decode(response.responseText);
			
			if(obj.droits > 0) {
				if(obj.droits & 1) {
					panelCentre.add( afficheListeActivite() );
				}
				if(obj.droits & 2) {
					panelCentre.add( afficheListeUser() );
				}
//				Ext.getCmp('tabPanel').add( afficheListeCom() );
			}
			if(obj.nbDroitAction > 0 || obj.droits & 4) {
				panelCentre.add( afficheListeCom() );
			}
		},
		failure: function(response, action){
			Ext.Msg.alert('Status', 'Erreur d\'authentification !');
		}
	});
	
	Ext.getCmp('tabPanel').setActiveTab(0); // Onglet Objets
	
	// Traitement des parametres passés dans l'URI
	search = window.location.search;
	if(search.length) {
		args = search.substr(1).split("&");
		if(args.length < 2) {
			args.unshift( "tb=act" );
		}
		table = args[0].split("=")[1];
		ident = parseInt( (args[1].split("="))[1] );
		if( ident > 0 ) {
			switch(table) {
				case "act" : formActivite(ident);
					break;
				case "ged" : formDocGed( { id : ident } );
					break;
				case "usr" : formUser(ident);
					break;
				case "dmd" : formUser(ident, true);
					break;
				case "com" : formCommission( ident );
					break;
			}
		}
	}
}