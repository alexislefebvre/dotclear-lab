<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin feedburner for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

$core->url->register('feedburnerStatsExport','feedburnerStatsExport','^feedburner/stats/export$',array('feedburnerUrl','export'));

class feedburnerUrl
{
	public static function export($args)
	{
		require dirname(__FILE__).'/inc/amstock/export.php';
		exit;
	}
}

/**
 * Class feedburnerPublic
 */
class feedburnerPublic
{
	/**
	 * Returns the public widget
	 *
	 * @param	objet	w
	 *
	 * @return	string
	 */
	public static function widget(&$w)
	{
		global $core;

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$fb = new feedburner($core);
		$fb->check($w->feed_id,'details');
		$datas = $fb->getDatas();

		if (count($fb->getErrors()) > 0) { return; }

		$text = str_replace(array('%readers%','%clics%'),array('%1$s','%2$s'),$w->text);

		$res =
			'<div id="feedburner">'.
			'<h2>'.$w->title.'</h2>'.
			'<p>'.sprintf($text,$datas[0]['circulation'],$datas[0]['hits']).'</p>'.
			'<p><a href="http://feeds.feedburner.com/'.
			$w->feed_id.'">'.$w->sign_up.'</a></p>';
			
		if ($w->feed_int_id && is_numeric($w->feed_int_id)) {
			$res .=
				'<a href="http://www.feedburner.com/fb/a/emailverifySubmit?feedId='.$w->feed_int_id.'">'.
				$w->sign_up.' '.__('by mail').'</a>';
		}
		
		$res .= '</div>';

		return $res;
	}

}

?>
