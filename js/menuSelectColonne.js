/**
 * menuSelectColonne.js
 * 
 * @auteur     marc laville
 * @Copyleft 2011
 * @date       24/04/2011
 * @version    0.1
 * @revision   $0$
 *
 *   Contruit la checkboxlist de Sélection des colonnes à l'export
 *
 * Licensed under the GPL license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

function checkBoxGroupColonne(typeRecord) {
	var tabCheckbox = new Array();

	Ext.Ajax.request({
		url:'./php/selectRubrique.php',
		params : { aliasTable: typeRecord },
		success:function(response){
			obj = Ext.util.JSON.decode(response.responseText); 
			if(obj.success) {
				Ext.each( obj.results, function(item) {
					tabCheckbox.push( {boxLabel: item.Field, name: item.Field, checked: true} );
				});
				tabCheckbox.push( {boxLabel: 'URI', name: 'URI', checked: true} );
			}
		}
	});
	
	return new Ext.form.CheckboxGroup({
		itemCls: 'x-check-group-alt',	
		columns: 1,	// Put all controls in a single column with width 100%
		items: 	tabCheckbox,
		stateful : 	true,
		stateId: 'COL-' + typeRecord
	});
}