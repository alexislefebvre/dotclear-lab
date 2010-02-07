<?php 
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of TinyMCE, a plugin for Dotclear 2
# Copyright 2010 Moe (http://gniark.net/)
#
# TinyMCE is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# TinyMCE is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public
# License along with this program. If not, see
# <http://www.gnu.org/licenses/>.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) {exit;}

# post
$core->addBehavior('adminPostNavLinks',
	array('tinyMceAdmin','adminPostNavLinks'));

# page

$core->addBehavior('adminPageNavLinks',
	array('tinyMceAdmin','adminPageNavLinks'));
 
class tinyMceAdmin
{
	public static function adminPostNavLinks($post)
	{
		# don't display anything if this is a new post
		$post_title = $post->post_title;
		if (!isset($post_title)) {return;}
 
		echo('<p>'.
			'<a href="plugin.php?p=tinyMce&amp;type=post&amp;id='.$post->post_id.'"'.
			' class="button" id="tinyMce">'.__('Edit this post with TinyMCE').'</a>'.
			'</p>');
	}
	
	public static function adminPageNavLinks($post)
	{
		# don't display anything if this is a new page
		$post_title = $post->post_title;
		if (!isset($post_title)) {return;}
 
		echo('<p>'.
			'<a href="plugin.php?p=tinyMce&amp;type=page&amp;id='.$post->post_id.'"'.
			' class="button" id="tinyMce">'.__('Edit this post with TinyMCE').'</a>'.
			'</p>');
	}
}

?>