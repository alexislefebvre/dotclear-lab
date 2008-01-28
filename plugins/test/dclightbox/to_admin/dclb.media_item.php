<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
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

require dirname(__FILE__).'/../inc/admin/prepend.php';

dcPage::check('media,media_admin');

try 
{
	if(!file_exists(dirname(__FILE__).'/dclb.js/dclb.popup_media.js'))
	{
		throw new Exception(__('Missing file:').' dclb.popup_media.js in '.dirname(__FILE__).'/dclb.js/');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

$post_id = !empty($_GET['post_id']) ? (integer) $_GET['post_id'] : null;
if ($post_id) {
	$post = $core->blog->getPosts(array('post_id'=>$post_id));
	if ($post->isEmpty()) {
		$post_id = null;
	}
	$post_title = $post->post_title;
	unset($post);
}

$file = null;
$type = !empty($_GET['type']) ? rawurlencode($_GET['type']) : '';
$popup = (integer) !empty($_GET['popup']);
$page_url = 'dclb.media_item.php?type='.rawurlencode($type).'&popup='.$popup.'&post_id='.$post_id;
$media_page_url = 'dclb.media.php?type='.rawurlencode($type).'&popup='.$popup.'&post_id='.$post_id;

$id = !empty($_REQUEST['id']) ? (integer) $_REQUEST['id'] : '';

if ($popup) {
	$open_f = array('dcPage','openPopup');
	$close_f = array('dcPage','closePopup');
} else {
	$open_f = array('dcPage','open');
	$close_f = array('dcPage','close');
}

try
{
	$core->media = new dcMedia($core,$type);
	
	if ($id) {
		$file = $core->media->getFile($id);
	}
	
	if ($file === null) {
		throw new Exception(__('Not a valid file'));
	}
	
	$core->media->chdir(dirname($file->relname));
	
	# Prepare directories combo box
	$dirs_combo = array();
	foreach ($core->media->getRootDirs() as $v) {
		if ($v->w) {
			$dirs_combo['/'.$v->relname] = $v->relname;
		}
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}

# Upload a new file
if ($file && !empty($_FILES['upfile']) && $file->editable)
{
	try {
		files::uploadStatus($_FILES['upfile']);
		$core->media->uploadFile($_FILES['upfile']['tmp_name'],$file->basename);
		http::redirect($page_url.'&id='.$id.'&fupl=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Update file
if ($file && !empty($_POST['media_file']) && $file->editable)
{
	$newFile = clone $file;
	
	$newFile->basename = $_POST['media_file'];
	
	if ($_POST['media_path']) {
		$newFile->dir = $_POST['media_path'];
		$newFile->relname = $_POST['media_path'].'/'.$newFile->basename;
	} else {
		$newFile->dir = '';
		$newFile->relname = $newFile->basename;
	}
	$newFile->media_title = $_POST['media_title'];
	$newFile->media_dt = strtotime($_POST['media_dt']);
	$newFile->media_dtstr = $_POST['media_dt'];
	$newFile->media_priv = !empty($_POST['media_private']);
	
	try {
		$core->media->updateFile($file,$newFile);
		http::redirect($page_url.'&id='.$id.'&fupd=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

/* DISPLAY Main page
-------------------------------------------------------- */
$starting_scripts = dcPage::jsLoad('js/_media_item.js');
if ($popup) {
	$starting_scripts .= dcPage::jsLoad('dclb.js/dclb.popup_media.js');
}
call_user_func($open_f,__('Media manager'),
	$starting_scripts.
	dcPage::jsDatePicker().
	dcPage::jsPageTabs()
);

if ($file === null) {
	call_user_func($close_f);
	exit;
}

if (!empty($_GET['fupd'])) {
	echo '<p class="message">'.__('File has been successfully updated').'</p>';
}

echo '<h2><a href="'.html::escapeURL($media_page_url).'">'.__('Media manager').'</a>'.
' / '.$core->media->breadCrum(html::escapeURL($media_page_url).'&amp;d=%s').
$file->basename.'</h2>';

# Insertion popup
if ($popup && $type == 'image')
{
	echo
	'<div id="media-insert" class="multi-part" title="'.__('Insert image').'">'.
	'<form id="media-insert-form" action="" method="get">'.
	'<h3>'.__('Image size:').'</h3> ';
	
	$s_checked = false;
	echo '<p>';
	foreach (array_reverse($file->media_thumb) as $s => $v) {
		$s_checked = ($s == 'm');
		echo '<label class="classic">'.
		form::radio(array('src'),html::escapeHTML($v),$s_checked).' '.
		$core->media->thumb_sizes[$s][2].'</label><br /> ';
	}
	$s_checked = (!isset($file->media_thumb['m']));
	echo '<label class="classic">'.
	form::radio(array('src'),$file->file_url,$s_checked).' '.__('original').'</label><br /> ';
	echo '</p>';
	
	
	echo '<h3>'.__('Image alignment').'</h3>';
	$i_align = array(
		'none' => array(__('None'),1),
		'left' => array(__('Left'),0),
		'right' => array(__('Right'),0),
		'center' => array(__('Center'),0)
	);
	
	echo '<p>';
	foreach ($i_align as $k => $v) {
		echo '<label class="classic">'.
		form::radio(array('alignment'),$k,$v[1]).' '.$v[0].'</label><br /> ';
	}
	echo '</p>';
	
	echo
	'<h3>'.__('Image insertion').'</h3>'.
	'<p>'.
	'<label class="classic">'.form::radio(array('insertion'),'simple',true).
	__('As a single image').'</label><br />'.
	'<label class="classic">'.form::radio(array('insertion'),'link',false).
	__('As a link to original image').'</label>'.
	'</p>'.
	'<fieldset>'.
	'<legend>'.__('Lightbox effect').'</legend>'.
	'<p>'.
	'<label class="classic">'.form::radio(array('insertion'),'lboxlink',false).
	__('As a link to original image with Lightbox effect').'</label>'.
	'<label>'.__('Groupe name:').' '.form::field('gname',35,512,html::escapeHTML($gname)).'</label>'.
	'</p>'.
	'</fieldset>';
	
	echo
	'<p><a id="media-insert-cancel" href="#">'.__('Cancel').'</a> - '.
	'<strong><a id="media-insert-ok" href="#">'.__('Insert image').'</a></strong>'.
	form::hidden(array('title'),html::escapeHTML($file->media_title)).
	form::hidden(array('url'),$file->file_url).
	'</p>';
	
	echo '</form></div>';
	
	
}

echo
'<div class="multi-part" title="'.__('Media details').'" id="media-details-tab">'.
'<p id="media-icon"><img src="'.$file->media_icon.'" alt="" /></p>';

echo
'<div id="media-details">';

if ($file->media_image)
{
	$thumb_size = !empty($_GET['size']) ? $_GET['size'] : 's';
	
	if (!isset($core->media->thumb_sizes[$thumb_size]) && $thumb_size != 'o') {
		$thumb_size = 's';
	}
	
	echo '<p>'.__('Available sizes:').' ';
	foreach (array_reverse($file->media_thumb) as $s => $v)
	{
		$strong_link = ($s == $thumb_size) ? '<strong>%s</strong>' : '%s';
		printf($strong_link,'<a href="'.html::escapeURL($page_url).
		'&amp;id='.$id.'&amp;size='.$s.'">'.$core->media->thumb_sizes[$s][2].'</a> | ');
	}
	echo '<a href="'.html::escapeURL($page_url).'&amp;id='.$id.'&amp;size=o">'.__('original').'</a>';
	echo '</p>';
	
	if (isset($file->media_thumb[$thumb_size])) {
		echo '<p><img src="'.$file->media_thumb[$thumb_size].'" alt="" /></p>';
	} elseif ($thumb_size == 'o') {
		$S = getimagesize($file->file);
		$class = ($S[1] > 500) ? ' class="overheight"' : '';
		unset($S);
		echo '<p id="media-original-image"'.$class.'><img src="'.$file->file_url.'" alt="" /></p>';
	}
}

if ($file->type == 'audio/mpeg3')
{
	echo $core->media->mp3player($file->file_url);
}

echo
'<h3>'.__('Media details').'</h3>'.
'<ul>'.
	'<li><strong>'.__('File owner:').'</strong> '.$file->media_user.'</li>'.
	'<li><strong>'.__('File type:').'</strong> '.$file->type.'</li>'.
	'<li><strong>'.__('File size:').'</strong> '.files::size($file->size).'</li>'.
	'<li><strong>'.__('File URL:').'</strong> <a href="'.$file->file_url.'">'.$file->file_url.'</a></li>'.
'</ul>';

if ($file->type == 'image/jpeg' && $meta = @simplexml_load_string($file->media_meta))
{
	echo
	'<h3>'.__('Image details').'</h3>'.
	'<ul>';
	
	$has_meta = false;
	foreach ($meta as $k => $v)
	{
		if ((string) $v) {
			$has_meta = true;
			echo '<li><strong>'.$k.':</strong> '.html::escapeHTML($v).'</li>';
		}
	}
	
	echo '</ul>';
	
	if (!$has_meta) {
		echo '<p>'.__('No detail').'</p>';
	}
}

if ($file->editable)
{
	echo
	'<h3>'.__('Change media properties').'</h3>'.
	'<form action="'.html::escapeURL($page_url).'" method="post">'.
	'<p><label>'.__('File name:').dcPage::help('media','f_name').
	form::field('media_file',30,255,html::escapeHTML($file->basename)).'</label></p>'.
	'<p><label>'.__('File title:').dcPage::help('media','f_title').
	form::field('media_title',30,255,html::escapeHTML($file->media_title)).'</label></p>'.
	'<p><label>'.__('File date:').dcPage::help('media','f_date').
	form::field('media_dt',16,16,html::escapeHTML($file->media_dtstr)).'</label></p>'.
	'<p><label class="classic">'.form::checkbox('media_private',1,$file->media_priv).' '.
	__('Private').dcPage::help('media','f_private').'</label></p>'.
	'<p><label>'.__('New directory:').dcPage::help('media','f_dir').
	form::combo('media_path',$dirs_combo,dirname($file->relname)).'</label></p>'.
	'<p><input type="submit" value="'.__('save').'" />'.
	form::hidden(array('id'),$id).'</p>'.
	'</form>';
	
	echo
	'<h3>'.__('Change file').dcPage::help('media','f_file').'</h3>'.
	'<form class="clear" action="'.html::escapeURL($page_url).'" method="post" enctype="multipart/form-data">'.
	'<div>'.form::hidden(array('MAX_FILE_SIZE'),DC_MAX_UPLOAD_SIZE).'</div>'.
	'<p><label>'.__('Choose a file:').
	' ('.sprintf(__('Maximum size %s'),files::size(DC_MAX_UPLOAD_SIZE)).') '.
	'<input type="file" name="upfile" size="35" />'.
	'</label></p>'.
	'<p><input type="submit" value="'.__('send').'" />'.
	form::hidden(array('id'),$id).'</p>'.
	'</form>';
}

echo
'</div>'.
'</div>';

call_user_func($close_f);
?>