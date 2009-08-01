<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Micro-Blogging, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jeremie Patonnier
# jeremie.patonnier@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }
 
$this->registerModule(
        /* Name */        "MicroBlog",
        /* Description*/  "Display and update your streamlifes all over the Web",
        /* Author */      "Jeremie Patonnier",
        /* Version */     '0.1.2',
        /* Permissions */ 'usage,contentadmin'
);

# CHANGELOG

# v. 0.1.2
# fix microBlog instenciation
# fix microBlogwidget instenciation
# fix some chmod issues in microBlogCache
# add microBlogService::formatOutput()

# v. 0.1.1 rev 1358
# some code polishing
# Add the licence block to each PHP file

# v. 0.1 rev 1350
# first alpha realease