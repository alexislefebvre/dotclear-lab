<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$o = new dcAliases($core);
$aliases = $o->getAliases();

# Update aliases
if (isset($_POST['a']) && is_array($_POST['a']))
{
	try {
		$o->updateAliases($_POST['a']);
		http::redirect($p_url.'&up=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# New alias
if (isset($_POST['alias_url']))
{
	try {
		$o->createAlias($_POST['alias_url'],$_POST['alias_destination'],count($aliases)+1);
		http::redirect($p_url.'&created=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo __('Aliases'); ?></title>
</head>

<body>
<?php
echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Aliases').'</h2>'.
'<h3>'.__('Aliases list').'</h3>';

if (empty($aliases))
{
	echo '<p>'.__('No alias').'</p>';
}
else
{
	echo
	'<form action="'.$p_url.'" method="post">'.
	'<table><tr>'.
	'<td>'.__('Alias URL').'</td>'.
	'<td>'.__('Alias destination').'</td>'.
	'<td>'.__('Alias position').'</td>'.
	'</tr>';
	
	foreach ($aliases as $k => $v)
	{
		echo
		'<tr>'.
		'<td>'.form::field(array('a['.$k.'][alias_url]'),30,255,html::escapeHTML($v['alias_url'])).'</td>'.
		'<td>'.form::field(array('a['.$k.'][alias_destination]'),50,255,html::escapeHTML($v['alias_destination'])).'</td>'.
		'<td>'.form::field(array('a['.$k.'][alias_position]'),3,5,html::escapeHTML($v['alias_position'])).'</td>'.
		'</tr>';
	}
	
	echo '</table>'.
	'<p>'.__('To remove an alias, empty its URL or destination.').'</p>'.
	'<p>'.$core->formNonce().
	'<input type="submit" value="'.__('Update').'" /></p>'.
	'</form>';
}

echo
'<h3>'.__('New alias').'</h3>'.
'<form action="'.$p_url.'" method="post">'.
'<p class="field"><label>'.__('Alias URL:').' '.form::field('alias_url',50,255).'</label></p>'.
'<p class="field"><label>'.__('Alias destination:').' '.form::field('alias_destination',50,255).'</label></p>'.
'<p>'.$core->formNonce().'<input type="submit" value="'.__('Save').'" /></p>'.
'</form>';

dcPage::helpBlock('alias');
?>
</body>
</html>
