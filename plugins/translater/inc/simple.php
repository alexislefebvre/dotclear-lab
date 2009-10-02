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

if (!defined('DC_CONTEXT_ADMIN')){return;}
if (!isset($O)) return;

# Modules list
$modules_list = array_merge(
	$O->listModules('plugin'),
	$O->listModules('theme')
);

# Header
echo 
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Translater').'</h2>'.
 (!empty($msg) ? '<p class="message">'.$msg.'</p>' : '').'
<div>';

# Modules list
$mlist = array('plugin'=>array('-'=>'-'),'theme'=>array('-'=>'-'));
foreach ($modules_list AS $k => $v) {
	$mtype = $O->ModuleInfo($k,'type');
	$mlist[$mtype][__($v['name'])] = $k;
}

echo 
($O->two_cols ? '<div class="two-cols"><div class="col">' : '').'

<form method="post" action="plugin.php">
<p>'.__('Choose an extension:').' '.
($O->two_cols ? '' : '<br />').
form::combo(array('module'),$mlist['plugin'],$module).'
<input type="submit" name="save" value="'.__('ok').'" />'.
$core->formNonce().
form::hidden(array('lang'),$lang).
form::hidden(array('type'),'plugin').
form::hidden(array('action'),'').
form::hidden(array('p'),'translater').'</p>
</form>

'.($O->two_cols ? '</div><div class="col">' : '').'

<form method="post" action="plugin.php">
<p>'.__('Choose a theme:').' '.
($O->two_cols ? '' : '<br />').
form::combo(array('module'),$mlist['theme'],$module).'
<input type="submit" name="save" value="'.__('ok').'" />'.
$core->formNonce().
form::hidden(array('lang'),$lang).
form::hidden(array('type'),'theme').
form::hidden(array('action'),'').
form::hidden(array('p'),'translater').'</p>
</form>
'.($O->two_cols ? '</div></div>' : '');

