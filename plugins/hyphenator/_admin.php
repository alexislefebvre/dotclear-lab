<?php 
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Hyphenator plugin for Dotclear 2.
#
# Copyright (c) 2009 kévin Lepeltier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

# Class
$GLOBALS['__autoload']['dcHyphenator'] = dirname(__FILE__).'/inc/class.dc.hyphenator.php';
$GLOBALS['__autoload']['LipkiUtils'] = dirname(__FILE__).'/inc/class.lipki.utils.php';

# Préférences du blog
$core->addBehavior('adminBlogPreferencesForm',array('LipkiUtils','adminEnabledPlugin'));
$core->addBehavior('adminEnabledPlugin',array('dcHyphenator','adminEnabledPlugin'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('dcHyphenator','adminBeforeBlogSettingsUpdate'));