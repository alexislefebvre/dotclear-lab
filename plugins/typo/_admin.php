<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Typo plugin for Dotclear 2.
#
# Copyright (c) 2008 Franck Paul and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

require_once dirname(__FILE__).'/inc/smartypants.php';
require_once dirname(__FILE__).'/inc/hyphenation.php';

/* Add behavior callback, will be used for all types of posts (standard, page, galery item, ...) */
$core->addBehavior('coreAfterPostContentFormat',array('adminTypo','updateTypoEntries'));

/* Add behavior callbacks, will be used for all comments (not trackbacks) */
$core->addBehavior('coreBeforeCommentCreate',array('adminTypo','updateTypoComments'));
$core->addBehavior('coreBeforeCommentUpdate',array('adminTypo','updateTypoComments'));

/* Add menu item in extension list */
$_menu['Plugins']->addItem(__('Typo'),'plugin.php?p=typo','index.php?pf=typo/icon.png',
		preg_match('/plugin.php\?p=typo(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('contentadmin',$core->blog->id));
		
/* Add behavior callbacks for posts actions */
$core->addBehavior('adminPostsActionsCombo',array('adminTypo','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('adminTypo','adminPostsActions'));
$core->addBehavior('adminPostsActionsContent',array('adminTypo','adminPostsActionsContent'));

class adminTypo
{
	public static function adminPostsActionsCombo(&$args)
	{
		global $core;
		// Add menuitem in actions dropdown list
		if ($core->auth->check('contentadmin',$core->blog->id))
			$args[0][__('typographic features')] = 'typo';
	}

	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action == 'typo')
		{
			echo
			'<h2>'.__('Apply typographic features to entries').'</h2>'.

			'<form action="posts_actions.php" method="post">'.

			'<p><label class="classic">'.
			form::checkbox('set_typo','1',$core->blog->settings->typo_active).
			' '.__('Apply typographic replacements for selected entries').'</label></p>'.
			
			'<p>'.__('Warning! These replacements will not be undoable.').'</p>'.
			
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'typo').
			'<p><input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
	}
	
	public static function adminPostsActions(&$core,$posts,$action,$redir)
	{
		if ($action == 'typo' && !empty($_POST['set_typo'])
		&& $core->auth->check('contentadmin',$core->blog->id))
		{
			try
			{
				if ((boolean)$_POST['set_typo']) {
					while ($posts->fetch())
					{
						# Apply typo features to entry
						$cur = $core->con->openCursor($core->prefix.'post');
						
						if ((boolean)$_POST['set_typo']) {
							if ($cur->excerpt_xhtml)
								$cur->excerpt_xhtml = SmartyPants($cur->excerpt_xhtml);
							if ($cur->content_xhtml)
								$cur->content_xhtml = SmartyPants($cur->content_xhtml);
						}

//						echo '<p>request : “'.$cur->getUpdate('WHERE post_id = '.(integer) $posts->post_id).'”</p>';
//						$cur->update('WHERE post_id = '.(integer) $posts->post_id);
					}
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}

	public static function updateTypoEntries($ref)
	{
		global $core;
		if ($core->blog->settings->typo_active) {
			if (@is_array($ref)) {
				/* Transform typo for excerpt (XHTML) */
				if (isset($ref['excerpt_xhtml'])) {
					$excerpt = &$ref['excerpt_xhtml'];
					if ($excerpt) {
						if ($core->blog->settings->typo_entries) {
							$excerpt = SmartyPants($excerpt);
						}
					}
				}
				/* Transform typo for content (XHTML) */
				if (isset($ref['content_xhtml'])) {
					$content = &$ref['content_xhtml'];
					if ($content) {
						if ($core->blog->settings->typo_entries) {
							$content = SmartyPants($content);
						}
					}
				}
			}
		}
	}
	
	public static function updateTypoComments(&$blog,&$cur)
	{
		global $core;
		if ($core->blog->settings->typo_active && $core->blog->settings->typo_comments)
		{
			/* Transform typo for comment content (XHTML) */
			if (!(boolean)$cur->comment_trackback) {
				if ($cur->comment_content != null) {
					if ($core->blog->settings->typo_comments)
						$cur->comment_content = SmartyPants($cur->comment_content);
				}
			}
		}
	}
}
?>
