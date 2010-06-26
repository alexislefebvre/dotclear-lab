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

# This file list plugins/themes for translater (called from index.php)

if (!defined('DC_CONTEXT_TRANSLATER') || DC_CONTEXT_TRANSLATER != 'modules'){return;}

echo '
<html>
<head><title>'.__('Translater').' - '.($type == 'theme' ? __('Themes') : __('Extensions')).'</title>'.$header;

# --BEHAVIOR-- translaterAdminHeaders
$core->callBehavior('translaterAdminHeaders');

echo 
'</head>
<body>'.$menu.
'<h3>'.($type == 'theme' ? __('Themes') : __('Plugins')).'</h3>'.
$msg.
'<form id="theme-form" method="post" action="'.$p_url.'">';

$res = '';
foreach ($O->listModules($type) as $name => $nfo)
{
	if ($O->hide_default && in_array($name,dcTranslater::$default_dotclear_modules[$type])) continue;
	
	if ($nfo['root_writable'])
	{
		$res .= 
		'<tr class="line">'.
		'<td class="nowrap">'.
		'<a href="'.$p_url.'&amp;part=module&amp;type='.$type.'&amp;module='.$name.'" title="'.
		($type == 'theme' ? __('Translate this theme') : __('Translate this plugin')).
		'">'.__($nfo['name']).'</a></td>';
	}
	else
	{
		$res .= 
		'<tr class="line offline">'.
		'<td class="nowrap">'.__($nfo['name']).'</td>';
	}
	$res .= 
	'<td class="nowrap">';
	$langs = $O->listLangs($name);
	$array_langs = array();
	foreach ($langs AS $lang_name => $lang_infos)
	{
		$array_langs[$lang_name] = 
		'<a class="wait" href="'.$p_url.'&amp;part=lang&amp;type='.$type.'&amp;module='.$name.'&amp;lang='.$lang_name.'">'.
		$lang_name.'</a>';
	}
	$res .=  implode(', ',$array_langs).
	'</td>'.
	'<td class="nowrap">'.$name.'</td>'.
	'<td class="nowrap">'.$nfo['version'].'</td>'.
	'<td class="maximal">'.html::escapeHTML($nfo['desc']).'</td>'.
	'<td class="nowrap">'.html::escapeHTML($nfo['author']).'</td>'.
	'</tr>';
}
if ($res)
{
	echo '
	<table class="clear">
	<tr>
	<th>'.__('Id').'</th>
	<th>'.__('Languages').'</th>
	<th>'.__('Name').'</th>
	<th class="nowrap">'.__('Version').'</th>
	<th class="nowrap">'.__('Details').'</th>
	<th class="nowrap">'.__('Author').'</th>
	</tr>'.
	$res.
	'</table>';

}
else
{
	echo '<tr><td colspan="6">'.__('There is no editable modules').'</td></tr>';
}
echo '
<p>&nbsp;</p>

</form>';
dcPage::helpBlock('translater');
echo $footer.'</body></html>';
?>