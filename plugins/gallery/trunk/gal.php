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


require DC_ROOT.'/inc/admin/lib.pager.php';

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
$can_view_page = true;
$can_edit_post = $core->auth->check('usage,contentadmin',$core->blog->id);
$can_publish = $core->auth->check('publish,contentadmin',$core->blog->id);

$core->media = new dcMedia($core);
$core->meta = new dcMeta($core);

$core->gallery = new dcGallery($core);

$themes = $core->gallery->getThemes();
/*
$post_headlink = '<link rel="%s" title="%s" href="post.php?id=%s" />';
$post_link = '<a href="post.php?id=%s" title="%s">%s</a>';
*/
$next_link = $prev_link = $next_headlink = $prev_headlink = null;

$gal_headlink = '<link rel="%s" title="%s" href="plugin.php?p=gallery&amp;m=gal&amp;id=%s" />';
$gal_link = '<a href="plugin.php?p=gallery&amp;m=gal&amp;id=%s" title="%s">%s</a>';


# If user can't publish
if (!$can_publish) {
	$post_status = -2;
}

$orderby_combo = $core->gallery->orderby;
$sortby_combo = $core->gallery->sortby;

# Getting categories
$categories_combo = array('&nbsp;' => '');
try {
	$categories = $core->blog->getCategories();
	while ($categories->fetch()) {
		$categories_combo[str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.
			html::escapeHTML($categories->cat_title)] = $categories->cat_id;
		$reverse_cat[$categories->cat_id] = html::escapeHTML($categories->cat_title);
	}
		$reverse_cat[null] = "";
} catch (Exception $e) { }

# Status combo
foreach ($core->blog->getAllPostStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

# Formaters combo
foreach ($core->getFormaters() as $v) {
	$formaters_combo[$v] = $v;
}

$c_media_dir = $c_tag = $c_user = $c_cat = 0;
$f_recurse_dir = 0;
$f_sub_cat = 0;
$f_media_dir = $f_tag = $f_user = $f_cat = null;
$f_orderby = $f_sortby = null;
$f_theme = "default";


# Get entry informations
if (!empty($_REQUEST['id']))
{
	$params['post_id'] = $_REQUEST['id'];
	
	$post = $core->gallery->getGalleries($params);
	
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
		if (trim($post_content) === '')
			$post_content = '';
		$post_content_xhtml = $post->post_content_xhtml;
		if (trim($post_content_xhtml) === '')
			$post_content_xhtml = '';
		$post_notes = $post->post_notes;
		$post_status = $post->post_status;
		$post_selected = (boolean) $post->post_selected;
		$post_open_comment = (boolean) $post->post_open_comment;
		$post_open_tb = (boolean) $post->post_open_tb;
		$gal_filters = $core->gallery->getGalParams($post);
		if (isset($gal_filters['media_dir'])) {
			$c_media_dir=true;
			$f_media_dir=$gal_filters['media_dir'][0];
		}
		if (isset($gal_filters['recurse_dir'])) {
			$f_recurse_dir = 1;
		}
		if (isset($gal_filters['sub_cat'])) {
			$f_sub_cat = 1;
		}
		if (isset($gal_filters['tag'])) {
			$c_tag=true;
			$f_tag=$gal_filters['tag'];
		}
		if (isset($gal_filters['user_id'])) {
			$c_user=true;
			$f_user=$gal_filters['user_id'];
		}
		if (isset($gal_filters['cat_id'])) {
			$c_cat=true;
			$f_cat=(integer)$gal_filters['cat_id'];
		}
		if (isset($gal_filters['orderby'])) {
			$f_orderby = $gal_filters['orderby'];
		} else {
			$f_orderby = 'P.post_dt';
		}
		if (isset($gal_filters['sortby'])) {
			$f_sortby = $gal_filters['sortby'];
		} else {
			$f_orderby = 'ASC';
		}
		$gal_thumb = $core->gallery->getPostMedia($post_id);
		$has_thumb = (sizeof($gal_thumb) != 0); 
		if ($has_thumb) {
			$gal_thumb = $gal_thumb[0];
		}
		$meta_list = $core->meta->getMetaArray($post->post_meta);
		$gal_nb_img = isset($meta_list['galitem'])?sizeof($meta_list['galitem']):0;
		$f_theme = isset($meta_list['galtheme'])?$meta_list['galtheme'][0]:'default';

		/*$gal_meta=$core->meta->getMetaArray($post->post_meta);
		if (isset($gal_meta["galordering"])) {
		} else {
			$gal_ordering = 'P.date'; 
		}
		if (isset($gal_meta["galorderdir"])) {
		} else {
			$gal_ordedir = 'ASC'; 
		}*/

		$page_title = __('Edit gallery');
		
		$can_edit_post = $post->isEditable();
		
		$next_rs = $core->gallery->getNextGallery($post_id,strtotime($post_dt),1);
		$prev_rs = $core->gallery->getNextGallery($post_id,strtotime($post_dt),-1);
		if ($next_rs !== null) {
			$next_link = sprintf($gal_link,$next_rs->post_id,
				html::escapeHTML($next_rs->post_title),__('next gallery').'&nbsp;&#187;');
			$next_headlink = sprintf($gal_headlink,'next',
				html::escapeHTML($next_rs->post_title),$next_rs->post_id);
		}
		
		if ($prev_rs !== null) {
			$prev_link = sprintf($gal_link,$prev_rs->post_id,
				html::escapeHTML($prev_rs->post_title),'&#171;&nbsp;'.__('previous gallery'));
			$prev_headlink = sprintf($gal_headlink,'previous',
				html::escapeHTML($prev_rs->post_title),$prev_rs->post_id);
		}
	}
}


