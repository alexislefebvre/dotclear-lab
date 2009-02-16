<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Noviny, a Dotclear 2 theme.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

# Ajax search URL
$core->url->register('ajaxsearch','ajaxsearch','^ajaxsearch(?:(?:/)(.*))?$',array('urlsNoviny','ajaxsearch'));

class urlsNoviny
{
	public static function ajaxsearch($args)
	{
		global $core;
		$res = '';

		try
		{
			if (!$args) {
				throw new Exception;
			}

			$q = rawurldecode($args);
			$rs = $core->blog->getPosts(array(
				'search' => $q,
				'limit' => 5
			));

			if ($rs->isEmpty()) {
				throw new Exception;
			}

			$res = '<ul>';
			while ($rs->fetch())
			{
				$res .= '<li><a href="'.$rs->getURL().'">'.html::escapeHTML($rs->post_title).'</a><br />';
/*

				$img_src_regexp = '/<img .*?src="(.+?)".*>/i';
				if (preg_match($img_src_regexp,$rs->post_excerpt_xhtml,$rs2)) {

					 	$img_thumb = '<img ';
						$img_path = $rs2[1];
						//découpage de l'url de l'image
						$path_string = explode("/", $img_path);
						$img_name = array_pop ($path_string);
						$path_string = implode("/", $path_string);
						if (substr($img_name,0,1) == '.') {
							//c'est une miniature
							$replace = array ('_t.jpg','_sq.jpg','_s.jpg','_m.jpg');
							$img_short_name = str_replace($replace,'',$img_name);
							$img_thumb_name = $path_string.'/'.$img_short_name.'_sq.jpg';
						} else {
							//c'est l'originale
							$replace = array ('.jpg','.png','.gif');
							$img_short_name = str_replace($replace,'',$img_name);
							$img_thumb_name = $path_string.'/.'.$img_short_name.'_sq.jpg';
						}

						// Test si l'image existe
						$path_string = explode("/", $core->blog->settings->public_path);
						$public_name = array_pop ($path_string);
						$path_string = implode("/", $path_string);
						$file_name = $path_string.$img_thumb_name;
						if (!file_exists($file_name)) {

							//$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
							//$img_thumb_name = $url.'/images/nothumbnail.jpg';
							//$img_thumb .= 'src="'.$img_thumb_name.'" alt="no thumbnail"';
							$img_thumb .= 'src="'.$img_thumb_name.'" alt="'.$rsp->post_title.'"';
						} else {
							$img_thumb .= 'src="'.$img_thumb_name.'" alt="'.$rsp->post_title.'"';
							$res .= "OK!!";
						}

						// Construction de l'attribut src
						$img_thumb .= ' style="float: left; margin: 5px;" />';


						// Remplacement de l'image par la miniature
						$img_big = $rs2[0];
						$excerpt = $rs->post_excerpt_xhtml;
						$excerpt_sq = str_replace($rs2,'',$excerpt);


						$res .= $excerpt_sq;

				}

*/

				$res .= '</li>';
			}
			$res .= '</ul>';
		}
		catch (Exception $e) {}

		header('Content-Type: text/plain; charset=UTF-8');
		echo $res;
	}
}
?>
