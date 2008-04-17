<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Gallery plugin.
# Copyright (c) 2008 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Gallery plugin for DC2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

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
