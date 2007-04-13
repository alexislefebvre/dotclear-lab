<?php
# ***** BEGIN LICENSE BLOCK *****
# This is spamplemousse2, a plugin for DotClear. 
# Copyright (c) 2007 Alain Vagner and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

global $__autoload, $core;

$__autoload['bayesian'] = dirname(__FILE__).'/inc/class.bayesian.php';
$__autoload['tokenizer'] = dirname(__FILE__).'/tokenizers/class.tokenizer.php';
$__autoload['url_tokenizer'] = dirname(__FILE__).'/tokenizers/class.url_tokenizer.php';
$__autoload['email_tokenizer'] = dirname(__FILE__).'/tokenizers/class.email_tokenizer.php';
$__autoload['ip_tokenizer'] = dirname(__FILE__).'/tokenizers/class.ip_tokenizer.php';
$__autoload['redundancies_tokenizer'] = dirname(__FILE__).'/tokenizers/class.redundancies_tokenizer.php';
$__autoload['reassembly_tokenizer'] = dirname(__FILE__).'/tokenizers/class.reassembly_tokenizer.php';
$__autoload['dcFilterSpample2'] = dirname(__FILE__).'/inc/class.dc.filter.spample2.php';

$core->spamfilters[] = 'dcFilterSpample2';
?>
