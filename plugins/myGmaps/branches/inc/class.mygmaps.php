<?php

class myGmapsUtils
{
	public static function getIcons()
	{
		$list = files::getDirList(path::real(dirname(__FILE__).'/../icons'));
		return $list['files'];
	}
	
	public static function getIconURL($filename)
	{
		global $core;
		
		return sprintf($core->blog->url.'pf=myGmaps/icons/%s',$filename);
	}
	
	public static function jsCommon($mode = 'view')
	{
		global $core;
		
		$allowed_modes = array('view','edit');
		
		if (!array_key_exists($mode,array_flip($allowed_modes))) {
			$mode = 'view';
		}
		
		$params = array(
			'sensor' => 'false',
			//'libraries' => 'geometry',
			'language' => $core->blog->settings->system->lang
		);
		
		array_walk($params,create_function('&$v,$k','$v=sprintf("%s=%s",$k,$v);'));
		
		$param_str = count($params) > 0 ? sprintf('?%s',implode('&',$params)) : '';
		
		$res =
		dcPage::jsLoad('http://maps.google.com/maps/api/js'.$param_str).
		dcPage::jsLoad(DC_ADMIN_URL.'?pf=myGmaps/js/myGmaps.js');
		
		if ($mode === 'edit') {
			$res .=
			dcPage::jsColorPicker().
			dcPage::jsLoad(DC_ADMIN_URL.'?pf=myGmaps/js/ui.core.js').
			dcPage::jsLoad(DC_ADMIN_URL.'?pf=myGmaps/js/ui.slider.js');
		}
		
		$res .=
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('myGmaps.msg.apply',__('Apply')).
		dcPage::jsVar('myGmaps.msg.coordinates',__('Coordinates')).
		dcPage::jsVar('myGmaps.msg.close',__('Close')).
		dcPage::jsVar('myGmaps.msg.fill_color',__('Fill color:')).
		dcPage::jsVar('myGmaps.msg.fill_opacity',__('Fill opacity:')).
		dcPage::jsVar('myGmaps.msg.fill_options',__('Fill options')).
		dcPage::jsVar('myGmaps.msg.geocoder_error',__('Geocode was not successful for the following reason:')).
		dcPage::jsVar('myGmaps.msg.invalid_url',__('Invalid kml URL')).
		dcPage::jsVar('myGmaps.msg.line_options',__('Line options')).
		dcPage::jsVar('myGmaps.msg.no_description',__('No description')).
		dcPage::jsVar('myGmaps.msg.marker_options',__('Marker options')).
		dcPage::jsVar('myGmaps.msg.select_instructions',__('After selecting type, click on a map to add a point, click on one point to delete it and right click on one point to custom it')).
		dcPage::jsVar('myGmaps.msg.stroke_color',__('Line color:')).
		dcPage::jsVar('myGmaps.msg.stroke_weight',__('Line weight:')).
		dcPage::jsVar('myGmaps.msg.stroke_opacity',__('Line opacity:')).
		dcPage::jsVar('myGmaps.msg.type',__('Type')).
		dcPage::jsVar('dotclear.msg.confirm_delete_posts',__("Are you sure you want to delete selected map elements (%s)?")).
		"\n//]]>\n".
		"</script>\n";
		
		return $res;
	}
	
