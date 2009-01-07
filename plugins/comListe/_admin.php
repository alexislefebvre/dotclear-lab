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

--- icon.png issue du pack mini_icons_2 offertes par Brandspankingnew
--- http://www.brandspankingnew.net
*/

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('ComListe'),'plugin.php?p=comListe','index.php?pf=comListe/img/icon.png',
		preg_match('/plugin.php\?p=comListe(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('admin',$core->blog->id));

?>