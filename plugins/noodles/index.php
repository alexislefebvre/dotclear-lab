<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of noodles, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

dcPage::check('admin');

$s = $core->blog->settings->noodles;
$msg = isset($_POST['done']) ? __('Configuration successfully updated') : '';
$tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'blocs';
$img_green = '<img alt="%s" src="images/check-on.png" />';
$img_red = '<img alt="%s" src="images/check-off.png" />';

include dirname(__FILE__).'/inc/_default_noodles.php';

$__noodles = noodles::decode($s->noodles_object);

if ($__noodles->isEmpty())
{
	$__noodles = $__default_noodles;
}
else
{
	$default_noodles_array = $__default_noodles->noodles();
	foreach($default_noodles_array AS $id => $noodle)
	{
		if ($__noodles->exists($id)) continue;
		$__noodles->{$id} = $noodle;
	}
}

if (!$s->noodles_active)
{
	$tab = 'settings';
}

$default_avatars_images = files::scandir(dirname(__FILE__).'/default-templates/img/');
$avatar_paths = noodlesLibImagePath::getArray($core,'noodles');

$combo_active = array(
	__('no') => 0,
	__('yes') => 1
);
$combo_place = array(
	__('Begin') => 'prepend',
	__('End') => 'append',
	__('Before') => 'before',
	__('After') => 'after'
);
$combo_rating = array(
	'G'=>'g',
	'PG'=>'pg',
	'R'=>'r',
	'X'=>'x'
);
$combo_size = array(
	'16px'=>16,
	'24px'=>24,
	'32px'=>32,
	'48px'=>48,
	'56px'=>56,
	'64px'=>64,
	'92px'=>92,
	'128px'=>128,
	'256px'=>256
);

