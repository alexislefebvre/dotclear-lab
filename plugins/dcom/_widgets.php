<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Dcom, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('widgetsDcom','initWidget'));

class widgetsDcom
{
	public static function initWidget(&$w)
	{
		global $core;
		
		commonDcom::adjustDefaults($p);
		
		$w->create('lastcomments',__('Last comments'),
			array('publicDcom','showWidget'));
		$w->lastcomments->setting('title',
			__('Title:'),$p['title']);
		$w->lastcomments->setting('c_limit',
			__('Comments limit:'),$p['c_limit']);
		$w->lastcomments->setting('t_limit',
			__('Title lenght limit:'),$p['t_limit']);
		$w->lastcomments->setting('co_limit',
			__('Comment lenght limit:'),$p['co_limit']);
		$w->lastcomments->setting('dateformat',
			__('Date format (leave empty to use default blog format):'),$p['dateformat']);
		$w->lastcomments->setting('stringformat',
			__('String format (%1$s = date; %2$s = title; %3$s = author; %4$s = content of the comment; %5$s = comment URL):'),
			$p['stringformat']);
		$w->lastcomments->setting('homeonly',
			__('Home page only'),$p['homeonly'],'check');
	}
}
?>