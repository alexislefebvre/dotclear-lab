<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Pixearch.
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
# http://www.alti.info/pages/pixearch
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_RC_PATH')) { return; }
 
$this->registerModule(
	/* Name */			"Pixearch",
	/* Description*/	"Search for external pictures from Flickr, Photobucket, and deviantART for inserting into posts",
	/* Author */		"Hadrien Lanneau (contact at hadrien dot eu)",
	/* Version */		'1.2.1',
	/* Permissions */	'usage,contentadmin'
);
