/* -*- tab-width: 4; indent-tabs-mode: t; c-basic-offset: 4 -*- */
/***************************************************************\
 *  This is 'Arlequin', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007,2015                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along 'Arlequin' (see COPYING.txt);                *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

var arlequin = {
	msg : {
		predefined_models : 'Generic models',
		select_model : 'Select a generic model:',
		user_defined : 'User defined'
	},
	
	models : Array(),
	
	addModel : function(model_name, s_html, e_html, a_html) {
		model = new Array(model_name,s_html,e_html,a_html);
		arlequin.models.push(model);
	},
	
	addDefault : function() {
		arlequin.addModel(arlequin.msg.user_defined,
			$("#s_html").val(),
			$("#e_html").val(),
			$("#a_html").val());
	},
	
	drawInterface : function() {
		if (!arlequin.models.length) {
			return;
		}
		
		res = '';
		res += '<p>'+arlequin.msg.select_model+' ';
		res += '<select id="mt_model">';
		for (i in arlequin.models) {
			res += '<option value="'+i+'">'
				+ arlequin.models[i][0]+'</option>';
		}
		res += '</select>';
		res += '</p>';
		
		return res;
	},
	
	selectModel : function(id) {
		if (!arlequin.models[id]) { return; }
		
		$("#s_html").val(arlequin.models[id][1]);
		$("#e_html").val(arlequin.models[id][2]);
		$("#a_html").val(arlequin.models[id][3]);
	}
};


$(function() {
	if (!document.getElementById || !document.getElementById('models')) { return; }
	
	var c = $('#models');
	c.html('<p><a id="model-control" class="form-control" style="display:inline;" href="#">'+
		arlequin.msg.predefined_models+'</a></p>');
	
	$('#model-control').click(function() {
		c.html(arlequin.drawInterface());
		
		$('#mt_model').change(function() {
			arlequin.selectModel(this.value);
		});
		
		return false;
	});
});