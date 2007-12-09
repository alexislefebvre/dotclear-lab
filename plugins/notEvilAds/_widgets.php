<?php
/***************************************************************\
 *  This is 'Not Evil Ads', a plugin for Dotclear 2            *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along 'Not evil ads' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

include_once dirname(__FILE__).'/lib/class.notevilads.php';

$core->addBehavior('initWidgets',array('notEvilAdsBehaviors','addAd'));
$core->addBehavior('initWidgets',array('notEvilAdsBehaviors','addTrigger'));

class notEvilAdsBehaviors
{
	public static function addAd(&$w)
	{
		global $core;

		$nea_ads = notEvilAds::loadAds($core->blog->settings->get('nea_ads'));
		if (!$nea_ads)
			return;
		
		$options = array();
		foreach ($nea_ads as $k=>$v)
		{
			$options[$k] = $v['identifier'];
		}
		
		$w->create('notEvilAds',__('Not Evil Ads'),
			array('publicNotEvilAds','showAdsInWidgets'));
		$w->notEvilAds->setting('identifier',__('Select the ad to show:'),
			null,'combo',$options);
	}
	
	public static function addTrigger(&$w)
	{
		$w->create('notEvilAdsTrigger',__('Not Evil Ads hide button'),
			array('publicNotEvilAds','triggerAdsInWidgets'));
		
		$w->notEvilAdsTrigger->setting('hValue',__('Hide button value:'),
			__('Hide ads'),'text');
		$w->notEvilAdsTrigger->setting('sValue',__('Show button value:'),
			__('Show me ads'),'text');
		$w->notEvilAdsTrigger->setting('attr',__('Extra DIV attributes:'),
			'style="text-align:center;"','text');
	}
}
?>
