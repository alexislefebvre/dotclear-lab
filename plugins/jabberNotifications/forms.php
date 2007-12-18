<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */

/***************************************************************\
 *  This is Jabber Notifications, a plugin for Dotclear 2      *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk, Olivier Tétard and contributors.       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along Jabber Notifications (see COPYING.txt);      *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$jn_mode = empty($jn_mode) ? 'config' : $jn_mode;

$jn_forms = array();

$jn_forms['list_notify'] = array(
	__('never')=>'never',
	__('for my entries')=>'entries',
	__('for my default blog')=>'blog',
	__('for all my blogs')=>'blogs');

if ($core->auth->isSuperAdmin()) {
	$jn_forms['list_notify'][__('all blogs')] = 'all';
}

/* FORMULAIRE - Configuration
--------------------------------------------------- */

if ($jn_mode == 'config'):
$jn_forms['config'] = '
<form action="'.$p_url.'" method="post">

<p><label class="classic">'.form::checkbox('jn_enab',1,$jn_enab).' '.
	__('Enable Jabber notifications').'</label></p>

<div id="jn_config">
<p><label class="required">'.__('Username:').' '.
	form::field('jn_user',20,255,html::escapeHTML($jn_user)).'</label></p>
<p><label class="required">'.__('Password:').' '.
	form::password('jn_pass',20,255,html::escapeHTML($jn_pass)).'</label></p>
<p><label class="required">'.__('Server:').' '.
	form::field('jn_serv',20,255,html::escapeHTML($jn_serv)).'</label></p>
<p><label class="required">'.__('Port:').' '.
	form::field('jn_port',4,5,html::escapeHTML($jn_port)).'</label></p>
<p><label>'.__('HTTP gateway (leave empty to disable):').' '.
	form::field('jn_gateway',20,255,html::escapeHTML($jn_gateway)).'</label></p>
</div>
<p><input type="submit" name="jn_action_config" value="'.__('save').'" />'.
(is_callable(array($core,'formNonce')) ? $core->formNonce() : '').'</p>
</form>';
endif;

/* FORMULAIRE - Préférences utilisateur
--------------------------------------------------- */

if ($jn_mode == 'user_settings'):
$jn_forms['user_settings'] = '
<fieldset><legend>'.__('Jabber notifications').'</legend>
<p><label>'.__('Notify new comments using Jabber:').' '.
	form::combo('jn_notify',$jn_forms['list_notify'],$jn_notify).'</label></p>
<p id="jn_jabberid"><label class="required">'.__('JabberID that should be notified:').' '.
	form::field(array('jn_jabberid'),20,255,html::escapeHTML($jn_jabberid)).'</label></p>
</fieldset>';
endif;
?>
