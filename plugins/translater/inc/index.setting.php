<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of translater, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# This file manage settings of translater (called from index.php)

if (!defined('DC_CONTEXT_TRANSLATER') || DC_CONTEXT_TRANSLATER != 'setting'){return;}
if (!$core->auth->check('admin',$core->blog->id)){return;}

# Update settings
if ($action == 'save_setting')
{
	try
	{
		if (empty($_POST['settings']))
		{
			throw new Exception(__('No setting update'));
		}
		
		if (empty($_POST['settings']['write_po'])
		 && empty($_POST['settings']['write_langphp']))
		{
			throw new Exception('You must choose one file format at least');
		}
		
		$settings = array();
		foreach($O->getDefaultSettings() as $k => $v)
		{
			$O->set($k,(isset($_POST['settings'][$k]) ? $_POST['settings'][$k] : ''));
		}
		
		http::redirect($p_url.'&part=setting&msg='.$action.'&section='.$section);
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}

echo '
<html>
<head><title>'.__('Translater').' - '.__('Settings').'</title>'.$header;

# --BEHAVIOR-- translaterAdminHeaders
$core->callBehavior('translaterAdminHeaders');

echo 
'</head>
<body>'.$menu.
'<h3>'.__('Settings').'</h3>'.
$msg.'
<form id="setting-form" method="post" action="'.$p_url.'">

<fieldset id="settingtranslation"><legend>'.__('Translation').'</legend>
<p><label class="classic">'.
form::checkbox(array('settings[write_po]'),'1',$O->write_po).' 
'.__('Write .po files').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[write_langphp]'),'1',$O->write_langphp).' 
'.__('Write .lang.php files').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[scan_tpl]'),'1',$O->scan_tpl).' 
'.__('Translate also strings of template files').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[parse_nodc]'),'1',$O->parse_nodc).' 
'.__('Translate only unknow strings').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[hide_default]'),'1',$O->hide_default).' 
'.__('Hide default modules of Dotclear').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[parse_comment]'),'1',$O->parse_comment).' 
'.__('Write comments in files').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[parse_user]'),'1',$O->parse_user).' 
'.__('Write informations about author in files').'</label><br />
'.form::field(array('settings[parse_userinfo]'),65,255,$O->parse_userinfo).'</p>
</fieldset>

<fieldset id="settingtool"><legend>'.__('Tools').'</legend>
<p><label class="classic">'.__('Use an help tool for translation:').'<br />'.
form::combo(array('settings[proposal_tool]'),
	$combo_proposal_tool,$O->proposal_tool).'</label></p>
<p><label class="classic">'.__('Default language of l10n source:').'<br />'.
form::combo(array('settings[proposal_lang]'),
	array_flip($O->getIsoCodes()),$O->proposal_lang).'</label></p>
</fieldset>

<fieldset id="settingpack"><legend>'.__('Import/Export').'</legend>
<p><label class="classic">'.
form::checkbox(array('settings[import_overwrite]'),'1',$O->import_overwrite).' 
'.__('Overwrite existing languages').'</label></p>
<p><label class="classic">'.__('Name of exported package').'<br />
'.form::field(array('settings[export_filename]'),65,255,$O->export_filename).'</label></p>
</fieldset>

<fieldset id="settingbackup"><legend>'.__('Backups').'</legend>
<p><label class="classic">'.
form::checkbox(array('settings[backup_auto]'),'1',$O->backup_auto).' 
'.__('Make backups when changes are made').'</label></p>
<p><label class="classic">'.sprintf(__('Limit backups to %s files per module'),
form::combo(array('settings[backup_limit]'),
	array(5=>5,10=>10,15=>15,20=>20,40=>40,60=>60),$O->backup_limit)).'</label></p>
<p><label class="classic">'.sprintf(__('Store backups in %s'),
form::combo(array('settings[backup_folder]'),
	array_flip($combo_backup_folder),$O->backup_folder)).'</label></p>
</fieldset>

<fieldset id="settingplugin"><legend>'.__('Behaviors').'</legend>
<p><label class="classic">'.
form::checkbox(array('settings[plugin_menu]'),'1',$O->plugin_menu).' 
'.__('Enable menu on extensions page').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[theme_menu]'),'1',$O->theme_menu).' 
'.__('Enable menu on themes page').'</label></p>
</fieldset>

<div class="clear">
<p><input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('p'),'translater').
form::hidden(array('part'),'setting').
form::hidden(array('section'),$section).
form::hidden(array('action'),'save_setting').'
</p></div>
</form>';

dcPage::helpBlock('translater');
echo $footer.'</body></html>';
?>