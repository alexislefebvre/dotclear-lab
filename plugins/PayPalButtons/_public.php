<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of PayPalButtons, a plugin for Dotclear 2.
#
# Copyright (c) 2011 Philippe aka amalgame
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

/* Settings
-------------------------------------------------------- */

$s =& $core->blog->settings->PayPalButtons;

if (!$s->PayPalButtons_enabled) {
	return;
}

$core->tpl->addValue('PayPalResponseValue',array('PayPalButtonsTpl','PayPalResponseValue'));
$core->tpl->addValue('PayPalCancelContent',array('PayPalButtonsTpl','PayPalCancelContent'));
$core->tpl->addValue('PayPalValidateContent',array('PayPalButtonsTpl','PayPalValidateContent'));
$core->tpl->addValue('PayPalNotifyContent',array('PayPalButtonsTpl','PayPalNotifyContent'));

class PayPalButtonsTpl
{
	public static function PayPalResponseValue()
	{
		return('<?php echo($_ctx->PayPalButtons); ?>');
	}
	public static function PayPalCancelContent()
	{
		
	}
	public static function PayPalValidateContent()
	{
		
	}
	public static function PayPalNotifyContent()
	{
		
	}
}

class PayPalButtonsPublic
{
	
}

/*  Public pages Urls
-------------------------------------------------------- */

class PublicPaypalButtonsDocument extends dcUrlHandlers
{
	public static function page($args)
	{
		global $core;
		$_ctx =& $GLOBALS['_ctx'];
		if ($args == 'validate') {
			$_ctx->PayPalButtons = __('Validation');
		} elseif ($args == 'cancel') {
			$_ctx->PayPalButtons = __('Cancelation');
		} elseif (($args == 'notify')) {
			$_ctx->PayPalButtons = __('Notification');
		}
 
		$core->tpl->setPath($core->tpl->getPath(),
			dirname(__FILE__).'/default-templates/');
 
		self::serveDocument('paypalbuttons.html','text/html');
	}
}
?>