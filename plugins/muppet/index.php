<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of muppet, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$my_types = muppet::getPostTypes();

$type = (!empty($_REQUEST['type'])) ? $_REQUEST['type'] : '';
$id = (!empty($_REQUEST['id'])) ? $_REQUEST['id'] : '';
$list = (!empty($_REQUEST['list'])) ? $_REQUEST['list'] : '';
$edit = (!empty($_REQUEST['edit'])) ? $_REQUEST['edit'] : '';

$newtype = $name = $plural = '';
$counts = array();
$icon = 'image-1.png';

if (!empty($type))
{
	if (empty($list))
	{
		include dirname(__FILE__).'/item.php';
	}
	else
	{
		include dirname(__FILE__).'/list.php';
	}
	return;
}

if (!$core->auth->check('admin',$core->blog->id)) { return; }

$add_type = false;
$icons = array();

for ($i = 1; $i <= 20; $i++) {
	$icons= array_merge($icons, array(sprintf('- %s -',$i) => sprintf('image-%s.png',$i)));
}

if (!empty($_POST['typeadd']))
{
	$type = mb_strtolower($_POST['newtype']);
	$newtype = $_POST['newtype'];
	$name = $_POST['name'];
	$plural = $_POST['plural'];
	$icon = $_POST['icon'];

	if (!preg_match('/^([a-z]{2,})$/',$type))
	{
		$core->error->add(__('Post type must contain at least 2 letters (only letters).'));
	}

	if (muppet::typeIsExcluded($type))
	{
		$core->error->add(__('This post type is aleady used by another plugin.'));
	}

	if (!preg_match('/^\w{1,}$/',$_POST['name']))
	{
		$core->error->add(__('Name should be a nice word.'));
	}

	$values = array(
		'name' =>  mb_strtolower($_POST['name']),
		'plural' => mb_strtolower($_POST['plural']),
		'icon' => $_POST['icon']
	);

	if (!$core->error->flag())
	{
		muppet::setNewPostType($type,$values);
		http::redirect($p_url.'&msg=saved');
	}
}
if (!empty($_POST['typedel']))
{
	$type = mb_strtolower($_POST['newtype']);
	
	if (!$core->error->flag())
	{
		muppet::removePostType($type);
		http::redirect($p_url.'&msg=deleted');
	}
}

if (!empty($_POST['getinfo']))
{
	$counts = muppet::getInBasePostTypesCounter();
}

# Messages - thanks JcDenis for the method :-)
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$msg_list = array(
	'saved' => __('Configuration successfully saved.'),
	'deleted' => __('Post type successuflly removed.')
);

if (isset($msg_list[$msg])) {
	$msg = sprintf('<p class="message">%s</p>',$msg_list[$msg]);
}

if (!empty($edit) || $core->error->flag())
{
	$add_type = true;
}

