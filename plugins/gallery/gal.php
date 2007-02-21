<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2007 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
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
if (!defined('DC_CONTEXT_ADMIN')) { exit; }


require dirname(__FILE__).'/../../inc/admin/lib.pager.php';

$gal_directory='/';
$post_id = '';
$cat_id = '';
$post_dt = '';
$post_format = $core->auth->getOption('post_format');
$post_password = '';
$post_url = '';
$post_lang = $core->auth->getInfo('user_lang');
$post_title = '';
$post_excerpt = '';
$post_excerpt_xhtml = '';
$post_content = '';
$post_content_xhtml = '';
$post_notes = '';
$post_status = $core->auth->getInfo('user_post_status');
$post_selected = false;
$post_open_comment = $core->blog->settings->allow_comments;
$post_open_tb = $core->blog->settings->allow_trackbacks;

$post_media = array();

$page_title = __('New gallery');
$params['post_type']='gal';
$can_view_page = true;
$can_edit_post = $core->auth->check('usage,gallery',$core->blog->id);
$can_publish = $core->auth->check('publish,galleryadmin',$core->blog->id);
$preview = false;

$core->media = new dcMedia($core);
$core->meta = new dcMeta($core);

$galtool = new dcGallery($core);

/*
$post_headlink = '<link rel="%s" title="%s" href="post.php?id=%s" />';
$post_link = '<a href="post.php?id=%s" title="%s">%s</a>';
*/
$next_link = $prev_link = $next_headlink = $prev_headlink = null;

$gal_headlink = '<link rel="%s" title="%s" href="plugin.php?p=gallery&m=gal&id=%s" />';
$gal_link = '<a href="plugin.php?p=gallery&m=gal&id=%s" title="%s">%s</a>';


# If user can't publish
if (!$can_publish) {
	$post_status = -2;
}

# Getting categories
$categories_combo = array('&nbsp;' => '');
try {
	$categories = $core->blog->getCategories();
	while ($categories->fetch()) {
		$categories_combo[html::escapeHTML($categories->cat_title)] = $categories->cat_id;
	}
} catch (Exception $e) { }

