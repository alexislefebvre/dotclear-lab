<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear bloganniv plugin.
# Copyright (c) 2007 Trautmann Francis and contributors. All rights
# reserved.
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

$core->addBehavior('initWidgets',array('blogAnnivBehaviors','initWidgets'));

class blogAnnivBehaviors
{
	public static function initWidgets($w)
	{
		global $core;
		$w->create('blogAnniv',__('Blog Anniv'),array('tplBlogAnniv','BlogAnnivWidget'));
		$w->blogAnniv->setting('title',__('Title :'),'');
		$w->blogAnniv->setting('ftdatecrea',__('Born Date (dd/mm/yyyy):'),'jj/mm/aaaa');
		$w->blogAnniv->setting('dispyearborn',__('Display Born Date'),1,'check');
		$w->blogAnniv->setting('dispyear',__('Display Year(s) Old'),1,'check');
		$w->blogAnniv->setting('homeonly',__('Home page only'),1,'check');
	}
}

?>
