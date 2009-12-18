<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of walouwalou, a theme for Dotclear 2.
# 
# Copyright (c) 2009 Osku
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');

function noviny_guess_url($url)
{
	global $core;
	
	if (preg_match('/^'.preg_quote($core->blog->url,'/').'/',$url)) {
		return preg_replace('/^'.preg_quote($core->blog->url,'/').'/','',$url);
	}
	
	return $url;
}

$walou_style = array(
	__('Default') => 'default',
	__('Monochrome') => 'grey',
	__('Gold') => 'gold',
	__('Pastel') => 'pastel'
);

$walou_gravatar_on = (boolean)$core->blog->settings->walou_gravatar_on;

$walouwalou_nav = array();
if ($core->blog->settings->walouwalou_nav) {
	$walouwalou_nav = @unserialize($core->blog->settings->walouwalou_nav);
}

if (!is_array($walouwalou_nav)) {
	$walouwalou_nav = array();
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
		
		$walouwalou_nav = $new_nav;
		
	}
	
	if (!empty($_POST['new_title']) && isset($_POST['new_url']))
	{
		$walouwalou_nav[] = array(
			$_POST['new_title'],
			noviny_guess_url($_POST['new_url'])
		);
		
		
	}

	$core->blog->settings->setNameSpace('walouwalou');
	if (!empty($_POST['walou_style']) && in_array($_POST['walou_style'],$walou_style))
	{
		$core->blog->settings->walou_style = $_POST['walou_style'];
		$core->blog->settings->put('walou_style',$core->blog->settings->walou_style,'string','Walou-walou theme style',true);
	}
	
	$walou_gravatar_on = (empty($_POST['walou_gravatar_on']))?false:true;
	$core->blog->settings->put('walou_gravatar_on',$walou_gravatar_on,'boolean','Walou-walou gravatars flag');
	
	$core->blog->settings->put('walouwalou_nav',serialize($walouwalou_nav),'string');
	$core->blog->triggerBlog();
	 echo '<p class="message">'.__('Theme configuration has been successfully updated.').'</p>';
}

echo '<fieldset><legend>Style</legend>'.
'<p class="field"><label>'.__('Style:').' '.
form::combo('walou_style',$walou_style,$core->blog->settings->walou_style).
'</p>'.
'</fieldset>';

echo '<fieldset><legend>'.__('Gravatar').'</legend>'.
'<p class="field"><label>'.__('Show gravatars:').' '.
form::checkbox('walou_gravatar_on', 1, $walou_gravatar_on).
'</p>'.
'</fieldset>';

echo '<fieldset><legend>'.__('Navigation bar').'</legend>';

foreach ($walouwalou_nav as $i => $v)
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