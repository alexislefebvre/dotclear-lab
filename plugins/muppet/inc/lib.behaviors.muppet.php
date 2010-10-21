<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of muppet, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class toolsmuppet
{
	public static function typesToInclude($url,&$params)
	{
		global $core;
		$types = muppet::getPostTypes();
		
		if (!empty($types)) {
			$post_types = array();
			
			if (in_array($url,unserialize($core->blog->settings->muppet->muppet_urls_integration))) {
				foreach ($types as $k => $v) {
					if ($v['integration'] === true) {
						$post_types[] = $k;
					}
				}
				$params['post_type'] = $post_types;
				$params['post_type'][] = 'post';
			}
			elseif ($url == 'feed' || $url =='tag_feed') {
				foreach ($types as $k => $v) {
					if ($v['feed'] === true) {
						$post_types[] = $k;
					}
				}
				$params['post_type'] = $post_types;
				$params['post_type'][] = 'post';
			}
			elseif (preg_match('#_feed#',$url)) {
				// Feed on muppet list entries
				$found = substr($core->url->type, 0, -5);
				$params['post_type'][] = $found;
			}
			elseif (array_key_exists(substr($core->url->type, 0, -1),$types)) {
				// List of entries
				$found = substr($core->url->type, 0, -1);
				$params['post_type'][] = $found;
			}
		}
	}
}
?>
