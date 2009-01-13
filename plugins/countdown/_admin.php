<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of CountDown.
# Copyright 2007 Moe (http://gniark.net/)
#
# CountDown is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# CountDown is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('initWidgets',array('CountDownBehaviors','initWidgets'));
 
class CountDownBehaviors
{
	public static function initWidgets(&$w)
	{
		# set timezone
		global $core;
		$tz = $core->blog->settings->blog_timezone;

		$w->create('CountDown',__('CountDown'),array('publicCountDown','Show'));

		$w->CountDown->setting('title',__('Title:'),__('CountDown'),'text');

		$w->CountDown->setting('text_before',__('Text displayed if the date is in the future:'),__('In'),'text');

		$w->CountDown->setting('text_after',__('Text displayed if the date is in the past:'),__('For'),'text');

		# create arrays for year, month, day, hour, minute and second
		$array_year = $array_month = $array_day = $array_hour = $array_minute = $array_number_of_times = array();
		for ($i = 1902;$i <= 2037;$i++)
		{
			$array_year[$i] = $i;
		}
		for ($i = 1;$i <= 12;$i++)
		{
			$i = str_repeat('0',(2-strlen($i))).$i;
			$array_month[ucfirst(__(strftime('%B', mktime(0, 0, 0, $i, 1, 1970)))).' ('.$i.')'] = $i;
		}
		for ($i = 1;$i <= 31;$i++)
		{
			$i = str_repeat('0',(2-strlen($i))).$i;
			$array_day[$i] = $i;
		}
		for ($i = 0;$i <= 23;$i++)
		{
			$i = str_repeat('0',(2-strlen($i))).$i;
			$array_hour[$i] = $i;
		}
		for ($i = 0;$i <= 60;$i++)
		{
			$i = str_repeat('0',(2-strlen($i))).$i;
			$array_minute[$i] = $i;
		}
		for ($i = 1;$i <= 5;$i++)
		{
			$array_number_of_times[$i] = $i;
		}
		$array_number_of_times['6 ('.__('all').')'] = 6;
		# /create arrays

		$w->CountDown->setting('year',ucfirst(__('year')).':',dt::str('%Y',null,$tz),'combo',$array_year);
		$w->CountDown->setting('month',ucfirst(__('month')).':',dt::str('%m',null,$tz),'combo',$array_month);
		$w->CountDown->setting('day',ucfirst(__('day')).':',dt::str('%d',null,$tz),'combo',$array_day);
		$w->CountDown->setting('hour',ucfirst(__('hour')).':',dt::str('%H',null,$tz),'combo',$array_hour);
		$w->CountDown->setting('minute',ucfirst(__('minute')).':',dt::str('%M',null,$tz),'combo',$array_minute);
		$w->CountDown->setting('second',ucfirst(__('second')).':',dt::str('%S',null,$tz),'combo',$array_minute);

		$w->CountDown->setting('number_of_times',__('Number of values to be displayed:'),'6','combo',$array_number_of_times);

		$w->CountDown->setting('zeros',__('Show zeros before hours, minutes and seconds'),false,'check');

		$w->CountDown->setting('homeonly',__('Home page only'),false,'check');

	}
}
?>