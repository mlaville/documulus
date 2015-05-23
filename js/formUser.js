/**
 * formUser.js
 * 
 * @auteur     marc laville : www.polinux.net
 * @Copyleft 2010
 * @date       01/07/2010
 * @version    0.9.5
 * @revision   $8$
 *
 * @dateRevision 07/10/2010 Gestion des droits utilisateur
 * @dateRevision 20/11/2010 Droits superviseur + num adhérant
 * @dateRevision 28/11/2010 Sélection des docGed sur num adhérant
 * @dateRevision 30/11/2010 Gestion de la vilisibilité de l'onglet Droits - Masque les droits utilisateurs pour les non administrateur
 * @dateRevision 04/12/2010 Creation de l'Action associé à un adhérent
 * @dateRevision 17/12/2010 Bouton Creation d'un doc lié à l'Action associé à l'adhérent
 * @dateRevision 10/01/2011 Validation des droits utilisateur pour les Dossiers adhérents
 * @dateRevision 02/05/2011 Création à partir d'une demande de Login
 * @date revision  30/10/2012 -- Tri des colonnes droit
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

function formUser(unId, boolDmd) {
	idDmd = 0;
	if(boolDmd) {
		idDmd = unId;
		unId = 0;
	}
	/*
	 * On garde les références de certain objets de l'interface
	 */
	var comboZoneGeo = new Ext.form.ComboBox({
		fieldLabel:'Zone Géographique',
		name:'USR_ZoneGeo',
		store: new Ext.data.JsonStore({
			url: 'php/listCentre.php',
			root: 'centres',
			fields: ['nomCentre']
		}),
		displayField: 'nomCentre',
		valueField:'nomCentre',
		typeAhead: true,
		mode: 'remote',
		triggerAction: 'all',
		emptyText: 'Sélection ou saisie de la zone géographique',
		selectOnFocus: true,
		anchor: '95%' 
	});

	/*
	 * Bouton de sélection des objets relatifs à l'adhérent (sélection sur le champ adhérent
	  * Sera actif si numéro d'adhérent non vide
	 */
	var btnAccesObjAdherent = new Ext.Button({
		width: 48,
		tooltip: 'Sélectionner l\'Action associée à cet Adhérent',
		hidden: ( unId == 0 ),
		iconCls:'icon-folder-user',
		handler  : function(b, e) {
			comboCommission.setValue( this.ownerCt.dfLibAction.getValue() );
			tfRechercheDocGed.setValue('');
			jsonDSResultReqListDoc.reload( { "params" : { idCommission : this.ownerCt.hdIdAction.getValue() } } );
			Ext.getCmp('tabPanel').activate( 0 );
		}
	});

	/*
	 * Bouton de création de l'action liée à un user
	  * Sera actif si numéro d'adhérent non vide
	 */
	var btnCreeActAdherent = new Ext.Button({
		width: 48,
		tooltip: 'Créer l\'Action qui sera associée à cet Adhérent',
		hidden: ( unId == 0),
		iconCls:'icon-folder-add',
		handler  : function(b, e) {
			/*
			 * Envoie la requete de création de l'action
			 * Retour :
			 */
			Ext.Ajax.request({
				url:'php/formCommission.php',
				params : { cmd:"createFromUser", identUser: unId },
				success:function(response){
					obj = Ext.util.JSON.decode(response.responseText); 
					if(obj.success) {
						alert("L\'Action " + obj.data + " a été créée et associée à cet Utilisateur");
						btnAccesObjAdherent.ownerCt.dfLibAction.setValue(obj.data);
						btnCreeActAdherent.hide();
						btnAccesObjAdherent.show();
						btnAccesObjAdherent.ownerCt.doLayout();
					}
				},
				failure: function(response, action){
					Ext.Msg.alert('Status', 'Erreur d\'authentification !');
				}
			});
		}
	});
	
	var btnCreeObjAdherent = new Ext.Button({
		width: 48,
		tooltip: 'Créer un Objet Adhérent dans l\'Action associée à l\'adhérent',
		iconCls:'icon-formulaire-ajout',
		hidden: ( unId == 0),
		handler  : function(b, e) {
			new formDocGed( { id : 0, ficDoc : "", idCom : this.ownerCt.hdIdAction.getValue(), comRep : "" } );
		}
	});

	var fsAdherent = new Ext.form.FieldSet({
		collapsible: true,
		collapsed: (unId > 0),
		title: 'N° Adhérent',
		layout:'column',
		autoHeight: true,
		items: [{
			border:false,
			columnWidth: 1,
			items:[{
				xtype:'textfield',
				name:'USR_NumAdherent',
				fieldLabel:'N° Adhérent',
				ref: '../tfNumAdherent'
			},{
				xtype:'hidden', 
				name:'USR_IdCom',
				ref: '../hdIdAction'

			},{
				xtype:'displayfield',
				name:'COM_Libelle',
				submitValue : false,
				columnWidth: .5,
				ref: '../dfLibAction'
			}]
		}, {
				xtype:'button',
				iconCls:'icon-loupe',
				width: 48,
				tooltip: 'Sélectionner les Objets sur le Numéro d\'adhérent',
				hidden: ( unId == 0),
				handler  : function(b, e) {
					strRecherche = this.ownerCt.tfNumAdherent.getValue();
					comboCommission.setValue('');
					tfRechercheDocGed.setValue(strRecherche);
					jsonDSResultReqListDoc.reload( { "params" : { tfRecherche : strRecherche } } );
					Ext.getCmp('tabPanel').activate( 0 );
				} 
			} , btnCreeActAdherent, btnAccesObjAdherent, btnCreeObjAdherent
		]
	});

	this.gridPanelDroit = new Ext.grid.GridPanel({
		title: "Droits Utilisateur",
		flex:1,
		autoScroll:true,
		bodyStyle:'padding:5px',
		store: new Ext.data.JsonStore({
			url: 'php/selectList.php',
			root: 'results',
			totalProperty: 'total',
			fields: [ 
				{name: 'IdCOM', type: 'int'},
				{name: 'COM_Libelle', type: 'string'},
				{name: 'droitLecture', type: 'string'},
				{name: 'droitModif', type: 'string'},
				{name: 'notification', type: 'string'},
				{name: 'superviseur', type: 'string'}
			]
		}),
		viewConfig: { forceFit: true },
		colModel: new Ext.grid.ColumnModel({
			defaults: {	sortable: true },
			columns: [
				{ 	header: "Ident.",
					xtype: 'numbercolumn',
					dataIndex: 'IdCOM',
					format : '0',
					align : 'right',
					width :48
				}, { header: "Libellé", dataIndex: 'COM_Libelle', width :180 },
					{ xtype: 'checkcolumn', header: 'Lecture', dataIndex: 'droitLecture', width: 72 },
					{ xtype: 'checkcolumn', header: 'Modif.', dataIndex: 'droitModif', width: 72 },
					{ xtype: 'checkcolumn', header: 'Notif.', dataIndex: 'notification', width: 72 },
					{ xtype: 'checkcolumn', header: 'Superviseur', dataIndex: 'superviseur', width: 72 } //			checkColumnSuperviseur
			]
		}),
 		tbar : [
		]
	});

	var myMask = new Ext.LoadMask(
		Ext.getBody(),
		{
			msg:"Chargement...",
			store: this.gridPanelDroit.store
		});

	var tabPanelUser = new Ext.TabPanel({
		 activeItem:0,
		 // this line is necessary for anchoring to work at
		 // lower level containers and for full height of tabs
		 anchor:'100% 100%',
		 // only fields from an active tab are submitted
		 // if the following line is not persent
		 deferredRender:false,
		 // tabs
		defaults:{
			 layout:'form',
			 labelWidth:96,
			 defaultType:'textfield',
			 bodyStyle:'padding:5px',
			 // as we use deferredRender:false we mustn't
			 // render tabs into display:none containers
			 hideMode:'offsets'
		 },
		items:[{
			 title:'Informations',
			 // fields
			defaults:{ anchor:'-12' },
			items:[{
				xtype:'hidden',
				name:'IdUSR',
				value: unId 
			}, {
				// column layout with 2 columns
				xtype:'panel',
				layout:'column',
				border:false,

				// defaults for columns
				defaults:{
					columnWidth:0.5,
					layout:'form',
					border:false,
					xtype:'panel',
					labelAlign:"right",
					labelWidth:60,
				},
				items:[{
					// left column
					// defaults for fields
					defaults:{anchor:'100%'},
					items:[{
						xtype:'textfield',
						name:'USR_Nom', fieldLabel:'Nom'
					}]
				},{
					// right column
					// defaults for fields
					 defaults:{anchor:'100%'},
					items:[{
						xtype:'textfield',
						name:'USR_Prenom', fieldLabel:'Prénom'
					}]
				}]
				}, fsAdherent, {
				// column layout with 2 columns
				xtype:'panel',
				layout:'column',
				border:false,
				// defaults for columns
				defaults:{
					layout:'form',
					border:false,
					xtype:'panel',
					labelAlign:"right",
					labelWidth:60,
					bodyStyle:'padding:0 0 0 18px'
				},
				items:[{
					// left column
					// defaults for fields
					defaults:{anchor:'100%'},
					columnWidth:0.9,
					defaultType:'textfield',
					items:[{
						name:'USR_Mail',
						fieldLabel:'Mail',
						vtype:'email',
						ref: '../tfMail'
					},{
						inputType: 'password',
						name:'USR_Pwd',
						fieldLabel:'Passe'
					}]
				}, {
					defaultType:'button',
					items:[{
						/* Affichage du bouton envoi mail */
						text:'Mail',
						width:64,
						iconCls: 'icon-email-edit',
						handler  : function(b, e) {
							location.href="mailto:" + this.ownerCt.ownerCt.tfMail.getValue();
						}
					}]
				}]
				}, { name:'USR_Tel', fieldLabel:'Téléphone'
				}, {
					name:'droitActivite',
					fieldLabel:'Visu Activités',
					xtype:'checkbox'
				}, comboZoneGeo, {
					name:'USR_Fonction', fieldLabel:'Fonction'
				}, {
					xtype:'textarea',
					fieldLabel: 'Commentaires',
					name: 'USR_Comment',
					anchor:'-12, -248'
			}]
		}]
	});

	href = window.location.href;

 	this.formulaire = new Ext.form.FormPanel({
		defaults:{ border:false },
 		url:'php/formUser.php',
        trackResetOnLoad: true,
		items:[tabPanelUser], 
 		bbar : [{
			xtype: 'tbtext',
			text: unId > 0 ? (href.split('?'))[0] + "?tb=usr&id=" + unId : ""
		}]

	});

	var gridPanelDroit = this.gridPanelDroit;
	var tbar = this.gridPanelDroit.getTopToolbar();
	
	var formulaire = this.formulaire;
	
	if( unId > 0 ) {
		this.formulaire.load({
			waitMsg:'Lecture ...',
			params:{ cmd:'load', ident:unId },
			success:function(form, a){
				obj = Ext.util.JSON.decode(a.response.responseText);
				if(!obj.data.USR_IdCom) {
					btnAccesObjAdherent.hide();
					btnCreeObjAdherent.hide();
					
					if(!obj.data.USR_NumAdherent.length) {
						btnCreeActAdherent.hide();
					}
				} else {
					btnCreeActAdherent.hide();
				}
				if(obj.data.USR_NumAdherent.length) {
					fsAdherent.expand( false );
				}
				tbar.add({ xtype: 'tbtext', text: 'Gestionnaire d\'Utilisateur :'}, ' ',
				{
					xtype: 'checkbox',
					fieldLabel: 'GestUser',
					checked:(obj.data.droit == 1),
					handler  : function(cb, b) {
						// Enregistre la visibilite
						Ext.Ajax.request({
							url:'php/formUser.php',
							params : {cmd:"droit", ident: unId, droitGestUser: b ? 1 : 0},
							success:function(response){
								gridPanelDroit.store.load({
									params: { nomListe : "droitsUtilisateur", IdURS: unId },
									callback:function(r, o, s){ }
								});

							},
							failure: function(response, action){
								Ext.Msg.alert('Status', 'Erreur d\'authentification !');
							}
						});
					}
				});
			},
		});
	}
	
	if( idDmd > 0 ) {
		Ext.Ajax.request({
			url:'php/selectDemande.php',
			params : { ident: idDmd },
			success:function(response){
				obj = Ext.util.JSON.decode(response.responseText);
				( this.formulaire.find( 'name', 'USR_Nom' ) )[0].setValue(obj.data.DMD_Nom);
				( this.formulaire.find( 'name', 'USR_Prenom' ) )[0].setValue(obj.data.DMD_Prenom);
				( this.formulaire.find( 'name', 'USR_Tel' ) )[0].setValue(obj.data.DMD_Tel);
				( this.formulaire.find( 'name', 'USR_Mail' ) )[0].setValue(obj.data.DMD_Mail);
				( this.formulaire.find( 'name', 'USR_Comment' ) )[0].setValue(obj.data.DMD_Comment);
			},
			failure: function(response, action){
				Ext.Msg.alert('Status', 'Erreur d\'authentification !');
			}
		});
			
	}
		
	this.gridPanelDroit.store.load({
		params: { nomListe : "droitsUtilisateur", IdURS: unId },
		callback:function(r, o, s){
			if(s) {
				tabPanelUser.add(gridPanelDroit);
			}
		}
	});

	this.win = new Ext.Window({
			title : '<img alt="user" src="./img/user_gray.png"> ID : ' + unId,
			layout:'fit',
			width:448,
			height:464,
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
					this.ownerCt.ownerCt.setTitle('Nouvel enregistrement');; // Accès à win
				}
			}, {
				text:'Enregistrer',
				iconCls:'icon-database-save',
				handler: function() {
					check=[];
					gridPanelDroit.store.each(function(r){
						ligne = r.data;
						if( ligne.droitLecture || ligne.droitModif || ligne.notification || ligne.superviseur ) {
							IdCOM = r.data.IdCOM;
							check.push( new Array( ligne.IdCOM, ( ligne.droitModif ? 3 : 1 ) + ( ligne.notification ? 4 : 0 ) + ( ligne.superviseur ? 8 : 0 ) ) );
						}
					});

					formulaire.getForm().submit({
						params:{ cmd:'save', droits:check.join("; ") },
						waitMsg:'Enregistrement...',
						success:function() {
							formulaire.ownerCt.close(); // Accès à win
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
