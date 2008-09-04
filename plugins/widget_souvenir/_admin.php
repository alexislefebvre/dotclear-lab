<?php
$core->addBehavior('initWidgets',array('souvenirBehaviors','initWidgets'));
 
class souvenirBehaviors
{
	public static function initWidgets(&$w)
	{
		global $core;

		$w->create('souvenir',__('Souvenir'),array('publicSouvenir','show'));

		$w->souvenir->setting('title',__('Title:').' ('.__('optional').')',__('One year ago'),'text');

		$array_intervals = array();
		$array_intervals[__('1 month ago')] = 1;
		for ($i = 2;$i <= 11;$i++)
		{
			$array_intervals[$i.' '.__('months ago')] = $i;
		}
		$array_intervals[__('1 year ago')] = 12;
		
		$w->souvenir->setting('interval',__('Show a link to a post published:'),12,'combo',$array_intervals);

		$array_range = array();
		for ($i = 0;$i <= 31;$i++)
		{
			$array_range[$i] = $i;
		}
		$w->souvenir->setting('range',__('Maximum number of days before of after the date in the past:'),7,'combo',$array_range);

		$w->souvenir->setting('truncate',__('Number of characters of the post title to display (empty means no limit):'),null,'text');

		$w->souvenir->setting('date',__('Display date after post title (see PHP strftime function):').' ('.__('optional').')','('.$core->blog->settings->date_format.')','text');
	}
}
?>