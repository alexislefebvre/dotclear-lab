<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of shortArchives, a plugin for Dotclear.
# 
# Copyright (c) 2009-10 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$core->addBehavior('adminDashboardFavs',array('shortArchivesfavBehaviors','dashboardFavs'));

class shortArchivesfavBehaviors
{
    public static function dashboardFavs($core,$favs)
    {
        $favs['shortArchives'] = new ArrayObject(array(
            'shortArchives',
            __('shortArchives'),
            'plugin.php?p=shortArchives',
            'index.php?pf=shortArchives/icon.png',
            'index.php?pf=shortArchives/icon-big.png',
            'usage,contentadmin',
            null,
            null));
    }
}

require dirname(__FILE__).'/_widgets.php';
?>
