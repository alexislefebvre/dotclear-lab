<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Remote LaTeX', a plugin for Dotclear              *
 *                                                             *
 *  Copyright (c) 2007-2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Remote LaTeX' (see COPYING.txt);       *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$forms = array();

$forms['admin_cfg'] = '
<form action="'.$p_url.'" method="post">
<fieldset><legend>'.__('Server settings').'</legend>
	<p><label class="required" title="'.__('Required field').'">'.
		__('LaTeX server location:').' '.
		form::field('latex_server',30,255,html::escapeHTML($latex_server)).
	'</label></p>
	<p>'.__('Note : this location should give a valid GIF or PNG image.').' '.
		__('Field "%s" will be replaced by LaTeX code to insert.').'</p>
	<p><input type="submit" name="act_config" value="'.__('save').'" /> '.
		'<input type="submit" name="act_test" value="'.__('test it').'" /></p>'.
	'<p><input type="submit" name="act_erase" value="'.__('delete cached images').'" />'.
		$core->formNonce().'</p>
</fieldset>
</form>';
?>