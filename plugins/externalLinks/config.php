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

$active = $core->blog->settings->externallinks->active;
$all_links = $core->blog->settings->externallinks->all_links;
$checkbox_new_links = $core->blog->settings->externallinks->checkbox_new_links;
$one_link = $core->blog->settings->externallinks->one_link;

$default_tab = 'externallinks_settings';

if (!empty($_POST['saveconfig'])) {
  try {
    $active = (empty($_POST['active']))?false:true;
    $all_links = (empty($_POST['all_links']))?false:true;
    $checkbox_new_links = (empty($_POST['checkbox_new_links']))?false:true;
    $one_link = (empty($_POST['one_link']))?false:true;

    $core->blog->settings->externallinks->put('active', $active, 'boolean');
    $core->blog->settings->externallinks->put('all_links', $all_links, 'boolean');
    $core->blog->settings->externallinks->put('checkbox_new_links', $checkbox_new_links, 'boolean');
    $core->blog->settings->externallinks->put('one_link', $one_link, 'boolean');
    $core->blog->triggerBlog();

    $message = __('Configuration successfully updated.');
  } catch(Exception $e) {
    $core->error->add($e->getMessage());
  }
}
include(dirname(__FILE__).'/tpl/index.tpl');
?>