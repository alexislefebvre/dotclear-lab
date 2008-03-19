<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2008 Bruno Hondelatte,  and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# All rights reserved.
#
# Mymeta plugin for DC2 is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

class adminMymetaList 
{
	private $core;
	private $data;
	private $count;

	public function __construct(&$core,$meta)
	{
		$this->core =& $core;
		$this->data = $meta;
		$this->count = sizeof($meta);


	}
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->count==0)
		{
			echo '<p><strong>'.__('No meta').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->count,$nb_per_page,10);
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="clear"><tr>'.
			'<th colspan="2">'.__('ID').'</th>'.
			'<th>'.__('Type').'</th>'.
			'<th>'.__('Prompt').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			foreach ($this->data as $id=>$rs)
			{
				echo $this->postLine($id,$rs);
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function postLine($id,$rs)
	{
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		if ($rs->enabled) {
			$img_status = sprintf($img,__('published'),'check-on.png');
		} else {
			$img_status = sprintf($img,__('unpublished'),'check-off.png');
		}
		$res = '<tr class="line'.($rs->enabled ? ' offline' : '').'"'.
		' id="p'.$id.'">';
		
		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('entries[]'),$id,'','','').'</td>'.
		'<td class="nowrap"><a href="plugin.php?p=mymeta&m=edit&id='.$id.'">'.
		html::escapeHTML($id).'</a></td>'.
		/*'<td class="nowrap">'.$cat_title.'</td>'.*/
		'<td class="nowrap">'.$rs->type.'</td>'.
		'<td class="nowrap">'.$rs->prompt.'</td>'.
		'<td class="nowrap">'.$img_status.'</td>'.
		'</tr>';
		
		return $res;
	}
}
