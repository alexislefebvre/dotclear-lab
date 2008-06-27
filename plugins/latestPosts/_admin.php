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
#
# D'après le plugin LastComment de Vincent Simonin et un billet de documentation 
# plugin du même auteur 
# visible sur http://www.forx.fr/post/2006/07/07/Doctclear-2-%3A-creation-de-plugin-premiere-partie
#
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('initWidgets',array('lastestPostsBehaviors','initWidgets'));

class lastestPostsBehaviors
{
	public static function initWidgets(&$widgets)
	{
		$widgets->create('lastestPosts',__('Lastest Categ. And Post'),array('tplLastestPosts','LastestPostsWidget'));
		$widgets->lastestPosts->setting('title',__('Title:'),'');
		$widgets->lastestPosts->setting('limit',__('Limit (empty means 10 posts):'),'10');
		$widgets->lastestPosts->setting('nb_letter',__('Number letter to keep (empty means all the posts title):'),'50');
		$widgets->lastestPosts->setting('categ_show',__('View category title'),1,'check');
		$widgets->lastestPosts->setting('protect_show',__('View posts protected'),1,'check');
		$widgets->lastestPosts->setting('homeonly',__('Home page only'),1,'check');
	}
}
?>
