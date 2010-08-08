<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of paypalDonation, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) return;
if (version_compare(DC_VERSION,'2.2-alpha','<')){return;}

global $__autoload, $core;

$__autoload['paypalDonation'] = dirname(__FILE__).'/inc/class.paypaldonation.php';

# Url of public page of thanks
$core->url->register('paypaldonation','paypal','^paypal(?:/(.+))?$',array('urlPaypalDonation','paypalreturn'));

?>