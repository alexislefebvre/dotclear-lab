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

// Categories
try {
	$categories = $core->blog->getCategories(array('post_type'=>'post'));
	while ($categories->fetch()) {
		$categories_combo[] = new formSelectOption(
			str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.html::escapeHTML($categories->cat_title),
			$categories->cat_id
		);
		$categories_name['category-'.$categories->cat_id.'.html'] = $categories->cat_title;
	}
} catch (Exception $e) { }

$hasCategories = ($categories->isEmpty()) ? false : true;

$combo_source = array(
	'post.html' => 'post',
	'page.html' => 'page'	
);

if (!$categories->isEmpty()) {
	$combo_source['category.html'] = 'category';
}

// Settings
$s = $core->blog->settings->templator;
$tpl = unserialize($s->templator_files);
$templator_flag = (boolean)$s->templator_flag;

// Get theme Infos
$core->themes = new dcThemes($core);
$core->themes->loadModules($core->blog->themes_path,null);
$T = $core->themes->getModules($core->blog->settings->system->theme);

// New Templator
$o = new dcTemplator($core);
$ressources = $o->canUseRessources(true);
$files= $o->tpl;
$t_files = $o->theme_tpl;

// May repair broken settings
foreach ($files as $k => $v)
{
	if(!isset($tpl[$k]))
	{
		$tpl[$k];
		$tpl[$k]['title'] = $k; 
		$tpl[$k]['isCat'] = preg_match('/^(category)(.+)$/',$k)? true : false; 
		$tpl[$k]['used'] = false; 
	}
}
foreach ($tpl as $k => $v)
{
	if(!isset($files[$k]))
	{
		unset($tpl[$k]);
	}
}

