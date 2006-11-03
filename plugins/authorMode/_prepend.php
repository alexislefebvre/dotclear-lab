<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
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
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

$author_url =  $GLOBALS['core']->blog->settings->author_url;
$authors_url =  $GLOBALS['core']->blog->settings->authors_url;
if ($author_url == null ) {$author_url = 'author';}
if ($authors_url == null ) {$authors_url = 'authors';}

$GLOBALS['core']->url->register('author',$author_url,'^'.$author_url.'/(.+)$',array('urlAuthor','author'));
$GLOBALS['core']->url->register('authors',$authors_url,'^'.$authors_url.'$',array('urlAuthor','authors'));
$GLOBALS['core']->url->register('author_feed','feed/'.$author_url,'^feed/'.$author_url.'/(.+)$',array('urlAuthor','feed'));
?>