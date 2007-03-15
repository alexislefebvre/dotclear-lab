<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2006 Olivier Meunier and contributors. All rights
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

$core->addBehavior('initWidgets',array('widgetsAuthorMode','init'));

class widgetsAuthorMode
{
	public static function authors(&$w)
	{
		global $core;
		
		if (!$core->blog->settings->authormode_active) return;
		
		$rs = authormodeUtils::getPostsUsers();
		if ($rs->isEmpty()) {
			return;
		}
		
		$res =
		'<div id="authors">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch()) {
			$res .=
			'<li><a href="'.$core->blog->url.$core->url->getBase('author').'/'.
			$rs->user_id.'">'.
			html::escapeHTML(
				dcUtils::getUserCN($rs->user_id, $rs->user_name,
					$rs->user_firstname, $rs->user_displayname)).'</a>'.
			($w->postcount ? ' ('.$rs->nb_post.')' : '').
			'</li>';
		}
		$res .= '</ul></div>';
		
		return $res;
	}


	public static function init(&$w)
	{
	    $w->create('authors',__('Authors'),array('widgetsAuthorMode','authors'));
	    $w->authors->setting('title',__('Title:'),__('Authors'));
	    $w->authors->setting('archiveonly',__('Archives only'),1,'check');
	    $w->authors->setting('postcount',__('With entries counts'),0,'check');
	}
}
?>