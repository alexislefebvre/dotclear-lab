<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminPreferencesForm',array('smsNotificationBehaviors','adminUserForm'));
$core->addBehavior('adminUserForm',array('smsNotificationBehaviors','adminUserForm'));
$core->addBehavior('adminBeforeUserUpdate',array('smsNotificationBehaviors','adminBeforeUserUpdate'));
?>