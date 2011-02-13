<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}$_menu['Plugins']->addItem(	__('Social'),	'plugin.php?p=soCialMe','index.php?pf=soCialMe/icon.png',	preg_match('/plugin.php\?p=soCialMe(&.*)?$/',$_SERVER['REQUEST_URI']),	$core->auth->check('admin',$core->blog->id));require_once dirname(__FILE__).'/_widgets.php';# Admin behaviors$core->addBehavior('adminPostHeaders',array('soCialMeAdmin','adminPostHeaders'));$core->addBehavior('adminPostFormSidebar',array('soCialMeAdmin','adminPostFormSidebar'));$core->addBehavior('adminAfterPostUpdate',array('soCialMeAdmin','adminAfterPostUpdate'));$core->addBehavior('adminAfterPostCreate',array('soCialMeAdmin','adminAfterPostCreate'));$core->addBehavior('adminPostsActions',array('soCialMeAdmin','adminPostsActions'));?>