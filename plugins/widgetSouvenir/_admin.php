<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Souvenir.
# Copyright 2008 Moe (http://gniark.net/)
#
# Souvenir is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Souvenir is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://txfx.net/code/wordpress/subscribe-to-comments/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$core->addBehavior('initWidgets',array('souvenirBehaviors','initWidgets'));
 
class souvenirBehaviors
{
	public static function initWidgets(&$w)
	{
		global $core;

		$w->create('souvenir',__('Souvenir'),array('publicSouvenir','show'));

		$w->souvenir->setting('title',__('Title:').' ('.__('optional').')',__('One year ago'),'text');

		$array_intervals = array();
		$array_intervals[__('1 month ago')] = 1;
		for ($i = 2;$i <= 11;$i++)
		{
			$array_intervals[$i.' '.__('months ago')] = $i;
		}
		$array_intervals[__('1 year ago')] = 12;
		
		$w->souvenir->setting('interval',__('Show a link to a post published:'),12,'combo',$array_intervals);

		$array_range = array();
		for ($i = 0;$i <= 31;$i++)
		{
			$array_range[$i] = $i;
		}
		$w->souvenir->setting('range',__('Maximum number of days before of after the date in the past:'),7,'combo',$array_range);

		$w->souvenir->setting('truncate',__('Number of characters of the post title to display (empty means no limit):'),null,'text');

		$w->souvenir->setting('date',__('Display date after post title (see PHP strftime function):').' ('.__('optional').')','('.$core->blog->settings->date_format.')','text');

		$w->souvenir->setting('home',__('Display on Home page'),true,'check');
	}
}
?>