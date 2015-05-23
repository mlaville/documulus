/**
 * formDocGed.js
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011-2012
 * @date       07/07/2010
 * @version    1.1
 * @revision   $17$
 * @date revision   09/08/2010 Correction requete selection
 * @date revision   16/08/2010 Gestion erreur en Mise à Jour (par ex. Pas de Droits)
 * @date revision   20/08/2010 design champ téléchargement
 * @date revision    13/09/2010 -- Gestion du chrono
 * @date revision    16/10/2010 -- Ajout champ Echéances + Commission -> Action
 * @date revision   31/10/2010 -- Gestion du commentaire sur le chrono
 * @date revision   22/11/2010 -- Ajout de la coche Visibilité pour le superviseur
 * @date revision   04/12/2010 -- Force la sélection de l'action
 * @date revision   05/12/2010 -- Gestion "mise à la corbeille"
 * @date revision   16/01/2011 -- Contrôle la valitité du nom de fichier (apostrophe)
 * @date revision   30/01/2011 -- Permet le téléchargement du doc lié, plutôt que l'affichage
 * @date revision   08/02/2011 --  En Creation, choisi l'Action séléctionnée dans le panneau de recherche
 * @date revision   23/05/2011 --  Debug pb mise à la corbeille de plusieurs fiches
 * @date revision   01/08/2012 --  Gestion du téléchargement
 * @date revision   12/11/2012 --  Affichage dans un layout 'border'
 * @date revision   17/11/2012 --  Miniature du doc joint (ok pour images et pdf)
 * @date revision   19/11/2012 --  Desactive la tabulation si le doc joint n'est pas visualisable (wav, doc, ..)
 * @date revision   27/11/2012 --  Debug ouverture doc affiche dans l'URI
 *
 * A Faire
 * - visualisation des txt et des wav
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
/*
 * A Faire : gestion focus
 */
 
/*
  * Création du panneau d'affichage en fonction du format de fichier détecté par l'extention
  * @nomFic nom complet du fichier
  */
function panelFic(nomFic, idCom, idDoc) {

	switch( ( nomFic || "" ).split(".").pop().toLowerCase() ) {
		case 'pdf':
			retour = new Ext.Panel({
				title:'Doc.',
				layout:'fit',
				html : '<object style="height:95%; width:100%;" type="application/pdf" data="./php/readFile.php?nomFic=' + nomFic 
					+ '&IdCOM=' + idCom + '#toolbar=0&navpanes=0"></object>'
			});
		break;
		case 'js': ;
		case 'css': ;
		case 'sql': ;
		case 'txt':
			retour = new Ext.Panel({
				title:'Doc.',
				layout:'fit',
//				autoLoad : "./php/readFile.php?nomFic=" + nomFic + '&IdCOM=' + idCom
				autoLoad : "./php/download.php?visu=1&idDoc=" + idDoc
			});
			break;
		case "bin": ;
		case "exe": 
			break;
		case "xls": ;
			break;
		case "ppt": ;
			break;
			
		case "wav": 
			retour = new Ext.Panel({
				hidden:true
/*				title:'Doc.',
				layout:'fit',
				html:'<audio controls="controls">'
					+ '<source src="./php/readFile.php?nomFic=' + nomFic + '&IdCOM=' + idCom + '" type="audio/wav" />'
					+ 'Your browser does not support the audio element.'
					+ '</audio>'*/
			});
			break;

		case "gif": ;
		case "tiff": ;
		case "tif": ;
		case "png": ;
		case "jpeg": ;
		case "jpe": ;
		case "jpg": 
			retour = new Ext.Panel({
				title:'Doc.',
				layout:'fit',
				html : '<img src="./php/readImage.php?nomFic=' + nomFic + '&IdCOM=' + idCom + '" />'
			});
			break;

		case "zip": ;
		case 'xml':
		case 'rtf':
		case 'doc':
		default: ;
			retour = new Ext.Panel({
				title:'Doc.',
				disabled:true
			});
			break;
			break;
	}
	return retour;
}

// Ext.getCmp('first').focus(true,10);
var storeAction = new Ext.data.JsonStore({
	url: 'php/listCommission.php',
	root: 'commissions',
	fields: ['IdCOM', 'COM_Libelle']
});

