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

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

$core->addBehavior('initWidgets',array('NewNavLinksBehaviors','initWidgets'));
 
class NewNavLinksBehaviors
{
	public static function initWidgets(&$w)
	{
		global $core;

		$w->create('NewNavLinks',__('New Navigation Links'),array('publicNewNavLinks','Show'));

		$w->NewNavLinks->setting('home',__('Home').': ('.__('optional').')',__('Home'),'text');

		$w->NewNavLinks->setting('homeonhome',__('Display link to Home page on Home page'),true,'check');

		$w->NewNavLinks->setting('archives',__('Archives').': ('.__('optional').')',__('Archives'),'text');

		$w->NewNavLinks->setting('archonarch',__('Display link to Archives on Archives page'),true,'check');

		(array)$tags_list = array('h2','h3','h4','p');
		(array)$tags = array();
		foreach ($tags_list as $tag)
		{
			$tags[html::escapeHTML('<'.$tag.'>')] = $tag;
		}
		$w->NewNavLinks->setting('tag',html::escapeHTML(__('Tag to use:')),'2','combo',$tags);

		$w->NewNavLinks->setting('homeonly',__('Home page only'),false,'check');

	}
}
?>