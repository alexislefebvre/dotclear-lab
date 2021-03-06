<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Subscribe to comments.
# Copyright (C) 2008-2018 Moe (http://gniark.net/)
#
# Subscribe to comments is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Subscribe to comments is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://txfx.net/code/wordpress/subscribe-to-comments/
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$__autoload['subscribeToComments'] =
	dirname(__FILE__).'/inc/lib.subscribeToComments.php';
$__autoload['subscribeToCommentsDocument'] =
	dirname(__FILE__).'/inc/lib.subscribeToComments.document.php';
$__autoload['subscribeToCommentsTpl'] =
	dirname(__FILE__).'/inc/lib.subscribeToComments.tpl.php';
$__autoload['subscriber'] =
	dirname(__FILE__).'/inc/class.subscriber.php';

$core->url->register('subscribetocomments','subscribetocomments',
	'^subscribetocomments(?:/(.+))?$',
	array('subscribeToCommentsDocument','page'));