<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of WFWComment, a plugin for DotClear2.
#
# Copyright (c) 2006-2009 Pep and contributors.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->url->register('wfwcomment','wfwcomment','^wfwcomment/(.+)$',array('wfwcommentUrl','wfwcomment'));

class wfwcommentUrl extends dcUrlHandlers
{
	public static function wfwcomment($args)
	{
		if (!preg_match('/^[0-9]+$/',$args)) {
			self::p404();
		} else {
			$wfwc = new dcWFWComment($GLOBALS['core']);
			$wfwc->receive($args);
		}
	}
}
?>