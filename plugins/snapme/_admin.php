<?php

# ***** BEGIN LICENSE BLOCK *****
# Widget SnapMe for DotClear.
# Copyright (c) 2007 Ludovic Toinel, All rights
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

// Behaviors du Widget
$core->addBehavior('initWidgets',array('snapMeBehaviors','init'));

// Ajout du plugin dans le menu d'administration 
$_menu['Plugins']->addItem('SnapMe','plugin.php?p=snapme','index.php?pf=snapme/icon.png',
		preg_match('/plugin.php\?p=snapme(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('snapme',$core->blog->id));
		
class snapMeBehaviors
{
        public static function init(&$w)
        {
		$w->create('snapme',__('SnapMe'),array('snapMeTpl','widget'));
		$w->snapme->setting('title',__('Title:'),__('SnapMe'),'text');
		$w->snapme->setting('display',__('Display :'),1,'combo',array(__('Last Snap') => 1, __('Random Snap') => 2));
		$w->snapme->setting('homeonly',__('Home page only'),1,'check');


        }
}
?>