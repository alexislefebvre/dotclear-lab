<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is Jabber Notifications, a plugin for Dotclear 2      *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

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
<fieldset><legend>'.__('Jabber account setup').'</legend>
<p><label class="classic">'.form::checkbox('jn_enab',1,$jn_enab).' '.
	__('Enable Jabber notifications').'</label></p>

<div id="jn_config" class="two-cols">
<div class="col">
<p><label class="required">'.__('Username:').' '.
	form::field('jn_user',20,255,html::escapeHTML($jn_user)).'</label></p>
<p><label class="required">'.__('Password:').' '.
	form::password('jn_pass',20,255,html::escapeHTML($jn_pass)).'</label></p>
<p><label class="required">'.__('Server:').' '.
	form::field('jn_serv',20,255,html::escapeHTML($jn_serv)).'</label></p>
<p><label class="required">'.__('Port:').' '.
	form::field('jn_port',4,5,html::escapeHTML($jn_port)).'</label></p>
<p><label>'.__('HTTP gateway (leave empty to disable):').' '.
	form::field('jn_gateway',40,255,html::escapeHTML($jn_gateway)).'</label></p>
</div>
<p class="col">'.__('Secure connection:').'<br/>'.
	form::radio(array('jn_con','jn_con1'),'',$jn_con == '').
	'<label class="classic" for="jn_con1"> '.__('None').'</label><br/>'.
	form::radio(array('jn_con','jn_con2'),'ssl://',$jn_con == 'ssl://').
	'<label class="classic" for="jn_con2"> '.__('SSL').'</label><br/>'.
	form::radio(array('jn_con','jn_con3'),'tls://',$jn_con == 'tls://').
	'<label class="classic" for="jn_con3"> '.__('TLS').'</label><br/>'.
'</p><br class="clear" />
</div>
<p><input type="submit" name="jn_action_config" value="'.__('save').'" />'.
(is_callable(array($core,'formNonce')) ? $core->formNonce() : '').'</p>
</fieldset>
<fieldset><legend>'.__('Setup test').'</legend>
<p>'.__('You can test this configuration without saving changes.').'</p>
<p><label>'.__('Destination JabberID:').' '.
	form::field('jn_dest',20,255,html::escapeHTML($jn_dest)).'</label></p>
<p><input type="submit" name="jn_action_test" value="'.__('test').'" /></p>
</fieldset>
</form>';

$jn_forms['help'] = '
<h3>Passerelle HTTP</h3>
<p>Certains hébergeurs (comme Free.fr) n\'autorisent pas les connexions sur le port 5222 qui est utilisé par les serveurs Jabber. Pour contourner ce problème, vous pouvez utiliser une passerelle HTTP.</p>
<p>La passerelle HTTP permet d\'envoyer les notifications Jabber à une page web (accessible généralement sur le port 80) qui se charge de la connexion XMPP pour vous.</p>
<h4>Considérations de sécurité</h4>
<p>Notez que dans ce cas, le mot mot de passe de votre compte Jabber peut être envoyé en clair sur le réseau. De plus, l\'administrateur de la passerelle a techniquement la possibilité d\'intercepter vos identifiants. Il est donc conseillé de créer un compte à part utilisé uniquement pour l\'envoi des notifications Jabber.</p>
<h4>Où trouver des passerelles ?</h4>
<p>La fonctionnalité des passerelles étant nouvelle, à l\'heure actuelle il n\'en existe qu\'une seule :<br/>
<tt>http://services.xn--phnix-csa.net/jabbernotifier/api.php</tt></p>
<h3>Test des paramètres</h3>
<p>Vous pouvez tester les paramètres de votre compte en utilisant le bouton <em>tester</em>. Pour cela, vous devez saisir une adresse Jabber <em>de destination</em>, c\'est-à-dire celle qui sera utilisée pour tester l\'envoi des messages.</p>
<p>Astuce : vous pouvez entrer plusieurs adresses Jabber dans ce champ, séparées par des virgules.</p>
';
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