if (!empty($_POST['save']) && $tab == 'settings')
{
	try
	{
		$s->put('noodles_active',$_POST['noodles_active'],'boolean');

		# Destination image according to noodlesLibImagePath()
		$dest_file = DC_ROOT.'/'.$s->public_path.'/noodles-default-image.png';

		# user upload image
		if ($_POST['noodles_image'] == 'user')
		{
			if (2 == $_FILES['noodlesuserfile']['error'])
			{
				throw new Exception(__('Maximum file size exceeded'));
			}
			if ($_FILES['noodlesuserfile']['type'] != 'image/x-png')
			{
				throw new Exception(__('Image must be in png format'));
			}
			if (0 != $_FILES['noodlesuserfile']['error'])
			{
				throw new Exception(__('Something went wrong while download file'));
			}
			if ($_FILES['noodlesuserfile']['type'] != 'image/x-png')
			{
				throw new Exception(__('Image must be in png format'));
			}
			if (move_uploaded_file($_FILES['noodlesuserfile']['tmp_name'],$dest_file))
			{
				$s->put('noodles_image',1,'boolean');
			}
		}
		# Default gravatar.com avatar
		elseif ($_POST['noodles_image'] == 'gravatar.com')
		{
			$s->put('noodles_image',0,'boolean');
		}
		# existsing noodles image on blog
		elseif ($_POST['noodles_image'] == 'existsing')
		{
			$s->put('noodles_image',1,'boolean');
		}
		# noodles image
		elseif (preg_match('/^gravatar-[0-9]+.png$/',$_POST['noodles_image']))
		{
			$source = dirname(__FILE__).'/default-templates/img/'.$_POST['noodles_image'];
			
			if (!file_exists($source))
			{
				throw new Exception(__('Something went wrong while search file'));
			}
			if (file_put_contents($dest_file,file_get_contents($source)))
			{
				$s->put('noodles_image',1,'boolean');
			}
		}
		
		$core->blog->triggerBlog();
		http::redirect('plugin.php?p=noodles&tab=settings&done=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

if (!empty($_POST['save']) && $tab == 'blocs' && !empty($_POST['noodle']))
{
	try
	{
		foreach($_POST['noodle'] as $id => $bloc)
		{
			foreach($bloc as $k => $v)
			{
				$__noodles->{$id}->set($k,$v);
			}
		}
		$s->put('noodles_object',$__noodles->encode(),'string');
		
		$core->blog->triggerBlog();
		http::redirect('plugin.php?p=noodles&tab=blocs&done=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

if (!empty($_POST['reset']) && $tab == 'blocs')
{
	try
	{
		$s->put('noodles_object','','string');
		$core->blog->triggerBlog();
		http::redirect('plugin.php?p=noodles&tab=blocs&done=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

echo
'<html>'.
'<head>'.
'<title>'.__('Noodles').'</title>'.
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsPageTabs($tab).
'</head>'.
'<body>'.
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Noodles').'</h2>'.
 (!empty($msg) ? '<p class="message">'.$msg.'</p>' : '');

# Blocs
if ($s->noodles_active)
{
	echo 
	'<div class="multi-part" id="blocs" title="'.__('Controls').'">'.
	'<form method="post" action="plugin.php">'.
	'<table><thead><tr>'.
	'<th class="nowrap">'.__('Name').'</th>'.
	'<th class="nowrap">'.__('Enable').'</th>'.
	'<th class="nowrap">'.__('Size').'</th>'.
	'<th class="nowrap">'.__('Rating').'</th>'.
	'<th class="nowrap">'.__('PHP').'</th>'.
	'<th class="nowrap">'.__('JS').'</th>'.
	'<th class="nowrap">'.__('JS target').'</th>'.
	'<th class="nowrap">'.__('JS place').'</th>'.
	'<th class="nowrap">'.__('Adjust avatar CSS').'</th>'.
	'</tr></thead>';

	foreach($__noodles->noodles() as $noodle)
	{
		echo
		'<tr class="line">'.
		'<td class="nowrap">'.$noodle->name().'</td>'.
		'<td>'.form::combo(array('noodle['.$noodle->id().'][active]'),$combo_active,$noodle->active).'</td>'.
		'<td>'.form::combo(array('noodle['.$noodle->id().'][size]'),$combo_size,$noodle->size).'</td>'.
		'<td>'.form::combo(array('noodle['.$noodle->id().'][rating]'),$combo_rating,$noodle->rating).'</td>'.
		'<td>'.($noodle->hasPhpCallback() ? $img_green : $img_red).'</td>'.
		'<td>'.$img_green.'</td>'.
		'<td>'.form::field(array('noodle['.$noodle->id().'][target]'),20,255,$noodle->target).'</td>'.
		'<td>'.form::combo(array('noodle['.$noodle->id().'][place]'),$combo_place,$noodle->place).'</td>'.
		'<td class="maximal">'.
		form::textArea(array('noodle['.$noodle->id().'][css]'),50,2,$noodle->css).
		' .noodles-'.$noodle->id().'{}</td>'.
		'</tr>';
	}
	echo 
	'</table>'.
	'<p>'.
	form::hidden(array('p'),'noodles').
	form::hidden(array('tab'),'blocs').
	$core->formNonce().
	'<input type="submit" name="save" value="'.__('Save').'" /> '.
	'<input type="submit" name="reset" value="'.__('Reset').'" /></p>'.
	'</form>'.
	'</div>';
}

# Settings
echo 
'<div class="multi-part" id="settings" title="'.__('Settings').'">'.
'<form method="post" action="plugin.php" enctype="multipart/form-data">'.
'<fieldset id="settings-plugin"><legend>'.__('Options').'</legend>'.
'<p class="field"><label>'.
form::combo(array('noodles_active'),$combo_active,$s->noodles_active).' '.
	__('Enable plugin').'</label></p>'.

'</fieldset>'.
'<fieldset id="setting-avatar"><legend>'.__('Default avatar').'</legend>'.
'<table><tr><th>&nbsp;</th><th>'.__('Avatar').'</th><th>'.__('size').'</th></tr>'.
'<tr class="line">';

// By default use gravatar.com image
$default = '' == $s->noodles_image;

echo 
'<td colspan="2"><label class="classic">'.
form::radio(array('noodles_image'),'gravatar.com',$default).
__('gravatar.com default image').'</label></td>'.
'<td></td>'.
'</tr>';

if (!$default)
{
	$exists = false;
	
	// then use theme image
	if (file_exists($avatar_paths['theme']['dir']))
	{
		$exists = $avatar_paths['theme'];
	}
	// then public image
	elseif (file_exists($avatar_paths['public']['dir']))
	{
		$exists = $avatar_paths['public'];
	}
	// then module
	elseif (file_exists($avatar_paths['module']['dir']))
	{
		$exists = $avatar_paths['module'];
	}
	if ($exists)
	{
		$sz = getimagesize($exists['dir']);
		$sz[2] = files::size(filesize($exists['dir']));
		
		echo 
		'<tr class="line">'.
		'<td><label class="classic">'.form::radio(array('noodles_image'),'existing',1).
		basename($exists['dir']).'</label></td>'.
		'<td><img src="'.$exists['url'].'" alt="" /></td>'.
		'<td>'.$sz[0].'x'.$sz[1].'<br />'.$sz[2].'</td>'.
		'</tr>';
	}
}

// noodles avatars
sort($default_avatars_images);
foreach($default_avatars_images AS $f)
{
	if (!preg_match('/gravatar-[0-9]+.png/',$f)) continue;
	$sz = getimagesize(dirname(__FILE__).'/default-templates/img/'.$f);
	$sz[2] = files::size(filesize(dirname(__FILE__).'/default-templates/img/'.$f));
	
	echo 
	'<tr class="line">'.
	'<td><label class="classic">'.form::radio(array('noodles_image'),$f).
	basename($f).'</label></td>'.
	'<td><img src="index.php?pf=noodles/default-templates/img/'.$f.'" alt="" /></td>'.
	'<td>'.$sz[0].'x'.$sz[1].'<br />'.$sz[2].'</td>'.
	'</tr>';
}

// user upload avatar
echo 
'<tr class="line">'.
'<td>'.form::radio(array('noodles_image'),'user').'</td>'.
'<td colspan="2">'.form::hidden(array('MAX_FILE_SIZE'),30000).
	'<input type="file" name="noodlesuserfile" /> *</td>'.
'</tr>'.
'</table>'.
'<p class="form-note">* '.__('Image must be in "png" format and have a maximum file size of 30Ko').'</p>'.
'</fieldset>'.
'<p>'.
form::hidden(array('p'),'noodles').
form::hidden(array('tab'),'settings').
$core->formNonce().
'<input type="submit" name="save" value="'.__('Save').'" /></p>'.
'</form>'.
'</div>';

# Footer
echo 
'<hr class="clear"/>
<p class="right">
noodles - '.$core->plugins->moduleInfo('noodles','version').'&nbsp;
<img alt="'.__('Noodles').'" src="index.php?pf=noodles/icon.png" />
</p>
</body></html>';
?>