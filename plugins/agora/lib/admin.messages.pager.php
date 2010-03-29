<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of agora, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku , Tomtom and contributors
## Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class adminMessageList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No message').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table><tr>'.
			'<th colspan="2">'.__('Title').'</th>'.
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Status').'</th>'.
			'<th>&nbsp;</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->messageLine();
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function messageLine()
	{
		global $author, $status, $sortby, $order, $nb_per_page;
		
		$author_url =
		'plugin.php?p=agora&amp;act=messages&amp;n='.$nb_per_page.
		'&amp;status='.$status.
		'&amp;sortby='.$sortby.
		'&amp;order='.$order.
		'&amp;author='.rawurlencode($this->rs->user_id);
		
		$post_url = $this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id);
		
		$message_url = 'plugin.php?p=agora&amp;act=messages&amp;id='.$this->rs->message_id;
		
		$message_dt =
		dt::dt2str($this->core->blog->settings->system->date_format.' - '.
		$this->core->blog->settings->system->time_format,$this->rs->message_dt);
		
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->message_status) {
			case 1:
				$img_status = sprintf($img,__('published'),'check-on.png');
				break;
			case 0:
				$img_status = sprintf($img,__('unpublished'),'check-off.png');
				break;
			case -1:
				$img_status = sprintf($img,__('pending'),'check-wrn.png');
				break;
			case -2:
				$img_status = sprintf($img,__('junk'),'junk.png');
				break;
		}
		
		
		$res = '<tr class="line'.($this->rs->message_status != 1 ? ' offline' : '').'"'.
		' id="c'.$this->rs->message_id.'">';
		
		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('messages[]'),$this->rs->message_id,'','','',0).'</td>'.
		'<td class="maximal"><a href="'.$post_url.'">'.
		html::escapeHTML($this->rs->post_title).'</a>'.
		($this->rs->post_type != 'thread' ? ' ('.html::escapeHTML($this->rs->post_type).')' : '').'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->message_dt).'</td>'.
		'<td class="nowrap"><a href="'.$author_url.'">'.$this->rs->user_id.'</a></td>'.
		'<td class="nowrap status">'.$img_status.'</td>'.
		'<td class="nowrap status"><a href="'.$message_url.'">'.
		'<img src="images/edit-mini.png" alt="" title="'.__('Edit this message').'" /></a></td>';
		
		$res .= '</tr>';
		
		return $res;
	}
}
?>