$s->put('templator_files',serialize($tpl),'string','My own supplementary template files');


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
			$core->blog->triggerBlog();	
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
		$s->put('templator_files',serialize($tpl),'string','My own supplementary template files');
		$core->blog->triggerBlog();
		http::redirect($p_url.'&del='.$id);
	}
	
	if (!empty($_POST['disable']))
	{
		$id = $_POST['file_id'];
		$tpl[$id]['used'] = false;
		$s->put('templator_files',serialize($tpl),'string','My own supplementary template files');
		$core->blog->triggerBlog();
		http::redirect($p_url.'&hide='.$id);
	}	
	
	if (!empty($_POST['enable']))
	{
		$id = $_POST['file_id'];
		$tpl[$id]['used'] = true;
		$s->put('templator_files',serialize($tpl),'string','My own supplementary template files');
		$core->blog->triggerBlog();
		http::redirect($p_url.'&show='.$id);
	}	
	
	if (!empty($_POST['update']))
	{
		$id = $_POST['file_id'];
		$tpl[$id]['title'] = empty($_POST['file_title'][$id]) ? $id : $_POST['file_title'][$id];
		$s->put('templator_files',serialize($tpl),'string','My own supplementary template files');
		$core->blog->triggerBlog();
		http::redirect($p_url.'&update='.$id);
	}
	
	if (!empty($_POST['copy']) && $core->blog->settings->system->theme != 'default')
	{
		$id = $_POST['file_id'];
		$o->copyTpl($id);
		$core->blog->triggerBlog();
		http::redirect($p_url.'&copy='.$id);
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
		$templator_flag = (empty($_POST['templator_flag'])) ? false : true;
		$s->put('templator_flag',$templator_flag,'boolean','Templator activation flag');
		$core->blog->triggerBlog();
		http::redirect($p_url.'&config=1');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

if (!empty($_POST['filesource']) && $core->auth->isSuperAdmin())
{
	$source = $_POST['filesource'];
	$name = files::tidyFileName($_POST['filename']).'.html';
	$isCat = false;
	if ($source == 'category')
	{
		$name = 'category-'.$_POST['filecat'].'.html';
		$isCat = true;
	}
	$title = (empty($_POST['filetitle'])) ? $name : trim($_POST['filetitle']);
	
	try {
		$o->initializeTpl($name,$source);
		$tpl[$name]['title'] = $title; 
		$tpl[$name]['isCat'] = $isCat; 
		$tpl[$name]['used'] = true; 
		$s->put('templator_files',serialize($tpl),'string','My own supplementary template files');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
		
	if (!$core->error->flag()) {
		$core->blog->triggerBlog();
		http::redirect($p_url.'&newtpl='.$name);
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
		$zip->addDirectory(DC_TPL_CACHE.'/templator/'.$core->blog->id.'-default-templates/','',true);
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

if (!empty($_GET['copy'])) {
	echo '<p class="message">'.__('The template has been successfully copied to blog theme.').'</p>';
}

if (!empty($_GET['del'])) {
	echo '<p class="message">'.sprintf(__('The template %s has been deleted.'),$_GET['del']).'</p>';
}

if (!empty($_GET['newtpl'])) {
	echo '<p class="message">'.sprintf(__('The template %s has been successfully created.'),$_GET['newtpl']).'</p>';
}

echo '<div id="private_options">
	<form method="post" action="'.$p_url.'">
	<fieldset>
	<legend>'. __('Plugin activation').'</legend>
	<p class="field">'.
	form::checkbox('templator_flag', 1, $templator_flag).
	'<label class=" classic" for="templator_flag">'.__('Enable extension').'</label>
	</p>
	<p>'.form::hidden(array('p'),'templator').
	$core->formNonce().
	'<input type="submit" name="saveconfig" value="'.__('Save configuration').'" />
	</p>
	</fieldset>
	</form>
	</div>';

if (!$core->error->flag())
{
	if(!empty($tpl))
	{
		echo
		'<div class="col">
		<fieldset>
		<legend>'.__('Available templates').'</legend>
		<table class="maximal">
		<thead>
		<tr>
		<th>'.__('Filename').'</th>
		<th>'.__('Title').'</th>
		<th>'.__('Action').'</th>
		<th>&nbsp;</th>
		</tr>
		</thead>
		<tbody id="tpl-post-list">';
		
		ksort($tpl);
	
		foreach ($tpl as $k => $v)
		{
			if(isset($tpl[$k]['used']) && $tpl[$k]['used']) {
				$line = '';
			}
			else
			{
				$line = 'offline';
			}
			
			$hisCat = $v['isCat'];

			$edit = ($core->auth->isSuperAdmin()) ? 
				'<a href="'.$p_url.'&amp;tpl='.$k.'"><img src="images/edit-mini.png" alt="" title="'.__('edit this template').'" /></a>' : '';

			echo
			'<tr class="line '.$line.'" id="l_'.($k).'">'.
			'<td >'.$k.'</td>'.
			'<td >';
			if (!$hisCat)
			{
				echo
				'<form action="'.$p_url.'" method="post">'.
				$core->formNonce().
				form::hidden(array('file_id'),html::escapeHTML($k)).
				'<p>'.
				form::field(array('file_title['.$k.']','t'.$k),30,255,$v['title']).
				'&nbsp;<input type="submit" class="update" name="update" value="'.__('Rename').'" />&nbsp;'.
				'</p>'.
				'</form>';
			}
			else 
			{
				echo __('Category template').'&nbsp;:&nbsp;<strong>'.$categories_name[$k].'</strong>';
			}
			echo
			'</td>'.
			'<td>'.
				'<form action="'.$p_url.'" method="post"><p>'.
				$core->formNonce().
				form::hidden(array('file_id'),html::escapeHTML($k)).
				((isset($tpl[$k]['used'])) && $tpl[$k]['used'] ? 
					'<input type="submit" class="disable" name="disable" value="'.__('Disable').'" /> ' : 
					'<input type="submit" class="enable" name="enable" value="'.__('Enable').'" /> ' );
				if (((!isset($t_files[$k]) ||
				(strpos($t_files[$k],path::real($core->blog->themes_path.'/'.$core->blog->settings->system->theme)) !== 0))) &&
				$core->blog->settings->system->theme != 'default')
				{
					echo '&nbsp;<input type="submit" class="copy" name="copy" value="'.__('Copy').'" />';
				}
				echo
				'</p></form></td>'.
			'<td  class="nowrap status" >'.$edit.'</td >'.
			'</tr>';
		}
		echo '</tbody></table>';
		
		if ($core->blog->settings->system->theme != 'default')
		{
			echo '<p>'.sprintf(__('The %s button makes a copy to blog theme (<strong>%s</strong>).'),'<span class="copy">'.__('Copy').'</span>',html::escapeHTML($T['name'])).'</p>';
		}
		
		if ($core->auth->isSuperAdmin())
		{
			echo '<p class="zip-dl"><a href="'.html::escapeURL($p_url).'&amp;zipdl=1">'.
				__('Download the templates directory as a zip file').'</a></p>';
		}
		echo '</fieldset></div>';
	}
	
	if ($core->auth->isSuperAdmin() && $file['c'] === null)
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
		'<p class="field"><label for="filesource" class="required">'.__('Template source:').' '.
		form::combo('filesource',$combo_source).'</label></p>'.
		'<p><label for="filename" class="classic required" title="'.__('Required field').'">'.__('Filename:').' '.
		form::field('filename',20,255).'</label><strong>'.html::escapeHTML('.html').'</strong></p>';
		
		if ($hasCategories) {
			echo 
			'<p class="field"><label for="filecat">'.__('Category:').
			form::combo('filecat',$categories_combo,'').'</label></p>';
		}
		
		echo
		'<p class="field"><label for="filetitle">'.__('Title:').' '.
		form::field('filetitle',30,255).'</label></p>'.
		'<p>'.form::hidden(array('p'),'templator').
		$core->formNonce().
		'<input type="submit" name="add_message" value="'.__('Create').'" /></p>'.
		'</fieldset>'.
		'</form></div>';
	
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
			echo '<p>'.__('This file is not writable. Please check your files permissions.').'</p>';
		}

		echo
		'</fieldset></form></div>';
	}
}
?>
</body>
</html>