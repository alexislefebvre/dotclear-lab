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

$default_tab = 'externallinks_settings';

if (!empty($_POST['saveconfig'])) {
  try {
    $active = (empty($_POST['active']))?false:true;

    $core->blog->settings->externallinks->put('active', $active, 'boolean');
    $core->blog->triggerBlog();

    $message = __('Configuration successfully updated.');
  } catch(Exception $e) {
    $core->error->add($e->getMessage());
  }
}
include(dirname(__FILE__).'/tpl/index.tpl');
?>