/**
 * formCommission.js
 * 
 * @auteur     marc laville
 * @Copyleft 2010-2011-2012
 * @date       11/08/2010
 * @version    0.9.7
 * @revision   $10$
 *
 * @date_revision   24/10/2010 Gestion du champ commentaire et duplication
 * @dateRevision 20/11/2010 Droits superviseur
 * @dateRevision 30/11/2010 Sélection des docs liés à l'action
 * @dateRevision 12/05/2011 Accès à la page de téléchargement
 * @date revision   02/07/2011 -- Gestion de la coche "photo"
 * @date revision   29/07/2011 -- Ne poste pas de parametre "droits" si l'utilisateur ne dispose pas des droits.
 * @date revision   10/08/2011 -- Sélection de l'action par l'identifiant (IdCom).
 * @date revision   30/12/2011 -- Correction bug d'affichage du panel chrono quand on sélectionne les objets de l'action
 * @date revision   23/10/2012 -- Affichage de la colonne n° adhérent dans la liste de droits
 * @date revision   30/10/2012 -- Affichage espace occupé 
 * @date revision   30/10/2012 -- Tri des colonnes droit
 * @date revision   21/05/2015 Gestion des upload
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */
 
function formCommission(unId) {

	this.gridPanelDroit = new Ext.grid.GridPanel({
		title: "Droits Utilisateurs",
		flex:1,
		autoScroll:true,
		bodyStyle:'padding:5px',
		store: new Ext.data.JsonStore({
			url: 'php/selectList.php',
			root: 'results',
			totalProperty: 'total',
			fields: [ 
				{name: 'IdUSR', type: 'int'},
				{name: 'USR_Nom', type: 'string'},
				{name: 'USR_Prenom', type: 'string'},
				{name: 'USR_Mail', type: 'string'},
				{name: 'USR_NumAdherent', type: 'string'},
				{name: 'droitLecture', type: 'string'},
				{name: 'droitModif', type: 'string'},
				{name: 'notification', type: 'string'},
				{name: 'superviseur', type: 'string'}
			]
		}),
		viewConfig: { forceFit: true },
		defaults: {	sortable: true },
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
				{ header: "Nom", dataIndex: 'USR_Nom', width :120 },
				{ header: "Prénom", sortable: true, dataIndex: 'USR_Prenom', width :120 },
				{ header: "Mail", sortable: true, dataIndex: 'USR_Mail', width :180 },
				{ header: "N° Adhèrent", sortable: true, dataIndex: 'USR_NumAdherent', width :120 },
				{ xtype: 'checkcolumn', header: 'Lecture', sortable: true, dataIndex: 'droitLecture', width: 72 },
				{ xtype: 'checkcolumn', header: 'Modif.', sortable: true, dataIndex: 'droitModif', width: 72 },
				{ xtype: 'checkcolumn', header: 'Notif.', dataIndex: 'notification', width: 72 },
				{ xtype: 'checkcolumn', header: 'Superviseur', dataIndex: 'superviseur', width: 72 } //			checkColumnSuperviseur
			]
		}),
	});

	var myMask = new Ext.LoadMask(
		Ext.getBody(),
		{
			msg:"Chargement...",
			store: this.gridPanelDroit.store
		}),
		champLibelle = new Ext.form.TextField({
			name: 'COM_Libelle', 
			fieldLabel: 'Libellé',
		}),
		/*
		 * Commende l'affiche de la fenetre upload  par lot
		 */
		showUpload = function() {
			return appDocumulus.showUpload( { idCommission: unId, libelle: champLibelle.value, } );
		},
		tabPanelCommission = new Ext.TabPanel({
			 activeItem:0,
			 // this line is necessary for anchoring to work at
			 // lower level containers and for full height of tabs
			 anchor:'100% 100%',
			 // seul les champ de l'onglet actif sont soumis
			 // si la ligne suivante n'est pas presente
			 deferredRender:false,
			 // tabs
			defaults:{
				layout:'form',
				labelWidth:96,
				labelAlign : 'right',
				defaultType: 'textfield',
				bodyStyle:'padding:5px',
				// as we use deferredRender:false we mustn't
				// render tabs into display:none containers
				hideMode:'offsets'
			 },
			items: [{
				 title:'Informations',
				 // fields
				defaults:{ anchor:'-12' },
				items:[{
					xtype:'hidden',
					name:'IdCOM',
					value: unId,
					ref: '../../hfIdent'
				}, champLibelle, {
					name:'COM_Repertoire', fieldLabel:'Répertoire',
				}, {
					xtype: 'displayfield',
					name: 'taille',
					style: {
						fontSize: '11px',
						marginTop: '-4px'
					}
				}, {
					xtype: 'checkbox',
					name:'photo',
					fieldLabel:'photo'
				}, {
					xtype:'textarea',
					fieldLabel: 'Commentaires',
					name: 'COM_Comment',
					anchor: '-12 -100'  // ou '95% -96'
				}]
		},
		this.gridPanelDroit
		]
	});

	
	this.formulaire = new Ext.form.FormPanel({
		defaults: { border:false },
 		url: 'php/formCommission.php',
        trackResetOnLoad: true,
		items: tabPanelCommission, 
		tbar: [{
			xtype: 'button',
			text: "Sélectionner les Objets",
			// Sélection de l'action par l'identifiant
			handler : function(){
				comboCommission.setValue( unId );
				tfRechercheDocGed.setValue('');
				jsonDSResultReqListDoc.reload( { "params" : { idCommission : this.ownerCt.ownerCt.hfIdent.getValue() } } );
				Ext.getCmp('tabPanel').activate( 1 );
			}
		}, '->', {
			xtype: 'button',
			text: 'Chargement des fichiers par lots',
			hidden: ( unId == 0 ),
			handler  : showUpload
		}],
		bbar: [{
			xtype: 'tbtext',
			text: unId > 0 ? ( window.location.href.split('?') )[0] + "?tb=com&id=" + unId : ""
		}]

	});

	if( unId > 0 ) {
		this.formulaire.load({
			waitMsg:'Lecture ...',
			params:{ cmd:'load', Ident:unId},
			success : function(form, action) {
				var obj = Ext.util.JSON.decode(action.response.responseText); 

				form.findField('taille').setValue( obj.data.taille.toByteSizeString() );
			}
		});
		
		this.gridPanelDroit.store.load( { params: { nomListe: "droits", IdCOM: unId } } );
	}
	
		
	var formulaire = this.formulaire,
		gridPanelDroit = this.gridPanelDroit;

	// Instanciation de la Window
	this.win = new Ext.Window({
			title : '<img alt="user" src="./img/folder.png"> ID : ' + unId,
			layout:'fit',
			width:520,
			height:360,
			closeAction:'hide',
			plain: true,
			collapsible: true,
			maximizable: true,
			items: this.formulaire,
			buttons: [{
				text:'Dupliquer',
				disabled:(unId == 0),
				handler: function() {
					formulaire.getComponent(0).getComponent(0).getComponent(0).setValue(0); // remet à Zéro l'identifiant
					this.ownerCt.ownerCt.setTitle('Nouvel enregistrement'); // Accès à win
				}
			}, {
				text:'Enregistrer',
				iconCls:'icon-database-save',
				handler: function() {
					params = { cmd:'save' };
					// Enregistrement des droits utilisateur
					if(gridPanelDroit.store.getCount() > 0) {
						check=[];
						gridPanelDroit.store.each(function(r){
							var ligne = r.data;
							if( ligne.droitLecture || ligne.droitModif || ligne.notification || ligne.superviseur ) {
								check.push( new Array(
									ligne.IdUSR,
									( ligne.droitModif ? 3 : 1 ) + ( ligne.notification ? 4 : 0 ) + ( ligne.superviseur ? 8 : 0 ) )
								);
							}
						});
						params.droits = check.join("; ");
					}
					formulaire.getForm().submit({
						params:params,
						waitMsg:'Enregistrement...',
						success:function() {
							formulaire.ownerCt.close(); // Accès à win
							storeAction.load();
						},
						failure:function(form, action){
							obj = Ext.util.JSON.decode(action.response.responseText); 
							Ext.Msg.alert('Echec !', obj.errors); 
						}
					});
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
