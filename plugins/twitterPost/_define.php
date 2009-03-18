<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of TwitterPost.
# Copyright (c) 2009 Hadrien Lanneau.
# All rights reserved.
#
# Pixearch is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# Pixearch is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with Pixearch; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# http://www.alti.info/pages/TwitterPost
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) { return; }
 
$this->registerModule(
	/* Name */			"TwitterPost",
	/* Description*/	"Update your status on new posts",
	/* Author */		"Hadrien Lanneau (http://www.alti.info)",
	/* Version */		'1.0',
	/* Permissions */ 	'usage,contentadmin'
);
