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

class feedburnerUi
{
	/**
	 * Diplays feeds table and form
	 *
	 * @param	array	feeds
	 * @param	string	url
	 *
	 * @return	string
	 */
	public static function feedsTable($feeds,$url)
	{
		global $core;

		$res =
			'<form action="'.$url.'" method="post">'.
			'<table summary="feeds" class="maximal">'.
			'<thead>'.
			'<tr>'.
			'<th class="nowrap">'.__('Feed ID').'</th>'.
			'<th>'.__('Feed URL').'</th>'.
			'<th class="nowrap">'.__('View feed').'</th>'.
			'</tr>'.
			'</thead>'.
			'<tbody>';

		foreach($feeds as $k => $v)
		{
			if ($k == 'rss2') {
				$label = __('RSS entries feed');
				$type = 'rss2';
			}
			if ($k == 'rss2_comments') {
				$label = __('RSS comments feed');
				$type = 'rss2/comments';
			}
			if ($k == 'atom') {
				$label = __('ATOM entries feed');
				$type = 'atom';
			}
			if ($k == 'atom_comments') {
				$label = __('ATOM comments feed');
				$type = 'atom/comments';
			}

			$res .=
				'<tr class="line wide" id="feed_'.$k.'">'.
				'<td class="minimal">'.
				'<strong>'.$k.'</strong>'.
				'</td>'."\n".
				'<td class="maximal">'.
				sprintf(__('Enter feed url for the %s : %s'),$label,$core->blog->settings->feedburner->feedburner_base_url).
				form::field(array($k),30,255,$v).
				'<p class="fb-note">'.sprintf(
					__('You have to set source on feedburner website at this URL: %s'),
					'<strong>'.$core->blog->url.$core->url->getBase("feed").'/'.$type.'</strong>'
				).'</p>'.
				'</td>'."\n".
				'<td class="minimal">';
			$res .= !empty($v) ?
				'<a href="'.$core->blog->settings->feedburner->feedburner_base_url.$v.'">'.
				'<img src="index.php?pf=feedburner/feed.png" alt="'.$k.'" title="'.$v.'" /></a>'
				: '';
			$res .=
				'</td>'."\n".
				'</tr>'."\n";
		}

		$res .=
			'</tbody>'.
			'</table>'.
			'<p class="form-note">'.__('NOTICE: You have to enable the AWARENESS API option for ALL feeds to get statistics').'</p>'.
			'<p class="col right">'.
			$core->formNonce().
			'<input type="submit" value="'.__('Save setup').'" name="save" /></p>'.
			'</form>';

		echo $res;
	}

	/**
	 * Displays form to select statistics to display
	 *
	 * @param	array	feeds
	 * @param	string	url
	 *
	 * @return	string
	 */
	public static function statsForm($feeds,$url)
	{
		global $core;

		$res[__('-- Activated feeds --')] = '';
		$default = !empty($_GET['id']) ? $_GET['id'] : '';

		foreach ($feeds as $k => $v) {
			if (!empty($v)) $res[$v] = $v;
		}

		echo
			'<fieldset><legend>'.__('Statistics').
			'</legend>'.
			'<form method="get" action="'.$url.'">'.
			form::hidden('p','feedburner').
			form::hidden('tab','stats').
			'<p><label class="classic">'.__('Choose the feed:').' '.
			form::combo(array('id'),$res,$default).
			'</label> '.
			'<input name="view" type="submit" value="'.__('View').'" /></p>'.
			'</form>'.
			'</fieldset>';
	}

	/**
	 * Displays all statistics
	 */
	public static function statsView()
	{
		if (empty($_GET['view'])) { return; }
		elseif(!empty($_GET['id'])) {
			feedburnerUi::statsDayView();
			feedburnerUi::statsAllView();
		}
		else { return; }
	}
	
	/**
	 * Displays daily table statistics
	 */
	public static function statsDayView()
	{
		global $core,$fb,$nb_per_page,$p_url,$page;

		$id = html::escapeHTML($_GET['id']);

		$fb->check($id,'details');
		$datas = $fb->getDatas();
		$errors = $fb->getErrors();

		$date = isset($datas[0]['date']) ? dt::str($core->blog->settings->system->date_format,strtotime($datas[0]['date'])) : __('your feed');

		echo '<h2>'.sprintf(__('Statistics of %s - Global'),$date).'</h2>';

		if (count($errors) == 0) {
			echo	isset($datas[0]['circulation']) ? '<h3>'.sprintf(__('Number of readers: %s'),$datas[0]['circulation']).'</h3>' : '';
			echo	isset($datas[0]['hits']) ? '<h3>'.sprintf(__('Number of feed calling: %s'),$datas[0]['hits']).'</h3>' : '';
			echo	isset($datas[0]['reach']) ? '<h3>'.sprintf(__('Read rate : %s%%'),$datas[0]['reach']).'</h3>' : '';
		}

		echo self::getErrors($errors);

		echo '<h2>'.sprintf(__('Statistics of %s - Details'),$date).'</h2>';

		if (count($errors) == 0) {
			$datas = isset($datas[0]['item']) ? $datas[0]['item'] : array();
			$fd_p_nb = count($datas);
			$fd_p_rs = staticRecord::newFromArray($datas);
			$fd_p_list = new feedburnerList($core,$fd_p_rs,$fd_p_nb);

			$p_url = $p_url.'&amp;tab=stats&amp;id='.$id.'&amp;view='.__('View');

			$fd_p_list->display($page,$nb_per_page,$p_url);
		}
		else {
			echo self::getErrors($errors);
		}
	}

