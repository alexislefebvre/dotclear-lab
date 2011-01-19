<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of myLocation, a plugin for Dotclear.
#
# Copyright (c) 2010 Tomtom and contributors
# http://blog.zenstyle.fr/
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('myLocation'),'plugin.php?p=myLocation','index.php?pf=myLocation/icon.png',
	preg_match('/plugin.php\?p=myLocation(&.*)?$/',$_SERVER['REQUEST_URI']),
	$core->auth->check('admin',$core->blog->id));

$core->addBehavior('adminPostHeaders',array('myLocationBehaviors','adminHeaders'));
$core->addBehavior('adminPageHeaders',array('myLocationBehaviors','adminHeaders'));

class myLocationBehaviors
{
	public static function adminHeaders()
	{
		global $core;
		
		return
		dcPage::jsLoad('index.php?pf=myLocation/js/post.js').
		'<script type="text/javascript">'."\n".
		'//<![CDATA['."\n".
		'var post_location_checkbox = "'.__('Add my location').'";'."\n".
		'var post_location_search = "'.__('Searching...').'";'."\n".
		'var post_location_error_denied = "'.__('Permission denied by your browser').'";'."\n".
		'var post_location_error_unavailable = "'.__('You location is currently unavailable. Please, try later').'";'."\n".
		'var post_location_error_accuracy = "'.__('You location is currently unavailable for the choosen accuracy').'";'."\n".
		'var post_location_longitude = "'.(isset($_POST['c_location_longitude']) ? $_POST['c_location_longitude'] : '').'";'."\n".
		'var post_location_latitude = "'.(isset($_POST['c_location_latitude']) ? $_POST['c_location_latitude'] : '').'";'."\n".
		'var post_location_address = "'.(isset($_POST['c_location_address']) ? $_POST['c_location_address'] : '').'";'."\n".
		'var post_location_accuracy = "'.$core->blog->settings->myLocation->accuracy.'";'."\n".
		'//]]>'."\n".
		'</script>'."\n";
	}
}

?>