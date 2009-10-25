<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear Mymeta plugin.
# Copyright (c) 2009 Bruno Hondelatte, and contributors.
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



/**
 * Core myMeta class
 *
 * Handles all backend settings for myMeta storage
 * 
 * @package MyMeta
 */
class myMeta
{
	private $core;
	private $con;
	public  $dcmeta;

	/** @var array types registered mymeta types */
	private static $types;

	/** @var array typesCombo registered mymeta types, as combo for form display */
	private static $typesCombo;
	
	/** @var array mymeta list of mymeta entries, indexed by meta position*/	
	protected $mymeta;

	/** @var array mymetaIDs, reference index for mymeta entries, indexed by meta ID */	
	protected $mymetaIDs;
        protected $html_list = "<ul>\n%s</ul>\n";       ///< <b>string</b>      HTML errors list pattern
	protected $html_item = "<li>%s</li>\n"; ///< <b>string</b>      HTML error item pattern

	protected $sep_prefix="__sep__";


	/**
	 * registerType 
	 *
	 * Registers a new meta type. Must extend myMetaEntry class
	 * 
	 * @param myMetaEntry $class class to register
	 * @static
	 * @access public
	 * @return void
	 */
	public static function registerType ($class) {
		$sample = new $class();
		$desc = $sample->getMetaTypeDesc();
		$type = $sample->getMetaTypeId();
		myMeta::$typesCombo[$desc]=$type;
		myMeta::$types[$type] = array(
			"desc" => $desc,
			"object" => $class);
	}

	/**
	 * Default constructor
	 * 
	 * Mymeta settings are retrieved from dc settings
	 * 
	 * @param mixed $core core to reference
	 * @access public
	 * @return void
	 */
	public function __construct($core,$bypass_settings=false)
	{
		$this->core =& $core;
		if (isset($core->meta))
			$this->dcmeta =& $core->meta;
		else
			$this->dcmeta = new dcMeta($core);

		$this->con =& $this->core->con;
		if (!$bypass_settings && $this->core->blog->settings->mymeta_fields) {
			$this->mymeta = @unserialize(base64_decode($this->core->blog->settings->mymeta_fields));
			if (!is_array($this->mymeta))
				$this->mymeta=array();
		} else {
				$this->mymeta=array();
		}
		$this->mymetaIDs=array();
		$this->sep_max=0;
		foreach ($this->mymeta as $k=>$v) {
				$this->mymetaIDs[$v->id] = $k;
			if ($v instanceof myMetaSection) {
				// Compute max section id, to anticipate
				// future section ids
				$sep_id=substr($v->id,strlen($this->sep_prefix));
				if ($this->sep_max < $sep_id)
					$this->sep_max = $sep_id;
			}
		}
	}

	/**
	 * getTypesAsCombo 
	 *
	 * Retrieves form-friendly registered mymeta types
	 * 
	 * @access public
	 * @return void
	 */
	public function getTypesAsCombo() {
		$combo = myMeta::$typesCombo;
		return $combo;
	}

	/**
	 * store 
	 *
	 * Stores mymeta settings
	 * 
	 * @access public
	 * @return void
	 */
	public function store () {
		$this->core->blog->settings->setNamespace('mymeta');
		$this->core->blog->settings->put(
			"mymeta_fields",
			base64_encode(serialize($this->mymeta)),
			'string',
			"MyMeta fields");
	}

	/**
	 * getAll 
	 *
	 * Retrieves all mymeta, indexed by position
	 * 
	 * @access public
	 * @return void
	 */
	public function getAll() {
		return $this->mymeta;
	}

	/**
	 * getByPos 
	 *
	 * Retrieves a mymeta, given its position
	 * 
	 * @param int $pos  the position
	 * @access public
	 * @return mymetaEntry the mymeta
	 */
	public function getByPos($pos) {
		return $this->mymeta[$pos];
	}

	/**
	 * getByID 
	 *
	 * Retrieves a mymeta, given its ID
	 * 
	 * @param String $id  the ID
	 * @access public
	 * @return mymetaEntry the mymeta
	 */
	public function getByID($id) {
		return $this->mymeta[$this->mymetaIDs[$id]];
	}


