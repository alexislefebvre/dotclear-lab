<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of community, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class communityList extends adminGenericList
{
	/**
	 * Display data table for plugins and themes lists
	 *
	 * @param	int		page
	 * @param	int		nb_per_page
	 * @param	string	type
	 * @param	string	url
	 * @param	string	q
	 */
	public function display($page,$nb_per_page,$type,$url,$q)
	{
		$type = ($type == 'standby') ? 'standby' : 'user';
		if ($type == 'standby') {
			$msg_th_label = __('Standby users');
			$lineMethod   = 'standbyLine';
		}
		else {
			$msg_th_label = __('Community users');
			$lineMethod   = 'userLine';
		}

		if (!$this->rs->isEmpty()) {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->base_url = $url.'&amp;tab='.$type.
			(!empty($q) ? '&amp;q-'.$type.'='.$q : '').
			'&amp;page=%s';
			
			$html_block =
				'<form action="'.$url.'" method="post">'.
				'<table class="clear"><tr>'.
				'<th colspan="2">'.__('User ID').'</th>'.
				'<th>'.__('Firstname').'</th>'.
				'<th>'.__('Name').'</th>'.
				'<th>'.__('Display name').'</th>'.
				'<th class="nowrap">'.__('Creation date').'</th>'.
				'</tr>%s</table>'.
				'<div class="two-cols">'.
				'<p class="col checkboxes-helpers"></p>'.
				'<p class="col right">'.
				$this->core->formNonce().
				$this->getComboCommunityList($type).
				form::hidden(array('type'),$type).
				'<input type="submit" value="'.__('ok').'" /></p>'.
				'</p>'.
				'</div>'.
				'</form>';
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			$blocks = explode('%s',$html_block);
			echo $blocks[0];
			
			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page) {
				echo $this->{$lineMethod}();
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
	 * Return a generic user community row
	 *
	 * @return	string
	 */
	private function userLine()
	{
		$format = $this->core->blog->settings->date_format.' - '.$this->core->blog->settings->time_format;

		return
			'<tr class="line" id="u_'.$this->rs->user_id.'">'.
			'<td class="nowrap">'.form::checkbox(array('users[]'),$this->rs->user_id).'</td>'.
			'<td class="maximal"><a href="'.DC_ADMIN_URL.'user.php?id='.
			$this->rs->user_id.'">'.html::escapeHTML($this->rs->user_id).'</a></td>'.
			'<td class="maximal">'.html::escapeHTML($this->rs->user_firstname).'</td>'.
			'<td class="nowrap">'.html::escapeHTML($this->rs->user_name).'</td>'.
			'<td class="nowrap">'.html::escapeHTML($this->rs->user_displayname).'</td>'.
			'<td class="nowrap">'.dt::str($format,$this->rs->user_creadt).'</td>'.
			'</tr>';
	}

	/**
	 * Return a generic theme row
	 *
	 * @return	string
	 */
	private function standbyLine()
	{
		$format = $this->core->blog->settings->date_format.' - '.$this->core->blog->settings->time_format;

		return
			'<tr class="line" id="s_'.$this->rs->login.'">'.
			'<td class="nowrap">'.
			form::checkbox(array('standby[]'),$this->rs->login).'</td>'.
			'<td class="maximal">'.$this->rs->login.'</td>'.
			'<td class="nowrap">'.html::escapeHTML($this->rs->firstname).'</td>'.
			'<td class="nowrap">'.html::escapeHTML($this->rs->name).'</td>'.
			'<td class="nowrap">'.html::escapeHTML($this->rs->displayname).'</td>'.
			'<td class="nowrap">'.dt::str($format,$this->rs->creadt).'</td>'.
			'</tr>';
	}

	private function getComboCommunityList($type)
	{
		$action[__('Delete selected users')] = 'delete';
		if ($type == 'standby') {
			$action[__('Enable selected users')] = 'enable';
		}

		return form::combo(array('action'),$action);
	}
}

?>