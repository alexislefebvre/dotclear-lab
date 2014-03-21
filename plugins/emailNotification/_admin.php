<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
#               2014 Vincent Danjean
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminPreferencesForm',array('notificationBehaviors','adminPreferencesForm'));// preferences.php
$core->addBehavior('adminUserForm',array('notificationBehaviors','adminUserForm'));// user.php

$core->addBehavior('adminBeforeUserOptionsUpdate',array('notificationBehaviors','adminBeforeUserUpdate'));// preferences.php
$core->addBehavior('adminBeforeUserUpdate',array('notificationBehaviors','adminBeforeUserUpdate'));// user.php

$core->addBehavior('adminAfterCommentCreate',array('notificationBehaviors','afterCommentCreate'));

$core->addBehavior('adminAfterPostCreate',array('notificationBehaviors','adminAfterPostCreate'));
$core->addBehavior('adminBeforePostUpdate',array('notificationBehaviors','adminBeforePostUpdate'));
$core->addBehavior('adminAfterPostUpdate',array('notificationBehaviors','adminAfterPostUpdate'));

$core->addBehavior('coreBlogAfterTriggerBlog',array('notificationBehaviors','coreBlogAfterTriggerBlog'));
