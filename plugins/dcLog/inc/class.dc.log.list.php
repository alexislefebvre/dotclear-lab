<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcLog, a plugin for Dotclear.
# 
# Copyright (c) 2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class dcLogList extends adminGenericList
{
	/**
	 * Display data table for logs
	 *
	 * @param	int		page
	 * @param	int		nb_per_page
	 * @param	string	html_block
	 * @param	string	url
	 */
	public function display($page,$nb_per_page,$html_block = '%s')
	{
		if (!$this->rs->isEmpty()) {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->var_page = 'page';
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			echo
			'<table summary="logs" class="maximal">'.
			'<thead>'.
			'<tr>'.
				'<th>'.__('Date').'</th>'.
				'<th>'.__('Message').'</th>'.
				'<th>'.__('Blog').'</th>'.
				'<th>'.__('Component').'</th>'.
				'<th>'.__('User').'</th>'.
				'<th>'.__('IP').'</th>'.
			'</tr>'.
			'</thead>'.
			'<tbody>';
			
			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page) {
				$this->logLine();
				
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
			echo '<p>'.__('No log').'</p>';
		}
	}
	
	private function logLine()
	{
		$format = $this->core->blog->settings->system->date_format.' - '.$this->core->blog->settings->system->time_format;
		
		$tz = dt::getTimeOffset($this->core->blog->settings->system->blog_timezone);
		
		$date = dt::str($format,strtotime($this->rs->log_dt) + $tz);
		
		echo 
			'<tr class="line wide" id="log_'.$this->rs->log_id.'">'."\n".
			'<td class="minimal nowrap">'.
				form::checkbox(array('ids[]'),$this->rs->log_id).
				'&nbsp;'.html::escapeHTML($date).
			"</td>\n".
			'<td class="maximal">'.
				html::escapeHTML($this->rs->log_msg).
			"</td>\n".
			'<td class="minimal nowrap">'.
				html::escapeHTML($this->rs->blog_id).
			"</td>\n".
			'<td class="minimal nowrap">'.
				html::escapeHTML($this->rs->log_table).
			"</td>\n".
			'<td class="minimal nowrap">'.
				html::escapeHTML($this->rs->getUserCN()).
			"</td>\n".
			'<td class="minimal nowrap">'.
				html::escapeHTML($this->rs->log_ip).
			"</td>\n".
			"</tr>\n";
	}
}

?>