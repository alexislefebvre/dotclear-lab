<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of filesAlias, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$o = new FilesAliases($core);
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
if (isset($_POST['filesalias_url']))
{
	$filesalias_url = empty($_POST['filesalias_url']) ? md5(uniqid(rand(), true)) : $_POST['filesalias_url'];
	
	try {
		$o->createAlias($filesalias_url,$_POST['filesalias_destination'],count($aliases)+1,$_POST['filesalias_disposable']);
		http::redirect($p_url.'&created=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# New prefix
if (isset($_POST['filesalias_prefix']))
{
	try {
		if (empty($_POST['filesalias_prefix'])) {
			throw new Exception(__('No prefix for your files aliases.'));
		}
		$core->blog->settings->setNameSpace('filesalias');
		$core->blog->settings->put('filesalias_prefix',$_POST['filesalias_prefix'],'string','Medias alias URL prefix');
		$core->blog->triggerBlog();
		http::redirect($p_url.'&modified=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
	<title><?php echo __('Aliases for files'); ?></title>
</head>

<body>
<?php
echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Aliases for files').'</h2>';
?>
<?php
if (!empty($_GET['up'])) {
	echo '<p class="message">'.__('Aliases successfully updated.').'</p>';
}

if (!empty($_GET['created'])) {
	echo '<p class="message">'.__('Alias for this media created.').'</p>';
}

if (!empty($_GET['modified'])) {
	echo '<p class="message">'.__('Configuration successfully updated.').'</p>';
}

if (empty($aliases))
{
	echo '<p>'.__('No alias').'</p>';
}
else
{
	echo
	'<form action="'.$p_url.'" method="post">'.
	'<fieldset>'.
	'<legend>'.__('Aliases list').'</legend>'.
	'<table class="maximal"><tr>'.
	'<th>'.__('alias').'</th>'.
	'<th>'.__('destination').'</th>'.
	'<th>'.'<img alt="'.__('disposable').'" title="'.__('disposable?').'" src="index.php?pf=filesAlias/img/trash.png" />'.'</th>'.	
	//'<td>'.__('Alias position').'</td>'.
	'</tr>';
	
	foreach ($aliases as $k => $v)
	{
		if($v['filesalias_disposable']) {
			$line = 'offline';
			$status = '<img alt="'.__('disposable').'" title="'.__('disposable').'" src="index.php?pf=filesAlias/img/bomb.png" />';
		}
		else
		{
			$line = '';
			$status = '<img alt="'.__('not disposable').'" title="'.__('not disposable').'" src="index.php?pf=filesAlias/img/default.png" />';
		}
		
		$link = '<a href="'.$core->blog->url.$core->url->getBase('filesalias').'/'.html::escapeHTML($v['filesalias_url']).'">'.
				'<img alt="'.__('Direct link').'" title="'.__('Direct link').'" src="index.php?pf=filesAlias/img/link.png" /></a>';
	
		echo
		'<tr class='.$line.'>'.
		'<td class="status nowrap">'.$status.form::field(array('a['.$k.'][filesalias_url]'),48,255,html::escapeHTML($v['filesalias_url']),'','','','style="margin-left:10px;"').'&nbsp;&bull;&nbsp;'.$link.'&nbsp;</td>'.
		'<td class=" ">'.form::field(array('a['.$k.'][filesalias_destination]'),70,255,html::escapeHTML($v['filesalias_destination']),'maximal').'</td>'.
		'<td class="status nowrap">'.form::checkbox(array('a['.$k.'][filesalias_disposable]'),1,$v['filesalias_disposable']).'</td>'.
		'</tr>';
	}
	
	echo '</table>'.
	'<p class="form-note">'.__('To remove a link, empty its alias or destination.').'</p>'.
	'<p>'.$core->formNonce().
	'<input type="submit" value="'.__('Update').'" /></p>'.
		'</fieldset>'.
	'</form>';
}

echo
'<form action="'.$p_url.'" method="post">'.
'<fieldset>'.
'<legend>'.__('New alias').'</legend>'.
'<p class="field"><label class="required">'.__('Destination:').' '.form::field('filesalias_destination',50,255).'</label></p>'.
'<p class="field"><label>'.__('Choose URL:').' '.form::field('filesalias_url',50,255).'</label></p><br />'.
'<p class="form-note">'.__('Leave empty for an auto-generated URL.').'</p>'.
'<p class="field"><label>'.__('Disposable:').' '.form::checkbox('filesalias_disposable',1).'</label></p>'.
'<p>'.$core->formNonce().'<input type="submit" value="'.__('Save').'" /></p>'.
'</fieldset>'.
'</form>';

echo
'<form action="'.$p_url.'" method="post">'.
'<fieldset>'.
'<legend>'.__('Prefix of Aliases URLs').'</legend>'.
'<p>'.__('Base URL scheme:').'&nbsp;&mdash;&nbsp;'.$core->blog->url.'<span style="color : #069">'.$core->url->getBase('filesalias').'</span></p>'.
'<p><label class="required">'
.__('Media prefix URL:').' '.form::field('filesalias_prefix',20,255,$core->blog->settings->filesalias_prefix).'</label></p>'.
'<p>'.$core->formNonce().'<input type="submit" value="'.__('Save').'" /></p>'.
'</fieldset>'.
'</form>';

dcPage::helpBlock('filesAlias');
?>
</body>
</html>
