<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Contribute, a plugin for Dotclear 2
# Copyright (C) 2008,2009,2010 Moe (http://gniark.net/)
#
# Contribute is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Contribute is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons :
# <http://www.famfamfam.com/lab/icons/silk/>
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('initWidgets',array('contributeWidget','initWidgets'));

/**
@ingroup Contribute
@brief Widget
*/
class contributeWidget
{
	/**
	widget
	@param	w	<b>object</b>	Widget
	*/
	public static function initWidgets($w)
	{
		$w->create('contribute',__('Contribute'),array('contributeWidget','show'));

		$w->contribute->setting('title',__('Title:').' ('.__('optional').')',
			__('Contribute'),'text');
		
		$w->contribute->setting('text',__('Text:').' ('.__('optional').')',
			__('Write a post for this blog'),'text');
		
		$w->contribute->setting('homeonly',__('Home page only'),false,'check');
	}
	
	/**
	show widget
	@param	w	<b>object</b>	Widget
	@return	<b>string</b> XHTML
	*/
	public static function show(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		# output
		$header = (strlen($w->title) > 0)
			? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;
		$text = (strlen($w->text) > 0)
			? '<p><a href="'.$core->blog->url.$core->url->getBase('contribute').
				'">'.html::escapeHTML($w->text).'</a></p>' : null;

		return '<div class="contribute">'.$header.$text.'</div>';
	}
}
?>