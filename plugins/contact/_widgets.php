<?php
# ***** BEGIN LICENSE BLOCK *****
# This is Contact, a plugin for DotClear.
# Copyright (c) 2005 k-net. All rights reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('contactWidget','initWidgets'));

class contactWidget
{
	public static function initWidgets($w)
	{
		global $core;
    $w->create('contact',__('Contact'),array('tplContact','contactWidget'));
		$w->contact->setting('title',__('Title: (Use %I for Contact icon)'),'%I Contact');
		$w->contact->setting('homeonly',__('Home page only'),0,'check');
		$w->contact->setting('usesubtitle',__('Display the link in a subtitle'),0,'check');
		$w->contact->setting('subtitle',__('If yes, subtitle :'),__('Contact me!'));
	}
}

?>
