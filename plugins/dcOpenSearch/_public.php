<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# Block functions
$core->tpl->addBlock('SearchLoop',array('dcOpenSearchTpl','SearchLoop'));
$core->tpl->addBlock('SearchIf',array('dcOpenSearchTpl','SearchIf'));
$core->tpl->addBlock('SearchFooter',array('dcOpenSearchTpl','SearchFooter'));
$core->tpl->addBlock('SearchHeader',array('dcOpenSearchTpl','SearchHeader'));
$core->tpl->addBlock('SearchPagination',array('dcOpenSearchTpl','SearchPagination'));
# Value functions
$core->tpl->addValue('SearchID',array('dcOpenSearchTpl','SearchID'));
$core->tpl->addValue('SearchIfFirst',array('dcOpenSearchTpl','SearchIfFirst'));
$core->tpl->addValue('SearchIfOdd',array('dcOpenSearchTpl','SearchIfOdd'));
$core->tpl->addValue('SearchLang',array('dcOpenSearchTpl','SearchLang'));
$core->tpl->addValue('SearchType',array('dcOpenSearchTpl','SearchType'));
$core->tpl->addValue('SearchURL',array('dcOpenSearchTpl','SearchURL'));
$core->tpl->addValue('SearchTitle',array('dcOpenSearchTpl','SearchTitle'));
$core->tpl->addValue('SearchAuthorLink',array('dcOpenSearchTpl','SearchAuthorLink'));
$core->tpl->addValue('SearchDate',array('dcOpenSearchTpl','SearchDate'));
$core->tpl->addValue('SearchTime',array('dcOpenSearchTpl','SearchTime'));
$core->tpl->addValue('SearchCategoryURL',array('dcOpenSearchTpl','SearchCategoryURL'));
$core->tpl->addValue('SearchCategory',array('dcOpenSearchTpl','SearchCategory'));
$core->tpl->addValue('SearchContent',array('dcOpenSearchTpl','SearchContent'));
$core->tpl->addValue('SearchCommentCount',array('dcOpenSearchTpl','SearchCommentCount'));
$core->tpl->addValue('SearchPingCount',array('dcOpenSearchTpl','SearchPingCount'));
$core->tpl->addValue('SearchCountByType',array('dcOpenSearchTpl','SearchCountByType'));

$core->tpl->addValue('PaginationURL',array('dcOpenSearchTpl','PaginationURL'));

class dcOpenSearchTpl
{
	public static function SearchLoop($attr,$content)
	{
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = "<?php\n";
		
		$p .= 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "\$params['limit'] = \$_ctx->nb_entry_per_page;\n";
		}
		
