<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of entryCSS, a plugin for Dotclear.
# 
# Copyright (c) 2009 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('publicHeadContent', array('publicEntryCSS','publicHeadContent'));

class publicEntryCSS
{
	public static function publicHeadContent(&$core,&$_ctx)
	{
		if($core->url->type == "post" || $core->url->type == "pages") {
			$query = 'SELECT post_css FROM '.$GLOBALS['core']->prefix.'post WHERE post_id = '.$_ctx->posts->post_id.';';
			$rs = $core->con->select($query);
			$value = $rs->f('post_css');
			echo '<style type="text/css">'.$value.'</style>';
		}
	}
}

?>
