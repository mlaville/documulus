/**
 * panelLogin.js
 * 
 * @auteur     marc laville
 * @Copyleft 2010
 * @date       04/08/2010
 * @version    0.7
 * @revision   $2$
 *
 * @date   revision   marc laville  30/11/2011 Changement de Mot de Passe
 * @date_revision   marc laville 24/01/2012 : Personnalisation du texte du bouton de demande de login (msgDemande) + ajout rubrique GSM
 * @date_revision   marc laville 16/05/2012 : Affichage du nom de la société  dans le titre du login
 * @date_revision   marc laville 01/08/2012 : Détection du navigateur
 * @date_revision   marc laville 14/10/2012 : Gestion du Captcha
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

function panelDemandeLogin(msgDemande) {

    var formDemandeLogin = new Ext.FormPanel({
        labelWidth:120,
        url:'php/formDemande.php', 
        frame:true, 
        defaultType:'textfield',
		defaults: {anchor:'95%'},
		monitorValid:true,
//		height:300,
        items:[{
			name:'DMD_Nom', fieldLabel:'Nom', allowBlank:false
		}, {
			name:'DMD_Prenom', fieldLabel:'Prénom'
		}, {
			name:'DMD_Mail',
			fieldLabel:'Mail',
			vtype:'email',
			vtypeText:'email invalide'
		},{
			name:'DMD_Tel', fieldLabel:'Téléphone'
		}, {
			name:'DMD_GSM', fieldLabel:'Portable', allowBlank:false
		}, {
			xtype:'textarea',
			fieldLabel: 'Commentaires',
			name: 'DMD_Comment'
		}, {
			xtype:'panel',
			autoLoad: './php/captcha.php',
		}, {
			name:'captcha',
			fieldLabel:'Captcha',
			allowBlank:false,
			emptyText: 'Saisissez le Code (Captcha)'
		}],
		buttons:[{
	        text:'Enregistrer',
	        formBind:true,   //If validation fails disable the button
				handler: function() {
					formDemandeLogin.getForm().submit({
						waitMsg:'Enregistrement...',
						success:function() {
							Ext.Msg.alert('Status', 'Votre Demande a été Enregistrée.');
							formDemandeLogin.ownerCt.close(); // Accès à win
						},
						failure:function(form, action){ 
							obj = Ext.util.JSON.decode(action.response.responseText); 
							Ext.Msg.alert('Echec !', obj.error); 
						}
					});
				}
	    }]
	});
	
	return new Ext.Window({
			title : msgDemande,
			width:460,
			height:620,
			closeAction:'hide',
			plain: true,
			items: [{
				bodyCfg: { tag: 'center' },
				height: 260,
				autoLoad: './tpl/msgLogin.html'
			}, formDemandeLogin
			]
		}).show();
}

var cp = Ext.util.Cookies;

function PanelLogin(effacePass, msgDemande, unNom) {

	var pw = cp.get('loginPassword');
	var sauvPass = false;
	if(pw) {
		sauvPass = pw.length > 0;
	}
	
	var nav = '???';
	if( Ext.isIE ) {
		nav = "IE";
	}
	if( Ext.isGecko ) {
		nav = "Gecko";
	}
	if( Ext.isChrome ) {
		nav = "Chrome";
	}
	if( Ext.isOpera ) {
		nav = "Opera";
	}
	if( Ext.isSafari ) {
		nav = "Safari";
	}
		
	// Instanciation du Form Panel ;
	// config des options.
    var login = new Ext.FormPanel({
        labelWidth:96,
		labelAlign:'right',
        url:'php/login.php', 
        frame:true, 
        defaultType:'textfield',
		defaults: {anchor:'95%'},
		monitorValid:true,
	//  Attributs specifiques aux champs de saisie loginServeur/loginUsername/loginPassword. 
	// L'attribut "name" definit le nom des variables envoyées au serveur.
        items:[{ 
                fieldLabel:'Mail', 
                name:'loginUsername', 
				stateId:'loginUsername',
				value:cp.get('loginUsername'),
                allowBlank:false,
           },{ 
                fieldLabel:'Mot de Passe', 
                name:'loginPassword', 
                inputType:'password', 
				value:effacePass ? "" : cp.get('loginPassword'),
            },{
				fieldLabel:'Mémo',
				name:'sauvePasse',
				xtype:'checkbox',
				value:sauvPass,
				checked:sauvPass
		}],
 
	// All the magic happens after the user clicks the button     
        buttons:[{ 
                text:'Login',
                formBind: true,	 
				iconCls:'icon-database-connect',
                // Function that fires when user clicks the button 
                handler:function(){ 
                    login.getForm().submit({ 
                        method:'POST', 
                        waitTitle:'Connexion en cours', 
                        waitMsg:'Envoi des données au serveur...',
 
			// Fonctions qui propagent les réponses du serveur (success or failure). 
			// The one that executes is determined by the 
			// response that comes from login.asp as seen below. The server would 
			// actually respond with valid JSON, 
			// something like: response.write "{ success: true}" or 
			// response.write "{ success: false, errors: { reason: 'Login failed. Try again.' }}" 
			// depending on the logic contained within your server script.
			// If a success occurs, the user is notified with an alert messagebox, 
			// and when they click "OK", they are redirected to whatever page
			// you define as redirect. 
                        success:function(form, action){
                            obj = Ext.util.JSON.decode(action.response.responseText); 
                        	Ext.Msg.alert('Status', 'Connexion établie !', function(btn, text){
									var dateExpire = new Date();
									dateExpire.setTime(dateExpire.getTime()+(14*24*60*60*1000)); // Le cookies expire dans 10 jours
									cp.set('loginUsername', login.items.itemAt(0).getValue(), dateExpire);
									cp.set( 'loginPassword', (login.items.itemAt(2).getValue()==1) ? login.items.itemAt(1).getValue() : "", dateExpire );

									initApp(login.items.itemAt(0).getValue(), obj.societe);
									winLogin.hide();
								});
                        },
 
			// Failure function, see comment above re: success and failure. 
			// You can see here, if login fails, it throws a messagebox
			// at the user telling him / her as much.  
                        failure:function(form, action){ 
                            if(action.failureType == 'server'){ 
                                obj = Ext.util.JSON.decode(action.response.responseText); 
                                Ext.Msg.alert('Echec de la connexion !', obj.errors.reason); 
                            }else{ 
                                Ext.Msg.alert('Warning!', 'Authentication server is unreachable : ' + action.response.responseText); 
                            } 
                            login.getForm().reset(); 
                        } 
                    }); 
                } 
            }],
		tbar : ['->', {
			text: msgDemande,
            handler:function(){
				panelDemandeLogin(msgDemande);
			}
		}]
    });
	
 
	// Creation de la fenetre qui va contenir notre formulaire de login. 
	// L'object login est passé dans la liste d'items.       
    winLogin = new Ext.Window({
        title:'documulus - Panneau d\'authenfication<br>' + unNom, 
        layout:'fit',
        width:380,
        height:240,
        closable: false,
        resizable: false,
        plain: true,
        border: true,
        items: [login], // La fenêtre va contenir le formulaire de login
		bbar : [{xtype: 'tbtext', text: nav}]
		});
	winLogin.show();
	login.items.itemAt(0).focus(true, 500); // sans delais (0.5 s) le focus disparait !
	
	if(location.hash == "#resa") {
		panelDemandeLogin(msgDemande);
	};
	
	this.paramConnect = function() {
		return { loginUsername: login.items.itemAt(0).getValue(), loginPassword: login.items.itemAt(1).getValue() };
	}
}

// Formulaire de changement de Mot de Passe
function formPass() {

	formPass = new Ext.FormPanel({
		title: 'Mot de Passe',
		width:312,
		labelWidth:140,
		url:'./php/changePass.php', 
		frame:true, 
        defaultType:'textfield',
		defaults: {
			anchor:'98%',
			inputType:'password',
			allowBlank:false 
		},
		items: [{
			fieldLabel:'Ancien Mot de Passe', 
			name:'loginPassword' 
		}, { 
			fieldLabel:'Nouveau Mot de Passe', 
			name:'nouveauPassword',
		},{ 
			fieldLabel:'Confirmation', 
			name:'confirmPassword'
		}],
		buttons:[{
			text:'Valider',
			formBind: true,	 
			iconCls:'icon-database-save',
			// Function that fires when user clicks the button 
			handler:function(){
				// tester l'égalité des 2 pass saisie
				formPass.getForm().submit({
					method:'POST', 
					waitTitle:'Changement en cours', 
					waitMsg:'Envoi des données au serveur...',

					success:function(form, action){
						obj = Ext.util.JSON.decode(action.response.responseText);
						// tester le retour
						if(obj.succes) {
							Ext.Msg.alert('Status', 'Votre Mot de Passe a été modifié avec succès');
						} else {
							Ext.Msg.alert('Status', obj.succes);
						}
					},
					failure:function(form, action){
						if(action.failureType == 'server'){ 
							obj = Ext.util.JSON.decode(action.response.responseText); 
							Ext.Msg.alert('Echec de la connexion !', obj.errors.reason); 
						}else{ 
							Ext.Msg.alert('Warning!', "Serveur d'Authentication introuvable : " + action.response.responseText); 
						} 
						login.formPass().reset(); 
					}
				})
			}
		}]
	});
	
	return formPass;
}

function panelPass() {

    winPass = new Ext.Window({
        title:'Modification de mot de passe', 
        layout:'fit',
        width:360,
        height:212,
        closable: true,
        resizable: false,
        plain: true,
        border: true,
        items: [  ] // La fenêtre va contenir le formulaire de mot de passe
	});
	winPass.show();
}