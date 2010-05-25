<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of smiliesEditor, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (!version_compare(DC_VERSION,'2.2-alpha','>=')) { 
	$core->blog->settings->setNamespace('smilieseditor'); 
	$s =& $core->blog->settings;
	$theme = $core->blog->settings->theme;
} else { 
	$core->blog->settings->addNamespace('smilieseditor'); 
	$s =& $core->blog->settings->smilieseditor;
	$theme = $core->blog->settings->system->theme;
}

// Init 
$smg_writable =  false;
if ($core->auth->isSuperAdmin() && $theme !='default')
{
	$combo_action[__('Definition')] = array(
	__('update smilies set') => 'update',
	__('delete smilies definition') => 'delete'
	);
}

$combo_action[__('Toolbar')] = array(
__('display in smilies bar') => 'display',
__('hide in smilies bar') => 'hide',
);

$smilies_bar_flag = (boolean)$s->smilies_bar_flag;
$smilies_preview_flag = (boolean)$s->smilies_preview_flag;

// Get theme Infos
$core->themes = new dcThemes($core);
$core->themes->loadModules($core->blog->themes_path,null);
$T = $core->themes->getModules($theme);

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

		$show = (empty($_POST['smilies_bar_flag']))?false:true;
		$preview = (empty($_POST['smilies_preview_flag']))?false:true;

		$s->put('smilies_bar_flag',$show,'boolean');
		$s->put('smilies_preview_flag',$preview,'boolean');
		
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
		$file = $o->uploadSmile($_FILES['upfile']['tmp_name'],$_FILES['upfile']['name']);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	
	if (!$core->error->flag()) {
		if ($file) {
			http::redirect($p_url.'&upok='.$file);
		}
		else {
			http::redirect($p_url.'&upzipok=1');
		}
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
				$o->setConfig($smilies);
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
			$smilies[$v]['code'] = isset($_POST['code'][$v]) ? preg_replace('/[\s]+/','',$_POST['code'][$v]) : $smilies[$v]['code'] ;
			$smilies[$v]['name'] = isset($_POST['name'][$v]) ? $_POST['name'][$v] : $smilies[$v]['name'];
			
			try {
				$o->setSmilies($smilies);
				$o->setConfig($smilies);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
				break;
			}
		}
		
		if (!$core->error->flag()) {
			http::redirect($p_url.'&update=1');
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

if (!empty($_POST['saveorder']) && !empty($order))
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

# Zip download
if (!empty($_GET['zipdl']))
{
	try
	{
		@set_time_limit(300);
		$fp = fopen('php://output','wb');
		$zip = new fileZip($fp);
		//$zip->addExclusion('#(^|/).(.*?)_(m|s|sq|t).jpg$#');
		$zip->addDirectory($core->themes->moduleInfo($theme,'root').'/smilies','',true);
		header('Content-Disposition: attachment;filename=smilies-'.$theme.'.zip');
		header('Content-Type: application/x-zip');
		$zip->write();
		unset($zip);
		exit;
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
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
	  <?php echo dcPage::jsVar('dotclear.smilies_base_url',$o->smilies_base_url);?>
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
		img.smiley {vertical-align : middle;}
		tr.offline {background-color : #f4f4ef;}
		tr td.smiley { text-align:center}
	</style>
</head>

<body>

<?php
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

echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Smilies Editor').'</h2>';

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

if (!empty($_GET['upzipok'])) {
		echo '<p class="message">'.__('A smilies zip package has been successfully installed.').'</p>';
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
'<p>'.sprintf(__('Your <a href="blog_theme.php">current theme</a> on this blog is "%s".'),'<strong>'.html::escapeHTML($T['name']).'</strong>').'</p>';

if (empty($smilies))
{
	echo '<p>'.__('No defined smiley').'</p>';
}
else
{
	echo
	'<div class="clear" id="smilies_options">'.
	'<form action="plugin.php" method="post" id="form_tribune_options">'.
		'<fieldset>'.
			'<legend>'.__('Smilies configuration').'</legend>'.
				'<div>'.
					'<p class="field">'.
						form::checkbox('smilies_bar_flag', '1', $smilies_bar_flag).
						'<label class=" classic" for="smilies_bar_flag">'.__('Show toolbar smilies').'</label>'.
					'</p>'.
					'<p class="field">'.
						form::checkbox('smilies_preview_flag', '1', $smilies_preview_flag).
						'<label class=" classic" for="smilies_preview_flag">'.__('Show smilies on preview').'</label>'.
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

	$colspan = ($core->auth->isSuperAdmin() && $theme !='default') ? 3 : 2;
	echo
		'<p class="zip-dl"><a href="'.html::escapeURL($p_url).'&amp;zipdl=1">'. 
		__('Download the smilies directory as a zip file').'</a></p>'. 
		'<h3>'.__('Smilies set').'</h3>'.
		'<form action="'.$p_url.'" method="post" id="smilies-form">'.
		'<table class="maximal dragable">'.
		'<thead>'.
		'<tr>'.
		'<th colspan="'.$colspan.'">'.__('Code').'</th>'.
		'<th>'.__('Image').'</th>'.
		'<th>'.__('Filename').'</th>'.
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
		$disabled = ($core->auth->isSuperAdmin() && $theme !='default') ? false : true;
		echo
		'<tr class="line '.$line.'" id="l_'.($k).'">';
		if ($core->auth->isSuperAdmin() && $theme !='default') {echo  '<td class="handle minimal">'.form::field(array('order['.$k.']'),2,5,$k).'</td>' ;}
		echo
		'<td class="minimal status">'.form::checkbox(array('select[]'),$k).'</td>'.
		'<td class="minimal">'.form::field(array('code[]','c'.$k),10,255,html::escapeHTML($v['code']),'','',$disabled).'</td>'.
		'<td class="minimal smiley"><img src="'.$o->smilies_base_url.$v['name'].'" alt="'.$v['code'].'" /></td>'.
		'<td class="nowrap">'.form::combo(array('name[]','n'.$k),$smileys_list,$v['name'],'','',$disabled).'</td>'.
		'<td class="nowrap status">'.$status.'</td>'.
		'</tr>';
	}
	
	
	echo '</tbody></table>';
	
	echo '<div class="two-cols">
		<p class="col checkboxes-helpers"></p>';
	
	echo	'<p class="col right">'.__('Selected smilies action:').' '.
		form::hidden('smilies_order','').
		form::hidden(array('p'),'smiliesEditor').
		form::combo('actionsmilies',$combo_action).
		$core->formNonce().
		'<input type="submit" value="'.__('ok').'" /></p>';
		
	if (($core->auth->isSuperAdmin() && $theme !='default')) { 
	echo '<p><input type="submit" name="saveorder" 
		value="'.__('save smilies order').'" 
		/></p>'; }
		
	echo '</div></form>';
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
	if ($core->auth->isSuperAdmin() && $theme !='default')
	{
		$val = array_values($images_all);
		$preview_smiley = '<img class="smiley" src="'.$val[0]['url'].'" alt="'.$val[0]['name'].'" title="'.$val[0]['name'].'" id="smiley-preview" />';

		echo
			'<div class="three-cols">'.
			'<div class="col">'.
			'<form action="'.$p_url.'" method="post" id="add-smiley-form">'.
			'<fieldset>'.
			'<legend>'.__('Create a smiley').'</legend>'.
			'<p><label class="classic required" title="'.__('Required field').'">'.__('Code:').' '.
			form::field('smilecode',10,255).'</label></p>'.

			'<p><label class="classic required" title="'.__('Required field').'">'.__('Image:').' '.
			form::combo('smilepic',$smileys_combo).'</label>'.$preview_smiley.'</p>'.

			'<p>'.form::hidden(array('p'),'smiliesEditor').
			$core->formNonce().
			'<input type="submit" name="add_message" value="'.__('Create').'" /></p>'.
			'</fieldset>'.
			'</form></div>';
	}
}

if ($smg_writable && $core->auth->isSuperAdmin() && $theme !='default')
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

if (!empty($images_all) && $core->auth->isSuperAdmin() && $theme !='default')
{
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
	echo '</div>';
}
?>
<?php dcPage::helpBlock('pouet');?>
</body>
</html>