<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

class dcQRcodeList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No QR code').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="clear"><tr>'.
			'<th colspan="2">'.__('ID').'</th>'.
			'<th>'.__('Image').'</th>'.
			'<th>'.__('Content').'</th>'.
			'<th>'.__('Size').'</th>'.
			'<th>'.__('Type').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block)
			{
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->qrLine();
			}
			
			echo $blocks[1];
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}
	
	private function qrLine()
	{
		return
		'<tr class="line">'.
		'<td class="nowrap">'.
			form::checkbox(array('entries[]'),$this->rs->qrcode_id,0).
		'</td>'.
		'<td class="nowrap">'.
		'<strong>'.
			$this->rs->qrcode_id.
		'</strong>'.
		'</td>'.
		'<td class="minimal">'.
		'<img alt="QR code" src="'.$this->rs->qrc->getURL($this->rs->qrcode_id).'" />'.
		'</td>'.
		'<td class="maximal">'.
			html::escapeHTML(QRcodeCore::unescape($this->rs->qrcode_data)).
		'</td>'.
		'<td class="nowrap">'.
			$this->rs->qrcode_size.'x'.$this->rs->qrcode_size.
		'</td>'.
		'<td class="nowrap">'.
			html::escapeHTML($this->rs->qrcode_type).
		'</td>'.
		'</tr>'."\n";
	}
}