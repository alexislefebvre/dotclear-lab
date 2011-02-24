<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Empreinte, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class tplEmpreinte
{
	public static function CommentIfUserAgent($attr,$content)
	{
		return
		'<?php $c_info = @publicEmpreinte::$c_info[$_ctx->comments->comment_id]; '.
		'if (!( empty($c_info["browser"]) || empty($c_info["system"]) )) : ?>'.
		$content.'<?php endif; unset($c_info); ?>';
	}
	
	public static function CommentCheckNoEmpreinte()
	{
		return
		'<?php if(!empty($_ctx->comment_preview[\'no_empreinte\']) '.
		'|| !empty($_POST[\'no_empreinte\'])) { echo \' checked="checked"\'; } ?>';
	}
	
	public static function CommentBrowser($attr)
	{
		$lcase = (integer) (boolean) @$attr['lowercase'];
		return '<?php echo $_ctx->comments->getBrowser('.$lcase.'); ?>';
	}
	
	public static function CommentSystem($attr)
	{
		$lcase = (integer) (boolean) @$attr['lowercase'];
		return '<?php echo $_ctx->comments->getSystem('.$lcase.'); ?>';
	}
	
	public static function CommentBrowserImg()
	{
		return self::PluginFileURL().'empreinte/icons/'.self::CommentBrowser(array('lowercase'=>1)).'.png';
	}
	
	public static function CommentSystemImg()
	{
		return self::PluginFileURL().'empreinte/icons/'.self::CommentSystem(array('lowercase'=>1)).'.png';
	}
	
	public static function PluginFileURL()
	{
		return $GLOBALS['core']->blog->getQmarkURL().'pf=';
	}
}
?>