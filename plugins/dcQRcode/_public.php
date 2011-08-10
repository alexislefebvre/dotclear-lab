<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

dcQRcode::defineCachePath($core);

# Load _wigdets.php
require dirname(__FILE__).'/_widgets.php';

# Public behaviors
$core->addBehavior('publicBeforeDocument',array('dcQRcodeUrl','publicBeforeDocument'));
$core->addBehavior('publicHeadContent',array('dcQRcodeUrl','publicHeadContent'));
$core->addBehavior('publicEntryBeforeContent',array('dcQRcodeUrl','publicEntryBeforeContent'));
$core->addBehavior('publicEntryAfterContent',array('dcQRcodeUrl','publicEntryAfterContent'));

$core->tpl->addValue('QRcode',array('dcQRcodeTpl','QRcode'));

class dcQRcodeUrl extends dcUrlHandlers
{
	# Public URL to serve QR code previously encode
	public static function image($args)
	{
		global $core;
		
		if (!$core->blog->settings->dcQRcode->qrc_active) {
			self::p404();
			return;
		}

		try {
			$qrc = new dcQRcode($core,QRC_CACHE_PATH);
		}
		catch (Exception $e) {
			self::p404();
			return;
		}
		$prefix = $qrc->getParam('prefix');
		
		if (!preg_match('#^'.preg_quote($prefix).'(.*?)\.png$#',$args,$m)) {
			self::p404();
			return;
		}
		
		if (empty($m[1])) {
			self::p404();
			return;
		}
		
		$id = urldecode($m[1]);
		if (null === $qrc->decodeData($id)) {
			self::p404();
			return;
		}
		
		$qrc->getImage();
		exit;
	}
	
	# Put QRcode class to context
	public static function publicBeforeDocument($core)
	{
		global $_ctx;
		
		$_ctx->qrcode = new dcQRcode($core,QRC_CACHE_PATH);
	}
	
	# Custom css
	public static function publicHeadContent($core)
	{
		$css = $core->blog->settings->dcQRcode->qrc_custom_css;
		if ($css) {
			echo 
			"\n<!-- CSS for dcQrCode --> \n".
			"<style type=\"text/css\"> \n".
			html::escapeHTML($css)."\n".
			"</style>\n";
		}
	}
	
	# Place QRcode before entry content
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		self::publicEntryBehaviorContent($core,$_ctx,'before');
	}
	
	# Place QR code after entry content
	public static function publicEntryAfterContent($core,$_ctx)
	{
		self::publicEntryBehaviorContent($core,$_ctx,'after');
	}
	
	# Generic place
	public static function publicEntryBehaviorContent($core,$_ctx,$place)
	{
		if (!$core->blog->settings->dcQRcode->qrc_active 
		|| $core->blog->settings->dcQRcode->qrc_bhv_entryplace != $place 
		|| !$_ctx->exists('posts') 
		|| !in_array($_ctx->current_tpl,array('home.html','post.html','category.html','tag.html','archive_month.html')) 
		|| !$core->blog->settings->dcQRcode->qrc_bhv_entrytplhome && $_ctx->current_tpl == 'home.html' 
		|| !$core->blog->settings->dcQRcode->qrc_bhv_entrytplpost && $_ctx->current_tpl == 'post.html' 
		|| !$core->blog->settings->dcQRcode->qrc_bhv_entrytplcategory && $_ctx->current_tpl == 'category.html' 
		|| !$core->blog->settings->dcQRcode->qrc_bhv_entrytpltag && $_ctx->current_tpl == 'tag.html' 
		|| !$core->blog->settings->dcQRcode->qrc_bhv_entrytplarchive && $_ctx->current_tpl == 'archive_month.html' 
		) return;
		
		$url = $_ctx->posts->getURL();
		$title = $core->blog->name.' - '.$_ctx->posts->post_title;
		
		$_ctx->qrcode->setType('URL');
		$id = $_ctx->qrcode->encodeData($url,$title);
		
		echo 
		'<div class="qrcode">'.
		'<img alt="QR code" src="'.$_ctx->qrcode->getURL($id).'" />'.
		'</div>';
	}
}

