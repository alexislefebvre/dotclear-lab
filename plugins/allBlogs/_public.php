<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of All Blogs List, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe aka amalgame
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }
 
require dirname(__FILE__).'/_widgets.php';

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');
 
$core->url->register('switchblog','switchblog','^switchblog$',array('switchurl','switchblog'));
 
class switchurl extends dcUrlHandlers
{
        public static function switchblog($args)
        {
                global $core;
				$url = $_POST['switchblog'];
				header('location:'.$url.'');
				
        }
}

class publicAllBlogs
{
	public static function allBlogs(&$w)
	{
		global $core;
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		if ($w->combo == '1') {
			$display = 'combo';
		}
		$excluded  = explode(",",$w->excluded);
		
		$res ='<div id="allblogs">';
		//liste des blogs
				
		$thisblog = $core->blog->id;
		
		$rs_blogs = $core->getBlogs(array('order'=>'LOWER(blog_name)','limit'=>20));
		$blogs = array();
		
		//liste des blogs
		if (isset($display) != 'combo') {
			$res .= ($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
			$res.= '<ul id=\'allblogs\'>';
			while ($rs_blogs->fetch()) {
				$blog = $core->getBlog($rs_blogs->blog_id);
				$k = $rs_blogs->blog_id;
				if($blog->blog_status==1 && !$w->$k && !in_array($blog->blog_id,$excluded)) {
					if ($blog->blog_id != $thisblog) {
						$res .= '<li><a href="'.$blog->blog_url.'" title="'.__('Visit blog:').' '.$blog->blog_name.'">'.$blog->blog_name.'</a></li>';
					} else {
						$res .= '<li><strong>'.$blog->blog_name.'</strong></li>';
					}
				}
			}
			$res.='</ul>';
		} else {
			$thisblogurl = $core->blog->url;			
			$res.= '<form action="'.$thisblogurl.'switchblog" method="post"><h2><label for="switchblog">'.html::escapeHTML($w->title).'</label></h2>'.
			'<p><select name="switchblog" id="switchblog">';
			while ($rs_blogs->fetch()) {
				$blog = $core->getBlog($rs_blogs->blog_id);
				$k = $rs_blogs->blog_id;
				if($blog->blog_status==1 && !$w->$k && !in_array($blog->blog_id,$excluded)) {
					if ($blog->blog_id != $thisblog) {
						$res .= '<option value="'.$blog->blog_url.'">'.$blog->blog_name.'</option>';
					} else {
						$res .= '<option value="'.$blog->blog_url.'" selected="selected">'.$blog->blog_name.'</option>';
					}
				}
			}
			$res.='</select></p><p><input type="submit" value="'.__('OK').'" /></p></form>';
			//
		}
		$res .= '</div>';
		return $res;
	}
}
?>