<?php
/* BEGIN LICENSE BLOCK
This file is part of Contact, a plugin for Dotclear.

K-net
Pierre Van Glabeke

Licensed under the GPL version 2.0 license.
A copy of this license is available in LICENSE file or at
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
END LICENSE BLOCK */
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
