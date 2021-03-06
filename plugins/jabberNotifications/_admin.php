<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Jabber Notifications, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>, Olivier Tétard
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Jabber notifications'),'plugin.php?p=jabberNotifications',
			   'index.php?pf=jabberNotifications/icon.png',
			   preg_match('/plugin.php\?p=jabberNotifications(&.*)?$/',$_SERVER['REQUEST_URI']),
			   $core->auth->isSuperAdmin());

$core->addBehavior('adminUserHeaders', array('privateJabberNotifications', 'javascriptHelpers'));
$core->addBehavior('adminPreferencesHeaders', array('privateJabberNotifications', 'javascriptHelpers'));
$core->addBehavior('adminPreferencesForm', array('privateJabberNotifications', 'adminPreferencesForm'));
$core->addBehavior('adminUserForm', array('privateJabberNotifications', 'adminUserForm'));
$core->addBehavior('adminBeforeUserUpdate', array('privateJabberNotifications', 'adminBeforeUserUpdate'));
$core->addBehavior('adminBeforeUserCreate', array('privateJabberNotifications', 'adminBeforeUserUpdate'));

class privateJabberNotifications
{
	public static function adminUserForm()
	{
		global $core,$user_options;
		
		$jn_mode = 'user_settings';
		$jn_notify = empty($user_options['jn_notify']) ? 'never' : $user_options['jn_notify'];
		$jn_jabberid = empty($user_options['jn_jabberid']) ? '' : $user_options['jn_jabberid'];
		
		include dirname(__FILE__).'/forms.php';
		echo $jn_forms['user_settings'];
	}

	public static function adminPreferencesForm(&$core)
	{
		global $user_options;
		
		$jn_mode = 'user_settings';
		$jn_notify = empty($user_options['jn_notify']) ? 'never' : $user_options['jn_notify'];
		$jn_jabberid = empty($user_options['jn_jabberid']) ? '' : $user_options['jn_jabberid'];
		
		include dirname(__FILE__).'/forms.php';
		echo $jn_forms['user_settings'];
	}

	public static function adminBeforeUserUpdate(&$cur)
	{
		global $core;
		
		// Vérification de la présence des paramètres
		if (empty($cur->user_options['jn_notify']))
		{
			$cur->user_options['jn_notify'] = 'never';
			$cur->user_options['jn_jabberid'] = '';
		}
		
		if ($_POST['jn_notify'] == 'never')
		{
			$cur->user_options['jn_notify'] = 'never';
		}
		elseif (isset($_POST['jn_notify']))
		{
			$cur->user_options['jn_notify'] = $_POST['jn_notify'];
			$cur->user_options['jn_jabberid'] = $_POST['jn_jabberid'];
		}
	
		# Notifications are disabled, nothing to do
		if ($cur->user_options['jn_notify'] == 'never') {
			return;
		}
		# Only JabberID is required
		else
		{
			if (empty($cur->user_options['jn_jabberid'])) {
				throw new Exception(__('Jabber ID is empty'));
			}
			if (!text::isEmail($cur->user_options['jn_jabberid'])) {
				throw new Exception(__('Invalid Jabber ID'));
			}
			if (!in_array($cur->user_options['jn_notify'],array('entries','blog','blogs','all'))) {
				throw new Exception(__('Invalid notification status'));
			}
		}
	}
	
	public static function javascriptHelpers()
	{
		return '<script type="text/javascript" src="index.php?pf=jabberNotifications/private_helpers.js"></script>';
	}
}
?>