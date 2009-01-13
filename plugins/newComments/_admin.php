<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of New comments.
# Copyright 2007 Moe (http://gniark.net/)
#
# New comments is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# New comments is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Image is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

	setcookie('lastvisit',$_SERVER['REQUEST_TIME'],
		($_SERVER['REQUEST_TIME']+3600*24*30),DC_ADMIN_URL);
	$_COOKIE['lastvisit'] = $_SERVER['REQUEST_TIME'];
	if (!isset($_SESSION['lastvisit'])) {$_SESSION['lastvisit'] = $_COOKIE['lastvisit'];}

	class newComments
	{
	
		public static function map($var)
		{
			if (strpos($var,'comments.php') !== false)
			{
				global $core;

				//echo($_SESSION['lastvisit']);
				//$dt = $_SERVER['REQUEST_TIME'];
				//if (isset($_SESSION['lastvisit'])) {$dt = $_SESSION['lastvisit'];}
				$dt = $_SESSION['lastvisit'];
				$params = array('sql' => 'AND (comment_dt >= \''.
				dt::str('%Y-%m-%d %T',$dt,$core->blog->settings->blog_timezone).'\')');
				$comments = $core->blog->getComments($params,true)->f('0');

				$class = '';
				if ($comments > 0) {$class = 'active';}
				$str = ' <span id="newComments"class="'.$class.'"><span class="'.$class.'">'.
				$comments.'</span></span>';
				return(str_replace('</li>',$str.'</li>',$var));
			}
			else
			{
				return($var);
			}
		}
		
		public static function adminPageHTMLHead()
		{
			global $_menu;
			# items of Plugins :
			$_menu['Blog']->items = array_map(array('self','map'),$_menu['Blog']->items);
			echo('<link rel="stylesheet" type="text/css" '.
			'href="index.php?pf=newComments/style.css" media="screen" />');
		}

	}

	$core->addBehavior('adminPageHTMLHead',array('newComments','adminPageHTMLHead'));

?>