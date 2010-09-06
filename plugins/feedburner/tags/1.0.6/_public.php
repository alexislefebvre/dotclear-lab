<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of feedburner, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('publicBeforeDocument',array('feedburnerBehaviors','redirect'));

class feedburnerBehaviors
{
	public static function redirect()
	{
		global $core;

		$feeds = unserialize($core->blog->settings->feedburner->feedburner_feeds);

		preg_match('#^.*('.$core->url->getBase('feed').')/(rss2|atom)?/?(comments)?$#',$_SERVER['REQUEST_URI'],$matches);

		$k = isset($matches[2]) ? $matches[2].(isset($matches[3]) ? '_'.$matches[3]: '') : '';

		if (array_key_exists($k,$feeds) && !empty($feeds[$k]) && !preg_match('#feedburner#i',$_SERVER['HTTP_USER_AGENT'])) {
			http::redirect($core->blog->settings->feedburner->feedburner_base_url.$feeds[$k]);
		}
	}
}

class feedburnerUrl
{
	public static function export($args)
	{
		require dirname(__FILE__).'/inc/amstock/export.php';
		exit;
	}
}

class feedburnerPublic
{
	/**
	 * Returns the public widget
	 *
	 * @param	objet	w
	 *
	 * @return	string
	 */
	public static function widget($w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$fb = new feedburner($core);
		$fb->check($w->feed_id,'details');
		$datas = $fb->getDatas();

		$text = str_replace(array('%readers%','%clics%'),array('%1$s','%2$s'),$w->text);
		
		$title = strlen($w->title) > 0 ? '<h2>'.$w->title.'</h2>' : '';

		$res =
			'<div id="feedburner">'.
			$title.
			(count($fb->getErrors()) > 0 ? '' : 
			'<p>'.sprintf($text,$datas[0]['circulation'],$datas[0]['hits']).'</p>').
			'<p><a href="http://feeds.feedburner.com/'.
			$w->feed_id.'">'.$w->sign_up.'</a></p>';
			
		if ($w->email) {
			$res .=
				'<p><a href="http://feedburner.google.com/fb/a/mailverify?uri='.$w->feed_id.'">'.
				$w->sign_up.' '.__('by mail').'</a></p>';
		}
		
		$res .= '</div>';

		return $res;
	}

}

?>