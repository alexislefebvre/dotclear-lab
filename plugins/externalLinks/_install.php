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

$version = $core->plugins->moduleInfo('externalLinks', 'version');
if (version_compare($core->getVersion('externalLinks'), $version,'>=')) {
  return;
}

$settings = $core->blog->settings;
$settings->addNamespace('externallinks');

$settings->externallinks->put('active', false, 'boolean', 'external Links plugin activation', false);

$core->setVersion('externalLinks', $version);
return true;
?>