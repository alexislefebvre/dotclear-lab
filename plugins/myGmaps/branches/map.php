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

$post_id = '';
$cat_id = '';
$post_dt = '';
$post_type = 'map';
$post_maps = 'none';
$post_format = $core->auth->getOption('post_format');
$post_password = '';
$post_url = '';
$post_lang = $core->auth->getInfo('user_lang');
$post_title = '';
$post_excerpt = '';
$post_excerpt_xhtml = '';
$post_content = 'Pas de description';
$post_content_xhtml = '<p>Pas de description</p>';
$post_notes = '';
$post_status = $core->auth->getInfo('user_post_status');
$post_selected = false;
$post_open_comment = '';
$post_open_tb = '';
$post_meta = array();

$post_media = array();

$page_title = __('New map element');

$can_view_page = true;
$can_edit_post = $core->auth->check('usage,contentadmin',$core->blog->id);
$can_publish = $core->auth->check('publish,contentadmin',$core->blog->id);
$can_delete = false;

$post_headlink = '<link rel="%s" title="%s" href="map.php?id=%s" />';
$post_link = '<a href="'.$p_url.'&amp;do=edit&amp;id=%s" title="%s">%s</a>';

$next_link = $prev_link = $next_headlink = $prev_headlink = null;

# If user can't publish
if (!$can_publish) {
	$post_status = -2;
}

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

# Status combo
$status_combo = array(
	__('pending') => '-2',
	__('online') => '1'
);

# Formaters combo
foreach ($core->getFormaters() as $v) {
	$formaters_combo[$v] = $v;
}

# Languages combo
$rs = $core->blog->getLangs(array('order'=>'asc'));
$all_langs = l10n::getISOcodes(0,1);
$lang_combo = array('' => '', __('Most used') => array(), __('Available') => l10n::getISOcodes(1,1));
while ($rs->fetch()) {
	if (isset($all_langs[$rs->post_lang])) {
		$lang_combo[__('Most used')][$all_langs[$rs->post_lang]] = $rs->post_lang;
		unset($lang_combo[__('Available')][$all_langs[$rs->post_lang]]);
	} else {
		$lang_combo[__('Most used')][$rs->post_lang] = $rs->post_lang;
	}
}
unset($all_langs);
unset($rs);

