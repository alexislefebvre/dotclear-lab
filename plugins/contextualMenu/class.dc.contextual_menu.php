<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of contextualMenu, a plugin for Dotclear.
# 
# Copyright (c) 2008 Frédéric Leroy
# bestofrisk@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcBlogMenu
{
	private $blog;
	private $con;
	private $table;
	
	public function __construct(&$blog)
	{
		$this->blog =& $blog;
		$this->con =& $blog->con;
		$this->table = $this->blog->prefix.'contextual_menu';
	}
	
	public function getLinks($params=array())
	{
		$strReq = 'SELECT link_id, link_title, link_href, link_type, link_desc, '.
				'link_lang, link_xfn, link_group, link_special_xfn, link_special_group, link_special_widget, '.
				'link_special_content, link_special_link_title, link_position '.
				'FROM '.$this->table.' '.
				"WHERE blog_id = '".$this->con->escape($this->blog->id)."' ";
		
		if (isset($params['link_id'])) {
			$strReq .= 'AND link_id = '.(integer) $params['link_id'].' ';
		}

		if (isset($params['link_type'])) {
			$strReq .= 'AND link_type = "'. trim($params['link_type']).'" ';
		}
	
		$strReq .= 'ORDER BY link_position ';
		
		$rs = $this->con->select($strReq);
		$rs = $rs->toStatic();
		
		$this->setLinksData($rs);
		
		return $rs;
	}
	
	public function getLink($id)
	{
		$params['link_id'] = $id;
		
		$rs = $this->getLinks($params);
		
		return $rs;
	}
	
	public function addLink($title,$href,$type, $desc='',$lang='', $xfn='', $group='', $special_xfn='', $special_group='', $special_widget='', $special_content='', $special_link_title='')
	{
		$cur = $this->con->openCursor($this->table);
		
		$cur->blog_id = (string) $this->blog->id;
		$cur->link_title = (string) $title;
		$cur->link_href = (string) $href;
		$cur->link_type = (string) $type;	
		$cur->link_desc = (string) $desc;
		
		$cur->link_lang = (string) $lang;
		$cur->link_xfn = ($type == 'context' ? (string) $xfn : 'all');
		$cur->link_group = (string) $group;
		
		$cur->link_special_xfn = ($type == 'context' ? (string) $special_xfn : 'none');
		$cur->link_special_group = (string) $special_group;
		$cur->link_special_widget = ($type == 'context' ? (string) $special_widget : 'not_defined');
		$cur->link_special_content = ($type == 'context' ? (string) $special_widget : '<setting name="title">' . (string) $title . '</setting>');
		$cur->link_special_link_title = (int) $special_link_title;
		
		if ($cur->link_title == '') {
			throw new Exception(__('You must provide a link title'));
		}
		
		if ($cur->link_href == '') {
			throw new Exception(__('You must provide a link URL'));
		}
		
		$strReq = 'SELECT MAX(link_id) FROM '.$this->table;
		$rs = $this->con->select($strReq);
		$cur->link_id = (integer) $rs->f(0) + 1;
		
		$cur->insert();
		$this->blog->triggerBlog();
	}
	
	public function updateLink($id,$title,$href,$type,$desc='',$lang='', $xfn='', $group='', $special_xfn='', $special_group='', $special_widget='', $special_content='', $special_link_title='')
	{
		$cur = $this->con->openCursor($this->table);
		
		$cur->link_title = (string) $title;
		$cur->link_href = (string) $href;
		$cur->link_type = (string) $type;
		$cur->link_desc = (string) $desc;
		
		$cur->link_lang = (string) $lang;
		$cur->link_xfn = (string) $xfn;
		$cur->link_group = (string) $group;
		$cur->link_special_xfn = (string) $special_xfn;
		$cur->link_special_group = (string) $special_group;
		$cur->link_special_widget = (string) $special_widget;
		$cur->link_special_content = (string) $special_content;
		$cur->link_special_link_title = (int) $special_link_title;
		
		if ($cur->link_title == '') {
			throw new Exception(__('You must provide a link title'));
		}
		
		if ($cur->link_href == '') {
			throw new Exception(__('You must provide a link URL'));
		}
		
		$cur->update('WHERE link_id = '.(integer) $id.
			" AND blog_id = '".$this->con->escape($this->blog->id)."'");
		$this->blog->triggerBlog();
	}

	
	public function setLinkSpecialWidgetToNotDefined($id)
	{

		$sql = 'UPDATE '.$this->table.' SET link_special_widget = "not_defined" WHERE link_id = ' . (int) $id ;
		$this->con->execute($sql);	
		$this->blog->triggerBlog();
		
	}
	
	
	public function delItem($id)
	{
		$id = (integer) $id;
		
		// On récupère la liste des enregistrements
		$rs = $this->getLinks();

		// On supprime l'id des champs link_group des autres links si il est présent		
		while ($rs->fetch())
		{
			if ($rs->link_id != $id) {
				$link_group = explode(',',$rs->link_group);
				if (in_array($id, $link_group)) {
					unset($link_group[array_search($id,$link_group)]);
					$sql = 'UPDATE '.$this->table.' SET link_group = "' . join(',', $link_group) . '" WHERE link_id = ' . (int) $rs->link_id ;
					$this->con->execute($sql);					
				}
			}
		}

		// On supprime l'id des champs link_special_group des autres links si il est présent		
		while ($rs->fetch())
		{
			if ($rs->link_id != $id) {
				$link_special_group = explode(',',$rs->link_special_group);
				if (in_array($id, $link_special_group)) {
					unset($link_special_group[array_search($id,$link_special_group)]);
					$sql = 'UPDATE '.$this->table.' SET link_special_group = "' . join(',', $link_special_group) . '" WHERE link_id = ' . (int) $rs->link_id ;
					$this->con->execute($sql);					
				}
			}
		}
		
		// On supprime l'enregistrement correspondant à l'id
		$strReq = 'DELETE FROM '.$this->table.' '.
				"WHERE blog_id = '".$this->con->escape($this->blog->id)."' ".
				'AND link_id = '.$id.' ';
		
		$this->con->execute($strReq);
		$this->blog->triggerBlog(); // Ecrase le cache ?
	}
	
	public function updateOrder($id,$position)
	{
		$cur = $this->con->openCursor($this->table);
		$cur->link_position = (integer) $position;
		
		$cur->update('WHERE link_id = '.(integer) $id.
			" AND blog_id = '".$this->con->escape($this->blog->id)."'");
		$this->blog->triggerBlog();
	}
	
	
	private function setLinksData(&$rs)
	{
		$cat_title = null;
		while ($rs->fetch()) {
			$rs->set('is_cat',!$rs->link_title && !$rs->link_href);
			
			if ($rs->is_cat) {
				$cat_title = $rs->link_desc;
				$rs->set('cat_title',null);
			} else {
				$rs->set('cat_title',$cat_title);
			}
		}
		$rs->moveStart();
	}
	
	public function getLinksHierarchy($rs)
	{
		$res = array();
		
		foreach ($rs->rows() as $k => $v)
		{
			if (!$v['is_cat']) {
				$res[$v['cat_title']][] = $v;
			}
		}
		
		return $res;
	}
}
?>