		if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
			$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		} else {
			$p .= "\$params['limit'] = array(0, \$params['limit']);\n";
		}
		
		$p .= "\$_ctx->post_params = \$params;\n";
		$p .= "\$_ctx->_search = dcOpenSearch::search(\$GLOBALS['_search'],\$GLOBALS['_filter'],false,\$_ctx->post_params['limit']);\n";
		$p .= "?>\n";
		
		return $p.'<?php while ($_ctx->_search->fetch()) : ?>'.$content.'<?php endwhile; unset($_ctx->_search); unset($_ctx->post_params); ?>';
	}
	
	public static function SearchIf($attr,$content)
	{
		$operator = isset($attr['operator']) ? dcOpenSearchTpl::getOperator($attr['operator']) : '&&';
		
		if (isset($attr['type_change'])) {
			$if[] = '$_ctx->_search->ifTypeChange()';
		}
		
		if (isset($attr['has_category'])) {
			$if[] = '$_ctx->_search->search_cat_id !== null';
		}
		
		if (isset($attr['has_author'])) {
			$if[] = '$_ctx->_search->search_author_id !== null';
		}
		
		if (isset($attr['show_comments'])) {
			$if[] = '$_ctx->_search->search_comment_nb !== null';
		}
		
		if (isset($attr['show_pings'])) {
			$if[] = '$_ctx->_search->search_trackback_nb !== null';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	public static function SearchHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->_search->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	public static function SearchFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->_search->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	public static function SearchPagination($attr,$content)
	{
		$p = "<?php\n";
		$p .= '$_ctx->pagination = dcOpenSearch::search((isset($_GET["qos"]) ? $_GET["qos"] : ""),$GLOBALS[\'_filter\'],true);'."\n";
		$p .= "?>\n";
		
		if (isset($attr['no_context'])) {
			return $p.$content;
		}

		return
		$p.
		'<?php if ($_ctx->pagination->f(0) > $_ctx->_search->count()) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	public static function SearchID($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->_search->search_id").'; ?>';
	}
	
	public static function SearchIfFirst($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->_search->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	public static function SearchIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->_search->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	public static function SearchLang($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo ucfirst('.sprintf($f,"\$_ctx->_search->search_lang").'); ?>';
	}
	
	public static function SearchType($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo ucfirst('.sprintf($f,"\$_ctx->_search->search_type").'); ?>';
	}
	
	public static function SearchURL($attr)
	{
		return '<?php echo $_ctx->_search->getItemPublicURL(); ?>';
	}

	public static function SearchTitle($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->_search->search_title').'; ?>';
	}
	
	public static function SearchAuthorLink($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->_search->getAuthorLink()').'; ?>';
	}
	
	public static function SearchDate($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->_search->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->_search->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->_search->getDate('".$format."')").'; ?>';
		}
	}
	
	public static function SearchTime($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->_search->getTime('".$format."')").'; ?>';
	}
	
	public static function SearchCategoryURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->_search->search_cat_url').'; ?>';
	}
	
	public static function SearchCategory($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->_search->search_cat_title').'; ?>';
	}
	
	public static function SearchContent($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->_search->getItemContent()').'; ?>';
	}
	
	public static function SearchCommentCount($attr)
	{
		$none = 'no comment';
		$one = 'one comment';
		$more = '%d comments';
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		
		if (empty($attr['count_all'])) {
			$operation = '$_ctx->_search->search_comment_nb';
		} else {
			$operation = '($_ctx->_search->search_comment_nb + $_ctx->_search->search_trackback_nb)';
		}
		
		return
		"<?php if (".$operation." == 0) {\n".
		"  printf(__('".$none."'),".$operation.");\n".
		"} elseif (".$operation." == 1) {\n".
		"  printf(__('".$one."'),".$operation.");\n".
		"} else {\n".
		"  printf(__('".$more."'),".$operation.");\n".
		"} ?>";
	}
	
	public static function SearchPingCount($attr)
	{
		$none = 'no trackback';
		$one = 'one trackback';
		$more = '%d trackbacks';
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		
		return
		"<?php if (\$_ctx->_search->search_trackback_nb == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->_search->search_trackback_nb);\n".
		"} elseif (\$_ctx->_search->search_trackback_nb == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->_search->search_trackback_nb);\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->_search->search_trackback_nb);\n".
		"} ?>";
	}
	
	public static function SearchCountByType($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_searchcountbytype[$_ctx->_search->search_type]').'; ?>';
	}
	
	public static function PaginationURL($attr)
	{
		$offset = 0;
		if (isset($attr['offset'])) {
			$offset = (integer) $attr['offset'];
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"dcOpenSearchTpl::SearchPaginationURL(".$offset.")").'; ?>';
	}
	
	public static function SearchPaginationURL($offset=0)
	{
		$args = array_key_exists('URL_REQUEST_PART',$_SERVER) ? $_SERVER['URL_REQUEST_PART'] : '';
		
		$n = context::PaginationPosition($offset);
		
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
		if (!empty($_GET['qos'])) {
			$s = strpos($url,'?') !== false ? '&amp;' : '?';
			$url .= $s.'qos='.rawurlencode($_GET['qos']);
		}
		# If search engine param
		if (!empty($_GET['se'])) {
			$url .= '&amp;se[]='.implode('&amp;se[]=',$_GET['se']);
		}
		return $url;
	}
	
	protected static function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}
}

?>