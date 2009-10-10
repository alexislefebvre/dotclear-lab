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

# Header
echo 
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Translater').'</h2>'.
 (!empty($msg) ? '<p class="message">'.$msg.'</p>' : '');

# Administration
echo 
'<div class="multi-part" id="setting" title="'.$tabs['setting'].'">
<form method="post" action="plugin.php">

'.($O->two_cols ? '<div class="two-cols"><div class="col">' : '').'

<h2>'.__('Interface').'</h2>
<p><label class="classic">'.
form::checkbox(array('settings[light_face]'),'1',$O->light_face).' 
'.__('Enable simple interface').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[two_cols]'),'1',$O->two_cols).' 
'.__('Use two columns on admin page').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[plugin_menu]'),'1',$O->plugin_menu).' 
'.__('Enable menu on extensions page').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[theme_menu]'),'1',$O->theme_menu).' 
'.__('Enable menu on themes page').'</label></p>

<h2>'.__('Translation').'</h2>
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
form::checkbox(array('settings[parse_comment]'),'1',$O->parse_comment).' 
'.__('Write comments in files').'</label></p>
<p><label class="classic">'.
form::checkbox(array('settings[parse_user]'),'1',$O->parse_user).' 
'.__('Write informations about author in files').'</label><br />
'.form::field(array('settings[parse_userinfo]'),65,255,$O->parse_userinfo).'</p>

'.($O->two_cols ? '</div></div><div class="two-cols"><div class="col">' : '').'

<h2>'.__('Tools').'</h2>
<p><label class="classic">'.__('Use an help tool for translation:').'<br />'.
form::combo(array('settings[proposal_tool]'),
	$combo_proposal_tool,$O->proposal_tool).'</label></p>
<p><label class="classic">'.__('Default language of l10n source:').'<br />'.
form::combo(array('settings[proposal_lang]'),
	array_flip($O->getIsoCodes()),$O->proposal_lang).'</label></p>

<h2>'.__('Import/Export').'</h2>
<p><label class="classic">'.
form::checkbox(array('settings[import_overwrite]'),'1',$O->import_overwrite).' 
'.__('Overwrite existing languages').'</label></p>
<p><label class="classic">'.__('Name of exported package').'<br />
'.form::field(array('settings[export_filename]'),65,255,$O->export_filename).'</label></p>

<h2>'.__('Backups').'</h2>
<p><label class="classic">'.
form::checkbox(array('settings[backup_auto]'),'1',$O->backup_auto).' 
'.__('Make backups when changes are made').'</label></p>
<p><label class="classic">'.sprintf(__('Limit backups to %s files per module'),
form::combo(array('settings[backup_limit]'),
	array(5=>5,10=>10,15=>15,20=>20,40=>40,60=>60),$O->backup_limit)).'</label></p>
<p><label class="classic">'.sprintf(__('Store backups in %s'),
form::combo(array('settings[backup_folder]'),
	array_flip($combo_backup_folder),$O->backup_folder)).'</label></p>

'.($O->two_cols ? '</div></div>' : '').'

<p class="clear">
<input type="submit" name="save" value="'.__('save').'" />'.
$core->formNonce().
form::hidden(array('tab'),'setting').
form::hidden(array('action'),'save_setting').
form::hidden(array('p'),'translater').'
</p>
</form>
<p>&nbsp;</p>
</div>';

# Extensions
echo '
<div class="multi-part" id="plugin" title="'.$tabs['plugin'].'">
<table class="clear">
<tr>
<th>'.__('Id').'</th>
<th>'.__('Languages').'</th>
<th>'.__('Name').'</th>
<th class="nowrap">'.__('Version').'</th>
<th class="nowrap">'.__('Details').'</th>
<th class="nowrap">'.__('Author').'</th>
</tr>';