?>
<html>
<head>
	<title><?php echo __('Muppet'); ?></title>
	<?php echo dcPage::jsLoad('index.php?pf=muppet/js/misc.js'); ?>
	<?php if (!$add_type) {
		echo dcPage::jsLoad('index.php?pf=muppet/js/form.js');
	}?>
	<script type="text/javascript">
	//<![CDATA[
	  <?php echo dcPage::jsVar('dotclear.icon_base_url','index.php?pf=muppet/img/');?>
	//]]>
	</script>
	<style type="text/css">
		img.icon {vertical-align:middle;}
		span.hot {color:#009966}
		input.delete {color:#FF0000;}
		a.none{border:none;}
		div.post {border-right:1px dotted #cecfca;margin: 0 20px 10px 0;}
		dl.list dd{margin-left:1em;padding:5px;}
	</style>
</head>
<body>
<?php
echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Supplementary post types').'</h2>';
echo $msg;
echo '<h3>'.__('My post types').'</h3>';
if (empty($my_types))
{
	echo '<p>'.__('No type has been defined yet.').'</p>';
}
else
{
	foreach ($my_types as $k => $v)
	{
		$plural = empty($v['plural']) ? $v['name'].'s' : $v['plural'];
		$redir = 'plugin.php?p=muppet&amp;edit=';
		
		echo
		'<div class="post" style="width:180px; float:left;">'.
		'<h4><a class="none" href="'.$redir.$k.'"><img src="images/edit-mini.png" alt="" title="'.__('edit this post type').'" /></a>&nbsp;'.sprintf(__('Type: <span class="hot">%s</span>'),$k).'</h4>'.
		'<dl class="list">'.
		'<dt>'.__('Name:').'</dt><dd>'.$v['name'].'&nbsp;('.$plural.')</dd>'.
		'<dt>'.__('Menu image:').'</dt><dd><img src="index.php?pf=muppet/img/'.$v['icon'].'" alt="'.$v['icon'].'" /></dd>'.
		'<dt>'.__('Permission:').'</dt><dd>'.sprintf(__('manage the %s'),$plural).'</dd>'.
		'</dl>'.
		'</div>';
	}
}

$legend = __('Create a new post type');
$label_add = __('Create');

if (!empty($edit))
{
	if (array_key_exists($edit,$my_types))
	{
		$newtype = $edit;
		$name = $my_types[$edit]['name'];
		$plural = $my_types[$edit]['plural'];
		$icon = $my_types[$edit]['icon'];
		$legend = __('Modify a post type');
		$label_add = __('Save');
	}
}

$preview_icon = '<img class="icon" src="index.php?pf=muppet/img/'.$icon.'" alt="'.$icon.'" title="'.$icon.'" id="icon-preview" />';


if (!$add_type) {
	echo '<div class="clear" id="new-type"><p><a class="new" id="muppet-control" href="#">'.
	__('Create a new post type').'</a></p></div>';
}

echo
'<div class="clear">'.
'<form action="'.$p_url.'" method="post" id="add-post-type">'.
'<fieldset>'.
'<legend>'.$legend .'</legend>'.
'<p><label class="required" title="'.__('Required field').'">'.__('Type:').' '.
form::field('newtype',30,255,$newtype).'</label></p>'.
'<p><label class="required" title="'.__('Required field').'">'.__('Name:').' '.
form::field('name',30,255,$name).'</label></p>'.
'<p><label class="" title="'.__('Required field').'">'.__('Plural form:').' '.
form::field('plural',30,255,$plural).'</label></p>'.
''.
'<p><label class="classic required" title="'.__('Required field').'">'.__('Image:').' '.
form::combo('icon',$icons,$icon).'</label>'.$preview_icon.'</p>'.
'<p>'.form::hidden(array('p'),'muppet').
$core->formNonce().
'<input type="submit" name="typeadd" value="'.$label_add.'" />&nbsp;';
echo (!empty($edit)) ? '<input type="submit" class="delete"  name="typedel" value="'.__('Delete').'" />' : '';
echo '</p>'.
'</fieldset>'.
'</form></div>';

if (empty($counts))
{
	echo
	'<form action="'.$p_url.'" method="post" id="get-infos">'.
	'<h3>'.__('Statistics').'</h3>'.
	'<p><input type="submit" name="getinfo" value="'.__('Retrieve types from database').'" /> '.
	$core->formNonce().
	form::hidden(array('p'),'muppet').'</p>'.
	'</form>';
}
else
{
	$line = '';
	foreach ($counts as $k => $v)
	{
		$t = ($v == 1 )? __('%s post') : __('%s posts');
		$line .= '<li>'.sprintf($t,$v).'&nbsp;'.sprintf(__('with type <strong>%s</strong>.'),$k).'</li>';
	}
	echo 
	'<div class="col">'.
	'<h3>'.__('Statistics').'</h3>'.
	'<ul>'.$line.'</ul>'.
	'</div>';
}

?>
</body>
</html>
