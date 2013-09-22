<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of New Navigation Links, a plugin for Dotclear 2.
# Copyright (c) 2007,2010 Moe (http://gniark.net/)
#
# New Navigation Links is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# New Navigation Links is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.
#
# Image is from Silk Icons :
#  <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {exit;}

$core->addBehavior('initWidgets',array('NewNavLinksBehaviors','initWidgets'));
 
class NewNavLinksBehaviors
{
	public static function initWidgets($w)
	{
		global $core;

		$w->create('NewNavLinks',__('New Navigation Links'),
			array('NewNavLinksBehaviors','Show'));

		$w->NewNavLinks->setting('home',__('Home').': ('.__('optional').')',
			__('Home'),'text');

		$w->NewNavLinks->setting('homeonhome',
			__('Display link to Home page on Home page'),true,'check');

		$w->NewNavLinks->setting('archives',__('Archives').': ('.
			__('optional').')',__('Archives'),'text');

		$w->NewNavLinks->setting('archonarch',
			__('Display link to Archives on Archives page'),true,'check');

		$tags_list = array('h2','h3','h4','p');
		$tags = array();
		
		foreach ($tags_list as $tag)
		{
			$tags[html::escapeHTML('<'.$tag.'>')] = $tag;
		}
		
		$w->NewNavLinks->setting('tag',html::escapeHTML(__('Tag to use:')),
			'2','combo',$tags);
			
		$w->NewNavLinks->setting('homeonly',__('Display on:'),0,'combo',
			array(
				__('All pages') => 0,
				__('Home page only') => 1,
				__('Except on home page') => 2
				)
		);
    $w->NewNavLinks->setting('content_only',__('Content only'),0,'check');
    $w->NewNavLinks->setting('class',__('CSS class:'),'');
	}
	
	public static function Show($w)
	{
		global $core;
		
		if (($w->homeonly == 1 && $core->url->type != 'default') ||
		($w->homeonly == 2 && $core->url->type == 'default')) {
		return;
		}

		$elements = array();
		if ((strlen($w->home) > 1) AND ((($core->url->type == 'default')
			AND ($w->homeonhome)) OR ($core->url->type != 'default'))) 
		{
			$elements[] = '<a href="'.$core->blog->url.'">'.html::escapeHTML($w->home).'</a>';
		}

		if ((strlen($w->archives) > 0) AND ((($core->url->type == 'archive')
			AND ($w->archonarch)) OR ($core->url->type != 'archive')))
		{
			$elements[] = '<a href="'.$core->blog->url.
				$core->url->getBase("archive").'">'.
				html::escapeHTML($w->archives).'</a>';
		}

		
		if (count($elements) > 0)
		{
			$str = implode('<span> - </span>',$elements);
			$class = ($w->tag == 'p') ? ' class="text"' : '';
			//return '<div id="newnav">
      return 		$res = ($w->content_only ? '' : '<div class="newnav'.($w->class ? ' '.html::escapeHTML($w->class) : '').'">').
      '<'.$w->tag.$class.'>'.$str.'</'.$w->tag.'>'.
      ($w->content_only ? '' : '</div>');
      //'</div>';
		}
	}
}
?>
