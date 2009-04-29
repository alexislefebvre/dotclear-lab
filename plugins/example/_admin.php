<?php
/***** BEGIN LICENSE BLOCK *****
Copyright (c) 2009, <Dotclear Lab Contributors>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

1. Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above
copyright notice, this list of conditions and the following
disclaimer in the documentation and/or other materials provided
with the distribution.
3. The name of the author may not be used to endorse or promote
products derived from this software without specific prior written
permission.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS
OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	
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