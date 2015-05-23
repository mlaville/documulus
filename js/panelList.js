/**
 * panelList.js
 * 
 * @auteur     marc laville
 * @Copyleft 2012
 * @date       25/06/201
 * @version    0.1
 * @revision   $0$
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

function boutonSelection(functionSelect) {
	return new Ext.Button({
		text: 'Lancer la Recherche',
		handler: functionSelect
	});
}

/* Calcul de la toolBar des panneau de recherche */
function tbSelection(functionSelect) {
	return new Ext.Toolbar({
//		width: 600,
		items: [{
				// xtype: 'button', // default for Toolbars, same as 'tbbutton'
			iconCls: 'icon-loupe',
			text: 'Lancer la Recherche',
			handler: functionSelect
	   }]
	});
}