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


class myMeta
{
	private $core;
	private $con;
	
	protected $mymeta;

	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->con =& $this->core->con;
		if ($this->core->blog->settings->mymeta_fields) {
			$this->mymeta = @unserialize(base64_decode($this->core->blog->settings->mymeta_fields));
			if (!is_array($this->mymeta))
				$this->mymeta=array();
		} else {
				$this->mymeta=array();
		}
	}

	public function store () {
		$this->core->blog->settings->setNamespace('mymeta');
		$this->core->blog->settings->put("mymeta_fields",base64_encode(serialize($this->mymeta)),'string',"MyMeta fields");
	}

	public function getAll() {
		return $this->mymeta;
	}
	public function get($id) {
		return $this->mymeta[$id];
	}

	public function update ($id,&$meta) {
		if (empty($this->mymeta[$id])) {
			$this->mymeta[$id]=$meta;
		} else {
			$this->mymeta[$id]=$meta;
		}
		$this->store();
	}

	public function delete($ids) {
		if (!is_array($ids)) {
			$ids=array($ids);
		}
		foreach ($ids as $id) {
			unset($this->mymeta[$id]);
		}
		$this->store();
	}

	public function setEnabled($ids,$enabled) {
		if (!is_array($ids)) {
			$ids=array($ids);
		}
		foreach ($ids as $id) {
			if (!empty($this->mymeta[$id])) {
				$this->mymeta[$id]->enabled=$enabled;
			}
		}
		$this->store();
	}

	public function newMyMeta() {
		return new myMetaEntry();
	}

	public function isMetaEnabled($id) {
		if (!empty($this->mymeta[$id])) {
			return $this->mymeta[$id]->enabled;
		} else {
			return false;
		}
	}

	public function hasMeta() {
		foreach ($this->mymeta as $id=>$meta) {
			if ($meta->enabled)
				return true;
		}
		return false;
	}

	public function valuesToArray($values) {
		$arr = array();
		$lines = explode("\n",$values);
		foreach ($lines as $line) {
			$entries=explode(":",$line);
			if (sizeof($entries)==1) {
				$key = $desc = trim($entries[0]);
			} else {
				$key = trim($entries[0]);
				$desc = trim($entries[1]);
			}
			if ($key != '')
				$arr[$desc]=$key;
		}
		return $arr;
	}

	public function arrayToValues($array) {
		$res = '';
		foreach ($array as $k => $v) {
			$res .= "$v : $k\n";
		}
		return $res;

	}

	public function showForm(&$post) {
		$res="";
		$dcmeta = new dcMeta($GLOBALS['core']);
		foreach ($this->mymeta as $id=>$meta) {
			if ($meta->enabled) {
				$value =  ($post) ? $dcmeta->getMetaStr($post->post_meta,$id): '';
				$res .= '<p><label><strong>'.$meta->prompt.'</strong></label>';
				switch ($meta->type) {
					case "string" :
						$res .= form::field('mymeta_'.$id,40,255,$value);
						$res .= '</p>';
						break;
					case "list" :
						$list = $meta->values;
						$list['']='';
						$res .= form::combo('mymeta_'.$id,$list,$value);
						$res .= '</p>';
						break;
				}
			}
		}
		return $res;
	}

	public function setMeta($post_id,$POST) {
		$dcmeta = new dcMeta($GLOBALS['core']);
		foreach ($this->mymeta as $id=>$meta) {
			if ($meta->enabled) {
				$dcmeta->delPostMeta($post_id,$id);
				if (!empty($POST['mymeta_'.$id])) {
					$dcmeta->setPostMeta($post_id,$id,$POST['mymeta_'.$id]);
				}
			}
		}
	}

}


class myMetaEntry {
	public $enabled;
	public $id_type;
	public $type;
	public $values;
	public $default;

	public function __construct()
	{
		$this->enabled = false;
		$this->prompt = "";
		$this->type = "string";
		$this->values = array();
		$this->default = "";
	}
}
?>
