<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku ,Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
class agoraTools
{	# Static methods for Messages pagination 
	public static function PaginationNbPages()
	{
		global $_ctx;
		
		if ($_ctx->pagination === null) {
			return false;
		}
		
		$nb_messages = $_ctx->pagination->f(0);
		$nb_per_page = $_ctx->message_params['limit'][1];
		
		$nb_pages = ceil($nb_messages/$nb_per_page);
		
		return $nb_pages;
	}
	
	public static function PaginationPosition($offset=0)
	{
		if (isset($GLOBALS['_page_number'])) {
			$p = $GLOBALS['_page_number'];
		} else {
			$p = 1;
		}
		
		$p = $p+$offset;
		
		$n = self::PaginationNbPages();
		if (!$n) {
			return $p;
		}
		
		if ($p > $n || $p <= 0) {
			return 1;
		} else {
			return $p;
		}
	}
	
	public static function PaginationStart()
	{
		if (isset($GLOBALS['_page_number'])) {
			return self::PaginationPosition() == 1;
		}
		
		return true;
	}
	
	public static function PaginationEnd()
	{
		if (isset($GLOBALS['_page_number'])) {
			return self::PaginationPosition() == self::PaginationNbPages();
		}
		
		return false;
	}
	
	public static function PaginationURL($offset=0)
	{
		$args = $_SERVER['URL_REQUEST_PART'];
		
		$n = self::PaginationPosition($offset);
		
		$args = preg_replace('#(^|/)page/([0-9]+)$#','',$args);
		
		$url = $GLOBALS['core']->blog->url.$args;
		
		if ($n > 1) {
			$url = preg_replace('#/$#','',$url);
			$url .= '/page/'.$n;
		}
		
		# If search param
		if (!empty($_GET['q'])) {
			$s = strpos($url,'?') !== false ? '&amp;' : '?';
			$url .= $s.'q='.rawurlencode($_GET['q']);
		}
		return $url;
	}
}
?>
