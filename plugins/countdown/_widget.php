<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of CountDown, a plugin for Dotclear 2
# Copyright 2007,2010 Moe (http://gniark.net/)
#
# CountDown is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# CountDown is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$core->addBehavior('initWidgets',
	array('CountDownBehaviors','initWidgets'));
 
class CountDownBehaviors
{
	public static function initWidgets($w)
	{
		# set timezone
		global $core;
		$tz = $core->blog->settings->blog_timezone;

		$w->create('CountDown',__('CountDown'),
			array('CountDownBehaviors','Show'));

		$w->CountDown->setting('title',__('Title:'),__('CountDown'),'text');

		$w->CountDown->setting('text_before',
			__('Text displayed if the date is in the future:'),__('In'),'text');

		$w->CountDown->setting('text_after',
			__('Text displayed if the date is in the past:'),__('For'),'text');

		# create arrays for year, month, day, hour, minute and second
		$array_year = $array_month = $array_day = $array_hour = array();
		$array_minute = $array_number_of_times = array();
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

		$w->CountDown->setting('year',ucfirst(__('year')).':',
			dt::str('%Y',null,$tz),'combo',$array_year);
		$w->CountDown->setting('month',ucfirst(__('month')).':',
			dt::str('%m',null,$tz),'combo',$array_month);
		$w->CountDown->setting('day',ucfirst(__('day')).':',
			dt::str('%d',null,$tz),'combo',$array_day);
		$w->CountDown->setting('hour',ucfirst(__('hour')).':',
			dt::str('%H',null,$tz),'combo',$array_hour);
		$w->CountDown->setting('minute',ucfirst(__('minute')).':',
			dt::str('%M',null,$tz),'combo',$array_minute);
		$w->CountDown->setting('second',ucfirst(__('second')).':',
			dt::str('%S',null,$tz),'combo',$array_minute);

		$w->CountDown->setting('number_of_times',
			__('Number of values to be displayed:'),'6','combo',
			$array_number_of_times);

		$w->CountDown->setting('zeros',
			__('Show zeros before hours, minutes and seconds'),false,'check');
		
		$w->CountDown->setting('dynamic',
			__('Enable dynamic display'),false,'check');
		
		$w->CountDown->setting('dynamic_format',
			sprintf(__('Dynamic display format (see <a href="%1$s" %2$s>jQuery Countdown Reference</a>):'),
			'http://keith-wood.name/countdownRef.html#format',
			'onclick="return window.confirm(\''.
			__('Are you sure you want to leave this page?').'\')"'),
			__('yowdHMS'),'text');
		
		$w->CountDown->setting('dynamic_layout_before',
			sprintf(__('Dynamic display layout if the date is in the future (see <a href="%1$s" %2$s>jQuery Countdown Reference</a>):'),
			'http://keith-wood.name/countdownRef.html#layout',
			'onclick="return window.confirm(\''.
			__('Are you sure you want to leave this page?').'\')"'),
			__('In {y<}{yn} {yl}, {y>} {o<}{on} {ol}, {o>} {w<}{wn} {wl}, {w>} {d<}{dn} {dl}, {d>} {hn} {hl}, {mn} {ml} and {sn} {sl}'),
			'textarea');
		
		$w->CountDown->setting('dynamic_layout_after',
			sprintf(__('Dynamic display layout if the date is in the past (see <a href="%1$s" %2$s>jQuery Countdown Reference</a>):'),
			'http://keith-wood.name/countdownRef.html#layout',
			'onclick="return window.confirm(\''.
			__('Are you sure you want to leave this page?').'\')"'),
			__('For {y<}{yn} {yl}, {y>} {o<}{on} {ol}, {o>} {w<}{wn} {wl}, {w>} {d<}{dn} {dl}, {d>} {hn} {hl}, {mn} {ml} and {sn} {sl}'),
			'textarea');
		
		$w->CountDown->setting('homeonly',
			__('Home page only'),false,'check');
	}
	