	/**
	 * updates mymeta table with a given meta 
	 * 
	 * @param mixed $meta the meta to store
	 * @access public
	 * @return void
	 */
	public function update ($meta) {
		$id = $meta->id;
		if (!isset($this->mymetaIDs[$id])) {
			// new id => create
			$this->mymeta[] = $meta;
			$this->mymetaIDs[$id] = sizeof($this->mymeta);
		} else {
			// ID already exists => update
			$this->mymeta[$this->mymetaIDs[$id]]= $meta;
		}
		$this->store();
	}

	public function newSection() {
		$this->sep_max++;
		$sep_id = $this->sep_prefix.$this->sep_max;
		$sep = new myMetaSection();
		$sep->id=$sep_id;
		return $sep;
	}

	public function reorder ($order=null) {
		$pos=0;
		$newmymeta = array();
		$newmymetaIDs = array();
		if ($order != null) {
			foreach ($order as $id) {
				if (isset($this->mymetaIDs[$id])) {
					$m = $this->mymeta[$this->mymetaIDs[$id]];
					$m->pos = $pos++;
					$newmymeta[] = $m;
					$newmymetaIDs[$id] = count($newmymeta)-1;
					unset($this->mymeta[$this->mymetaIDs[$id]]);
					unset($this->mymetaIDs[$id]);
				}
			}
		}
		// Just in case, if some items remain, add them
		foreach ($this->mymeta as $m) {
			$m->pos = $pos++;
			$newmymeta[] = $m;
			$newmymetaIDs[$m->id] = count($newmymeta)-1;
		}
		$this->mymeta = $newmymeta;
		$this->mymetaIDs = $newmymetaIDs;
	}
	public function delete($ids) {
		if (!is_array($ids)) {
			$ids=array($ids);
		}
		foreach ($ids as $id) {
			if (isset($this->mymetaIDs[$id])) {
				$pos = $this->mymetaIDs[$id];
				unset($this->mymeta[$pos]);
			}
		}
		$this->store();
	}

	public function setEnabled($ids,$enabled) {
		if (!is_array($ids)) {
			$ids=array($ids);
		}
		foreach ($ids as $id) {
			if (isset($this->mymetaIDs[$id])) {
				$pos = $this->mymetaIDs[$id];
				$this->mymeta[$pos]->enabled=$enabled;
			}
		}
		$this->store();
	}

	public function newMyMeta($type="string",$id="") {
		if (!empty(myMeta::$types[$type]))
			return new myMeta::$types[$type]['object']($id);
		else
			return null;
	}

	public function isMetaEnabled($id) {
		if (!isset($this->mymetaIDs[$id]))
			return false;
		$pos = $this->mymetaIDs[$id];
		if (!empty($this->mymeta[$pos])) {
			return $this->mymeta[$pos]->enabled;
		} else {
			return false;
		}
	}

	public function hasMeta() {
		foreach ($this->mymeta as $id=>$meta) {
			if ($meta instanceof myMetaField && $meta->enabled)
				return true;
		}
		return false;
	}

	public function postShowHeader($post) {
		$res="";
		foreach ($this->mymeta as $meta) {
			if ($meta instanceof myMetaField && $meta->enabled)
				$res .= $meta->postHeader($post);
		}
		return $res;
	}

	public function postShowForm($post) {
		$res="";
		$inSection=false;
		foreach ($this->mymeta as $id=>$meta) {
			if ($meta instanceof myMetaSection) {
				if ($inSection)
					$res .= '</fieldset>';
				$res .= '<fieldset><legend>'.__($meta->prompt).'</legend>';
				$inSection=true;
			} elseif ($meta->enabled) {
				if (!$inSection) {
					$res .= '<fieldset><legend>'.__('My Meta').'</legend>';
					$inSection=true;
				}
				$res .= $meta->postShowForm($this->dcmeta, $post);
			}
		}
		$res .= '</fieldset>';
		return $res;
	}

	public function setMeta($post_id,$POST) {
		$errors=array();
		foreach ($this->mymeta as $meta) {
			if ($meta instanceof myMetaField && $meta->enabled) {
				try {
					$meta->setPostMeta($this->dcmeta,$post_id,$POST);
				} catch (Exception $e) {
					$errors[]=$e->getMessage();
				}
			}
		}
		if (count($errors) != 0) {
			$res='';
			foreach ($errors as $msg) {
				$res .= sprintf($this->html_item,$msg);
			}
			throw new Exception (__("Mymeta errors :").sprintf($this->html_list,$res));
		}
	}

}


require_once dirname(__FILE__).'/class.mymetatypes.php';
?>