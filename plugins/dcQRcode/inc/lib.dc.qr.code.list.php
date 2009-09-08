<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

class dcQRcodeList extends adminGenericList
{
	public function display($page,$nb_per_page,$page_name,$base_url,$redir)
	{
		if ($this->rs->isEmpty())
			echo '<p><strong>'.__('No record').'</strong></p>';

		else {
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);

			$pager->base_url = $base_url;

			echo
			'<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>'.
			'<form action="plugin.php?p=dcQRcode" method="post">'.
			'<table class="clear">'.
			'<thead>'.
			'<tr>'.
			'<th class="nowrap" colspan="2">'.__('ID').'</th>'.
			'<th class="minimal">'.__('Img').'</th>'.
			'<th class="maximal">'.__('Data').'</th>'.
			'<th class="nowrap">'.__('Size').'</th>'.
			'<th class="nowrap">'.__('Type').'</th>'.
			'</tr>'.
			'</thead>'.
			'<tbody>';

			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page)
			{
				echo $this->parseLine($iter);

				if ($this->rs->isEnd())
				{
					break;
				}
				else
				{
					$this->rs->moveNext();
				}
				$iter++;
			}

			echo  
			'</tbody>'.
			'</table>'.
			'<div class="two-cols">'.
			'<p class="col checkboxes-helpers"></p><p>'.
			'<input type="submit" name="delete_qrcode" value="'.__('Delete selected records').'" />'.
			form::hidden(array($page_name),$page).
			form::hidden(array('nb'),$nb_per_page).
			form::hidden(array('redir'),$redir).
			$this->core->formNonce().'</p>'.
			'</div>'.
			'</form>'.
			'<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	private function parseLine($loop)
	{
		$url = $this->core->blog->url.$this->core->url->getBase('dcQRcodeImage').'/'.$this->rs->qrcode_id.'.png';

		return
		'<tr class="line">'."\n".
		'<td class="nowrap">'.
			form::checkbox(array('entries['.$loop.']'),$this->rs->qrcode_id,0).
		'</td>'.
		'<td class="nowrap">'.
		'<strong>'.
			$this->rs->qrcode_id.
		'</strong>'.
		"</td>\n".
		'<td class="minimal">'.
		'<img alt="QR code" src="'.$url.'" />'.
		"</td>\n".
		'<td class="maximal">'.
			html::escapeHTML(dcQRcode::unescape($this->rs->qrcode_data)).
		"</td>\n".
		'<td class="nowrap">'.
			$this->rs->qrcode_size.'x'.$this->rs->qrcode_size.
		"</td>\n".
		'<td class="nowrap">'.
			html::escapeHTML($this->rs->qrcode_type).
		"</td>\n".
		'</tr>'."\n";
	}
}