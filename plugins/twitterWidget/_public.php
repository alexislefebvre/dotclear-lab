<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of twitterWidget, a plugin for Dotclear.
# 
# Copyright (c) 2008 annso and contributors
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

require dirname(__FILE__).'/_widgets.php';

$core->addBehavior('publicHeadContent',
	array('publicTwitterWidget','publicHeadContent'));

class publicTwitterWidget
{
	public static function publicHeadContent($core)
	{
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo '<script type="text/javascript" src="'.$url.'/js/twitter-1.13.min.js"></script>'."\n";
	}
	
	public static function getTweets($w)
	{
		global $core;
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$id_of_container = $w->username.'_tweets';
		
		$num_tweets = abs((integer)$w->count);
		if ($num_tweets > 20) $num_tweets = 20;
		if ($num_tweets < 1) $num_tweets = 1;
		
		$ignore_replies = $w->ignorereplies ? 'true' : 'false';
		$enable_links = $w->enablelinks ? 'true' : 'false';
		
		$res =
		'<div class="twitter">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '');
		
		$res .=
		'<div id="'.$id_of_container.'">'.
		'<p>Loading tweets '.
		'<img src="'.$url.'/img/ajax-loader.gif" alt="Loading" />'.
		'</p></div>';
		
		# Call of getTwitters
		$res .= "\n".
		'<script type="text/javascript">/*<![CDATA[*/'.
		'getTwitters(\''.$id_of_container.'\',{'.
			'id: \''.$w->username.'\','.
			'count: '.$num_tweets.','.
			'prefix: \''.$w->prefix.'\','.
			'clearContents: true,'.
			'ignoreReplies: '.$ignore_replies.',' .
			'template: \''.$w->template.'\','.
			'enableLinks: '.$enable_links.','.
			'newwindow: false'.
		'});'.
		'/*]]>*/</script>'."\n";
		
		# End of div.twitter
		$res .= '</div>';
		
		return $res;
	}
}
?>