<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2008 Olivier Meunier and contributors. All rights
# reserved.
# Copyright(C) 2010 Nicolas Roudaire - http://www.nikrou.net
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
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$href = !empty($_GET['href']) ? $_GET['href'] : '';
$hreflang = !empty($_GET['hreflang']) ? $_GET['hreflang'] : '';
$external = !empty($_GET['external']) ? $_GET['external'] : '';

/* Languages combo */
$rs = $core->blog->getLangs(array('order'=>'asc'));
$all_langs = l10n::getISOcodes(0,1);
$lang_combo = array('' => '', __('Most used') => array(), __('Available') => l10n::getISOcodes(1,1));
while ($rs->fetch()) {
  if (isset($all_langs[$rs->post_lang])) {
    $lang_combo[__('Most used')][$all_langs[$rs->post_lang]] = $rs->post_lang;
    unset($lang_combo[__('Available')][$all_langs[$rs->post_lang]]);
  } else {
    $lang_combo[__('Most used')][$rs->post_lang] = $rs->post_lang;
  }
}
unset($all_langs);
unset($rs);

include(dirname(__FILE__).'/tpl/popup.tpl');
?>