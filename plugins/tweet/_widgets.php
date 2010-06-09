<?php
 
$core->addBehavior('initWidgets',
	array('tweetWidgets','initWidgets'));
 
class tweetWidgets
{
	public static function initWidgets(&$w)
	{
		$w->create('Tweet','Tweet!',
			array('publicTweet','divTweet'));
			
		$w->Tweet->setting('title',__('Title'),'Tweet!');
		$w->Tweet->setting('divClass',__('Div’s class (needed to display several widgets with different contents):'),'tweet');
		$w->Tweet->setting('queryType',__('Query type:'),null,'combo',array(__('user(s)') => 1, __('list') => 2, __('search') => 3),1);
		$w->Tweet->setting('queryValue',__('– user(s): Users names or IDs, comma separated').'<br />'.__('– search: search string').'<br />'.__('– list: name or ID of the list owner'),'seaofclouds');
		$w->Tweet->setting('list',__('If list, list name:'),'');
		$w->Tweet->setting('count',__('Tweets to display (1 to 100):'),'3','text');
		$w->Tweet->setting('avatarSize',__('Avatar size (void not to display avatars, 48 max):'),'30','text');
		$w->Tweet->setting('defaultText',__('Default introducing text (%u will display user’s name):'),__('I said'));
		$w->Tweet->setting('replyText',__('Reply text intro:'),__('I replied to'));
		$w->Tweet->setting('lessMin',__('More recent than a minute:'),__('less than a minute ago'));
		$w->Tweet->setting('oneMin',__('Between one and two minutes:'),__('about a minute ago'));
		$w->Tweet->setting('nMins',__('Between two minutes and one hour:'),__('%t minutes ago'));
		$w->Tweet->setting('oneHour',__('Between one and two hours:'),__('about an hour ago'));
		$w->Tweet->setting('nHours',__('Less than a day old:'),__('about %t hours ago'));
		$w->Tweet->setting('oneDay',__('Between 24 and 48 hours old:'),__('1 day ago'));
		$w->Tweet->setting('nDays',__('More than two days old:'),__('%t days ago'));
		$w->Tweet->setting('homeonly',__('Home page only'),1,'check');
	}
}
?>