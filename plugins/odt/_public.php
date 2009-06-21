<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of odt, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('ODT',array('tplOdt','odtLink'));

$core->addBehavior('publicHeadContent',
	array('odtBehaviors','publicHeadContent'));
$core->addBehavior('publicEntryBeforeContent',
	array('odtBehaviors','publicEntryBeforeContent'));
$core->addBehavior('publicEntryAfterContent',
	array('odtBehaviors','publicEntryAfterContent'));

require_once(dirname(__FILE__)."/inc/lib.odt.utils.php");
require_once(dirname(__FILE__)."/inc/class.odt.dcodf.php");

class urlOdt extends dcUrlHandlers
{
	public static function odt($args)
	{
		global $core;
		if (!odtUtils::checkConfig()) {
			throw new Exception('Configuration problem, see admin page for details');
		}
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
		
		$core->blog->withoutPassword(true);
		
		if ($_ctx->posts->isEmpty()) {
			# No entry
			self::p404();
		}
		
		$post_id = $_ctx->posts->post_id;
		$post_password = $_ctx->posts->post_password;
		
		# Password protected entry
		if ($post_password != '') {
			# Get passwords cookie
			if (isset($_COOKIE['dc_passwd'])) {
				$pwd_cookie = unserialize($_COOKIE['dc_passwd']);
			} else {
				$pwd_cookie = array();
			}
			
			# Check for match
			if ((!empty($_POST['password']) && $_POST['password'] == $post_password)
			|| (isset($pwd_cookie[$post_id]) && $pwd_cookie[$post_id] == $post_password)) {
				$pwd_cookie[$post_id] = $post_password;
				setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
			} else {
				self::serveDocument('password-form.html','text/html',false);
				exit;
			}
		}
		
		return $_ctx->posts->post_type;	
	}

	protected static function serveDocument($tpl,$content_type='text/html',$http_cache=true,$http_etag=true)
	{
		global $core, $_ctx, $odf;

		if ($content_type != 'application/vnd.oasis.opendocument.text') {
			return parent::serveDocument($tpl,$content_type,$http_cache,$http_etag);
		}

		if ($_ctx->nb_entry_per_page === null) {
			$_ctx->nb_entry_per_page = $core->blog->settings->nb_post_per_page;
		}
		
		$tpl_file = $core->tpl->getFilePath($tpl);
		
		if (!$tpl_file) {
			throw new Exception('Unable to find template');
		}
		
		$odf = new dcOdf($tpl_file);
		$odf->get_remote_images = $core->blog->settings->odt_import_images;
		$odf->params["domain"] = $_SERVER["SERVER_NAME"];
		if ($tpl == 'home.odt') {
			$odf->params["heading.minus.level"] = 1;
		} else {
			$odf->params["heading.minus.level"] = 2;
		}

		$odf->compile();

		// Export the file to download
		if ($_ctx->posts) {
			$title = $_ctx->posts->post_title;
		} else {
			$title = $core->blog->name;
		}
		$odf->exportAsAttachedFile(str_replace('"','',$title).".odt");
	}
	
}

class tplOdt
{

	public static function odtLink($attr)
	{
		if (!odtUtils::checkConfig()) { return; } # config problem
		return odtUtils::getButton($attr);
	}

	# Widget function
	public static function odtWidget(&$w)
	{
		global $core, $_ctx;

		if (!odtUtils::checkConfig()) { return; } # config problem
		
		if ($w->where == "allbuthome" && $core->url->type == 'default') {
			return;
		} elseif ($w->where == "home" && $core->url->type != 'default') {
			return;
		}
		
		$url = eval('return '.odtUtils::getLink().";");
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

class odtBehaviors
{

	public static function publicHeadContent(&$core,$_ctx)
	{
		if (!odtUtils::checkConfig()) { return; } # config problem
		echo "\n<!-- ODT export -->\n";
		echo (
			'<style type="text/css" media="screen">'.
			'@import url('.$core->blog->getQmarkURL().
			'pf=odt/style.css);</style>'."\n"
			);
	}

	public static function publicEntryBeforeContent(&$core,$_ctx)
	{
		if (!odtUtils::checkConfig()) { return; } # config problem
		# Not on the home page
		if ($core->url->type == "default" 
				or $core->url->type == "default-page") { 
			return;
		}
		if ($core->blog->settings->odt_behavior != "top") { return; }
		echo (odtUtils::getButton(false,"top"));
	}

	public static function publicEntryAfterContent(&$core,$_ctx)
	{
		if (!odtUtils::checkConfig()) { return; } # config problem
		# Not on the home page
		if ($core->url->type == "default" 
				or $core->url->type == "default-page") { 
			return;
		}
		if ($core->blog->settings->odt_behavior != "bottom") { return; }
		echo (odtUtils::getButton(false,"bottom"));
	}
}
?>
