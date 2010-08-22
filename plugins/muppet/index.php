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

$newtype = $name = $plural = $post_url = '';
$counts = array();
$icon = 'image-1.png';
$integration = $feed = false;
$yes = __('yes');
$no = __('no');

# Post URL combo
$post_url_combo = array(
	__('title') => '{t}',
	__('id') => '{id}',
	__('year/month/day/title') => '{y}/{m}/{d}/{t}',
	__('year/month/title') => '{y}/{m}/{t}',
	__('year/title') => '{y}/{t}'
);

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

for ($i = 1; $i <= 49; $i++) {
	$icons= array_merge($icons, array(sprintf('&nbsp;&bull; %s',$i) => sprintf('image-%s.png',$i)));
}

if (!empty($_POST['typeadd']))
{
	$type = mb_strtolower($_POST['newtype']);
	$newtype = $_POST['newtype'];
	$name = trim($_POST['name']);
	$plur = trim($_POST['plural']);
	$plural = empty($plur)? $name.'s' : $plur;
	$icon = $_POST['icon'];
	$post_url = $_POST['urlformat'];
	$integration = isset($_POST['integration'])? true : false;
	$feed = isset($_POST['feed'])? true : false;

	if (!preg_match('/^([a-z]{2,})$/',$type))
	{
		$core->error->add(__('Post type must contain at least 2 letters (only letters).'));
	}

	if (muppet::typeIsExcluded($type))
	{
		$core->error->add(__('This post type is aleady used by another plugin.'));
	}

	$urlformat = empty($_POST['urlformat']) ? '{t}': $_POST['urlformat'];

	//if (!preg_match('/^\w+(\s*\w+)?$/',$name))
	if (empty($name))
	{
		$core->error->add(__('Name should be a nice word.'));
	}

	$values = array(
		'name' =>  mb_strtolower($name),
		'plural' => mb_strtolower($plural),
		'icon' => $_POST['icon'],
		'urlformat' => $urlformat,
		'integration' => $integration,
		'feed' => $feed
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
		input.delete {color:#DD0000;}
		a.none{border:none;}
		div.post {border-right:1px dotted #cecfca;margin: 0 20px 10px 0;}
		dl.list dd{margin-left:1em;padding:5px;}
		#icon,#icon option {background-color:transparent;
			background-repeat:no-repeat;background-position:4% 50%;
			padding:1px 1px 1px 16px;color:#444;}
	</style>
</head>
<body>
<?php
echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Supplementary post types');
if (!$add_type) {
	echo ' &rsaquo; <a class="button" id="muppet-control" href="#">'.
	__('New post type').'</a>';
}
echo '</h2>';
echo $msg;

$legend = __('Create a new post type');
$label_add = __('create');

if (!empty($edit))
{
	if (array_key_exists($edit,$my_types))
	{
		$newtype = $edit;
		$name = $my_types[$edit]['name'];
		$plural = $my_types[$edit]['plural'];
		$icon = $my_types[$edit]['icon'];
		$post_url = $my_types[$edit]['urlformat'];
		$integration = (boolean) $my_types[$edit]['integration'];
		$feed = (boolean) $my_types[$edit]['feed'];

		$legend = __('Modify a post type');
		$label_add = __('save');
	}
}

//$preview_icon = '<img class="icon" src="index.php?pf=muppet/img/'.$icon.'" alt="'.$icon.'" title="'.$icon.'" id="icon-preview" />';

echo
'<div class="clear">
<form action="'.$p_url.'" method="post" id="add-post-type">
<fieldset>
<legend>'.$legend .'</legend>
<p class="field"><label class="classic required" title="'.__('Required field').'">'.__('Image:').' '.
form::combo('icon',$icons,$icon).'</label></p>
<p class="field"><label class="required classic" title="'.__('Required field').'">'.__('Type:').' '.
form::field('newtype',30,255,$newtype).'</label></p>
<p class="field"><label class="required classic" title="'.__('Required field').'">'.__('Name:').' '.
form::field('name',30,255,$name).'</label></p>
<h3>'.__('Miscellaneous').'</h3>
<p class="field"><label class="classic" title="'.__('Required field').'">'.__('Plural form:').' '.
form::field('plural',30,255,$plural).'</label></p>
<p class="field"><label class="classic" >'.__('New post URL format:').
form::field('urlformat',30,255,$post_url).'</label></p>
<p class="form-note">'.__('{y}: year, {m}: month, {d}: day, {id}: post id, {t}: entry title').'</p>
<h3>'.__('Integration').'</h3>
<p class="field"><label class="classic" >'.__('Blog content:').
form::checkbox('integration','1',$integration).'</label></p>
<p class="field"><label class="classic" >'.__('Feeds:').
form::checkbox('feed','2',$feed).'</label></p>
<p>'.form::hidden(array('p'),'muppet').
$core->formNonce().
'<input type="submit" name="typeadd" value="'.$label_add.'" />&nbsp;';
echo (!empty($edit)) ? '<input type="submit" class="delete"  name="typedel" value="'.__('delete').'" />' : '';
echo '</p>
</fieldset>
</form></div>';

if (empty($my_types))
{
	echo '<p>'.__('No type has been defined yet.').'</p>';
}
else
{
	echo '<div class="media-list">';
	foreach ($my_types as $k => $v)
	{
		$plural = empty($v['plural']) ? $v['name'].'s' : $v['plural'];
		$redir = 'plugin.php?p=muppet&amp;edit=';
		$content = $v['integration'] ? $yes : $no;
		$feeds = $v['feed'] ? $yes : $no;

		echo
		'<div class="media-item">
		<a class="media-icon media-link" href="'.$redir.$k.'" title="'.__('edit this post type').'" ><img src="index.php?pf=muppet/img/'.$v['icon'].'" alt="'.$v['icon'].'" /></a>
		<ul class="list">
		<li><h3><a href="'.$redir.$k.'" title="'.__('edit this post type').'" >'.$k.'</a></h3></li>
		<li><strong>'.__('Name:').'</strong> '.$v['name'].'&nbsp;('.$plural.')</li>
		<li><strong>'.__('Permission:').'</strong> '.sprintf(__('manage the %s'),$plural).'</li>
		<li><strong>'.__('New post URL format:').'</strong> '.$v['urlformat'].'</li>
		<li><strong>'.__('In content:').'</strong> '.$content.'</li>
		<li><strong>'.__('In feeds:').'</strong> '.$feeds.'</li>
		</ul>
		</div>';
	}
	echo '</div>';
}

if (empty($counts))
{
	echo
	'<form action="'.$p_url.'" method="post" id="get-infos">
	<p class="clear right"><input type="submit" name="getinfo" value="'.__('Statistics').'" /> '.
	$core->formNonce().
	form::hidden(array('p'),'muppet').
	'</p>
	</form>';
}
if (!empty($counts))
{
	$line = '';
	foreach ($counts as $k => $v)
	{
		$t = ($v == 1 )? __('%s post') : __('%s posts');
		$line .= '<li>'.sprintf($t,$v).'&nbsp;'.sprintf(__('with type <strong>%s</strong>.'),$k).'</li>';
	}
	echo
	'<div class="clear col">
	<ul>'.$line.'</ul>
	</div>';
}

dcPage::helpBlock('muppet');
?>
</body>
</html>