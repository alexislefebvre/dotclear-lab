<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of translater, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}
if (!isset($O)) return;

# Retrieve some infos
$M->langs = $O->listLangs($module);
$M->backups = $O->listBackups($module);
$M->unused_langs = array_flip(array_diff($O->getIsoCodes(),$M->langs));
$M->used_langs = array_flip(array_diff($M->langs,array_flip($O->getIsoCodes())));
$allowed_groups = array_combine(
	dcTranslater::$allowed_l10n_groups,
	dcTranslater::$allowed_l10n_groups
);

# Header
echo 
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.
'<a href="'.$p_url.'">'.__('Translater').'</a> &rsaquo; '.
'<a href="'.$p_url.'&amp;type='.$type.'&amp;module='.$module.'">'.
str_replace('%s',$module,__('Translation of %s')).'</a></h2>'.
(!empty($msg) ? '<p class="message">'.$msg.'</p>' : '').
'<p><a href="'.$p_url.'&amp;tab='.$type.'">'.
($type == 'plugin' ? __('Back to plugins list') : __('Back to themes list')).'</a>'.
'</p>';

# Summary
echo '
<div class="multi-part" id="summary" title="'.$tabs['summary'].'">
<h2>'.__('Module').'</h2>
<table class="clear">
<tr><th colspan="2">'.__('About').'</th></tr>
<tr class="line">
<td class="nowrap"><a>'.__('Name').'</a></td><td class="nowrap"> '.$M->name.'</td>
</tr><tr class="line">
<td class="nowrap"><a>'.__('Version').'</a></td><td class="nowrap"> '.$M->version.'</td>
</tr><tr class="line">
<td class="nowrap"><a>'.__('Author').'</a></td><td class="nowrap"> '.$M->author.'</td>
</tr><tr class="line">
<td class="nowrap"><a>'.__('Type').'</a></td><td class="nowrap"> '.$M->type.'</td>
</tr><tr class="line">
<td class="nowrap"><a>'.__('Root').'</a></td><td class="nowrap"> '.$M->root.'</td>
</tr><tr class="line">
<td class="nowrap"><a>'.__('Backups').'</a></td><td class="nowrap"> '.
	$O->getBackupFolder($module).'</td>
</tr>
</table>
<p>&nbsp;</p>';

if (count($M->langs))
{
	echo 
	'<h2>'.__('l10n').'</h2>'.
	'<table class="clear">'.
	'<tr>'.
	'<th>'.__('Languages').'</th>'.
	'<th>'.__('Code').'</th>'.
	'<th>'.__('Backups').'</th>'.
	'<th>'.__('Last backup').'</th>'.
	'</tr>';
	foreach($M->langs AS $lang => $name) {
		echo 
		'<tr class="line">'.
		'<td class="nowrap">'.
		'<a href="plugin.php?p=translater'.
			'&amp;type='.$type.'&amp;module='.$module.'&amp;tab='.$lang.'">'.$name.'</a>'.
		'</td>'.
		'<td class="nowrap"> '.$lang.'</td>';

		if (isset($M->backups[$lang])) {
			foreach($M->backups[$lang] AS $file => $info) {
				$time[$lang] = isset($time[$lang]) && $time[$lang] > $info['time'] ? 
					$time[$lang] : $info['time'];
			}
			echo 
			'<td class="nowrap">'.count($M->backups[$lang]).'</td>'.
			'<td class="nowrap"> '.
			dt::str('%Y-%m-%d %H:%M',$time[$lang],$core->blog->settings->blog_timezone).
			'</td>';
		} else {
			echo '<td class="nowrap" colspan="4">'.__('no backup').'</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
}
echo '</div>';

# Add/Remove/Edit lang
echo '<div class="multi-part" id="lang" title="'.$tabs['lang'].'">';


# Edit lang
if (!empty($M->langs))
{
	echo '
	<h2>'.__('Edit language').'</h2>
	<form method="post" action="plugin.php">
	<p>'.__('Select language:').' '. 
	form::combo(array('tab'),$M->used_langs,$tab).'</p>
	<p><input type="submit" name="save" value="'.__('Edit translation').'" />'.
	$core->formNonce().
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'').
	form::hidden(array('p'),'translater').'
	</p>
	</form>
	<p>&nbsp;</p>';
}

# New lang
if (!empty($M->unused_langs))
{
	echo '
	<h2>'.__('Add language').'</h2>
	<form method="post" action="plugin.php">
	<p class="nowrap">'.__('Select language:').' '. 
	form::combo(array('lang'),array_merge(array('-'=>'-'),$M->unused_langs)).'</p>';
	if (!empty($M->used_langs)) {
		echo 
		'<p>'.__('Copy from language:').' '. 
		form::combo(array('from'),array_merge(array('-'=>'-'),$M->used_langs)).
		' ('.__('Optionnal').')</p>';
	} else {
		echo '<p>'.form::hidden(array('from'),'').'</p>';
	}
	echo '
	<p><input type="submit" name="save" value="'.__('Add translation').'" />'.
	$core->formNonce().
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'add_lang').
	form::hidden(array('p'),'translater').'
	</p>
	</form>
	<p>&nbsp;</p>';
}

