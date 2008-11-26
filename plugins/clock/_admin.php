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

$_menu['Plugins']->addItem(__('Clock'),'plugin.php?p=clock','index.php?pf=clock/icon.png',
		preg_match('/plugin.php\?p=clock(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));

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

		$w->Clock->setting('timezone',__('Timezone:'),$tz,'text');
		
		$w->Clock->setting('format',__('Format (see PHP strftime function) (HMS display dynamically %H:%M:%S):'),'%A, %e %B %Y, HMS','text');

		$w->Clock->setting('homeonly',__('Home page only'),false,'check');

	}
}
?>