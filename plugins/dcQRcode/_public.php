<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Load _wigdets.php
require dirname(__FILE__).'/_widgets.php';

# Public behaviors
$core->addBehavior('publicHeadContent',array('dcQRcodeUrl','publicBeforeDocument'));
$core->addBehavior('publicEntryBeforeContent',array('dcQRcodeUrl','publicEntryBeforeContent'));
$core->addBehavior('publicEntryAfterContent',array('dcQRcodeUrl','publicEntryAfterContent'));

$core->tpl->addValue('QRcode',array('dcQRcodeTpl','QRcode'));

class dcQRcodeUrl extends dcUrlHandlers
{
	public static function image($args)
	{
		global $core;

		if (!$core->blog->settings->qrc_active) {
			self::p404();
			exit;
		}

		if (!preg_match('#^(.*?)\.png$#',$args,$m)) {
			self::p404();
			exit;
		}

		if (empty($m[1])) {
			self::p404();
			exit;
		}

		try
		{
			$qrc = new dcQRcode($core,QRC_CACHE_PATH);
		}
		catch (Exception $e)
		{
			self::p404();
			exit;
		}

		if (null === $qrc->decode($m[1])) {
			self::p404();
			exit;
		}

		$qrc->getImage();
		exit;
	}

	public static function publicBeforeDocument($core)
	{
		global $_ctx;

		$_ctx->qrcode = new dcQRcode($core,QRC_CACHE_PATH);
		$_ctx->qrcode->setSize($core->blog->settings->qrc_img_size);
		$_ctx->qrcode->setParams('use_mebkm',$core->blog->settings->qrc_use_mebkm);
	}

	public static function publicEntryBeforeContent($core,$_ctx)
	{
		if (!$core->blog->settings->qrc_active 
		 || !$core->blog->settings->qrc_bhv_entrybeforecontent 
		 || !$_ctx->exists('posts')) return;

		$url = $core->blog->url.$core->url->getBase('post').'/'.$_ctx->posts->post_url;
		$title = $_ctx->posts->post_title;

		$id = $_ctx->qrcode->encode($url,$title);

		echo 
		'<p class="qrcode"><img alt="QR code" src="'.
		$core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$id.
		'.png" /></p>';
	}

	public static function publicEntryAfterContent($core,$_ctx)
	{
		if (!$core->blog->settings->qrc_active 
		 || !$core->blog->settings->qrc_bhv_entryaftercontent 
		 || !$_ctx->exists('posts')) return;

		$url = $core->blog->url.$core->url->getBase('post').'/'.$_ctx->posts->post_url;
		$title = $_ctx->posts->post_title;

		$id = $_ctx->qrcode->encode($url,$title);

		echo 
		'<p class="qrcode"><img alt="QR code" src="'.
		$core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$id.
		'.png" /></p>';
	}
}

