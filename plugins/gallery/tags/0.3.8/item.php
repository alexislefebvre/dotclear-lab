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

$params['post_type']='galitem';
$can_view_page = true;
$can_edit_post = $core->auth->check('usage,contentadmin',$core->blog->id);
$can_publish = $core->auth->check('publish,contentadmin',$core->blog->id);

$core->media = new dcMedia($core);
$core->meta = new dcMeta($core);

$core->gallery = new dcGallery($core);

/*
$post_headlink = '<link rel="%s" title="%s" href="post.php?id=%s" />';
$post_link = '<a href="post.php?id=%s" title="%s">%s</a>';
*/
$next_link = $prev_link = $next_headlink = $prev_headlink = null;

$item_headlink = '<link rel="%s" title="%s" href="plugin.php?p=gallery&amp;m=item&amp;id=%s" />';
$item_link = '<a href="plugin.php?p=gallery&amp;m=item&amp;id=%s" title="%s">%s</a>';


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


if (empty($_REQUEST['id'])) {
	$core->error->add(__('This entry does not exist.'));
	$can_view_page = false;
} else {
	$params['post_id'] = $_REQUEST['id'];
	
	$post = $core->gallery->getGalImageMedia($params);
	/*$post->extend(rsExtImage);*/
	
	if ($post->isEmpty())
	{
		$core->error->add(__('This entry does not exist.'));
		$can_view_page = false;
	}
	else
	{
		$media=$core->media->getFile($post->media_id);
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
		if (trim($post_content) === "///html\n<p></p>\n///" || trim($post_content) == '')
			$post_content = '';
		$post_content_xhtml = $post->post_content_xhtml;
		if (trim($post_content_xhtml) === '<p></p>' || trim($post_content_xhtml) == '')
			$post_content_xhtml = '';
		$post_notes = $post->post_notes;
		$post_status = $post->post_status;
		$post_selected = (boolean) $post->post_selected;
		$post_open_comment = (boolean) $post->post_open_comment;
		$post_open_tb = (boolean) $post->post_open_tb;
		$page_title = __('Edit image');
		
		$can_edit_post = $post->isEditable();
		$img_gals = $core->gallery->getImageGalleries($post_id);
		
		$next_rs = $core->gallery->getNextGalleryItem($post,1);
		$prev_rs = $core->gallery->getNextGalleryItem($post,-1);
		if ($next_rs !== null) {
			$next_link = sprintf($item_link,$next_rs->post_id,
				html::escapeHTML($next_rs->post_title),__('next item').'&nbsp;&#187;');
			$next_headlink = sprintf($item_headlink,'next',
				html::escapeHTML($next_rs->post_title),$next_rs->post_id);
		}
		
		if ($prev_rs !== null) {
			$prev_link = sprintf($item_link,$prev_rs->post_id,
				html::escapeHTML($prev_rs->post_title),'&#171;&nbsp;'.__('previous item'));
			$prev_headlink = sprintf($item_headlink,'previous',
				html::escapeHTML($prev_rs->post_title),$prev_rs->post_id);
		}
		
	}
}


