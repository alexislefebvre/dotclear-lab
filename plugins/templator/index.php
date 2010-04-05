<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of templator a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$file_default = $file = array('c'=>null, 'w'=>false, 'type'=>null, 'f'=>null, 'default_file'=>false);

$combo_types = array(
	__('Entry') => 'post',
	__('Page') => 'page'
);

$tpl = unserialize($core->blog->settings->templator_files);
$active_tpl = unserialize($core->blog->settings->templator_files_active);
$templator_flag = (boolean)$core->blog->settings->templator_flag;

$o = new dcTemplator($core);
$ressources = $o->canUseRessources(true);
$files= $o->tpl;

$add_template = false;

if (!$ressources)
{
	$core->error->add(__('The plugin is unusable with your configuration. You have to change file permissions.'));
}

try
{
	try
	{
		if (!empty($_REQUEST['tpl']) && $core->auth->isSuperAdmin()) {
			$file = $o->getSourceContent($_REQUEST['tpl']);
		} 
	}
	catch (Exception $e)
	{
		$file = $file_default;
		throw $e;
	}
	# Write file
	if (!empty($_POST['write']) && $core->auth->isSuperAdmin())
	{
		$file['c'] = $_POST['file_content'];
		$o->writeTpl($file['f'],$file['c']);
	}

	if (!empty($_POST['delete']) && $core->auth->isSuperAdmin())
	{
		$id = $_POST['file_id'];
		if (@unlink($files[$id]) === false) {
			throw new Exception(__('Cannot delete file.'));
		}
		unset($tpl[$id]);
		unsert($active_tpl[$id]);
		$core->blog->settings->setNamespace('templator');
		$core->blog->settings->put('templator_files',serialize($tpl),'string','My own supplementary template files',true,true);
		$core->blog->settings->put('templator_files_active',serialize($active_tpl),'string','My active supplementary template files');
		http::redirect($p_url.'&del='.$id);
	}
	
	if (!empty($_POST['disable']))
	{
		$id = $_POST['file_id'];
		$active_tpl[$id]['used'] = false;
		
		$core->blog->settings->setNamespace('templator');
		//$core->blog->settings->put('templator_files',serialize($tpl),'string','My own supplementary template files',true,true);
		$core->blog->settings->put('templator_files_active',serialize($active_tpl),'string','My active supplementary template files');
		http::redirect($p_url.'&hide='.$id);
	}	
	
	if (!empty($_POST['enable']))
	{
		$id = $_POST['file_id'];
		$active_tpl[$id]['used'] = true;
		
		$core->blog->settings->setNamespace('templator');
		//$core->blog->settings->put('templator_files',serialize($tpl),'string','My own supplementary template files',true,true);
		$core->blog->settings->put('templator_files_active',serialize($active_tpl),'string','My active supplementary template files');
		http::redirect($p_url.'&show='.$id);
	}	
	
	if (!empty($_POST['update']))
	{
		$id = $_POST['file_id'];
		$tpl[$id]['title'] = $_POST['file_title'][$id];
		
		$core->blog->settings->setNamespace('templator');
		$core->blog->settings->put('templator_files',serialize($tpl),'string','My own supplementary template files',true,true);
		http::redirect($p_url.'&update='.$id);
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

if (!empty($_POST['saveconfig']))
{
	try
	{
		$templator_flag = (empty($_POST['templator_flag']))?false:true;
		$core->blog->settings->setNamespace('templator');
 		$core->blog->settings->put('templator_flag',$templator_flag,'boolean','Templator activation flag');

		$core->blog->triggerBlog();
		http::redirect($p_url.'&config=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

if (!empty($_POST['filename']) && !empty($_POST['filetype']) && !empty($_POST['filetitle']) && $core->auth->isSuperAdmin())
{
	$type = $_POST['filetype'];
	$name = files::tidyFileName($_POST['filename']).'.html';

	try {
		$o->initializeTpl($name,$type);
		$tpl[$name]['title'] = $_POST['filetitle']; 
		$tpl[$name]['type'] = $type; 
		$active_tpl[$name]['used'] = true; 
		$core->blog->settings->setNamespace('templator');
		$core->blog->settings->put('templator_files',serialize($tpl),'string','My own supplementary template files',true,true);
		$core->blog->settings->put('templator_files_active',serialize($active_tpl),'string','My active supplementary template files');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
		
	if (!$core->error->flag()) {
		http::redirect($p_url.'&newtpl='.$type.'&name='.$name);
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
		$zip->addDirectory($core->plugins->moduleInfo('templator','root').'/default-templates','',true);
		header('Content-Disposition: attachment;filename=templator-templates.zip');
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
	<title><?php echo __('Templator'); ?></title>
	<link rel="stylesheet" type="text/css" href="index.php?pf=templator/style.css" />
	<script type="text/javascript">
	//<![CDATA[
	<?php echo dcPage::jsVar('dotclear.msg.saving_document',__("Saving document...")); ?>
	<?php echo dcPage::jsVar('dotclear.msg.document_saved',__("Document saved")); ?>
	<?php echo dcPage::jsVar('dotclear.msg.error_occurred',__("An error occurred:")); ?>
	//]]>
	</script>
	<script type="text/javascript" src="index.php?pf=templator/script.js"></script>
	<?php if (!$add_template) {
		echo dcPage::jsLoad('index.php?pf=templator/form.js');
	}?>
</head>

<body>
<?php
echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Supplementary templates').'</h2>';

if (!empty($_GET['config'])) {
	echo '<p class="message">'.__('Configuration successfully updated.').'</p>';
}

if (!empty($_GET['hide'])) {
	echo '<p class="message">'.sprintf(__('The template %s is now unavailable.'),$_GET['hide']).'</p>';
}

if (!empty($_GET['show'])) {
	echo '<p class="message">'.sprintf(__('The template %s is now available.'),$_GET['show']).'</p>';
}

if (!empty($_GET['update'])) {
	echo '<p class="message">'.__('The template title has been successfully updated.').'</p>';
}

if (!empty($_GET['del'])) {
	echo '<p class="message">'.sprintf(__('The template %s has been deleted.'),$_GET['del']).'</p>';
}

if (!empty($_GET['newtpl'])) {
	echo '<p class="message">'.sprintf(__('The template %s has been successfully created.'),$_GET['name']).'</p>';
}

echo '<div id="private_options">'.
	'<form method="post" action="'.$p_url.'">'.
		'<fieldset>'.
			'<legend>'. __('Plugin activation').'</legend>'.
				'<p class="field">'.
					form::checkbox('templator_flag', 1, $templator_flag).
					'<label class=" classic" for="templator_flag">'.__('Enable extension').'</label>'.
				'</p>'.
		'</fieldset>'.
		'<p>'.form::hidden(array('p'),'templator').
		$core->formNonce().
		'<input type="submit" name="saveconfig" value="'.__('Save configuration').'" /></p></form></div>';

if (!$core->error->flag())
{
	if ($core->auth->isSuperAdmin())
	{
		if (!$add_template) {
			echo '<div class="two-cols" id="new-template"><h3><a class="new" id="templator-control" href="#">'.
			__('Create a new template').'</a></h3></div>';
		}

		echo
		'<div class="col">'.
		'<form action="'.$p_url.'" method="post" id="add-template">'.
		'<fieldset>'.
		'<legend>'.__('Create a new template').'</legend>'.
		'<p><label>'.__('Usage:').' '.
		form::combo('filetype',$combo_types).'</label></p>'.
		'<p><label class="required" title="'.__('Required field').'">'.__('Filename:').' '.
		form::field('filename',30,255).'</label></p>'.
		'<p class="form-note">'.sprintf(__('The extension %s is automatically added'),'<code>.html</code>').'</p>'.
		'<p><label class="required" title="'.__('Required field').'">'.__('Title:').' '.
		form::field('filetitle',30,255).'</label></p>'.
		'<p>'.form::hidden(array('p'),'templator').
		$core->formNonce().
		'<input type="submit" name="add_message" value="'.__('Create').'" /></p>'.
		'</fieldset>'.
		'</form></div>';
	
	}

	if(!empty($tpl))
	{
		echo
		'<div class="col">'.
		'<h3>'.__('Available templates').'</h3>'.
		'<table class="maximal">'.
		'<thead>'.
		'<tr>'.
		'<th >'.__('Usage').'</th>'.
		'<th>'.__('Filename').'</th>'.
		'<th>'.__('Title').'</th>'.
		'<th colspan="2" >'.__('Action').'</th>'.
		'<th>&nbsp;</th>'.
		'</tr>'.
		'</thead>'.
		'<tbody id="tpl-post-list">';
	
		foreach ($tpl as $k => $v)
		{
			if ($v['type'] == 'page') {
				$type = __('Page');
			}
			else
			{
				$type = __('Entry');
			}

			if($active_tpl[$k]['used']) {
				$line = '';
				$status = '<img alt="'.__('available').'" title="'.__('available').'" src="images/check-on.png" />';
			}
			else
			{
				$line = 'offline';
				$status = '<img alt="'.__('unavailable').'" title="'.__('unavailable').'" src="images/check-off.png" />';
			}

			$edit = ($core->auth->isSuperAdmin()) ? 
				'<a href="'.$p_url.'&amp;tpl='.$k.'"><img src="images/edit-mini.png" alt="" title="'.__('edit this template').'" /></a>' : '';

			echo
			'<tr class="line '.$line.'" id="l_'.($k).'">'.
			'<td class="nowrap">'.$type.'</td>'.
			'<td ><code>'.$k.'</code></td>'.
			'<td >'.
				'<form action="'.$p_url.'" method="post"><p>'.
				$core->formNonce().
				form::hidden(array('file_id'),html::escapeHTML($k)).
				form::field(array('file_title['.$k.']','t'.$k),30,255,$v['title']).
				'<input type="submit" class="update" name="update" value="'.__('Update').'" />'.
				'</p></form></td>'.

			'<td>'.
				'<form action="'.$p_url.'" method="post"><p>'.
				$core->formNonce().
				form::hidden(array('file_id'),html::escapeHTML($k)).
				(($active_tpl[$k]['used']) ? 
					'<input type="submit" class="disable" name="disable" value="'.__('Disable').'" /> ' : 
					'<input type="submit" class="enable" name="enable" value="'.__('Enable').'" /> ' ).
				'</p></form></td>'.
			'<td class="nowrap status">'.$status.'</td>'.
			'<td  class="nowrap status" >'.$edit.'</td >'.

			'</tr></tbody></table></div>';
		}

		if ($core->auth->isSuperAdmin())
		{
			echo '<p class="zip-dl"><a href="'.html::escapeURL($p_url).'&amp;zipdl=1">'.
				__('Download the templates directory as a zip file').'</a></p>';
		}
	}

	if (($file['c'] !== null) && $core->auth->isSuperAdmin())
	{
		echo
		'<div id="file-templator">'.
		'<form id="file-form" action="'.$p_url.'" method="post">'.
		'<fieldset><legend>'.__('File editor').'</legend>'.
		'<p>'.sprintf(__('Editing file %s'),'<strong>'.$file['f']).'</strong></p>'.
		'<p>'.form::textarea('file_content',72,25,html::escapeHTML($file['c']),'maximal','',!$file['w']).'</p>';

		if ($file['w'])
		{
			echo
			'<p><input type="submit" name="write" value="'.__('save').' (s)" accesskey="s" /> '.
			$core->formNonce().
			form::hidden(array('file_id'),html::escapeHTML($k)).
			(files::isDeletable($files[$k]) ? '<input type="submit" class="delete" name="delete" value="'.__('delete').'" /> ' : '').
			'</p>';
		}
		else
		{
			echo '<p>'.__('This file is not writable. Please check your theme files permissions.').'</p>';
		}

		echo
		'</fieldset></form></div>';
	}
}
?>
</body>
</html>
