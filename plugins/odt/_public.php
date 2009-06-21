<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of threadedComments, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('ODT',array('tplOdt','odtLink'));

class urlOdt extends dcUrlHandlers
{
	public static function odt($args)
	{
		global $core;
		if (!$args) {
			$tpl_name = "home";
		} else {
			$tpl_name = self::loadPost($args);
		}
		# The entry
		$core->tpl->setPath($core->tpl->getPath(),
		                    dirname(__FILE__).'/default-templates');
		self::serveDocument($tpl_name.'.odt',
		                    'application/vnd.oasis.opendocument.text');
		exit;
	}

	protected static function loadPost($args)
	{
		global $core, $_ctx;
		
		$core->blog->withoutPassword(false);
		
		$args_array = explode("/",$args);
		$params = new ArrayObject();
		$params['post_type'] = $args_array[0];
		$params['post_url'] = implode("/",array_slice($args_array,1));
		if ($params['post_type'] == "pages") $params['post_type'] = "page";

		$_ctx->posts = $core->blog->getPosts($params);
		
		/*
		$_ctx->comment_preview = new ArrayObject();
		$_ctx->comment_preview['content'] = '';
		$_ctx->comment_preview['rawcontent'] = '';
		$_ctx->comment_preview['name'] = '';
		$_ctx->comment_preview['mail'] = '';
		$_ctx->comment_preview['site'] = '';
		$_ctx->comment_preview['preview'] = false;
		$_ctx->comment_preview['remember'] = false;
		*/
		
		$core->blog->withoutPassword(true);
		
		if ($_ctx->posts->isEmpty())
		{
			# No entry
			self::p404();
		}
		
		$post_id = $_ctx->posts->post_id;
		$post_password = $_ctx->posts->post_password;
		
		# Password protected entry
		if ($post_password != '')
		{
			# Get passwords cookie
			if (isset($_COOKIE['dc_passwd'])) {
				$pwd_cookie = unserialize($_COOKIE['dc_passwd']);
			} else {
				$pwd_cookie = array();
			}
			
			# Check for match
			if ((!empty($_POST['password']) && $_POST['password'] == $post_password)
			|| (isset($pwd_cookie[$post_id]) && $pwd_cookie[$post_id] == $post_password))
			{
				$pwd_cookie[$post_id] = $post_password;
				setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
			}
			else
			{
				self::serveDocument('password-form.html','text/html',false);
				exit;
			}
		}
		
		$post_comment =
			isset($_POST['c_name']) && isset($_POST['c_mail']) &&
			isset($_POST['c_site']) && isset($_POST['c_content']) &&
			$_ctx->posts->commentsActive();
		return $_ctx->posts->post_type;	
	}

	protected static function serveDocument($tpl,$content_type='text/html',$http_cache=true,$http_etag=true)
	{
		global $odf;
		if ($content_type != 'application/vnd.oasis.opendocument.text') {
			return parent::serveDocument($tpl,$content_type,$http_cache,$http_etag);
		}

		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
		
		if ($_ctx->nb_entry_per_page === null) {
			$_ctx->nb_entry_per_page = $core->blog->settings->nb_post_per_page;
		}
		
		$tpl_file = $core->tpl->getFilePath($tpl);
		
		if (!$tpl_file) { // fallback to post.odt
			$tpl_file = $core->tpl->getFilePath("post.odt");
		}
		if (!$tpl_file) {
			throw new Exception('Unable to find template');
		}
		
		/*
		if ($http_cache) {
			$GLOBALS['mod_files'][] = $tpl_file;
			http::cache($GLOBALS['mod_files'],$GLOBALS['mod_ts']);
		}
		*/

		require_once("inc/class.odt.dcodf.php");
		$odf = new dcOdf($tpl_file, $_ctx);

		$odf->compile();

		// On exporte le fichier
		$title = $_ctx->posts->post_title;
		if (!$title) { $title = $core->blog->name; }
		$odf->exportAsAttachedFile(str_replace('"','',$title).".odt");
		return;
		
		$result = new ArrayObject;
		
		header('Content-Type: '.$content_type.'; charset=UTF-8');
		$_ctx->current_tpl = $tpl;
		$result['content'] = $core->tpl->getData($tpl);
		$result['content_type'] = $content_type;
		$result['tpl'] = $tpl;
		$result['blogupddt'] = $core->blog->upddt;
		
		# --BEHAVIOR-- urlHandlerServeDocument
		$core->callBehavior('urlHandlerServeDocument',$result);
		
		if ($http_cache && $http_etag) {
			http::etag($result['content'],http::getSelfURI());
		}
		echo $result['content'];
	}
	
}

class tplOdt
{
	protected static function getLink()
	{
		global $core;
		$url = '$core->blog->url.$core->url->getBase("odt")';
		if ($core->url->type != 'default') {
			$url .= '."/".$core->url->type."/".$_ctx->posts->post_url';
		}
		return $url;
	}

	public static function odtLink($attr)
	{
		global $core, $_ctx;
		$f = $core->tpl->getFilters($attr);
		$url = sprintf($f,self::getLink());
		$image_url = $core->blog->getQmarkURL().'pf=odt/img/odt.png';
		$widget = '<p class="odt"><a href="<?php echo '.$url.'; ?'.'>" title="'.
		__("Export to ODT").'"><img alt="ODT" class="odt" src="'.$image_url.
		'" /></a></p>';
		//$widget.= "<pre>".$core->url->type."</pre>";
		//$widget.= "<pre>".$_ctx->posts->getURL()."</pre>";
		return $widget;
	}

	# Widget function
	public static function odtWidget(&$w)
	{
		global $core, $_ctx;
		
		if (!$w->onhome && $core->url->type == 'default') {
			return;
		}
		
		$url = eval('return '.self::getLink().";");
		$image_url = $core->blog->getQmarkURL().'pf=odt/img/odt.png';
		$res =
		'<div class="odt">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<p><a href="'.$url.'" title="'.__("Export to ODT").
		'"><img alt="ODT" class="odt" src="'.$image_url.
		'" style="vertical-align:middle" /></a> <a href="'.$url.'">'.
		($w->link_title ? html::escapeHTML($w->link_title) : __('Export to ODT')).
		'</a></p>'.
		'</div>';
		
		return $res;
	}
}

?>
