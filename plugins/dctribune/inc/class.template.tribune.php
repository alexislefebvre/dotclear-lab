<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dctribune, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku  and contributors
# Many thanks to Pep, Tomtom and JcDenis
# Originally from Antoine Libert
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class tribuneTemplate
{
	public static function Tribune($attr,$content)
	{
		global $core, $_ctx;
		// Lecture config du blog par défaut ...
		$p = "\$params = array();\n";

		if (isset($attr['limit'])) {
			$p .= "\$params['limit'] = '".abs($attr['limit'])."';\n";
		}
		else {$p .= "\$params['limit'] = '".abs($core->blog->settings->tribune_limit + 1)."';\n";}
		
		if (isset($attr['state'])) {
			$p .= "\$params['tribune_state'] = '".(integer) $attr['state']."';\n";
		}
		else {$p .= "\$params['tribune_state'] = 1;\n";}
		
		$sortby = 'tribune_dt';
		$order = 'desc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'author' : $sortby = 'tribune_nick'; break;
				case 'date' : $sortby = 'tribune_dt'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
	
		$p .= "\$params['order'] = '".$sortby." ".$order."';\n";
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->tribune = $core->tribune->getMsgs($params); unset($params);'."\n";
		$res .= "?>\n";
		
		if (!$core->blog->settings->tribune_display_order)
		{
			$res .= '<?php while ($_ctx->tribune->fetch()) : ?>'.$content.'<?php endwhile; ';
		}
		else
		{
			$res .= '<?php $_ctx->tribune->moveEnd(); do { $_ctx->tribune->movePrev(); ?>'.$content.'<?php ;  }while (!$_ctx->tribune->isStart()); ';
		}
		
		$res .= '$_ctx->tribune = null; $_ctx->post_params = null; ?>';
		
		return $res;
		
	}
	
	public static function Author($attr)
	{
		global $core, $_ctx;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->tribune->tribune_nick').'; ?>';
	}
	
	public static function Message($attr)
	{
		global $core, $_ctx;
		$f = $core->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->tribune->tribune_msg').'; ?>';
	}
	
	public static function Date($attr)
	{
		global $core, $_ctx;
		$format = $core->blog->settings->date_format.', '.$core->blog->settings->time_format;
		
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$f = $core->tpl->getFilters($attr);
	         
		return '<?php echo '.sprintf($f,"dt::dt2str('".$format."',\$_ctx->tribune->tribune_dt)").'; ?>';
	}
}
?>