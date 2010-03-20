<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of twitterWidget, a plugin for Dotclear.
# 
# Copyright (c) 2008 - annso
# contact@as-i-am.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

$core->addBehavior('publicHeadContent',array('publicTwitterWidget','publicHeadContent'));

if (!defined('DC_RC_PATH')) { return; }
 
class publicTwitterWidget
{

	public static function publicHeadContent(&$core)
	{
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		echo '<script type="text/javascript" charset="utf-8" src="'.$url.'/js/twitter-1.12.2.min.js"></script>'."\n";
	}
	
	
        public static function getTweets(&$w)
	{
		global $core;
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		$ignoreReplies = $w->ignoreReplies ? 'true' : 'false';
		$enableLinks = $w->enableLinks ? 'true' : 'false';
		$count = abs((integer) $w->count);
		if($count > 20) $count = 20;
		if($count < 1) $count = 1;
		
		$affTitle = '';
        if ( $w->blogTitle != "" ) { $affTitle = '<h2>'.$w->blogTitle.'</h2>'."\n"; }
        
		$res =  
			"\n".
			'<div id="twitterList">'."\n".
			$affTitle.
			'<div id="'.$w->userName.'">'."\n".
			'<p>Loading tweets <img src="'.$url.'/img/ajax-loader.gif" width="16" height="16" alt="loading..." /></p>'."\n".
			'</div>'."\n".
			'<script type="text/javascript">'."\n".
			'<!--'."\n".
			'getTwitters(\''.$w->userName.'\', { '."\n".
			'	id: \''.$w->userName.'\', '."\n".
			'	count: '.$count.', '."\n".
			'	enableLinks: true, '."\n".
			'	ignoreReplies: '.$ignoreReplies.', ' ."\n".
			'	clearContents: true, '."\n".
			'	template: \''.$w->template.'\','."\n".
			'	prefix: \''.$w->prefix.'\''."\n".
			'});'."\n".
			'//-->'."\n".
			'</script>'."\n".
			'</div>'."\n";
			
		return $res;
	}
}
?>
