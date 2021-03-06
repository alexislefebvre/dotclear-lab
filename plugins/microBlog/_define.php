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
        /* Name */        "Micro-Blogging",
        /* Description*/  "Display and update your streamlifes all over the Web",
        /* Author */      "Jeremie Patonnier",
        /* Version */     '0.3',
        /* Permissions */ 'usage,contentadmin'
);

# CHANGELOG
# TODO Check login validity when adding a service
# TODO Add help page
# TODO Fix autosend note for planified post

# v. 0.3
# make behaviors PHP 5.3 ready
# Clear blog cache every 10 min (necessary for widget... need improvement)
# Add new post message customization in admin


# v. 0.2.2 rev 1391
# fixe install

# v. 0.2.1 rev 1374
# Add fr local

# v. 0.2 rev 1361
# turn BETA
# Start implementation of microBlogService::sanitize()
# Clean up services instenciation
# Refactoring the admin page

# v. 0.1.2 rev 1359
# fix microBlog instenciation
# fix microBlogwidget instenciation
# fix some chmod issues in microBlogCache
# add microBlogService::formatOutput()

# v. 0.1.1 rev 1358
# some code polishing
# Add the licence block to each PHP file

# v. 0.1 rev 1350
# first alpha realease