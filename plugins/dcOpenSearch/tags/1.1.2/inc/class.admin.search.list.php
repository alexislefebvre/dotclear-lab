<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class adminSearchList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		$res = '';
		
		if ($this->rs->isEmpty()) {
			$res = '<h3>'.__('No result').'</h3>';
		}
		else {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="clear"><tr>'.
			'<th>'.__('Type').'</th>'.
			'<th>'.__('Title').'</th>'.
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Category').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Comments').'</th>'.
			'<th>'.__('Trackbacks').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			$res .= '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			$res .= $blocks[0];
			
			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			
			$iter = 0;
			
			while ($iter < $nb_per_page) {
				$res .= $this->searchLine();
				if ($this->rs->isEnd()) {
					break;
				}
				else {
					$this->rs->moveNext();
					$iter++;
				}
			}
			
			$res .= $blocks[1];
			
			$res .= '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
		
		return $res;
	}
	
	private function searchLine()
	{
		if ($this->core->auth->check('categories',$this->core->blog->id)) {
			$cat_link = '<a href="category.php?id=%s">%s</a>';
		} else {
			$cat_link = '%2$s';
		}
		
		if ($this->rs->search_cat_title) {
			$cat_title = sprintf($cat_link,$this->rs->search_cat_id,
			html::escapeHTML($this->rs->search_cat_title));
		} else {
			$cat_title = __('None');
		}
		
		$author_link = $this->rs->search_author_url ? '<a href="%1$s">%2$s</a>' : '%2$s';
		
		$res = '<tr class="line" id="p'.$this->rs->search_id.'">';
		
		$res .=
		'<td class="nowrap">'.dcOpenSearch::getLabel($this->rs).'</td>'.
		'<td class="maximal"><a href="'.$this->rs->getItemAdminURL().'">'.
		html::escapeHTML($this->rs->search_title).'</a></td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->search_dt).'</td>'.
		'<td class="nowrap">'.$cat_title.'</td>'.
		'<td class="nowrap">'.sprintf($author_link,$this->rs->search_author_url,$this->rs->search_author_name).'</td>'.
		'<td class="nowrap">'.$this->rs->search_comment_nb.'</td>'.
		'<td class="nowrap">'.$this->rs->search_trackback_nb.'</td>'.
		'</tr>';
		
		return $res;
	}
}

?>