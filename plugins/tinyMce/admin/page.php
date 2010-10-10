<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }
dcPage::check('pages,contentadmin');

$default_page = $core->getPostAdminURL('page',$_REQUEST['id']);

$redir_url = $p_url.'&type=page';

$post_id = '';
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
$post_status = $core->auth->getInfo('user_post_status');
$post_position = 0;
$post_open_comment = false;
$post_open_tb = false;

$post_media = array();

$page_title = __('New page');

$can_view_page = true;
$can_edit_page = $core->auth->check('page,usage',$core->blog->id);
$can_publish = $core->auth->check('page,publish,contentadmin',$core->blog->id);
$can_delete = false;

// link
$tmp = $redir_url;

$redir_url = 'plugin.php?p=page&p=pages';

$post_headlink = '<link rel="%s" title="%s" href="'.html::escapeURL($redir_url).'&amp;id=%s" />';
$post_link = '<a href="'.html::escapeURL($redir_url).'&amp;id=%s" title="%s">%s</a>';

$redir_url = $tmp;
unset($tmp);

$next_link = $prev_link = $next_headlink = $prev_headlink = null;

# If user can't publish
if (!$can_publish) {
	$post_status = -2;
}

# Get page informations
if (!empty($_REQUEST['id']))
{
	$params['post_type'] = 'page';
	$params['post_id'] = $_REQUEST['id'];
	
	$post = $core->blog->getPosts($params);
	
	if ($post->isEmpty())
	{
		$core->error->add(__('This page does not exist.'));
		$can_view_page = false;
	}
	else
	{
		$post_id = $post->post_id;
		$post_dt = date('Y-m-d H:i',strtotime($post->post_dt));
		$post_format = $post->post_format;
		
		# check post format
		if (($post_format != 'xhtml') && (empty($_GET['xconv'])))
		{
			$core->error->add(__('This entry format is not XHTML.').' '.
				__('You have to convert this entry to XHTML format to use TinyMCE, this operation cannot be undone.').' '.
				'<a href="'.$p_url.'&amp;type=page&amp;id='.$post_id.'&amp;xconv=1" '.
				'id="tinyMce-convert" class="button">'.
					__('Convert this post to XHTML format').'</a> '.
				'<a href="'.$default_page.'" class="button">'.
					__('Return to the default page').'</a> ');
			$can_view_page = false;
		}
		
		$post_password = $post->post_password;
		$post_url = $post->post_url;
		$post_lang = $post->post_lang;
		$post_title = $post->post_title;
		$post_excerpt = $post->post_excerpt;
		$post_excerpt_xhtml = $post->post_excerpt_xhtml;
		$post_content = $post->post_content;
		$post_content_xhtml = $post->post_content_xhtml;
		$post_status = $post->post_status;
		$post_position = (integer) $post->post_position;
		$post_open_comment = (boolean) $post->post_open_comment;
		$post_open_tb = (boolean) $post->post_open_tb;
		
		$page_title = __('Edit page');
		
		$can_edit_page = $post->isEditable();
		$can_delete= $post->isDeletable();
		
		$next_rs = $core->blog->getNextPost($post,1);
		$prev_rs = $core->blog->getNextPost($post,-1);
		
		if ($next_rs !== null) {
			$next_link = sprintf($post_link,$next_rs->post_id,
				html::escapeHTML($next_rs->post_title),__('next page').'&nbsp;&#187;');
			$next_headlink = sprintf($post_headlink,'next',
				html::escapeHTML($next_rs->post_title),$next_rs->post_id);
		}
		
		if ($prev_rs !== null) {
			$prev_link = sprintf($post_link,$prev_rs->post_id,
				html::escapeHTML($prev_rs->post_title),'&#171;&nbsp;'.__('previous page'));
			$prev_headlink = sprintf($post_headlink,'previous',
				html::escapeHTML($prev_rs->post_title),$prev_rs->post_id);
		}
		
		try {
			$core->media = new dcMedia($core);
			$post_media = $core->media->getPostMedia($post_id);
		} catch (Exception $e) {}
	}
}

# Format content
if (!empty($_POST) && $can_edit_page)
{
	$post_format = $_POST['post_format'];
	$post_excerpt = $_POST['post_excerpt'];
	$post_content = $_POST['post_content'];
	
	$post_title = $_POST['post_title'];
	
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
	$post_lang = $_POST['post_lang'];
	$post_password = !empty($_POST['post_password']) ? $_POST['post_password'] : null;
	$post_position = (integer) $_POST['post_position'];
	
	if (isset($_POST['post_url'])) {
		$post_url = $_POST['post_url'];
	}
	
	$core->blog->setPostContent(
		$post_id,$post_format,$post_lang,
		$post_excerpt,$post_excerpt_xhtml,$post_content,$post_content_xhtml
	);
}

