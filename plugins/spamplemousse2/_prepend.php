<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Spamplemousse2, a plugin for Dotclear 2.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

global $__autoload, $core;

$__autoload['bayesian'] = dirname(__FILE__).'/inc/class.bayesian.php';
$__autoload['tokenizer'] = dirname(__FILE__).'/tokenizers/class.tokenizer.php';
$__autoload['url_tokenizer'] = dirname(__FILE__).'/tokenizers/class.url_tokenizer.php';
$__autoload['email_tokenizer'] = dirname(__FILE__).'/tokenizers/class.email_tokenizer.php';
$__autoload['ip_tokenizer'] = dirname(__FILE__).'/tokenizers/class.ip_tokenizer.php';
$__autoload['redundancies_tokenizer'] = dirname(__FILE__).'/tokenizers/class.redundancies_tokenizer.php';
$__autoload['reassembly_tokenizer'] = dirname(__FILE__).'/tokenizers/class.reassembly_tokenizer.php';
$__autoload['dcFilterSpample2'] = dirname(__FILE__).'/inc/class.dc.filter.spample2.php';
$__autoload['progress'] = dirname(__FILE__).'/inc/class.progress.php';

$core->spamfilters[] = 'dcFilterSpample2';
?>
