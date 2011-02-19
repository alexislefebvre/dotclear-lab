<?php
# ***** BEGIN LICENSE BLOCK *****
#
# This file is part of Super Admin, a plugin for Dotclear 2
# Copyright (C) 2009, 2011 Moe (http://gniark.net/)
#
# Super Admin is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License v2.0
# as published by the Free Software Foundation.
#
# Super Admin is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software Foundation,
# Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# Icon (icon.png) is from Silk Icons : http://www.famfamfam.com/lab/icons/silk/
#
# ***** END LICENSE BLOCK *****

class adminGenericList
{
	protected $core;
	protected $rs;
	protected $rs_count;
	
	public function __construct(&$core,&$rs,$rs_count)
	{
		$this->core =& $core;
		$this->rs =& $rs;
		$this->rs_count = $rs_count;
		$this->html_prev = __('&#171;prev.');
		$this->html_next = __('next&#187;');
	}
}

class superAdminPostList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No entry').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="clear"><tr>'.
			'<th colspan="2">'.__('Title').'</th>'.
			'<th>'.__('Date').' '.__('(in the blog timezone)').'</th>'.
			'<th>'.__('Category').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('Blog').'</th>'.
			'<th>'.__('Comments').'</th>'.
			'<th>'.__('Trackbacks').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];

			$this->enable_content_edition =
				(boolean) superAdminAdmin::enableContentEditionPref();

			while ($this->rs->fetch())
			{
				echo $this->postLine();
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function postLine()
	{
		global $p_url;
		
		$cat_link = '%2$s';
		
		if ($this->rs->cat_title) {
			$cat_title = sprintf($cat_link,$this->rs->cat_id,
			html::escapeHTML($this->rs->cat_title));
		} else {
			$cat_title = __('None');
		}
		
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status) {
			case 1:
				$img_status = sprintf($img,__('published'),'check-on.png');
				break;
			case 0:
				$img_status = sprintf($img,__('unpublished'),'check-off.png');
				break;
			case -1:
				$img_status = sprintf($img,__('scheduled'),'scheduled.png');
				break;
			case -2:
				$img_status = sprintf($img,__('pending'),'check-wrn.png');
				break;
		}
		
		$protected = '';
		if ($this->rs->post_password) {
			$protected = sprintf($img,__('protected'),'locker.png');
		}
		
		$selected = '';
		if ($this->rs->post_selected) {
			$selected = sprintf($img,__('selected'),'selected.png');
		}
		
		$attach = '';
		$nb_media = $this->rs->countMedia();
		if ($nb_media > 0) {
			$attach_str = $nb_media == 1 ? __('%d attachment') : __('%d attachments');
			$attach = sprintf($img,sprintf($attach_str,$nb_media),'attach.png');
		}
		
		$post_url = $this->core->getPostAdminURL($this->rs->post_type,
			$this->rs->post_id);

		$post_type = '';
		if ($this->rs->post_type != 'post')
		{
			$post_type = sprintf(__(' (%s)'),$this->rs->post_type);
		}
		
		# the entry is from the current blog
		if ($this->rs->blog_id == $this->core->blog->id)
		{
			$post_link = '<a href="'.$post_url.'">'.
				html::escapeHTML($this->rs->post_title).$post_type.'</a>';
		}
		# the entry is from a different blog, edition links are active
		else if ($this->enable_content_edition)
		{
			$post_link = '<a href="'.$post_url.
				'&amp;switchblog='.urlencode($this->rs->blog_id).'" '.
				'class="superAdmin-change-blog">'.
				html::escapeHTML($this->rs->post_title).$post_type.'</a>';
		}
		# the entry is from a different blog and edition links are disabled
		else
		{
			$post_link = html::escapeHTML($this->rs->post_title).$post_type;
		}
		
		$author_link = '<a href="'.$p_url.
			'&amp;file=posts&amp;user_id='.urlencode($this->rs->user_id).'">'.
			html::escapeHTML($this->rs->user_id).'</a>';
		
		$blog_link = '<a href="'.$p_url.
			'&amp;file=posts&amp;blog_id='.urlencode($this->rs->blog_id).'">'.
			html::escapeHTML($this->rs->blog_name).'</a>';
		
		$res = '<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').'"'.
		' id="p'.$this->rs->post_id.'">';
		
		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('entries[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
		'<td class="maximal">'.$post_link.'</td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),
			$this->rs->post_dt).'</td>'.
		'<td class="nowrap">'.$cat_title.'</td>'.
		'<td class="nowrap">'.$author_link.'</td>'.
		'<td>'.$blog_link.'</td>'.
		'<td class="nowrap">'.$this->rs->nb_comment.'</td>'.
		'<td class="nowrap">'.$this->rs->nb_trackback.'</td>'.
		'<td class="nowrap status">'.$img_status.' '.$selected.' '.$protected.' '.$attach.'</td>'.
		'</tr>'."\n";
		
		return $res;
	}
}

