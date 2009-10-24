<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Empreinte', a plugin for Dotclear 2               *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Empreinte' (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_forms = array();

$_forms['admin_cfg'] = '
<form action="'.$p_url.'" method="post">
<p><label for="authorlink_mask">'.__('HTML display code:').'<label> '.
	form::textArea('authorlink_mask',80,6,html::escapeHTML($authorlink_mask)).
	'</p>
<p>'.form::checkbox('allow_disable',1,$allow_disable).' '.
	'<label for="allow_disable" class="classic">'.
	__('Allow visitors remain anonymous').'</label></p>
<p><input type="submit" name="action_config" value="'.__('Update').'" />'.
	(is_callable(array($core,'formNonce')) ? $core->formNonce() : '').'</p>
</form>';

$_forms['admin_help'] = '
<h2>Configuration générale</h2>
<h3>Le principe</h3>
<p>Remplissez le champ <em>Code HTML</em> en utilisant des variables de la forme
<tt style="font-size:1.5em;">%<ins>n</ins>$s</tt> (où n est un nombre naturel)
pour afficher le navigateur et le système d\'exploitation du visiteur<strong> à
côté de son nom</strong>.</p>
<h3>Liste des variables disponibles</h3>
<table><thead>
<tr><th>Variable</th><th>Signification</th></tr>
</thead><tbody class="noborder">
<tr><th>%1$s</th><td>Nom du visiteur</td></tr>
<tr><th>%2$s</th><td>URL permettant de récupérer un fichier de n\'importe quel
	plugin, p.ex <samp>http://example.com/blog/?pf=</samp></td></tr>
<tr><th>%3$s</th><td>Système d\'exploitation</td></tr>
<tr><th>%4$s</th><td>Système d\'exploitation (en minuscules)</td></tr>
<tr><th>%5$s</th><td>Navigateur web</td></tr>
<tr><th>%6$s</th><td>Navigateur web (en minuscules)</td></tr>
</tbody></table>
<h3>Quelques exemples</h3>
<h4>Afficher le navigateur puis le système d\'exploitation sous forme d\'images
juste après le nom de l\'auteur du commentaire</h4>
<pre>
%1$s
&lt;img src=&quot;%2$sempreinte/icons/%6$s.png&quot; alt=&quot;%5$s&quot; /&gt;
&lt;img src=&quot;%2$sempreinte/icons/%4$s.png&quot; alt=&quot;%3$s&quot; /&gt;
</pre>
<h4>Afficher le nom du système d\'exploitation entre les parenthèses</h4>
<pre>
%1$s (sous %3$s)
</pre>
<h2>Utiliser les fonctions template</h2>
<p>Pour pouvoir contrôler totalemnt l\'affichage, il est possible d\'utiliser
des fonctions template à insérer dans vos fichiers de thèmes.</p>
<h3>Liste des fonctions disponibles</h3>
<table><thead>
<tr><th>Fontion</th><th>Type</th><th>Signification</th><th>Example</th></tr>
</thead><tbody>
<tr><th style="border-bottom-color:#ccc;">CommentIfUserAgent</th>
	<td>Block</td>
	<td>Condition de vérification si les informations concernant le navigateur sont disponibles</td>
	<td><strong>Utilisation :</strong>
<pre style="overflow-x:auto;">
&lt;tpl:CommentIfUserAgent&gt;
Informations renseignées :&lt;br/&gt;
Navigateur : {{tpl:CommentBrowser}}&lt;br/&gt;
Système d\'exploitation : {{tpl:CommentSystem}}
&lt;/tpl:CommentIfUserAgent&gt;</pre></td></tr>
<tr><th style="border-bottom-color:#ccc;">PluginFileURL</th>
	<td>Valeur</td>
	<td>URL permettant de récupérer des fichiers de n\'importe quel plugin</td>
	<td>http://example.com/blog/?pf=</td></tr>
<tr><th style="border-bottom-color:#ccc;">CommentCheckNoEmpreinte</th>
	<td>Valeur</td>
	<td>Renvoie <samp>checked="checked"</samp> si la case
		"<em>Ne pas enregistrer des informations concernant mon navigateur</em>"
		doit être cochée</td>
	<td><strong>Utilisation :</strong>
<pre style="overflow-x:auto;">
&lt;input type="checkbox" name="no_empreinte" value="1"{{tpl:CommentCheckNoEmpreinte}} /&gt;
Ne pas enregistrer des informations concernant mon navigateur</pre></td></tr>
<tr><th style="border-bottom-color:#ccc;">CommentSystem</th>
	<td>Valeur</td>
	<td>Nom du système d\'exploitation</td>
	<td>Windows</td></tr>
<tr><th style="border-bottom-color:#ccc;">CommentBrowser</th><td>Valeur</td>
	<td>Nom du navigateur web</td>
	<td>Firefox</td></tr>
<tr><th style="border-bottom-color:#ccc;">CommentSystemImg</th><td>Valeur</td>
	<td>Icône représentant le système d\'exploitation</td>
	<td><img src="index.php?pf=empreinte/icons/windows.png" alt="Windows" /></td></tr>
<tr><th style="border-bottom-color:#ccc;">CommentBrowserImg</th><td>Valeur</td>
	<td>Icône représentant le navigateur web</td>
	<td><img src="index.php?pf=empreinte/icons/firefox.png" alt="Firefox" /></td></tr>
</tbody></table>
';
?>