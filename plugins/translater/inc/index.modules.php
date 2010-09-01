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
<body>'.sprintf($menu,' &rsaquo; '.($type == 'theme' ? __('Themes') : __('Plugins')).
($type == 'theme' ?
	' - <a class="button" href="'.$p_url.'&amp;part=modules&amp;type=plugin">'.__('Plugins').'</a>' :
	' - <a class="button" href="'.$p_url.'&amp;part=modules&amp;type=theme">'.__('Themes').'</a>'
).' - <a class="button" href="'.$p_url.'&amp;part=pack">'.__('Import/Export').'</a>'
).$msg.
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
		sprintf(
			($type == 'theme' ? 
				html::escapeHTML(__('Translate theme "%s" (by %s)')) : 
				html::escapeHTML(__('Translate plugin "%s" (by %s)'))
			),
			html::escapeHTML(__($nfo['name'])),html::escapeHTML($nfo['author'])
		).
		'">'.$name.'</a></td>';
	}
	else
	{
		$res .= 
		'<tr class="line offline">'.
		'<td class="nowrap">'.$name.'</td>';
	}
	$res .= 
	'<td class="nowrap">'.$nfo['version'].'</td>'.
	'<td class="nowrap">';
	$langs = $O->listLangs($name);
	$array_langs = array();
	foreach ($langs AS $lang_name => $lang_infos)
	{
		$array_langs[$lang_name] = 
		'<a class="wait maximal nowrap" title="'.__('Edit translation').'" href="'.$p_url.'&amp;part=lang&amp;type='.$type.'&amp;module='.$name.'&amp;lang='.$lang_name.'">'.
		$lang_name.'</a>';
	}
	$res .=  implode(', ',$array_langs).
	'</td>'.
	'</tr>';
}
if ($res)
{
	echo '
	<table class="clear">
	<tr>
	<th>'.__('Id').'</th>
	<th class="nowrap">'.__('Version').'</th>
	<th class="nowrap maximal">'.__('Languages').'</th>
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