<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
#
# Copyright (c) 2010 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require DC_ROOT.'/inc/admin/lib.pager.php';
if (empty($_GET['id'])) {
	http::redirect($p_url);
	exit;
}

$mymetaEntry = $mymeta->getByID($_GET['id']);
if ($mymetaEntry == null) {
	http::redirect($p_url);
	exit;
}
class adminMyMetaList extends adminGenericList
{
	public function display($page,$nb_per_page,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No value in entries').'</strong></p>';
		}
		else
		{
			$pager = new pager($page,$this->rs_count,$nb_per_page,10);
			$pager->html_prev = $this->html_prev;
			$pager->html_next = $this->html_next;
			$pager->var_page = 'page';
			
			$html_block =
			'<table class="clear"><tr>'.
			'<th>'.__('Value').'</th>'.
			'<th>'.__('Nb Posts').'</th>'.
			'</tr>%s</table>';
			
			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}
			
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
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
		global $p_url,$mymetaEntry;
		$res = '<tr class="line">';
		
		$res .=
		'<td class="nowrap"><a href="'.$p_url.'&amp;m=viewposts&amp;id='.$mymetaEntry->id.'&amp;value='.rawurlencode($this->rs->meta_id).'">'.
		$this->rs->meta_id.'</a></td>'.
		'<td class="nowrap">'.$this->rs->count.' '.(($this->rs->count<=1)?__('entry'):__('entries')).'</td>'.
		'</tr>';
		
		return $res;
	}
}
$statuses = array(
	'valchg' => __('Value has been successfully changed')
);

?>
<html>
<head>
  <title><?php echo __('My metadata').'&gt;'.$mymetaEntry->id; ?></title>
  <?php echo dcPage::jsPageTabs('mymeta');?>
</head>
<body>
<?php

if (isset($_GET['status']) && array_key_exists($_GET['status'], $statuses)) {
	echo '<p class="message">'.$statuses[$_GET['status']].'</p>';
}
echo '<h2>'.html::escapeHTML($core->blog->name).'&gt;'.__('My Metadata').' &gt; </h2>';

echo '<p><a href="plugin.php?p=mymeta" class="multi-part">'.__('My metadata').'</a></p>';
echo '<div class="multi-part" id="mymeta" title="'.__('Metadata').' : '.html::escapeHTML($mymetaEntry->id).'">';

$params=array('meta_type' => $mymetaEntry->id,
	'order' => 'count DESC');
$rs = $mymeta->getMetadata($params,false);
$count = $mymeta->getMetadata($params,true);
echo '<fieldset><legend>'.__('Values').'</legend>';
$list = new adminMyMetaList($core,$rs,$count->f(0));
echo $list->display(1,10,'%s');
echo '</fieldset></div>';
?>
</body>
</html>
