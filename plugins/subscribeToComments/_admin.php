<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Subscribe to comments.
# Copyright 2008 Moe (http://gniark.net/)
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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Subscribe to comments'),
	'plugin.php?p=subscribeToComments',
	'index.php?pf=subscribeToComments/icon.png',
	preg_match('/plugin.php\?p=subscribeToComments(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

	if ($core->blog->settings->subscribetocomments_active)
	{
		require_once(dirname(__FILE__).'/lib.subscribeToComments.php');
		require_once(dirname(__FILE__).'/class.subscriber.php');

		$core->addBehavior('adminAfterCommentCreate',array('subscribeToComments',
			'adminAfterCommentCreate'));
		$core->addBehavior('adminAfterCommentDesc',array('subscribeToComments',
			'adminAfterCommentDesc'));
		# when a comment is published
		$core->addBehavior('coreAfterCommentUpdate',array('subscribeToComments',
			'coreAfterCommentUpdate'));
	}

?>