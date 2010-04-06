<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of commentNotifications, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$__autoload['commentNotificationsRestMethods'] = dirname(__FILE__).'/_services.php';

$core->addBehavior('adminPageHTMLHead',array('commentNotificationsBehaviors','adminPageHTMLHead'));

$core->rest->addFunction('getNbComments',array('commentNotificationsRestMethods','getNbComments'));

class commentNotificationsBehaviors
{
	public static function adminPageHTMLHead()
	{
		global $core;
		
		$params = array();
		$nb_comments = $core->blog->getComments($params,true)->f(0);
		$reload_nb_comments = preg_match('/comments.php(.*)?/',$_SERVER['REQUEST_URI']);
		
		echo
		dcPage::jsLoad('index.php?pf='.basename(dirname(__FILE__)).'/_admin.js').
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('notificator.nb_comments',$nb_comments).
		dcPage::jsVar('notificator.reload_nb_comments',($reload_nb_comments ? 'true' : 'false')).
		dcPage::jsVar('notificator.msg.comment',__('comment')).
		dcPage::jsVar('notificator.msg.comments',__('comments')).
		dcPage::jsVar('notificator.msg.recent',__('new')).
		dcPage::jsVar('notificator.msg.recents',__('news')).
		"\n//]]>\n".
		"</script>\n";
	}
}

?>