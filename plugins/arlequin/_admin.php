<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Arlequin', a plugin for Dotclear 2                *
 *                                                             *
 *  Copyright (c) 2007,2015                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Arlequin' (see COPYING.txt);           *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Blog']->addItem(__('Theme switcher'),'plugin.php?p=arlequin',
	'index.php?pf=arlequin/icon.png',
	preg_match('/plugin.php\?p=arlequin(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('contentadmin',$core->blog->id));

require dirname(__FILE__).'/_widgets.php';

$core->addBehavior('adminDashboardFavorites','arlequinDashboardFavorites');

function arlequinDashboardFavorites($core,$favs)
{
	$favs->register('arlequin', array(
		'title' => __('Theme switcher'),
		'url' => 'plugin.php?p=arlequin',
		'small-icon' => 'index.php?pf=arlequin/icon.png',
		'large-icon' => 'index.php?pf=arlequin/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}