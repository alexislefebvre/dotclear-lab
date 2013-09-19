<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of authorMode, a plugin for DotClear2.
#
# Copyright (c) 2003 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

require_once dirname(__FILE__).'/_widgets.php';

$_menu['Plugins']->addItem('authorMode','plugin.php?p=authorMode','index.php?pf=authorMode/icon.png',
		preg_match('/plugin.php\?p=authorMode(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());

$core->addBehavior('adminUserHeaders',array('authorModeBehaviors','adminAuthorHeaders'));
$core->addBehavior('adminPreferencesHeaders',array('authorModeBehaviors','adminAuthorHeaders'));
$core->addBehavior('adminUserForm',array('authorModeBehaviors','adminAuthorForm')); // user.php
//$core->addBehavior('adminPreferencesForm',array('authorModeBehaviors','adminAuthorForm')); //preferences.php
$core->addBehavior('adminBeforeUserCreate',array('authorModeBehaviors','adminBeforeUserUpdate'));
$core->addBehavior('adminBeforeUserUpdate',array('authorModeBehaviors','adminBeforeUserUpdate'));

class authorModeBehaviors
{
	public static function adminBeforeUserUpdate($cur,$user_id = '')
	{
		$cur->user_desc = $_POST['user_desc'];
	}
	
	public static function adminAuthorHeaders()
	{
		return
		dcPage::jsToolBar().
		dcPage::jsLoad('index.php?pf=authorMode/_user.js');
	}
	
	public static function adminAuthorForm($rs)
	{
		if ($rs instanceof dcCore) {
			$strReq = 'SELECT user_desc '.
					'FROM '.$rs->con->escapeSystem($rs->prefix.'user').' '.
					"WHERE user_id = '".$rs->con->escape($rs->auth->userID())."' ";
			$_rs = $rs->con->select($strReq);
			if (!$_rs->isEmpty()) {$user_desc = $_rs->user_desc;}
		}
		elseif ($rs instanceof record && $rs->exists('user_desc')) {$user_desc = $rs->user_desc;}
		else $user_desc = '';
		
		echo
		'<p><label>'.__('Author\'s description:').
		dcPage::help('users','user_desc').'</label>'.
		form::textarea('user_desc',50,8,html::escapeHTML($user_desc),'',4).
		'</p>';
	}
}
?>
