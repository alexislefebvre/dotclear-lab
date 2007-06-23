<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2003-2007 dcTeam and contributors. All rights
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
require_once dirname(__FILE__).'/_widgets.php';

$core->tpl->addValue('AuthorCommonName',array('tplAuthor','AuthorCommonName'));
$core->tpl->addValue('AuthorDisplayName',array('tplAuthor','AuthorDisplayName'));
$core->tpl->addValue('AuthorEmail',array('tplAuthor','AuthorEmail'));
$core->tpl->addValue('AuthorID',array('tplAuthor','AuthorID'));
$core->tpl->addValue('AuthorLink',array('tplAuthor','AuthorLink'));
$core->tpl->addValue('AuthorName',array('tplAuthor','AuthorName'));
$core->tpl->addValue('AuthorFirstName',array('tplAuthor','AuthorFirstName'));
$core->tpl->addValue('AuthorURL',array('tplAuthor','AuthorURL'));
$core->tpl->addValue('AuthorDesc',array('tplAuthor','AuthorDesc'));
$core->tpl->addValue('AuthorPostsURL',array('tplAuthor','AuthorPostsURL'));
$core->tpl->addValue('AuthorNbPosts',array('tplAuthor','AuthorNbPosts'));
$core->tpl->addValue('AuthorFeedURL',array('tplAuthor','AuthorFeedURL'));

$core->tpl->addBlock('Authors',array('tplAuthor','Authors'));
$core->tpl->addBlock('AuthorsHeader',array('tplAuthor','AuthorsHeader'));
$core->tpl->addBlock('AuthorsFooter',array('tplAuthor','AuthorsFooter'));

$core->addBehavior('templateBeforeBlock',array('behaviorAuthorMode','block'));
$core->addBehavior('publicBeforeDocument',array('behaviorAuthorMode','addTplPath'));

class behaviorAuthorMode
{
	public static function block()
	{
		$args = func_get_args();
		array_shift($args);

		if ($args[0] == 'Comments') {
			$p =
			'<?php if ($_ctx->exists("users")) { '.
				"@\$params['sql'] .= \"AND P.user_id = '\$_ctx->users->user_id' \";".
				"unset(\$params['limit']); ".
			"} ?>\n";
			return $p;
		}
	}
	
	public static function addTplPath(&$core)
	{
		$core->tpl->setPath($core->tpl->getPath(),dirname(__FILE__).'/default-templates');
	}
}

class tplAuthor
{
	public static function Authors($attr,$content)
	{
		$p = '';
		if (isset($attr['post_type'])) {
			$p .= "\$params['post_type'] = '".addslashes($attr['post_type'])."';\n";
		}
		if (isset($attr['sortby'])) {
			$order = 'asc';
			switch ($attr['sortby']) {
				case 'id'    : $sortby = 'user_id'; break;
				case 'posts' : $sortby = 'nb_post'; break;
				case 'name'  : $sortby = 'user_displayname, user_firstname, user_name'; break;
			}
			if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
				$order = $attr['order'];
			}
			if (isset($sortby)) {
				$p .= "\$params['order'] = '".$sortby." ".$order."';\n";
			}
		}
		
		if (empty($p)) {
			$p = '$params = null;'."\n";
		} else {
			$p = '$params = array();'."\n".$p;
		}
	
		$res =
		"<?php\n".
		'if (!$_ctx->exists("users")) { '.
		$p.
		'$_ctx->users = authormodeUtils::getPostsUsers($params); unset($params);'."\n".
		' } '.
		"?>\n".
		'<?php while ($_ctx->users->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->users = null; ?>';
		
