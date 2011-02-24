<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Arlequin, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

try
{
	include dirname(__FILE__).'/models.php';

	$messages = array();
	$default_tab = empty($_GET['tab']) ? 'mt_config' : html::escapeHTML($_GET['tab']);
	
	/* Initialisation
	--------------------------------------------------- */
	
	$s = &$core->blog->settings->arlequin;
	
	list($config,$exclude) = adminArlequin::loadSettings($s,$initialized);
	
	/* Enregistrement des données depuis les formulaires
	--------------------------------------------------- */
	
	if (isset($_POST['mt_action_config']))
	{
		$config['e_html'] = $_POST['e_html'];
		$config['a_html'] = $_POST['a_html'];
		$config['s_html'] = $_POST['s_html'];
		$config['homeonly'] = (bool) $_POST['mt_homeonly'];
		$exclude = $_POST['mt_exclude'];
	}
	
	/* Traitement des requêtes
	--------------------------------------------------- */
	
	if (isset($_POST['mt_action_config']))
	{
		$s->put('config',serialize($config));
		$s->put('exclude',$exclude);
		$messages[] = __('Settings have been successfully updated.');
		$core->blog->triggerBlog();
	}
	if (isset($_POST['mt_action_restore']))
	{
		$s->drop('config');
		$s->drop('exclude');
		$core->blog->triggerBlog();
		http::redirect($p_url);
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
<title>'.__('Arlequin - theme switcher configuration').'</title>'.
dcPage::jsToolMan().($default_tab ? dcPage::jsPageTabs($default_tab) : '').
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
</head><body>
<h2 style="padding:8px 0 8px 34px;background:url(index.php?pf=arlequin/icon_32.png) no-repeat;">'.
	__('Arlequin configuration').'</h2>';

// Messages
if (!empty($messages))
{
	if (count($messages) < 2)
	{
		echo '	<p class="message">'.end($messages)."</p>\n";
	}
	else
	{
		echo '<ul class="message">';
		foreach ($messages as $message)
		{
			echo '	<li>'.$message."</li>\n";
		}
		echo "</ul>\n";
	}
}

include dirname(__FILE__).'/forms.php';

echo
	'<div class="multi-part" id="mt_config" title="'.__('Configuration').'">'.
	$mt_forms['admin_cfg'].
	"</div>\n\n".
	'<div class="multi-part" id="mt_help" title="'.__('Help').'">'.
	$mt_forms['admin_help'].
	"</div>\n\n";
?>
</body></html>