<?php
# vim: set noexpandtab tabstop=5 shiftwidth=5:
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of threadedComments, a plugin for Dotclear.
# 
# Copyright (c) 2009 Aurélien Bompard <aurelien@bompard.org>
# 
# Licensed under the AGPL version 3.0.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/agpl-3.0.html
# -- END LICENSE BLOCK ------------------------------------


class adminThreadedCommentList extends adminCommentList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No comment').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table><tr>'.
			'<th>&nbsp;</th>'.
			'<th>'.__('Comment').'</th>'.
			'<th class="nowrap">'.__('Anwsers to').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Edit').'</th>'.
			'<th>'.__('Entry title').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->commentLine();
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function commentLine()
	{
		global $author, $status, $sortby, $order, $nb_per_page;
		
		$post_url = $this->core->getPostAdminURL($this->rs->post_type,$this->rs->post_id);
		
		$comment_url = 'comment.php?id='.$this->rs->comment_id;

		$comment_dt =
		dt::dt2str($this->core->blog->settings->date_format.' - '.
		$this->core->blog->settings->time_format,$this->rs->comment_dt);
		
		$comment_author = html::escapeHTML($this->rs->comment_author);
		if (mb_strlen($comment_author) > 20) {
			$comment_author = mb_strcut($comment_author,0,17).'...';
		}
		
		$res = '<tr class="line'.($this->rs->comment_status != 1 ? ' offline' : '').'"'.
		' id="c'.$this->rs->comment_id.'">';
		
		$res .=
		'<td></td>'.
		'<td class="nowrap center">'.
		form::radio(array('comment'),$this->rs->comment_id,false,'').'</td>'.
		'<td class="nowrap center">'.
		form::radio(array('answersto'),$this->rs->comment_id,false,'').'</td>'.
		'<td class="nowrap">'.$comment_author.'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->comment_dt).'</td>'.
		'<td class="nowrap status center"><a href="'.$comment_url.'">'.
		'<img src="images/edit-mini.png" alt="" title="'.__('Edit this comment').'" /></a></td>'.
		'<td class="maximal"><a href="'.$post_url.'">'.
		html::escapeHTML($this->rs->post_title).'</a>'.
		($this->rs->post_type != 'post' ? ' ('.html::escapeHTML($this->rs->post_type).')' : '').'</td>';
		
		$res .= '</tr>';
		
		return $res;
	}
}

?>
