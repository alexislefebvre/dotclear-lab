<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Offline mode, a plugin for Dotclear 2.
# 
# Copyright (c) 2008-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->tpl->addValue('OfflinePageTitle',array('tplOffline','OfflinePageTitle'));
$core->tpl->addValue('OfflineMsg',array('tplOffline','OfflineMsg'));

$core->addBehavior('publicBeforeDocument',array('urlOffline','offline'));

class urlOffline extends dcUrlHandlers
{
	public static function offline($args)
	{
		global $core;

		if ($core->blog->settings->blog_off_flag){
			if ($core->blog->settings->blog_off_ip_ok != ""){
				if ($_SERVER['REMOTE_ADDR'] == $core->blog->settings->blog_off_ip_ok){
					return;
				}
			}
			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			self::serveDocument('offline.html');
			exit;
			}
		return;
	}
}

class tplOffline
{
	public static function OfflinePageTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->blog_off_page_title').'; ?>';
	}

	public static function OfflineMsg($attr)
	{
		return '<?php echo $core->blog->settings->blog_off_msg; ?>';
	}
}
?>