class dcQRcodeTpl
{
	public static function QRcode($attr)
	{
		global $core, $_ctx;

		if (!$core->blog->settings->qrc_active) return;

		$size = isset($attr['size']) ? (integer) $attr['size'] : 128;
		$type = isset($attr['type']) ? html::escapeHTML($attr['type']) : 'URL';

		$res = 
		"<?php \n".
		"\$_ctx->qrcode->setSize(".$size."); ".
		"\$_ctx->qrcode->setType('".$type."'); ".
		"\$id = null; ".
		"?>\n";

		/* Related to context */
		
		# posts
		if ($type == 'posts')
		{
			$mebkm = '';
			if (isset($attr['use_mebkm'])) {
				$mebkm = " \$_ctx->qrcode->setParams('use_mebkm',".((boolean) $attr['use_mebkm'])."); \n";
			}

			$res .= 
			"<?php if (\$_ctx->exists('posts')".
			" && \$_ctx->posts->post_type == 'post'".
			" && \$core->blog->settings->qrc_active) { \n".
			$mebkm.
			" \$title = \$_ctx->posts->post_title; \n".
			" \$url = \$core->blog->url.\$core->url->getBase('post').'/'.\$_ctx->posts->post_url; \n".
			" \$id = \$_ctx->qrcode->encode(\$url,\$title); \n".
			"} ?>\n";
		}

		/* Related to QRtype */

		# URL
		if ($type == 'URL' && isset($attr['url']))
		{
			$mebkm = '';
			if (isset($attr['use_mebkm'])) {
				$mebkm = " \$_ctx->qrcode->setParams('use_mebkm',".((boolean) $attr['use_mebkm'])."); \n";
			}

			$res .= 
			"<?php \n".
			$mebkm.
			" \$title = '".(isset($attr['title']) ? html::escapeHTML($attr['title']) : '')."'; \n".
			" \$url = '".html::escapeHTML($attr['url'])."'; \n".
			" \$id = \$_ctx->qrcode->encode(\$url,\$title); \n".
			"?>\n";
		}

		# MECARD
		if ($type == 'MECARD' && isset($attr['name']))
		{
			$res .= 
			"<?php \n".
			" \$name = '".html::escapeHTML($attr['name'])."'; \n".
			" \$address = '".(isset($attr['address']) ? html::escapeHTML($attr['address']) : '')."'; \n".
			" \$phone = '".(isset($attr['phone']) ? html::escapeHTML($attr['phone']) : '')."'; \n".
			" \$email = '".(isset($attr['email']) ? html::escapeHTML($attr['email']) : '')."'; \n".
			" \$id = \$_ctx->qrcode->encode(\$name,\$address,\$phone,\$email); \n".
			"?>\n";
		}

		# GEO
		if ($type == 'GEO' && isset($attr['latitude']) && isset($attr['longitude']))
		{
			$res .= 
			"<?php \n".
			" \$latitude = '".html::escapeHTML($attr['latitude'])."'; \n".
			" \$longitude = '".html::escapeHTML($attr['longitude'])."'; \n".
			" \$altitude = '".(isset($attr['altitude']) ? html::escapeHTML($attr['altitude']) : '0')."'; \n".
			" \$id = \$_ctx->qrcode->encode(\$latitude,\$longitude,\$altitude); \n".
			"?>\n";
		}

		# MARKET
		if ($type == 'MARKET' && isset($attr['cat']) && isset($attr['search']))
		{
			$res .= 
			"<?php \n".
			" \$cat = '".html::escapeHTML($attr['cat'])."'; \n".
			" \$search = '".html::escapeHTML($attr['search'])."'; \n".
			" \$id = \$_ctx->qrcode->encode(\$cat,\$search); \n".
			"?>\n";
		}

		# ICAL
		if ($type == 'ICAL' && isset($attr['summary']) && isset($attr['start-date']) && isset($attr['end-date']))
		{
			$res .= 
			"<?php \n".
			" \$summary = '".html::escapeHTML($attr['summary'])."'; \n".
			" \$start_date = '".html::escapeHTML($attr['start-date'])."'; \n".
			" \$end_date = '".html::escapeHTML($attr['end-date'])."'; \n".
			" \$id = \$_ctx->qrcode->encode(\$summary,\$start_date,\$end_date); \n".
			"?>\n";
		}


		# --BEHAVIOR-- QRcodeTemplate
		$res .= $core->callBehavior('QRcodeTemplate',$attr);


		$res .=
		"<?php if (\$id) { \n".
		"echo '".
		"<p class=\"qrcode\">".
		"<img alt=\"QR code\" src=\"'.".
		"\$core->blog->url.\$core->url->getBase('dcQRcodeImage').'/'.\$id.".
		"'.png\" />".
		"</p>'; \n".
		// regain standard settings
		"\$_ctx->qrcode->setSize(\$core->blog->settings->qrc_img_size); \n".
		"\$_ctx->qrcode->setParams('use_mebkm',\$core->blog->settings->qrc_use_mebkm); \n".
		"} ?> \n";

		return $res;
	}
}

class dcQRcodePublicWidget
{
	public static function posts($w)
	{
		global $core, $_ctx;

		# plugin active
		if (!$core->blog->settings->qrc_active) return;
		# Post page only
		if ('post.html' != $_ctx->current_tpl || !$_ctx->exists('posts')) return;
		# qrc class
		$qrc = new dcQRcode($core,QRC_CACHE_PATH);
		$qrc->setSize($w->size);
		$qrc->setParams('use_mebkm',$w->mebkm);

		$url = $core->blog->url.$core->url->getBase('post').'/'.$_ctx->posts->post_url;
		$title = $_ctx->posts->post_title;
		$id = $qrc->encode($url,$title);

		# Display
		$res =
		'<div class="qrc_posts">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<p class="qrcode">'.
		'<img alt="QR code" src="'.
			$core->blog->url.$core->url->getBase('dcQRcodeImage').'/'.$id.
		'.png" />'.
		'</p></div>';

		return $res;
	}
}
?>