<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicFooterContent',array('piwikPublic','publicFooterContent'));

class piwikPublic
{
	public static function publicFooterContent(&$core,&$_ctx)
	{
		$piwik_service_uri = $core->blog->settings->piwik_service_uri;
		$piwik_site = $core->blog->settings->piwik_site;
		$piwik_ips = $core->blog->settings->piwik_ips;
		
		if (!$piwik_service_uri || !$piwik_site) {
			return;
		}
		
		$action = $_SERVER['URL_REQUEST_PART'];
		if ($core->blog->settings->piwik_fancy) {
			$action = str_replace('/',' : ',$action);
		}
		echo $action;
		echo dcPiwik::getScriptCode($piwik_service_uri,$piwik_site,$action);
	}
}
?>