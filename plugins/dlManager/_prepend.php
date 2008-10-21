<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of DL Manager.
# Copyright 2008 Moe (http://gniark.net/) and Tomtom (http://blog.zenstyle.fr)
#
# DL Manager is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# DL Manager is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Image (control_play.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$__autoload['dlManager'] = dirname(__FILE__).'/lib.dlManager.php';

$core->url->register('media','media',
		'^media(/.+)?$',array('dlManagerPageDocument','page'));
$core->url->register('mediaplayer','mediaplayer',
		'^mediaplayer/([0-9]+)?$',array('dlManagerPageDocument','player'));
$core->url->register('download','download',
		'^download/([0-9]+)$',array('dlManagerPageDocument','wrapper'));
$core->url->register('icon','icon',
		'^icon/(.+)?$',array('dlManagerPageDocument','icon'));

?>
