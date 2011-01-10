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

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicFooterContent',array('myLocationBehaviors','publicFooterContent'));
$core->addBehavior('templateBeforeBlock',array('myLocationBehaviors','templateBeforeBlock'));
$core->addBehavior('coreBeforeCommentCreate',array('myLocationBehaviors','coreBeforeCommentCreate'));
$core->addBehavior('coreBlogGetComments',array('myLocationBehaviors','coreBlogGetComments'));

if ($core->blog->settings->myLocation->position === 'afterContent') {
	$core->addBehavior('publicCommentAfterContent',array('myLocationBehaviors','publicCommentAfterContent'));
}
else {
	$core->addBehavior('templateAfterValue',array('myLocationBehaviors','templateAfterValue'));
}

class myLocationBehaviors
{
	public static function publicFooterContent($core,$_ctx)
	{
		$js = $core->blog->getQMarkURL().'pf='.basename(dirname(__FILE__)).'/js/post.js';
		$css =
			$core->blog->settings->myLocation->css === '' ? 
			$core->blog->getQMarkURL().'pf='.basename(dirname(__FILE__)).'/style.css' : 
			$core->blog->settings->myLocation->css;
		
		echo $core->blog->settings->myLocation->enable ?
		'<link rel="stylesheet" media="screen" type="text/css" href="'.$css.'" />'."\n".
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
		'</script>'."\n".
		'<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>'."\n".
		'<script type="text/javascript" src="'.$js.'"></script>'."\n" : '';
	}
	
	public static function templateBeforeBlock($core,$tag,$attr)
	{
		if ($tag === 'Comments') {
			return '<?php $params["columns"][] = "comment_location"; ?>';
		}
	}
	
	public static function publicCommentAfterContent($core,$_ctx)
	{
		if ($_ctx->comments->hasLocation()) {
			echo '<p class="comment-location">'.$_ctx->comments->getLocation().'</p>';
		}
	}
	
	public static function templateAfterValue($core,$tag,$attr)
	{
		$fit_tag = $core->blog->settings->myLocation->position;
		
		if ($tag === $fit_tag) {
			return
			"<?php\n".
			'if ($_ctx->comments->hasLocation()) {'."\n".
				'echo \'&nbsp;<span class="comment-location">\'.$_ctx->comments->getLocation().\'</span>\';'."\n".
			'}'."\n".
			"?>";
		}
	}
	
	public static function coreBeforeCommentCreate($blog,$cur)
	{
		$location = array(
			'longitude' => $_POST['c_location_longitude'],
			'latitude' => $_POST['c_location_latitude'],
			'address' => $_POST['c_location_address']
		);
		$cur->comment_location = serialize($location);
	}
	
	public static function coreBlogGetComments($rs)
	{
		$rs->extend('rsExtCommentLocation');
	}
}

?>