<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009, 2011 Moe (http://gniark.net/)
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

$tab = 'cpmv_post';

$msg = (string)'';

$post_id = ( (isset($_REQUEST['post_id']))
	? (integer) $_REQUEST['post_id'] : null);
$blog_id = ( (isset($_REQUEST['blog_id']))
	? (string) $_REQUEST['blog_id'] : null);
$post_status = ( (isset($_REQUEST['post_status']))
	? (integer) $_REQUEST['post_status'] : null);

$core->blog->settings->addNamespace('superAdmin');

# posts list
$posts_list = array();

$rs = superAdmin::getPosts();

while ($rs->fetch())
{
	$posts_list[html::escapeHTML($rs->post_title).' - '.$rs->post_dt.
		' ('.html::escapeHTML($rs->blog_name).') (#'.$rs->post_id.')'] = $rs->post_id;
}

unset($rs);

# blogs list
$blogs_list = array();

# from /dotclear/inc/admin/lib.dc.page.php
$rs = $core->getBlogs(array('order'=>'LOWER(blog_name)'));
while ($rs->fetch()) {
	$blogs_list[html::escapeHTML($rs->blog_name.' ('.$rs->blog_id.')')]
		= $rs->blog_id;
}
# /from /dotclear/inc/admin/lib.dc.page.php

unset($rs);

# Status combo
$status_combo = array();
foreach ($core->blog->getAllPostStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

# actions
if (isset($_POST['copy']))
{
	# load post
	$rs = superAdmin::getPosts(array('post_id' => $post_id));
	
	# from admin/post.php
	$cur = $core->con->openCursor($core->prefix.'post');
	
	$cur->post_title = $rs->post_title;
	
	if ($rs->blog_id != $blog_id)
	{
		$cur->cat_id = null;
	}
	else
	{
		$cur->cat_id = $rs->cat_id;
	}
	
	$cur->post_dt = $rs->post_dt;
	$cur->post_tz = $rs->post_tz;
	$cur->post_format = $rs->post_format;
	$cur->post_password = $rs->post_password;
	$cur->post_type = $rs->post_type;
	$cur->post_format = $rs->post_format;
	$cur->post_lang = $rs->post_lang;
	$cur->post_title = $rs->post_title;
	$cur->post_excerpt = $rs->post_excerpt;
	$cur->post_excerpt_xhtml = $rs->post_excerpt_xhtml;
	$cur->post_content = $rs->post_content;
	$cur->post_content_xhtml = $rs->post_content_xhtml;
	$cur->post_notes = $rs->post_notes;
	
	$words =
		$rs->post_title.' '.
		$rs->post_excerpt_xhtml.' '.
		$rs->post_content_xhtml;
			
	$cur->post_words = implode(' ',text::splitWords($words));
	
	$cur->post_status = $post_status;
	$cur->post_selected = (integer) $rs->post_selected;
	$cur->post_open_comment = (integer) $rs->post_open_comment;
	$cur->post_open_tb = (integer) $rs->post_open_tb;
	
	$cur->post_url = $rs->post_url;
	
	$cur->post_meta = $rs->post_meta;
	
	$cur->user_id = $rs->user_id;
	
	$cur->blog_id = $blog_id;
	
	# switch blog
	$core->setBlog($blog_id);
	
	try
	{
		# --BEHAVIOR-- adminBeforePostCreate
		$core->callBehavior('adminBeforePostCreate',$cur);
		
		# Special case of scheduled post :
		#  save state and temporary unset to pending
		if ($post_status == -1) {
			$scheduled = true;
			$rs->post_status = -2; # will create post with pending state
		} else {
			$scheduled = false;
		}
		
		$return_id = $core->blog->addPost($cur);
		
		# Special case of scheduled post :
		#  switch back created post to scheduled state
		if ($scheduled) {
			$core->blog->updPostStatus($return_id,-1);
		}
		
		# Metadata
		$post_meta = @unserialize($rs->post_meta);
		
		if (is_array($post_meta))
		{
			foreach($post_meta as $meta_type => $values)
			{
				foreach ($values as $meta_id)
				{
					$cur = $core->con->openCursor($core->prefix.'meta');
					$cur->meta_type = $meta_type;
					$cur->meta_id = $meta_id;
					$cur->post_id = $return_id;
					$cur->insert();
				}
			}
		}
		
		# --BEHAVIOR-- adminAfterPostCreate
		$core->callBehavior('adminAfterPostCreate',$cur,$return_id);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	# /from admin/post.php
	
	unset($rs);
	
	http::redirect($p_url.'&file=cpmv_post&post_id='.$post_id.
		'&new_post_id='.$return_id.'&blog_id='.urlencode($blog_id).
		'&post_copied=1');
}
elseif (isset($_POST['move']))
{
	$cur = $core->con->openCursor($core->prefix.'post');
	$cur->blog_id = $blog_id;
	$cur->cat_id = null;
	
	$cur->post_status = $post_status;
	
	# switch blog
	$core->setBlog($blog_id);
	
	try
	{
		# --BEHAVIOR-- adminBeforePostUpdate
		$core->callBehavior('adminBeforePostUpdate',$cur,$post_id);
		
		$cur->update('WHERE post_id = '.$post_id.' ');
		
		$core->blog->triggerBlog();
		
		# --BEHAVIOR-- adminAfterPostUpdate
		$core->callBehavior('adminAfterPostUpdate',$cur,$post_id);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	
	http::redirect($p_url.'&file=cpmv_post&post_id='.$post_id.
		'&blog_id='.urlencode($blog_id).'&post_moved=1');
}

if (isset($_GET['post_copied']))
{
	$rs = superAdmin::getPosts(array('post_id' => $post_id));
	
	$rs_blog = $core->getBlogs(array('blog_id' => $_GET['blog_id']));
	
	$blog_name = $rs_blog->blog_name;
	
	$new_entry_id = '';

	if (isset($_GET['new_post_id']))
	{
		if ($core->blog->settings->superAdmin->enable_content_edition)
		{
			$class = '';
			if ($rs->blog_id != $core->blog->id)
			{
				$class = ' class="superAdmin-change-blog"';
			}
			
			$new_entry_id = 
			'<a href="'.
				$core->getPostAdminURL($rs->post_type,$_GET['new_post_id']).
				'&amp;switchblog='.urlencode($blog_id).
				'"'.$class.'>'.
				$_GET['new_post_id'].'</a>';
		}
		else
		{
			$new_entry_id = $_GET['new_post_id'];
		}
	}
	
	$msg = sprintf(__('Entry #%1$s %2$s copied to blog %3$s, new entry id: #%4$s'),
		$post_id,'<strong>'.$rs->post_title.'</strong>',
		'<strong>'.$blog_name.'</strong>',$new_entry_id);
	
	$blog_id = $rs->blog_id;
} elseif (isset($_GET['post_moved']))
{
	$rs = superAdmin::getPosts(array('post_id' => $post_id));
	
	if ($core->blog->settings->superAdmin->enable_content_edition)
	{
		$class = '';
		if ($rs->blog_id != $core->blog->id)
		{
			$class = ' class="superAdmin-change-blog"';
		}
		
		$msg = sprintf(__('Entry #%1$s %2$s moved to blog %3$s'),
			'<a href="'.
				$core->getPostAdminURL($rs->post_type,$post_id).
				'&amp;switchblog='.urlencode($blog_id).
				'"'.$class.'>'.$post_id.'</a>',
			'<strong>'.$rs->post_title.'</strong>',
			'<strong>'.$rs->blog_name.'</strong>');
	}
	else
	{
		$msg = sprintf(__('Entry #%1$s %2$s moved to blog %3$s'),
			$post_id,
			'<strong>'.$rs->post_title.'</strong>',
			'<strong>'.$rs->blog_name.'</strong>');
	}
	
	$blog_id = $rs->blog_id;
}

/* DISPLAY
-------------------------------------------------------- */

dcPage::open(__('Copy or move entry').' &laquo; '.__('Super Admin'),
	dcPage::jsPageTabs($tab).
	"<script type=\"text/javascript\">
  //<![CDATA[
  ".
  	dcPage::jsVar('dotclear.msg.confirm_copy_post',
  	__('Are you sure you want to copy the post?')).
  	dcPage::jsVar('dotclear.msg.confirm_move_post',
  	__('Are you sure you want to move the post?')).
  	dcPage::jsVar('dotclear.msg.confirm_change_blog',
  	__('Are you sure you want to change the current blog?').' '.
  		__('See the help for more information.')).
  	"
  	$(function() {
			$('input[name=\"copy\"]').click(function() {
				return window.confirm(dotclear.msg.confirm_copy_post);
			});
			$('input[name=\"move\"]').click(function() {
				return window.confirm(dotclear.msg.confirm_move_post);
			});
			$('.superAdmin-change-blog').click(function() {
				return window.confirm(dotclear.msg.confirm_change_blog);
			});
		});
  //]]>
  </script>");

echo('<h2>'.html::escapeHTML('Super Admin').' &rsaquo; '.__('Copy or move entry').'</h2>');

if (!empty($msg)) {echo '<p class="message">'.$msg.'</p>';}

echo('<p><a href="'.$p_url.'&amp;file=posts" class="multi-part">'.
	__('Entries').'</a></p>');
echo('<p><a href="'.$p_url.'&amp;file=comments" class="multi-part">'.
	__('Comments').'</a></p>');

echo('<div class="multi-part" id="cpmv_post" title="'.__('Copy or move entry').'">');

if ((!isset($_COOKIE['superadmin_default_tab']))
	OR ((isset($_COOKIE['superadmin_default_tab']))
		&& ($_COOKIE['superadmin_default_tab'] != 'cpmv_post')))
{
	echo('<p><a href="'.$p_url.'&amp;file=cpmv_post&amp;default_tab=cpmv_post" class="button">'.
		__('Make this tab my default tab').'</a></p>');
}

echo('<form method="post" action="'.$p_url.'">'.
		form::hidden('p','superAdmin').
		form::hidden('file','cpmv_post').
		'<p><label>'.__('Entry:').
			form::combo('post_id',$posts_list,$post_id).'</label></p> '.
		'<p><label>'.__('Copy or move to blog:').
			form::combo('blog_id',$blogs_list,$blog_id).'</label></p> '.
		'<p><label>'.__('Status of the copied or moved entry:').
			form::combo('post_status',$status_combo,-2,'',3).
			'</label></p>'.
		'<p class="form-note"><big>'.
			__('The category will be removed if the entry is not copied to the same blog.').
			'</big></p>'.
		$core->formNonce().
		'<p><input type="submit" name="copy" value="'.__('Copy').'" /> '.
		'<input type="submit" name="move" value="'.__('Move').'" /></p>'.
	'</form>');
echo('</div>');

echo('<p><a href="'.$p_url.'&amp;file=medias" class="multi-part">'.
	__('Media directories').'</a></p>');
echo('<p><a href="'.$p_url.'&amp;file=settings" class="multi-part">'.
	__('Settings').'</a></p>');

if ($core->blog->settings->superAdmin->enable_content_edition)
{
	dcPage::helpBlock('change_blog');
}

dcPage::close();
?>