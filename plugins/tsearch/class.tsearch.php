<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
class dcTsearch
{
	public static function corePostSearch(&$core,$p)
	{
		$words =& $p[0];
		$sql =& $p[1];
		$params =& $p[2];
		
		# We need to format query string
		$search = self::formatQuery($words,$core);
		if (empty($search)) {
			return;
		}
		
		# That's tricky, one day we'll have a query builder ;)
		list($select,$from) = explode('FROM',$sql,2);
		list($from,$where) = explode('WHERE',$from,2);
		
		$from .= " INNER JOIN find_post('".$search."')  F USING(post_id) ";
		
		$sql = $select.' FROM '.$from.' WHERE '.$where;
		
		# We must set $words to null to avoid regular search to continue
		$words = null;
	}
	
	public static function coreCommentSearch(&$core,$p)
	{
		$words =& $p[0];
		$sql =& $p[1];
		$params =& $p[2];
		
		# We need to format query string
		$search = self::formatQuery($words,$core);
		if (empty($search)) {
			return;
		}
		
		# That's tricky, one day we'll have a query builder ;)
		list($select,$from) = explode('FROM',$sql,2);
		list($from,$where) = explode('WHERE',$from,2);
		
		$from .= " INNER JOIN find_comment('".$search."')  F USING(comment_id) ";
		
		$sql = $select.' FROM '.$from.' WHERE '.$where;
		
		# We must set $words to null to avoid regular search to continue
		$words = null;
	}
	
	private static function formatQuery($s,&$core)
	{
		$s = implode(' ',$s);
		$s = preg_replace('/[&|!\\\]/',' ',$s);
		$s = preg_split('/\s+/',trim($s));
		foreach ($s as &$q) {
			$q = $core->con->escape($q);
		}
		
		if (empty($s)) {
			return null;
		}
		
		return implode(' & ',$s);
	}
}
?>