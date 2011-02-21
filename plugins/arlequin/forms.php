<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Arlequin', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Arlequin' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$mt_forms = array();

$mt_forms['admin_cfg'] = '
<form action="'.$p_url.'" method="post">
<fieldset class="two-cols"><legend>'.__('Switcher display format').'</legend>
<div id="models"></div>
<p class="col"><label for="s_html">'.__('Switcher HTML code:').'</label> '.
	form::textArea('s_html',50,10,html::escapeHTML($mt_cfg['s_html'])).'</p>
<div class="col">
<p><label>'.__('Item HTML code:').' '.
	form::field('e_html',35,'',html::escapeHTML($mt_cfg['e_html'])).'</label></p>
<p><label>'.__('Active item HTML code:').' '.
	form::field('a_html',35,'',html::escapeHTML($mt_cfg['a_html'])).'</label></p>
</div><br class="clear" />
<p><label class="classic">'.
	form::checkbox(array('mt_homeonly'),1,$mt_cfg['homeonly']).
	__('Home page only').'</label></p>
<p><label>'.__('Excluded themes (separated by slashs \'/\'):').
	form::field(array('mt_exclude'),40,'',html::escapeHTML($mt_exclude)).'</label></p>
</fieldset>
<p><input type="submit" name="mt_action_config" value="'.__('Update').'" />
	<input type="submit" name="mt_action_restore" value="'.__('Restore defaults').'" />'.
	(is_callable(array($core,'formNonce')) ? $core->formNonce() : '').'</p>
</form>';

$mt_forms['admin_help'] = '
<h2>Insertion du sélecteur de thème dans l\'interface du blog</h2>
<h3>Avec un widget</h3>
<p>Le plus simple est d\'utiliser le <a href="plugin.php?p=widgets">widget</a> <em>Sélecteur de thème</em> qui affiche la liste des thèmes disponibles.</p>
<h3>Dans le fichier template</h3>
<p>Le sélecteur peut aussi être intégré dans l\'interface du blog en éditant directement votre fichier template. Il suffit pour cela d\'ajouter l\'instruction <code>{{tpl:themesList}}</code> à l\'endroit voulu.</p>
<h2>Comprendre les modèles</h2>
<p>Arlequin est remarquable pour sa souplesse de configuration, due à la notion de <em>modèles</em> qui permettent de créer ses propres interfaces pour le sélecteur de thème.</p>
<p>Les informations nécessaires au fonctionnement du sélecteur de thème sont contenues dans des variables de la forme <strong>%n$s</strong> où <strong>n</strong> est un entier désignant une variable.</p>
<p>Voici la liste des variables que vous pouvez utiliser :</p>
<h3>'.__('In switcher HTML code').'</h3>
<table><thead>
<tr><th>'.__('Variable').'</th><th>'.__('Meaning').'</th></tr>
</thead><tbody class="noborder">
<tr><th>%1$s</th><td>'.sprintf(__('Current page %sURL%s'),
	'<acronym title="'.__('Uniform Ressource Locator').'">','</acronym>').
	'</td></tr>
<tr><th>%2$s</th><td>'.__('Items HTML code').'</td></tr>
</tbody></table>
<h3>'.__('In items HTML code').'</h3>
<table><thead>
<tr><th>'.__('Variable').'</th><th>'.__('Meaning').'</th></tr>
</thead><tbody class="noborder">
<tr><th>%1$s</th><td>'.sprintf(__('Current page %sURL%s'),
	'<acronym title="'.__('Uniform Ressource Locator').'">','</acronym>').
	'</td></tr>
<tr><th>%2$s</th><td>'.
	sprintf(__('A suffix to send theme setting through %sURL%s, e.g. \'%3$s\''),
	'<acronym title="'.__('Uniform Ressource Locator').'">','</acronym>',
	'<strong>&amp;theme=</strong>').'</td></tr>
<tr><th>%3$s</th><td>'.__('Theme identifier').' '.'<em>à utiliser uniquement dans une URL</em>'.'</td></tr>
<tr><th>%4$s</th><td>'.__('Theme name').'</td></tr>
<tr><th>%5$s</th><td>'.__('Theme description').'</td></tr>
<tr><th>%6$s</th><td>'.__('Theme identifier').'</td></tr>
</tbody></table>
<h2>Ajouter ses propres modèles prédéfinis</h2>
<p>Si vous êtes l\'administrateur d\'une plate-forme de blogs, vous pouvez modifier les modèles prédéfinis en éditant le fichier <strong>plugins/arlequin/models.php</strong>.</p>
<p><strong>Conseil</strong> : des modèles assez intéressants peuvent être créés en association avec du JavaScript ou avec des propriétés <acronym title="Cascading Style Sheet">CSS</acronym>, en modifiant vos thèmes.</p>
';
?>