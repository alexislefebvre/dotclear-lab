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

# This file manage import/export for translater (called from index.php)

if (!defined('DC_CONTEXT_TRANSLATER') || DC_CONTEXT_TRANSLATER != 'pack'){return;}

# Import language packages
if ($action == 'import_pack')
{
	try
	{
		if (empty($_FILES['packfile']['name']))
		{
			throw new Exception(__('Nothing to import'));
		}
		$O->importPack($_POST['modules'],$_FILES['packfile']);
		
		http::redirect($p_url.'&part=pack&msg='.$action.'&section=pack-import');
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}
# Export language packages
if ($action == 'export_pack')
{
	try
	{
		if (empty($_POST['modules']) || empty($_POST['entries']))
		{
			throw new Exception(__('Nothing to export'));
		}
		$O->exportPack($_POST['modules'],$_POST['entries']);
		
		http::redirect($p_url.'&part=pack&msg='.$action.'&section=pack-export');
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}

echo '
<html>
<head><title>'.__('Translater').' - '.__('Import/Export').'</title>'.$header;

# --BEHAVIOR-- translaterAdminHeaders
$core->callBehavior('translaterAdminHeaders');

echo 
'</head>
<body>'.$menu.
'<h3>'.__('Import/Export').'</h3>'.
$msg;

# Import
echo '
<div id="pack-form">
<fieldset id="pack-import"><legend>'.__('Import').'</legend>
<form method="post" action="plugin.php" enctype="multipart/form-data">
<p>'.__('Choose language package to import').'<br />
<input type="file" name="packfile" size="40"/></p>
<p>
<input type="submit" name="save" value="'.__('Import').'" />';
$i=0;
foreach($O->listModules() AS $name => $infos)
{
	if ($O->hide_default && (
	in_array($name,dcTranslater::$default_dotclear_modules['theme']) || 
	in_array($name,dcTranslater::$default_dotclear_modules['theme']))) continue;
	
	echo form::hidden(array('modules[]'),$name);$i++;
}
echo 
$core->formNonce().
form::hidden(array('type'),$type).
form::hidden(array('module'),$module).
form::hidden(array('action'),'import_pack').
form::hidden(array('section'),$section).
form::hidden(array('part'),'pack').
form::hidden(array('p'),'translater').'
</p>
</form>
</fieldset>';

# Export
echo '
<fieldset id="pack-export"><legend>'.__('Export').'</legend>
<form method="post" action="plugin.php">
<p>'.__('Choose modules to export').'</p>
<table class="clear">
<tr><th colspan="2">'.__('Modules').'</th><th>'.__('Languages').'</th></tr>';
$i=0;
$langs_list = array();

foreach($O->listModules() AS $name => $infos)
{
	if ($O->hide_default && (
	in_array($name,dcTranslater::$default_dotclear_modules['theme']) || 
	in_array($name,dcTranslater::$default_dotclear_modules['theme']))) continue;
	
	$info_lang = $O->listLangs($name);
	if (!is_array($info_lang) || 1 > count($info_lang)) continue;

	$i++;
	$langs_list = array_merge($langs_list,$info_lang);

	echo '
	<tr class="line">
	<td class="minimal">'.form::checkbox(array('modules[]'),$name,'','','',false).'</td>
	<td class="nowrap">'.$infos['name'].'</td>
	<td class="maximal">'.implode(', ',$info_lang).'</td>
	</tr>';
}

echo '
</table>
<p>'.__('Choose languages to export').'</p>
<table class="clear">
<tr><th colspan="2">'.__('Languages').'</th><th>'.__('Code').'</th></tr>';
$i=0;
foreach($langs_list AS $lang => $name)
{
	$i++;
	echo '
	<tr class="line">
	<td class="minimal">'.form::checkbox(array('entries[]'),$lang,'','','',false).'</td>
	<td class="nowwrap">'.$name.'</td>
	<td class="maximal">'.$lang.'</td>
	</tr>';
}
echo '
</table>
<div class="two-cols">
<p class="col checkboxes-helpers"></p>
<p class="col right">&nbsp;</p>
</div>
<p>
<input type="submit" name="save" value="'.__('Export').'" />'.
$core->formNonce().
form::hidden(array('type'),$type).
form::hidden(array('module'),$module).
form::hidden(array('action'),'export_pack').
form::hidden(array('section'),$section).
form::hidden(array('part'),'pack').
form::hidden(array('p'),'translater').'
</p>
</form>
</fieldset>
</div>';
dcPage::helpBlock('translater');
echo $footer.'</body></html>';
?>