$dirs_combo = array();
foreach ($core->media->getRootDirs() as $v) {
	if ($v->relname == "")
		$dirs_combo['/'] = ".";
	else
		$dirs_combo['/'.$v->relname] = $v->relname;
}
# Format excerpt and content
if (!empty($_POST) && $can_edit_post)
{
	$post_format = $_POST['post_format'];
	$post_excerpt = $_POST['post_excerpt'];
	$post_content = $_POST['post_content'];

	/* Enable null post content */
	if (trim($post_content)==='')
		$post_content="\t";
	
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

	$c_media_dir = !empty($_POST['c_media_dir']);
	$c_tag = !empty($_POST['c_tag']);
	$c_cat = !empty($_POST['c_cat']);
	$c_user = !empty($_POST['c_user']);
	$f_media_dir = !empty($_POST['f_media_dir']) ? $_POST['f_media_dir'] : null;
	$f_recurse_dir = !empty($_POST['f_recurse_dir']);
	$f_sub_cat = !empty($_POST['f_sub_cat']);
	$f_tag = !empty($_POST['f_tag']) ? $_POST['f_tag'] : null;
	$f_cat = !empty($_POST['f_cat']) ? $_POST['f_cat'] : null;
	$f_user = !empty($_POST['f_user']) ? $_POST['f_user'] : null;
	$f_orderby = !empty($_POST['f_orderby']) ? $_POST['f_orderby'] : null;
	$f_sortby = !empty($_POST['f_sortby']) ? $_POST['f_sortby'] : null;
	$f_theme = !empty($_POST['f_theme']) ? $_POST['f_theme'] : 'default';


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
	$cur->post_type='gal';	

	if (isset($_POST['post_url'])) {
		$cur->post_url = $post_url;
	}
	
	$updated=false;
	# Update post
	if ($post_id)
	{
		try
		{
			# --BEHAVIOR-- adminBeforeGalleryUpdate
			$core->callBehavior('adminBeforeGalleryUpdate',$cur,$post_id);
			
			$core->blog->updPost($post_id,$cur);

			# --BEHAVIOR-- adminAfterGalleryUpdate
			$core->callBehavior('adminAfterGalleryUpdate',$cur,$post_id);
			
			$updated=true;
			
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
			# --BEHAVIOR-- adminBeforeGalleryUpdate
			$core->callBehavior('adminBeforeGalleryCreate',$cur,$post_id);
		
			$post_id = $core->blog->addPost($cur);

			# --BEHAVIOR-- adminAfterGalleryUpdate
			$core->callBehavior('adminAfterGalleryUpdate',$cur,$post_id);
			$updated=true;
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
	if ($updated) {
		$core->meta->delPostMeta($post_id,"galmediadir");
		$core->meta->delPostMeta($post_id,"galrecursedir");
		$core->meta->delPostMeta($post_id,"galsubcat");
		$core->meta->delPostMeta($post_id,"galtag");
		$core->meta->delPostMeta($post_id,"galcat");
		$core->meta->delPostMeta($post_id,"galuser");
		$core->meta->delPostMeta($post_id,"galorderby");
		$core->meta->delPostMeta($post_id,"galsortby");
		$core->meta->delPostMeta($post_id,"galtheme");
		$core->meta->delPostMeta($post_id,"subcat");
		if ($c_media_dir) {
			$core->meta->setPostMeta($post_id,"galmediadir",$f_media_dir);
			$core->meta->setPostMeta($post_id,"galrecursedir",(integer)$f_recurse_dir);
		}
		if ($c_tag) {
			$core->meta->setPostMeta($post_id,"galtag",$f_tag);
		}
		if ($c_cat) {
			$core->meta->setPostMeta($post_id,"galcat",$f_cat);
			$core->meta->setPostMeta($post_id,"galsubcat",(integer)$f_sub_cat);
		}
		if ($c_user) {
			$core->meta->setPostMeta($post_id,"galuser",$f_user);
		}
		if (isset ($f_orderby)) {
			$core->meta->setPostMeta($post_id,"galorderby",$f_orderby);
		}
		if (isset ($f_sortby)) {
			$core->meta->setPostMeta($post_id,"galsortby",$f_sortby);
		}
		if (isset ($f_theme) && $f_theme != 'default') {
			$core->meta->setPostMeta($post_id,"galtheme",$f_theme);
		}
		$core->gallery->refreshGallery($post_id);

		http::redirect('plugin.php?p=gallery&m=gal&id='.$post_id.'&upd=1');
	}
}
?>
<html>
<head>
  <title>Gallery</title>
<?php echo dcPage::jsDatePicker().
	dcPage::jsToolBar().
	dcPage::jsModal().
	dcPage::jsLoad('index.php?pf=gallery/js/_gal.js').
	dcPage::jsLoad('index.php?pf=gallery/js/posttag.js').
	dcPage::jsConfirmClose('entry-form').
	dcPage::jsPageTabs('edit-entry').
	metaBehaviors::postHeaders().
	$core->callBehavior('adminGalleryHeaders'); ?>

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
		echo '<p class="message">'.__('The gallery has been successfully updated.').'</p>';
}
elseif (!empty($_GET['crea'])) {
		echo '<p class="message">'.__('The gallery has been successfully created.').'</p>';
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


if ($core->error->flag()) {
	echo
	'<div class="error"><strong>'.__('Errors:').'</strong>'.
	$core->error->toHTML().
	'</div>';
}

echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('Gallery')." &gt; ".$page_title.'</h2>';

# Exit if we cannot view page
if (!$can_view_page) {
	exit;
}
if ($post_id)
{
	echo '<p>';
	if ($prev_link) {
		echo $prev_link.' - ';
	}
	
	if ($post->post_status == 1) {
		echo '<a id="post-preview" href="'.$post->getURL().'" class="button">'.__('view gallery').'</a>';
	} else {
		
		$preview_url =
		$core->blog->url.$core->url->getBase('gallerypreview').'/'.
		$core->auth->userID().'/'.
		http::browserUID(DC_MASTER_KEY.$core->auth->userID().$core->auth->getInfo('user_pwd')).
		'/'.$post->post_url;
		echo '<a id="post-preview" href="'.$preview_url.'" class="button">'.__('Preview gallery').'</a>';
	}
	
	if ($next_link) {
		echo ' - '.$next_link;
	}

	# --BEHAVIOR-- adminGalleryNavLinks
	$core->callBehavior('adminGalleryNavLinks',isset($post) ? $post : null);
	
	echo '</p>';
}
echo '<p><a href="plugin.php?p=gallery" class="multi-part">'.__('Galleries').'</a></p>';
echo '<div id="edit-entry" class="multi-part" title="'.__('Gallery').'">';

if ($post_id) {
	echo "<fieldset><legend>".__('Information')."</legend>";
	echo '<div class="two-cols">'.
		'<div class="col">'.
		"<h3>".__('Presentation thumbnail')."</h3>";
	$change_thumb_url='plugin.php?p=gallery&amp;m=galthumb&amp;gal_id='.$post_id;
	if ($c_media_dir)
		$change_thumb_url .= '&amp;d='.$f_media_dir;

	if ($has_thumb) {
		echo '<div class="gal-media-item">';
		echo '<a class="media-icon media-link" href="'.$gal_thumb->file_url.'"><img src="'.$gal_thumb->media_icon.'" /></a>';
		echo '<form action="plugin.php?p=gallery&amp;m=galthumb" method="post">';
		echo '<ul>';
		echo '<li>'.$gal_thumb->basename.'</li>';
		echo '<li>'.$gal_thumb->media_dtstr.' - '. files::size($gal_thumb->size).' - '.
		'<a href="'.$change_thumb_url.'">'.__('Change').'</a></li>'.
		'<li><input type="image" src="images/minus.png" alt="'.__('Remove').'" style="border: 0px;" '.
		'title="'.__('Remove').'" />&nbsp;'.__('Remove').' '.
		form::hidden('gal_id',$post_id).
		form::hidden('detach',1).$core->formNonce().
		'</form></li></ul>';
		echo '</div>';
	} else {
		echo '<p>'.__('This gallery has no presentation thumbnail').'</p>';
		echo '<p><a href="'.$change_thumb_url.'">'.__('Define one').'</a>'.'</p>';
	}
	$gal_nb_img_txt = ($gal_nb_img > 1) ? __("This gallery has %d images"):__("This gallery has %d image");
	echo '</div>'.
		'<div class="col">'.
		"<h3>".__('Images')."</h3>".
		'<p>'.sprintf($gal_nb_img_txt,$gal_nb_img).'</p>'.
		'</div>'.
		'</div>';
	echo "</fieldset>";
}


/* Post form if we can edit post
-------------------------------------------------------- */
if ($can_edit_post)
{

	echo '<form action="plugin.php?p=gallery&amp;m=gal" method="post" id="entry-form">';
	echo '<div id="entry-sidebar">';
	
	echo '<p><label>'.__('Category:').
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
	($post_id && $post_format != 'xhtml' ? '<a href="plugin.php?p=gallery&amp;m=gal&amp;id='.$post_id.'&amp;xconv=1">'.__('Convert to XHTML').'</a>' : '').
	'</label></p>'.
	
	'<p><label class="classic">'.form::checkbox('post_open_comment',1,$post_open_comment,'',3).' '.
	__('Accept comments').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('post_open_tb',1,$post_open_tb,'',3).' '.
	__('Accept trackbacks').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('post_selected',1,$post_selected,'',3).' '.
	__('Selected gallery').'</label></p>'.
	
	'<p><label>'.__('Gallery password:').
	form::field('post_password',10,32,html::escapeHTML($post_password),'maximal',3).
	'</label></p>'.
	
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
	$core->callBehavior('adminGalleryFormSidebar',isset($post) ? $post : null);
	
	echo '</div>';		// End #entry-sidebar
	
	echo '<div id="entry-content"><fieldset class="constrained">';
	
	echo
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').
	form::field('post_title',20,255,html::escapeHTML($post_title),'maximal',2).
	'</label></p>'.
	
	"<fieldset><legend>".__('Settings')."</legend>".
	'<div class="two-cols">'.
	'<div class="col">'.
	"<h3>".__('Filters')."</h3>".
	"<p>".__('Select below the image filters you wish to set for this gallery (at least 1 must be selected)')."</p>".
	'<p><label class="classic">'.form::checkbox('c_media_dir',1,$c_media_dir,"disablenext").'</label><label class="classic">'.
	__('Media dir')." : ".form::combo('f_media_dir',$dirs_combo,$f_media_dir).'</label>'.
	'<br /><label class="classic" style="margin-left: 20px;">'.form::checkbox('f_recurse_dir',1,$f_recurse_dir).__('include subdirs').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('c_tag',1,$c_tag,"disablenext").'</label><label class="classic">'.
	__('Tag')." : ".form::field('f_tag',20,100,$f_tag,'',2).'</label></p>'.
	'<p><label class="classic">'.form::checkbox('c_cat',1,$c_cat,"disablenext").'</label><label class="classic">'.
	__('Category')." : ".form::combo('f_cat',$categories_combo,$f_cat).'</label>'.
	'<br /><label class="classic" style="margin-left: 20px;">'.form::checkbox('f_sub_cat',1,$f_sub_cat).__('Include sub-categories').'</label></p>'.
	'<p><label class="classic">'.form::checkbox('c_user',1,$c_user,"disablenext").'</label><label class="classic">'.
	__('User')." : ".form::field('f_user',20,20,$f_user,'',2).'</label></p>'.
	"</div>".
	'<div class="col">'.
	"<h3>".__('Order')."</h3>".
	'<p><label class="classic">'.__('Order')." : ".form::combo('f_orderby',$orderby_combo,$f_orderby).'</label></p>'.
	'<p><label class="classic">'.__('Sort')." : ".form::combo('f_sortby',$sortby_combo,$f_sortby).'</label></p>'.
	"<h3>".__('Theme')."</h3>".
	'<p><label class="classic">'.__('Gallery theme')." : ".form::combo('f_theme',$themes,$f_theme).'</label></p>'.
	'</div>'.
	'</div>'.
	"</fieldset>".

	'<p class="area" id="excerpt-area"><label for="post_excerpt">'.__('Excerpt:').
	'</label> '.
	form::textarea('post_excerpt',50,5,html::escapeHTML($post_excerpt),'',2).
	'</p>'.
	
	'<p class="area" id="content-area"><label '.
	'for="post_content">'.__('Content:').'</label> '.
	form::textarea('post_content',50,$core->auth->getOption('edit_size'),html::escapeHTML($post_content),'',2).
	'</p>'.
	
	'<p class="area" id="notes-area"><label>'.__('Notes:').'</label>'.
	form::textarea('post_notes',50,5,html::escapeHTML($post_notes),'',2).
	'</p>';

	# --BEHAVIOR-- adminGalleryForm
	$core->callBehavior('adminGalleryForm',isset($post) ? $post : null);
	
	
	echo
	'<p>'.
	($post_id ? form::hidden('id',$post_id) : '').
	$core->formNonce().
	'<input type="submit" value="'.__('save').' (s)" tabindex="4" '.
	'accesskey="s" name="save" /> '.
	'</p>';

	
	echo '</fieldset></div>';		// End #entry-content
	echo '</form>';
	
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
		$core->formNonce().
		form::hidden(array('remove'),1).'</div></form>';
	}
} // if canedit post
echo '</div>';
echo '<p><a href="plugin.php?p=gallery&amp;m=items" class="multi-part">'.__('Images').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=newitems" class="multi-part">'.__('Manage new items').'</a></p>';
echo '<p><a href="plugin.php?p=gallery&amp;m=options" class="multi-part">'.__('Options').'</a></p>';
if ($core->auth->isSuperAdmin())
	echo '<p><a href="plugin.php?p=gallery&amp;m=maintenance" class="multi-part">'.__('Maintenance').'</a></p>';
?>


</body>
</html>


