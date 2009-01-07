<?php
/*
--- BEGIN LICENSE BLOCK --- 
This file is part of comListe, a plugin for printing comments list 
in public part of DotClear2.
Copyright (C) 2009 Benoit de Marne,  and contributors

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
--- END LICENSE BLOCK ---
*/

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('comListeWidgets','initWidgets'));

class comListeWidgets
{
	public static function initWidgets(&$w)
	{
		$w->create('comListe',__('List of comments'),array('tplComListe','comListeWidget'));
		$w->comListe->setting('title',__('Title:'),__('ComListe'));
		$w->comListe->setting('link_title',__('Link title:'),__('List of comments'));
		$w->comListe->setting('homeonly',__('Home page only'),0,'check');
	}
}
?>