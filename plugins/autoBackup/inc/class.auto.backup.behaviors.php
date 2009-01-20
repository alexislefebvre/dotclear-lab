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

class autoBackupBehaviors
{
	public static function countAddedItems()
	{
		global $core;

		$config = $core->blog->autobackup->getConfig();

		if ($config['activity_threshold'] > 0) {
			$core->blog->autobackup->setConfig(array('activity_count' => ($config['activity_count'] + 1)));
		}

		$core->blog->autobackup->check();
	}
}

?>