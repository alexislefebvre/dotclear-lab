<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of threadedComments, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------

class odtTemplate extends dcTemplate
{
	private $core;
	private $current_tag;
	
	function __construct($cache_dir,$self_name,&$core)
	{
		parent::__construct($cache_dir,$self_name);
		
		$this->remove_php = false;
		$this->use_cache = false;
		
		//$this->tag_block = '<tpl:(%1$s)(?:(\s+.*?)>|>)(.*?)</tpl:%1$s>';
		//$this->tag_value = '{{tpl:(%s)(\s(.*?))?}}';
	}
	
	/* TEMPLATE FUNCTIONS
	------------------------------------------------------- */
	
	public function l10n($attr,$str_attr)
	{
		# Normalize content
		$str_attr = preg_replace('/\s+/x',' ',$str_attr);
		
		return __(str_replace("'","\\'",$str_attr));
	}
	
	
	/* Blog ----------------------------------------------- */
	/*dtd
	<!ELEMENT tpl:BlogArchiveURL - O -- Blog Archives URL -->
	*/
	public function BlogArchiveURL($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->url.$core->url->getBase("archive"));
	}
	
	/*dtd
	<!ELEMENT tpl:BlogCopyrightNotice - O -- Blog copyrght notices -->
	*/
	public function BlogCopyrightNotice($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->settings->copyright_notice);
	}
	
	/*dtd
	<!ELEMENT tpl:BlogDescription - O -- Blog Description -->
	*/
	public function BlogDescription($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->desc);
	}
	
	/*dtd
	<!ELEMENT tpl:BlogEditor - O -- Blog Editor -->
	*/
	public function BlogEditor($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->settings->editor);
	}
	
	/*dtd
	<!ELEMENT tpl:BlogFeedID - O -- Blog Feed ID -->
	*/
	public function BlogFeedID($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,"urn:md5:".$core->blog->uid);
	}
	
	/*dtd
	<!ELEMENT tpl:BlogFeedURL - O -- Blog Feed URL -->
	<!ATTLIST tpl:BlogFeedURL
	type	(rss2|atom)	#IMPLIED	-- feed type (default : rss2)
	>
	*/
	public function BlogFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}
		
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->url.$core->url->getBase("feed")."/".$type);
	}
	
	/*dtd
	<!ELEMENT tpl:BlogName - O -- Blog Name -->
	*/
	public function BlogName($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->name);
	}
	
	/*dtd
	<!ELEMENT tpl:BlogLanguage - O -- Blog Language -->
	*/
	public function BlogLanguage($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->settings->lang);
	}
	
	/*dtd
	<!ELEMENT tpl:BlogThemeURL - O -- Blog's current Themei URL -->
	*/
	public function BlogThemeURL($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->settings->themes_url."/".$core->blog->settings->theme);
	}

	/*dtd
	<!ELEMENT tpl:BlogPublicURL - O -- Blog Public directory URL -->
	*/
	public function BlogPublicURL($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->settings->public_url);
	}
	
	/*dtd
	<!ELEMENT tpl:BlogUpdateDate - O -- Blog last update date -->
	<!ATTLIST tpl:BlogUpdateDate
	format	CDATA	#IMPLIED	-- date format (encoded in dc:str by default if iso8601 or rfc822 not specified)
	iso8601	CDATA	#IMPLIED	-- if set, tells that date format is ISO 8601
	rfc822	CDATA	#IMPLIED	-- if set, tells that date format is RFC 822
	>
	*/
	public function BlogUpdateDate($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		} else {
			$format = '%Y-%m-%d %H:%M:%S';
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $this->getFilters($attr);
		
		if ($rfc822) {
			return sprintf($f,dt::rfc822($core->blog->upddt,$core->blog->settings->blog_timezone));
		} elseif ($iso8601) {
			return sprintf($f,dt::iso8601($core->blog->upddt,$core->blog->settings->blog_timezone));
		} else {
			return sprintf($f,dt::str($format,$core->blog->upddt));
		}
	}
	
	/*dtd
	<!ELEMENT tpl:BlogID - 0 -- Blog ID -->
	*/
	public function BlogID($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->id);
	}
	
	/*dtd
	<!ELEMENT tpl:BlogRSDURL - O -- Blog RSD URL -->
	*/
	public function BlogRSDURL($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->url.$core->url->getBase("rsd"));
	}
	
	/*dtd
	<!ELEMENT tpl:BlogURL - O -- Blog URL -->
	*/
	public function BlogURL($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->url);
	}
	
	/*dtd
	<!ELEMENT tpl:BlogQmarkURL - O -- Blog URL, ending with a question mark -->
	*/
	public function BlogQmarkURL($attr)
	{
		$f = $this->getFilters($attr);
		return sprintf($f,$core->blog->getQmarkURL());
	}
	
	/*dtd
	<!ELEMENT tpl:BlogMetaRobots - O -- Blog meta robots tag definition, overrides robots_policy setting -->
	<!ATTLIST tpl:BlogMetaRobots
	robots	CDATA	#IMPLIED	-- can be INDEX,FOLLOW,NOINDEX,NOFOLLOW,ARCHIVE,NOARCHIVE
	>
	*/
	public function BlogMetaRobots($attr)
	{
		$robots = isset($attr['robots']) ? addslashes($attr['robots']) : '';
		return context::robotsPolicy($core->blog->settings->robots_policy,$robots);
	}
	
	
	/* Entries -------------------------------------------- */
	/*dtd
	<!ELEMENT tpl:Entries - - -- Blog Entries loop -->
	<!ATTLIST tpl:Entries
	lastn	CDATA	#IMPLIED	-- limit number of results to specified value
	author	CDATA	#IMPLIED	-- get entries for a given user id
	category	CDATA	#IMPLIED	-- get entries for specific categories only (multiple comma-separated categories can be specified. Use "!" as prefix to exclude a category)
	no_category	CDATA	#IMPLIED	-- get entries without category
	no_context (1|0)	#IMPLIED  -- Override context information
	sortby	(title|selected|author|date|id)	#IMPLIED	-- specify entries sort criteria (default : date)
	order	(desc|asc)	#IMPLIED	-- specify entries order (default : desc)
	no_content	(0|1)	#IMPLIED	-- do not retrieve entries content
	selected	(0|1)	#IMPLIED	-- retrieve posts marked as selected only (value: 1) or not selected only (value: 0)
	url		CDATA	#IMPLIED	-- retrieve post by its url
	type		CDATA	#IMPLIED	-- retrieve post with given post_type (there can be many ones separated by comma)
	ignore_pagination	(0|1)	#IMPLIED	-- ignore page number provided in URL (useful when using multiple tpl:Entries on the same page)
	>
	*/
	public function Entries($attr,$content)
	{
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
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
		
		if (isset($attr['author'])) {
			$p .= "\$params['user_id'] = '".addslashes($attr['author'])."';\n";
		}
		
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}
		
		if (isset($attr['no_category'])) {
			$p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
			$p .= "unset(\$params['cat_url']);\n";
		}
		
		if (!empty($attr['type'])) {
			$p .= "\$params['post_type'] = preg_split('/\s*,\s*/','".addslashes($attr['type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		}
		
		if (!empty($attr['url'])) {
			$p .= "\$params['post_url'] = '".addslashes($attr['url'])."';\n";
		}
		
		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("users")) { '.
				"\$params['user_id'] = \$_ctx->users->user_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("archives")) { '.
				"\$params['post_year'] = \$_ctx->archives->year(); ".
				"\$params['post_month'] = \$_ctx->archives->month(); ".
				"unset(\$params['limit']); ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['post_lang'] = \$_ctx->langs->post_lang; ".
			"}\n";
			
			$p .=
			'if (isset($_search)) { '.
				"\$params['search'] = \$_search; ".
			"}\n";
		}
		
		
		
		$sortby = 'post_dt';
		$order = 'desc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'title': $sortby = 'post_title'; break;
				case 'selected' : $sortby = 'post_selected'; break;
				case 'author' : $sortby = 'user_id'; break;
				case 'date' : $sortby = 'post_dt'; break;
				case 'id' : $sortby = 'post_id'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
		
		$p .= "\$params['order'] = '".$sortby." ".$order."';\n";
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		if (isset($attr['selected'])) {
			$p .= "\$params['post_selected'] = ".(integer) (boolean) $attr['selected'].";";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->posts = $core->blog->getPosts($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:DateHeader - O -- Displays date, if post is the first post of the given day -->
	*/
	public function DateHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->posts->firstPostOfDay()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:DateFooter - O -- Displays date,  if post is the last post of the given day -->
	*/
	public function DateFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->posts->lastPostOfDay()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryIf - - -- tests on current entry -->
	<!ATTLIST tpl:EntryIf
	type	CDATA	#IMPLIED	-- post has a given type (default: "post")
	category	CDATA	#IMPLIED	-- post has a given category
	first	(0|1)	#IMPLIED	-- post is the first post from list (value : 1) or not (value : 0)
	odd	(0|1)	#IMPLIED	-- post is in an odd position (value : 1) or not (value : 0)
	even	(0|1)	#IMPLIED	-- post is in an even position (value : 1) or not (value : 0)
	extended	(0|1)	#IMPLIED	-- post has an excerpt (value : 1) or not (value : 0)
	selected	(0|1)	#IMPLIED	-- post is selected (value : 1) or not (value : 0)
	has_category	(0|1)	#IMPLIED	-- post has a category (value : 1) or not (value : 0)
	has_attachment	(0|1)	#IMPLIED	-- post has attachments (value : 1) or not (value : 0)
	comments_active	(0|1)	#IMPLIED	-- comments are active for this post (value : 1) or not (value : 0)
	pings_active	(0|1)	#IMPLIED	-- trackbacks are active for this post (value : 1) or not (value : 0)
	show_comments	(0|1)	#IMPLIED	-- there are comments for this post (value : 1) or not (value : 0)
	show_pings	(0|1)	#IMPLIED	-- there are trackbacks for this post (value : 1) or not (value : 0)
	operator	(and|or)	#IMPLIED	-- combination of conditions, if more than 1 specifiec (default: and)
	url		CDATA	#IMPLIED	-- post has given url
	>
	*/
	public function EntryIf($attr,$content)
	{
		$if = array();
		$extended = null;
		$hascategory = null;
		
		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';

		if (isset($attr['type'])) {
			$type = trim($attr['type']);
			$type = !empty($type)?$type:'post';
			$if[] = '$_ctx->posts->post_type == "'.addslashes($type).'"';
		}
		
		if (isset($attr['url'])) {
			$url = trim($attr['url']);
			if (substr($url,0,1) == '!') {
				$url = substr($url,1);
				$if[] = '$_ctx->posts->post_url != "'.addslashes($url).'"';
			} else {
				$if[] = '$_ctx->posts->post_url == "'.addslashes($url).'"';
			}
		}
		
		if (isset($attr['category'])) {
			$category = addslashes(trim($attr['category']));
			if (substr($category,0,1) == '!') {
				$category = substr($category,1);
				$if[] = '($_ctx->posts->cat_url != "'.$category.'")';
			} else {
				$if[] = '($_ctx->posts->cat_url == "'.$category.'")';
			}
		}
		
		if (isset($attr['first'])) {
			$sign = (boolean) $attr['first'] ? '=' : '!';
			$if[] = '$_ctx->posts->index() '.$sign.'= 0';
		}
		
		if (isset($attr['odd'])) {
			$sign = (boolean) $attr['odd'] ? '=' : '!';
			$if[] = '($_ctx->posts->index()+1)%2 '.$sign.'= 1';
		}
		
		if (isset($attr['extended'])) {
			$sign = (boolean) $attr['extended'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->isExtended()';
		}
		
		if (isset($attr['selected'])) {
			$sign = (boolean) $attr['selected'] ? '' : '!';
			$if[] = $sign.'(boolean)$_ctx->posts->post_selected';
		}
		
		if (isset($attr['has_category'])) {
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->cat_id';
		}
		
		if (isset($attr['has_attachment'])) {
			$sign = (boolean) $attr['has_attachment'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->countMedia()';
		}
		
		if (isset($attr['comments_active'])) {
			$sign = (boolean) $attr['comments_active'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->commentsActive()';
		}
		
		if (isset($attr['pings_active'])) {
			$sign = (boolean) $attr['pings_active'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->trackbacksActive()';
		}
		
		if (isset($attr['has_comment'])) {
			$sign = (boolean) $attr['has_comment'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->hasComments()';
		}
		
		if (isset($attr['has_ping'])) {
			$sign = (boolean) $attr['has_ping'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->hasTrackbacks()';
		}
		
		if (isset($attr['show_comments'])) {
			if ((boolean) $attr['show_comments']) {
				$if[] = '($_ctx->posts->hasComments() || $_ctx->posts->commentsActive())';
			} else {
				$if[] = '(!$_ctx->posts->hasComments() && !$_ctx->posts->commentsActive())';
			}
		}
		
		if (isset($attr['show_pings'])) {
			if ((boolean) $attr['show_pings']) {
				$if[] = '($_ctx->posts->hasTrackbacks() || $_ctx->posts->trackbacksActive())';
			} else {
				$if[] = '(!$_ctx->posts->hasTrackbacks() && !$_ctx->posts->trackbacksActive())';
			}
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/*dtd
	<!ELEMENT tpl:EntryIfFirst - O -- displays value if entry is the first one -->
	<!ATTLIST tpl:EntryIfFirst
	return	CDATA	#IMPLIED	-- value to display in case of success (default: first)
	>
	*/
	public function EntryIfFirst($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->posts->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryIfOdd - O -- displays value if entry is in an odd position -->
	<!ATTLIST tpl:EntryIfOdd
	return	CDATA	#IMPLIED	-- value to display in case of success (default: odd)
	>
	*/
	public function EntryIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->posts->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryIfSelected - O -- displays value if entry is selected -->
	<!ATTLIST tpl:EntryIfSelected
	return	CDATA	#IMPLIED	-- value to display in case of success (default: selected)
	>
	*/
	public function EntryIfSelected($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'selected';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->posts->post_selected) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryContent - O -- Entry content -->
	<!ATTLIST tpl:EntryContent
	absolute_urls	CDATA	#IMPLIED -- transforms local URLs to absolute one
	full			(1|0)	#IMPLIED -- returns full content with excerpt
	>
	*/
	public function EntryContent($attr)
	{
		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}
		
		$f = $this->getFilters($attr);
		
		if (!empty($attr['full'])) {
			return '<?php echo '.sprintf($f,
				'$_ctx->posts->getExcerpt('.$urls.')." ".$_ctx->posts->getContent('.$urls.')').'; ?>';
		} else {
			return '<?php echo '.sprintf($f,'$_ctx->posts->getContent('.$urls.')').'; ?>';
		}
	}
	
	/*dtd
	<!ELEMENT tpl:EntryExcerpt - O -- Entry excerpt -->
	<!ATTLIST tpl:EntryExcerpt
	absolute_urls	CDATA	#IMPLIED -- transforms local URLs to absolute one
	>
	*/
	public function EntryExcerpt($attr)
	{
		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getExcerpt('.$urls.')').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAttachmentCount - O -- Number of attachments for entry -->
	<!ATTLIST tpl:EntryAttachmentCount
	none	CDATA	#IMPLIED	-- text to display for "no attachment" (default: no attachment)
	one	CDATA	#IMPLIED	-- text to display for "one attachment" (default: one attachment)
	more	CDATA	#IMPLIED	-- text to display for "more attachment" (default: %s attachment, %s is replaced by the number of attachments)
	>
	*/
	public function EntryAttachmentCount($attr)
	{
		$none = 'no attachment';
		$one = 'one attachment';
		$more = '%d attachments';
		
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
		"<?php if (\$_ctx->posts->countMedia() == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->posts->countMedia());\n".
		"} elseif (\$_ctx->posts->countMedia() == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->posts->countMedia());\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->posts->countMedia());\n".
		"} ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorCommonName - O -- Entry author common name -->
	*/
	public function EntryAuthorCommonName($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getAuthorCN()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorDisplayName - O -- Entry author display name -->
	*/
	public function EntryAuthorDisplayName($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->user_displayname').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorID - O -- Entry author ID -->
	*/
	public function EntryAuthorID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->user_id').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorEmail - O -- Entry author email -->
	<!ATTLIST tpl:EntryAuthorEmail
	spam_protected	(0|1)	#IMPLIED	-- protect email from spam (default: 1)
	>
	*/
	public function EntryAuthorEmail($attr)
	{
		$p = 'true';
		if (isset($attr['spam_protected']) && !$attr['spam_protected']) {
			$p = 'false';
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->posts->getAuthorEmail(".$p.")").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorLink - O -- Entry author link -->
	*/
	public function EntryAuthorLink($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getAuthorLink()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorURL - O -- Entry author URL -->
	*/
	public function EntryAuthorURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->user_url').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryBasename - O -- Entry short URL (relative to /post) -->
	*/
	public function EntryBasename($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->post_url').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCategory - O -- Entry category (full name) -->
	*/
	public function EntryCategory($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->cat_title').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCategoriesBreadcrumb - - -- Current entry parents loop (without last one) -->
	*/
	public function EntryCategoriesBreadcrumb($attr,$content)
	{
		return
		"<?php\n".
		'$_ctx->categories = $core->blog->getCategoryParents($_ctx->posts->cat_id);'."\n".
		'while ($_ctx->categories->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->categories = null; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCategoryID - O -- Entry category ID -->
	*/
	public function EntryCategoryID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->cat_id').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCategoryURL - O -- Entry category URL -->
	*/
	public function EntryCategoryURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getCategoryURL()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCategoryShortURL - O -- Entry category short URL (relative URL, from /category/) -->
	*/
	public function EntryCategoryShortURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->cat_url').'; ?>';
	}
	
	
	/*dtd
	<!ELEMENT tpl:EntryFeedID - O -- Entry feed ID -->
	*/
	public function EntryFeedID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getFeedID()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryFirstImage - O -- Extracts entry first image if exists -->
	<!ATTLIST tpl:EntryAuthorEmail
	size			(sq|t|s|m|o)	#IMPLIED	-- Image size to extract
	class		CDATA		#IMPLIED	-- Class to add on image tag
	with_category	(1|0)		#IMPLIED	-- Search in entry category description if present (default 0)
	>
	*/
	public function EntryFirstImage($attr)
	{
		$size = !empty($attr['size']) ? $attr['size'] : '';
		$class = !empty($attr['class']) ? $attr['class'] : '';
		$with_category = !empty($attr['with_category']) ? 'true' : 'false';
		
		return "<?php echo context::EntryFirstImageHelper('".addslashes($size)."',".$with_category.",'".addslashes($class)."'); ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryID - O -- Entry ID -->
	*/
	public function EntryID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->post_id').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryLang - O --  Entry language or blog lang if not defined -->
	*/
	public function EntryLang($attr)
	{
		$f = $this->getFilters($attr);
		return
		'<?php if ($_ctx->posts->post_lang) { '.
			'echo '.sprintf($f,'$_ctx->posts->post_lang').'; '.
		'} else {'.
			'echo '.sprintf($f,'$core->blog->settings->lang').'; '.
		'} ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryNext - - -- Next entry block -->
	<!ATTLIST tpl:EntryNext
	restrict_to_category	(0|1)	#IMPLIED	-- find next post in the same category (default: 0)
	restrict_to_lang		(0|1)	#IMPLIED	-- find next post in the same language (default: 0)
	>
	*/
	public function EntryNext($attr,$content)
	{
		$restrict_to_category = !empty($attr['restrict_to_category']) ? '1' : '0';
		$restrict_to_lang = !empty($attr['restrict_to_lang']) ? '1' : '0';
		
		return
		'<?php $next_post = $core->blog->getNextPost($_ctx->posts,1,'.$restrict_to_category.','.$restrict_to_lang.'); ?>'."\n".
		'<?php if ($next_post !== null) : ?>'.
			
			'<?php $_ctx->posts = $next_post; unset($next_post);'."\n".
			'while ($_ctx->posts->fetch()) : ?>'.
			$content.
			'<?php endwhile; $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryPrevious - - -- Previous entry block -->
	<!ATTLIST tpl:EntryPrevious
	restrict_to_category	(0|1)	#IMPLIED	-- find previous post in the same category (default: 0)
	restrict_to_lang		(0|1)	#IMPLIED	-- find next post in the same language (default: 0)
	>
	*/
	public function EntryPrevious($attr,$content)
	{
		$restrict_to_category = !empty($attr['restrict_to_category']) ? '1' : '0';
		$restrict_to_lang = !empty($attr['restrict_to_lang']) ? '1' : '0';
		
		return
		'<?php $prev_post = $core->blog->getNextPost($_ctx->posts,-1,'.$restrict_to_category.','.$restrict_to_lang.'); ?>'."\n".
		'<?php if ($prev_post !== null) : ?>'.
			
			'<?php $_ctx->posts = $prev_post; unset($prev_post);'."\n".
			'while ($_ctx->posts->fetch()) : ?>'.
			$content.
			'<?php endwhile; $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryTitle - O -- Entry title -->
	*/
	public function EntryTitle($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->post_title').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryURL - O -- Entry URL -->
	*/
	public function EntryURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getURL()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryDate - O -- Entry date -->
	<!ATTLIST tpl:EntryDate
	format	CDATA	#IMPLIED	-- date format (encoded in dc:str by default if iso8601 or rfc822 not specified)
	iso8601	CDATA	#IMPLIED	-- if set, tells that date format is ISO 8601
	rfc822	CDATA	#IMPLIED	-- if set, tells that date format is RFC 822
	>
	*/
	public function EntryDate($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $this->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->posts->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->posts->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->posts->getDate('".$format."')").'; ?>';
		}
	}
	
	/*dtd
	<!ELEMENT tpl:EntryTime - O -- Entry date -->
	<!ATTLIST tpl:EntryTime
	format	CDATA	#IMPLIED	-- time format 
	>
	*/
	public function EntryTime($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->posts->getTime('".$format."')").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntriesHeader - - -- First entries result container -->
	*/
	public function EntriesHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->posts->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntriesFooter - - -- Last entries result container -->
	*/
	public function EntriesFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->posts->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCommentCount - O -- Number of comments for entry -->
	<!ATTLIST tpl:EntryCommentCount
	none		CDATA	#IMPLIED	-- text to display for "no comment" (default: no comment)
	one		CDATA	#IMPLIED	-- text to display for "one comment" (default: one comment)
	more		CDATA	#IMPLIED	-- text to display for "more comments" (default: %s comments, %s is replaced by the number of comment)
	count_all	CDATA	#IMPLIED	-- count comments and trackbacks
	>
	*/
	public function EntryCommentCount($attr)
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
			$operation = '$_ctx->posts->nb_comment';
		} else {
			$operation = '($_ctx->posts->nb_comment + $_ctx->posts->nb_trackback)';
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
	
	/*dtd
	<!ELEMENT tpl:EntryPingCount - O -- Number of trackbacks for entry -->
	<!ATTLIST tpl:EntryPingCount
	none	CDATA	#IMPLIED	-- text to display for "no ping" (default: no ping)
	one	CDATA	#IMPLIED	-- text to display for "one ping" (default: one ping)
	more	CDATA	#IMPLIED	-- text to display for "more pings" (default: %s trackbacks, %s is replaced by the number of pings)
	>
	*/
	public function EntryPingCount($attr)
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
		"<?php if (\$_ctx->posts->nb_trackback == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->posts->nb_trackback);\n".
		"} elseif (\$_ctx->posts->nb_trackback == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->posts->nb_trackback);\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->posts->nb_trackback);\n".
		"} ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryPingData - O -- Display trackback RDF information -->
	*/
	public function EntryPingData($attr)
	{
		return "<?php if (\$_ctx->posts->trackbacksActive()) { echo \$_ctx->posts->getTrackbackData(); } ?>\n";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryPingLink - O -- Entry trackback link -->
	*/
	public function EntryPingLink($attr)
	{
		return "<?php if (\$_ctx->posts->trackbacksActive()) { echo \$_ctx->posts->getTrackbackLink(); } ?>\n";
	}
	
	/* Languages -------------------------------------- */
	/*dtd
	<!ELEMENT tpl:Languages - - -- Languages loop -->
	<!ATTLIST tpl:Languages
	lang	CDATA	#IMPLIED	-- restrict loop on given lang
	order	(desc|asc)	#IMPLIED	-- languages ordering (default: desc)
	>
	*/
	public function Languages($attr,$content)
	{
		$p = '$params = array();';
		
		if (isset($attr['lang'])) {
			$p = "\$params['lang'] = '".addslashes($attr['lang'])."';\n";
		}
		
		$order = 'desc';
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$p .= "\$params['order'] = '".$attr['order']."';\n ";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->langs = $core->blog->getLangs($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php if ($_ctx->langs->count() > 1) : '.
		'while ($_ctx->langs->fetch()) : ?>'.$content.
		'<?php endwhile; $_ctx->langs = null; endif; ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:LanguagesHeader - - -- First languages result container -->
	*/
	public function LanguagesHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->langs->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:LanguagesFooter - - -- Last languages result container -->
	*/
	public function LanguagesFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->langs->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:LanguageCode - O -- Language code -->
	*/
	public function LanguageCode($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->langs->post_lang').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:LanguageIfCurrent - - -- tests if post language is current language -->
	*/
	public function LanguageIfCurrent($attr,$content)
	{
		return
		"<?php if (\$_ctx->cur_lang == \$_ctx->langs->post_lang) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:LanguageURL - O -- Language URL -->
	*/
	public function LanguageURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("lang").$_ctx->langs->post_lang').'; ?>';
	}
	
	/* Comments --------------------------------------- */
	/*dtd
	<!ELEMENT tpl:Comments - - -- Comments container -->
	<!ATTLIST tpl:Comments
	with_pings	(0|1)	#IMPLIED	-- include trackbacks in request
	lastn	CDATA	#IMPLIED	-- restrict the number of entries 
	no_context (1|0)		#IMPLIED  -- Override context information
	order	(desc|asc)	#IMPLIED	-- result ordering (default: asc)
	>
	*/
	public function Comments($attr,$content)
	{
		$p =
		"if (\$_ctx->posts !== null) { ".
			"\$params['post_id'] = \$_ctx->posts->post_id; ".
			"\$core->blog->withoutPassword(false);\n".
		"}\n";
		
		if (empty($attr['with_pings'])) {
			$p .= "\$params['comment_trackback'] = false;\n";
		}
		
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "if (\$_ctx->nb_comment_per_page !== null) { \$params['limit'] = \$_ctx->nb_comment_per_page; }\n";
		}
		
		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['sql'] = \"AND P.post_lang = '\".\$core->blog->con->escape(\$_ctx->langs->post_lang).\"' \"; ".
			"}\n";
		}
		
		$order = 'asc';
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
		
		$p .= "\$params['order'] = 'comment_dt ".$order."';\n";
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->comments = $core->blog->getComments($params); unset($params);'."\n";
		$res .= "if (\$_ctx->posts !== null) { \$core->blog->withoutPassword(true);}\n";
		
		if (!empty($attr['with_pings'])) {
			$res .= '$_ctx->pings = $_ctx->comments;'."\n";
		}
		
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->comments->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->comments = null; ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:CommentAuthor - O -- Comment author -->
	*/
	public function CommentAuthor($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->comments->comment_author").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentAuthorDomain - O -- Comment author website domain -->
	*/
	public function CommentAuthorDomain($attr)
	{
		return '<?php echo preg_replace("#^http(?:s?)://(.+?)/.*$#msu",\'$1\',$_ctx->comments->comment_site); ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentAuthorLink - O -- Comment author link -->
	*/
	public function CommentAuthorLink($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->getAuthorLink()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentAuthorMailMD5 - O -- Comment author email MD5 sum -->
	*/
	public function CommentAuthorMailMD5($attr)
	{
		return '<?php echo md5($_ctx->comments->comment_email) ; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentAuthorURL - O -- Comment author URL -->
	*/
	public function CommentAuthorURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->getAuthorURL()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentContent - O --  Comment content -->
	<!ATTLIST tpl:CommentContent
	absolute_urls	(0|1)	#IMPLIED	-- convert URLS to absolute urls
	>
	*/
	public function CommentContent($attr)
	{
		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->getContent('.$urls.')').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentDate - O -- Comment date -->
	<!ATTLIST tpl:CommentDate
	format	CDATA	#IMPLIED	-- date format (encoded in dc:str by default if iso8601 or rfc822 not specified)
	iso8601	CDATA	#IMPLIED	-- if set, tells that date format is ISO 8601
	rfc822	CDATA	#IMPLIED	-- if set, tells that date format is RFC 822
	>
	*/
	public function CommentDate($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $this->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->comments->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->comments->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->comments->getDate('".$format."')").'; ?>';
		}
	}
	
	/*dtd
	<!ELEMENT tpl:CommentTime - O -- Comment date -->
	<!ATTLIST tpl:CommentTime
	format	CDATA	#IMPLIED	-- time format 
	>
	*/
	public function CommentTime($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->comments->getTime('".$format."')").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentEmail - O -- Comment author email -->
	<!ATTLIST tpl:CommentEmail
	spam_protected	(0|1)	#IMPLIED	-- protect email from spam (default: 1)
	>
	*/
	public function CommentEmail($attr)
	{
		$p = 'true';
		if (isset($attr['spam_protected']) && !$attr['spam_protected']) {
			$p = 'false';
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->comments->getEmail(".$p.")").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentEntryTitle - O -- Title of the comment entry -->
	*/
	public function CommentEntryTitle($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->post_title').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentFeedID - O -- Comment feed ID -->
	*/
	public function CommentFeedID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->getFeedID()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentID - O -- Comment ID -->
	*/
	public function CommentID($attr)
	{
		return '<?php echo $_ctx->comments->comment_id; ?>';
	}

	/*dtd
	<!ELEMENT tpl:CommentIf - - -- test container for comments -->
	<!ATTLIST tpl:CommentIf
	is_ping	(0|1)	#IMPLIED	-- test if comment is a trackback (value : 1) or not (value : 0)
	>
	*/
	public function CommentIf($attr,$content)
	{
		$if = array();
		$is_ping = null;
		
		if (isset($attr['is_ping'])) {
			$sign = (boolean) $attr['is_ping'] ? '' : '!';
			$if[] = $sign.'$_ctx->comments->comment_trackback';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' && ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/*dtd
	<!ELEMENT tpl:CommentIfFirst - O -- displays value if comment is the first one -->
	<!ATTLIST tpl:CommentIfFirst
	return	CDATA	#IMPLIED	-- value to display in case of success (default: first)
	>
	*/
	public function CommentIfFirst($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->comments->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CommentIfMe - O -- displays value if comment is the from the entry author -->
	<!ATTLIST tpl:CommentIfMe
	return	CDATA	#IMPLIED	-- value to display in case of success (default: me)
	>
	*/
	public function CommentIfMe($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'me';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->comments->isMe()) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CommentIfOdd - O -- displays value if comment is  at an odd position -->
	<!ATTLIST tpl:CommentIfOdd
	return	CDATA	#IMPLIED	-- value to display in case of success (default: odd)
	>
	*/
	public function CommentIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->comments->index()+1)%2) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CommentIP - O -- Comment author IP -->
	*/
	public function CommentIP($attr)
	{
		return '<?php echo $_ctx->comments->comment_ip; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentOrderNumber - O -- Comment order in page -->
	*/
	public function CommentOrderNumber($attr)
	{
		return '<?php echo $_ctx->comments->index()+1; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentsFooter - - -- Last comments result container -->
	*/
	public function CommentsFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->comments->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CommentsHeader - - -- First comments result container -->
	*/
	public function CommentsHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->comments->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPostURL - O -- Comment Entry URL -->
	*/
	public function CommentPostURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->getPostURL()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:IfCommentAuthorEmail - - -- Container displayed if comment author email is set -->
	*/
	public function IfCommentAuthorEmail($attr,$content)
	{
		return
		"<?php if (\$_ctx->comments->comment_email) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/* Comment preview -------------------------------- */
	/*dtd
	<!ELEMENT tpl:IfCommentPreview - - -- Container displayed if comment is being previewed -->
	*/
	public function IfCommentPreview($attr,$content)
	{
		return
		'<?php if ($_ctx->comment_preview !== null && $_ctx->comment_preview["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPreviewName - O -- Author name for the previewed comment -->
	*/
	public function CommentPreviewName($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comment_preview["name"]').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPreviewEmail - O -- Author email for the previewed comment -->
	*/
	public function CommentPreviewEmail($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comment_preview["mail"]').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPreviewSite - O -- Author site for the previewed comment -->
	*/
	public function CommentPreviewSite($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comment_preview["site"]').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPreviewContent - O -- Content of the previewed comment -->
	<!ATTLIST tpl:CommentPreviewContent
	raw	(0|1)	#IMPLIED	-- display comment in raw content
	>
	*/
	public function CommentPreviewContent($attr)
	{
		$f = $this->getFilters($attr);
		
		if (!empty($attr['raw'])) {
			$co = '$_ctx->comment_preview["rawcontent"]';
		} else {
			$co = '$_ctx->comment_preview["content"]';
		}
		
		return '<?php echo '.sprintf($f,$co).'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPreviewCheckRemember - O -- checkbox attribute for "remember me" (same value as before preview) -->
	*/
	public function CommentPreviewCheckRemember($attr)
	{
		return
		"<?php if (\$_ctx->comment_preview['remember']) { echo ' checked=\"checked\"'; } ?>";
	}
	
}
?>
