<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Empreinte, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class rsExtCommentEmpreinte
{
	public static function getAuthorLink(&$rs)
	{
		global $core;
		
		$res = rsExtComment::getAuthorLink($rs);
		
		if (!( $mask = $core->blog->settings->empreinte->authorlink_mask
		and isset(publicEmpreinte::$c_info[$rs->comment_id]['browser'])
		and isset(publicEmpreinte::$c_info[$rs->comment_id]['system']) ))
		{
			return $res;
		}
		
		return sprintf($mask,$res,tplEmpreinte::PluginFileURL(),
			$rs->getBrowser(),$rs->getBrowser(1),
			$rs->getSystem(),$rs->getSystem(1));
	}
	
	public static function getBrowser(&$rs,$lcase=true)
	{
		if ($res = @publicEmpreinte::$c_info[$rs->comment_id]['browser'])
		{
			if ($lcase) {
				return strtolower($res);
			}
			return $res;
		}
		if ($lcase) {
			return 'unknown';
		}
		return __('Unknown');
	}
	
	public static function getSystem(&$rs,$lcase=false)
	{
		if ($res = @publicEmpreinte::$c_info[$rs->comment_id]['system'])
		{
			if ($lcase) {
				return strtolower($res);
			}
			return $res;
		}
		if ($lcase) {
			return 'unknown';
		}
		return __('Unknown');
	}
}
?>