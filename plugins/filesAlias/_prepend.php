<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of filesAlias, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$__autoload['FilesAliases'] = dirname(__FILE__).'/inc/class.files.alias.php';

$core->url->register('filesalias',$core->blog->settings->filesalias_prefix,'^'
		.$GLOBALS['core']->blog->settings->filesalias_prefix.'/(.+)$',array('urlFilesAlias','alias'));

?>
