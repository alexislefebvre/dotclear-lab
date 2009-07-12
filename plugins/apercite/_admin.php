<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Apercite, a plugin for Dotclear.
# 
# Copyright (c) 2009 Francis Besset
# francis.besset@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) exit;

$_menu['Plugins']->addItem(__('Apercite'),'plugin.php?p=apercite','index.php?pf=apercite/apercite.gif',
		preg_match('/plugin.php\?p=apercite(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());

$__autoload['aperciteBehaviors'] = dirname(__FILE__).'/lib.apercite.php';

$core->addBehavior('adminPostFormSidebar',array('aperciteBehaviors','aperciteForm'));
$core->addBehavior('adminAfterPostCreate',array('aperciteBehaviors','doUpdate'));
$core->addBehavior('adminAfterPostUpdate',array('aperciteBehaviors','doUpdate'));
?>