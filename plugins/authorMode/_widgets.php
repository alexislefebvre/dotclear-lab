<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

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
		
		switch ($core->url->type)
		{
			case 'post' :
				$currentuser = $GLOBALS['_ctx']->posts->user_id;
				break;
			case 'author' :
				$currentuser = $GLOBALS['_ctx']->users->user_id;
				break;
			default :
				$currentuser = '';
		}
		
		$res =
		'<div id="authors">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<ul>';
		
		while ($rs->fetch()) {
			$res .= '<li'.
			($rs->user_id == $currentuser ? ' class="current-author"' : '').
			'><a href="'.$core->blog->url.$core->url->getBase('author').'/'.
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
