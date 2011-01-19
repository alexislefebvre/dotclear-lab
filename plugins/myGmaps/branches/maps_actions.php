<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of myGmaps, a plugin for Dotclear 2.
#
# Copyright (c) 2010 Philippe aka amalgame
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$entries	= isset($_POST['entries']) ? $_POST['entries'] : array();
$action	= isset($_POST['action']) ? $_POST['action'] : '';
$redir	= isset($_POST['redir']) ? $_POST['redir'] : $p_url;
		
foreach ($entries as $k => $v) {
	$entries[$k] = (integer) $v;
}

$params['sql'] = 'AND P.post_id IN('.implode(',',$entries).') ';
$params['post_type'] = 'map';
$params['no_content'] = true;

$posts = $core->blog->getPosts($params);

foreach ($filters as $k) {
	if (array_key_exists($k,$_POST)) {
		$redir .= sprintf('&%s=%s',$k,$_POST[$k]);
	}
}

/* Actions
-------------------------------------------------------- */
if (!empty($action) && count(entries) > 0)
{
	try {
		$act = null;
		# Change map posts status
		if ($action === 'published' || $action === 'pending' || $action === 'unpublished') {
			foreach ($entries as $id) {
				switch ($action) {
					case 'published' : $status = 1; break;
					case 'pending' : $status = -2; break;
					case 'unpublished' : $status = 0; break;
					default : $status = 1; break;
				}
				$core->blog->updPostStatus($id,$status);
			}
			$act = 1;
		}
		# Change map posts category
		if ($action == 'category' && isset($_POST['new_cat_id']))
		{
			while ($posts->fetch()) {
				$new_cat_id = (integer) $_POST['new_cat_id'];
				$core->blog->updPostCategory($posts->post_id,$new_cat_id);
			}
			$act = 2;
		}
		# Change map posts authors
		if ($action == 'author' && isset($_POST['new_auth_id'])
		&& $core->auth->check('admin',$core->blog->id))
		{
			$new_user_id = $_POST['new_auth_id'];
			
			if ($core->getUser($new_user_id)->isEmpty()) {
				throw new Exception(__('This user does not exist'));
			}
			
			while ($posts->fetch())
			{
				$cur = $core->con->openCursor($core->prefix.'post');
				$cur->user_id = $new_user_id;
				$cur->update('WHERE post_id = '.(integer) $posts->post_id);
			}
			$act = 3;
		}
		# Delete map posts
		if ($action === 'delete') {
			foreach ($entries as $id) {
				# --BEHAVIOR-- adminBeforePostDelete
				$core->callBehavior('adminBeforePostDelete',$id);
				$core->blog->delPost($id);
			}
			$act = 4;
		}
		
		if (!is_null($act)) {
			http::redirect($redir.'&act='.$act);
		}
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# --BEHAVIOR-- adminPostsActions
$core->callBehavior('adminPostsActions',$core,$posts,$action,$p_url);

/* DISPLAY
-------------------------------------------------------- */

echo
'<html>'.
'<head>'.
	'<title>'.__('Google Maps').'</title>'.
	dcPage::jsMetaEditor();
	# --BEHAVIOR-- adminBeforePostDelete
	$core->callBehavior('adminPostsActionsHeaders');
echo
'</head>'.
'<body>';

$hidden_fields = form::hidden('redir',$redir);
while ($posts->fetch()) {
	$hidden_fields .= form::hidden(array('entries[]'),$posts->post_id);
}

# --BEHAVIOR-- adminPostsActionsContent
$core->callBehavior('adminPostsActionsContent',$core,$action,$hidden_fields);

if ($action === 'category')
{
	echo
	'<h2>'.
		html::escapeHTML($core->blog->name).' &rsaquo; '.
		'<a href="'.$p_url.'">'.__('Google Maps').'</a> &rsaquo; '.
		__('Change category for map elements').
	'</h2>';
	
	# categories list
	# Getting categories
	$categories_combo = array('&nbsp;' => '');
	try {
		$categories = $core->blog->getCategories(array('post_type'=>'post'));
		while ($categories->fetch()) {
			$categories_combo[] = new formSelectOption(
				str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.html::escapeHTML($categories->cat_title),
				$categories->cat_id
			);
		}
	} catch (Exception $e) { }
	
	echo
	'<form action="'.$p_url.'&amp;go=maps_actions" method="post">'.
	'<p><label class="classic">'.__('Category:').' '.
	form::combo('new_cat_id',$categories_combo,'').
	'</label> ';
	
	echo
	$hidden_fields.
	$core->formNonce().
	form::hidden(array('action'),'category').
	'<input type="submit" value="'.__('save').'" /></p>'.
	'</form>';
}
elseif ($action === 'author' && $core->auth->check('admin',$core->blog->id))
{
	echo
	'<h2>'.
		html::escapeHTML($core->blog->name).' &rsaquo; '.
		'<a href="'.$p_url.'">'.__('Google Maps').'</a> &rsaquo; '.
		__('Change author for map elements').
	'</h2>';
	
	echo
	'<form action="'.$p_url.'&amp;go=maps_actions" method="post">'.
	'<p><label class="classic">'.__('Author ID:').' '.
	form::field('new_auth_id',20,255).
	'</label> ';
	
	echo
	$hidden_fields.
	$core->formNonce().
	form::hidden(array('action'),'author').
	'<input type="submit" value="'.__('save').'" /></p>'.
	'</form>';
}

$posts_list = '';

while ($posts->fetch()) {
	$link = sprintf('<a href="%s">%s</a>',$p_url.'&amp;go=map&amp;id='.$posts->post_id,$posts->post_title);
	$posts_list .= sprintf('<li>#%s - %s</li>',$posts->post_id,$link);	
}

if ($posts_list !== '') {
	echo sprintf('<p><ul>%s</ul></p>',$posts_list);
}

echo
'<p><a class="back" href="'.html::escapeURL($p_url).'">'.__('back').'</a></p>'.
'</body>'.
'</html>';

?>