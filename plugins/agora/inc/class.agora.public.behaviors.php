<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2012 Osku and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class agorapublicBehaviors
{
	// Template directory 
	public static function publicBeforeDocument($core)
	{
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/../default-templates');
	}

	// We set all cache to false (authentication tweak)
	public static function urlHandlerBeforeGetData($_ctx)
	{
		$_ctx->http_cache = false;
		$_ctx->http_etag = false;
	}

	// Update post_dt entry when a published message is writtern
	public static function updPubDatePost($cur,$message_id)
	{
		if ($cur->message_status == 1) {
			$GLOBALS['core']->agora->triggerPost($cur->post_id);
		}
	}

	// Update messages entry count
	public static function countMessages($cur,$message_id)
	{
		$GLOBALS['core']->agora->triggerMessage($message_id);
	}

	// trick for feeds : we only display published content in feeds
	public static function templateBeforeBlock($core,$b,$attr)
	{
		if ($b == 'Entries' && preg_match('#feed#',$core->url->type)) 
		{
			return
			'<?php '.
				"\$params['sql'] = 'AND post_status = 1';\n".
			" ?>\n";
		}
		elseif ($b == 'Messages' && preg_match('#feed#',$core->url->type)) 
		{
			return
			'<?php '.
				"\$params['sql'] = 'AND post_status = 1 AND message_status = 1';\n".
			" ?>\n";
		}
	}

	public static function mytemplateCustomSortByAlias($alias)
	{
		$alias = array(
			'user' => array(
				'author' => 'user_id',
				'date' => 'user_creadt',
				'post' => 'nb_post',
				'message' => 'nb_message'
			)
		);
	}

	// Check if Messages flag is open
	public static function tplIfConditions($tag,$attr,$content,$if)
	{
		global $core;

		if ($tag == "EntryIf" && isset($attr['messages_active'])) {
			$sign = (boolean) $attr['messages_active'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->messagesActive()';
		}
		if ($tag == "EntryIf" && isset($attr['show_messages'])) {
			if ((boolean) $attr['show_messages']) {
				$if[] = '($_ctx->posts->hasMessages() || $_ctx->posts->messagesActive())';
			} else {
				$if[] = '(!$_ctx->posts->hasMessages() && !$_ctx->posts->messagesActive())';
			}
		}
		if ($tag == "EntryIf" && isset($attr['is_published'])) {
			$sign = (boolean) $attr['is_published'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->isPublished()';
		}
		if ($tag == "EntryIf" && isset($attr['is_editable'])) {
			$sign = (boolean) $attr['is_editable'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->isEditable()';
		}
		if ($tag == "EntryIf" && isset($attr['auth_me'])) {
			$sign = (boolean) $attr['auth_me'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->authMe()';
		}
		if ($tag == "SysIf" && isset($attr['messages'])) {
			$sign = (boolean) $attr['messages'] ? '!' : '=';
			$if[] = '$_ctx->messages '.$sign.'== null';
		}
		if ($tag == "SysIf" && isset($attr['user_search_count']) &&
			preg_match('/^((=|!|&gt;|&lt;)=|(&gt;|&lt;))\s*[0-9]+$/',trim($attr['user_search_count']))) {
			$if[] = '(isset($_user_search_count) && $_user_search_count '.html::decodeEntities($attr['user_search_count']).')';
		}

		if ($tag == "SysIf" && isset($attr['is_auth'])) {
			$sign = (boolean) $attr['is_auth'] ? '' : '!';
			//$if[] = '$_ctx->messages '.$sign.'== null';
			$if[] = $sign.'$core->auth->userID() !== false && isset($_SESSION[\'sess_user_id\'])';
		}
		if ($tag == "SysIf" && isset($attr['user_id'])) {
			$sign = '';
			if (substr($attr['user_id'],0,1) == '!') {
				$sign = '!';
				$attr['user_id'] = substr($attr['user_id'],1);
			}
			$if[] = $sign."(\$core->auth->userID() == '".addslashes($attr['user_id'])."')";
		}
	}

	// DOESN'T WORK
	public static function templateCustomSortByAlias($alias)
	{
		$alias = array(
			'message' => array(
				'author' => 'user_id',
				'date' => 'message_dt',
				'id' => 'message_id',
				'post_id' => 'post_id'
			),
			'user' => array(
				'author' => 'user_id',
				'date' => 'user_creadt',
				'post' => 'nb_post',
				'message' => 'nb_message'
			)
		);
	}

	// Recover password and register links on login page
	public static function publicLoginFormAfter($core)
	{
		$res = '';
		if ($core->blog->settings->agora->recover_flag) {
			$res .= '<p><a href="'.$core->blog->url.$core->url->getURLFor("recover").'/">'.__('I forgot my password').'</a></p>';
		}
		if ($core->blog->settings->agora->register_flag) {
			$res .= '<p><a href="'.$core->blog->url.$core->url->getURLFor("register").'">'.__('Register').'</a></p>';
		}
		echo $res;
	}

	// New entry form options
	public static function publicEntryFormBefore($core)
	{
		$cat_id = isset($GLOBALS['_ctx']->post_preview["cat"]) ? $GLOBALS['_ctx']->post_preview["cat"] : '';
		$selected = isset($GLOBALS['_ctx']->post_preview["selected"]) ? $GLOBALS['_ctx']->post_preview["selected"] : '';
		$excerpt = isset($GLOBALS['_ctx']->post_preview["rawexcerpt"]) ? $GLOBALS['_ctx']->post_preview["rawexcerpt"] : '';

		# Getting categories
		if ($core->blog->settings->agora->empty_category) {
			$categories_combo = array('&nbsp;' => '');
		}
		try {
			$categories = $core->blog->getCategories(array('post_type'=>'post'));
			if (!$categories->isEmpty()) {
				while ($categories->fetch()) {
					$categories_combo[] = new formSelectOption(
						str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.html::escapeHTML($categories->cat_title),
						$categories->cat_id
					);
				}
			}
		} catch (Exception $e) { }
		
		if (!$categories->isEmpty()) {
			echo
			'<p class="field"><label for="c_cat">'.__('Category').'&nbsp;:</label>'.
			form::combo('c_cat',$categories_combo,$cat_id).
			'</p>';
		}
		if ($core->blog->settings->agora->entry_excerpt) {
			echo
			'<p class="field"><label for="c_excerpt">'.__('Excerpt (optional)').'&nbsp;:</label>'.
			form::textarea('c_excerpt',35,8,$excerpt).'</p>';
		}
	}

	// Save category and selected flag in base
	public static function publicBeforePostUpdate($cur)
	{
		if (isset($_POST['c_cat']) && ((integer) $_POST['c_cat'] > 0)) {
			$cur->cat_id = (integer) $_POST['c_cat'] ;
		} else {
			$cur->cat_id = null;
		}
		if (!empty($_POST['c_excerpt'])) {
			$cur->post_excerpt = $_POST['c_excerpt'];
			//$cur->post_excerpt_xhtml = $GLOBALS['_ctx']->post_preview['excerpt'];
		}
	}

	public static function publicEntryPreviewBeforeContent($core)
	{
		echo '<div class="post-excerpt">'.$GLOBALS['_ctx']->post_preview['excerpt'].'</div>';
	}

	public static function publicBeforePostPreview($post)
	{
		if (isset($_POST['c_cat']) && ((integer) $_POST['c_cat'] > 0)) {
			$post['cat'] = (integer) $_POST['c_cat'] ;
		} else {
			$post['cat'] = null;
		}
		
		if (!empty($_POST['c_excerpt'])) {
			$post['rawexcerpt'] = $_POST['c_excerpt'];
			$post['excerpt'] = $GLOBALS['core']->callFormater($GLOBALS['core']->blog->settings->agora->content_syntax,$_POST['c_excerpt']);
			$post['excerpt'] = $GLOBALS['core']->HTMLfilter($post['excerpt']);
		}
	}

	public static function publicPreferencesAfterContent($core)
	{
		echo $GLOBALS['_ctx']->users->wikiDesc(); // TPL Now
	}

	public static function publicPreferencesBeforeContent($core)
	{
		if ($GLOBALS['_ctx']->users->hasAvatar()) {
			echo '<img class="right" 
				src="'.$GLOBALS['_ctx']->users->mediaDir().'.avatar_t.jpg" 
				alt="'.html::escapeHTML($GLOBALS['_ctx']->users->getAuthorCN()).'" />';
		} elseif (mediaAgora::defaultAvatarExists()) {
			echo '<img class="right" 
				src="'.mediaAgora::imagesURL().'/.avatar_t.jpg" 
				alt="'.html::escapeHTML($GLOBALS['_ctx']->users->getAuthorCN()).'" />';
		}
	}

	public static function publicEntryBeforeContent($core)
	{
		if ($GLOBALS['_ctx']->posts->hasAvatar()) {
			echo '<img class="right" 
				src="'.$GLOBALS['_ctx']->posts->mediaDir().'.avatar_sq.jpg" 
				alt="'.html::escapeHTML($GLOBALS['_ctx']->posts->getAuthorCN()).'" />';
		} elseif (mediaAgora::defaultAvatarExists()) {
			echo '<img class="right" 
				src="'.mediaAgora::imagesURL().'/.avatar_sq.jpg" 
				alt="'.html::escapeHTML($GLOBALS['_ctx']->posts->getAuthorCN()).'" />';
		}
	}

	public static function publicMessageBeforeContent($core)
	{
		if ($GLOBALS['_ctx']->messages->hasAvatar()) {
			echo '<img class="right" 
				src="'.$GLOBALS['_ctx']->messages->mediaDir().'.avatar_sq.jpg" 
				alt="'.html::escapeHTML($GLOBALS['_ctx']->messages->getAuthorCN()).'" />';
		} elseif (mediaAgora::defaultAvatarExists()) {
			echo '<img class="right" 
				src="'.mediaAgora::imagesURL().'/.avatar_sq.jpg" 
				alt="'.html::escapeHTML($GLOBALS['_ctx']->messages->getAuthorCN()).'" />';
		}
	}

	public static function publicPreferencesFormBefore($core)
	{
		if ($core->blog->settings->agora->modify_pseudo) {
			echo '<p class="field"><label for="li_pseudo">'.__('Name or nickname:').'</label>
				<input name="li_pseudo" id="li_pseudo" type="text" size="30" maxlength="255"
				value="'.html::escapeHTML($GLOBALS['_ctx']->users->user_displayname).'" />
				</p>';
		}
	}

	public static function publicPreferencesFormAfter($core)
	{
		$res ='';
		
		if ($core->auth->allowPassChange()) {
			$res = 
			'<p class="field"><label for="li_pwd">'.__('New password:').'</label>'.
			form::password('li_pwd',30,255).'</p>
			<p class="field"><label for="li_pwd2">'.__('Confirm password:').'</label>'.
			form::password('li_pwd2',30,255).'</p>';
		}
		
		if ($core->blog->settings->agora->user_desc) {
			$res .= 
			'<p class="field"><label for="c_content">'.__('Informations:').'</label>'.
			form::textarea('c_content',35,10,html::escapeHTML($GLOBALS['_ctx']->users->user_desc)).'</p>';
		}
		
		if ((string)$core->blog->settings->agora->avatar > 0) {
			$res .= 
			'<p id="uploader" class="field"><label for="c_upfile">'.__('Avatar:').'</label>'.
			'<input type="file" name="c_upfile" id="c_upfile" size="30" />'.
			'</p>';
		}
		
		echo $res;
	}

	public static function publicBeforeUserUpdate($cur)
	{
		if (isset($_POST['li_pseudo'])) {
			$cur->user_displayname = trim($_POST['li_pseudo']);
		}
		
		if (isset($_POST['c_content'])) {
			$cur->user_desc = trim($_POST['c_content']);
		}
		
		if (!empty($_FILES['c_upfile']) 
			&& !empty($_FILES['c_upfile']['name']) 
			&& mediaAgora::checkType($_FILES['c_upfile']))
		{
			$GLOBALS['core']->auth->sudo(
				array($GLOBALS['core']->agora,'uploadFile'),
				$_FILES['c_upfile'],
				'avatar.jpg',
				$cur->user_id
			);
		}
	}

	public static function publicEntryAfterContent($core)
	{
		//$status = $core->blog->getAllPostStatus();
		$edit = $publish = '';
		
		if ($core->auth->check('contentadmin',$core->blog->id) 
			&& $core->url->type == $GLOBALS['_ctx']->posts->post_type) 
		{
			$publish = (string) $GLOBALS['_ctx']->posts->post_status != 1 ? 
				' <a class="button publish"
					 href="'.$core->blog->url.
						$core->url->getURLFor("publishpost",$GLOBALS['_ctx']->posts->post_id). '" 
					title="'.__('publish this entry').'">'.__('publish').
				'</a>' : 
				' <a class="button unpublish" 
					href="'.$core->blog->url.
						$core->url->getURLFor("unpublishpost",$GLOBALS['_ctx']->posts->post_id). '" 
					title="'.__('unpublish this entry').'">'.__('unpublish').
				'</a>';
		}
		
		if ((integer)$GLOBALS['_ctx']->posts->post_status != 1 ) {
			$publish =  '<em> - '.__($GLOBALS['_ctx']->posts->getStatus()).' - </em>'.$publish;
		}
		
		$edit = 
			((($GLOBALS['_ctx']->posts->isEditable()) 
			//&& $GLOBALS['_ctx']->posts->isStillinTime() 
			|| ($core->blog->settings->agora->wiki_flag
			&& $core->auth->userID())
			)
			&& $core->url->type == $GLOBALS['_ctx']->posts->post_type)
			? 
			'<a class="edit" href="'.$core->blog->url.$core->url->getURLFor("editpost",$GLOBALS['_ctx']->posts->post_id).'"
			title="'.__('edit this entry').'">'.__('edit').'</a>' : '';
		
		if ($publish || $edit) {
			echo '<p class ="posts-actions">'.$edit.'&nbsp;'.$publish.'</p>';
		}
	}

	public static function publicMessageAfterContent($core)
	{
		$status = $core->agora->getAllMessageStatus();
		$edit = $publish = '';
		
		if ($core->agora->isModerator($core->auth->userID())) 
		{
			$publish = (string) $GLOBALS['_ctx']->messages->message_status != 1 ? 
				' <a class="button publish"
					href="'.$core->blog->url.
						$core->url->getURLFor("publishmessage",$GLOBALS['_ctx']->messages->message_id). '" 
					title="'.__('publish this message').'">'.__('publish').
				'</a>' : 
				' <a class="button unpublish"
					href="'.$core->blog->url.
						$core->url->getURLFor("unpublishmessage",$GLOBALS['_ctx']->messages->message_id). '" 
				title="'.__('unpublish this message').'">'.__('unpublish').
				'</a>';	
		}
		
		if ((integer)$GLOBALS['_ctx']->messages->message_status != 1 ) {
			$publish = '<em> - '.__($status[$GLOBALS['_ctx']->messages->message_status]).' - </em>'.$publish;
		} 
		
		$edit = 
			($GLOBALS['_ctx']->messages->isEditable())
			&& $GLOBALS['_ctx']->messages->isStillinTime() ? 
			'<a class="edit" href="'.$core->blog->url.$core->url->getURLFor("editmessage",$GLOBALS['_ctx']->messages->message_id).'"
			title="'.__('edit this message').'">'.__('edit').'</a>' : '';
		
		if ($publish || $edit) {
			echo '<p class ="messages-actions">'.$edit.'&nbsp;'.$publish.'</p>';
		}
	}

	public static function markItUpCSS()
	{
		global $core;
		if (!$core->auth->userID() || $core->blog->settings->agora->content_syntax != 'wiki') 
		{
			return;
		}

		$main_css = html::stripHostURL($core->blog->getQmarkURL().'pf=agora/js/markitup/skins/simple/style.css');
		$syntax_css = html::stripHostURL($core->blog->getQmarkURL().'pf=agora/js/markitup/sets/dotclear/style.css');
		
		echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$main_css.'"/>'."\n".
			'<link rel="stylesheet" type="text/css" media="screen" href="'.$syntax_css.'"/>'."\n";
	}


	public static function markItUpJS()
	{
		global $core;

		if (!$core->auth->userID() || $core->blog->settings->agora->content_syntax != 'wiki') 
		{
			return;
		}

		$allowed_types = new ArrayObject(array('new','editpost'));
		$allowed_types_m = new ArrayObject(array('post','editmessage'));
		$core->callBehavior('initAgoraMarkItUp',$allowed_types,$allowed_types_m);

		$main_js = html::stripHostURL($core->blog->getQmarkURL().'pf=agora/js/markitup/jquery.markitup.js');
		$syntax_js = html::stripHostURL($core->blog->getQmarkURL().'pf=agora/js/markitup/sets/dotclear/set.js');
		$post_js = '<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			"addListener(window,'load',function() {\n".
			"if (document.getElementById) { \n".
			"	if (document.getElementById('".html::escapeJS('c_content')."')) { \n".
			"		$('#c_content').markItUp(myPostSettings);\n".
			"		$('#c_excerpt').markItUp(myPostSettings);\n".
			"	}\n".
			"}\n".
			"});\n".
			"\n//]]>\n".
			"</script>\n";
		$message_js = '<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			"addListener(window,'load',function() {\n".
			"if (document.getElementById) { \n".
			"	if (document.getElementById('".html::escapeJS('c_content')."')) { \n".
			"		$('#c_content').markItUp(myMessageSettings);\n".
			"	}\n".
			"}\n".
			"});\n".
			"\n//]]>\n".
			"</script>\n";
		if (in_array($core->url->type,(array)$allowed_types))
		{
			echo '<script type="text/javascript" src="'.$main_js.'"></script>'."\n".
				'<script type="text/javascript" src="'.$syntax_js.'"></script>'."\n".
				$post_js."\n";
		} elseif (in_array($core->url->type,(array)$allowed_types_m))
		{
			echo '<script type="text/javascript" src="'.$main_js.'"></script>'."\n".
				'<script type="text/javascript" src="'.$syntax_js.'"></script>'."\n".
				$message_js."\n";
		}
	}
}
?>
