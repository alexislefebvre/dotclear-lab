<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

require_once dirname(__FILE__).'/_widgets.php';

if ($core->blog->settings->cinecturlink2_active)
{
	$core->tpl->addBlock('c2If',array('tplCinecturlink2','c2If'));

	$core->tpl->addValue('c2PageFeedID',array('tplCinecturlink2','c2PageFeedID'));
	$core->tpl->addValue('c2PageFeedURL',array('tplCinecturlink2','c2PageFeedURL'));
	$core->tpl->addValue('c2PageURL',array('tplCinecturlink2','c2PageURL'));
	$core->tpl->addValue('c2PageTitle',array('tplCinecturlink2','c2PageTitle'));
	$core->tpl->addValue('c2PageDescription',array('tplCinecturlink2','c2PageDescription'));

	$core->tpl->addBlock('c2Entries',array('tplCinecturlink2','c2Entries'));
	$core->tpl->addBlock('c2EntriesHeader',array('tplCinecturlink2','c2EntriesHeader'));
	$core->tpl->addBlock('c2EntriesFooter',array('tplCinecturlink2','c2EntriesFooter'));
	$core->tpl->addBlock('c2EntryIf',array('tplCinecturlink2','c2EntryIf'));
	$core->tpl->addValue('c2EntryIfOdd',array('tplCinecturlink2','c2EntryIfOdd'));
	$core->tpl->addValue('c2EntryIfFirst',array('tplCinecturlink2','c2EntryIfFirst'));
	$core->tpl->addValue('c2EntryFeedID',array('tplCinecturlink2','c2EntryFeedID'));
	$core->tpl->addValue('c2EntryID',array('tplCinecturlink2','c2EntryID'));
	$core->tpl->addValue('c2EntryTitle',array('tplCinecturlink2','c2EntryTitle'));
	$core->tpl->addValue('c2EntryDescription',array('tplCinecturlink2','c2EntryDescription'));
	$core->tpl->addValue('c2EntryFromAuthor',array('tplCinecturlink2','c2EntryFromAuthor'));
	$core->tpl->addValue('c2EntryAuthorCommonName',array('tplCinecturlink2','c2EntryAuthorCommonName'));
	$core->tpl->addValue('c2EntryAuthorDisplayName',array('tplCinecturlink2','c2EntryAuthorDisplayName'));
	$core->tpl->addValue('c2EntryAuthorEmail',array('tplCinecturlink2','c2EntryAuthorEmail'));
	$core->tpl->addValue('c2EntryAuthorID',array('tplCinecturlink2','c2EntryAuthorID'));
	$core->tpl->addValue('c2EntryAuthorLink',array('tplCinecturlink2','c2EntryAuthorLink'));
	$core->tpl->addValue('c2EntryAuthorURL',array('tplCinecturlink2','c2EntryAuthorURL'));
	$core->tpl->addValue('c2EntryLang',array('tplCinecturlink2','c2EntryLang'));
	$core->tpl->addValue('c2EntryURL',array('tplCinecturlink2','c2EntryURL'));
	$core->tpl->addValue('c2EntryCategory',array('tplCinecturlink2','c2EntryCategory'));
	$core->tpl->addValue('c2EntryCategoryID',array('tplCinecturlink2','c2EntryCategoryID'));
	$core->tpl->addValue('c2EntryCategoryURL',array('tplCinecturlink2','c2EntryCategoryURL'));
	$core->tpl->addValue('c2EntryImg',array('tplCinecturlink2','c2EntryImg'));
	$core->tpl->addValue('c2EntryDate',array('tplCinecturlink2','c2EntryDate'));
	$core->tpl->addValue('c2EntryTime',array('tplCinecturlink2','c2EntryTime'));

	$core->tpl->addBlock('c2Pagination',array('tplCinecturlink2','c2Pagination'));
	$core->tpl->addValue('c2PaginationCounter',array('tplCinecturlink2','c2PaginationCounter'));
	$core->tpl->addValue('c2PaginationCurrent',array('tplCinecturlink2','c2PaginationCurrent'));
	$core->tpl->addBlock('c2PaginationIf',array('tplCinecturlink2','c2PaginationIf'));
	$core->tpl->addValue('c2PaginationURL',array('tplCinecturlink2','c2PaginationURL'));

	$core->tpl->addBlock('c2Categories',array('tplCinecturlink2','c2Categories'));
	$core->tpl->addBlock('c2CategoriesHeader',array('tplCinecturlink2','c2CategoriesHeader'));
	$core->tpl->addBlock('c2CategoriesFooter',array('tplCinecturlink2','c2CategoriesFooter'));
	$core->tpl->addBlock('c2CategoryIf',array('tplCinecturlink2','c2CategoryIf'));
	$core->tpl->addValue('c2CategoryFeedID',array('tplCinecturlink2','c2CategoryFeedID'));
	$core->tpl->addValue('c2CategoryFeedURL',array('tplCinecturlink2','c2CategoryFeedURL'));
	$core->tpl->addValue('c2CategoryID',array('tplCinecturlink2','c2CategoryID'));
	$core->tpl->addValue('c2CategoryTitle',array('tplCinecturlink2','c2CategoryTitle'));
	$core->tpl->addValue('c2CategoryDescription',array('tplCinecturlink2','c2CategoryDescription'));
	$core->tpl->addValue('c2CategoryURL',array('tplCinecturlink2','c2CategoryURL'));
}
else
{
	$core->tpl->addBlock('c2If',array('tplCinecturlink2','disable'));

	$core->tpl->addValue('c2PageFeedID',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2PageFeedURL',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2PageURL',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2PageTitle',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2PageDescription',array('tplCinecturlink2','disable'));

	$core->tpl->addBlock('c2Entries',array('tplCinecturlink2','disable'));
	$core->tpl->addBlock('c2EntriesHeader',array('tplCinecturlink2','disable'));
	$core->tpl->addBlock('c2EntriesFooter',array('tplCinecturlink2','disable'));
	$core->tpl->addBlock('c2EntryIf',array('tplCinecturlink2','disable'));

	$core->tpl->addValue('c2EntryIfOdd',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryIfFirst',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryFeedID',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryID',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryTitle',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryDescription',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryFromAuthor',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryAuthorCommonName',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryAuthorDisplayName',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryAuthorEmail',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryAuthorID',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryAuthorLink',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryAuthorURL',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryLang',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryURL',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryCategory',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryCategoryID',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryCategoryURL',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryImg',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryDate',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2EntryTime',array('tplCinecturlink2','disable'));

	$core->tpl->addBlock('c2Pagination',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2PaginationCounter',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2PaginationCurrent',array('tplCinecturlink2','disable'));
	$core->tpl->addBlock('c2PaginationIf',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2PaginationURL',array('tplCinecturlink2','disable'));

	$core->tpl->addBlock('c2Categories',array('tplCinecturlink2','disable'));
	$core->tpl->addBlock('c2CategoriesHeader',array('tplCinecturlink2','disable'));
	$core->tpl->addBlock('c2CategoriesFooter',array('tplCinecturlink2','disable'));
	$core->tpl->addBlock('c2CategoryIf',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2CategoryFeedID',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2CategoryFeedURL',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2CategoryID',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2CategoryTitle',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2CategoryDescription',array('tplCinecturlink2','disable'));
	$core->tpl->addValue('c2CategoryURL',array('tplCinecturlink2','disable'));
}

class urlCinecturlink2 extends dcUrlHandlers
{
	public static function c2Page($args)
	{
		global $core, $_ctx;

		if (!$core->blog->settings->cinecturlink2_active 
		 || !$core->blog->settings->cinecturlink2_public_active)
		{
			self::p404();
			return;
		}

		$core->tpl->setPath($core->tpl->getPath(),
			dirname(__FILE__).'/default-templates/'
		);

		$params = array();

		$n = self::getPageArgs($args,'c2page');
		if ($n) {
			$GLOBALS['c2_page_number'] = $n;
		}

		$caturl = $core->blog->settings->cinecturlink2_public_caturl;
		if (!$caturl) $caturl = 'c2cat';

		$c = self::getPageArgs($args,$caturl);
		if ($c) {
			if (is_numeric($c))
			{
				$params['cat_id'] = (integer) $c;
			}
			else
			{
				$params['cat_title'] = urldecode($c);
			}
		}

		$f = self::getPageArgs($args,'feed');
		if ($f && in_array($f,array('atom','rss2')))
		{
			$mime = $f == 'atom' ? 'application/atom+xml' : 'application/xml';

			//$_ctx->short_feed_items = $core->blog->settings->short_feed_items;

			$params['limit'] = $core->blog->settings->nb_post_per_feed;
			$_ctx->c2_page_params = $params;

			header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->robots_policy,''));
			self::serveDocument('cinecturlink2-'.$f.'.xml',$mime);
		}
		else
		{
			$d = self::getPageArgs($args,'c2detail');
			if ($d) {
				if (is_numeric($d))
				{
					$params['link_id'] = (integer) $d;
				}
				else
				{
					$params['link_title'] = urldecode($d);
				}
			}

			$params['limit'] = $core->blog->settings->cinecturlink2_public_nbrpp;
			$_ctx->c2_page_params = $params;

			self::serveDocument('cinecturlink2.html','text/html');
		}
		return;
	}

	protected static function getPageArgs(&$args,$part)
	{
		if (preg_match('#(^|/)'.$part.'/([^/]+)#',$args,$m))
		{
			$args = preg_replace('#(^|/)'.$part.'/([^/]+)#','',$args);
			return $m[2];
		}
		return false;
	}
}
 
