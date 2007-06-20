<?php
class dcTsearch
{
	public static function corePostSearch(&$core,$p)
	{
		$words =& $p[0];
		$sql =& $p[1];
		$params =& $p[2];
		
		# We need to format query string
		$search = implode(' ',$words);
		$search = preg_replace('/[&|!\\\]/',' ',$search);
		$search = preg_split('/\s+/',trim($search));
		foreach ($search as &$q) {
			$q = $core->con->escape($q);
		}
		
		if (empty($search)) {
			$words = null;
			return;
		}
		
		$search = implode(' & ',$search);
		
		# That's tricky, one day we'll have a query builder ;)
		list($select,$from) = explode('FROM',$sql,2);
		list($from,$where) = explode('WHERE',$from,2);
		
		$from .= " INNER JOIN find_post('".$search."')  F USING(post_id) ";
		
		$sql = $select.' FROM '.$from.' WHERE '.$where;
		$params['order'] = 'rank_cd DESC';
		
		# We must set $words to null to avoid regular search to continue
		$words = null;
	}
}
?>