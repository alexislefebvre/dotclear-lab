<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of hum, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {

	return null;
}

$core->blog->settings->addNamespace('hum');

if ($core->blog->settings->hum->active) {

	$core->addBehavior(
		'coreBeforeCommentCreate',
		array('publicHum', 'coreBeforeCommentCreate')
	);
	$core->addBehavior(
		'publicHeadContent',
		array('publicHum', 'publicHeadContent')
	);
	$core->addBehavior(
		'templateBeforeBlock',
		array('publicHum', 'templateBeforeBlock')
	);

	$core->tpl->addBlock(
		'CommentSelectedIf',
		array('tplHum', 'CommentSelectedIf')
	);
	$core->tpl->addValue(
		'CommentIfSelected',
		array('tplHum', 'CommentIfSelected')
	);
	$core->tpl->addValue(
		'CommentIfNotSelected',
		array('tplHum', 'CommentIfNotSelected')
	);
}
else {
	$core->tpl->addBlock(
		'CommentSelectedIf',
		array('tplHum', 'disabled')
	);
	$core->tpl->addValue(
		'CommentIfSelected',
		array('tplHum', 'disabled')
	);
	$core->tpl->addValue(
		'CommentIfNotSelected',
		array('tplHum', 'disabled')
	);
}

/**
 * @ingroup DC_PLUGIN_HUM
 * @brief Plublic methods for JS, CSS and sql queries.
 * @since 2.6
 */
class publicHum
{
	/**
	 * Add hum JS and CSS to theme header
	 * 
	 * @param  dcCore $core dcCore instance
	 */
	public static function publicHeadContent(dcCore $core)
	{		
		$css_extra = $core->blog->settings->hum->css_extra;
		if (!empty($css_extra)) {
			echo 
			"\n<!-- style for plugin hum --> \n".
			'<style type="text/css">'."\n".
			html::escapeHTML($css_extra)."\n".
			"</style>\n";
		}

		if ($core->blog->settings->hum->jquery_hide){
			
			$title_tag = $core->blog->settings->hum->title_tag;
			if (!preg_match('#^[a-zA-Z0-9]{2,}$#', $title_tag)) {
				$title_tag = 'dt';
			}

			$content_tag = $core->blog->settings->hum->content_tag;
			if (!preg_match('#^[a-zA-Z0-9]{2,}$#', $content_tag)) {
				$content_tag = 'dd';
			}

			echo 
			"\n<!-- JS for plugin hum --> \n".
			"<script type=\"text/javascript\"> \n".
			"//<![CDATA[\n".
			"\$(function() { \n".
			" \$('#comments ".$title_tag.".unselected').each(function(){ \n".
			"  var title=$(this); \n".
			"  \$(this).next('".$content_tag."').hide(); \n".
			"  \$(this).append(". 
				"\$('<a>').attr('href','#').attr('title','".html::escapeJS(__('Click to show this commnet'))."').attr('class','read-it').text('".html::escapeJS(__('Read this comment'))."').click(function(){\$(title).next('".$content_tag."').toggle();\$(this).parent().children('.read-it').remove();return false;})".
			  ").append(".
				"$('<a>').attr('href','#').attr('title','".html::escapeJS(__('Show all comments'))."').attr('class','read-it').text('".html::escapeJS(__('All'))."').click(function(){\$('#comments ".$content_tag."').show();\$('#comments ".$title_tag.".unselected .read-it').remove();return false;})".
			  "); \n".
			" }); \n".
			"}); \n".
			"//]]>\n".
			"</script>\n";
		}
	}

	/**
	 * Add column to sql queries
	 * 
	 * @param  dcCore $core dcCore instance
	 * @param  string $tag  Template block name
	 * @param  array  $attr Tempalte block attributes
	 * @return string       HTML PHP part to add query params
	 */
	public static function templateBeforeBlock(dcCore $core, $tag, $attr)
	{
		if ($tag != 'Comments') {

			return null;
		}

		$res = "\$params['columns'][] = 'comment_selected'; ";

		if (isset($attr['selected'])) {
			$selected = (boolean) $attr['selected'] ? '0' : '1';
			$res .= "@\$params['sql'] .= 'AND comment_selected = ".((integer) $selected)."'; ";
		}

		return '<?php '.$res.' ?>';
	}

	/**
	 * Add default value to cursor
	 * 
	 * @param  dcBlog $blog dcCore instance
	 * @param  cursor $cur  cursor
	 */
	public static function coreBeforeCommentCreate(dcBlog $blog, cursor $cur)
	{
		if (null === $cur->comment_selected) {
			$cur->comment_selected = (integer) $blog->settings->hum->comment_selected;
		}
	}
}

/**
 * @ingroup DC_PLUGIN_HUM
 * @brief Public methods for tempate tag and block.
 * @since 2.6
 */
class tplHum
{
	/**
	 * Template block to add comment selection condition
	 * 
	 * @param array  $attr    Template block attributes
	 * @param string $content Tempalde block content
	 */
	public static function CommentSelectedIf($attr, $content)
	{
		$if = array();
		
		if (isset($attr['is_selected'])) {
			$sign = (boolean) $attr['is_selected'] ? '' : '!';
			$if[] = $sign.'$_ctx->comments->comment_selected';
		}

		return empty($if) ?
			$content :
			'<?php if('.implode(' && ',$if).') : ?>'.$content.'<?php endif; ?>';
	}

	/**
	 * Template value to add something if comment is selected
	 * 
	 * @param  array $attr Template value attributes
	 * @return string      Something
	 */
	public static function CommentIfSelected($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'selected';
		$ret = html::escapeHTML($ret);

		return
		'<?php if ($_ctx->comments->comment_selected) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	/**
	 * Template value to add something if comment is not selected
	 * 
	 * @param  array $attr Template value attributes
	 * @return string      Something
	 */
	public static function CommentIfNotSelected($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'unselected';
		$ret = html::escapeHTML($ret);

		return
		'<?php if (!$_ctx->comments->comment_selected) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}

	/**
	 * Disable hum template blck and value
	 * 
	 * @param  array  $attr Template value attributes
	 * @return string       Nothing
	 */
	public static function disabled($attr, $content='')
	{
		return '';
	}
}