foreach ($O->listModules('plugin') as $name => $plugin) {

	if ($plugin['root_writable']) {
		echo
		'<tr class="line">'.
		'<td class="nowrap">'.
		'<a href="'.$p_url.'&amp;type=plugin&amp;module='.$name.'" title="'.
			__('Translate this plugin').'">'.__($plugin['name']).'</a></td>';
	} else {
		echo 
		'<tr class="line offline">'.
		'<td class="nowrap">'.__($plugin['name']).'</td>';
	}
	echo
	'<td class="nowrap">';
	$langs = $O->listLangs($name);
	$array_langs = array();
	foreach ($langs AS $lang_name => $lang_infos) {
		$array_langs[$lang_name] = 
		'<a href="'.$p_url.'&amp;type=plugin&amp;module='.$name.'&amp;tab='.$lang_name.'">'.
		$lang_name.'</a>';
	}
	echo implode(', ',$array_langs);
	echo
	'</td>'.
	'<td class="nowrap">'.$name.'</td>'.
	'<td class="nowrap">'.$plugin['version'].'</td>'.
	'<td class="maximal">'.html::escapeHTML($plugin['desc']).'</td>'.
	'<td class="nowrap">'.html::escapeHTML($plugin['author']).'</td>'.
	'</tr>';
}
echo '
</table>
<p>&nbsp;</p>
</div>';

# Themes
echo 
'<div class="multi-part" id="theme" title="'.$tabs['theme'].'">
<table class="clear">
<tr>
<th>'.__('Id').'</th>
<th>'.__('Languages').'</th>
<th>'.__('Name').'</th>
<th class="nowrap">'.__('Version').'</th>
<th class="nowrap">'.__('Details').'</th>
<th class="nowrap">'.__('Author').'</th>
</tr>';

foreach ($O->listModules('theme') as $name => $theme) {

	if ($plugin['root_writable']) {
		echo
		'<tr class="line">'.
		'<td class="nowrap">'.
		'<a href="'.$p_url.'&amp;type=theme&amp;module='.$name.'" title="'.
			__('Translate this theme').'">'.__($theme['name']).'</a></td>';
	} else {
		echo 
		'<tr class="line offline">'.
		'<td class="nowrap">'.__($theme['name']).'</td>';
	}
	echo
	'<td class="nowrap">';
	$langs = $O->listLangs($name);
	$array_langs = array();
	foreach ($langs AS $lang_name => $lang_infos) {
		$array_langs[$lang_name] = 
		'<a href="'.$p_url.'&amp;type=theme&amp;module='.$name.'&amp;tab='.$lang_name.'">'.
		$lang_name.'</a>';
	}
	echo implode(', ',$array_langs);
	echo
	'</td>'.
	'<td class="nowrap">'.$name.'</td>'.
	'<td class="nowrap">'.$theme['version'].'</td>'.
	'<td class="maximal">'.html::escapeHTML($theme['desc']).'</td>'.
	'<td class="nowrap">'.html::escapeHTML($theme['author']).'</td>'.
	'</tr>';
}
echo '
</table>
<p>&nbsp;</p>
</div>';

# Import/Export pack

echo '
<div class="multi-part" id="pack" title="'.$tabs['pack'].'">';

# Import
echo '
<h2>'.__('Import').'</h2>
<form method="post" action="plugin.php" enctype="multipart/form-data">
<p>'.__('Choose package to import').'<br />
<input type="file" name="packfile" size="40"/></p>
<p>
<input type="submit" name="save" value="'.__('Import').'" />';
$i=0;
foreach($O->listModules() AS $name => $infos) {
	echo form::hidden(array('modules[]'),$name);$i++;
}
echo 
$core->formNonce().
form::hidden(array('type'),$type).
form::hidden(array('module'),$module).
form::hidden(array('action'),'import_pack').
form::hidden(array('p'),'translater').'
</p>
</form>
<p>&nbsp;</p>';

# Export
echo '
<h2>'.__('Export').'</h2>
<form method="post" action="plugin.php">
<p>'.__('Choose modules to export').'</p>
<table class="clear">
<tr><th colspan="2">'.__('Modules').'</th><th>'.__('Languages').'</th></tr>';
$i=0;
$langs_list = array();

foreach($O->listModules() AS $name => $infos) {

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
foreach($langs_list AS $lang => $name) {
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
form::hidden(array('p'),'translater').'
</p>
</form>
<p>&nbsp;</p>
</div>';

?>