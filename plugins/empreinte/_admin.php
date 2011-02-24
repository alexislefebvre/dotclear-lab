<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Empreinte, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(
	'Empreinte','plugin.php?p=empreinte',
	'index.php?pf=empreinte/icon.png',
	preg_match('/plugin.php\?p=empreinte(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('usage,contentadmin',$core->blog->id)
);
$core->addBehavior('pluginsAfterDelete',array('adminEmpreinte','pluginsAfterDelete'));

$core->blog->settings->addNamespace('empreinte');

class adminEmpreinte
{
	public static function pluginsAfterDelete($plugin)
	{
		if ($plugin['id'] == 'empreinte') {
			if (!files::deltree(DC_TPL_CACHE.DIRECTORY_SEPARATOR.'cbtpl')) {
				throw new Exception(__('To complete plugin uninstall, please delete the whole cache/cbtpl directory.'));
			}
		}
	}
}
?>