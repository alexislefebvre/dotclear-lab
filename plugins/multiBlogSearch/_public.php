<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiBlogSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zesntyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('publicBeforeDocument',array('multiBlogSearchBehaviors','addTplPath'));
$core->addBehavior('publicBeforeDocument',array('multiBlogSearchBehaviors','multiBlogSearch'));

$core->tpl->addValue('MultiBlogSearchPaginationURL',array('multiBlogSearchTpl','paginationURL'));
$core->tpl->addValue('MultiBlogSearchBlogName',array('multiBlogSearchTpl','blogName'));
$core->tpl->addValue('MultiBlogSearchEntryURL',array('multiBlogSearchTpl','entryURL'));
$core->tpl->addValue('MultiBlogSearchCategoryURL',array('multiBlogSearchTpl','categoryURL'));
$core->tpl->addValue('MultiBlogSearchMetaURL',array('multiBlogSearchTpl','metaURL'));
$core->tpl->addBlock('MultiBlogSearchEntries',array('multiBlogSearchTpl','entries'));
$core->tpl->addBlock('MultiBlogSearchPagination',array('multiBlogSearchTpl','pagination'));
$core->tpl->addBlock('MultiBlogSearchBlogHeader',array('multiBlogSearchTpl','blogHeader'));

class multiBlogSearchBehaviors extends dcUrlHandlers
{

	public static function addTplPath()
	{
		global $core;

		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}

	public static function multiBlogSearch()
	{
		$GLOBALS['_search'] = !empty($_GET['qm']) ? rawurldecode($_GET['qm']) : '';

		$_SERVER['URL_REQUEST_PART'] = $GLOBALS['core']->url->mode == 'path_info' ? substr($_SERVER['PATH_INFO'],1) : $_SERVER['QUERY_STRING'];
	
		preg_match('#page/([0-9]+)#',$_SERVER['URL_REQUEST_PART'],$args);

		$GLOBALS['_page_number'] = isset($args[1]) ? $args[1] : 1;

		if ($GLOBALS['_search']) {
			$GLOBALS['_ctx']->multiblogsearch = new multiBlogSearch($GLOBALS['core']);
			$GLOBALS['_search_count'] = $GLOBALS['_ctx']->multiblogsearch->getPosts(array('search' => $GLOBALS['_search']),true)->f(0);
			self::serveDocument('multiblogsearch.html');
			exit;
		}
	}
}

class multiBlogSearchTpl
{
	public static function paginationURL($attr)
	{
		$offset = 0;
		if (isset($attr['offset'])) {
			$offset = (integer) $attr['offset'];
		}

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,"multiBlogSearch::PaginationURL(".$offset.")").'; ?>';
	}

	public static function blogName($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->blog_name').'; ?>';
	}

	public static function entryURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->blog_url.$core->url->getBase("post").'.
		'"/".$_ctx->posts->post_url').'; ?>';
	}

	public static function categoryURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->blog_url.$core->url->getBase("category").'.
		'"/".$_ctx->posts->cat_url').'; ?>';
	}
	
	public static function metaURL($attr)
	{
		$f = $GLOBALS['core']->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->blog_url.$core->url->getBase("tag").'.
		'"/".rawurlencode($_ctx->meta->meta_id)').'; ?>';
	}

	public static function entries($attr,$content)
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
			$p .= "\$params['limit'] = array(((\$GLOBALS['_page_number']-1)*\$params['limit']),\$params['limit']);\n";
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

		$sortby = 'blog_name';
		$order = 'asc';
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
		$res .= '$_ctx->posts = $_ctx->multiblogsearch->getPosts($params);'."\n";
		$res .= 'unset($params);'."\n";
		$res .= "?>\n";

		$res .=
		'<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';

		return $res;
	}

	public static function blogHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->multiblogsearch->firstPostOfBlog(\$_ctx->posts)) : ?>".
		$content.
		"<?php endif; ?>";
	}

	public static function pagination($attr,$content)
	{
		$p = "<?php\n";
		$p .= '$params = $_ctx->post_params;'."\n";
		$p .= '$_ctx->pagination = $_ctx->multiblogsearch->getPosts($params,true); unset($params);'."\n";
		$p .= "?>\n";

		if (isset($attr['no_context'])) {
			return $p.$content;
		}

		return
		$p.
		'<?php if ($_ctx->pagination->f(0) > $_ctx->posts->count()) : ?>'.
		$content.
		'<?php endif; ?>';
	}
}

class multiBlogSearchPublic
{
	public static function widget(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		$value = isset($_GET['qm']) ? html::escapeHTML($_GET['qm']) : '';

		$title = (strlen($w->title) > 0) ? '<h2><label for="qm">'.html::escapeHTML($w->title).'</label></h2>' : '';

		$res = 
			'<div id="multisearch">'.
			$title.
			'<form action="'.$core->blog->url.'" method="get">'.
			'<fieldset>'.
			'<p><input type="text" size="10" maxlength="255" id="qm" name="qm" value="'.$value.'" /> '.
			'<input class="submit" type="submit" value="ok" /></p>'.
			'</fieldset>'.
			'</form>'.
			'</div>';

		return $res;
	}
}

?>