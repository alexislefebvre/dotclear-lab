<?php

$core->addBehavior('publicHeadContent',array('publicTweet','publicHeadContent'));

class publicTweet
{
	public static function publicHeadContent(&$core)
	{
	/*	if (!$core->blog->settings->lightbox_enabled) {
			return;
		}*/
		
		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		$count = abs((integer) $w->count);
		if($count > 100) $count = 100;
		if($count < 1) $count = 1;

		echo
		'<style type="text/css">'."\n".
		'@import url('.$url.'/css/jquery.tweet.css);'."\n".
		'@import url('.$url.'/css/jquery.twwet.query.css);'."\n".
		"</style>\n".
		'<script type="text/javascript" src="'.$url.'/js/jquery.tweet.js"></script>'."\n";
	}
	public static function divTweet(&$w)
	{
		global $core;
		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
// 		$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
		
		/*$ignoreReplies = $w->ignoreReplies ? 'true' : 'false';
		$enableLinks = $w->enableLinks ? 'true' : 'false';
		$count = abs((integer) $w->count);
		if($count > 100) $count = 100;
		if($count < 1) $count = 1;*/
		
		$res =  '<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			  '$(document).ready(function(){'."\n".
			  '$("';
		$res .=  $w->divClass != '' ? '.'.$w->divClass : '.tweet';
		$res .=  '").tweet({'."\n";
		if ($w->queryType == 1) 
			 {
			 $res .= 'username: ['.$w->queryValue.'],'."\n";
			 }
			 
		elseif ($w->queryType == 2)
			 {
			 $res .= 'username: "'.$w->queryValue.'",'."\n".
			 	 'list: "'.$w->list.'",'."\n";
			 }
		else
			 {
			 $res .= 'query: "'.$w->queryValue.'",'."\n";
			 }
		$res .=	  'join_text: "auto",'."\n".
			  'avatar_size: '.$w->avatarSize.','."\n".
			  'count: '.$w->count.','."\n".
			  'auto_join_text_default: "'.$w->defaultText.'",'."\n".
			  'auto_join_text_ed: "'.$w->defaultText.'",'."\n".
			  'auto_join_text_ing: "'.$w->defaultText.'",'."\n".
			  'auto_join_text_reply: "'.$w->replyText.'",'."\n".
			  'auto_join_text_url: "'.$w->defaultText.'",'."\n".
			  'loading_text: "'.$w->loadText.'",'."\n".
			  'text_less_min: "'.$w->lessMin.'",'."\n".
			  'text_one_min: "'.$w->oneMin.'",'."\n".
			  'text_n_mins: "'.$w->nMins.'",'."\n".
			  'text_one_hour: "'.$w->oneHour.'",'."\n".
			  'text_n_hours: "'.$w->nHours.'",'."\n".
			  'text_one_day: "'.$w->oneDay.'",'."\n".
			  'text_n_days: "'.$w->nDays.'"'."\n".
			  "});\n".
			"});\n".
			"\n//]]>\n".
			"</script>\n".
			'<div class="tweet';
		$res .= $w->divClass !='' ? ' '.$w->divClass : '';
		$res .= '">'.
			($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
			'</div>';
			
		return $res;
	}

}
?>
