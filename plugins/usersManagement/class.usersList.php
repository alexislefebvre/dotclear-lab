<?php

class userList extends adminGenericList
{
	public function displayUsers($page,$nb_per_page)
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No user').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->var_page = 'page';

			$html_block =
			'<table class="clear"><tr>'.
			'<th>'.__('Identifiant').'</th>'.
			'<th>'.__('Firstname').' '.__('Name').'</th>'.
			'<th>'.__('Display name').'</th>'.
			'</tr>%s</table>';

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';

			$blocks = explode('%s',$html_block);

			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->userLine();
			}

			echo $blocks[1];

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	private function userLine()
	{
		return
		'<tr class="line">'.
		'<td class="nowrap"><a href="plugin.php?p=usersManagement&action=getPermissions&user_id='.$this->rs->user_id.'">'.$this->rs->user_id.'</a></td>'.
		'<td>'.
		$this->rs->user_firstname.' '.$this->rs->user_name.'</td>'.
		'<td>'.$this->rs->user_displayname.'</td>'.
		'</tr>';
	}


}
?>