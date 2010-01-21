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

# replace default page
//$core->setPostType('post','plugin.php?p=tinyMce&id=%d',$core->url->getBase('post').'/%s');

$core->addBehavior('adminPostHeaders',
	array('tinyMceAdmin','postHeaders'));

$core->addBehavior('adminPostNavLinks',
	array('tinyMceAdmin','adminPostNavLinks'));
 
class tinyMceAdmin
{
	public static function postHeaders()
	{
		global $core;
		
		return;
		return
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('dotclear.msg.confirm_tinyMce',
  	__('Are you sure you want to convert this post to XHTML?')).
		'$(function() {'.
		'$("#tinyMce").click(function() {'.
			'return window.confirm(dotclear.msg.confirm_tinyMce);'.
		'});'.
		'});'.
		"\n//]]>\n".
		"</script>\n";
	}
	
	public static function adminPostNavLinks($post)
	{
		# don't display anything if this is a new post
		$post_title = $post->post_title;
		if (!isset($post_title)) {return;}
 
		echo('<p>'.
			'<a href="plugin.php?p=tinyMce&amp;id='.$post->post_id.'"'.
			' class="button" id="tinyMce">'.__('Edit this post with TinyMCE').'</a>'.
			'</p>');
	}
}

?>