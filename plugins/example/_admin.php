<?php
/***** BEGIN LICENSE BLOCK *****
This program is free software. It comes without any warranty, to
the extent permitted by applicable law. You can redistribute it
and/or modify it under the terms of the Do What The Fuck You Want
To Public License, Version 2, as published by Sam Hocevar. See
http://sam.zoy.org/wtfpl/COPYING for more details.
	
Icon (icon.png) is from Silk Icons :
	http://www.famfamfam.com/lab/icons/silk/

***** END LICENSE BLOCK *****/

if (!defined('DC_CONTEXT_ADMIN')) {return;}

# add the plugin in the plugins list
$_menu['Plugins']->addItem(
	# name of the link
	__('Example'),
	# base URL of the plugin's page
	'plugin.php?p=example',
	# image displayed as an icon
	'index.php?pf=example/icon.png',
	# regex URL of the plugin's page
	preg_match('/plugin.php\?p=example(&.*)?$/',
		$_SERVER['REQUEST_URI']),
	# check permissions for this plugin
	$core->auth->check('usage,contentadmin',$core->blog->id));

# adminPostFormSidebar displays what you want when editing an entry
$core->addBehavior('adminPostFormSidebar',
	array('exampleAdmin','adminPostFormSidebar'));

/**
@ingroup Example
@brief Admin
*/
class exampleAdmin
{
	/**
	adminPostFormSidebar behavior
	@param	post	<b>cursor</b>	Post
	*/
	public static function adminPostFormSidebar(&$post)
	{
		echo
		'<div id="planet-infos">'.'<h3>'.('Example').'</h3>'.
		'<p>'.
			sprintf('This post title is <strong>%s</strong>',
				$post->post_title).
		'</p>'.
		'</div>';
	}
}

?>