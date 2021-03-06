<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009, 2011 Moe (http://gniark.net/)
#
# Super Admin is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Super Admin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

dcPage::checkSuper();

# cookie and session for last visit timestamp
setcookie('superadmin_lastvisit',$_SERVER['REQUEST_TIME'],
	strtotime('+1 year'),'','',DC_ADMIN_SSL);

$_COOKIE['superadmin_lastvisit'] = $_SERVER['REQUEST_TIME'];

if (!isset($_SESSION['superadmin_lastvisit']))
{
	$_SESSION['superadmin_lastvisit'] =
		$_COOKIE['superadmin_lastvisit'];
}

# set default tab cookie
if (!empty($_GET['default_tab']))
{
	setcookie('superadmin_default_tab',$_GET['default_tab'],
		strtotime('+1 year'),'','',DC_ADMIN_SSL);
	
	http::redirect($p_url.'&file='.$_REQUEST['file']);
}

require_once(dirname(__FILE__).'/inc/lib.superAdmin.php');
require_once(dirname(__FILE__).'/inc/lib.pager.php');
require_once(dirname(__FILE__).'/php-xhtml-table/class.table.php');

$msg = (string)'';

$tab = 'comments';

if (!empty($_REQUEST['file']))
{
	switch ($_REQUEST['file'])
	{
		case 'comments' :
			require dirname(__FILE__).'/admin/comments.php';
			break;
		case 'comments_actions' :
			require dirname(__FILE__).'/admin/comments_actions.php';
			break;
		case 'services' :
			require dirname(__FILE__).'/admin/services.php';
			break;
		case 'posts' :
			require dirname(__FILE__).'/admin/posts.php';
			break;
		case 'posts_actions' :
			require dirname(__FILE__).'/admin/posts_actions.php';
			break;
		case 'cpmv_post' :
			require dirname(__FILE__).'/cpmv_post.php';
			break;
		case 'medias' :
			require dirname(__FILE__).'/medias.php';
			break;
		case 'settings' :
			require dirname(__FILE__).'/settings.php';
			break;
		default :
			require dirname(__FILE__).'/admin/posts.php';
			break;
	}
}
else
{
	# read default tab cookie
	if (isset($_COOKIE['superadmin_default_tab']))
	{
		switch ($_COOKIE['superadmin_default_tab'])
		{
			case 'posts' :
				require dirname(__FILE__).'/admin/posts.php';
				break;
			case 'comments' :
				require dirname(__FILE__).'/admin/comments.php';
				break;
			case 'cpmv_post' :
				require dirname(__FILE__).'/cpmv_post.php';
				break;
			case 'medias' :
				require dirname(__FILE__).'/medias.php';
				break;
			default :
				require dirname(__FILE__).'/admin/posts.php';
				break;
		}
	}
	else
	{
		require dirname(__FILE__).'/admin/posts.php';
	}
}

exit;
?>