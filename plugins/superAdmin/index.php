<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009 Moe (http://gniark.net/)
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

require_once(dirname(__FILE__).'/inc/lib.superAdmin.php');
require_once(dirname(__FILE__).'/inc/lib.pager.php');

$msg = (string)'';

$tab = 'comments';

if (!empty($_GET['comment_deleted']))
{
	$msg = __('Comment has been successfully deleted.');
}

if (!empty($_REQUEST['file']))
{
	switch ($_REQUEST['file'])
	{
		case 'comment' :
			require dirname(__FILE__).'/admin/comment.php';
			break;
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
		case 'post' :
			require dirname(__FILE__).'/admin/post.php';
			break;
		default :
			throw new Exception(__('Invalid file.'));
			break;
	}
}
else
{
	require dirname(__FILE__).'/admin/comments.php';
}

exit;
?>