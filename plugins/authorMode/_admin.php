<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
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

require_once dirname(__FILE__).'/_widgets.php';

$core->addBehavior('adminInitWidgets',array('widgetsAuthorMode','init'));

$core->addBehavior('adminUserHeaders',array('authorModeBehaviors','adminAuthorHeaders'));
$core->addBehavior('adminPreferencesHeaders',array('authorModeBehaviors','adminAuthorHeaders'));

$core->addBehavior('adminUserForm',array('authorModeBehaviors','adminAuthorForm'));
$core->addBehavior('adminPreferencesForm',array('authorModeBehaviors','adminAuthorForm'));


$core->addBehavior('adminBeforeUserCreate',array('authorModeBehaviors','adminBeforeUserUpdate'));
$core->addBehavior('adminBeforeUserUpdate',array('authorModeBehaviors','adminBeforeUserUpdate'));

class authorModeBehaviors {

	public static function adminBeforeUserUpdate(&$cur,&$user_id = '')
	{
		$cur->user_desc = $_POST['user_desc'];
	}


	public static function adminAuthorHeaders()
	{
		return (dcPage::jsToolBar().
			'<script type="text/javascript" src="index.php?pf=authorMode/_user.js"></script>');
	}

	public static function adminAuthorForm(&$rs)
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

		$res = '<div class="clear">'.
			'<p class="area"><label for="user_desc">'.__('Description:').
			dcPage::help('users','user_desc').'</label> '.
			form::textarea('user_desc',50,8,html::escapeHTML($user_desc),'',4).
			'</p></div>';

		return $res;
	}
}
?>
