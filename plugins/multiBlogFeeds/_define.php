<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiBlogFeeds, a plugin for Dotclear 2.
#
# Copyright (c) 2009 Johan Pustoch and contributors
# johan.pustoch@crdp.ac-versailles.fr
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
    /* Name */                      "Rss feed of all blogs",
    /* Description*/                "Rss feed of all blogs (posts or comments)",
    /* Author */                    "Johan Pustoch",
    /* Version */                   '0.2',
    /* Permissions */               'usage'
);
?>