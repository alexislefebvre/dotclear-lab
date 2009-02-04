<?php
# ***** BEGIN LICENSE BLOCK *****
#
# Tribune Libre is a small chat system for Dotclear 2
# Copyright (C) 2007  Antoine Libert
# 
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****
$core->addBehavior('initWidgets',array('tribuneWidgets','initWidgets'));
$core->addBehavior('initWidgets',array('tribuneWidgets','initFormWidgets'));

class tribuneWidgets
{
	public static function initWidgets(&$w)
	{
		$w->create('tribunelibre',__('Free chatbox'),array('tplTribune','tribunelibreWidget'));
		$w->tribunelibre->setting('title',__('Title:'),__('Free chatbox'));
		$w->tribunelibre->setting('homeonly',__('Home page only'),1,'check');
		$w->tribunelibre->setting('deltime',__('Allow deleting messages for (seconds)'),'280');
		$w->tribunelibre->setting('nbshow',__('Number of posts to show'),'15');
		$w->tribunelibre->setting('nbtronq',__('Number of caracters before wordwrap'),'50');
		$w->tribunelibre->setting('sortasc',__('Revert the order'),0,'check');
	}
	
	public static function initFormWidgets(&$w)
	{
		$w->create('tribunelibreform',__('Free chatbox form'),array('tplTribune','tribunelibreFormWidget'));
		$w->tribunelibreform->setting('homeonly',__('Home page only'),1,'check');
	}
}
?>
