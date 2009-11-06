<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of editComment, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('publicBeforeDocument',array('editComment','addTplPath'));
$core->addBehavior('publicCommentAfterContent',array('editComment','addLinks'));
$core->addBehavior('publicHeadContent',array('editComment','addHeaderFiles'));

$core->tpl->addValue('EditSubmitURL',array('editCommentTpl','EditSubmitURL'));
$core->tpl->addValue('CommentReferer',array('editCommentTpl','CommentReferer'));

$core->tpl->addBlock('EditIf', array('editCommentTpl','EditIf'));

class editCommentUrl extends dcUrlHandlers
{
	public static function editComment($args)
	{
		global $core,$_ctx;
		
		preg_match('#(comment|submit)(/([0-9]+))?(/ajax)?#',$args,$matches);
		
		if (!$core->blog->settings->ec_enable) {
			self::p404();
		}
		
		if ($matches[1] === 'comment') {
			$id = $matches[3];
	
			$_ctx->comments = $core->blog->getComments(array('comment_id' => $id));
			
			self::serveDocument('editcomment.html');
		}
		elseif ($matches[1] === 'submit') {
			if (!isset($matches[4])) {
				editComment::update();
				http::redirect(rawurldecode($_POST['c_referer']));
			}
			else {
				try { editComment::update(); }
				catch (Exception $e) { echo $e->getMessage(); } 
			}
		}
		else {
			self::p404();
		}
	}
}

class editCommentTpl
{
	public static function EditSubmitURL($attr)
	{
		return '<? echo $core->blog->url.$core->url->getBase("edit")."/submit"; ?>';
	}
	
	public static function CommentReferer($attr)
	{
		return '<?php echo rawurlencode($_SERVER["HTTP_REFERER"]); ?>';
	}
	
	public static function EditIf($attr,$content)
	{
		$operator = isset($attr['operator']) ? $GLOBALS['core']->tpl->getOperator($attr['operator']) : '&&';
		
		if (isset($attr['has_comment'])) {
			$sign = (boolean) $attr['has_comment'] ? '!' : '';
			$if[] = $sign.'$_ctx->comments->isEmpty()';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
}

?>