function formDocGed(unObjet) {

	var unId = unObjet.id;
	
	var comboMotClef = new Ext.form.ComboBox({
		fieldLabel:'Mot Clef',
		name:libelleChamp( 'DOC_MotClef' ),
		store: new Ext.data.JsonStore({
			url: 'php/selectList.php',
			baseParams : { nomListe : 'mots_clef' },
			root: 'results',
			fields: ['mot_clef']
		}),
		displayField: 'mot_clef',
		valueField:'mot_clef',
		mode: 'local',
		triggerAction: 'all',
		selectOnFocus: true
	});
	
	var comboAction = new Ext.form.ComboBox({
		fieldLabel:libelleChamp( 'Action' ),
		name:'DOC_IdCOM',
		store: storeAction,
		displayField: 'COM_Libelle',
		valueField:'IdCOM',
		mode: 'local',
		triggerAction: 'all',
		listeners:{
			'select': function() {
				idComSelect = comboAction.getValue();
				comboMotClef.store.load( { params : { idCom : idComSelect } } );
				Ext.Ajax.request({
					url:'./php/selectList.php',
					params : { nomListe : 'repertoire', idCom : idComSelect },
					success:function(response){
						obj = Ext.util.JSON.decode(response.responseText);
						comboAction.ownerCt.tfRep.setValue(obj.results[0].COM_Repertoire);
						comboAction.ownerCt.tfPath.setValue(obj.results[0].COM_Path);
					},
					failure: function(response, action){
						Ext.Msg.alert('Status', 'Erreur d\'authentification !');
					}
				});
				
			}
		},
		emptyText: 'Sélection de l\'Action',
		selectOnFocus: true,
		anchor: '95%' 
	});

	var gridPanelChrono = new Ext.grid.EditorGridPanel({
		title: "Chrono. des MàJ",
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
		colModel: new Ext.grid.ColumnModel({
			defaults: {	sortable: true },
			columns: [{
				header: "Ident.",
				xtype: 'numbercolumn',
				dataIndex: 'IdCHR',
				format : '0',
				align : 'right',
				width :48
			}, {
				header: "Nature Modif.",
				dataIndex: 'Action',
				width :64 
			}, {
				header: "Utilisateur",
				dataIndex: 'CHR_User',
				width :220 
			}, {
				header: "Date MàJ",
				dataIndex: 'CHR_Date',
				xtype: 'datecolumn', // use xtype instead of renderer
				width :128,
				format: 'd/m/Y à H\\hi' // configuration property for Ext.grid.DateColumn
			}, {
				header: "Commentaire",
				dataIndex: 'CHR_Comment',
				editable: true,
				editor: new Ext.form.TextField({ }),
				width :220 
			}]
		}),
		listeners: {
			'afteredit': function(e){
				Ext.Ajax.request({
					url:'php/crudChrono.php',
					params: { cmd:'save' , IdCHR: e.record.data.IdCHR, CHR_Comment: e.value },
					success:function(response){ },
					failure: function(response, action){
						Ext.Msg.alert('Status', 'Erreur d\'authentification !');
					}
				})
			}
		}
	});

 	var champNomFic = new Ext.form.TextField({
		fieldLabel: libelleChamp( 'Documentation' ),
		name: 'DOC_Fic',
		readOnly : true,
		value: unObjet.ficDoc, 
		ref: '../../tfDoc',
		anchor: '98%'
	});

	var tabPanelDescript = new Ext.TabPanel({
			margins:'3 3 3 0',
			itemId:'actOnglet',
			region: 'center',
			plain:true,
			activeTab: 0,
    /*
              By turning off deferred rendering we are guaranteeing that the
              form fields within tabs that are not activated will still be rendered.
              This is often important when creating multi-tabbed forms.
          */
			deferredRender: false,
			items:[
				panelFic(champNomFic.getValue(), unObjet.idCom, unId),
				{
					title:'Descript.',
					layout:'fit',
					items: {
						xtype:'textarea',
						name: 'DOC_Descriptif'
					}
				}, {
					title:'Info. Compl.',
					layout:'fit',
					items: {
						xtype:'textarea',
						name: 'DOC_InfoComplementaires',
						anchor:'95% 95%'
					}
				},
				gridPanelChrono
			]
	});
	
	// Si le doc n'est pas visualisable, on passe sur la tabulation suivante
	panelDoc = tabPanelDescript.items.items[0];
	tabPanelDescript.setActiveTab( panelDoc.disabled ? 1 : 0 );
	
	href = window.location.href;

 	var formChamps = new Ext.Panel({
			border:false,
            region: 'west',
			width:460,
 //			collapsible: true,
			split: true,
			layout:'form',
			defaultType: 'textfield',
			defaults:{anchor:'95%'},
			items:[{
				itemId:'ident',
				xtype:'hidden',
				name:'IdDoc',
				value: unId 
			}, {
				name:'DOC_Libelle', fieldLabel:libelleChamp( 'Libelle' ),
			}, comboAction, {
				xtype:'combo',
				fieldLabel:libelleChamp( 'Etat' ),
				name:'DOC_Etat',
				store: new Ext.data.JsonStore({
					url: 'php/listEtat.php',
					root: 'etats',
					fields: ['etat']
				}),
				displayField: 'etat',
				valueField:'etat',
				mode: 'remote',
				triggerAction: 'all',
				emptyText: 'Etat',
				selectOnFocus: true
			}, comboMotClef, {
				name:'DOC_Nature',
				fieldLabel:libelleChamp( 'Nature' )
			}, {
				xtype: 'compositefield',
				fieldLabel: libelleChamp( 'Echéance' ),
				items: [{
					xtype: 'datefield',
					width: 92,
					format:'d/m/Y',
					altFormats:'Y-m-d',
					name: 'DOC_DateEcheance'
				}, {
					xtype: 'textfield',
					name: 'DOC_LibEcheance'
				}]
			}, {
				xtype: 'compositefield',
				fieldLabel: libelleChamp( 'Fin échéance' ),
				items: [{
					xtype: 'datefield',
					width: 92,
					name: 'DOC_DateFinEcheance',
					format:'d/m/Y',
					altFormats:'Y-m-d'
				}, {
					xtype: 'textfield',
					name: 'DOC_LibFinEcheance'
				}]
			}, {
				xtype:'panel',
				layout:'column',
				autoHeight: true,
				border:false,
				items: [{
					layout:'form',
					border:false,
					columnWidth:1.0,
					bodyStyle: 'padding: 0 10px 0 0;',
					items: champNomFic
				},{
					xtype: 'fileuploadfield',
					buttonOnly: true,
					name: 'ficDocPapier',
					buttonText: '', //'Sélection ...',
					buttonCfg: {
						iconCls: 'icon-explore', iconAlign: 'right'
					},
					listeners: {
						'fileselected': function(fb, v){
							// refuse les apostrophes
							nomFic = v.split("\\").pop();
							if( nomFic.indexOf("'") > 0 ) {
								alert("Caractère Invalide (apostrophe)\nRenommez le Fichier avant de le Sélectionner");
							} else {
								champNomFic.setValue(nomFic);
							}
						}
					}
				}]
			}, {
				xtype:'panel',
				layout:'column',
				autoHeight: true,
				border:false,
				items: [{
					xtype:'hidden',
					name:'COM_Path',
					ref: '../tfPath',
				},{
					xtype:'hidden',
					name:'COM_Repertoire',
					ref: '../tfRep',
					value: unObjet.COM_Path + unObjet.comRep
				},{
					xtype:'button',
					text:'Ouvrir le Document Lié',
					iconCls:'icon-selection-voir',
					handler : function(b, e) {
						ownerCtownerCt = this.ownerCt.ownerCt;
						source = href.substr(0, href.lastIndexOf('/'))
							+ '/' + ownerCtownerCt.tfPath.getValue() 
							+ ownerCtownerCt.tfRep.getValue() 
							+ '/' + ownerCtownerCt.tfDoc.getValue();
						if(	source.split('.').pop() == 'wav' ) {
						    win = new Ext.Window({
								title:source.split('/').pop(),
								layout:'fit',
								width:320,
								height:96,
								closeAction:'close',
								items: new Ext.Panel({
									html:'<audio controls="controls">'
										+ '<source src="' + source + '" type="audio/wav" />'
										+ 'Your browser does not support the audio element.'
										+ '</audio>'
								})
							});
							win.show();

						} else {
							// Affichage du document joint
							if(unId>0) {
								window.open( "php/download.php?visu=1&idDoc=" + unId, 'windoc' );
							} else {
								window.open( source, 'windoc2' );
							}
						}
					}
				},{
					xtype:'button',
					text:'Télécharger le Document Lié',
					iconCls:'icon-telecharge',
					handler : function(b, e) {
						window.open( "php/download.php?visu=0&idDoc=" + unId, 'windoc' );
					}
				}]
			}]
      });

	this.formulaire = new Ext.form.FormPanel({
 		url:'php/formDocGed.php',
		fileUpload: true,
		layout: 'border',
        bodyStyle:'padding:5px',
        defaults:{bodyStyle:'padding:5px'},
        trackResetOnLoad: true,
		items:[formChamps, tabPanelDescript],
		tbar : [],
		bbar : [{
			xtype: 'tbtext',
			text: unId > 0 ? (href.split('?'))[0] + "?tb=ged&id=" + unId : ""
		}]

	});
	
	var formulaire = this.formulaire;
		
	// Gere l'Action séléctionnée dans le panneau de recherche
	if( unObjet.idCom > 0 ) {
		comboAction.setValue(unObjet.idCom);
		comboMotClef.store.load( { params : { idCom : unObjet.idCom } } );
		Ext.Ajax.request({
			url:'./php/selectList.php',
			params : { nomListe : 'repertoire', idCom : unObjet.idCom },
			success:function(response){
				obj = Ext.util.JSON.decode(response.responseText);
				comboAction.ownerCt.tfRep.setValue(obj.results[0].COM_Repertoire)
				comboAction.ownerCt.tfPath.setValue(obj.results[0].COM_Path);
			},
			failure: function(response, action){
				Ext.Msg.alert('Status', 'Erreur d\'authentification !');
			}
		});
	}
	
	if( unId > 0 ) {
		this.formulaire.load({
			waitMsg:'Lecture ...',
			params:{ cmd:'load', identifiant: unId },
			success : function ( f, a ) {
				var tb = formulaire.getTopToolbar();

				obj = Ext.util.JSON.decode(a.response.responseText);
				comboMotClef.store.load( { params : { idCom : comboAction.getValue() } } );
				
				if(obj.data.Visibilite != null){
					tb.add({ xtype: 'tbtext', text: 'visible :'}, ' ',
					{
						xtype: 'checkbox',
						fieldLabel: 'Visu',
						checked:(obj.data.Visibilite == 1),
						handler  : function(cb, b) {
						
							Ext.Ajax.request({
								url:'./php/formDocGed.php',
								params : {cmd:"visibilite", ident: unId, visibilite: b},
								success:function(response){
									obj = Ext.util.JSON.decode(response.responseText); 
								},
								failure: function(response, action){
									Ext.Msg.alert('Status', 'Erreur d\'authentification !');
								}
							});
						}
					}, ' ', {
						xtype: 'button',
						iconCls:'icon-trash',
						handler: function(){
							Ext.Ajax.request({
								url:'php/formDocGed.php',
								params : {cmd:"trash", ident: unId},
								success:function(response){
									formulaire.ownerCt.close(); // Accès à win
								},
								failure: function(response, action){
									Ext.Msg.alert('Status', 'Erreur d\'authentification !');
								}
							});
						}
					});
					formulaire.doLayout();
					tabPanelDescript.remove( tabPanelDescript.getComponent( 0 ) );
					tabPanelDescript.insert( 0, panelFic( champNomFic.getValue(), comboAction.getValue(), unId ) );
					tabPanelDescript.setActiveTab( tabPanelDescript.getComponent( 0 ).disabled ? 1 : 0 );
				}
			}
		});
		gridPanelChrono.store.load();
	} else {
		lib = this.formulaire.find( "name", "DOC_Libelle" )[0];
		if( unObjet.ficDoc.length ) {
			lib.setValue(unObjet.ficDoc);
		}
		lib.focus();
	};
	
	// Instanciation de la Window
	this.win = new Ext.Window({
		title : '<img alt="objet" src="./img/brick.png"> ID : ' + unObjet.id,
		layout: 'fit',
		width: 740,
		height: 400,
		closeAction:'hide',
		plain: true,
		collapsible: true,
		maximizable: true,
		items: this.formulaire,
		buttons: [{
			text:'Dupliquer',
			disabled:(unId == 0),
			handler: function() {
				formulaire.getComponent(0).getComponent('ident').setValue(0); // remet à Zéro l'identifiant
				this.ownerCt.ownerCt.setTitle('Nouvel enregistrement');; // Accès à win
			}
		},{
			text:'Enregistrer',
			iconCls:'icon-database-save',
			handler: function() {
				idAction = comboAction.getValue();
				if( idAction > 0 ) {
					formulaire.getForm().submit({
						params:{ cmd:'save' , "idCOM" : idAction },
						waitMsg:'Enregistrement...',
						success:function() {
							formulaire.ownerCt.close(); // Accès à win
						},
						failure:function(form, action){ 
							obj = Ext.util.JSON.decode(action.response.responseText); 
							Ext.Msg.alert('Echec !', obj.errors); 
						}
					});
				} else {
					alert("Sélectionner une Action");
				}
			}
		},{
			text: 'Annuler',
			handler: function(){
				formulaire.getForm().reset();
			}
		},{
			text: 'Fermer',
			handler: function(){
				this.ownerCt.ownerCt.close(); // Accès à win
			}
		}]
	});

	this.win.show();

	return this ;
}