class tplCinecturlink2
{
	public static function disable($a,$c=null)
	{
		return '';
	}

	public static function c2PageURL($a)
	{
		return "<?php echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$core->blog->url.$core->url->getBase(\'cinecturlink2\')')."; ?>";
	}

	public static function c2PageTitle($a)
	{
		return "<?php \$title = (string) \$core->blog->settings->cinecturlink2_public_title; if (empty(\$title)) { \$title = __('My cinecturlink'); } echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$title')."; ?>";
	}
	
	public static function c2PageFeedURL($a)
	{
		return "<?php echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$core->blog->url.$core->url->getBase("cinecturlink2")."/feed/'.(!empty($a['type']) && preg_match('#^(rss2|atom)$#',$a['type']) ? $a['type'] : 'atom').'"')."; ?>";
	}

	public static function c2PageFeedID($a)
	{
		return 'urn:md5:<?php echo md5($core->blog->blog_id."cinecturlink2"); ?>';
	}

	public static function c2PageDescription($a)
	{
		return "<?php \$description = (string) \$core->blog->settings->cinecturlink2_public_description; echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$description')."; ?>";
	}

	public static function c2If($a,$c)
	{
		$if = array();

		$operator = isset($a['operator']) ? self::getOperator($a['operator']) : '&&';

		if (isset($a['request_link']))
		{
			$sign = (boolean) $a['request_link'] ? '' : '!';
			$if[] = $sign.'(isset($_ctx->c2_page_params["link_id"]) || isset($_ctx->c2_page_params["link_title"]))';
		}

		if (isset($a['request_cat']))
		{
			$sign = (boolean) $a['request_cat'] ? '' : '!';
			$if[] = $sign.'(isset($_ctx->c2_page_params["cat_id"]) || isset($_ctx->c2_page_params["cat_title"]))';
		}

		if (empty($if))
		{
			return $c;
		}
		return 
		"<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".
		$c.
		"<?php endif; ?>\n";
	}

