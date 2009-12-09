<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kUtRL, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

class kutrlIndexlist extends adminGenericList
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
				'<th class="nowrap" colspan="2">'.__('Service').'</th>'.
				'<th class="nowrap">'.__('Hash').'</th>'.
				'<th class="maximal">'.__('Link').'</th>'.
				'<th class="nowrap">'.__('Date').'</th>'.
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
		$type = $this->rs->kut_type;
		$hash = $this->rs->kut_hash;

		if (isset($this->core->kutrlServices[$this->rs->kut_type]))
		{
			$o = new $this->core->kutrlServices[$this->rs->kut_type]($this->core);
			$type = '<a href="'.$o->home.'" title="'.$o->name.'">'.$o->name.'</a>';
			$hash = '<a href="'.$o->url_base.$hash.'" title="'.$o->url_base.$hash.'">'.$hash.'</a>';
		}

		return
		'<tr class="line">'."\n".
		'<td class="nowrap">'.
			form::checkbox(array('entries['.$loop.']'),$this->rs->kut_id,0).
		'</td>'.
		'<td class="nowrap">'.
			$type.
		"</td>\n".
		'<td class="nowrap">'.
			$hash.
		"</td>\n".
		'<td class="maximal">'.
		'<a href="'.$this->rs->kut_url.'">'.$this->rs->kut_url.'</a>'.
		"</td>\n".
		'<td class="nowrap">'.
			dt::dt2str($this->core->blog->settings->date_format.', '.$this->core->blog->settings->time_format,$this->rs->kut_dt,$this->core->auth->getInfo('user_tz')).
		"</td>\n".
		'</tr>'."\n";
	}
}

?>