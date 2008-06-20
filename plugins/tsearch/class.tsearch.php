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