# Status combo
foreach ($core->blog->getAllPostStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

# Formaters combo
foreach ($core->getFormaters() as $v) {
	$formaters_combo[$v] = $v;
}


# Get entry informations
if (!empty($_REQUEST['id']))
{
	$params['post_id'] = $_REQUEST['id'];
	
	$post = $core->blog->getPosts($params);
	$post->extend(rsExtGallery);
	
	if ($post->isEmpty())
	{
		$core->error->add(__('This entry does not exist.'));
		$can_view_page = false;
	}
	else
	{
		$post_id = $post->post_id;
		$cat_id = $post->cat_id;
		$post_dt = date('Y-m-d H:i',strtotime($post->post_dt));
		$post_format = $post->post_format;
		$post_password = $post->post_password;
		$post_url = $post->post_url;
		$post_lang = $post->post_lang;
		$post_title = $post->post_title;
		$post_excerpt = $post->post_excerpt;
		$post_excerpt_xhtml = $post->post_excerpt_xhtml;
		$post_content = $post->post_content;
		$post_content_xhtml = $post->post_content_xhtml;
		$post_notes = $post->post_notes;
		$post_status = $post->post_status;
		$post_selected = (boolean) $post->post_selected;
		$post_open_comment = (boolean) $post->post_open_comment;
		$post_open_tb = (boolean) $post->post_open_tb;
		$gal_meta=$core->meta->getMetaArray($post->post_meta);
		$gal_directory=$core->meta->getMetaStr($post->post_meta,"galmediadir");
		$page_title = __('Edit gallery');
		
		$can_edit_post = $post->isEditable();
		
		$next_rs = $galtool->getNextGallery($post_id,strtotime($post_dt),1);
		$prev_rs = $galtool->getNextGallery($post_id,strtotime($post_dt),-1);
		if ($next_rs !== null) {
			echo '<p>Next:'.$next_rs->post_id.'</p>';
			$next_link = sprintf($gal_link,$next_rs->post_id,
				html::escapeHTML($next_rs->post_title),__('next gallery').'&nbsp;&#187;');
			$next_headlink = sprintf($gal_headlink,'next',
				html::escapeHTML($next_rs->post_title),$next_rs->post_id);
		}
		
		if ($prev_rs !== null) {
			echo '<p>Prev:'.$next_rs->post_id.'</p>';
			$prev_link = sprintf($gal_link,$prev_rs->post_id,
				html::escapeHTML($prev_rs->post_title),'&#171;&nbsp;'.__('previous gallery'));
			$prev_headlink = sprintf($gal_headlink,'previous',
				html::escapeHTML($prev_rs->post_title),$prev_rs->post_id);
		}
		
		try {
			$post_media = $core->media->getPostMedia($post_id);
		} catch (Exception $e) {}
	}
}


$dirs_combo = array();
foreach ($core->media->getRootDirs() as $v) {
	if ($v->w) {
	$dirs_combo['/'.$v->relname] = $v->relname;
	}
}
# Format excerpt and content
if (!empty($_POST) && $can_edit_post)
{
	$post_format = $_POST['post_format'];
	$post_excerpt = $_POST['post_excerpt'];
	$post_content = $_POST['post_content'];
	
	$post_title = $_POST['post_title'];
	
	$cat_id = (integer) $_POST['cat_id'];
	
	if (isset($_POST['post_status'])) {
		$post_status = (integer) $_POST['post_status'];
	}
	
	if (empty($_POST['post_dt'])) {
		$post_dt = '';
	} else {
		$post_dt = strtotime($_POST['post_dt']);
		$post_dt = date('Y-m-d H:i',$post_dt);
	}
	
	$post_open_comment = !empty($_POST['post_open_comment']);
	$post_open_tb = !empty($_POST['post_open_tb']);
	$post_selected = !empty($_POST['post_selected']);
	$post_lang = $_POST['post_lang'];
	$post_password = !empty($_POST['post_password']) ? $_POST['post_password'] : null;
	
	$post_notes = $_POST['post_notes'];
	$gal_directory = $_POST['p_gal_directory'];
	if (isset($_POST['post_url'])) {
		$post_url = $_POST['post_url'];
	}
	
	$core->blog->setPostContent(
		$post_id,$post_format,$post_lang,
		$post_excerpt,$post_excerpt_xhtml,$post_content,$post_content_xhtml
	);
	
	$preview = !empty($_POST['preview']);
}

# Create or update post
if (!empty($_POST) && !empty($_POST['save']) && $can_edit_post)
{
	$cur = $core->con->openCursor($core->prefix.'post');
	
	$cur->post_title = $post_title;
	$cur->cat_id = ($cat_id ? $cat_id : null);
	$cur->post_dt = $post_dt ? date('Y-m-d H:i:00',strtotime($post_dt)) : '';
	$cur->post_format = $post_format;
	$cur->post_password = $post_password;
	$cur->post_lang = $post_lang;
	$cur->post_title = $post_title;
	$cur->post_excerpt = $post_excerpt;
	$cur->post_excerpt_xhtml = $post_excerpt_xhtml;
	$cur->post_content = $post_content;
	$cur->post_content_xhtml = $post_content_xhtml;
	$cur->post_notes = $post_notes;
	$cur->post_status = $post_status;
	$cur->post_selected = (integer) $post_selected;
	$cur->post_open_comment = (integer) $post_open_comment;
	$cur->post_open_tb = (integer) $post_open_tb;
	$cur->post_type='gal';	

	if (isset($_POST['post_url'])) {
		$cur->post_url = $post_url;
	}
	
	# Update post
	if ($post_id)
	{
		try
		{
			$core->blog->updPost($post_id,$cur);
			
			/*metaBehaviors::setTags('adminAfterPostUpdate',$cur,$post_id);*/
			$core->meta->delPostMeta($post_id,"galmediadir");
			$core->meta->setPostMeta($post_id,"galmediadir",$gal_directory);
			http::redirect('plugin.php?p=gallery&m=gal&id='.$post_id.'&upd=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	else
	{
		$cur->user_id = $core->auth->userID();
		
		try
		{
		
			$return_id = $core->blog->addPost($cur);
			$core->meta->delPostMeta($return_id,"galmediadir");
			$core->meta->setPostMeta($return_id,"galmediadir",$gal_directory);
			
			http::redirect('plugin.php?p=gallery&m=gal&id='.$return_id.'&crea=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}
?>
<html>
<head>
  <title>Gallery</title>
<?php echo dcPage::jsDatePicker(); ?>  
  <?php echo dcPage::jsToolBar(); ?>
  <?php echo dcPage::jsLoad('index.php?pf=gallery/js/_gal.js')?>
  <?php echo dcPage::jsConfirmClose('entry-form'); ?>
  <?php echo dcPage::jsPageTabs('edit-entry'); ?>

  <?php echo metaBehaviors::postHeaders();?>
  
</script>
</head>
<body>
<?php
/* DISPLAY
-------------------------------------------------------- */
$default_tab = 'edit-entry';
if (!$can_edit_post || !empty($_POST['preview'])) {
	$default_tab = 'preview-entry';
}
if (!empty($_GET['co'])) {
	$default_tab = 'comments';
}

if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Entry has been successfully updated.').'</p>';
}
elseif (!empty($_GET['crea'])) {
		echo '<p class="message">'.__('Entry has been successfully created.').'</p>';
}
elseif (!empty($_GET['attached'])) {
	echo '<p class="message">'.__('File has been successfully attached.').'</p>';
}
elseif (!empty($_GET['rmattach'])) {
	echo '<p class="message">'.__('Attachment has been successfully removed.').'</p>';
}

if ($post_id)
{
	echo '<p>';
	if ($prev_link) {
		echo $prev_link.' - ';
	}
	
	if ($post->post_status == 1) {
		echo '<a href="'.$post->getURL().'">'.__('view gallery').'</a>';
	} else {
		echo __('view gallery');
	}
	
	if ($next_link) {
		echo ' - '.$next_link;
	}
	echo '</p>';
}


if ($core->error->flag()) {
	echo
	'<div class="error"><strong>'.__('Errors:').'</strong>'.
	$core->error->toHTML().
	'</div>';
}
/*
$params['post_type']='gal';
$params['post_id'] = $post_id;

$post = $core->blog->getPosts($params);

$core->media=new dcMedia($core);
$core->meta=new dcMeta($core);
$galitems = $core->media->getPostMedia($galid);

*/


/*$galitems=$galtool->getGalImageMedia(array(),$gal_id=$post_id);*/



# $gal_directory=$core->meta->getMetaStr($galmeta,"galmediadir");



echo '<h2>'.$core->blog->name.' &gt; '.$page_title.'</h2>';
# Exit if we cannot view page
if (!$can_view_page) {
	exit;
}

/* Post form if we can edit post
-------------------------------------------------------- */
if ($can_edit_post)
{
?>
<div id="edit-entry" class="multi-part" title="description">
<?php
	echo '<form action="plugin.php?p=gallery&m=gal" method="post" id="entry-form">';
	echo '<div id="entry-sidebar">'.
	
	'<p><label>'.__('Category:').dcPage::help('post','p_category').
	form::combo('cat_id',$categories_combo,$cat_id,'maximal',3).
	'</label></p>'.
	
	'<p><label>'.__('Gallery status:').dcPage::help('post','p_status').
	form::combo('post_status',$status_combo,$post_status,'',3,!$can_publish).
	'</label></p>'.
	
	'<p><label>'.__('Published on:').dcPage::help('post','p_date').
	form::field('post_dt',16,16,$post_dt,'',3).
	'</label></p>'.
	
	'<p><label>'.__('Text formating:').dcPage::help('post','p_format').
	form::combo('post_format',$formaters_combo,$post_format,'',3).
	'</label></p>'.
	
	'<p><label class="classic">'.form::checkbox('post_open_comment',1,$post_open_comment,'',3).' '.
	__('Accept comments').dcPage::help('post','p_comments').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('post_open_tb',1,$post_open_tb,'',3).' '.
	__('Accept trackbacks').dcPage::help('post','p_trackbacks').'</label></p>'.
	
/*	'<p><label>'.__('Entry password:').dcPage::help('post','p_password').
	form::field('post_password',10,32,html::escapeHTML($post_password),'maximal',3).
	'</label></p>'.*/
	
	'<div class="lockable">'.
	'<p><label>'.__('Basename:').dcPage::help('post','p_basename').
	form::field('post_url',10,255,html::escapeHTML($post_url),'maximal',3).
	'</label></p>'.
	'<p class="form-note warn">'.
	__('Warning: If you set the URL manually, it may conflict with another entry.').
	'</p>'.
	'</div>'.
	
	'<p><label>'.__('Entry lang:').dcPage::help('post','p_lang').
	form::field('post_lang',5,255,html::escapeHTML($post_lang),'',3).
	'</label></p>';
	if (isset($post))
		metaBehaviors::tagsField($post);

	echo '</div>';		// End #entry-sidebar
	
	echo '<div id="entry-content"><fieldset class="constrained">';
	
	echo
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').
	dcPage::help('post','p_title').
	form::field('post_title',20,255,html::escapeHTML($post_title),'maximal',2).
	'</label></p>'.
	
	'<p><label>'.__('Media Directory:').dcPage::help('post','p_gal_directory').
	form::combo('p_gal_directory',$dirs_combo,$gal_directory,'maximal',3).
	'</label></p>'.

	'<p class="area" id="excerpt-area"><label for="post_excerpt">'.__('Excerpt:').
	dcPage::help('post','p_excerpt').'</label> '.
	form::textarea('post_excerpt',50,5,html::escapeHTML($post_excerpt),'',2).
	'</p>'.
	
	'<p class="area"><label class="required" title="'.__('Required field').'" '.
	'for="post_content">'.__('Content:').
	dcPage::help('post','p_content').'</label> '.
	form::textarea('post_content',50,$core->auth->getOption('edit_size'),html::escapeHTML($post_content),'',2).
	'</p>'.
	
	'<p class="area" id="notes-area"><label>'.__('Notes:').dcPage::help('post','p_notes').'</label>'.
	form::textarea('post_notes',50,5,html::escapeHTML($post_notes),'',2).
	'</p>';
	
	echo
	'<p>'.
	($post_id ? form::hidden('id',$post_id) : '').
	'<input type="submit" value="'.__('save').' (s)" tabindex="4" '.
	'accesskey="s" name="save" /> '.
	'<input type="submit" value="'.__('preview').' (p)" tabindex="4" '.
	'accesskey="p" name="preview" />'.
	'</p>';
	
	echo '</fieldset></div>';		// End #entry-content
	echo '</form>';
	echo '</div>';
	
	/*if ($post_id && $post->post_status == 1) {
		echo '<br /><p><a href="trackbacks.php?id='.$post_id.'" class="multi-part">'.
		__('Ping blogs').'</a></p>';
	}*/
	
	if ($post_id && !empty($post_media))
	{
		echo
		'<form action="post_media.php" id="attachment-remove-hide" method="post">'.
		'<div>'.form::hidden(array('post_id'),$post_id).
		form::hidden(array('media_id'),'').
		form::hidden(array('remove'),1).'</div></form>';
	}
} // if canedit post
?>


<?php 
if ($post_id) {
	echo '<br /><p><a href="plugin.php?p=gallery&amp;m=galitemlist&amp;id='.$post_id.'" class="multi-part">'.
		__('Items').'</a></p>';
}
?>

<?php 
if ($post_id) {
	echo '<br /><p><a href="plugin.php?p=gallery&amp;m=galupdate&amp;id='.$post_id.'" class="multi-part">'.
		__('Maintenance').'</a></p>';
}

?>
</body>
</html>--

