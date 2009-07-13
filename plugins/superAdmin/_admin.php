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

$_menu['System']->addItem(__('Super Admin'),'plugin.php?p=superAdmin',
	'index.php?pf=superAdmin/icon.png',
	preg_match('/plugin.php\?p=superAdmin(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->isSuperAdmin());
	
# dashboard
if ($core->auth->isSuperAdmin())
{
	$core->addBehavior('adminDashboardIcons',
		array('superAdminAdmin','adminDashboardIcons'));
}

require_once(dirname(__FILE__).'/inc/lib.superAdmin.php');

class superAdminAdmin {
	public static function adminDashboardIcons(&$core, &$icons)
	{
		$spam_count =
			superAdmin::getComments(array('comment_status'=>-2),true)->f(0);
		
		$url = 'plugin.php?p=superAdmin&amp;file=comments';
		
		switch ($spam_count) {
			case 0 :
				$str = sprintf(__('%s spam comment'),$spam_count);
				$icon = 'trash-empty.png';
				break;
			case 1 :
				$str = sprintf(__('%s spam comment'),$spam_count);
				$url .= '&amp;status=-2';
				$icon = 'trash-full.png';
				break;
			default :
				$str = sprintf(__('%s spam comments'),$spam_count);
				$url .= '&amp;status=-2';
				$icon = 'trash-full.png';
				break;
		}
		
		if (isset($_SESSION['superadmin_lastvisit']))
		{
			$spam_count_last_visit = superAdmin::getComments(
				array('comment_status' => -2,
					'sql' => 'AND (comment_dt >= \''.
					dt::str('%Y-%m-%d %T',$_SESSION['superadmin_lastvisit']).'\')'
				)
				,true)->f(0);
			
			if ($spam_count_last_visit == 1)
			{
				$pattern = __('(including %d spam comment since your last visit)');
			}
			elseif ($spam_count_last_visit > 1)
			{
				$pattern = __('(including %d spam comment since your last visit)');
			}
			
			if (isset($pattern))
			{
				$str .= '</a><br /><a href="'.$url.'&last_visit=1">'.
					sprintf($pattern,$spam_count_last_visit).
					'</a>';
			}
		}
		
		$icons['superAdmin'] = array($str,$url,
			'index.php?pf=superAdmin/img/'.$icon);
	}
}
?>