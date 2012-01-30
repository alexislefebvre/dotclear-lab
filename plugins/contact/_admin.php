<?php
# ***** BEGIN LICENSE BLOCK *****
# This is Contact, a plugin for DotClear. 
# Copyright (c) 2005 k-net. All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$adminaccess = $core->blog->settings->get('contact_adminaccess');
$adminaccess = $adminaccess === null ? 0 : $adminaccess;

if ($core->auth->isSuperAdmin()
|| ($adminaccess == 1 && $core->auth->check('admin',$core->blog->id))
|| $adminaccess == 0) {
	$_menu['Plugins']->addItem(__('Contact'),'plugin.php?p=contact','index.php?pf=contact/icon.png',
		preg_match('/plugin.php\?p=contact(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));
}

$core->addBehavior('adminDashboardFavs',array('contactfavBehaviors','dashboardFavs'));

class contactfavBehaviors
{
    public static function dashboardFavs($core,$favs)
    {
        $favs['Contact'] = new ArrayObject(array(
            'Contact',
            __('Contact'),
            'plugin.php?p=contact',
            'index.php?pf=contact/icon.png',
            'index.php?pf=contact/icon-big.png',
            'usage,contentadmin',
            null,
            null));
    }
}
require dirname(__FILE__).'/_widgets.php';
?>