# Format excerpt and content
if (!empty($_POST) && $can_edit_post)
{
	$post_format = $_POST['post_format'];
	$post_excerpt = $_POST['post_excerpt'];
	$post_content = $_POST['post_content'];

	/* Enable null post content */
	if (trim($post_content)==='')
		$post_content="///html\n<p></p>\n///";
	
	if (trim($post_content_xhtml)==='')
		$post_content_xhtml="<p></p>";
	
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
	if (isset($_POST['post_url'])) {
		$post_url = $_POST['post_url'];
	}
	
	$core->blog->setPostContent(
		$post_id,$post_format,$post_lang,
		$post_excerpt,$post_excerpt_xhtml,$post_content,$post_content_xhtml
	);
	
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
	$cur->post_type='galitem';	

	if (isset($_POST['post_url'])) {
		$cur->post_url = $post_url;
	}
	
	# Update post
	if ($post_id)
	{
		try
		{
			# --BEHAVIOR-- adminBeforeGalleryItemUpdate
			$core->callBehavior('adminBeforeGalleryItemUpdate',$cur,$post_id);
			
			$core->blog->updPost($post_id,$cur);
			
			metaBehaviors::setTags($cur,$post_id);

			# --BEHAVIOR-- adminBeforeGalleryItemUpdate
			$core->callBehavior('adminAfterGalleryItemUpdate',$cur,$post_id);
			
			http::redirect('plugin.php?p=gallery&m=item&id='.$post_id.'&upd=1');
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
		
			# --BEHAVIOR-- adminBeforeGalleryItemCreate
			$core->callBehavior('adminBeforeGalleryItemCreate',$cur);
		
			$return_id = $core->blog->addPost($cur);
			
			# --BEHAVIOR-- adminAfterGalleryItemCreate
			$core->callBehavior('adminAfterGalleryItemCreate',$cur,$return_id);
		
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
<?php echo dcPage::jsDatePicker().
	dcPage::jsToolBar().
	dcPage::jsModal().
	dcPage::jsLoad('index.php?pf=gallery/js/_item.js').
	dcPage::jsLoad('index.php?pf=gallery/js/posttag.js').
	dcPage::jsConfirmClose('entry-form').
	dcPage::jsPageTabs('edit-entry').
	metaBehaviors::postHeaders().
	$core->callBehavior('adminGalleryItemHeaders'); ?>

  <link rel="stylesheet" type="text/css" href="index.php?pf=gallery/admin_css/style.css" />

  
</script>
</head>
<body>
<?php
/* DISPLAY
-------------------------------------------------------- */
$default_tab = 'edit-entry';
if (!$can_edit_post) {
	$default_tab = '';
}
if (!empty($_GET['co'])) {
	$default_tab = 'comments';
}

if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('The image has been successfully updated.').'</p>';
}
elseif (!empty($_GET['crea'])) {
		echo '<p class="message">'.__('The image has been successfully created.').'</p>';
}
elseif (!empty($_GET['attached'])) {
	echo '<p class="message">'.__('File has been successfully attached.').'</p>';
}
elseif (!empty($_GET['rmattach'])) {
	echo '<p class="message">'.__('Attachment has been successfully removed.').'</p>';
}

# XHTML conversion
if (!empty($_GET['xconv']))
{
	$post_excerpt = $post_excerpt_xhtml;
	$post_content = $post_content_xhtml;
	$post_format = 'xhtml';
	
	echo '<p class="message">'.__('Don\'t forget to validate your XHTML conversion by saving your post.').'</p>';
}


if ($post_id)
{
	echo '<p>';
	if ($prev_link) {
		echo $prev_link.' - ';
	}
	if ($post->post_status == 1) {
		echo '<a id="post-preview" href="'.$post->getURL().'" class="button">'.__('view item').'</a>';
	} else {
		$preview_url =
		$core->blog->url.$core->url->getBase('imagepreview').'/'.
		$core->auth->userID().'/'.
		http::browserUID(DC_MASTER_KEY.$core->auth->userID().$core->auth->getInfo('user_pwd')).
		'/'.$post->post_url;
		echo '<a id="post-preview" href="'.$preview_url.'" class="button">'.__('Preview image').'</a>';
	}
	
	if ($next_link) {
		echo ' - '.$next_link;
	}

	# --BEHAVIOR-- adminGalleryItemNavLinks
	$core->callBehavior('adminGalleryItemNavLinks',isset($post) ? $post : null);
	
	echo '</p>';
}


if ($core->error->flag()) {
	echo
	'<div class="error"><strong>'.__('Errors:').'</strong>'.
	$core->error->toHTML().
	'</div>';
}

echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('Galleries').' &gt; '.__('Entries').' &gt; '.$page_title.'</h2>';
# Exit if we cannot view page
if (!$can_view_page) {
	exit;
}
echo '<p><a href="plugin.php?p=gallery" class="multi-part">'.__('Galleries').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=items" class="multi-part">'.__('Images').'</a></p>';
echo '<div id="edit-entry" class="multi-part" title="'. __('Image').'">';
echo "<fieldset><legend>".__('Information')."</legend>";
echo '<div class="three-cols">'.
	'<div class="col">'.
	'<img style="float:left;margin-right: 20px;" src="'.$media->media_thumb['t'].'" alt="'.$media->media_title.'" />'.
	'</div>'.
	'<div class="col">'.
	'<h3>'.__('Media').'</h3>'.
	'<p><a href="media_item.php?id='.$media->media_id.'&amp;popup=0">'.__('View associated media').'</a></p>';

$img_gals_txt = ($img_gals->count() > 1)?__('This image belongs to %d galleries'):__('This image belongs to %d gallery');

echo '</div>'.
	'<div class="col">'.
	'<h3>'.__('Galleries').'</h3>'.
	'<p>'.sprintf($img_gals_txt,$img_gals->count()).' :</p>';
if ($img_gals->count() != 0) {
	echo '<ul>';
	while ($img_gals->fetch()) {
		echo '<li><a href="plugin.php?p=gallery&amp;m=gal&amp;id='.$img_gals->post_id.'" alt="'.$img_gals->post_title.'">'.$img_gals->post_title.'</a></li>';
	}
	echo '</ul>';
}
	
echo '</div>'.
	'</div>';
echo "</fieldset></div>";

/* Post form if we can edit post
-------------------------------------------------------- */
if ($can_edit_post)
{
?>
<?php
	echo '<form action="plugin.php?p=gallery&amp;m=item" method="post" id="entry-form">';
	echo '<div id="entry-sidebar">'.
	
	'<p><label>'.__('Category:').
	form::combo('cat_id',$categories_combo,$cat_id,'maximal',3).
	'</label></p>'.
	
	'<p><label>'.__('Gallery status:').
	form::combo('post_status',$status_combo,$post_status,'',3,!$can_publish).
	'</label></p>'.
	
	'<p><label>'.__('Published on:').
	form::field('post_dt',16,16,$post_dt,'',3).
	'</label></p>'.
	
	'<p><label>'.__('Text formating:').
	form::combo('post_format',$formaters_combo,$post_format,'',3).
	($post_id && $post_format != 'xhtml' ? '<a href="plugin.php?p=gallery&amp;m=item&amp;id='.$post_id.'&amp;xconv=1">'.__('Convert to XHTML').'</a>' : '').
	'</label></p>'.
	
	'<p><label class="classic">'.form::checkbox('post_open_comment',1,$post_open_comment,'',3).' '.
	__('Accept comments').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('post_open_tb',1,$post_open_tb,'',3).' '.
	__('Accept trackbacks').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('post_selected',1,$post_selected,'',3).' '.
	__('Selected image').'</label></p>'.
	
/*	'<p><label>'.__('Entry password:').
	form::field('post_password',10,32,html::escapeHTML($post_password),'maximal',3).
	'</label></p>'.*/
	
	'<div class="lockable">'.
	'<p><label>'.__('Basename:').
	form::field('post_url',10,255,html::escapeHTML($post_url),'maximal',3).
	'</label></p>'.
	'<p class="form-note warn">'.
	__('Warning: If you set the URL manually, it may conflict with another entry.').
	'</p>'.
	'</div>'.
	
	'<p><label>'.__('Entry lang:').
	form::field('post_lang',5,255,html::escapeHTML($post_lang),'',3).
	'</label></p>';
	if (isset($post))
		metaBehaviors::tagsField($post);

	# --BEHAVIOR-- adminGalleryFormSidebar
	$core->callBehavior('adminGalleryItemFormSidebar',isset($post) ? $post : null);
	
	echo '</div>';		// End #entry-sidebar
	
	echo '<div id="entry-content"><fieldset class="constrained">';
	
	echo
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').
	form::field('post_title',20,255,html::escapeHTML($post_title),'maximal',2).
	'</label></p>'.
	
	'<p class="area" id="excerpt-area"><label for="post_excerpt">'.__('Excerpt:').
	'</label> '.
	form::textarea('post_excerpt',50,5,html::escapeHTML($post_excerpt),'',2).
	'</p>'.
	
	'<p class="area" id="content-area"><label '.
	'for="post_content">'.__('Content:').
	'</label> '.
	form::textarea('post_content',50,$core->auth->getOption('edit_size'),html::escapeHTML($post_content),'',2).
	'</p>'.
	
	'<p class="area" id="notes-area"><label>'.__('Notes:').'</label>'.
	form::textarea('post_notes',50,5,html::escapeHTML($post_notes),'',2).
	'</p>';

	# --BEHAVIOR-- adminGalleryItemForm
	$core->callBehavior('adminGalleryItemForm',isset($post) ? $post : null);
	
	
	echo
	'<p>'.
	$core->formNonce().
	($post_id ? form::hidden('id',$post_id) : '').
	'<input type="submit" value="'.__('save').' (s)" tabindex="4" '.
	'accesskey="s" name="save" /> '.
	'</p>';
	
	echo '</fieldset></div>';		// End #entry-content
	echo '</form>';
	
	/*if ($post_id && $post->post_status == 1) {
		echo '<br /><p><a href="trackbacks.php?id='.$post_id.'" class="multi-part">'.
		__('Ping blogs').'</a></p>';
	}*/
	
} // if canedit post
echo '<p><a href="plugin.php?p=gallery&amp;m=newitems" class="multi-part">'.__('Manage new items').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
if ($core->auth->isSuperAdmin())
	echo '<p><a href="plugin.php?p=gallery&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
?>
</body>
</html>--

