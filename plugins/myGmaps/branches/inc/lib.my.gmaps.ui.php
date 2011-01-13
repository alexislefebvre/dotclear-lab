<?php

class myGmapsUtils
{
	public static function getMapIcons()
	{
		$list = files::getDirList(path::real(dirname(__FILE__).'/../icons'));
		return $list['files'];
	}
	
	public static function getAdminIconURL($filename)
	{
		return sprintf('index.php?pf=myGmaps/icons/%s',$filename);
	}
	
	public static function getMapIconsJS()
	{
		$root = path::real(dirname(__FILE__).'/../icons');
		$icons = array();
		
		$mask = '#'.$root.'/(.*)#';
		
		foreach (self::getMapIcons() as $icon)
		{
			$icon = preg_replace('#'.$root.'/(.*)#',"'index.php?pf=myGmaps/icons/$1'",$icon); 
			array_push($icons,$icon);
		}
		
		return sprintf('myGmaps.icons = [%s];',implode(',',$icons));
	}
}

class adminMyGmapsList extends adminGenericList
{
	public function display($page,$nb_per_page,$p_url,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No element').'</strong></p>';
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
			'<th>'.__('Date').'</th>'.
			'<th>'.__('Category').'</th>'.
			'<th>'.__('Author').'</th>'.
			'<th class="nowrap">'.__('Map element type').'</th>'.
			'<th>'.__('Status').'</th>'.
			'</tr>%s</table>';

			if ($enclose_block) {
				$html_block = sprintf($enclose_block,$html_block);
			}

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';

			$blocks = explode('%s',$html_block);

			echo $blocks[0];

			while ($this->rs->fetch())
			{
				echo $this->postLine($p_url);
			}

			echo $blocks[1];

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	private function postLine($p_url)
	{
		if ($this->core->auth->check('categories',$this->core->blog->id)) {
			$cat_link = '<a href="category.php?id=%s">%s</a>';
		} else {
			$cat_link = '%2$s';
		}
		$img = '<img alt="%1$s" title="%1$s" src="images/%2$s" />';
		switch ($this->rs->post_status) {
			case 1:
				$img_status = sprintf($img,__('published'),'check-on.png');
				break;
			case -2:
				$img_status = sprintf($img,__('pending'),'check-wrn.png');
				break;
			case 0:
				$img_status = sprintf($img,__('unpublished'),'check-off.png');
				break;
		}
		if ($this->rs->cat_title) {
			$cat_title = sprintf($cat_link,$this->rs->cat_id,
			html::escapeHTML($this->rs->cat_title));
		} else {
			$cat_title = __('None');
		}

		$res = '<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').'"'.
		' id="p'.$this->rs->post_id.'">';
		
		$type_list = array(
			'none' => __('None'),
			'marker' => __('Point of interest'),
			'polyline' => __('Polyline'),
			'polygon' => __('Polygon'),
			'kml' => __('Included kml file')
		);
		$meta = unserialize($this->rs->post_meta);
		
		$type = array_key_exists($meta['elt_type'][0],$type_list) ? $type_list[$meta['elt_type'][0]] : '';

		$res .=
		'<td class="nowrap">'.
		form::checkbox(array('entries[]'),$this->rs->post_id,'','','',!$this->rs->isEditable()).'</td>'.
		'<td class="maximal"><a href="'.$p_url.'&amp;go=map&amp;id='.$this->rs->post_id.'">'.
		html::escapeHTML($this->rs->post_title).'</a></td>'.
		'<td class="nowrap">'.dt::dt2str(__('%Y-%m-%d %H:%M'),$this->rs->post_dt).'</td>'.
		'<td class="nowrap">'.$cat_title.'</td>'.
		'<td class="nowrap">'.$this->rs->user_id.'</td>'.
		'<td class="nowrap">'.$type.'</td>'.
		'<td class="nowrap status">'.$img_status.'</td>'.
		'</tr>';

		return $res;
	}
}

?>