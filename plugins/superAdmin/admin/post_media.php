<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
#
# Super Admin is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Super Admin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

dcPage::checkSuper();

$post_id = !empty($_REQUEST['post_id']) ? (integer) $_REQUEST['post_id'] : null;
$media_id = !empty($_REQUEST['media_id']) ? (integer) $_REQUEST['media_id'] : null;

if (!$post_id) {
	exit;
}
$rs = superAdmin::getPosts(array('post_id' => $post_id,'post_type'=>''));
if ($rs->isEmpty()) {
	exit;
}

# switch blog
$core->setBlog($rs->blog_id);

if ($post_id && $media_id && !empty($_POST['attach']))
{
	$core->media = new dcMedia($core);
	$core->media->addPostMedia($post_id,$media_id);
	http::redirect($p_url.'&file=post&id='.$post_id);
}

try {
	$core->media = new dcMedia($core);
	$f = $core->media->getPostMedia($post_id,$media_id);
	if (empty($f)) {
		$post_id = $media_id = null;
		throw new Exception(__('This attachment does not exist'));
	}
	$f = $f[0];
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Remove a media from en
if (($post_id && $media_id) || $core->error->flag())
{
	if (!empty($_POST['remove']))
	{
		$core->media->removePostMedia($post_id,$media_id);
		http::redirect($p_url.'&file=post&id='.$post_id.'&rmattach=1');
	}
	elseif (isset($_POST['post_id'])) {
		http::redirect($p_url.'&file=post&id='.$post_id);
	}
	
	if (!empty($_GET['remove']))
	{
		dcPage::open(__('Remove attachment'));
		
		echo '<h2>'.__('Attachment').' &rsaquo; '.__('confirm removal').'</h2>';
		
		echo
		'<form action="'.$p_url.'" method="post">'.
		form::hidden('file','post_media').
		'<p>'.__('Are you sure you want to remove this attachment?').'</p>'.
		'<p><input type="submit" value="'.__('cancel').'" /> '.
		' &nbsp; <input type="submit" name="remove" value="'.__('yes').'" />'.
		form::hidden('post_id',$post_id).
		form::hidden('media_id',$media_id).
		$core->formNonce().'</p>'.
		'</form>';
		
		dcPage::close();
		exit;
	}
}
?>