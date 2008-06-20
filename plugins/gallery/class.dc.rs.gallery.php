<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 2 Gallery plugin.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class rsExtGallery 
{
        public static function getURL(&$rs)
        {
                return $rs->core->blog->url.$rs->core->url->getBase('gal').'/'.
                html::sanitizeURL($rs->post_url);
        }
}

class rsExtImage
{
        public static function getURL(&$rs)
        {
                return $rs->core->blog->url.$rs->core->url->getBase('galitem').'/'.
                html::sanitizeURL($rs->post_url);
        }
}

?>
