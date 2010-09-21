<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcCron, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcCronList extends adminGenericList
{
	/**
	Display data table for planned and enabled tasks
	
	@param	page			<b>int</b>		Current page number
	@param	nb_per_page	<b>int</b>		Item number to display per page
	@param	html_block	<b>string</b>		String that wrap generated table
	@param	url			<b>string</b>		Plugin URL
	 */
	public function display($page,$nb_per_page,$html_block = '%s',$url = '')
	{
		if (!$this->rs->isEmpty()) {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->base_url = $url.'&amp;page=%s';
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			echo
			'<table summary="enabled_tasks" class="maximal">'.
			'<thead>'.
			'<tr>'.
			'<th>'.__('Task id').'</th>'.
			'<th class="nowrap">'.__('Interval').'</th>'.
			'<th class="nowrap">'.__('Last run').'</th>'.
			'<th class="nowrap">'.__('Next run planned').'</th>'.
			'<th class="nowrap">'.__('Status').'</th>'.
			'<th></th>'.
			'</tr>'.
			'</thead>'.
			'<tbody>';
			
			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page) {
				$this->taskLine($url);
				
				if ($this->rs->isEnd()) {
					break;
				}
				else {
					$this->rs->moveNext();
					$iter++;
				}
			}
			
			echo
			'</tbody>'.
			'</table>';
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
		else {
			echo '<p>'.__('No tasks').'</p>';
		}
	}
	
	private function taskLine($url)
	{
		$format = $this->core->blog->settings->system->date_format.' - '.$this->core->blog->settings->system->time_format;
		$tz = dt::getTimeOffset($this->core->blog->settings->system->blog_timezone);
		
		$p_img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		$p_a = '<a href="%1$s">%2$s</a>';
		
		$last_run =
			!(boolean) $this->rs->last_run ?
			__('Never') :
			dt::str($format,$this->rs->last_run + $tz);
		$next_run = 
			!(boolean) $this->rs->last_run ?
			dt::str($format,$this->rs->first_run + $tz) : 
			dt::str($format,$this->rs->last_run + $tz + $this->rs->interval);
		$interval = dcCron::getInterval($this->rs->interval);
		
		switch ($this->rs->status) {
			case 1:
				$offline = '';
				$img_status = sprintf($p_img,__('enabled'),'check-on.png');
				break;
			case 0:
				$offline = ' offline';
				$img_status = sprintf($p_img,__('disabled'),'check-wrn.png');
				break;
			case -1:
				$offline = ' offline';
				$img_status = sprintf($p_img,__('blocked'),'locker.png');
				break;
		}
		
		$img_once = (integer) $this->rs->interval === 0 ? sprintf($p_img,__('Execute once'),'scheduled.png') : '';
		
		$link_edit = sprintf(
			$p_a,
			$url.'&amp;tab=form&amp;id='.$this->rs->id,
			sprintf($p_img,__('Edit task'),'edit-mini.png')
		);
		
		echo 
			'<tr class="line wide'.$offline.'" id="task_'.$this->rs->id.'">'."\n".
			'<td class="maximal nowrap">'.
				form::checkbox(array('ids[]'),$this->rs->id).'&nbsp;'.
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
				$img_status.'&nbsp;'.$img_once.
			"</td>\n".
			'<td class="minimal nowrap status">'.
				$link_edit.
			"</td>\n".
			"</tr>\n";
	}
}

?>