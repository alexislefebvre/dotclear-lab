<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of hum, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

$core->blog->settings->addNamespace('hum');

if ($core->blog->settings->hum->active) {
	# Default value of new comments
	$core->addBehavior('coreBeforeCommentCreate',array('publicHum','coreBeforeCommentCreate'));
	# CSS and JS
	$core->addBehavior('publicHeadContent',array('publicHum','publicHeadContent'));
	# Add comment_selected field to Comments tag
	$core->addBehavior('templateBeforeBlock',array('publicHum','templateBeforeBlock'));
	# Tags
	$core->tpl->addBlock('CommentSelectedIf',array('tplHum','CommentSelectedIf'));
	$core->tpl->addValue('CommentIfSelected',array('tplHum','CommentIfSelected'));
	$core->tpl->addValue('CommentIfNotSelected',array('tplHum','CommentIfNotSelected'));
}

class tplHum
{
	public function CommentSelectedIf($attr,$content)
	{
		$if = array();
		
		if (isset($attr['is_selected'])) {
			$sign = (boolean) $attr['is_selected'] ? '' : '!';
			$if[] = $sign.'$_ctx->comments->comment_selected';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' && ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	public function CommentIfSelected($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'selected';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->comments->comment_selected) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	public function CommentIfNotSelected($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'unselected';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (!$_ctx->comments->comment_selected) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
}

class publicHum
{
	public static function publicHeadContent($core)
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
			if (!preg_match('#^[a-zA-Z0-9]{2,}$#',$title_tag)) $title_tag = 'dt';
			$content_tag = $core->blog->settings->hum->content_tag;
			if (!preg_match('#^[a-zA-Z0-9]{2,}$#',$content_tag)) $content_tag = 'dd';
			
			echo 
			"\n<!-- JS for plugin hum --> \n".
			"<script type=\"text/javascript\"> \n".
			"//<![CDATA[\n".
			"\$(function() { \n".
			" \$('#comments ".$title_tag.".unselected').each(function(){ \n".
			"  var title=$(this); \n".
			"  \$(this).next('".$content_tag."').hide(); \n".
			"  \$(this).append(".
			 
			 "\$('<a href=\"#\" title=\"".html::escapeJS(__('Click to show this commnet')).
			 "\" class=\"read-it\">".html::escapeJS(__('Read this comment'))."</a>'".
			 ").click(function(){\$(title).next('".$content_tag."').toggle();\$(this).parent().children('.read-it').remove();return false;})".
			 ").append(".
			 
			 "$('<a href=\"#\" title=\"".html::escapeJS(__('Show all comments')).
			 "\" class=\"read-it\">".html::escapeJS(__('All'))."</a>'".
			 ").click(function(){\$('#comments ".$content_tag."').show();\$('#comments ".$title_tag.".unselected .read-it').remove();return false;})".
			 
			 "); \n".
			" }); \n".
			"}); \n".
			"//]]>\n".
			"</script>\n";
		}
	}
	
	public static function templateBeforeBlock($core,$tag,$attr)
	{
		if ($tag != 'Comments') {return;}
		
		$res = "\$params['columns'][] = 'comment_selected'; ";
		
		if (isset($attr['selected'])) {
			$selected = (boolean) $attr['selected'] ? '0' : '1';
			$res .= "@\$params['sql'] .= 'AND comment_selected = ".((integer) $selected)."'; ";
		}
		return '<?php '.$res.' ?>';
	}
	
	public static function coreBeforeCommentCreate($blog,$cur)
	{
		if (null === $cur->comment_selected) {
			$cur->comment_selected = (integer) $blog->settings->hum->comment_selected;
		}
	}
}
?>