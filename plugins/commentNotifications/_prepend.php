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

# Init cookie
if (defined('DC_CONTEXT_ADMIN')) {
	$params = array();
	$cookie_name = 'dc_nb_comments';
	$nb_comments = $core->blog->getComments($params,true)->f(0);
		
	if (!isset($_COOKIE[$cookie_name])) {
		setcookie($cookie_name,$core->blog->getComments($params,true)->f(0));
	}
}

$__autoload['commentNotificationsRestMethods'] = dirname(__FILE__).'/_services.php';

$core->addBehavior('adminPageHTMLHead',array('commentNotificationsBehaviors','adminPageHTMLHead'));
$core->addBehavior('adminCommentsHeaders',array('commentNotificationsBehaviors','adminCommentsHeaders'));

$core->rest->addFunction('getNbComments',array('commentNotificationsRestMethods','getNbComments'));

class commentNotificationsBehaviors
{
	public static function adminPageHTMLHead()
	{
		global $core;
		
		$params = array();
		$cookie_name = 'dc_nb_comments';
		$nb_comments = $core->blog->getComments($params,true)->f(0);
		
		if (!isset($_COOKIE[$cookie_name])) {
			setcookie($cookie_name,$core->blog->getComments($params,true)->f(0));
		}
		
		echo
		'<script type="text/javascript">'.
		'var nb_comments = '.$nb_comments.';'.
		'</script>'.
		'<script type="text/javascript" src="index.php?pf='.
		basename(dirname(__FILE__)).'/_admin.js"></script>';
	}
	
	public static function adminCommentsHeaders()
	{
		global $core;
		
		$params = array();
		$cookie_name = 'dc_nb_comments';
		
		if (isset($_COOKIE[$cookie_name])) {
			setcookie($cookie_name,$core->blog->getComments($params,true)->f(0));
		}
	}
}

?>