	public static function c2Entries($a,$c)
	{
		$lastn = isset($a['lastn']) ? abs((integer) $a['lastn'])+0 : -1;

		$res = 'if (!isset($c2_page_number)) { $c2_page_number = 1; }'."\n";

		if ($lastn != 0)
		{
			if ($lastn > 0) {
				$res .= "\$params['limit'] = ".$lastn.";\n";
			} else {
				$res .= 
				"if (!isset(\$params['limit']) || \$params['limit'] < 1) { \$params['limit'] = 10; }\n";
			}
			
			if (!isset($a['ignore_pagination']) || $a['ignore_pagination'] == "0") {
				$res .= "\$params['limit'] = array(((\$c2_page_number-1)*\$params['limit']),\$params['limit']);\n";
			} else {
				$res .= "\$params['limit'] = array(0, \$params['limit']);\n";
			}
		}

		if (isset($a['category']))
		{
			if ($a['category'] == 'null')
			{
				$res .= "\$params['sql'] = ' AND L.cat_id IS NULL ';\n";
			}
			elseif (is_numeric($a['category']))
			{
				$res .= "\$params['cat_id'] = ".(integer) $a['category'].";\n";
			}
			else
			{
				$res .= "\$params['cat_title'] = '".$a['category']."';\n";
			}
		}

		$sort = isset($a['sort']) && $a['sort'] == 'asc' ? ' asc' : ' desc';
		$sortby = isset($a['order']) && in_array($a['order'],array('link_count','link_upddt','link_creadt','link_note','link_title')) ? $a['order'] : 'link_upddt';

		$res .= 
		"\$params['order'] = '".$sortby.$sort."';\n";

		return 
		"<?php \n".
		"\$params = is_array(\$_ctx->c2_page_params) ? \$_ctx->c2_page_params : array(); \n".
		$res.
		"\$_ctx->c2_params = \$params; unset(\$params);\n".
		"if (!\$_ctx->exists('cinecturlink')) { \$_ctx->cinecturlink = new cinecturlink2(\$core); } \n".
		"\$_ctx->c2_entries = \$_ctx->cinecturlink->getLinks(\$_ctx->c2_params); \n".
		'while ($_ctx->c2_entries->fetch()) : ?>'.$c.'<?php endwhile; '."\n".
		"\$_ctx->c2_entries = null; \$_ctx->c2_params = null; \n".
		"?>\n";
	}

