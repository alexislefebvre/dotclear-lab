<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of MyMetaWidget, a plugin for Dotclear.
# 
# Copyright (c) 2008 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }
 
require dirname(__FILE__).'/_widgets.php';

class publicMyMetaWidget
{

    public static function getContent(&$w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$titre = '';
        if ( $w->title != '' ) { $titre = '<h2>'.html::escapeHTML($w->title).'</h2>'."\n"; }
        
		$query = 'SELECT DISTINCT meta_type FROM '.$core->prefix.'meta WHERE meta_type != \'tag\';';
		$rs = $core->con->select($query);
		
		$res = '<div>'.$titre;
		
		$res .= '<ul>';
		while ($rs->fetch()) {
			
			$meta_type = $rs->f('meta_type');
			
			$res .= '<li class="mymeta-'.$meta_type.'">';
			$res .= '<span class="mymeta-item"><a href="'.
						$core->blog->url.'meta/'.$meta_type.'">'.$meta_type.
					'</a></span>';
			
			$query2 = 'SELECT DISTINCT meta_id FROM '.$core->prefix.'meta WHERE meta_type = \''.$meta_type.'\';';
			$rs2 = $core->con->select($query2);
			$res .= '<ul>';
			while ($rs2->fetch()) {
				$meta_id = $rs2->f('meta_id');
				$res .= '<li><a href="'.$core->blog->url.
							'meta/'.$meta_type.'/'.$meta_id.'">'.$meta_id.'</a></li>';
			}
			$res .= '</ul>';
			
			$res .= '</li>';
		}
		$res .= '</ul>';
		
		$res .= '</div>';
			
		return $res;
	}
}
?>
