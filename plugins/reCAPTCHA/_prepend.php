<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of reCAPTCHA, a plugin for Dotclear 2
# Copyright 2009 Moe (http://gniark.net/)
#
# reCAPTCHA is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# reCAPTCHA is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

global $__autoload, $core;

$__autoload['dcFilterreCAPTCHA'] =
	dirname(__FILE__).'/inc/class.dc.filter.reCAPTCHA.php';

$core->spamfilters[] = 'dcFilterreCAPTCHA';

?>