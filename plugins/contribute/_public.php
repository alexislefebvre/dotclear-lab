<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute.
# Copyright 2008 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) { return; }


# load locales for the blog language
l10n::set(dirname(__FILE__).'/locales/'.$core->blog->settings->lang.'/public');

/**
@ingroup Contribute
@brief Document
*/
class contributeDocument extends dcUrlHandlers
{
	/**
	serve the document
	@param	args	<b>string</b>	Argument
	*/
	public static function page($args)
	{
		global $core;

		if (!$core->blog->settings->noname_active) {self::p404();}

		# start session
		$session_id = session_id();
		if (empty($session_id)) {session_start();}
		
		$_ctx =& $GLOBALS['_ctx'];
		
		$_ctx->contribute = new ArrayObject();
		$_ctx->contribute->message = $_ctx->contribute->error = '';
		$_ctx->contribute->preview = false;
		$_ctx->contribute->form = true;
		
		$_ctx->comment_preview = new ArrayObject();
		$_ctx->comment_preview['name'] = __('Anonymous');
		$_ctx->comment_preview['mail'] = $_ctx->comment_preview['site'] = '';
		
		
		try
		{
			# this may be dangerous
			$_ctx->posts = $core->blog->getPosts(array('post_id' => -1));
			
			$post =& $_ctx->posts;			
			
			$post->post_dt = date('Y-m-d H:i:00');
			$post->post_title = ((isset($_POST['post_title'])) ? $_POST['post_title'] : '');
			$post->cat_id = ((isset($_POST['cat_id'])) ? $_POST['cat_id'] : '');
			
			if (($post->cat_id != '') && (!preg_match('/^[0-9]+$/',$post->cat_id)))
			{
				$_ctx->contribute->error = __('Invalid cat_id');
			}
			$post->post_excerpt_xhtml = ((isset($_POST['post_excerpt'])) ? $core->wikiTransform($_POST['post_excerpt']) : '');
			$post->post_excerpt_wiki = ((isset($_POST['post_excerpt'])) ? $_POST['post_excerpt'] : '');
			$post->post_content_xhtml = ((isset($_POST['post_content'])) ? $core->wikiTransform($_POST['post_content']) : '');
			$post->post_content_wiki = ((isset($_POST['post_content'])) ? $_POST['post_content'] : '');
			
			if (isset($_POST['preview']))
			{
				if (!isset($_POST['post_title']) || empty($_POST['post_title']))
				{
					$_ctx->contribute->error = __('No entry title');
				} elseif (!isset($_POST['post_content']) || empty($_POST['post_content']))
				{
					$_ctx->contribute->error = __('No entry content');
				} else {
					$_ctx->contribute->preview = true;
					$_ctx->contribute->message = __('This is a preview. Save it when the post is ready to be published.');
				}
				
				$_ctx->comment_preview['name'] = ((isset($_POST['c_name'])) ? $_POST['c_name'] : '');
				$_ctx->comment_preview['mail'] = ((isset($_POST['c_mail'])) ? $_POST['c_mail'] : '');
				$_ctx->comment_preview['site'] = ((isset($_POST['c_site'])) ? $_POST['c_site'] : '');
				
			} elseif (isset($_POST['add'])) {
				$core->auth->checkUser($core->blog->settings->noname_user);
				
				$cur = $core->con->openCursor($core->prefix.'post');
				
				$cur->user_id = $core->auth->userID();
				$cur->cat_id = $post->cat_id;
				$cur->post_dt = date('Y-m-d H:i:00');
				$cur->post_format = 'wiki';
				$cur->post_status = -2;
				$cur->post_title = $post->post_title;
				$cur->post_excerpt = $post->post_excerpt_wiki;
				$cur->post_content = $post->post_content_wiki;
				
				# --BEHAVIOR-- adminBeforePostCreate
				$core->callBehavior('adminBeforePostCreate',$cur);
				
				$return_id = $core->blog->addPost($cur);
				
				# --BEHAVIOR-- adminAfterPostCreate
				$core->callBehavior('adminAfterPostCreate',$cur,$return_id);
				
				if (is_int($return_id))
				{
					$_ctx->contribute->message = __('The post has been saved. It needs to be approved by the administrator to appear on the blog.');
					$_ctx->contribute->preview = false;
					$_ctx->contribute->form = false;
				}
			}
		}
		catch (Exception $e)
		{
			$_ctx->contribute->error = $e->getMessage();
		}

		$core->tpl->setPath($core->tpl->getPath(),
			dirname(__FILE__).'/default-templates/');

		self::serveDocument('contribute.html','text/html',false,false/* // debug mode */);
	}
}