	public static function c2EntriesHeader($a,$c)
	{
		return "<?php if (\$_ctx->c2_entries->isStart()) : ?>".$c."<?php endif; ?>";
	}

	public static function c2EntriesFooter($a,$c)
	{
		return "<?php if (\$_ctx->c2_entries->isEnd()) : ?>".$c."<?php endif; ?>";
	}

	public static function c2EntryIf($a,$c)
	{
		$if = array();

		$operator = isset($a['operator']) ? self::getOperator($a['operator']) : '&&';

		if (isset($a['has_category']))
		{
			$sign = (boolean) $a['has_category'] ? '!' : '=';
			$if[] = '($_ctx->exists("c2_entries") && "" '.$sign.'= $_ctx->c2_entries->cat_title)';
		}

		return empty($if) ? $c : "<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".$c."<?php endif; ?>\n";
	}

	public static function c2EntryIfFirst($a)
	{
		return '<?php if ($_ctx->c2_entries->index() == 0) { echo "'.(isset($a['return']) ? addslashes(html::escapeHTML($a['return'])) : 'first').'"; } ?>';
	}

	public static function c2EntryIfOdd($a)
	{
		return '<?php if (($_ctx->c2_entries->index()+1)%2 == 1) { echo "'.(isset($a['return']) ? addslashes(html::escapeHTML($a['return'])) : 'odd').'"; } ?>';
	}

	public static function c2EntryFeedID($attr)
	{
		return 'urn:md5:<?php echo md5($_ctx->c2_entries->blog_id.$_ctx->c2_entries->link_id.$_ctx->c2_entries->link_dt); ?>';
	}

	public static function c2EntryID($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->link_id',$a);
	}

	public static function c2EntryTitle($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->link_title',$a);
	}

	public static function c2EntryDescription($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->link_desc',$a);
	}

	public static function c2EntryAuthorCommonName($a)
	{
		return self::getGenericValue('dcUtils::getUserCN($_ctx->c2_entries->user_id,$_ctx->c2_entries->user_name,$_ctx->c2_entries->user_firstname,$_ctx->c2_entries->user_displayname)',$a);
	}

	public static function c2EntryAuthorDisplayName($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->user_displayname',$a);
	}

	public static function c2EntryAuthorID($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->user_id',$a);
	}

