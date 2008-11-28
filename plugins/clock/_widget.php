<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Clock.
# Copyright 2007-2008 Moe (http://gniark.net/)
#
# Clock is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Clock is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

$core->addBehavior('initWidgets',array('ClockBehaviors','initWidgets'));
 
class ClockBehaviors
{
	public static function initWidgets(&$w)
	{
		# set timezone
		global $core;
		$tz = $core->blog->settings->blog_timezone;

		$w->create('Clock',__('Clock'),array('publicClock','Show'));

		$w->Clock->setting('title',__('Title:').' ('.__('optional').')',__('Local time in').' '.substr(strrchr($tz,'/'),1),'text');
		
		$w->Clock->setting('timezone',__('Timezone:'),'','combo',
			dt::getZones(true,true));
		
		$w->Clock->setting('format',__('Format (see PHP strftime function) (HMS display dynamically %H:%M:%S):'),'%A, %e %B %Y, HMS','text');

		$w->Clock->setting('homeonly',__('Home page only'),false,'check');

	}
}

class publicClock
{
	public static function Show(&$w)
	{
		# get blog language
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}

		# output
		$header = (strlen($w->title) > 0) ? '<h2>'.html::escapeHTML($w->title).'</h2>' : null;

		if (strpos($w->format, 'HMS') !== False)
		{
			$id = str_replace('/','',strtolower($w->timezone));

			$js = (string)'';
			$js .= '<script type="text/javascript">';
			/* http://binnyva.blogspot.com/2005/12/my-custom-javascript-functions.html */
			if (!defined('CLOCK_GEBI'))
			{
				$js .= 'function gEBI(id) {return document.getElementById(id);}';
				$js .= 'function zeros(int) {if (10 > int) {int = \'0\'+int;}return int;}';
				$js .= 'var d = new Date();';
				define('CLOCK_GEBI',(bool)true);
			}

			$js .= 'var diffH_'.$id.' = (d.getHours()-'.(dt::str('%H',null,$w->timezone)*1).');';

			$js .= 'function clock_'.$id.'() {'.
				'var d = new Date();'.
				'var h = zeros(d.getHours()-diffH_'.$id.');'.
				'var m = zeros(d.getMinutes());'.
				'var s = zeros(d.getSeconds());'.
				'gEBI(\'hms_'.$id.'\').innerHTML = h+\':\'+m+\':\'+s;'.
				'setTimeout("clock_'.$id.'()",500);'.
				'}';
	
			$js .= 'clock_'.$id.'();';
			$js .= '</script>';

			$hms = '<span id="hms_'.$id.'">'.dt::str('%H',null,$w->timezone).':'.dt::str('%M',null,$w->timezone).':'.dt::str('%S',null,$w->timezone).'</span>';
			$time = dt::str($w->format,null,$w->timezone);
			$time = str_replace('HMS',$hms,$time);
		}
		else
		{
			$time = dt::str($w->format,null,$w->timezone);
			$js = null;
		}

		return '<div class="clock">'.$header.'<p class="text">'.$time.'</p>'.$js.'</div>';
	}
}

?>