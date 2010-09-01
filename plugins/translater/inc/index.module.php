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

if (!defined('DC_CONTEXT_TRANSLATER') || DC_CONTEXT_TRANSLATER != 'module'){return;}

# Create lang
if ($action == 'add_lang')
{
	try
	{
		if (empty($lang))
		{
			throw new Exception(__('No lang to create'));
		}
		$O->addLang($module,$lang,$from);
		
		http::redirect($p_url.'&part=lang&module='.$module.'&type='.$type.'&lang='.$lang.'&msg='.$action);
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}
# Delete lang
if ($action == 'delete_lang')
{
	try
	{
		if (empty($lang))
		{
			throw new Exception(__('No lang to delete'));
		}
		$O->delLang($module,$lang);
		
		http::redirect($p_url.'&part=module&module='.$module.'&type='.$type.'&section=modulelang&msg='.$action);
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}
# Create backup
if ($action == 'create_backup')
{
	try
	{
		if (empty($_POST['modules']) || empty($_POST['langs']))
		{
			throw new Exception(__('No lang to backup'));
		}
		
		foreach($_POST['modules'] as $b_module)
		{
			$b_list = $O->listLangs($b_module);
			foreach($_POST['langs'] as $b_lang)
			{
				if (isset($b_list[$b_lang]))
				{
					$O->createBackup($b_module,$b_lang);
				}
			}
		}
		
		http::redirect($p_url.'&part=module&module='.$module.'&type='.$type.'&section=modulebackup&msg='.$action);
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}
# Restore backup
if ($action == 'restore_backup')
{
	try
	{
		if (empty($_POST['modules']) || empty($_POST['files']))
		{
			throw New Exception(__('No blackup to restore'));
		}
		
		sort($_POST['files']);
		$done = false;
		foreach($_POST['modules'] as $b_module)
		{
			$b_list = $O->listBackups($b_module,true);
			foreach($_POST['files'] as $b_file)
			{
				if (in_array($b_file,$b_list))
				{
					$O->restoreBackup($b_module,$b_file);
					$done = true;
				}
			}
		}
		if (!$done)
		{
			throw new Exception(__('No bakcup to to restore'));
		}
		
		http::redirect($p_url.'&part=module&module='.$module.'&type='.$type.'&section=modulebackup&msg='.$action);
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}
# Delete backup
if ($action == 'delete_backup')
{
	try
	{
		if (empty($_POST['modules']) || empty($_POST['files']))
		{
			throw New Exception(__('No backup to delete'));
		}

		$done = false;
		foreach($_POST['modules'] as $b_module)
		{
			$b_list = $O->listBackups($b_module,true);
			foreach($_POST['files'] as $b_file)
			{
				if (in_array($b_file,$b_list))
				{
					$O->deleteBackup($b_module,$b_file);
					$done = true;
				}
			}
		}
		if (!$done)
		{
			throw new Exception(__('No backup to delete'));
		}
		
		http::redirect($p_url.'&part=module&module='.$module.'&type='.$type.'&section=modulebackup&msg='.$action);
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}
# Import language package
if ($action == 'import_pack')
{
	try
	{
		if (empty($_FILES['packfile']['name']))
		{
			throw new Exception(__('Nothing to import'));
		}
		$O->importPack($_POST['modules'],$_FILES['packfile']);
		
		http::redirect($p_url.'&part=module&module='.$module.'&type='.$type.'&section=modulepack&msg='.$action);
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}
# Export language package
if ($action == 'export_pack')
{
	try
	{
		if (empty($_POST['modules']) || empty($_POST['entries']))
		{
			throw new Exception(__('Nothing to export'));
		}
		$O->exportPack($_POST['modules'],$_POST['entries']);
		
		http::redirect($p_url.'&part=module&module='.$module.'&type='.$type.'&section=modulepack&msg='.$action);
	}
	catch (Exception $e)
	{
		$core->error->add(sprintf($errors[$action],$e->getMessage()));
	}
}

# Get infos on module wanted
try
{
	$M = $O->getModule($module,$type);
}
catch(Exception $e)
{
	$core->error->add(sprintf(__('Failed to launch translater: %s'),$e->getMessage()));
	$action = $module = $type = '';
	$M = false;
}
if (!empty($module) && !empty($type) && !$M)
{
	$action = $module = $type = '';
	$M = false;
}

# Retrieve some infos
$M->langs = $O->listLangs($module);
$M->backups = $O->listBackups($module);
$M->unused_langs = array_flip(array_diff($O->getIsoCodes(),$M->langs));
$M->used_langs = array_flip(array_diff($M->langs,array_flip($O->getIsoCodes())));
$allowed_groups = array_combine(
	dcTranslater::$allowed_l10n_groups,
	dcTranslater::$allowed_l10n_groups
);

echo '
<html>
<head><title>'.__('Translater').' - '.__('Module').'</title>'.$header;

# --BEHAVIOR-- translaterAdminHeaders
$core->callBehavior('translaterAdminHeaders');

echo 
'</head>
<body>'.sprintf($menu,
' &rsaquo; <a href="'.$p_url.'&amp;part=modules&type='.$type.'">'.($type == 'theme' ? __('Themes') : __('Plugins')).'</a>'.
' &rsaquo; "<a href="'.$p_url.'&amp;part=module&type='.$type.'&module='.$module.'&amp;section=modulelang">'.$module.'</a>"'.
($type == 'theme' ?
	' - <a class="button" href="'.$p_url.'&amp;part=modules&amp;type=plugin">'.__('Plugins').'</a>' :
	' - <a class="button" href="'.$p_url.'&amp;part=modules&amp;type=theme">'.__('Themes').'</a>'
).' - <a class="button" href="'.$p_url.'&amp;part=pack">'.__('Import/Export').'</a>'
).$msg.'<div id="module-form">';

# Summary
echo '
<fieldset id="modulesummary"><legend>'.__('Summary').'</legend>
<h3>'.__('Module').'</h3>
<table class="clear">
<tr><th colspan="2">'.__('About').'</th></tr>
<tr class="line">
<td class="nowrap">'.__('Name').'</td><td class="nowrap"> '.$M->name.'</td>
</tr><tr class="line">
<td class="nowrap">'.__('Version').'</td><td class="nowrap"> '.$M->version.'</td>
</tr><tr class="line">
<td class="nowrap">'.__('Author').'</td><td class="nowrap"> '.$M->author.'</td>
</tr><tr class="line">
<td class="nowrap">'.__('Type').'</td><td class="nowrap"> '.$M->type.'</td>
</tr><tr class="line">
<td class="nowrap">'.__('Root').'</td><td class="nowrap"> '.$M->root.'</td>
</tr><tr class="line">
<td class="nowrap">'.__('Backups').'</td><td class="nowrap"> '.
	$O->getBackupFolder($module).'</td>
</tr>
</table>
<p>&nbsp;</p>';

if (count($M->langs))
{
	echo 
	'<h3>'.__('l10n').'</h3>'.
	'<table class="clear">'.
	'<tr>'.
	'<th>'.__('Languages').'</th>'.
	'<th>'.__('Code').'</th>'.
	'<th>'.__('Backups').'</th>'.
	'<th>'.__('Last backup').'</th>'.
	'</tr>';
	
	foreach($M->langs AS $lang => $name)
	{
		echo 
		'<tr class="line">'.
		'<td class="nowrap">'.
		'<a href="'.$p_url.'&amp;part=lang&amp;type='.$type.'&amp;module='.$module.'&amp;lang='.$lang.'">'.$name.'</a>'.
		'</td>'.
		'<td class="nowrap"> '.$lang.'</td>';
		
		if (isset($M->backups[$lang]))
		{
			foreach($M->backups[$lang] AS $file => $info)
			{
				$time[$lang] = isset($time[$lang]) && $time[$lang] > $info['time'] ? 
					$time[$lang] : $info['time'];
			}
			echo 
			'<td class="nowrap">'.count($M->backups[$lang]).'</td>'.
			'<td class="nowrap"> '.
			dt::str('%Y-%m-%d %H:%M',$time[$lang],$core->blog->settings->system->blog_timezone).
			'</td>';
		}
		else
		{
			echo '<td class="nowrap" colspan="4">'.__('no backup').'</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
}
echo '</fieldset>';

# Add/Remove/Edit lang
echo '<fieldset id="modulelang"><legend>'.__('Translations').'</legend>';


# Edit lang
if (!empty($M->langs))
{
	echo '
	<h3>'.__('Edit language').'</h3>
	<form method="post" action="plugin.php">
	<p>'.__('Select language:').' '. 
	form::combo(array('lang'),$M->used_langs,$lang).'</p>
	<p><input type="submit" name="save" value="'.__('Edit translation').'" />'.
	$core->formNonce().
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'').
	form::hidden(array('part'),'lang').
	form::hidden(array('p'),'translater').'
	</p>
	</form>
	<p>&nbsp;</p>';
}

# New lang
if (!empty($M->unused_langs))
{
	echo '
	<h3>'.__('Add language').'</h3>
	<form method="post" action="plugin.php">
	<p class="nowrap">'.__('Select language:').' '. 
	form::combo(array('lang'),array_merge(array('-'=>'-'),$M->unused_langs),$core->auth->getInfo('user_lang')).'</p>';
	if (!empty($M->used_langs))
	{
		echo 
		'<p>'.__('Copy from language:').' '. 
		form::combo(array('from'),array_merge(array('-'=>'-'),$M->used_langs)).
		' ('.__('Optionnal').')</p>';
	}
	else
	{
		echo '<p>'.form::hidden(array('from'),'').'</p>';
	}
	echo '
	<p><input type="submit" name="save" value="'.__('Add translation').'" />'.
	$core->formNonce().
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'add_lang').
	form::hidden(array('section'),$section).
	form::hidden(array('part'),'module').
	form::hidden(array('p'),'translater').'
	</p>
	</form>
	<p>&nbsp;</p>';
}

# Delete lang
if (!empty($M->used_langs))
{
	echo '
	<h3>'.__('Delete language').'</h3>
	<form method="post" action="plugin.php">
	<p>'.__('Select language:').' '. 
	form::combo(array('lang'),array_merge(array('-'=>'-'),$M->used_langs)).'</p>
	<p><input type="submit" name="save" value="'.__('Delete translation').'" />'.
	$core->formNonce().
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'delete_lang').
	form::hidden(array('section'),$section).
	form::hidden(array('part'),'module').
	form::hidden(array('p'),'translater').'
	</p>
	</form>
	<p>&nbsp;</p>';
}
echo '</fieldset>';

# Create/delete/restore backups
if (!empty($M->used_langs) || !empty($M->backups)) {

echo '<fieldset id="modulebackup"><legend>'.__('Backups').'</legend>';

if (!empty($M->used_langs))
{
	echo '
	<h3>'.__('Create backups').'</h3>
	<form method="post" action="plugin.php">
	<p>'.__('Choose languages to backup').'</p>
	<table class="clear">
	<tr><th colspan="3"></th></tr>';
	$i=0;
	foreach($M->used_langs AS $name => $lang)
	{
		$i++;
		echo '
		<tr class="line">
		<td class="minimal">'.form::checkbox(array('langs[]'),$lang,'','','',false).'</td>
		<td class="maximal">'.$name.'</td>
		<td class="nowrap">'.$lang.'</td>
		</tr>';
	}
	echo '
	</table>
	<div class="two-cols">
	<p class="col checkboxes-helpers">&nbsp;</p>
	<p class="col right">&nbsp;</p>
	</div>
	<p>
	<input type="submit" name="save" value="'.__('Backup').'" />'.
	form::hidden(array('modules[]'),$module).
	$core->formNonce().
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'create_backup').
	form::hidden(array('section'),$section).
	form::hidden(array('part'),'module').
	form::hidden(array('p'),'translater').'
	</p>
	</form>
	<p>&nbsp;</p>';
}

if (!empty($M->backups))
{
	echo 
	'<h3>'.__('List of backups').'</h3>'.
	'<form method="post" action="plugin.php">'.
	'<table class="clear">'.
	'<tr>'.
	'<th colspan="2">'.__('File').'</th>'.
	'<th>'.__('Date').'</th>'.
	'<th>'.__('Language').'</th>'.
	'<th>'.__('Size').'</th>'.
	'</tr>';
	$i=0;
	foreach($M->backups as $lang => $langs)
	{
		foreach($langs as $file => $infos)
		{
			$i++;
			echo 
			'<tr class="line">'.
			'<td class="minimal">'.form::checkbox(array('files[]'),$file,'','','',false).'</td>'.
			'<td class="maximal">'.$file.'</td>'.
			'<td class="nowrap">'.
			dt::str(__('%Y-%m-%d %H:%M:%S'),$infos['time'],$core->blog->settings->system->blog_timezone).
			'</td>'.
			'<td class="nowrap">'.$O->isIsoCode($lang).'</td>'.
			'<td class="nowrap">'.files::size($infos['size']).'</td>'.
			'</tr>';
		}
	}
	echo '
	</table>
	<div class="two-cols">
	<p class="col checkboxes-helpers">&nbsp;</p>
	<p class="col right">'.__('Selected backups action:').' '.
	form::combo('action',array(
		__('Restore backups') => 'restore_backup',
		__('Delete backups') => 'delete_backup')
	).'
	<input type="submit" name="save" value="'.__('ok').'" />'.
	form::hidden(array('modules[]'),$module).
	$core->formNonce().
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('section'),$section).
	form::hidden(array('part'),'module').
	form::hidden(array('p'),'translater').'
	</p>
	</div>
	</form>
	<p>&nbsp;</p>';
}
echo '</fieldset>';

} // end if (!empty($M->used_langs) || !empty($M->backups)) {

# Import/Export pack
echo '<fieldset id="modulepack"><legend>'.__('Import/Export').'</legend>';

# Import
echo '
<h3>'.__('Import').'</h3>
<form method="post" action="plugin.php" enctype="multipart/form-data">
<p>'.__('Choose language package to import').'<br />
<input type="file" name="packfile" size="40"/></p>
<p>
<input type="submit" name="save" value="'.__('Import').'" />'.
form::hidden(array('modules[]'),$module).
$core->formNonce().
form::hidden(array('type'),$type).
form::hidden(array('module'),$module).
form::hidden(array('action'),'import_pack').
form::hidden(array('section'),$section).
form::hidden(array('part'),'module').
form::hidden(array('p'),'translater').'
</p>
</form>
<p>&nbsp;</p>';

# Export
if (!empty($M->used_langs))
{
	echo 
	'<h3>'.__('Export').'</h3>'.
	'<form method="post" action="plugin.php">'.
	'<p>'.__('Choose languages to export').'</p>'.
	'<table class="clear">'.
	'<tr><th colspan="3"></th></tr>';
	$i=0;
	foreach($M->used_langs AS $name => $lang)
	{
		$i++;
		echo 
		'<tr class="line">'.
		'<td class="minimal">'.
		form::checkbox(array('entries[]'),$lang,'','','',false).
		'</td>'.
		'<td class="maximal">'.$name.'</td>'.
		'<td class="nowrap">'.$lang.'</td>'.
		'</tr>';
	}
	echo 
	'</table>'.
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers">&nbsp;</p>'.
	'<p class="col right">&nbsp;</p>'.
	'</div>'.
	'<p>'.
	'<input type="submit" name="save" value="'.__('Export').'" />'.
	form::hidden(array('modules[]'),$module).
	$core->formNonce().
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'export_pack').
	form::hidden(array('section'),'pack').
	form::hidden(array('part'),'module').
	form::hidden(array('p'),'translater').
	'</p>'.
	'</form>'.
	'<p>&nbsp;</p>';
}
echo '</fieldset></div>';

dcPage::helpBlock('translater');
echo $footer.'</body></html>';
?>