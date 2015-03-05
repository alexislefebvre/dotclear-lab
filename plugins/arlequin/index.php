<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
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
 *  License along with 'Arlequin' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$page_title = __('Arlequin');

try
{
	include dirname(__FILE__).'/models.php';

	$messages = array();
	
	/* Initialisation
	--------------------------------------------------- */
	
	$core->blog->settings->addNameSpace('multitheme');
	list($mt_cfg,$mt_exclude) =
		adminArlequin::loadSettings ($core->blog->settings,$initialized);
	
	/* Enregistrement des données depuis les formulaires
	--------------------------------------------------- */
	
	if (isset($_POST['mt_action_config']))
	{
		$mt_cfg['e_html'] = $_POST['e_html'];
		$mt_cfg['a_html'] = $_POST['a_html'];
		$mt_cfg['s_html'] = $_POST['s_html'];
		$mt_exclude = $_POST['mt_exclude'];
	}
	
	/* Traitement des requêtes
	--------------------------------------------------- */
	
	if (isset($_POST['mt_action_config']))
	{
		$core->blog->settings->multitheme->put('mt_cfg',serialize($mt_cfg));
		$core->blog->settings->multitheme->put('mt_exclude',$mt_exclude);
		$messages[] = __('Settings have been successfully updated.');
		$core->blog->triggerBlog();
		http::redirect($p_url.'&config=1');
	}
	if (isset($_POST['mt_action_restore']))
	{
		$core->blog->settings->multitheme->drop('mt_cfg');
		$core->blog->settings->multitheme->drop('mt_exclude');
		$core->blog->triggerBlog();
		http::redirect($p_url.'&restore=1');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

/* DISPLAY
--------------------------------------------------- */

if ($initialized) {
	$messages[] = __('Settings have been reinitialized.');
}

// Headers

$jsModels = ''; $cslashes = "\n\"\'";
foreach ($mt_models as $m)
{
	$jsModels .= "\t".
		'arlequin.addModel('.
		'"'.html::escapeJS($m['name']).'",'.
		'"'.addcslashes($m['s_html'],$cslashes).'",'.
		'"'.addcslashes($m['e_html'],$cslashes).'",'.
		'"'.addcslashes($m['a_html'],$cslashes).'"'.
		");\n";
}

echo '
<html><head>
<title>'.$page_title.'</title>'.
dcPage::jsLoad('index.php?pf=arlequin/js/models.js').'
<script type="text/javascript">
//<![CDATA[
arlequin.msg.predefined_models = "'.html::escapeJS(__('Predefined models')).'";
arlequin.msg.select_model = "'.html::escapeJS(__('Select a model')).'";
arlequin.msg.user_defined = "'.html::escapeJS(__('User defined')).'";
$(function() {
	arlequin.addDefault();
'.$jsModels.'
});
//]]>
</script>
</head><body>'.
dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));

// Messages
if (!empty($_GET['config'])) {
  dcPage::success(__('Settings have been successfully updated.'));
}
if (!empty($_GET['restore'])) {
  dcPage::success(__('Settings have been reinitialized.'));
}

echo
	'<form action="'.$p_url.'" method="post">
<div class="fieldset two-cols"><h4>'.__('Switcher display format').'</h4>
<div id="models"></div>
<p class="col"><label for="s_html">'.__('Switcher HTML code:').'</label> '.
	form::textArea('s_html',50,10,html::escapeHTML($mt_cfg['s_html'])).'</p>
<div class="col">
<p><label>'.__('Item HTML code:').' '.
	form::field('e_html',35,'',html::escapeHTML($mt_cfg['e_html'])).'</label></p>
<p><label>'.__('Active item HTML code:').' '.
	form::field('a_html',35,'',html::escapeHTML($mt_cfg['a_html'])).'</label></p>
</div><br class="clear" />

<p><label>'.__('Excluded themes (separated by slashs \'/\'):').' '.
	form::field(array('mt_exclude'),40,'',html::escapeHTML($mt_exclude)).'</label></p>
</div>
<p><input type="submit" name="mt_action_config" value="'.__('Update').'" />
	<input type="submit" name="mt_action_restore" value="'.__('Restore defaults').'" />'.
	(is_callable(array($core,'formNonce')) ? $core->formNonce() : '').'</p>
</form>';

dcPage::helpBlock('arlequin'); ?>
</body></html>