	public static function c2EntryAuthorEmail($a)
	{
		return self::getGenericValue((isset($a['spam_protected']) && !$a['spam_protected'] ? '$_ctx->c2_entries->user_email' : "strtr(\$_ctx->c2_entries->user_email,array('@'=>'%40','.'=>'%2e'))"),$a);
	}

	public static function c2EntryAuthorLink($a)
	{
		return self::getGenericValue('sprintf(($_ctx->c2_entries->user_url ? \'<a href="%2$s">%1$s</a>\' : \'%1$s\'),html::escapeHTML(dcUtils::getUserCN($_ctx->c2_entries->user_id,$_ctx->c2_entries->user_name,$_ctx->c2_entries->user_firstname,$_ctx->c2_entries->user_displayname)),html::escapeHTML($_ctx->c2_entries->user_url))',$attr);
	}

	public static function c2EntryAuthorURL($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->user_url',$a);
	}

	public static function c2EntryFromAuthor($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->link_author',$a);
	}

	public static function c2EntryLang($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->link_lang',$a);
	}

	public static function c2EntryURL($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->link_url',$a);
	}

	public static function c2EntryCategory($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->cat_title',$a);
	}

	public static function c2EntryCategoryID($a)
	{
		return self::getGenericValue('$_ctx->c2_entries->cat_id',$a);
	}

	public static function c2EntryCategoryURL($a)
	{
		return self::getGenericValue('$core->blog->url.$core->url->getBase("cinecturlink2")."/".$core->blog->settings->cinecturlink2_public_caturl."/".urlencode($_ctx->c2_entries->cat_title)',$a);
	}

	public static function c2EntryImg($a)
	{
		global $core;
		$f = $core->tpl->getFilters($a);
		$style = isset($a['style']) ? html::escapeHTML($a['style']) : '';

		return 
		"<?php if (\$_ctx->exists('c2_entries')) { ".
		"\$widthmax = (integer) \$core->blog->settings->cinecturlink2_widthmax; ".
		"\$img = sprintf('<img src=\"%s\" alt=\"%s\" %s/>',".
		"\$_ctx->c2_entries->link_img, ".
		"html::escapeHTML(\$_ctx->c2_entries->link_title.' - '.\$_ctx->c2_entries->link_author), ".
		"(\$widthmax ? ' style=\"width:'.\$widthmax.'px;$style\"' : '') ".
		"); ".
		"echo ".sprintf($f,'$img')."; unset(\$img); } ?> \n";
	}

	public static function c2EntryDate($a)
	{
		$format = !empty($a['format']) ? addslashes($a['format']) : '';

		if (!empty($a['rfc822']))
			$p = "dt::rfc822(strtotime(\$_ctx->c2_entries->link_creadt),\$_ctx->posts->post_tz)";
		elseif (!empty($a['iso8601']))
			$p = "dt::iso8601(strtotime(\$_ctx->c2_entries->link_creadt),\$_ctx->posts->post_tz)";
		elseif ($format)
			$p = "dt::dt2str('".$format."',\$_ctx->c2_entries->link_creadt)";
		else
			$p = "dt::dt2str(\$core->blog->settings->date_format,\$_ctx->c2_entries->link_creadt)";

		return self::getGenericValue($p,$a);
	}

	public static function c2EntryTime($a)
	{
		return self::getGenericValue("dt::dt2str(".(!empty($a['format']) ? "'".addslashes($a['format'])."'" : '$core->blog->settings->time_format').",\$_ctx->c2_entries->link_creadt)",$a);
	}

	public static function c2Pagination($a,$c)
	{
		$p = 
		"<?php\n".
		"\$params = \$_ctx->c2_params;\n".
		"\$_ctx->c2_pagination = \$_ctx->cinecturlink->getLinks(\$params,true); unset(\$params);\n".
		"?>\n";

		if (isset($a['no_context'])) return $p.$c;

		return $p.'<?php if ($_ctx->c2_pagination->f(0) > $_ctx->c2_entries->count()) : ?>'.$c.'<?php endif; ?>';
	}

	public static function c2PaginationCounter($a)
	{
		return self::getGenericValue('c2_context::PaginationNbPages()',$a);
	}