class superAdminCommentList extends adminGenericList
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
			'<th colspan="2">'.__('Title').'</th>'.
			'<th>'.__('Date').' '.__('(in the blog timezone)').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th>'.__('IP address').'</th>'.
			'<th>'.__('Blog').'</th>'.
			'<th>'.__('Type').'</th>'.
			'<th>'.__('Status').'</th>'.
			'<th>&nbsp;</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];

			$this->enable_content_edition =
				(boolean) superAdminAdmin::enableContentEditionPref();
			
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
		global $author, $status, $sortby, $order, $nb_per_page, $p_url;
		
		$author_url =
			$p_url.
			'&amp;file=comments'.
			'&amp;n='.$nb_per_page.
			'&amp;status='.$status.
			'&amp;sortby='.$sortby.
			'&amp;order='.$order.
			'&amp;author='.rawurlencode($this->rs->comment_author);
		
		$ip_url =
			$p_url.
			'&amp;file=comments'.
			'&amp;n='.$nb_per_page.
			'&amp;status='.$status.
			'&amp;sortby='.$sortby.
			'&amp;order='.$order.
			'&amp;ip='.rawurlencode($this->rs->comment_ip);
		
		$post_url = $this->core->getPostAdminURL($this->rs->post_type,
			$this->rs->post_id);

		$post_type = '';
		if ($this->rs->post_type != 'post')
		{
			$post_type = sprintf(__(' (%s)'),$this->rs->post_type);
		}

		# the entry is from the current blog
		if ($this->rs->blog_id == $this->core->blog->id)
		{
			$post_link = '<a href="'.$post_url.'">'.
				html::escapeHTML($this->rs->post_title).$post_type.'</a>';

			$comment_link = '<a href="comment.php?id='.$this->rs->comment_id.
				'"><img src="images/edit-mini.png" alt="" title="'.
				__('Edit this comment').'" /></a>';
		}
		# the entry is from a different blog, edition links are active
		else if ($this->enable_content_edition)
		{
			$post_link = '<a href="'.$post_url.
				'&amp;switchblog='.urlencode($this->rs->blog_id).'" '.
				'class="superAdmin-change-blog">'.
				html::escapeHTML($this->rs->post_title).$post_type.'</a>';

			$comment_link = '<a href="comment.php?id='.$this->rs->comment_id.
				'&amp;switchblog='.urlencode($this->rs->blog_id).
				'" class="superAdmin-change-blog">'.
				'<img src="images/edit-mini.png" alt="" title="'.
				__('Edit this comment').'" /></a>';
		}
		# the entry is from a different blog and edition links are disabled
		else
		{
			$post_link = html::escapeHTML($this->rs->post_title).$post_type;

			$comment_link = '&nbsp;';
		}
		
		
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->comment_status) {
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
		
		$comment_author = html::escapeHTML($this->rs->comment_author);
		if (mb_strlen($comment_author) > 20) {
			$comment_author = mb_strcut($comment_author,0,17).'...';
		}
		
		$blog_link = '<a href="'.$p_url.
			'&amp;file=comments&amp;blog_id='.urlencode($this->rs->blog_id).'">'.
			html::escapeHTML($this->rs->blog_name).'</a>';
		
		$res = '<tr class="line'.($this->rs->comment_status != 1 ? ' offline' : '').'"'.
		' id="c'.$this->rs->comment_id.'">';
		
		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('comments[]'),$this->rs->comment_id,'','','',0).'</td>'.
		'<td class="maximal">'.$post_link.'</td>'.
		'<td class="nowrap">'.dt::dt2str('%Y-%m-%d %H:%M:%S',
			$this->rs->comment_dt).'</td>'.
		'<td class="nowrap"><a href="'.$author_url.'">'.$comment_author.'</a></td>'.
		'<td class="nowrap"><a href="'.$ip_url.'">'.$this->rs->comment_ip.'</a></td>'.
		'<td>'.$blog_link.'</td>'.	
		'<td class="nowrap">'.($this->rs->comment_trackback ? __('trackback') : __('comment')).'</td>'.
		'<td class="nowrap status">'.$img_status.'</td>'.
		'<td class="nowrap status">'.$comment_link.'</td>';
		
		$res .= '</tr>'."\n";
		
		return $res;
	}
}
?>