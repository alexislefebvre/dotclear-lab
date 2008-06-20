<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 2 Gallery plugin.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { exit; }
$core->meta=new dcMeta($core);
$core->gallery=new dcGallery($core);

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
		$redir =
		'plugin.php?p=gallery&m=items&user_id='.$_POST['user_id'].
		'&cat_id='.$_POST['cat_id'].
		'&status='.$_POST['status'].
		'&selected='.$_POST['selected'].
		'&month='.$_POST['month'].
		'&lang='.$_POST['lang'].
		'&sortby='.$_POST['sortby'].
		'&order='.$_POST['order'].
		'&gal_id='.$_POST['gal_id'].
		'&media_dir='.$_POST['media_dir'].
		'&tag='.$_POST['tag'].
		'&page='.$_POST['page'].
		'&nb='.$_POST['nb'];
	}
	
	foreach ($entries as $k => $v) {
		$entries[$k] = (integer) $v;
	}
	
	$params['sql'] = 'AND P.post_id IN('.implode(',',$entries).') ';
	$params['no_content'] = true;
	
	$posts = $core->gallery->getGalItems($params);
	
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
	elseif ($action == 'removeimgpost')
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
				throw new Exception(__('This user does not exists'));
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
	elseif ($action == 'tags' && !empty($_POST['new_tags']))
	{
		try
		{
			$meta = new dcMeta($core);
			$tags = $meta->splitMetaValues($_POST['new_tags']);
			
			while ($posts->fetch())
			{
				# Get tags for post
				$post_meta = $meta->getMeta('tag',null,null,$posts->post_id);
				$pm = array();
				while ($post_meta->fetch()) {
					$pm[] = $post_meta->meta_id;
				}
				
				foreach ($tags as $t) {
					if (!in_array($t,$pm)) {
						$meta->setPostMeta($posts->post_id,'tag',$t);
					}
				}
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
?>
<html>
<head>
  <title><?php echo __('Entries'); ?></title>
</head>
<body>

<h2><?php echo html::escapeHTML($core->blog->name); ?> &gt;
<?php
if (!isset($action)) {
?>
</body>
</html>
<?php
	dcPage::close();
	exit;
}

$hidden_fields = '';
while ($posts->fetch()) {
	$hidden_fields .= form::hidden(array('entries[]'),$posts->post_id);
}

if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false)
{
	$hidden_fields .= form::hidden(array('redir'),$_POST['redir']);
}
else
{
	$hidden_fields .=
	form::hidden(array('user_id'),$_POST['user_id']).
	form::hidden(array('cat_id'),$_POST['cat_id']).
	form::hidden(array('status'),$_POST['status']).
	form::hidden(array('selected'),$_POST['selected']).
	form::hidden(array('month'),$_POST['month']).
	form::hidden(array('lang'),$_POST['lang']).
	form::hidden(array('sortby'),$_POST['sortby']).
	form::hidden(array('order'),$_POST['order']).
	form::hidden(array('gal_id'),$_POST['gal_id']).
	form::hidden(array('media_dir'),$_POST['media_dir']).
	form::hidden(array('tag'),$_POST['tag']).
	form::hidden(array('page'),$_POST['page']).
	form::hidden(array('nb'),$_POST['nb']);
}


if ($action == 'category')
{
	echo __('Change category for entries').'</h2>';
	
	# categories list
	# Getting categories
	$categories_combo = array('&nbsp;' => '');
	try {
		$categories = $core->blog->getCategories();
		while ($categories->fetch()) {
			$categories_combo[$categories->cat_title] = $categories->cat_id;
		}
	} catch (Exception $e) { }
	
	echo
	'<form action="plugin.php?p=gallery&m=itemsactions" method="post">'.
	'<p><label class="classic">'.__('Category:').' '.
	form::combo('new_cat_id',$categories_combo,'').
	'</label> ';
	
	echo
	$core->formNonce().
	$hidden_fields.
	form::hidden(array('action'),'category').
	'<input type="submit" value="'.__('save').'" /></p>'.
	'</form>';
}
elseif ($action == 'author' && $core->auth->check('admin',$core->blog->id))
{
	echo __('Change author for entries').'</h2>';
	
	echo
	'<form action="plugin.php?p=gallery&m=itemsactions" method="post">'.
	'<p><label class="classic">'.__('Author ID:').' '.
	form::field('new_auth_id',20,255).
	'</label> ';
	
	echo
	$core->formNonce().
	$hidden_fields.
	form::hidden(array('action'),'author').
	'<input type="submit" value="'.__('save').'" /></p>'.
	'</form>';
}
elseif ($action == 'tags')
{
	echo
	'<h2>'.__('Add tags to entries').'</h2>'.
	'<form action="plugin.php?p=gallery&m=itemsactions" method="post">'.
	'<p><label class="area">'.__('Tags to add:').' '.
	form::textarea('new_tags',60,3).
	'</label> '.
	
	$core->formNonce().
	$hidden_fields.
	form::hidden(array('action'),'tags').
	'<input type="submit" value="'.__('save').'" /></p>'.
	'</form>';
}

echo '<p><a href="'.str_replace('&','&amp;',$redir).'">'.__('back').'</a></p>';





?>
</body>
</html>
