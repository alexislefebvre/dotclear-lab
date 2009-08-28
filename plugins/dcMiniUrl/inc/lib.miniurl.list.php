<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcMiniUrl, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

class adminlistMiniurl extends adminGenericList
{
	public function display($page,$nb_per_page,$url)
	{
		if ($this->rs->isEmpty())
			echo '<p><strong>'.__('No short link').'</strong></p>';

		else {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);

			$pager->base_url = $url;

			$html_block =
				'<table class="clear">'.
				'<thead>'.
				'<tr>'.
				'<th class="nowrap" colspan="2">'.__('Short link').'</th>'.
				'<th class="maximal">'.__('Long link').'</th>'.
				'<th class="nowrap">'.__('Followed').'</th>'.
				'<th class="nowrap">'.__('Date').'</th>'.
				'<th class="nowrap">'.__('Type').'</th>'.
				'</tr>'.
				'</thead>'.
				'<tbody>%s</tbody>'.
				'</table>';

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			$blocks = explode('%s',$html_block);
			echo $blocks[0];

			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page) {

				echo $this->urlLine($url,$iter);

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

	private function urlLine($url,$loop)
	{
		$url = $this->core->blog->url.$this->core->url->getBase('miniUrl').'/'.html::escapeHTML($this->rs->miniurl_id);
		if (strlen($url) > 20)
			$url = '...'.substr($url,-17);

		return
		'<tr class="line">'."\n".
		'<td class="nowrap">'.
			form::checkbox(array('entries['.$loop.']'),$this->rs->miniurl_id,0).
			form::hidden(array('urltypes['.$loop.']'),$this->rs->miniurl_type).
		'</td>'.
		'<td class="nowrap">'.
		'<strong><a href="'.$this->core->blog->url.$this->core->url->getBase('miniUrl').'/'.$this->rs->miniurl_id.'">'.
			$url.
		'</a></strong>'.
		"</td>\n".
		'<td class="maximal">'.
		'<a href="'.html::escapeHTML($this->rs->miniurl_str).'">'.
			html::escapeHTML($this->rs->miniurl_str).
		'</a>'.
		"</td>\n".
		'<td class="nowrap">'.
			html::escapeHTML($this->rs->miniurl_counter).
		"</td>\n".
		'<td class="nowrap">'.
			dt::dt2str($this->core->blog->settings->date_format.', '.$this->core->blog->settings->time_format,$this->rs->miniurl_dt,$this->core->auth->getInfo('user_tz')).
		"</td>\n".
		'<td class="nowrap">'.
			html::escapeHTML($this->rs->miniurl_type).
		"</td>\n".
		'</tr>'."\n";
	}
}

?>