	public static function c2PaginationCurrent($a)
	{
		return self::getGenericValue('c2_context::PaginationPosition('.(isset($a['offset']) ? (integer) $a['offset'] : 0).')',$a);
	}

	public static function c2PaginationIf($a,$c)
	{
		$if = array();
		
		if (isset($a['start'])) {
			$sign = (boolean) $a['start'] ? '' : '!';
			$if[] = $sign.'c2_context::PaginationStart()';
		}
		
		if (isset($a['end'])) {
			$sign = (boolean) $a['end'] ? '' : '!';
			$if[] = $sign.'c2_context::PaginationEnd()';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' && ',$if).') : ?>'.$c.'<?php endif; ?>';
		} else {
			return $c;
		}
	}

	public static function c2PaginationURL($a)
	{
		return self::getGenericValue('c2_context::PaginationURL('.(isset($a['offset']) ? (integer) $a['offset'] : 0).')',$a);
	}

	public static function c2Categories($a,$c)
	{
		return 
		"<?php \n".
		"if (!\$_ctx->exists('cinecturlink')) { \$_ctx->cinecturlink = new cinecturlink2(\$core); } \n".
		"\$_ctx->c2_categories = \$_ctx->cinecturlink->getCategories(); \n".
		'while ($_ctx->c2_categories->fetch()) : ?>'.$c.'<?php endwhile; '."\n".
		"\$_ctx->c2_categories = null; \n".
		"?>\n";
	}

	public static function c2CategoriesHeader($a,$c)
	{
		return "<?php if (\$_ctx->c2_categories->isStart()) : ?>".$c."<?php endif; ?>";
	}

	public static function c2CategoriesFooter($a,$c)
	{
		return "<?php if (\$_ctx->c2_categories->isEnd()) : ?>".$c."<?php endif; ?>";
	}

	public static function c2CategoryIf($a,$c)
	{
		$if = array();

		if (isset($a['current'])) {
			$sign = (boolean) $a['current'] ? '' : '!';
			$if[] = $sign.'c2_context::CategoryCurrent()';
		}

		if (isset($a['first'])) {
			$sign = (boolean) $a['first'] ? '' : '!';
			$if[] = $sign.'$_ctx->c2_categories->isStart()';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' && ',$if).') : ?>'.$c.'<?php endif; ?>';
		} else {
			return $c;
		}
	}
	
	public static function c2CategoryFeedURL($a)
	{
		$p = !empty($a['type']) ? $a['type'] : 'atom';
		
		if (!preg_match('#^(rss2|atom)$#',$p)) {
			$p = 'atom';
		}

		return "<?php echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$core->blog->url.$core->url->getBase("cinecturlink2")."/".$core->blog->settings->cinecturlink2_public_caturl."/".urlencode($_ctx->c2_categories->cat_title)."/feed/'.$p.'"')."; ?>";
	}

	public static function c2CategoryFeedID($a)
	{
		return 'urn:md5:<?php echo md5($core->blog->blog_id."cinecturlink2".$_ctx->c2_categories->cat_id); ?>';
	}

	public static function c2CategoryID($a)
	{
		return "<?php if (\$_ctx->exists('c2_categories')) { echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$_ctx->c2_categories->cat_id')."; } ?>";
	}

	public static function c2CategoryTitle($a)
	{
		return "<?php if (\$_ctx->exists('c2_categories')) { echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$_ctx->c2_categories->cat_title')."; } ?>";
	}

	public static function c2CategoryDescription($a)
	{
		return "<?php if (\$_ctx->exists('c2_categories')) { echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$_ctx->c2_categories->cat_desc')."; } ?>";
	}

	public static function c2CategoryURL($a)
	{
		return "<?php if (\$_ctx->exists('c2_categories')) { echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$core->blog->url.$core->url->getBase("cinecturlink2")."/".$core->blog->settings->cinecturlink2_public_caturl."/".urlencode($_ctx->c2_categories->cat_title)')."; } ?>";
	}

	protected static function getGenericValue($p,$a)
	{
		return "<?php if (\$_ctx->exists('c2_entries')) { echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),"$p")."; } ?>";
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