<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Link Summarizer.
# Copyright 2009 Moe (http://gniark.net/)
#
# Link Summarizer is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 3 of the License, or
# (at your option) any later version.
#
# Link Summarizer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# Icon (icon.png) and images are from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# Inspired by http://wordpress.org/extend/plugins/link-summarizer/
#
# http://mac.partofus.org/macpress/2007/08/02/wordpress-plugin-link-summarizer/
# http://mac.partofus.org/macpress/forum/english-support-forum/dotclear-plugin/page-1/post-6/#p6
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
     /* Name */                      'Link Summarizer',
     /* Description*/                'Extracts links from posts',
     /* Author */                    'Moe (http://gniark.net/)',
     /* Version */                   '0.1.2',
     /* Permissions */               'admin,contentadmin'
);
?>