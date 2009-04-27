<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of "translater" a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) return;
if (!isset($O)) return;

# Retrieve some infos
$M->langs = $O->listLangs($module);
$M->backups = $O->listBackups($module);
$M->unused_langs = array_flip(array_diff($O->getIsoCodes(),$M->langs));
$M->used_langs = array_flip(array_diff($M->langs,array_flip($O->getIsoCodes())));

# Header
echo 
'<h2>'.html::escapeHTML($core->blog->name).' &gt; '.
'<a href="'.$p_url.'">'.__('Translater').'</a> &gt; '.
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

if (count($M->langs)) {
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

# Add/Remove lang
echo '<div class="multi-part" id="lang" title="'.$tabs['lang'].'">';

# New lang
if (!empty($M->unused_langs)) {
	echo '
	<h2>'.__('Add language').'</h2>
	<form method="post" action="'.$p_url.'">
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
if (!empty($M->used_langs)) {
	echo '
	<h2>'.__('Delete language').'</h2>
	<form method="post" action="'.$p_url.'">
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

if (!empty($M->used_langs)) {
	echo '
	<h2>'.__('Create backups').'</h2>
	<form method="post" action="'.$p_url.'">
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

if (!empty($M->backups)) {
	echo 
	'<h2>'.__('List of backups').'</h2>'.
	'<form method="post" action="'.$p_url.'">'.
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
<form method="post" action="'.$p_url.'" enctype="multipart/form-data">
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
if (!empty($M->used_langs)) {
	echo 
	'<h2>'.__('Export').'</h2>'.
	'<form method="post" action="'.$p_url.'">'.
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
if (!empty($M->langs)) {

$M->msgids = $O->getMsgIds($module);
$M->msgstrs = $O->getMsgStrs($module);
foreach($O->listModules() AS $o_module => $o_infos) {
	if ($o_module == $module) continue;
	$M->o_msgstrs[$o_module] = $O->getMsgStrs($o_module);
}
$M->o_msgstrs['dotclear'] = $O->getMsgStrs('dotclear');

foreach($M->langs AS $lang => $iso) {
	echo 
	'<div class="multi-part" id="'.$lang.'" title="'.$iso.'">'.
	'<form method="post" action="'.$p_url.'">'.
	'<table>'.
	'<tr>'.
	'<th>'.__('Group').'</th>'.
	'<th>'.__('String').'</th>'.
	'<th>'.__('File').'</th>'.
	'<th>'.__('Translation').'</th>'.
	'<th>'.__('Existing').'</th>'.
	'</tr>';
	$i = 0;
	foreach ($M->msgids AS $id => $location) {
		$i++;
		$in_dc = ($O->parse_nodc && isset($M->o_msgstrs['dotclear'][$id][$lang]));
		echo 
		'<tr class="line'.($in_dc ? ' offline' : '').'">'.
		'<td class="">'.
		form::combo(
			array('groups['.html::escapeHTML($id).']'),
			array_combine(
				dcTranslater::$allowed_l10n_groups,
				dcTranslater::$allowed_l10n_groups),
			(isset($M->msgstrs[$id][$lang]['group']) ? 
				$M->msgstrs[$id][$lang]['group'] : 'main'),
			'','',$in_dc).
		'</td>'.
		'<td class="">'.html::escapeHTML($id).'</td>'.
		'<td class="nowrap">';
		foreach($location AS $file => $lines) {
			foreach($lines AS $kk => $line) {
				echo $file.' : '.$line.'<br />';
			}
		}
		echo 
		'</td>'.
		'<td class="nowrap">'.
		form::field(
			array('fields['.html::escapeHTML($id).']'),75,255,
			(isset($M->msgstrs[$id][$lang]['msgstr']) ? 
				html::escapeHTML($M->msgstrs[$id][$lang]['msgstr']) : '')
			,'','',
			$in_dc
		).
		'</td>'.
		'<td class="">';
		foreach($M->o_msgstrs AS $o_name => $o_infos) {
			if (!isset($o_infos[$id][$lang])) continue;

			echo str_replace(array('%s','%m','%f'),array(
				'<strong>'.html::escapeHTML($o_infos[$id][$lang]['msgstr']).'</strong>',
				$o_name,$o_infos[$id][$lang]['file']),
				__('%s in %m => %f')).
			'<br />';
		}
		echo '</td></tr>';
	}
	foreach($M->msgstrs AS $id => $info) {
		if (isset($M->msgids[$id])) continue;

		$i++;
		echo 
		'<tr>'.
		'<td class="">'.
		form::combo(array(
			'groups['.html::escapeHTML($id).']'),
			array_combine(
				dcTranslater::$allowed_l10n_groups,
				dcTranslater::$allowed_l10n_groups
			),
			(isset($M->msgstrs[$id][$lang]['group']) ? 
				$M->msgstrs[$id][$lang]['group'] : 'main')
		).
		'</td>'.
		'<td class="">'.html::escapeHTML($id).'</td>'.
		'<td class="nowrap">&nbsp;</td>'.
		'<td class="nowrap">'.
		form::field(array(
			'fields['.html::escapeHTML($id).']'),
			75,
			255,
			(isset($M->msgstrs[$id][$lang]['msgstr']) ? 
				html::escapeHTML($M->msgstrs[$id][$lang]['msgstr']) : '')
		).
		'</td>'.
		'<td class="">';
		if (isset($M->o_msgstrs[$id][$lang])) {
			foreach($M->o_msgstrs[$id][$lang] AS $o_name => $o_infos) {
				echo str_replace(array('%s','%m','%f'),array(
						'<strong>'.html::escapeHTML($o_infos['msgstr']).'</strong>',
						$o_name,
						$o_infos['file']
					),
					__('%s in %m => %f')
				).'<br />';
			}
		} else {
			echo '&nbsp;';
		}
		echo '</td></tr>';
	}
	echo 
	'<tr>'.
	'<td class="">'.
	form::combo(array('wildcard_group'),array_combine(
		dcTranslater::$allowed_l10n_groups,
		dcTranslater::$allowed_l10n_groups),'main'
	).
	'</td>'.
	'<td class="" colspan="2">'.form::field(array('wildcard_id'),75,255,'').'</td>'.
	'<td class="nowrap">'.form::field(array('wildcard_str'),75,255,'').'</td>'.
	'<td class="">&nbsp;</td>'.
	'</tr>'.
	'</table>'.
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
}
} // end if (!empty($M->langs)) {

?>