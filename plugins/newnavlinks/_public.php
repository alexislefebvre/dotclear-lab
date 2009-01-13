<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of New Navigation Links.
# Copyright 2007 Moe (http://gniark.net/)
#
# New Navigation Links is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# New Navigation Links is distributed in the hope that it will be useful,
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

if (!defined('DC_RC_PATH')) {return;}

class publicNewNavLinks
{
	public static function Show(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		(array)$elements = array();
		if ((strlen($w->home) > 1) AND ((($core->url->type == 'default') AND ($w->homeonhome)) OR ($core->url->type != 'default'))) 
		{
			$elements[] = '<a href="'.$core->blog->url.'">'.html::escapeHTML($w->home).'</a>';
		}

		if ((strlen($w->archives) > 0) AND ((($core->url->type == 'archive') AND ($w->archonarch)) OR ($core->url->type != 'archive')))
		{
			$elements[] = '<a href="'.$core->blog->url.$core->url->getBase("archive").'">'.html::escapeHTML($w->archives).'</a>';
		}

		$str = implode('<span> - </span>',$elements);
		if (strlen($str) > 1)
		{
			$class = ($w->tag == 'p') ? ' class="text"' : '';
			return '<div id="newnav"><'.$w->tag.$class.'>'.$str.'</'.$w->tag.'></div>';
		}
	}
}
?>