	# escape quotes but not XHTML tags
	# inspired by html::escapeJS()
	public static function escapeQuotes($str)
	{
		$str = str_replace("'","\'",$str);
		$str = str_replace('"','\"',$str);
		return $str;
	}
	
	public static function Show($w)
	{
		# set timezone
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		# get local time
		$local_time = dt::addTimeZone($core->blog->settings->blog_timezone);

		$ts = mktime($w->hour,$w->minute,$w->second,$w->month,$w->day,
			$w->year);
		# get difference
		(int)$diff = ($local_time - $ts);
		$after = ($diff > 0) ? true : false;
		$diff = abs($diff);
		
		$times = array();
		
		$intervals = array
		(
			(3600*24*365.24) => array('one'=>__('year'),'more'=>__('years'),
				'zeros'=>false),
			(3600*24*30.4) => array('one'=>__('month'),'more'=>__('months'),
				'zeros'=>false),
			(3600*24) => array('one'=>__('day'),'more'=>__('days'),
				'zeros'=>false),
			(3600) => array('one'=>__('hour'),'more'=>__('hours'),
				'zeros'=>true),
			(60) => array('one'=>__('minute'),'more'=>__('minutes'),
				'zeros'=>true),
			(1) => array('one'=>__('second'),'more'=>__('seconds'),
				'zeros'=>true),
		);
		
		foreach ($intervals as $k => $v)
		{
			if ($diff >= $k)
			{
				$time = floor($diff/$k);
				$times[] = (($w->zeros AND $v['zeros'])
					? sprintf('%02d',$time) : $time).' '.(($time <= 1) ? $v['one']
					: $v['more']);
				$diff = $diff%$k;
			}
		}
		
		# output
		$header = (strlen($w->title) > 0)
			? '<h2>'.html::escapeHTML($w->title).'</h2>' : '';
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
		
		if (!$w->dynamic)
		{
			return '<div class="countdown">'.$header.
				'<p class="text">'.$text.'<span>'.$str.'</span></p>'.
			'</div>';
		}
		else
		{
			# dynamic display with Countdown for jQuery
			if (!is_numeric($GLOBALS['_ctx']->countdown))
			{
				$GLOBALS['_ctx']->countdown = 0;
			}
			$id = $GLOBALS['_ctx']->countdown;
			$GLOBALS['_ctx']->countdown += 1;
			
			$script = '';
			
			if (!defined('COUNTDOWN_SCRIPT'))
			{
				$script =
					'<script type="text/javascript" src="'.
					$core->blog->getQmarkURL().
					'pf=countdown/js/jquery.countdown.min.js"></script>'."\n";
		
				$l10n_file =
					'jquery.countdown-'.$core->blog->settings->lang.'.js';
				if (file_exists(dirname(__FILE__).'/js/'.$l10n_file))
				{
					$script .= 
					'<script type="text/javascript" src="'.$core->blog->getQmarkURL().
						'pf=countdown/js/'.$l10n_file.'"></script>'."\n";
				}
				
				define('COUNTDOWN_SCRIPT',(bool)true);
			}
			
			if ($after)
			{
				$to = 'since';
				$layout = $w->dynamic_layout_after;
			}
			else
			{
				$to = 'until';
				$layout = $w->dynamic_layout_before;
			}
			
			return $script.'<div class="countdown">'.$header.
				'<p class="text" id="countdown-'.$id.'">'.$text.$str.'</p>'.
				'<script type="text/javascript">'."\n".
				'//<![CDATA['."\n".
					'$().ready(function() {'.
					"$('#countdown-".$id."').countdown({".
						# In Javascript, 0 = January, 11 = December
						$to.": new Date(".(int)$w->year.",".(int)$w->month."-1,".
						(int)$w->day.",".(int)$w->hour.",".(int)$w->minute.",".
						(int)$w->second."),
						description: '".html::escapeJS($text)."',
						format: '".$w->dynamic_format."',
						layout: '".$layout."',
						expiryText: '".html::escapeJS($w->text_after)."'
					});".
					'});'."\n".
				'//]]>'.
				'</script>'."\n".
			'</div>';
		}
	}
}
?>