	public static function jsData($post_id = null)
	{
		global $core;
		
		if (is_null($post_id) || !is_array($post_id)) {
			return;
		}
		
		$map_data = $items = array();
		$p_item = '{'.
			'type: "%1$s",'.
			'markers: [%2$s],'.
			'icon: "%3$s",'.
			'infowindow: "%4$s",'.
			'url: "%5$s",'.
			'o: []'.
		'}';
		$p_polyline_options = '{'.
			'strokeColor: "%1$s",'.
			'strokeWeight: %2$s,'.
			'strokeOpacity: %3$s'.
		'}';
		$p_polygon_options = '{'.
			'strokeColor: "%1$s",'.
			'strokeWeight: %2$s,'.
			'strokeOpacity: %3$s,'.
			'fillColor: "%4$s",'.
			'fillOpacity: %5$s'.
		'}';
		
		if (count($post_id) === 0) {
			array_push($post_id,'null');
		}
		
		$data = $core->blog->getPosts(array('post_type' => 'map', 'post_id' => $post_id, 'post_status' => 1));
		
		while ($data->fetch())
		{
			$coords = explode("\n",$data->post_excerpt);
			$meta = $core->meta->getMetaArray($data->post_meta);
			
			$type = $meta['elt_type'][0];
			$markers = array();
			$icon = array_key_exists('icon',$meta) ? $meta['icon'][0] : '';
			$infowindow = addslashes(preg_replace("#(\n|\r)#",'',$data->getContent(true)));
			$url = '';
			
			if ($type !== 'kml' && $type !== 'none') {
				foreach ($coords as $coord) {
					$point = explode('|',$coord);
					array_push($markers,sprintf('{lat:%1$s,lng:%2$s}',$point[0],$point[1]));
				}
			}
			else {
				$url = $coords[0];
			}
			
			if ($type === 'polyline' || $type === 'polygon') {
				$options = sprintf(
					($type === 'polyline' ? $p_polyline_options : $p_polygon_options),
					$meta['stroke_color'][0],
					$meta['stroke_weight'][0],
					$meta['stroke_opacity'][0],
					$meta['fill_color'][0],
					$meta['fill_opacity'][0]
				);
				array_push($map_data,sprintf('myGmaps.setObjectsOptions("%1$s",%2$s);',$type,$options));
			}
			
			$item = sprintf($p_item,$type,implode(',',$markers),$icon,$infowindow,$url);
		
			array_push($map_data,sprintf('myGmaps.addItems(%s);',$item));
		}
		
		return
		'<script type="text/javascript">'."\n".
		"$(function() {\n".
		implode("\n",$map_data).
		"\n});\n".
		"</script>\n";
	}
	
	public static function jsIcons()
	{
		$icon_data = array();
		
		foreach (self::getIcons() as $icon) {
			array_push($icon_data,sprintf("'%s'",self::getIconURL(basename($icon))));
		}
		
		return
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		sprintf("var icons = [%s];\n",implode(",\n",$icon_data)).
		"\n//]]>\n".
		"</script>\n";
	}
}

class adminMyGmapsList extends adminGenericList
{
	public function __construct($core,$rs,$count,$bound = null,$pagination = true)
	{
		$this->bound = array();
		$this->pagination = true;
		
		if (is_array($bound)) {
			$this->bound = $bound;
		}
		if (is_bool($pagination)) {
			$this->pagination = $pagination;
		}
		
		parent::__construct($core,$rs,$count);
	}
	
	public function display($page,$nb_per_page,$p_url,$enclose_block='')
	{
		if ($this->rs->isEmpty())
		{
			echo '<p><strong>'.__('No map element').'</strong></p>';
		}
		else
		{
			if ($this->pagination) {
				$pager = new pager($page,$this->rs_count,$nb_per_page,10);
				$pager->html_prev = $this->html_prev;
				$pager->html_next = $this->html_next;
				$pager->var_page = 'page';
			}
			
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
			
			if ($this->pagination) {
				echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			}
			
			$blocks = explode('%s',$html_block);
			
			echo $blocks[0];
			
			while ($this->rs->fetch())
			{
				echo $this->postLine($p_url);
			}
			
			echo $blocks[1];
			
			if ($this->pagination) {
				echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			}
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
		
		$res = '<tr class="line'.($this->rs->post_status != 1 ? ' offline' : '').
		(array_key_exists($this->rs->post_id,array_flip($this->bound)) ? ' bind' : '').'"'.
		' id="e'.$this->rs->post_id.'">';
		
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