# message
$core->tpl->addBlock('ContributeIfMessage',array('contributeTpl','ifMessage'));
$core->tpl->addValue('ContributeMessage',array('contributeTpl','message'));

# error
$core->tpl->addBlock('ContributeIfError',array('contributeTpl','ifError'));
$core->tpl->addValue('ContributeError',array('contributeTpl','error'));

$core->tpl->addBlock('ContributePreview',array('contributeTpl','preview'));
$core->tpl->addBlock('ContributeForm',array('contributeTpl','form'));
$core->tpl->addBlock('ContributeSelectedCategory',array('contributeTpl','selectedCategory'));

$core->tpl->addValue('ContributeEntryExcerptWiki',array('contributeTpl','entryExcerptWiki'));
$core->tpl->addValue('ContributeEntryContentWiki',array('contributeTpl','entryContentWiki'));

$core->tpl->addValue('CategoryID',array('contributeTpl','categoryID'));

/**
@ingroup Contribute
@brief Template
*/
class contributeTpl
{
	/**
	if there is a message
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ifMessage($attr,$content)
	{
		return
		"<?php if (\$_ctx->contribute->message != '') : ?>"."\n".
		$content.
		"<?php endif; ?>";
	}

	/**
	display a message
	@return	<b>string</b> PHP block
	*/
	public static function message()
	{
		return("<?php if (\$_ctx->contribute->message != '') :"."\n".
		"echo(\$_ctx->contribute->message);".
		"endif; ?>");
	}
	
	/**
	if there is an error
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function ifError($attr,$content)
	{
		return
		"<?php if (\$_ctx->contribute->error != '') : ?>"."\n".
		$content.
		"<?php endif; ?>";
	}

	/**
	display an error
	@param	attr	<b>array</b>	Attribute
	@return	<b>string</b> PHP block
	*/
	public static function error($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		return("<?php if (\$_ctx->contribute->error != '') :"."\n".
		'echo('.sprintf($f,'$_ctx->contribute->error').');'.
		"endif; ?>");
	}
	
	/**
	display preview
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function preview($attr,$content)
	{
		return
		'<?php if ($_ctx->contribute->preview === true) : ?>'."\n".
		$content.
		'<?php endif; ?>';
	}
	
	/**
	display form
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function form($attr,$content)
	{
		return
		'<?php if ($_ctx->contribute->form === true) : ?>'."\n".
		$content.
		'<?php endif; ?>';
	}
	
	/**
	if the category is selected
	@param	attr	<b>array</b>	Attribute
	@param	content	<b>string</b>	Content
	@return	<b>string</b> PHP block
	*/
	public static function selectedCategory($attr,$content)
	{
		return
		'<?php if ($_ctx->categories->cat_id == $_ctx->posts->cat_id) : ?>'."\n".
		$content.
		'<?php endif; ?>';
	}
	
	public static function entryExcerptWiki($attr)
	{		
		return('<?php echo($_ctx->posts->post_excerpt_wiki); ?>');
	}
	
	public static function entryContentWiki($attr)
	{		
		return('<?php echo($_ctx->posts->post_content_wiki); ?>');
	}
	
	public static function categoryID($attr)
	{		
		return('<?php echo($_ctx->categories->cat_id); ?>');
	}
	
}

/**
@ingroup Contribute
@brief Widget
*/
class contributeWidget
{
	/**
	show widget
	@param	w	<b>object</b>	Widget
	@return	<b>string</b> XHTML
	*/
	public static function show(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		# output
		$header = (strlen($w->title) > 0)
			? '<h2><a href="'.$core->blog->url.$core->url->getBase('contribute').
				'">'.html::escapeHTML($w->title).'</a></h2>' : null;

		return '<div class="dlmanager">'.$header.'</div>';
	}
}

?>
