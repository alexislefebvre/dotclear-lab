<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
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
#
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('initWidgets',array('blogAnnivBehaviors','initWidgets'));

class blogAnnivBehaviors
{
	public static function initWidgets(&$widgets)
	{
		$widgets->create('blogAnniv',__('Blog Anniv'),array('tplBlogAnniv','BlogAnnivWidget'));
		$widgets->blogAnniv->setting('title',__('Title :'),'');
		$widgets->blogAnniv->setting('ftdatecrea',__('Born Date (dd/mm/yyyy) :'),'jj/mm/aaaa');
		$widgets->blogAnniv->setting('dispyearborn',__('Display Born Date'),1,'check');
		$widgets->blogAnniv->setting('dispyear',__('Display Year(s) Old'),1,'check');
		$widgets->blogAnniv->setting('homeonly',__('Home page only'),1,'check');
	}
}

?>