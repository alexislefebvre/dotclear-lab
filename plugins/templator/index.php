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
dcPage::check('templator,contentadmin');

if ((!empty($_REQUEST['m'])) && ($_REQUEST['m'] = 'template_posts')) {
	require dirname(__FILE__).'/'.$_REQUEST['m'].'.php';
	return;
}
if (!empty($_REQUEST['edit'])) {
	require dirname(__FILE__).'/edit.php';
	return;
}
if (!empty($_REQUEST['mode']) && $_REQUEST['mode'] = 'db') {
	require dirname(__FILE__).'/advanced.php';
	return;
}

$file_default = $file = array('c'=>null, 'w'=>false, 'type'=>null, 'f'=>null, 'default_file'=>false);
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  20;
$msg = '';
$remove_confirm = false;

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
	'post.html' => 'post'
);

if ($core->auth->check('pages',$core->blog->id)) {
	$combo_source['page.html'] = 'page';
}

if (!$categories->isEmpty()) {
	$combo_source['category.html'] = 'category';
}

// Load infos.
$ressources = $core->templator->canUseRessources(true);
$files= $core->templator->tpl;

// Media
$media = new dcMedia($core);
$media->chdir($core->templator->template_dir_name);
// For users with only templator permission, we use sudo.
$core->auth->sudo(array($media,'getDir'));
$dir =& $media->dir;

$add_template = false;

if (!$ressources)
{
	$core->error->add(__('The plugin is unusable with your configuration. You have to change file permissions.'));
}

if (!empty($_POST['filesource']))
{
	try
	{
		$source = $_POST['filesource'];
		if (empty($_POST['filename']) && $source != 'category') {
			throw new Exception(__('Filename is empty.'));
		}
		$name = files::tidyFileName($_POST['filename']).'.html';
		if ($source == 'category')
		{
			$name = 'category-'.$_POST['filecat'].'.html';
		}
		$core->templator->initializeTpl($name,$source);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	if (!$core->error->flag()) {
		http::redirect($p_url.'&msg=new');
	}
}

if (!empty($_POST['rmyes']) && !empty($_POST['remove']) ) {
	try
	{
		$file = rawurldecode($_POST['remove']);
		$media->removeItem($file);
		$core->meta->delMeta($file,'template');
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	if (!$core->error->flag()) {
		http::redirect($p_url.'&msg=del');
	}
}

if (!empty($_GET['remove']))
{
	$remove_confirm = true;
}

$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'new' => __('The new template has been successfully created.'),
	'del' => __('The template has been successuflly removed.')
);
if (isset($msg_list[$msg])) {
	$msg = sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}
?>
<html>
<head>
	<title><?php echo __('Templator'); ?></title>
	<link rel="stylesheet" type="text/css" href="index.php?pf=templator/style/style.css" />
	<?php if (!$add_template) {
		echo dcPage::jsLoad('index.php?pf=templator/js/form.js');
	}?>
	<?php echo dcPage::jsLoad('index.php?pf=templator/js/script.js');?>
</head>

<body>
<?php
echo $msg;

echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Supplementary templates').'</h2>';

if ($remove_confirm) {
	echo
	'<form action="'.$p_url.'" method="post">'.
	'<p>'.sprintf(__('Are you sure you want to remove %s?'),
	html::escapeHTML($_GET['remove'])).'</p>'.
	'<p><input type="submit" value="'.__('cancel').'" /> '.
	' &nbsp; <input type="submit" name="rmyes" value="'.__('yes').'" />'.
	$core->formNonce().
	form::hidden('remove',html::escapeHTML($_GET['remove'])).'</p>'.
	'</form>';
}

$items = array_values($dir['files']);
if (count($items) == 0)
{
	echo '<p><strong>'.__('No template.').'</strong></p>';
}
else
{
	$pager = new pager($page,count($items),$nb_per_page,10);
	$pager->html_prev = __('&#171;prev.');
	$pager->html_next = __('next&#187;');
	
	echo
	'<form action="media.php" method="get">'.
	'</form>'.
	
	'<div class="media-list">'.
	'<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
	
	for ($i=$pager->index_start, $j=0; $i<=$pager->index_end; $i++, $j++)
	{
		echo pagerTemplator::templatorItemLine($items[$i],$j);
	}
	
	echo
	'<p class="clear">'.__('Page(s)').' : '.$pager->getLinks().'</p>'.
	'</div>';
}

if (!$add_template) {
	echo '<div class="two-cols" id="new-template"><h3><a class="new" id="templator-control" href="#">'.
	__('New template').'</a></h3></div>';
}

echo
'<div class="two-cols"><div class="col">'.
'<form action="'.$p_url.'" method="post" id="add-template">'.
'<h3>'.__('New template').'</h3>'.
'<fieldset>'.
'<p class="field"><label for="filesource" class="required">'.__('Template source:').' '.
form::combo('filesource',$combo_source).'</label></p>'.
'<p><label for="filename" class="classic required" title="'.__('Required field').'">'.__('Filename:').' '.
form::field('filename',25,255).'</label><code>'.html::escapeHTML('.html').'</code></p>';

if ($hasCategories) {
	echo '<p class="field"><label for="filecat">'.__('Category:').
	form::combo('filecat',$categories_combo,'').'</label></p>';
}

echo
'<p>'.form::hidden(array('p'),'templator').
$core->formNonce().
'<input type="submit" name="add_message" value="'.__('create').'" /></p>'.
'</fieldset>'.
'</form></div></div>';

?>
</body>
</html>
