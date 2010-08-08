<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of paypalDonation, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

# Namespace for settings
$core->blog->settings->addNamespace('paypalDonation');

require_once dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicEntryBeforeContent',array('publicPaypalDonation','publicEntryBeforeContent'));
$core->addBehavior('publicEntryAfterContent',array('publicPaypalDonation','publicEntryAfterContent'));
$core->tpl->addValue('PaypalDonationPageTitle',array('tplPaypalDonation','PaypalDonationPageTitle'));
$core->tpl->addValue('PaypalDonationPageContent',array('tplPaypalDonation','PaypalDonationPageContent'));
$core->tpl->addValue('PaypalDonationButton',array('tplPaypalDonation','PaypalDonationButton'));

class publicPaypalDonation
{
	# Before entry content
	public static function publicEntryBeforeContent($core,$_ctx)
	{
		return self::publicEntryContent($core,$_ctx,'before');
	}

	# After entry content
	public static function publicEntryAfterContent($core,$_ctx)
	{
		return self::publicEntryContent($core,$_ctx,'after');
	}
	
	public static function publicEntryContent($core,$_ctx,$place)
	{
		if (!$core->blog->settings->paypalDonation->active 
		 || !$_ctx->exists('posts') || $_ctx->posts->post_type != 'post' 
		 || $core->blog->settings->paypalDonation->button_place != $place) {
			return;
		}
		
		$button_tpl = @unserialize($core->blog->settings->paypalDonation->button_tpl);
		if (!is_array($button_tpl) || !in_array($core->url->type,$button_tpl))
		{
			return;
		}

		
		$ppd = new paypalDonation($core);
		$res = $ppd->getPostInfo($_ctx->posts->post_id);
		
		if (!isset($res['use']) || !$res['use']) {
			return;
		}
		
		$ppd->item_name = $ppd->parsePostInfo($res['item_name'],$_ctx->posts);
		$ppd->item_number = $ppd->parsePostInfo($res['item_number'],$_ctx->posts);
		$ppd->amount = $res['amount'];
		
		echo $ppd->build();
	}
}

class urlPaypalDonation extends dcUrlHandlers
{
	public static function paypalreturn($args)
	{
		global $core;
		
		if (!$core->blog->settings->paypalDonation->active 
		 || !$core->blog->settings->paypalDonation->return_page 
		 || !$core->blog->settings->paypalDonation->page_title 
		 || !$core->blog->settings->paypalDonation->page_content) {
			self::p404();
			exit;
		}
		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('paypaldonation.html');
	}
}

class tplPaypalDonation
{
	public static function PaypalDonationPageTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->paypalDonation->page_title').'; ?>';
	}
	
	public static function PaypalDonationPageContent($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->paypalDonation->page_content').'; ?>';
	}
	
	public static function PaypalDonationButton($attr)
	{
		$res = '';
		if (!empty($attr['purpose'])) {
			$res .= '$ppd->item_name = $ppd->parsePostInfo("'.html::escapeHTML($attr['purpose']).'"); '."\n";
		}
		if (!empty($attr['reference'])) {
			$res .= '$ppd->item_number = $ppd->parsePostInfo("'.html::escapeHTML($attr['reference']).'"); '."\n";
		}
		if (!empty($attr['amount'])) {
			$res .= '$ppd->amount = "'.html::escapeHTML($attr['amount']).'"; '."\n";
		}
		
		return 
		"<?php \n".
		"\$ppd = new paypalDonation(\$core); \n".
		$res.
		"echo \$ppd->build(); \n".
		"unset(\$ppd); ?>\n";
	}
}
?>