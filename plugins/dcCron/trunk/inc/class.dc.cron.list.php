<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcCronEnableList extends adminGenericList
{
	/**
	 * Display data table for planned and enabled tasks
	 *
	 * @param	int		page
	 * @param	int		nb_per_page
	 * @param	string	url
	 */
	public function display($page,$nb_per_page,$url)
	{
		global $core;

		if (!$this->rs->isEmpty()) {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->base_url = $url.'&amp;page=%s';
			$html_block =
				'<form action="'.$url.'" method="post">'.
				'<table summary="enabled_tasks" class="maximal">'.
				'<thead>'.
				'<tr>'.
				'<th>'.__('Task id').'</th>'.
				'<th class="nowrap">'.__('Interval').'</th>'.
				'<th class="nowrap">'.__('Last run').'</th>'.
				'<th class="nowrap">'.__('Next run planned').'</th>'.
				'<th>'.__('Actions').'</th>'.
				'</tr>'.
				'</thead>'.
				'<tbody>%s</tbody>'.
				'</table>'.
				'<div class="two-cols">'.
				'<p class="col checkboxes-helpers"></p>'.
				'<p class="col right">'.
				$this->core->formNonce().
				'<input type="submit" value="'.__('Delete selected modules').'" name="delete" class="delete" /></p>'.
				'</div>'.
				'</form>';

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			$blocks = explode('%s',$html_block);
			echo $blocks[0];

			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page) {
				$format = $core->blog->settings->date_format.' - '.$core->blog->settings->time_format;
				$last_run =
					$this->rs->last_run == 0 ?
					__('Never') :
					dt::str(
						$format,
						$this->rs->last_run
					);
				$next_run = 
					$this->rs->last_run == 0 ?
					dt::str(
						$format,
						$this->rs->first_run
					) : 
					dt::str(
						$format,
						$this->rs->last_run + $this->rs->interval
					);
				$interval = dcCronEnableList::getInterval($this->rs->interval);
				echo 
					'<tr class="line wide" id="task_'.$this->rs->id.'">'."\n".
					'<td class="maximal nowrap">'.
						form::checkbox(array('nids[]'),$this->rs->id).
						'<strong>'.html::escapeHTML($this->rs->id).'</strong>'.
					"</td>\n".
					'<td class="minimal nowrap">'.
						html::escapeHTML($interval).
					"</td>\n".
					'<td class="minimal nowrap">'.
						html::escapeHTML($last_run).
					"</td>\n".
					'<td class="minimal nowrap">'.
						html::escapeHTML($next_run).
					"</td>\n".
					'<td class="minimal nowrap">'.
						'<form action="'.$url.'" method="post">'.
						'<p><input name="nid" value="'.$this->rs->id.'" type="hidden" />'.
						$this->core->formNonce().
						'<input class="edit" name="edit" value="'.
						__('Edit').'" type="submit" />&nbsp;'.
						'<input class="disable" name="disable" value="'.
						__('Disable').'" type="submit" />'.
						'</form>'.
					"</td>\n".
					"</tr>\n";
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
	 * Returns interval (in second) to string can be read by human
	 *
	 * @param:	$interval	int
	 *
	 * @return:	string
	 */
	public static function getInterval($interval)
	{
		$res = array();

		$weeks = ($interval/(3600*24*7))%(3600*24*7);
		if ($weeks > 0) {
			$res[] = sprintf('%s %s',$weeks,($weeks == 1 ? __('week') : __('weeks')));
			$interval = $interval - $weeks*3600*24*7;
		}
		$days = ($interval/(3600*24))%(3600*24);
		if ($days > 0) {
			$res[] = sprintf('%s %s',$days,($days == 1 ? __('day') : __('days')));
			$interval = $interval - $days*3600*24;
		}
		$hours = ($interval/3600)%3600;
		if ($hours > 0) {
			$res[] = sprintf('%s %s',$hours,($hours == 1 ? __('hour') : __('hours')));
			$interval = $interval - $hours*3600;
		}
		$minutes = ($interval/60)%60;
		if ($minutes > 0) {
			$res[] = sprintf('%s %s',$minutes,($minutes == 1 ? __('minute') : __('minutes')));
			$interval = $interval - $minutes*60;
		}
		if ($interval > 0) {
			$res[] = sprintf('%s %s',$interval,($interval == 1 ? __('seconde') : __('secondes')));
		}

		return implode(' - ',$res);
	}
}

class dcCronDisableList extends adminGenericList
{
	/**
	 * Display data table for planned and disabled tasks
	 *
	 * @param	int		page
	 * @param	int		nb_per_page
	 * @param	string	url
	 */
	public function display($url)
	{
		global $core;

		if (!$this->rs->isEmpty()) {
			$html_block =
				'<table summary="disabled_tasks" class="maximal">'.
				'<thead>'.
				'<tr>'.
				'<th>'.__('Task id').'</th>'.
				'<th class="nowrap">'.__('Interval').'</th>'.
				'<th class="nowrap">'.__('Last run').'</th>'.
				'<th>'.__('Actions').'</th>'.
				'</tr>'.
				'</thead>'.
				'<tbody>%s</tbody>'.
				'</table>';

			$blocks = explode('%s',$html_block);
			echo $blocks[0];

			$iter = 0;
			while ($iter < $this->rs->count()) {
				$format = $core->blog->settings->date_format.' - '.$core->blog->settings->time_format;
				$last_run = dt::str(
					$format,
					$this->rs->last_run
				);
				$interval = dcCronEnableList::getInterval($this->rs->interval);
				echo 
					'<tr class="line wide" id="task_'.$this->rs->id.'">'."\n".
					'<td class="maximal nowrap">'.
						'<strong>'.html::escapeHTML($this->rs->id).'</strong>'.
					"</td>\n".
					'<td class="minimal nowrap">'.
						html::escapeHTML($interval).
					"</td>\n".
					'<td class="minimal nowrap">'.
						html::escapeHTML($last_run).
					"</td>\n".
					'<td class="minimal nowrap">'.
						'<form action="'.$url.'" method="post">'.
						'<p><input name="nid" value="'.$this->rs->id.'" type="hidden" />'.
						$this->core->formNonce().
						'<input class="enable" name="enable" value="'.
						__('Enable').'" type="submit" />'.
						'</form>'.
					"</td>\n".
					"</tr>\n";
				$this->rs->moveNext();
				$iter++;
			}
			echo $blocks[1];
		}
	}
}

?>