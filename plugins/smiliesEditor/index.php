<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of smiliesEditor, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }



// Init 
$smg_writable =  false;
$combo_action[__('Definition')] = array(
__('update smilies set') => 'update',
__('save smilies order') => 'saveorder',
__('delete smilies definition') => 'delete'
);

$combo_action[__('Toolbar')] = array(
__('display in smilies bar') => 'display',
__('hide in smilies bar') => 'hide',
);

if (is_null($core->blog->settings->smilies_bar_flag)) {
	try {
		$core->blog->settings->setNameSpace('smilieseditor');

		// Smilies bar is not displayed by default
		$core->blog->settings->put('smilies_bar_flag',false,'boolean','Show smilies toolbar');
		$core->blog->settings->put('smilies_toolbar','','string','Smilies displayed in toolbar');
		
		$core->blog->settings->setNameSpace('system');
		
		$core->blog->triggerBlog();
	}
	catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$smilies_bar_flag = (boolean)$core->blog->settings->smilies_bar_flag;

// Get theme Infos
$core->themes = new dcThemes($core);
$core->themes->loadModules($core->blog->themes_path,null);
$T = $core->themes->getModules($core->blog->settings->theme);

// Get smilies code
$o = new smiliesEditor($core);
$smilies = $o->getSmilies();

// Try to create the subdirectory smilies
if (!empty($_POST['create_dir']) )
{
	try {
		$o->createDir();
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
		
	if (!$core->error->flag()) {
		http::redirect($p_url.'&creadir=1');
	}
}

// Init the filemanager
try 
{
	$smilies_files = $o->getFiles();
	$smg_writable = $o->filemanager->writable();
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}

if (!empty($_POST['saveconfig']))
{
	try
	{
		$core->blog->settings->setNameSpace('smilieseditor');

		$show = (empty($_POST['smilies_bar_flag']))?false:true;

		$core->blog->settings->put('smilies_bar_flag',$show,'boolean');
		
		$core->blog->triggerBlog();
		http::redirect($p_url.'&config=1');

	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

// Create array of used smilies filename
$smileys_list = array();
foreach ($smilies as $k => $v) {
	$smileys_list= array_merge($smileys_list, array($v['name']=> $v['name']));
}

// Delete all unused images
if (!empty($_POST['rm_unused_img']) )
{
	if (!empty($o->images_list))
	{
		foreach ($o->images_list as $k => $v) 
		{ 
			if (!array_key_exists($v['name'],$smileys_list))
			{ 
				try {
					$o->filemanager->removeItem($v['name']);
				} catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			}
		}
	}
			
	if (!$core->error->flag()) {
		http::redirect($p_url.'&dircleaned=1');
	}
}

if (!empty($_FILES['upfile']))
{
	try
	{
		files::uploadStatus($_FILES['upfile']);
		//$f_name = (isset($_POST['upsmiletitle']) ? $_POST['upsmiletitle'] : '');
		$file = $o->uploadSmile($_FILES['upfile']['tmp_name'],$_FILES['upfile']['name']);
		
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
				
	if (!$core->error->flag()) {
		http::redirect($p_url.'&upok='.$file);
	}
}

// Create the combo of all images available in directory
if (!empty($o->images_list))
{
	$smileys_combo = array();
	foreach ($o->images_list as $k => $v) {
		$smileys_combo= array_merge($smileys_combo, array($v['name']=> $v['name']));
	}
}

$order = array();
if (empty($_POST['smilies_order']) && !empty($_POST['order'])) {
	$order = $_POST['order'];
	asort($order);
	$order = array_keys($order);
} elseif (!empty($_POST['smilies_order'])) {
	$order = explode(',',$_POST['smilies_order']);
}

if (!empty($_POST['actionsmilies']))
{
	$action = $_POST['actionsmilies'];
	
	if($action == 'delete' && !empty($_POST['select']))
	{
		foreach ($_POST['select'] as $k => $v)
		{
			unset ($smilies[$v]);
			
			try {
				$o->setSmilies($smilies);
				$o->setConfig($new_smilies);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
		
		if (!$core->error->flag()) {
			http::redirect($p_url.'&remove=1');
		}
	} 

	elseif($action == 'update' && !empty($_POST['select']))
	{
		foreach ($_POST['select'] as $k => $v)
		{
			$new_smilies = $smilies;
			$new_smilies[$v]['code'] = isset($_POST['code'][$v]) ? preg_replace('/[\s]+/','',$_POST['code'][$v]) : $smilies[$v]['code'] ;
			$new_smilies[$v]['name'] = isset($_POST['name'][$v]) ? $_POST['name'][$v] : $smilies[$v]['name'];
			
			try {
				$o->setSmilies($new_smilies);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
		
		if (!$core->error->flag()) {
			http::redirect($p_url.'&update=1');
		}
		
	} 
	
	elseif($action == 'saveorder' && !empty($order))
	{
		foreach ($order as $k => $v)
		{ 
			$new_smilies[$v] = $smilies[$v]; 
		}
		
		try {
			$o->setSmilies($new_smilies);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
			break;
		}
		
		if (!$core->error->flag()) {
			http::redirect($p_url.'&neworder=1');
		}
		
	} 
	
	elseif($action == 'display' && !empty($_POST['select']))
	{
		foreach ($_POST['select'] as $k => $v)
		{ 
			$smilies[$v]['onSmilebar'] = true;
		}
		
		try {
			$o->setConfig($smilies);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
			break;
		}
		
		if (!$core->error->flag()) {
			http::redirect($p_url.'&display=1');
		}
		
	} 
	
	elseif($action == 'hide' && !empty($_POST['select']))
	{
		foreach ($_POST['select'] as $k => $v)
		{  
			$smilies[$v]['onSmilebar'] = false;
		}
		
		try {
			$o->setConfig($smilies);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
			break;
		}
		
		if (!$core->error->flag()) {
			http::redirect($p_url.'&hide=1');
		}
		
	} 
}

if (!empty($_POST['smilecode']) && !empty($_POST['smilepic']))
{
	$count = count($smilies);
	$smilies[$count]['code'] = preg_replace('/[\s]+/','',$_POST['smilecode']);
	$smilies[$count]['name'] = $_POST['smilepic'];

	try {
		$o->setSmilies($smilies);
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
		
	if (!$core->error->flag()) {
		http::redirect($p_url.'&addsmile=1');
	}
}
?>
<html>
<head>
	<title><?php echo __('Smilies Editor'); ?></title>
	<?php echo dcPage::jsToolMan(); ?>
	<?php echo dcPage::jsLoad('index.php?pf=smiliesEditor/js/_smilies.js'); ?>
	
	  <script type="text/javascript">
	  //<![CDATA[
	  dotclear.msg.confirm_image_delete = '<?php echo html::escapeJS(sprintf(__('Are you sure you want to remove these %s ?'),'images')) ?>';
	  $(function() {
	    $('#del_form').submit(function() {
	      return window.confirm(dotclear.msg.confirm_image_delete);
	    });
	  });
	  //]]>
	  </script>

	
	<style type="text/css">
		option[selected=selected] {color:#c00;}
		a.add {background:inherit url(images/plus.png) top left;}
	</style>
</head>

<body>

<?php

if ($core->blog->settings->theme == 'default') {
	echo '<p class="error">'.__("You can't edit default theme.").'</p>';
}
	
if (!empty($o->images_list))
{
	$images_all = $o->images_list;
	foreach ($o->images_list as $k => $v) 
	{ 
		if (array_key_exists($v['name'],$smileys_list))
			{ 
				unset ($o->images_list[$k]);
			}
	}
}

if (!empty($_GET['config'])) {
	echo '<p class="message">'.__('Configuration successfully updated.').'</p>';
}

if (!empty($_GET['creadir'])) {
	echo '<p class="message">'.__('The subfolder has been successfully created').'</p>';
}

if (!empty($_GET['dircleaned'])) {
		echo '<p class="message">'.__('Unused images have been successfully removed.').'</p>';
}

if (!empty($_GET['upok'])) {
		echo '<p class="message">'. sprintf(__('The image <em>%s</em> has been successfully uploaded.'),$_GET['upok']).'</p>';
}

if (!empty($_GET['remove'])) {
		echo '<p class="message">'.__('Smilies has been successfully removed.').'</p>';
}

if (!empty($_GET['update'])) {
		echo '<p class="message">'.__('Smilies has been successfully updated.').'</p>';
}

if (!empty($_GET['neworder'])) {
		echo '<p class="message">'.__('Order of smilies has been successfully changed.').'</p>';
}

if (!empty($_GET['hide'])) {
		echo '<p class="message">'.__('These selected smilies are now hidden on toolbar.').'</p>';
}

if (!empty($_GET['display'])) {
		echo '<p class="message">'.__('These selected smilies are now displayed on toolbar').'</p>';
}

if (!empty($_GET['addsmile'])) {
		echo '<p class="message">'.__('A new smiley has been successfully created').'</p>';
}

echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; <a href="blog_theme.php">'.__('Blog aspect').'</a> &rsaquo; '.__('Smilies Editor').'</h2>';

echo
'<p><strong>'.sprintf(__('Your current theme on this blog is "%s".'),html::escapeHTML($T['name'])).'</strong></p>';

if (empty($smilies))
{
	echo '<p>'.__('No defined smiley').'</p>';
}
else
{
	echo
		'<h3>'.__('Smilies set').'</h3>'.
		'<form action="'.$p_url.'" method="post" id="links-form">'.
		'<table class="maximal dragable">'.
		'<thead>'.
		'<tr>'.
		'<th colspan="3">'.__('Code').'</th>'.
		'<th>'.__('Filename').'</th>'.
		'<th>'.__('Image').'</th>'.
		'<th>'.__('Displayed in toolbar').'</th>'.
		'</tr>'.
		'</thead>'.
	
	'<tbody id="smilies-list">';
	foreach ($smilies as $k => $v)
	{
		if($v['onSmilebar']) {
			$line = '';
			$status = '<img alt="'.__('displayed').'" title="'.__('displayed').'" src="images/check-on.png" />';
		}
		else
		{
			$line = 'offline';
			$status = '<img alt="'.__('undisplayed').'" title="'.__('undisplayed').'" src="images/check-off.png" />';
		}

		echo
		'<tr class="line '.$line.'" id="l_'.($k).'">'.
		'<td class="handle minimal">'.form::field(array('order['.$k.']'),2,5,$k).'</td>'.
		'<td class="minimal">'.form::checkbox(array('select[]'),$k).'</td>'.
		'<td class="minimal">'.form::field(array('code[]','c'.$k),10,255,html::escapeHTML($v['code'])).'</td>'.
		'<td class="nowrap">'.form::combo(array('name[]','n'.$k),$smileys_list,$v['name']).'</td>'.
		'<td class="nowrap status"><img src="'.$o->smilies_base_url.$v['name'].'" alt="'.$v['code'].'" /></td>'.
		'<td class="nowrap status">'.$status.'</td>'.
		'</tr>';
	}
	
	
	echo '</tbody></table>';

	echo '<div class="two-cols">'.
		'<p class="col checkboxes-helpers"></p>';

	echo	'<p class="col right">'.__('Selected smilies action:').' '.
		form::hidden('smilies_order','').
		form::hidden(array('p'),'smiliesEditor').
		form::combo('actionsmilies',$combo_action).
		$core->formNonce().
		'<input type="submit" value="'.__('ok').'" /></p></div></form>';
}

?>

<?php 
if (empty($images_all))
{
	echo '<p>'.__('No smiley available').'</p>';
	
	if (empty($o->filemanager))
	{
		echo	'<div><form action="'.$p_url.'" method="post" id="dir_form"><p>'.form::hidden(array('p'),'smiliesEditor').
		$core->formNonce().
		'<input type="submit" name="create_dir" value="'.__('create smilies directory').'" /></p></form></div>';
	}
}
else
{
	echo
		'<div class="three-cols">'.
		'<div class="col">'.
		'<form action="'.$p_url.'" method="post" id="add-smiley-form">'.
		'<fieldset>'.
		'<legend>'.__('Create a smiley').'</legend>'.
		'<p><label class="classic required" title="'.__('Required field').'">'.__('Code:').' '.
		form::field('smilecode',10,255).'</label></p>'.

		'<p><label class="classic required" title="'.__('Required field').'">'.__('Image:').' '.
		form::combo('smilepic',$smileys_combo).'</label></p>'.

		'<p>'.form::hidden(array('p'),'smiliesEditor').
		$core->formNonce().
		'<input type="submit" name="add_message" value="'.__('Create').'" /></p>'.
		'</fieldset>'.
		'</form></div>';
		
	if (!empty($o->images_list))
	{
		echo '<div class="col"><form action="'.$p_url.'" method="post" id="del_form">'.
		'<fieldset><legend>'.__('Unset Smilies').'</legend>'.
			'<p>'.__('Here you have displayed the unset smilies. Pass your mouse over the image to get the filename.').'</p>';
		
		echo '<p>';
		foreach ($o->images_list as $k => $v)
		{
			echo	'<img src="'.$v['url'].'" alt="'.$v['name'].'" title="'.$v['name'].'" />';
		}
		echo '</p>';
		
		echo	
		'<p>'.form::hidden(array('p'),'smiliesEditor').
		$core->formNonce().
		'<input type="submit" name="rm_unused_img" 
		value="'.__('Delete all unused images').'" 
		/></p></fieldset></form></div>';
	}
	

	
}

if ($smg_writable)
{
	echo
	'<div id="upl-smile" class="col">'.
	'<form id="upl-smile-form" action="'.html::escapeURL($p_url).'" method="post" enctype="multipart/form-data">'.
	'<fieldset id="add-file-f">'.
	'<legend>'.__('Add files').'</legend>'.
	'<p>'.form::hidden(array('MAX_FILE_SIZE'),DC_MAX_UPLOAD_SIZE).
	$core->formNonce().
	'<label>'.__('Choose a file:').
	' ('.sprintf(__('Maximum size %s'),files::size(DC_MAX_UPLOAD_SIZE)).')'.
	'<input type="file" name="upfile" size="20" />'.
	'</label></p>'.
	'<p><input type="submit" value="'.__('send').'" />'.
	form::hidden(array('d'),null).'</p>'.
	'<p class="form-note">'.__('Please take care to publish media that you own and that are not protected by copyright.').'</p>'.
	'</fieldset>'.
	'</form>'.
	'</div>';
}

	echo '</div>';

if (!empty($smilies))
{
	echo
	'<div class="clear" id="smilies_options">'.
	'<form action="plugin.php" method="post" id="form_tribune_options">'.
		'<fieldset>'.
			'<legend>'.__('Smilies configuration').'</legend>'.
				'<div>'.
					'<p class="field">'.
						form::checkbox('smilies_bar_flag', 1, $smilies_bar_flag).
						'<label class=" classic" for="smilies_bar_flag">'.__('Show toolbar smilies').'</label>'.
					'</p>'.
					'<p class="form-note">'.
						sprintf(__('Don\'t forget to <a href="%s">display smilies</a> on your blog configuration.'),'blog_pref.php').
					'</p>'.
					'<p>'.
						form::hidden(array('p'),'smiliesEditor').
						$core->formNonce().
						'<input type="submit" name="saveconfig" value="'.__('Save configuration').'" />'.
					'</p>'.
				'</div>'.

		'</fieldset>'.
	'</form></div>';
}
?>

<?php dcPage::helpBlock('pouet');?>
</body>
</html>