<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of lastImages, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Philippe Amalgame and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('publicHeadContent',array('lastImagesPublic','publicHeadContent'));
require dirname(__FILE__).'/_widget.php';
class lastImagesPublic
{
	public static function publicHeadContent(&$core)
	{
		
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo
		'<style type="text/css">'."\n".
		'@import url('.$url.'/css/image-box.css);'."\n".
		"</style>\n";
	}
}
class publicLastImages
{
	public static function lastImages(&$w)
	{
		global $core;
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		$params['limit'] = '0';
		$params['no_content'] = false;
		
		if ($w->category != '') {
		$category  = $w->category;
		$params['cat_url'] = explode(",", $category);
		}
		if ($w->selected == '1') {
			$params['post_selected'] = '1';
		}
		
		$rsp = $core->blog->getPosts($params);
		if ($rsp->isEmpty()) {
			return;
		}

		$size = $w->size;

		$i = '1';
		$ret = '<div class="images">';
		$ret .= ($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
		$ret .= '<div class="wrapper">';
		$nb_billets = $rsp->count();
		$randIDs = array();
		for ($j = 0; $j < $nb_billets; $j++) {

			if ($w->random != '0') {
				
				$id = mt_rand(0, $nb_billets - 1);
				
				$key_attempts = 0;
				while (array_key_exists($id, $randIDs) && $key_attempts < 20) {
					$id = mt_rand(0, $nb_billets - 1);
					$key_attempts++;
				}
				$randIDs[$id] = TRUE;
				$rsp->index($id);
			} else {
				$rsp->index($j);
			}

			// URL du billet
			$full_url = $rsp->getURL();
			
			// Construction de la vignette
			$img_src_regexp = '/<img .*?src="(.+?)"/i';
			
			if (($i <= abs((integer) $w->limit) || abs((integer) $w->limit) == '0') &&
				(preg_match($img_src_regexp,$rsp->post_excerpt_xhtml,$rs) || preg_match($img_src_regexp,$rsp->post_content_xhtml,$rs))) {
				$img_thumb = '<img ';
				$img_path = $rs[1];
				//découpage de l'url de l'image
				
				$img_path = parse_url($img_path, PHP_URL_PATH); //si url absolue ?
					
				$path_string = explode("/", $img_path);
				$img_name = array_pop ($path_string);
				$path_string = implode("/", $path_string);
				if (substr($img_name,0,1) == '.') {
					//c'est une miniature
					$replace = array ('_t.jpg','_sq.jpg','_s.jpg','_m.jpg');
					$img_short_name = str_replace($replace,'',$img_name);
					if ($size == 'o') {
						$img_thumb_name = $path_string.'/'.ltrim($img_short_name,'.').'.jpg';
					} else {
						$img_thumb_name = $path_string.'/'.$img_short_name.'_'.$size.'.jpg';
					}
				} else {
					//c'est l'originale
					$replace = array ('.jpg','.png','.gif');
					$img_short_name = str_replace($replace,'',$img_name);
					
					if ($size == 'o') {
						$img_thumb_name = $path_string.'/'.$img_name;
					} else {
						$img_thumb_name = $path_string.'/.'.$img_short_name.'_'.$size.'.jpg';
					}
				}
				// Test si la miniature demandée existe
				
				$public_string = $core->blog->settings->public_url;
				$file_name = $core->blog->settings->public_path.str_replace($public_string,'',$img_thumb_name);

				if (file_exists($file_name)) {
				
					$img_thumb .= 'src="'.$img_thumb_name.'" alt="'.$rsp->post_title.'"';
					$img_thumb .= ' />';
					
					// Mise en forme html
					$ret .= '<div class="img-box">'."\n";				
					$ret .= '    <div class="img-thumbnail"><a title="'.$rsp->post_title.'" href="'.$full_url.'">'.$img_thumb.'</a></div>'."\n";
					$ret .= '</div>'."\n";
					
					$i++;
				}		
			}
		}
		$ret .= '</div>'."\n";
		$ret .= '</div>'."\n";
		return $ret;
	}
}
?>