class dcQRcodeTpl
{
	# Custom template tag to generate QR code
	public static function QRcode($attr)
	{
		global $core, $_ctx;
		
		if (!$core->blog->settings->dcQRcode->qrc_active) return;
		
		$size = isset($attr['size']) ? (integer) $attr['size'] : 128;
		$type = isset($attr['type']) ? html::escapeHTML($attr['type']) : 'URL';
		
		$res = 
		"<?php \n".
		"\$_ctx->qrcode->setSize(".$size."); ".
		"\$_ctx->qrcode->setType('".$type."'); ".
		"\$id = null; ".
		"?>\n";
		
		# posts
		if ($type == 'posts')
		{
			$res .= 
			"<?php if (\$_ctx->exists('posts')".
			" && \$_ctx->posts->post_type == 'post'".
			" && \$core->blog->settings->dcQRcode->qrc_active) { \n".
			"\$_ctx->qrcode->setType('URL'); ".
			"\$title = \$core->blog->name.' - '.\$_ctx->posts->post_title; \n".
			"\$url = \$_ctx->posts->getURL(); \n".
			"\$id = \$_ctx->qrcode->encodeData(\$url,\$title); \n".
			"} ?>\n";
		}
		# categories
		elseif ($type == 'categories')
		{
			$res .= 
			"<?php if (\$_ctx->exists('categories')".
			" && \$core->blog->settings->dcQRcode->qrc_active) { \n".
			"\$_ctx->qrcode->setType('URL'); ".
			"\$title = \$core->blog->name.' - '.\$_ctx->categories->cat_title; \n".
			"\$url = \$core->blog->url.\$core->url->getBase('category').'/'.\$_ctx->categories->cat_url; \n".
			"\$id = \$_ctx->qrcode->encodeData(\$url,\$title); \n".
			"} ?>\n";
		}
		# tags
		elseif ($type == 'tags')
		{
			$res .= 
			"<?php if (\$_ctx->exists('meta')".
			" && \$core->blog->settings->dcQRcode->qrc_active) { \n".
			"\$_ctx->qrcode->setType('URL'); ".
			"\$title = \$core->blog->name.' - '.\$_ctx->meta->meta_id; \n".
			"\$url = \$core->blog->url.\$core->url->getBase('tag').'/'.\$_ctx->meta->meta_id; \n".
			"\$id = \$_ctx->qrcode->encodeData(\$url,\$title); \n".
			"} ?>\n";
		}
		else {
			$res .= $_ctx->qrcode->getTemplate($attr);
		}
		
		return 
		"<?php if (\$id) { \n".
		"echo '".
		"<p class=\"qrcode\">".
		"<img alt=\"QR code\" src=\"'.\$_ctx->qrcode->getURL(\$id).'\" />".
		"</p>'; \n".
		"unset(\$id); } ?> \n";
	}
}

class dcQRcodePublicWidget
{
	public static $tpls = array(
		'posts' => array(
			'post.html' => 'post',
			'page.html' => 'pages',
			'archive_month.html' => 'post'
		),
		'categories' => array(
			'post.html' => 'categories',
			'category.html' => 'categories'),
		'tags' => array(
			'tag.html' => 'tag'
		)
	);
	
	public static function posts($w)
	{
		global $core, $_ctx;
		
		# plugin active
		if (!$core->blog->settings->dcQRcode->qrc_active) return;
		
		# qrc class
		$qrc = new dcQRcode($core,QRC_CACHE_PATH);
		$qrc->setSize($w->size);
		$qrc->setType('URL');
		
		$url = $core->blog->url;
		$title = $core->blog->name.' - ';
		$id = null;
		
		# posts
		if ($w->context == 'posts')
		{
			if (empty(self::$tpls['posts'][$_ctx->current_tpl]) 
			 || !$_ctx->exists('posts')) return;

			$url = $_ctx->posts->getURL();
			$title .= $_ctx->posts->post_title;
			$id = $qrc->encodeData($url,$title);
		}
		# categories
		if ($w->context == 'categories')
		{
			if (empty(self::$tpls['categories'][$_ctx->current_tpl]) 
			 || !$_ctx->exists('categories')) return;

			$url .= 
				$core->url->getBase(self::$tpls['categories'][$_ctx->current_tpl]).
				'/'.$_ctx->categories->cat_url;
			$title .= $_ctx->categories->cat_title;
			$id = $qrc->encodeData($url,$title);
		}
		# tags
		if ($w->context == 'tags')
		{
			if (empty(self::$tpls['tags'][$_ctx->current_tpl]) 
			 || !$_ctx->exists('meta')) return;

			$url .= 
				$core->url->getBase(self::$tpls['tags'][$_ctx->current_tpl]).
				'/'.$_ctx->meta->meta_id;
			$title .= $_ctx->meta->meta_id;
			$id = $qrc->encodeData($url,$title);
		}
		
		if (!$id) return;
		
		# Display
		$res =
		'<div class="qrcode-widget">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<img alt="QR code" src="'.$qrc->getURL($id).'" />'.
		'</div>';
		
		return $res;
	}
}
?>