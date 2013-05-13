<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of licenseBootstrap, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

global $__autoload,$core;

if ($core->blog === null) return;

$__autoload['licenseBootstrap'] = dirname(__FILE__).'/inc/class.license.bootstrap.php';
$__autoload['libLicenseBootstrap'] = dirname(__FILE__).'/inc/lib.license.bootstrap.index.php';

$core->addBehavior('packmanBeforeCreatePackage',array('licenseBootstrap','packmanBeforeCreatePackage'));
$core->addBehavior('dcTranslaterAfterWriteLangFile',array('licenseBootstrap','dcTranslaterAfterWriteLangFile'));
?>