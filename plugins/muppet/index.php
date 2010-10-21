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
$integration = $feed = $blogmenu = false;
$yes = '<img class="icon" alt="'.__('available').'" title="'.__('available').'" src="images/check-on.png" />';
$no = '<img class="icon" alt="'.__('unavailable').'" title="'.__('unavailable').'" src="images/check-off.png" />';

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

for ($i = 1; $i <= 69; $i++) {
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
	$blogmenu = isset($_POST['blogmenu'])? true : false;

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
		'feed' => $feed,
		'blogmenu' => $blogmenu
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
		$blogmenu = (boolean) $my_types[$edit]['blogmenu'];
		$legend = __('Modify a post type');
		$label_add = __('save');
	}
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
	input.delete {color:#cc0000;}
	dl.list dd{margin-left:1em;padding:5px;}
	#icon,#icon option {background-color:transparent;
		background-repeat:no-repeat;background-position:4% 50%;
		padding:1px 1px 1px 16px;color:#444;}
</style>
</head>
<body>
<?php
echo $msg;

echo
'<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Supplementary post types');
if (!$add_type) {
	echo ' &rsaquo; <a class="button" id="muppet-control" href="#">'.
	__('New post type').'</a>';
}
echo '</h2>';

echo
'<div class="clear">
<form action="'.$p_url.'" method="post" id="add-post-type">
<fieldset>
<legend>'.$legend .'</legend>
<div class="two-cols"><div class="col">
<p class="field"><label for ="#icon" class=" required" title="'.__('Required field').'">'.__('Image:').' '.
form::combo('icon',$icons,$icon).'</label></p>
<p class="field"><label for="#newtype" class="required " title="'.__('Required field').'">'.__('Type:').' '.
form::field('newtype',30,255,$newtype).'</label></p>
<p class="field"><label for="#name" class="required " title="'.__('Required field').'">'.__('Name:').' '.
form::field('name',30,255,$name).'</label></p>
<h3>'.__('Blog').'</h3>
<h4>'.__('Integration').'</h4>
<p class="field"><label for="#integration">'.__('Blog content:').
form::checkbox('integration','1',$integration).'</label></p>
<p class="field"><label for="#feed" class="classic" >'.__('Feeds:').
form::checkbox('feed','2',$feed).'</label></p></div>
<div class="col">
<h3>'.__('Administration').'</h3>
<h4>'.__('Dashboard menu').'</h4>
<p class="field"><label for="#blogmenu" class="classic" >'.__('Blog menu:').
form::checkbox('blogmenu','3',$blogmenu).'</label></p>
<h4>'.__('Miscellaneous').'</h4>
<p class="field"><label for="#plural" class="classic">'.__('Plural form:').' '.
form::field('plural',30,255,$plural).'</label></p>
<p class="field"><label for="#urlformat" class="classic" >'.__('New post URL format:').
form::field('urlformat',30,255,$post_url).'</label></p>
<p class="form-note">'.__('{y}: year, {m}: month, {d}: day, {id}: post id, {t}: entry title').'</p></div></div>
<p class="clear">'.form::hidden(array('p'),'muppet').
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
	echo '<table class="maximal clear">
		<tr><th>'.__('Type').'</th>
		<th>'.__('Name').'</th>
		<th>'.__('Entries').'</th>
		<th>'.__('Menu').'</th>
		<th class="nowrap">'.__('URL format').'</th>
		<th class="nowrap">'.__('Blog content').'</th>
		<th class="nowrap">'.__('Feeds').'</th></tr>';

	foreach ($my_types as $k => $v)
	{
		$plural = empty($v['plural']) ? $v['name'].'s' : $v['plural'];
		$redir = 'plugin.php?p=muppet&amp;edit=';
		$content = $v['integration'] ? $yes : $no;
		$feeds = $v['feed'] ? $yes : $no;
		$menu = $v['blogmenu'] ? __('Blog') : __('Content');
		$link_muppet = '<a class="muppet status" href="plugin.php?p=muppet&amp;type=%s&amp;list=all">%s</a>';
		try {
			$counter = $core->blog->getPosts(array('post_type'=>$k),true);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
		}

		if ($counter->f(0) == 0) {
			$count = $counter->f(0);
		} else {
			$count = sprintf($link_muppet,$k,$counter->f(0));
		}

		echo
		'<tr class="muppet-item">
		<td class="maximal"><a class="media-icon" href="'.$redir.$k.'" title="'.__('edit this post type').'" ><img src="index.php?pf=muppet/img/'.$v['icon'].'" alt="'.$v['icon'].'" /></a>&nbsp;

		<a href="'.$redir.$k.'" title="'.__('edit this post type').'" >'.$k.'</a> </td>
		<td class="nowrap">'.$v['name'].' ('.$plural.')</td>
		<td class="nowrap">'.$count.'</td>
		<td class="nowrap">'.$menu.'</td>
		<td><code>'.$v['urlformat'].'</code></td>
		<td>'.$content.'</td>
		<td>'.$feeds.'</td>
		</tr>';
	}
	echo '</table>';
}

if (empty($counts))
{
	echo
	'<form action="'.$p_url.'" method="post" id="get-infos">
	<p class="clear right"><input type="submit" name="getinfo" value="'.__('statistics').'" /> '.
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
	<h3>'.__('statistics').'</h3>
	<ul>'.$line.'</ul>
	</div>';
}

dcPage::helpBlock('muppet');
?>
</body>
</html>