		return $res;
	}

	public static function AuthorsHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->users->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	public static function AuthorsFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->users->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	public static function AuthorDesc($attr)
	{
		$res =
		"<?php\n".
		'echo $_ctx->users->user_desc;'."\n".
		"?>\n";

		return $res;
	}

	public static function AuthorPostsURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.
			sprintf($f,'$core->blog->url.$core->url->getBase("author").
			"/".$_ctx->users->user_id').'; ?>';
	}

	public static function AuthorNbPosts($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->nb_post').'; ?>';
	}
	

	public static function AuthorCommonName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->getAuthorCN()').'; ?>';
	}
	
	public static function AuthorDisplayName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_displayname').'; ?>';
	}
	
	public static function AuthorFirstName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_firstname').'; ?>';
	}
	
	public static function AuthorName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_name').'; ?>';
	}
	
	public static function AuthorID($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_id').'; ?>';
	}

	public static function AuthorEmail($attr)
	{
		$p = 'true';
		if (isset($attr['spam_protected']) && !$attr['spam_protected']) {
			$p = 'false';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->users->getAuthorEmail(".$p.")").'; ?>';
	}
	
	public static function AuthorLink($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->getAuthorLink()').'; ?>';
	}
	
	public static function AuthorURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->users->user_url').'; ?>';
	}

	public static function AuthorFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'rss2';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'rss2';
		}
		
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("author_feed")."/".'.
		'rawurlencode($_ctx->users->user_id)."/'.$type.'"').'; ?>';
	}

}
	

class urlAuthor extends dcUrlHandlers
{
	public static function Author($args)
	{
		$n = self::getPageNumber($args);
		
		if ($args == '' && !$n) {
			self::p404();
		} else {
			if ($n) {
				$GLOBALS['_page_number'] = $n;
			}
			$GLOBALS['_ctx']->users = authormodeUtils::getPostsUsers($args);

			if ($GLOBALS['_ctx']->users->isEmpty()) {
				self::p404();
			}
		
			self::serveDocument('author.html');
		}
		exit;
	}
	
	public static function Authors($args)
	{
		$GLOBALS['_ctx']->users = authormodeUtils::getPostsUsers($args);

		if ($GLOBALS['_ctx']->users->isEmpty()) {
			self::p404();
		}

		self::serveDocument('authors.html');
		exit;
	}

	public static function feed($args)
	{
		$mime = 'application/xml';
		
		if (preg_match('#^(.+)/(atom|rss2)(/comments)?$#',$args,$m))
		{
			$author = $m[1];
			$type = $m[2];
			$comments = !empty($m[3]);
		}
		else
		{
			self::p404();
		}
		
		$GLOBALS['_ctx']->users = authormodeUtils::getPostsUsers($author);
		
		if ($GLOBALS['_ctx']->users->isEmpty()) {
			self::p404();
		}
			
		if ($type == 'atom') {
			$mime = 'application/atom+xml';
		}
		
		$tpl = $type;
		if ($comments) {
			$tpl .= '-comments';
		}
		$tpl .= '.xml';
		
		self::serveDocument($tpl,$mime);
		exit;
	}
}

class authormodeUtils
{
	public static function getPostsUsers($params=null)
	{
		global $core;

		if ($params !== null && is_string($params)) {
			$params = array('author' => $params);
		}

		$strReq =
		'SELECT P.user_id, user_name, user_firstname, '.
		'user_displayname, user_desc, COUNT(P.post_id) as nb_post '.
		'FROM '.$core->prefix.'user U '.
		'LEFT JOIN '.$core->prefix.'post P ON P.user_id = U.user_id '.
		"WHERE blog_id = '".$core->con->escape($core->blog->id)."' ".
		'AND P.post_status = 1 ';

		if (!empty($params['author'])) {
			$strReq .=
			" AND P.user_id = '".$core->con->escape($params['author'])."' ";
		}
		
		if (!empty($params['post_type'])) {
			$strReq .=
			" AND P.post_type = '".$core->con->escape($params['post_type'])."' ";
		}
		elseif ($core->blog->settings->authormode_default_posts_only)
		{
			$strReq .=
			" AND P.post_type = 'post' ";
		}
						
		$strReq .=
		'GROUP BY P.user_id, user_name, user_firstname, user_displayname, user_desc ';

		if (!empty($params['order'])) {
			$strReq .=
			'ORDER BY '.$core->con->escape($params['order']).' ';
		}
		elseif ($core->blog->settings->authormode_default_alpha_order)
		{
			$strReq .=
			'ORDER BY user_displayname, user_firstname, user_name ';
		}

		try
		{
			$rs = $core->con->select($strReq);
			$rs->extend('rsAuthor');
			return $rs;
		}
		catch (Exception $e) {
			throw $e;
		}
	}
}
?>
