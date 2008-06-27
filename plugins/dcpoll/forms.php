<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'DC Poll', a plugin for Dotclear 2                 *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'DC Poll' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$poll_forms = array();

$poll_forms['list_actions'] = array(
	__('Delete')=>'delete',
	__('Enable')=>'enable',
	__('Disable')=>'disable');
$poll_forms['list_ctrl_methods'] = array(
	__('Any')=>'any',
	__('IP Address')=>'ipaddr',
	__('Client unique identifier')=>'browseruid',
	__('Cookie')=>'cookie');

/* FORMULAIRE - Configuration
--------------------------------------------------- */

$poll_forms['config'] = '
<form action="'.$p_url.'" method="post">
<p><label>'.__('Vote control method:').' '.
form::combo('dcp_ctrl_method',$poll_forms['list_ctrl_methods']).'</label></p>
<p><input type="submit" name="poll_config" value="'.__('ok').'" />'.
$core->formNonce().'</p>
</form>
';

/* FORMULAIRE - Liste des sondages
--------------------------------------------------- */

$poll_forms['list'] = '';

/* FORMULAIRE - Ajout / modification d'un sondage
--------------------------------------------------- */

$poll_forms['edit'] = '

';

/* FORMULAIRE - Aide
--------------------------------------------------- */

$poll_forms['help'] = '';
?>