	/**
	 * Displays details statistics
	 */
	public static function statsAllView()
	{
		global $p_url;

		$id = html::escapeHTML($_GET['id']);

		echo
			'<h2>'.__('Global statistics').'</h2>'."\n".
			'<script type="text/javascript" src="'.$p_url.'&amp;file=swfobject.js"></script>'."\n".
			'<div id="flashcontent">'."\n".
			'<strong>'.__('You need to upgrade your Flash Player').'</strong>'."\n".
			'</div>'."\n".
			'<script type="text/javascript">'."\n".
			'// <![CDATA['."\n".
			'var so = new SWFObject("'.$p_url.'&file=amstock.swf", "amstock", "800", "600", "8", "#FFFFFF");'."\n".
			'so.addVariable("settings_file", encodeURIComponent("'.$p_url.'&file=amstock_settings.php&id='.$id.'"));'."\n".
			'so.write("flashcontent");'."\n".
			'// ]]>'."\n".
			'</script>';
	}
	
	
	/**
	 * Returns feedburner API's errors
	 */
	public static function getErrors($errors)
	{
		$res = '';
		
		foreach ($errors as $k => $v) {
			$res .= '<h3>'.sprintf(__('Error %1$s : %2$s'),$k,text::toUTF8($v)).'</h3>';
		}
	
		return $res;
	}
}

/**
 * Class feedburnerList
 */
class feedburnerList extends adminGenericList
{
	/**
	 * Displays data table for feedburner lists
	 *
	 * @param	int		page
	 * @param	int		nb_per_page
	 * @param	string	url
	 */
	public function display($page,$nb_per_page,$url)
	{
		$html_block =
			'<table summary="details" class="maximal">'.
			'<thead>'.
			'<tr>'.
			'<th>'.__('Read items').'</th>'.
			'<th class="nowrap">'.__('Item views').'</th>'.
			'<th class="nowrap">'.__('Click throughs').'</th>'.
			'</tr>'.
			'</thead>'.
			'<tbody>%s</tbody>'.
			'</table>';

		if ($this->rs->isEmpty()) {
			echo '<p><strong>'.__('No detail statistics').'</strong></p>';
		}
		else {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->base_url = $url.'&amp;page=%s';

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			$blocks = explode('%s',$html_block);
			echo $blocks[0];

			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page) {
				echo $this->dayLine($url);
				if ($this->rs->isEnd()) {
					break;
				}
				else {
					$this->rs->moveNext();
					$iter++;
				}
			}
			echo $blocks[1];
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	/**
	 * Returns day row
	 *
	 * @param	string	url
	 *
	 * @return	string
	 */
	private function dayLine($url)
	{
		$referrers = '';

		foreach ($this->rs->referrer as $ref)
		{
			$referrers .=
				'<li>'.
				(preg_match('#^http://.*$#',$ref['url']) ?
				'<a href="'.
				html::escapeHTML($ref['url']).'">'.
				html::escapeHTML($ref['url']).
				'</a>' :
				html::escapeHTML($ref['url'])).
				' <em>('.__('Item views').': '.
				html::escapeHTML($ref['itemviews']).
				' - '.__('Click throughs').': '.
				html::escapeHTML($ref['clickthroughs']).
				')</em>'.
				'</li>';
		}

		if(!empty($referrers)) {
			$referrers =
				'<h4>'.__('Referrer(s):').'</h4>'.
				'<ul>'.$referrers.'</ul>';
		}

		return
			'<tr class="line wide" id="item_'.$this->rs->index().'">'."\n".
			// Item
			'<td class="maximal nowrap">'.
				'<strong><a href="'.html::escapeHTML($this->rs->url).
				'">'.html::escapeHTML($this->rs->title).'</a></strong>'.
				$referrers.
			"</td>\n".
			// Views
			'<td class="minimal nowrap center"><strong>'.
				html::escapeHTML($this->rs->itemviews).
				'</strong>'.
			"</td>\n".
			// Clics
			'<td class="minimal nowrap center"><strong>'.
				html::escapeHTML($this->rs->clickthroughs).
				'</strong>'.
			"</td>\n".
			'</tr>'."\n";
	}
}

?>