# Get entry informations
if (!empty($_REQUEST['id']))
{
	$params['post_id'] = $_REQUEST['id'];
	$params['post_type'] = 'map';
	
	$post = $core->blog->getPosts($params);
	
	if ($post->isEmpty())
	{
		$core->error->add(__('This map element does not exist.'));
		$can_view_page = false;
	}
	else
	{
		$post_id = $post->post_id;
		$cat_id = $post->cat_id;
		$post_dt = date('Y-m-d H:i',strtotime($post->post_dt));
		$post_type = $post->post_type;
		$post_maps = $post->post_maps;
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
		$post_meta = unserialize($post->post_meta);
		
		$page_title = __('Edit map element');
		
		$can_edit_post = $post->isEditable();
		$can_delete= $post->isDeletable();
		
		$next_rs = $core->blog->getNextPost($post,1);
		$prev_rs = $core->blog->getNextPost($post,-1);
		
		if ($next_rs !== null) {
			$next_link = sprintf($post_link,$next_rs->post_id,
				html::escapeHTML($next_rs->post_title),__('next element').'&nbsp;&#187;');
			$next_headlink = sprintf($post_headlink,'next',
				html::escapeHTML($next_rs->post_title),$next_rs->post_id);
		}
		
		if ($prev_rs !== null) {
			$prev_link = sprintf($post_link,$prev_rs->post_id,
				html::escapeHTML($prev_rs->post_title),'&#171;&nbsp;'.__('previous element'));
			$prev_headlink = sprintf($post_headlink,'previous',
				html::escapeHTML($prev_rs->post_title),$prev_rs->post_id);
		}
		
		try {
			$core->media = new dcMedia($core);
			$post_media = $core->media->getPostMedia($post_id);
		} catch (Exception $e) {}
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
	$cur->post_type = $post_type;
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
	
	if (isset($_POST['post_url'])) {
		$cur->post_url = $post_url;
	}
	
	# Update post
	if ($post_id)
	{
		try
		{
			# --BEHAVIOR-- adminBeforePostUpdate
			$core->callBehavior('adminBeforePostUpdate',$cur,$post_id);
			
			$core->blog->updPost($post_id,$cur);
			
			# --BEHAVIOR-- adminAfterPostUpdate
			$core->callBehavior('adminAfterPostUpdate',$cur,$post_id);
			
			if (isset($_POST['post_maps'])) {
				$tags = $_POST['post_maps'];
				$myGmaps_center = $_POST['myGmaps_center'];
				$myGmaps_zoom = $_POST['myGmaps_zoom'];
				$myGmaps_type = $_POST['myGmaps_type'];
				$meta =& $GLOBALS['core']->meta;
				$meta->delPostMeta($post_id,'map');
				$meta->delPostMeta($post_id,'map_options');
				
				foreach ($meta->splitMetaValues($tags) as $tag) {
					$meta->setPostMeta($post_id,'map',$tag);
				}
				$map_options = $myGmaps_center.','.$myGmaps_zoom.','.$myGmaps_type;
				$meta->setPostMeta($post_id,'map_options',$map_options);
			}
			http::redirect(''.$p_url.'&do=edit&id='.$post_id.'&upd=1');
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
			# --BEHAVIOR-- adminBeforePostCreate
			$core->callBehavior('adminBeforePostCreate',$cur);
			
			$return_id = $core->blog->addPost($cur);
			
			# --BEHAVIOR-- adminAfterPostCreate
			$core->callBehavior('adminAfterPostCreate',$cur,$return_id);
			
			if (isset($_POST['post_maps'])) {
				$tags = $_POST['post_maps'];
				$myGmaps_center = $_POST['myGmaps_center'];
				$myGmaps_zoom = $_POST['myGmaps_zoom'];
				$myGmaps_type = $_POST['myGmaps_type'];
				$meta =& $GLOBALS['core']->meta;
				
				foreach ($meta->splitMetaValues($tags) as $tag) {
					$meta->setPostMeta($return_id,'map',$tag);
				}
				$map_options = $myGmaps_center.','.$myGmaps_zoom.','.$myGmaps_type;
				$meta->setPostMeta($return_id,'map_options',$map_options);
			}
			
			http::redirect(''.$p_url.'&do=edit&id='.$return_id.'&crea=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}

if (!empty($_POST['delete']) && $can_delete)
{
	try {
		# --BEHAVIOR-- adminBeforePostDelete
		$core->callBehavior('adminBeforePostDelete',$post_id);
		$core->blog->delPost($post_id);
		http::redirect($p_url.'&do=list');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

/* DISPLAY
-------------------------------------------------------- */

echo
'<html>'.
'<head>'.
	'<title>'.$page_title.'</title>'.
	dcPage::jsDatePicker().
	dcPage::jsToolBar().
	dcPage::jsModal().
	dcPage::jsConfirmClose('entry-form').
	dcPage::jsColorPicker().
	dcPage::jsLoad('http://maps.google.com/maps/api/js?sensor=false').
	dcPage::jsLoad(DC_ADMIN_URL.'?pf=myGmaps/js/myGmaps.js').
	dcPage::jsLoad(DC_ADMIN_URL.'?pf=myGmaps/js/_map.js').
	$next_headlink."\n".$prev_headlink.
	'<link type="text/css" rel="stylesheet" href="'.DC_ADMIN_URL.'?pf=myGmaps/style.css" />'.
	'<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.
'</head>'.
'<body>';

if (isset($_GET['upd'])) {
	echo '<p class="message">'.__('Map element has been successfully updated.').'</p>';
}
if (isset($_GET['crea'])) {
	echo '<p class="message">'.__('Map element has been successfully created.').'</p>';
}

# XHTML conversion
if (!empty($_GET['xconv']))
{
	$post_excerpt = $post_excerpt_xhtml;
	$post_content = $post_content_xhtml;
	$post_format = 'xhtml';
	
	echo '<p class="message">'.__('Don\'t forget to validate your XHTML conversion by saving your post.').'</p>';
}

echo
'<h2>'.
	html::escapeHTML($core->blog->name).' &rsaquo; '.
	'<a href="'.$p_url.'">'.__('Google Maps').'</a> &rsaquo; '.
	$page_title.
'</h2>';

if ($post_id)
{
	echo '<p>';
	if ($prev_link) {
		echo $prev_link.' - '.'<a href="'.$p_url.'&amp;do=list">'.__('elements list').'</a>';
	} else {
		echo '<a href="'.$p_url.'&amp;do=list">'.__('elements list').'</a>';
	}
	if ($next_link) {
		echo ' - '.$next_link;
	}
	
	# --BEHAVIOR-- adminPostNavLinks
	$core->callBehavior('adminPostNavLinks',isset($post) ? $post : null);
	
	echo '</p>';
}

# Exit if we cannot view page
if (!$can_view_page) {
	dcPage::helpBlock('core_post');
	dcPage::close();
	exit;
}

/* Post form if we can edit post
-------------------------------------------------------- */
if ($can_edit_post)
{
	echo
	'<div id="edit-entry">'.
	'<form action="'.$p_url.'&amp;do=edit" method="post" id="entry-form">'.
	'<div id="entry-sidebar">';
	
	echo
	'<p><label>'.__('Category:').
	form::combo('cat_id',$categories_combo,$cat_id,'maximal',3).
	'</label></p>'.
	
	'<p><label>'.__('Map element status:').
	form::combo('post_status',$status_combo,$post_status,'',3,!$can_publish).
	'</label></p>'.
	
	'<p><label>'.__('Map element published on:').
	form::field('post_dt',16,16,$post_dt,'',3).
	'</label></p>'.
	
	'<p><label>'.__('Text formating:').
	form::combo('post_format',$formaters_combo,$post_format,'',3).
	'</label></p>';
	
	
	echo '</div>';	// End #entry-sidebar
	
	echo '<div id="entry-content"><fieldset class="constrained">';
	
	echo
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').
	form::field('post_title',20,255,html::escapeHTML($post_title),'maximal',2).
	'</label></p>';
	
	$meta =& $GLOBALS['core']->meta;
	
	if(isset($post)) {
		echo form::hidden('post_maps',$meta->getMetaStr($post->post_meta,'map')).'</p>';
	} else {
		echo form::hidden('post_maps','').'</p>';
	}
	
	echo
	'<p class="area" id="excerpt-area" style="display:none;"><label for="post_excerpt">'.__('Coordinates:').'</label> '.
	form::textarea('post_excerpt',50,3,html::escapeHTML($post_excerpt)).
	'</p>';
	
	echo
	'<div class="area" id="toolbar-area">'.
		'<ul>'.
			'<li id="none"></li>'.
			'<li id="marker"></li>'.
			'<li id="polyline"></li>'.
			'<li id="polygon"></li>'.
			'<li>'.
				form::field('q',50,255).
				'<input type="button" class="submit" name="mq" id="search" value="'.__('Search').'" />'.
			'</li>'.
			'<li><input type="button" class="submit" name="reset" id="reset"  value="'.__('Reset map').'" /></li>'.
			'<li><input type="button" class="submit" name="kml" id="kml" value="'.__('Include kml file').'" /></li>'.
		'</ul>'.
	'</div>';
	
	echo
	'<div class="area" id="map_canvas"></div>';
	
	echo
	'<p class="area" id="description-area" >'.
		'<label class="infowindow" for="post_content">'.__('Description:').'</label>'.
		form::textarea('post_content',50,$core->auth->getOption('edit_size'),html::escapeHTML($post_content),'',2).
	'</p>';
	
	echo
	'<p class="area" id="map-details-area" >'.
		'<label class="infowindow" for="map-details">'.__('Map details:').'</label>'.
		'<div id="map-details"></div>'.
	'</p>';
	
	echo
	'<p class="area" id="notes-area"><label>'.__('Notes:').'</label>'.
	form::textarea('post_notes',50,5,html::escapeHTML($post_notes),'',2).
	'</p>';
	
	$metas = array(
		'elt_type' => array_key_exists('elt_type',$post_meta) ? $post_meta['elt_type'] : 'none',
		'stroke_weight' => array_key_exists('stroke_weight',$post_meta) ? $post_meta['stroke_weight'] : '',
		'stroke_opacity' => array_key_exists('stroke_opacity',$post_meta) ? $post_meta['stroke_opacity'] : '',
		'stroke_color' => array_key_exists('stroke_color',$post_meta) ? $post_meta['stroke_color'] : '',
		'fill_color' => array_key_exists('fill_color',$post_meta) ? $post_meta['fill_color'] : ''
	);
	foreach ($metas as $k => $v) {
		echo form::hidden($k,$v);
	}
	
	echo
	form::hidden('center',$core->blog->settings->myGmaps->center).
	form::hidden('zoom',$core->blog->settings->myGmaps->zoom).
	form::hidden('map_type',$core->blog->settings->myGmaps->map_type).
	form::hidden('scrollwheel',$core->blog->settings->myGmaps->scrollwheel);
	
	echo
	'<p>'.
	($post_id ? form::hidden('id',$post_id) : '').
	'<input type="submit" value="'.__('save').' (s)" tabindex="4" '.
	'accesskey="s" name="save" /> '.
	($can_delete ? '<input type="submit" value="'.__('delete').'" name="delete" />' : '').
	$core->formNonce().
	'</p>';
	
	echo
	'</fieldset></div>'.
	'</form>'.
	'</div>';
}

dcPage::helpBlock('myGmap','core_wiki');

echo
'</body>'.
'</html>';

?>