# Create or update post
if (!empty($_POST) && !empty($_POST['save']) && $can_edit_page)
{
	$cur = $core->con->openCursor($core->prefix.'post');
	
	# Magic tweak :)
	$core->blog->settings->setNameSpace('pages');
	
	$core->blog->settings->post_url_format = $page_url_format;
	$core->blog->settings->setNameSpace('system');
	
	$cur->post_type = 'page';
	$cur->post_title = $post_title;
	$cur->post_dt = $post_dt ? date('Y-m-d H:i:00',strtotime($post_dt)) : '';
	$cur->post_format = $post_format;
	$cur->post_password = $post_password;
	$cur->post_lang = $post_lang;
	$cur->post_title = $post_title;
	$cur->post_excerpt = $post_excerpt;
	$cur->post_excerpt_xhtml = $post_excerpt_xhtml;
	$cur->post_content = $post_content;
	$cur->post_content_xhtml = $post_content_xhtml;
	$cur->post_status = $post_status;
	$cur->post_position = $post_position;
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
			# --BEHAVIOR-- adminBeforePageUpdate
			//$core->callBehavior('adminBeforePageUpdate',$cur,$post_id);
			
			$core->blog->updPost($post_id,$cur);
			
			# --BEHAVIOR-- adminAfterPageUpdate
			//$core->callBehavior('adminAfterPageUpdate',$cur,$post_id);
			
			http::redirect($redir_url.'&id='.$post_id.'&upd=1');
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}
}

dcPage::open($page_title.' - '.__('Pages'),
	dcPage::jsModal().
	dcPage::jsConfirmClose('entry-form').
	# --BEHAVIOR-- adminPostHeaders
	//$core->callBehavior('adminPostHeaders').
	$next_headlink."\n".$prev_headlink.
	# TinyMCE Javascript
	'<script type="text/javascript" src="index.php?pf='.
		'tinyMce/js/tiny_mce_jquery/jquery.tinymce.js">'.
	'</script>'.
	'<script type="text/javascript" src="index.php?pf='.
		'tinyMce/js/init_jquery.js">'.
	'</script>'
);

if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Page has been successfully updated.').'</p>';
}

# XHTML conversion
if (!empty($_GET['xconv']))
{
	$post_excerpt = $post_excerpt_xhtml;
	$post_content = $post_content_xhtml;
	$post_format = 'xhtml';
	
	echo '<p class="message">'.__('Don\'t forget to validate your XHTML conversion by saving your post.').'</p>';
}

echo '<h2>'.html::escapeHTML($core->blog->name).
' &rsaquo; <a href="'.$p_url.'">'.__('Pages').'</a> &rsaquo; '.$page_title;

if ($post_id && $post->post_status == 1) {
	echo ' - <a id="post-preview" href="'.$post->getURL().'" class="button">'.__('View page').'</a>';
} elseif ($post_id) {
	$preview_url =
	$core->blog->url.$core->url->getBase('pagespreview').'/'.
	$core->auth->userID().'/'.
	http::browserUID(DC_MASTER_KEY.$core->auth->userID().$core->auth->getInfo('user_pwd')).
	'/'.$post->post_url;
	echo ' - <a id="post-preview" href="'.$preview_url.'" class="button">'.__('Preview page').'</a>';
}

echo '</h2>';

if ($post_id)
{
	echo '<p>';
	if ($prev_link) { echo $prev_link; }
	if ($next_link && $prev_link) { echo ' - '; }
	if ($next_link) { echo $next_link; }
	
	# --BEHAVIOR-- adminPageNavLinks 
	//$core->callBehavior('adminPageNavLinks',isset($post) ? $post : null);
	
	# link to default page
	echo('<p><a href="'.$default_page.'" class="button">'.
		__('Return to the default page').'</a></p>');
	
	echo '</p>';
}

# Exit if we cannot view page
if (!$can_view_page) {
	echo '</body></html>';
	return;
}


/* Post form if we can edit post
-------------------------------------------------------- */
if ($can_edit_page)
{
	echo '<div class="multi-part" title="'.__('Edit page').'" id="edit-entry">';
	echo '<form action="'.html::escapeURL($redir_url).'" method="post" id="entry-form">';
	echo form::hidden('p','tinyMce');
	echo form::hidden('type','page');
	
	echo(
		form::hidden('post_status',$post_status).
		form::hidden('post_dt',$post_dt).
		form::hidden('post_format',$post_format).
		
		form::hidden('post_open_comment',(integer) $post_open_comment).
		form::hidden('post_open_tb',(integer) $post_open_tb).
		form::hidden('post_lang',$post_lang).
		form::hidden('post_password',html::escapeHTML($post_password)).
		form::hidden('post_url',html::escapeHTML($post_url)).
		
		form::hidden('post_position',(string) $post_position)
	);
	
	# --BEHAVIOR-- adminPageFormSidebar
	//$core->callBehavior('adminPageFormSidebar',isset($post) ? $post : null);
	
	echo '<div><fieldset class="constrained">';
	
	echo
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').
	form::field('post_title',20,255,html::escapeHTML($post_title),'maximal',2).
	'</label></p>'.
	
	'<p class="area" id="excerpt-area"><label for="post_excerpt">'.__('Excerpt:').'</label> '.
	form::textarea('post_excerpt',50,5,html::escapeHTML($post_excerpt),'tinymce',2).
	'</p>'.
	
	'<p class="area"><label class="required" title="'.__('Required field').'" '.
	'for="post_content">'.__('Content:').'</label> '.
	form::textarea('post_content',50,$core->auth->getOption('edit_size'),html::escapeHTML($post_content),'tinymce',2).
	'</p>';
	
	# --BEHAVIOR-- adminPageForm
	//$core->callBehavior('adminPageForm',isset($post) ? $post : null);
	
	echo
	'<p>'.
	($post_id ? form::hidden('id',$post_id) : '').
	'<input type="submit" value="'.__('save').' (s)" tabindex="4" '.
	'accesskey="s" name="save" /> '.
	$core->formNonce().
	'</p>';
	
	echo '</fieldset></div>';		// End #entry-content
	echo '</form>';
	echo '</div>';
}

dcPage::close();

?>