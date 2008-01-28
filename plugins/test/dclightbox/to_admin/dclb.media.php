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
catch (Exception $e) {
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

$d = isset($_REQUEST['d']) ? $_REQUEST['d'] : null;
$dir = null;
$upfile = array();

$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$nb_per_page =  30;

# We are on home not comming from media manager
if ($d === null && isset($_SESSION['media_manager_dir'])) {
	# We get session information
	$d = $_SESSION['media_manager_dir'];
}

if (!isset($_GET['page']) && isset($_SESSION['media_manager_page'])) {
	$page = $_SESSION['media_manager_page'];
}

# We set session information about directory and page
if ($d) {
	$_SESSION['media_manager_dir'] = $d;
} else {
	unset($_SESSION['media_manager_dir']);
}
if ($page != 1) {
	$_SESSION['media_manager_page'] = $page;
} else {
	unset($_SESSION['media_manager_page']);
}

$type = !empty($_GET['type']) ? $_GET['type'] : '';
$popup = (integer) !empty($_GET['popup']);

$page_url = 'dclb.media.php?type='.rawurlencode($type).'&popup='.$popup.'&post_id='.$post_id;

if ($popup) {
	$open_f = array('dcPage','openPopup');
	$close_f = array('dcPage','closePopup');
} else {
	$open_f = array('dcPage','open');
	$close_f = array('dcPage','close');
}

try {
	$core->media = new dcMedia($core,$type);
	$core->media->chdir($d);
	$core->media->getDir();
	$dir =& $core->media->dir;
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# New directory
if ($dir && !empty($_POST['newdir']))
{
	try {
		$core->media->makeDir($_POST['newdir']);
		http::redirect($page_url.'&d='.$d.'&mkdok=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Adding a file
if ($dir && !empty($_FILES['upfile']))
{
	for ($i=0; $i<count($_FILES['upfile']['name']); $i++)
	{
		$upfile[] = array(
			'name' => $_FILES['upfile']['name'][$i],
			'type' => $_FILES['upfile']['name'][$i],
			'tmp_name' => $_FILES['upfile']['tmp_name'][$i],
			'error' => $_FILES['upfile']['error'][$i],
			'size' => $_FILES['upfile']['size'][$i],
			'title' => (isset($_POST['upfiletitle'][$i]) ? $_POST['upfiletitle'][$i] : ''),
			'private' => (isset($_POST['upfilepriv'][$i]) ? $_POST['upfilepriv'][$i] : false)
		);
	}
	
	foreach ($upfile as $f)
	{
		try {
			files::uploadStatus($f);
			$core->media->uploadFile($f['tmp_name'],$f['name'],$f['title'],$f['private']);
		} catch (Exception $e) {
			$core->error->add($e->getMessage());
			break;
		}
	}
	
	if (!$core->error->flag()) {
		http::redirect($page_url.'&d='.$d.'&upok=1');
	}
}


# Removing item
if ($dir && !empty($_POST['rmyes']) && !empty($_POST['remove']))
{
	$_POST['remove'] = rawurldecode($_POST['remove']);
	
	try {
		$core->media->removeItem($_POST['remove']);
		http::redirect($page_url.'&d='.$d.'&rmfok=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Rebuild directory
if ($dir && $core->auth->isSuperAdmin() && !empty($_POST['rebuild']))
{
	try {
		$core->media->rebuild($d);
		http::redirect($page_url.'&d='.$d.'&rebuildok=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}


# DSIPLAY confirm page for rmdir & rmfile
if ($dir && !empty($_GET['remove']))
{
	call_user_func($open_f,__('Media manager'));
	
	echo '<h2>'.__('Media manager').' &gt; '.__('confirm removal').'</h2>';
	
	echo
	'<form action="'.html::escapeURL($page_url).'" method="post">'.
	'<p>'.sprintf(__('Are you sure you want to remove %s?'),
	html::escapeHTML($_GET['remove'])).'</p>'.
	'<p><input type="submit" value="'.__('cancel').'" /> '.
	' &nbsp; <input type="submit" name="rmyes" value="'.__('yes').'" />'.
	form::hidden('d',$d).
	form::hidden('remove',html::escapeHTML($_GET['remove'])).'</p>'.
	'</form>';
	
	call_user_func($close_f);
	exit;
}

/* DISPLAY Main page
-------------------------------------------------------- */
call_user_func($open_f,__('Media manager'),dcPage::jsLoad('js/_media.js'));

if (!empty($_GET['mkdok'])) {
	echo '<p class="message">'.__('Directory has been successfully created.').'</p>';
}

if (!empty($_GET['upok'])) {
	echo '<p class="message">'.__('Files have been successfully uploaded.').'</p>';
}

if (!empty($_GET['rmfok'])) {
	echo '<p class="message">'.__('File has been successfully removed.').'</p>';
}

if (!empty($_GET['rmdok'])) {
	echo '<p class="message">'.__('Directory has been successfully removed.').'</p>';
}

if (!empty($_GET['rebuildok'])) {
	echo '<p class="message">'.__('Directory has been successfully rebuilt.').'</p>';
}

if (!$dir) {
	call_user_func($close_f);
	exit;
}

echo '<h2><a href="'.html::escapeURL($page_url.'&d=').'">'.__('Media manager').'</a>'.
' / '.$core->media->breadCrum(html::escapeURL($page_url).'&amp;d=%s').'</h2>';

if ($post_id) {
	echo '<p><strong>'.sprintf(__('Choose a file to attach to entry %s by clicking on %s.'),
	'<a href="post.php?id='.$post_id.'">'.$post_title.'</a>',
	'<img src="images/plus.png" alt="'.__('Attach this file to entry').'" />').'</strong></p>';
}
if ($popup) {
	echo '<p><strong>'.sprintf(__('Choose a file to insert into entry by clicking on %s.'),
	'<img src="images/plus.png" alt="'.__('Attach this file to entry').'" />').'</strong></p>';
}


$items = array_values(array_merge($dir['dirs'],$dir['files']));
if (count($items) == 0)
{
	echo '<p><strong>'.__('No file.').'</strong></p>';
}
else
{
	$pager = new pager($page,count($items),$nb_per_page,10);
	
	echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
	
	echo '<div class="media-list">';
	for ($i=$pager->index_start, $j=0; $i<=$pager->index_end; $i++, $j++)
	{
		echo mediaItemLine($items[$i],$j);
	}
	echo '</div>';
	
	echo '<p class="clear">'.__('Page(s)').' : '.$pager->getLinks().'</p>';
}

if (is_writable($core->media->root))
{
	echo
	'<h3 id="add-file">'.__('Add files').dcPage::help('media','m_newfile').'</h3>'.
	'<form class="clear" action="'.html::escapeURL($page_url).'" method="post" enctype="multipart/form-data">'.
	'<div>'.form::hidden(array('MAX_FILE_SIZE'),DC_MAX_UPLOAD_SIZE).'</div>'.
	'<fieldset id="add-file-f">'.
	'<div>'.
	'<p><label>'.__('Choose a file:').
	' ('.sprintf(__('Maximum size %s'),files::size(DC_MAX_UPLOAD_SIZE)).')'.
	dcPage::help('media','m_file').
	'<input type="file" name="upfile[]" size="35" />'.
	'</label></p>'.
	'<p class="form-note">'.__('Please take care to publish media that you own and that are not protected by copyright.').'</p>'.
	'<p><label>'.__('Title:').dcPage::help('media','m_title').
	form::field(array('upfiletitle[]'),35,255).'</label></p>'.
	'<p><label class="classic">'.form::checkbox(array('upfilepriv[]'),1).' '.
	__('Private').dcPage::help('media','m_private').'</label></p>'.
	'</div>'.
	'<p><input type="submit" value="'.__('send').'" />'.
	form::hidden(array('d'),$d).'</p>'.
	'</fieldset>'.
	'</form>';
	
	echo
	'<h3 id="new-dir">'.__('New directory').dcPage::help('media','m_newdir').'</h3>'.
	'<form class="clear" action="'.html::escapeURL($page_url).'" method="post">'.
	'<fieldset id="new-dir-f">'.
	'<p><label>'.__('Name:').
	form::field(array('newdir'),35,255).'</label></p>'.
	'<p><input type="submit" value="'.__('save').'" />'.
	form::hidden(array('d'),html::escapeHTML($d)).'</p>'.
	'</fieldset>'.
	'</form>';
}

# rebuild directory
if ($core->auth->isSuperAdmin())
{
	echo
	'<h3 style="margin-top:2.5em;">'.__('Rebuild this directory').'</h3>'.
	'<form action="'.html::escapeURL($page_url).'" method="post">'.
	'<p>'.__('This will rebuild the database for this directory and sub directories.').'</p>'.
	'<p><input type="submit" name="rebuild" value="'.__('Rebuild directory').'" />'.
	form::hidden(array('d'),$d).'</p>'.
	'</form>';
}

# Empty remove form (for javascript actions)
echo
'<form id="media-remove-hide" action="'.html::escapeURL($page_url).'" method="post"><div>'.
form::hidden('rmyes',1).form::hidden('d',html::escapeHTML($d)).
form::hidden('remove','').
'</div></form>';

call_user_func($close_f);

/* ----------------------------------------------------- */
function mediaItemLine($f,$i)
{
	global $page_url, $type, $popup, $post_id;
	
	$fname = $f->basename;
	
	if ($f->d) {
		$link = html::escapeURL($page_url).'&amp;d='.html::sanitizeURL($f->relname);
		if ($f->parent) {
			$fname = '..';
		}
	} else {
		$link =
		'dclb.media_item.php?type='.rawurlencode($type).
		'&amp;id='.$f->media_id.'&amp;popup='.$popup.'&amp;post_id='.$post_id;
	}
	
	$class = 'media-item media-col-'.($i%2);
	
	$res =
	'<div class="'.$class.'"><a class="media-icon media-link" href="'.$link.'">'.
	'<img src="'.$f->media_icon.'" alt="" /></a>'.
	'<ul>'.
	'<li><a class="media-link" href="'.$link.'">'.$fname.'</a></li>';
	
	if (!$f->d) {
		$res .=
		'<li>'.$f->media_title.'</li>'.
		'<li>'.
		$f->media_dtstr.' - '.
		files::size($f->size).' - '.
		'<a href="'.$f->file_url.'">'.__('open').'</a>'.
		'</li>';
	}
	
	$res .= '<li class="media-action">&nbsp;';
	
	if ($post_id && !$f->d) {
		$res .= '<form action="post_media.php" method="post">'.
		'<input type="image" src="images/plus.png" alt="'.__('Attach this file to entry').'" '.
		'title="'.__('Attach this file to entry').'" /> '.
		form::hidden('media_id',$f->media_id).
		form::hidden('post_id',$post_id).
		form::hidden('attach',1).
		'</form>';
	}
	
	if ($popup && !$f->d) {
		$res .= '<a href="'.$link.'"><img src="images/plus.png" alt="'.__('Insert this file into entry').'" '.
		'title="'.__('Insert this file into entry').'" /></a> ';
	}
	
	if ($f->del) {
		$res .= '<a class="media-remove" '.
		'href="'.html::escapeURL($page_url).'&amp;d='.
		rawurlencode($GLOBALS['d']).'&amp;remove='.rawurlencode($f->basename).'">'.
		'<img src="images/trash.png" alt="'.__('delete').'" title="'.__('delete').'" /></a>';
	}
	
	$res .= '</li>';
	
	if ($f->type == 'audio/mpeg3') {
		$res .= '<li>'.$GLOBALS['core']->media->mp3player($f->file_url).'</li>';
	}
	
	$res .= '</ul></div>';
	
	return $res;
}
?>