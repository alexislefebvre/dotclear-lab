<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Carnaval a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Me and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

# On surchage les fonctions template

if ($core->blog->settings->carnaval_active){
	$core->tpl->addValue('CommentIfMe',array('tplCarnaval','CommentIfMe'));
	$core->tpl->addValue('PingIfOdd',array('tplCarnaval','PingIfOdd'));

	if ($core->blog->settings->carnaval_colors){
		$core->addBehavior('publicHeadContent',array('tplCarnaval','publicHeadContent'));
	}
}

class tplCarnaval
{
	public static function CommentIfMe($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'me';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->comments->isMe()) { '.
		"echo '".addslashes($ret)."'; } ".
		"echo tplCarnaval::getCommentClass(); ?>";
	}
	
	public static function PingIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->pings->index()+1)%2) { '.
		"echo '".addslashes($ret)."'; } ".
		"echo tplCarnaval::getPingClass(); ?>";
	}
	
	public static function getCommentClass()
	{
		global $core, $_ctx;
		$carnaval = new dcCarnaval ($core->blog);
		$classe_perso = $carnaval->getCommentClass($_ctx->comments->getEmail(false));
		return html::escapeHTML($classe_perso);
	}
	
	public static function getPingClass()
	{
		global $core, $_ctx;
		$carnaval = new dcCarnaval ($core->blog);
		$classe_perso = $carnaval->getPingClass($_ctx->pings->getAuthorURL());
		return html::escapeHTML($classe_perso);
	}
	
	
	public static function publicHeadContent()
	{
		echo '<style type="text/css">'."\n".self::carnavalStyleHelper()."\n</style>\n";
	}
	
	public static function carnavalStyleHelper()
	{
		global $core;
	
		$carnaval = new dcCarnaval ($core->blog);
		$cval = $carnaval->getClasses();
		$css = array();
		while ($cval->fetch())
			{
				$res = '';
				$cl_class = $cval->comment_class;
				$cl_txt = $cval->comment_text_color;
				$cl_backg = $cval->comment_background_color;
				self::prop($css,'#comments dd.'.$cl_class,'color',$cl_txt);
				self::prop($css,'#comments dd.'.$cl_class,'background-color',$cl_backg);
				if ($core->blog->settings->theme == 'default') {
					self::backgroundImg($css,'#comments dt.'.$cl_class, $cl_backg,$cl_class.'-comment-t.png');
					self::backgroundImg($css,'#comments dd.'.$cl_class,$cl_backg,$cl_class.'-comment-b.png');
				}
				foreach ($css as $selector => $values)
				{
					$res .= $selector." {\n";
					foreach ($values as $k => $v) {
						$res .= $k.':'.$v.";\n";
					}
					$res .= "}\n";
				}
			}
			return $res;
	}

	protected static function prop(&$css,$selector,$prop,$value)
	{
		if ($value) {
			$css[$selector][$prop] = $value;
		}
	}
	
	protected static function backgroundImg(&$css,$selector,$value,$image)
	{
		$file = carnavalConfig::imagesPath().'/'.$image;
		if ($value && file_exists($file)){
			$css[$selector]['background-image'] = 'url('.carnavalConfig::imagesURL().'/'.$image.')';
		}
	}
}
?>