if ($M) {
	$langs = $O->listLangs($module);

if (!$langs)
	echo '<p class="message">'.__('There is no language files for this module').'</p>';
else {
	$backup_folder = $O->getBackupFolder($module);
	$backup_list = $O->listBackups($module);

echo 
($O->two_cols ? '<div class="two-cols"><div class="col">' : '').'

<form method="post" action="plugin.php">
<p>'.__('Choose language to edit:').' '.
($O->two_cols ? '' : '<br />').
form::combo(array('lang'),array_flip($langs),$lang).' 
<input type="submit" name="save" value="'.__('ok').'" />'.
$core->formNonce().
form::hidden(array('type'),$type).
form::hidden(array('module'),$module).
form::hidden(array('action'),'').
form::hidden(array('p'),'translater').'
</p>
</form>';

# New lang
$new_langs_list = array_flip(array_diff($O->getISOcodes(),$langs));

if (!empty($new_langs_list)) {
	echo 
	($O->two_cols ? '</div><div class="col">' : '').
	'<form method="post" action="plugin.php">'.
	'<p>'.__('Choose language to add:').' '.
	($O->two_cols ? '' : '<br />').
	form::combo(array('lang'),array_merge(array('-'=>'-'),$new_langs_list)).' '.
	'<input type="submit" name="save" value="'.__('ok').'" />'.
	$core->formNonce().
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'add_lang').
	form::hidden(array('p'),'translater').
	'</p>'.
	'</form>'.
	'<p>&nbsp;</p>';
}
echo ($O->two_cols ? '</div></div>' : '');

# Existing langs
if (isset($langs[$lang]))
{
	$name = $langs[$lang];

	echo '
	<div>
	<div id="action_'.$lang.'">';

	# Delete
	echo '
	<form method="post" action="plugin.php" style="float:left"><div>&nbsp;
	<input type="submit" name="save" value="'.__('Delete translation').'" />'.
	$core->formNonce().
	form::hidden(array('lang'),$lang).
	form::hidden(array('type'),$type).
	form::hidden(array('module'),$module).
	form::hidden(array('action'),'delete_lang').
	form::hidden(array('p'),'translater').'</div>
	</form>';

	# Backup
	if ($backup_folder) {
		echo '
		<form method="post" action="plugin.php" style="float:left"><div>&nbsp;
		<input type="submit" name="save" value="'.__('Backup translation').'" />'.
		form::hidden(array('langs[0]'),$lang).
		form::hidden(array('modules[0]'),$module).
		$core->formNonce().
		form::hidden(array('type'),$type).
		form::hidden(array('module'),$module).
		form::hidden(array('action'),'create_backup').
		form::hidden(array('p'),'translater').'</div>
		</form>';
	}

	# Restore
	if (isset($backup_list[$lang]) 
	 && 0 < count($backup_list[$lang])) {

		$last = array();
		foreach($backup_list[$lang] as $file => $infos) {
			$last[$infos['time']] = $file;
		}
		sort($last);
		$bck = array_reverse($last);

		$bck_file = '';
		if ($O->backup_auto && 1 < count($bck)) {
			$bck_file = $bck[1];
		} elseif (!$O->backup_auto && isset($bck[0])) {
			$bck_file = $bck[0];
		}
		if ('' != $bck_file) {
			echo 
			'<form method="post" action="'.$p_url.'" style="float:left"><div>&nbsp;'.
			'<input type="submit" name="save" value="'.__('Restore last backup').'" '.
			'title="'.dt::str('%Y-%m-%d %H:%M:%S',$backup_list[$lang][$bck_file]['time']).
			'" />'.
			$core->formNonce().
			form::hidden(array('files[0]'),$bck_file).
			form::hidden(array('modules[0]'),$module).
			form::hidden(array('action'),'restore_backup').
			form::hidden(array('type'),$type).
			form::hidden(array('module'),$module).
			form::hidden(array('p'),'translater').
			'</div>'.
			'</form>';
		}
	}
	echo '</div>';

	# translate
	echo 
	'<form method="post" action="plugin.php" class="clear">'.
	'<table>'.
	'<tr>'.
	'<th class="offline">#</th>'.
	'<th>'.__('String').'</th>'.
	'<th>'.__('Translation').'</th>'.
	'<th>'.__('Existing').'</th>'.
	'</tr>';
	$i = 0;
	$msgs = $O->getMsgs($module,$lang);
	foreach ($msgs AS $msgid => $rs) {

		$i++;
		$in_dc = ($rs['in_dc'] && $O->parse_nodc);
		echo 
		'<tr class="line'.($in_dc ? ' offline' : '').'">'.
		'<td class="minimal offline">#'.$i.'</td>'.
		'<td';
		if ('' != $O->proposal_tool) {
			echo ' class="translaterproposal" title="'.
			__('Click to try to translate').'"';
		}
		echo
		'>'.html::escapeHTML($msgid).'</td>'.
		'<td class="nowrap">'.
		form::hidden(array('entries['.$i.'][group]'),$rs['group']).
		form::hidden(array('entries['.$i.'][msgid]'),html::escapeHTML($msgid)).
		form::field(array('entries['.$i.'][msgstr]'),
			75,255,html::escapeHTML($rs['msgstr']),'','',$in_dc).
		'</td>'.
		'<td class="">';
		$o_strs = array();
		foreach($rs['o_msgstrs'] AS $o_msgstr) {
			$o_strs[$o_msgstr['msgstr']][] = $o_msgstr['module'];
		}
		foreach($o_strs AS $o_str => $o_modules) {
			echo 
			'<strong>'.html::escapeHTML($o_str).'</strong> '.
			'('.implode(', ',$o_modules).') <br />';
		}
		echo '</td></tr>';
	}
	echo 
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

} // end if (!count($O->langs)) { } else


} // end if (!$O->exists) { } else 
echo '</div>';
?>
