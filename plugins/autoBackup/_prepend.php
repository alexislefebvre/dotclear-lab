<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of autoBackup, a plugin for Dotclear.
# 
# Copyright (c) 2008 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$__autoload['autoBackupBehaviors']	= dirname(__FILE__).'/inc/class.auto.backup.behaviors.php';
$__autoload['autoBackup']		= dirname(__FILE__).'/inc/lib.auto.backup.php';
$__autoload['mail']				= dirname(__FILE__).'/inc/class.mail.php';

# Post behaviors
$core->addBehavior('adminBeforePostCreate',array('autoBackupBehaviors','countAddedItems'));
$core->addBehavior('adminAfterPostUpdate',array('autoBackupBehaviors','countAddedItems'));
# Comment behaviors
$core->addBehavior('coreAfterCommentCreate',array('autoBackupBehaviors','countAddedItems'));
$core->addBehavior('coreAfterCommentUpdate',array('autoBackupBehaviors','countAddedItems'));
# Category behaviors
$core->addBehavior('adminAfterCategoryCreate',array('autoBackupBehaviors','countAddedItems'));
$core->addBehavior('adminAfterCategoryUpdate',array('autoBackupBehaviors','countAddedItems'));
# Trackback behaviors
$core->addBehavior('publicAfterTrackbackCreate',array('autoBackupBehaviors','countAddedItems'));

$core->blog->autobackup = new autoBackup($core);
$core->blog->autobackup->check();

?>