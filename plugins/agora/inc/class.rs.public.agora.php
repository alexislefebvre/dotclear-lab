<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku ,Tomtom and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class rsExtMessagePublic extends rsExtMessage
{
    public static function getContent($rs,$absolute_urls=false)
    {
         # Not very nice hack but it does the job :)
         if (isset($GLOBALS['_ctx']) && $GLOBALS['_ctx']->short_feed_items === true) {
             $_ctx =& $GLOBALS['_ctx'];
             $c = parent::getContent($rs,$absolute_urls);
             $c = context::remove_html($c);
             $c = context::cut_string($c,350);
               
             $c =
             '<p>'.$c.'... '.
             '<em><a href="'.$rs->getURL().'">'.__('Read').'</em> '.
             html::escapeHTML($rs->post_title).'</a></p>';
               
             return $c;
          }
         
        if ($rs->core->blog->settings->system->use_smilies)
        {
			$c = parent::getContent($rs,$absolute_urls);
			//return self::smilies(parent::getContent($rs,$absolute_urls),$rs->core->blog);
			if (!isset($GLOBALS['__smilies'])) {
				$GLOBALS['__smilies'] = context::getSmilies($rs->core->blog);
			}
			return context::addSmilies($c);
        }
         
         return parent::getContent($rs,$absolute_urls);
    }
}
?>