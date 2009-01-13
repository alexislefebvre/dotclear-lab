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

class publicCountDown
{
	public static function Show(&$w)
	{
		# set timezone
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		# get local time
		$local_time = dt::addTimeZone($core->blog->settings->blog_timezone);

		$ts = mktime($w->hour,$w->minute,$w->second,$w->month,$w->day,$w->year);
		# get difference
		(int)$diff = ($local_time - $ts);
		$after = ($diff > 0) ? true : false;
		$diff = abs($diff);

		$times = array();

		$intervals = array
		(
			(3600*24*365.24) => array('one'=>__('year'),'more'=>__('years'),'zeros'=>false),
			(3600*24*30.4) => array('one'=>__('month'),'more'=>__('months'),'zeros'=>false),
			(3600*24) => array('one'=>__('day'),'more'=>__('days'),'zeros'=>false),
			(3600) => array('one'=>__('hour'),'more'=>__('hours'),'zeros'=>true),
			(60) => array('one'=>__('minute'),'more'=>__('minutes'),'zeros'=>true),
			(1) => array('one'=>__('second'),'more'=>__('seconds'),'zeros'=>true),
		);

		foreach ($intervals as $k => $v)
		{
			if ($diff >= $k)
			{
				$time = floor($diff/$k);
				$times[] = (($w->zeros AND $v['zeros']) ? sprintf('%02d',$time) : $time).' '.(($time <= 1) ? $v['one'] : $v['more']);
				$diff = $diff%$k;
			}
		}

		# output
		$header = (strlen($w->title) > 0) ? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;
		$text = ($after) ? $w->text_after : $w->text_before;
		if (strlen($text) > 0) {$text .= ' ';}

		# get times and make a string
		$times = array_slice($times,0,$w->number_of_times);
		if (count($times) > 1)
		{
			$last = array_pop($times);
			$str = implode(', ',$times).' '.__('and').' '.$last;
		}
		else {$str = implode('',$times);}

		return '<div class="countdown">'.$header.'<p class="text">'.$text.$str.'</p></div>';
	}
}
?>