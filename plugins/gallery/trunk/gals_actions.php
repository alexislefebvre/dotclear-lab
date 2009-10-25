<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

dcPage::check('usage,contentadmin');

$core->gallery = new dcGallery($core);

$params = array();

/* Actions
-------------------------------------------------------- */
if (!empty($_POST['action']) && !empty($_POST['entries']))
{
	$entries = $_POST['entries'];
	$action = $_POST['action'];
	
	if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false)
	{
		$redir = $_POST['redir'];
	}
	else
	{
		$redir = 'plugin.php?p=gallery'.
		'&page='.$_POST['page'];
	}
	
	foreach ($entries as $k => $v) {
		$entries[$k] = (integer) $v;
	}
	
	$params['sql'] = 'AND P.post_id IN('.implode(',',$entries).') ';
	$params['no_content'] = true;
	
	$posts = $core->gallery->getGalleries($params);
	
	# --BEHAVIOR-- adminPostsActions
	$core->callBehavior('adminGalleriesActions',$core,$posts,$action,$redir);
	
	if (preg_match('/^(publish|unpublish|schedule|pending)$/',$action))
	{
		switch ($action) {
			case 'unpublish' : $status = 0; break;
			case 'schedule' : $status = -1; break;
			case 'pending' : $status = -2; break;
			default : $status = 1; break;
		}
		
		try
		{
			while ($posts->fetch()) {
				$core->blog->updPostStatus($posts->post_id,$status);
			}
			
			http::redirect($redir);
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	elseif ($action == 'delete')
	{
		try
		{
			while ($posts->fetch()) {
				$core->blog->delPost($posts->post_id);
			}
			
			http::redirect($redir);
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
		
	}
	elseif ($action == 'selected' || $action == 'unselected')
	{
		try
		{
			while ($posts->fetch()) {
				$core->blog->updPostSelected($posts->post_id,$action == 'selected');
			}
			
			http::redirect($redir);
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	elseif ($action == 'category' && isset($_POST['new_cat_id']))
	{
		try
		{
			while ($posts->fetch())
			{
				$new_cat_id = (integer) $_POST['new_cat_id'];
				$core->blog->updPostCategory($posts->post_id,$new_cat_id);
			}
			http::redirect($redir);
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	elseif ($action == 'author' && isset($_POST['new_auth_id'])
	&& $core->auth->check('admin',$core->blog->id))
	{
		$new_user_id = $_POST['new_auth_id'];
		
		try
		{
			if ($core->getUser($new_user_id)->isEmpty()) {
				throw new Exception(__('This user does not exist'));
			}
			
			while ($posts->fetch())
			{
				$cur = $core->con->openCursor($core->prefix.'post');
				$cur->user_id = $new_user_id;
				$cur->update('WHERE post_id = '.(integer) $posts->post_id);
			}
			
			http::redirect($redir);
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}

/* DISPLAY
-------------------------------------------------------- */

if (!isset($action)) {
	exit;
}
?>
<html>
<head>
  <title><?php echo __('Galleries'); ?></title>
  <?php
  	if ($action == 'update') {
	   echo dcPage::jsLoad('index.php?pf=gallery/js/_sequential_ajax.js').
	   		dcPage::jsLoad('index.php?pf=gallery/js/_gals_actions.js').
			 dcPage::jsPageTabs("new_items");
		echo 
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		"var gals = [".implode(',',$entries)."];\n".
		"dotclear.msg.refresh_gallery = '".html::escapeJS(__('Refresh gallery'))."';\n".
		"\n//]]>\n".
		"</script>\n";
	}
  ?>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
<?php


$hidden_fields = '';
while ($posts->fetch()) {
	$hidden_fields .= form::hidden(array('entries[]'),$posts->post_id);
}

if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false)
{
	$hidden_fields .= form::hidden(array('redir'),$_POST['redir']);
}
# --BEHAVIOR-- adminPostsActionsContent
$core->callBehavior('adminPostsActionsContent',$core,$action,$hidden_fields);

if ($action == 'category')
{
	echo __('Change category for entries').'</h2>';
	
	# categories list
	# Getting categories
	$categories_combo = array('&nbsp;' => '');
	try {
		$categories = $core->blog->getCategories();
		while ($categories->fetch()) {
			$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
				html::escapeHTML($categories->cat_title)] = $categories->cat_id;
		}
	} catch (Exception $e) { }
	
	echo
	'<form action="plugin.php?p=gallery&amp;m=galsactions" method="post">'.
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
elseif ($action == 'author' && $core->auth->check('admin',$core->blog->id))
{
	echo __('Change author for entries').'</h2>';
	
	echo
	'<form action="plugin.php?p=gallery&amp;m=galsactions" method="post">'.
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
elseif ($action == 'update') {
	echo __('Update galeries').'</h2>';
	echo '<fieldset><legend>'.__('Processing result').'</legend>';
	echo '<p><input type="button" id="cancel" value="'.__('cancel').'" /></p>';
	echo '<h3>'.__('Actions').'</h3>';
	echo '<table id="process"><tr class="keepme"><th>ID</th><th>Action</th><th>Status</th></tr></table>';
	echo '</fieldset>';
}

echo '<p><a href="'.str_replace('&','&amp;',$redir).'">'.__('back').'</a></p>';

?>
</body>
</html>