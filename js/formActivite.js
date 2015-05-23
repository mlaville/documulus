/**
 * formActivite.js
 * 
 * @auteur     marc laville : www.polinux.net
 * @Copyleft 2010
 * @date    création   02/07/2010
 * @version    0.7.2
 * @revision   $5$
 * @date revision   03/09/2010 : ajout zone url ; acces direct à la fiche user par le bouton géolocalisation
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

function formActivite(unId) {

	var comboZoneGeo = new Ext.form.ComboBox({
		fieldLabel:'Zone Géographique',
		name:'ACT_nomCentre',
		store: new Ext.data.JsonStore({
			url: 'php/listCentre.php',
			root: 'centres',
			fields: ['nomCentre']
		}),
		displayField: 'nomCentre',
		valueField:'nomCentre',
		mode: 'remote',
		triggerAction: 'all',
		emptyText: 'Sélection ou Saisie de la Zone Géographique',
		selectOnFocus: true,
		anchor: '95%' 
	});

	href = window.location.href;
						
 	this.formulaire = new Ext.form.FormPanel({
        bodyStyle:'padding:5px',
        width: 700,
 		url:'php/formActivite.php',
        trackResetOnLoad: true,
		fileUpload: true,
		layout:'vbox',
		layoutConfig: {
			align : 'stretch',
			pack  : 'start',
		},
		items: [{
			layout:'form',
            border:false,
            items:[{
				itemId:'ident',
				xtype:'hidden',
				name:'IdActivite',
				value: unId 
			}, comboZoneGeo, {
				xtype:'textfield',
				name:'ACT_Societe',
				fieldLabel:'Libellé',
				anchor: '95%' 
			},{
				xtype:'textfield',
				name:'ACT_Type',
				fieldLabel:'Typologie',
				anchor: '95%' 
			}]
		},{
            layout:'column',
            border:false,
            items:[{
                columnWidth:.5,
                layout: 'form',
                border:false,
                defaultType: 'textfield',
 				defaults:{anchor:'95%'},
               items: [{
                    fieldLabel: 'Département',
                    name: 'ACT_NomDept',
                }, {
                    fieldLabel: 'Commune',
                    name: 'ACT_Commune',
                }]
            },{
                columnWidth:.5,
                layout: 'form',
                border:false,
                defaultType: 'textfield',
				defaults:{anchor:'95%'},
                items: [{
                    fieldLabel: 'Nom du Correspondant',
                    name: 'ACT_NomCorresp',
                },{
                    fieldLabel: 'N° Tél 001',
                    name: 'ACT_NumTel',
                }]
            }]
		},{
			layout:'hbox',
			height:24,
            border:false,
			pack:'center',
			defaultType: 'button',
            defaults:{width:64},
			items: [
				{
					text:'Mail',
					iconCls: 'icon-email_edit',
					handler  : function(b, e) {
						location.href="mailto:" + this.ownerCt.ownerCt.tfMail.getValue();
					}
				},{
					xtype:'spacer', flex:1
				},{
					text:'Web',
					handler  : function(b, e) {
						url = this.ownerCt.ownerCt.tfWeb.getValue();
						if( url.indexOf( "http://" ) != 0 ) {
							url = "http://" + url;
						}
						window.open( url, 'winDoc' );
					}
				},{
					xtype:'spacer', flex:1
				}, {
					text:'Documentation',
					handler  : function(b, e) {
						window.open(href.substr(0, href.lastIndexOf('/')) + '/docs/' + this.ownerCt.ownerCt.tfDoc.getValue(), 'winDoc');
					}
				},{
					xtype:'spacer', flex:1
				},{
					text:'Géolocalisation',
					handler  : function(b, e) {
						uri = this.ownerCt.ownerCt.tfGeo.getValue();
						indexComplUrl = uri.indexOf( "?tb=usr&id=" );
						if( indexComplUrl > 0 ) {
							formUser(uri.substr( indexComplUrl + "?tb=usr&id=".length ));
						} else {
							if( uri.indexOf( "http://" ) != 0 ) {
								uri = "http://" + uri;
							}
							window.open( uri, 'winDoc' );
						}
					}
				}]
		},{
            xtype:'tabpanel',
			itemId:'actOnglet',
            plain:true,
            activeTab: 1,
 			flex:1,
            /*
              By turning off deferred rendering we are guaranteeing that the
              form fields within tabs that are not activated will still be rendered.
              This is often important when creating multi-tabbed forms.
            */
            deferredRender: false,
            defaults:{bodyStyle:'padding:5px'},
            items:[{
                title:'Coordonnées',
                layout:'form',
  				defaults:{anchor:'95%'},
				defaultType: 'textfield',

                items: [{
                    fieldLabel: 'Code Postal',
                    name: 'ACT_CodePostal',
 //                   allowBlank:false,
                },{
                    fieldLabel: 'N° Tél 002',
                    name: 'ACT_NumTel2'
                },{
                    fieldLabel: 'n° Fax',
                    name: 'ACT_NumFax',
                }, {
                    fieldLabel: 'Email',
                    name: 'ACT_Courriel',
                    vtype:'email',
					ref: '../../tfMail'
                }, {
                    fieldLabel: 'Web',
                    name: 'ACT_Url',
					ref: '../../tfWeb'
                }, {
                    fieldLabel: 'Documentation',
                    name: 'ACT_DocPapier',
					readOnly : true,
					ref: '../../tfDoc',
               }, {
					xtype: 'fileuploadfield',
                    name: 'ficDocPapier',
					buttonText: 'Sélection',
					buttonOnly: true,
					listeners: {
						'fileselected': function(fb, v){
							this.ownerCt.ownerCt.ownerCt.tfDoc.setValue(v);
						}
					}
                },
/*				{
				xtype:'panel',
				layout:'column',
//				frame: true,
				autoHeight: true,
				border:false,
				items: [{
					layout:'form',
					border:false,
					columnWidth:1.0,
					bodyStyle: 'padding: 0 10px 0 0;',
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Documentation',
						name: 'DOC_Fic',
						readOnly : true,
						ref: '../../tfDoc',
						anchor: '90%'
					}]
				},{
					xtype: 'fileuploadfield',
					buttonOnly: true,
					name: 'ficDocPapier',
					buttonText: '', //'Sélection ...',
					buttonCfg: {
						iconCls: 'icon-explore',
						iconAlign: 'right'
					},
					listeners: {
						'fileselected': function(fb, v){
							this.ownerCt.ownerCt.tfDoc.setValue(v);
						}
					}
				}]
			},*/
			{
 					xtype:'textarea',
					fieldLabel: 'Adresse',
                    name: 'ACT_Adresse',
                }]
            },{
                title:'Descriptif Court',
				itemId:'actDescriptCourt',
				items: [
				{
					layout:'form',
					defaults: {anchor:'95%'},
					labelWidth:180,
					border:false,
					defaultType: 'textfield',
					items: [{
						fieldLabel: 'Type public et Tranches d\'âge',
						name: 'ACT_TypePublic'
					},{
						fieldLabel: 'Agenda et Tarifs indicatifs',
						name: 'ACT_PeriodeOuverture'
					}]
				}, {
					layout:'column',
					itemId:'actDescriptCourtCol',
					border:false,
					items: [{
						columnWidth: .5,
						border:false,
						items: [{
							layout:'form',
							defaults: {anchor:'95%'},
							border:false,
							defaultType: 'textfield',
							items: [{
								fieldLabel: 'Pays',
								name: 'ACT_Pays'
							},{
								fieldLabel: 'Bassin',
								name: 'ACT_Bassin'
							},{
								fieldLabel: 'Lieu dit',
								name: 'ACT_LieuDit'
							}]
						}]
					},{
						columnWidth: .5,
						border:false,
						itemId:'actCol2',
						items: [{
							layout:'form',
							itemId:'actCol2',
							defaults: {anchor:'95%'},
							border:false,
							defaultType: 'textfield',
							items: [{
								fieldLabel: 'GPS',
								name: 'ACT_CoordGeo',
								ref: '../../../../../tfGeo'
							},{
								fieldLabel: 'Distance',
								name: 'ACT_DistCentre',
							},{
								fieldLabel: 'Handicap',
								name: 'ACT_Handicap'
							},{
								fieldLabel: 'Langues parlées',
								name: 'ACT_Langue'
							}]
						}]
					}]
				},{
					layout:'form',
					border:false,
					items: [{
						xtype:'textarea',
						fieldLabel: 'Descriptif Court',
						name: 'ACT_DescriptCourt',
						anchor:'95%'
					}]
				}]
			},/*{
                cls:'x-plain',
                title:'Descriptif Long',
                layout:'fit',
                items: {
                    xtype:'htmleditor',
                    id:'DescriptifLong',
                    fieldLabel:'Descriptif Long'
                }
            },*/{
                title:'Descriptif Long',
                layout:'fit',
                items: {
					layout:'form',
					border:false,
					labelAlign: 'top',
					items: [{
						xtype:'textarea',
						fieldLabel: 'Descriptif Long',
						name: 'ACT_DescriptLong',
						anchor:'95% 95%'
					}]
				}
            },{
                title:'info complémentaires',
                layout:'fit',
                items: {
					layout:'form',
					border:false,
					labelAlign: 'top',
					items: [{
						xtype:'textarea',
						fieldLabel: 'Informations Complémentaires',
						name: 'ACT_InfoComplem',
						anchor:'95% 95%'
					}]
				}
            }]
        }],
		bbar : [{
			xtype: 'tbtext',
			text: unId > 0 ? (href.split('?'))[0] + "?tb=act&id=" + unId : ""
		}]

	});
	
	reqActivite = 'SELECT'
		+ ' IdActivite, ACT_couleur, ACT_nomCentre, ACT_Societe, ACT_Type, ACT_DescriptCourt, ACT_DescriptLong,'
		+ ' ACT_LieuDit, ACT_Commune, ACT_CodePostal, ACT_NomDept, ACT_Bassin, ACT_Pays, ACT_DistCentre, ACT_CoordGeo,'
		+ ' ACT_TypePublic, ACT_Handicap, ACT_PeriodeOuverture, ACT_InfoComplem, ACT_Adresse, ACT_NomCorresp, ACT_NumTel, ACT_NumTel2, ACT_NumFax, ACT_Courriel,'
		+ ' ACT_Url, ACT_DocPapier, ACT_Langue'
		+ ' FROM activite'
		+ ' WHERE IdActivite = ' + unId;

	if( unId > 0 ) {
		this.formulaire.load({
			waitMsg:'Lecture ...',
			params:{ cmd:'load', vbSelect:reqActivite }
		});
	}
	
	var formulaire = this.formulaire;

	this.win = new Ext.Window({
			title : '<img alt="activité" src="./img/sport_soccer.png"> ID : ' + unId,
			layout:'fit',
			width:520,
			height:560,
			closeAction:'hide',
			plain: true,
			collapsible: true,
			maximizable: true,

			items: this.formulaire,
			buttons: [{
				text:'Dupliquer',
				iconCls:'icon-disk',
				disabled:(unId == 0),
				handler: function() {
					formulaire.getComponent(0).getComponent(0).setValue(0); // remet à Zéro l'identifiant
//					this.win.setTitle('Nouvel enregistrement');
					this.ownerCt.ownerCt.setTitle('Nouvel enregistrement');; // Accès à win
				}
			},{
				text:'Enregistrer',
				iconCls:'icon-database-save',
//                    disabled:true
				handler: function() {
					formulaire.getForm().submit({
						params:{cmd:'save'},
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
