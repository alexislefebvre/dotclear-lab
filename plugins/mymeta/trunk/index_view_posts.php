<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Mymeta plugin for DC2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { return; }

if (empty($_GET['id']) || empty($_GET['value'])) {
	http::redirect($p_url);
	exit;
}

$mymetaEntry = $mymeta->getByID($_GET['id']);
if ($mymetaEntry == null) {
	http::redirect($p_url);
	exit;
}

$value = rawurldecode($_GET['value']);

$this_url = $p_url.'&amp;m=viewposts&amp;id='.$mymetaEntry->id.'&amp;value='.$value;


$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  30;

# Rename a tag
if (!empty($_POST['rename']))
{
	$new_value = $_POST['mymeta_'.$mymetaEntry->id];
	try {
		if ($core->meta->updateMeta($value,$new_value,$mymetaEntry->id)) {
			http::redirect($p_url.'&m=view&id='.$mymetaEntry->id);
		}
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Delete a tag
if (!empty($_POST['delete']) && $core->auth->check('publish,contentadmin',$core->blog->id))
{
	try {
		/*$core->meta->delMeta($tag,'tag');
		http::redirect($p_url.'&m=tags&del=1');*/
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);
$params['no_content'] = true;

$params['meta_id'] = $value;
$params['meta_type'] = $mymetaEntry->id;;
$params['post_type'] = '';

# Get posts
try {
	$posts = $core->meta->getPostsByMeta($params);
	$counter = $core->meta->getPostsByMeta($params,true);
	$post_list = new adminPostList($core,$posts,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Actions combo box
$combo_action = array();
if ($core->auth->check('publish,contentadmin',$core->blog->id))
{
	$combo_action[__('Status')] = array(
		__('Publish') => 'publish',
		__('Unpublish') => 'unpublish',
		__('Schedule') => 'schedule',
		__('Mark as pending') => 'pending'
	);
}
$combo_action[__('Mark')] = array(
	__('Mark as selected') => 'selected',
	__('Mark as unselected') => 'unselected'
);
$combo_action[__('Change')] = array(__('Change category') => 'category');
if ($core->auth->check('admin',$core->blog->id))
{
	$combo_action[__('Change')] = array_merge($combo_action[__('Change')],
		array(__('Change author') => 'author'));
}
if ($core->auth->check('delete,contentadmin',$core->blog->id))
{
	$combo_action[__('Delete')] = array(__('Delete') => 'delete');
}

# --BEHAVIOR-- adminPostsActionsCombo
$core->callBehavior('adminPostsActionsCombo',array(&$combo_action));

?>
<html>
<head>
  <title>MyMeta</title>
  <link rel="stylesheet" type="text/css" href="index.php?pf=tags/style.css" />
  <script type="text/javascript" src="js/_posts_list.js"></script>
  <script type="text/javascript">
  //<![CDATA[
  dotclear.msg.confirm_tag_delete = '<?php echo html::escapeJS(sprintf(__('Are you sure you want to remove this %s?'),'tag')) ?>';
  $(function() {
    $('#tag_delete').submit(function() {
      return window.confirm(dotclear.msg.confirm_tag_delete);
    });
  });
  //]]>
  </script>
  <?php 
	echo dcPage::jsPageTabs('mymeta');
	echo $mymetaEntry->postHeader(null,true);
  
  ?>
  </head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &rsaquo;
<?php echo __('Edit MyMeta'); ?></h2>

<?php
if (!empty($_GET['renamed'])) {
	echo '<p class="message">'.__('MyMeta has been successfully renamed').'</p>';
}

echo '<p><a href="'.$p_url.'" class="multi-part">'.__('My metadata').'</a></p>';
echo '<p><a href="'.$p_url.'&amp;m=view&amp;id='.$mymetaEntry->id.'" class="multi-part">'.__('Metadata').' : '.html::escapeHTML($mymetaEntry->id).'</a></p>';
echo '<div class="multi-part" id="mymeta" title="'.__('MyMeta Posts').' : '.html::escapeHTML($mymetaEntry->id).'">';

if (!$core->error->flag())
{
	echo '<h3>'.__('Current value :').' '.html::escapeHTML($value).'</h3>';
	if (!$posts->isEmpty())
	{
		echo
		'<fieldset><legend>'.__('Change MyMeta value').'</legend><form action="'.$this_url.'" method="post">'.
		$mymetaEntry->postShowForm($mymeta, null, html::escapeHTML($value)).
		'<p><input type="submit" name="rename" value="'.__('save').'" />'.
		form::hidden(array('value'),html::escapeHTML($value)).
		$core->formNonce().
		'</p></form></fieldset>';
	}
	
	# Show posts
	$post_list->display($page,$nb_per_page,
	'<form action="posts_actions.php" method="post" id="form-entries">'.
	
	'%s'.
	
	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.
	
	'<p class="col right">'.__('Selected entries action:').' '.
	form::combo('action',$combo_action).
	'<input type="submit" value="'.__('ok').'" /></p>'.
	form::hidden('post_type','').
	form::hidden('redir',$p_url.'&amp;m=view&amp;id='.
		$mymetaEntry->id.'&amp;page='.$page).
	$core->formNonce().
	'</div>'.
	'</form>');
	
	# Remove tag
	if (!$posts->isEmpty() && $core->auth->check('contentadmin',$core->blog->id)) {
		echo
		'<form id="tag_delete" action="'.$this_url.'" method="post">'.
		'<p><input type="submit" name="delete" value="'.__('Delete this tag').'" />'.
		$core->formNonce().'</p>'.
		'</form>';
	}
}
echo '</div>';
?>
</body>
</html>