# Delete lang
if (!empty($M->used_langs))
{
	echo '
	<h2>'.__('Delete language').'</h2>
	<form method="post" action="plugin.php">
	<p>'.__('Select language:').' '. 
	form::combo(array('lang'),array_merge(array('-'=>'-'),$M->used_langs)).'</p>
	<p><input type="submit" name="save" value="'.__('Delete translation').'" />'.
	$core->formNonce().
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'delete_lang').
	form::hidden(array('p'),'translater').'
	</p>
	</form>
	<p>&nbsp;</p>';
}
echo '</div>';

# Create/delete/restore backups
if (!empty($M->used_langs) || !empty($M->backups)) {

echo '<div class="multi-part" id="backup" title="'.$tabs['backup'].'">';

if (!empty($M->used_langs))
{
	echo '
	<h2>'.__('Create backups').'</h2>
	<form method="post" action="plugin.php">
	<p>'.__('Choose languages to backup').'</p>
	<table class="clear">
	<tr><th colspan="3"></th></tr>';
	$i=0;
	foreach($M->used_langs AS $name => $lang) {
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
	form::hidden(array('p'),'translater').'
	</p>
	</form>
	<p>&nbsp;</p>';
}

if (!empty($M->backups))
{
	echo 
	'<h2>'.__('List of backups').'</h2>'.
	'<form method="post" action="plugin.php">'.
	'<table class="clear">'.
	'<tr>'.
	'<th colspan="2">'.__('File').'</th>'.
	'<th>'.__('Date').'</th>'.
	'<th>'.__('Language').'</th>'.
	'<th>'.__('Size').'</th>'.
	'</tr>';
	$i=0;
	foreach($M->backups as $lang => $langs) {
		foreach($langs as $file => $infos) {
			$i++;
			echo 
			'<tr class="line">'.
			'<td class="minimal">'.form::checkbox(array('files[]'),$file,'','','',false).'</td>'.
			'<td class="maximal">'.$file.'</td>'.
			'<td class="nowrap">'.
			dt::str(__('%Y-%m-%d %H:%M:%S'),$infos['time'],$core->blog->settings->blog_timezone).
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
	form::hidden(array('p'),'translater').'
	</p>
	</div>
	</form>
	<p>&nbsp;</p>';
}
echo '</div>';

} // end if (!empty($M->used_langs) || !empty($M->backups)) {

# Import/Export pack
echo '<div class="multi-part" id="pack" title="'.$tabs['pack'].'">';

# Import
echo '
<h2>'.__('Import').'</h2>
<form method="post" action="plugin.php" enctype="multipart/form-data">
<p>'.__('Choose package to import').'<br />
<input type="file" name="packfile" size="40"/></p>
<p>
<input type="submit" name="save" value="'.__('Import').'" />'.
form::hidden(array('modules[]'),$module).
$core->formNonce().
form::hidden(array('type'),$type).
form::hidden(array('module'),$module).
form::hidden(array('action'),'import_pack').
form::hidden(array('p'),'translater').'
</p>
</form>
<p>&nbsp;</p>';

# Export
if (!empty($M->used_langs))
{
	echo 
	'<h2>'.__('Export').'</h2>'.
	'<form method="post" action="plugin.php">'.
	'<p>'.__('Choose languages to export').'</p>'.
	'<table class="clear">'.
	'<tr><th colspan="3"></th></tr>';
	$i=0;
	foreach($M->used_langs AS $name => $lang) {
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
	form::hidden(array('p'),'translater').
	'</p>'.
	'</form>'.
	'<p>&nbsp;</p>';
}
echo '</div>';

# Existing langs
if (!empty($M->langs) && isset($M->langs[$tab]))
{
	$lang = $tab;
	$iso = $M->langs[$tab];

	$i = 0;
	$sort_order = 'asc';
	$lines = $O->getMsgs($module,$lang);

	# Sort array
	if (isset($_GET['sort']) && !empty($lines)) {
		$sort = explode(',',$_GET['sort']);
		$sort_by = $sort[0];
		$sort_order = isset($sort[1]) && $sort[1] == 'desc' ? 'asc' : 'desc';

		switch($sort_by) {
			case 'group':
			foreach($lines AS $k => $v) {
				$sort_list[] = $v['group'];
			}
			break;

			case 'msgid':
			foreach($lines AS $k => $v) {
				$sort_list[] = strtolower($k);
			}
			break;

			case 'file':
			foreach($lines AS $k => $v) {
				$file = array();
				foreach($v['files'] as $fv) {
					$file[] = empty($fv[0]) || empty($fv[1]) ? '' : $fv[0].($fv[1] /1000);
				}
				sort($file);
				$sort_list[] = $file[0];
			}
			break;

			case 'msgstr':
			foreach($lines AS $k => $v) {
				$sort_list[] = strtolower($v['msgstr']);
			}
			break;

			default:
			$sort_list = false;
			break;
		}
		if ($sort_list) {
			array_multisort(
				$sort_list,
				($sort_order == 'asc' ? SORT_DESC : SORT_ASC),
				SORT_STRING,
				$lines
			);
		}
	}

	echo 
	'<div class="multi-part" id="'.$lang.'" title="'.$iso.'">'.
	'<form method="post" action="plugin.php">'.
	'<table>'.
	'<tr>'.
	'<th><a href="'.$p_url.'&amp;module='.$module.'&amp;type='.$type.'&amp;tab='.$lang.
	'&amp;sort=group,'.$sort_order.'">'.__('Group').'</a></th>'.
	'<th><a href="'.$p_url.'&amp;module='.$module.'&amp;type='.$type.'&amp;tab='.$lang.
	'&amp;sort=msgid,'.$sort_order.'">'.__('String').'</a></th>'.
	'<th><a href="'.$p_url.'&amp;module='.$module.'&amp;type='.$type.'&amp;tab='.$lang.
	'&amp;sort=file,'.$sort_order.'">'.__('File').'</a></th>'.
	'<th><a href="'.$p_url.'&amp;module='.$module.'&amp;type='.$type.'&amp;tab='.$lang.
	'&amp;sort=msgstr,'.$sort_order.'">'.__('Translation').'</a></th>'.
	'<th>'.__('Existing').'</th>'.
	'</tr>';

	foreach ($lines AS $msgid => $rs) {

		$i++;
		$in_dc = ($rs['in_dc'] && $O->parse_nodc);
		echo 
		'<tr class="line'.($in_dc ? ' offline' : ' translaterline').'">'.
		'<td class="nowrap">'.
		form::checkbox(array('entries['.$i.'][check]'),1).' '.
		form::combo(array('entries['.$i.'][group]'),
			$allowed_groups,$rs['group'],'','',$in_dc
		).
		'</td>'.
		'<td'.('' != $O->proposal_tool ? ' class="translatermsgid"' : '' ).'>'.
		html::escapeHTML($msgid).'</td>'.
		'<td class="nowrap">';
		foreach($rs['files'] as $location) {
			echo implode(' : ',$location).'<br />';
		}
		echo 
		'</td>'.
		'<td class="nowrap translatertarget">'.
		form::hidden(array('entries['.$i.'][msgid]'),html::escapeHTML($msgid)).
		form::field(array('entries['.$i.'][msgstr]'),
			75,255,html::escapeHTML($rs['msgstr']),'','',$in_dc).
		'</td>'.
		'<td class="translatermsgstr">';
		foreach($rs['o_msgstrs'] AS $o_msgstr) {

			echo str_replace(array('%s','%m','%f'),array(
				'<strong>'.html::escapeHTML($o_msgstr['msgstr']).'</strong>',
				$o_msgstr['module'],$o_msgstr['file']),
				__('%s in %m => %f')).
			'<br />';
		}
		echo '</td></tr>';
	}

	$i++;
	echo 
	'<tr>'.
	'<td class="nowrap">'.
	form::checkbox(array('entries['.$i.'][check]'),1).' '.
	form::combo(array('entries['.$i.'][group]'),$allowed_groups,'main').
	'</td>'.
	'<td class="" colspan="2">'.form::field(array('entries['.$i.'][msgid]'),75,255,'').'</td>'.
	'<td class="nowrap">'.form::field(array('entries['.$i.'][msgstr]'),75,255,'').'</td>'.
	'<td class="">&nbsp;</td>'.
	'</tr>'.
	'</table>'.
	'<p>'.sprintf(__('Total of %s strings.'),$i-1).'</p>'.
	'<p class="col checkboxes-helpers"></p>'.
	'<p class="col right">'.__('Change the group of the selected entries to:').' '.
	form::combo(array('multigroup'),$allowed_groups).
	'</p>'.
	'<p>'.
	'<input type="submit" name="save" value="'.__('save').'" />'.
	$core->formNonce().
	form::hidden(array('lang'),$lang).
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'update_lang').
	form::hidden(array('p'),'translater').
	'</p>'.
	'</form>'.
	'<p>&nbsp;</p>'.
	'</div>';
} // end if (!empty($M->langs)) {

?>