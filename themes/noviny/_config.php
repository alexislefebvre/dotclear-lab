<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Noviny, a Dotclear 2 theme.
#
# Copyright (c) 2003-2008 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

function noviny_guess_url($url)
{
	global $core;
	
	if (preg_match('/^'.preg_quote($core->blog->url,'/').'/',$url)) {
		return preg_replace('/^'.preg_quote($core->blog->url,'/').'/','',$url);
	}
	
	return $url;
}

$noviny_nav = array();
if ($core->blog->settings->noviny_nav) {
	$noviny_nav = @unserialize($core->blog->settings->noviny_nav);
}

if (!is_array($noviny_nav)) {
	$noviny_nav = array();
}

if (!empty($_POST))
{
	if (!empty($_POST['nav_title']) && !empty($_POST['nav_url']) && !empty($_POST['nav_pos']))
	{
		$new_nav = array();
		$nav_title = $_POST['nav_title'];
		$nav_url = $_POST['nav_url'];
		$nav_pos = $_POST['nav_pos'];
		
		asort($nav_pos);
		foreach ($nav_pos as $i => $v) {
			if (empty($nav_title[$i]) || !isset($nav_url[$i])) {
				continue;
			}
			$new_nav[] = array(
				$nav_title[$i],
				noviny_guess_url($nav_url[$i])
			);
		}
		
		$noviny_nav = $new_nav;
	}
	
	if (!empty($_POST['new_title']) && isset($_POST['new_url']))
	{
		$noviny_nav[] = array(
			$_POST['new_title'],
			noviny_guess_url($_POST['new_url'])
		);
		
		
	}
	$core->blog->settings->setNameSpace('noviny');
	$core->blog->settings->put('noviny_nav',serialize($noviny_nav),'string');
	$core->blog->triggerBlog();
}



echo '<fieldset><legend>'.__('Navigation bar').'</legend>';

foreach ($noviny_nav as $i => $v)
{
	if ($i == 0) {
		echo '<h4>'.__('Edit navigation items').'</h4>';
	}
	
	echo
	'<p><label class="classic">'.__('Title:').' '.
	form::field(array('nav_title['.$i.']'),15,90,html::escapeHTML($v[0])).'</label> '.
	'<label class="classic">'.__('Link:').' '.
	form::field(array('nav_url['.$i.']'),30,120,html::escapeHTML($v[1])).'</label> '.
	'<label class="classic">'.__('Position:').' '.
	form::field(array('nav_pos['.$i.']'),2,3,(string) $i).'</label></p>';
}

echo
'<h4>'.__('Add a navigation item').'</h4>'.
'<p><label class="classic">'.__('Title:').' '.
form::field(array('new_title'),15,90,'').'</label> '.
'<label class="classic">'.__('Link:').' '.
form::field(array('new_url'),30,120,'').'</label></p>';


echo '</fieldset>';
?>