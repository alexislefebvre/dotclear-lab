<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of zoneclearFeedServer, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 JC Denis, BG and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

class zoneclearFeedServerLists extends adminGenericList
{
	public function feedsDisplay($page,$nb_per_page,$url)
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('There is no feed').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);

			$pager->base_url = $url;

			$html_block =
			'<table class="clear">'.
			'<thead>'.
			'<tr>'.
			'<th class="nowrap" colspan="2">&nbsp;</th>'.
			'<th>'.__('Name').'</th>'.
			'<th>'.__('Feed').'</th>'.
			'<th>'.__('Lang').'</th>'.
			'<th>'.__('Tags').'</th>'.
			'<th>'.__('Frequency').'</th>'.
			'<th class="nowrap">'.__('Last update').'</th>'.
			'<th>'.__('Category').'</th>'.
			'<th>'.__('Owner').'</th>'.
			'<th>'.__('Entries').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>'.
			'</thead>'.
			'<tbody>%s</tbody>'.
			'</table>';

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			$blocks = explode('%s',$html_block);
			echo $blocks[0];

			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page)
			{
				echo $this->feedsLine($url,$iter);

				if ($this->rs->isEnd())
					break;
				else
					$this->rs->moveNext();

				$iter++;
			}
			echo $blocks[1];
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	private function feedsLine($url,$loop)
	{
		$combo_status = zoneclearFeedServer::getAllStatus();
		$combo_upd_int = zoneclearFeedServer::getAllUpdateInterval();
		$status = $this->rs->feed_status ? 
			'<img src="images/check-on.png" alt="enable" />' :
			'<img src="images/check-off.png" alt="disable" />';
		$category = $this->rs->cat_id ? $this->rs->cat_title : __('none');

		return
		'<tr class="line">'."\n".
		'<td class="nowrap">'.
			form::checkbox(array('feeds[]'),$this->rs->feed_id,0).
		'</td>'.
		'<td class="nowrap">'.
		'<a href="plugin.php?p=zoneclearFeedServer&amp;tab=editfeed&amp;feed_id='.$this->rs->feed_id.'" title="'.__('Edit').'"><img src="index.php?pf=zoneclearFeedServer/inc/img/icon-edit.png" alt="'.__('Edit').'" /></a>'.
		"</td>\n".
		'<td class="nowrap">'.
		'<a href="'.$this->rs->feed_url.'" title="'.$this->rs->feed_url.'">'.html::escapeHTML($this->rs->feed_name).'</a>'.
		"</td>\n".
		'<td class="maximal nowrap">'.
		'<a href="'.$this->rs->feed_feed.'" title="'.html::escapeHTML($this->rs->feed_desc).'">'.$this->rs->feed_feed.'</a>'.
		"</td>\n".
		'<td class="nowrap">'.
		html::escapeHTML($this->rs->feed_lang).
		"</td>\n".
		'<td class="nowrap">'.
		html::escapeHTML($this->rs->feed_tags).
		"</td>\n".
		'<td class="nowrap">'.
		array_search($this->rs->feed_upd_int,$combo_upd_int).
		"</td>\n".
		'<td class="nowrap">'.
		($this->rs->feed_upd_last < 1 ? 
			__('never') :
			dt::str(__('%Y-%m-%d %H:%M'),$this->rs->feed_upd_last)
		).
		"</td>\n".
		'<td>'.
		html::escapeHTML($category).
		"</td>\n".
		'<td class="nowrap">'.
		html::escapeHTML($this->rs->feed_owner).
		"</td>\n".
		'<td class="nowrap">'.
		($this->rs->zc->getPostsByFeed(array('feed_id'=>$this->rs->feed_id),true)->f(0)).
		"</td>\n".
		'<td>'.
		$status.
		"</td>\n".
		'</tr>'."\n";
	}
}

?>