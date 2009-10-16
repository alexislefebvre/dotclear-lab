<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of hackMyTags,
# a plugin for DotClear2.
#
# Copyright (c) 2008 Bruno Hondelatte and contributors
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/gpl-2.0.txt
#
# -- END LICENSE BLOCK ------------------------------------

class tagHack {
	public $id;
	public $tag;
	public $attr;
	public $override;
	public $type;
	public $modes;
	public $cond;
	public $enabled;
}

/**
 * This class handles all tag hacks
 *
 * @package    HackMyTags
 * @author     Bruno Hondelatte
 * @version    SVN: $Id: $
 */
class dcHackMyTags
{
	/**
	 * dcCore reference
	 *
	 * @var dcCore
	 * @access private
	 */
	private $core;

	/**
	 * HackMyTags fields
	 *
	 * @var tagHacks
	 * @access private
	 */
	private $tagHacks;
	private $hackedIndex;
	private $counters;
	
	/**
	 * Constructor
	 *
	 * @param dcCore $core
	 */
	public function __construct(dcCore $core,$bypass_settings=false)
	{
		$this->core =& $core;

		if (!$bypass_settings && $this->core->blog->settings->mymeta_fields) {
			$this->tagHacks = @unserialize(base64_decode($this->core->blog->settings->hackmytags_fields));
			if (!is_array($this->tagHacks))
				$this->tagHacks=array();
		} else {
				$this->tagHacks=array();
		}
		foreach ($this->tagHacks as $id=>$hack) {
			$index=$hack->type.":".$hack->tag;
			$this->hackedIndex[$index][]=&$hack;
		}
	}
		
	/**
	 * store 
	 *
	 * Stores hackmytags settings
	 * 
	 * @access public
	 * @return void
	 */
	public function store () {
		$this->core->blog->settings->setNamespace('hackmytags');
		$this->core->blog->settings->put(
			"hackmytags_fields",
			base64_encode(serialize($this->tagHacks)),
			'string',
			"HackMyTags fields");
	}

	/**
	 * getCounter
	 *
	 * Retrieves current tag count inside current template
	 * 
	 * @access public
	 * @return void
	 */
	private function getCounter($prefix,$b) {
		$tag=$prefix.":".$b;
		if (!isset($this->counters[$tag])) {
			return 0;
		} else {
			return $this->counters[$tag];
		}
	}
	
	/**
	 * updateCounter
	 *
	 * Updates counter for a given tag inside a template
	 *
	 * @param string $type tag type ('b': block, 'v' : value)
	 * @param string $b tag name
	 * @access public
	 * @return void
	 */
	private function updateCounters ($type,$b) {
		$tag=$type.":".$b;
		if (!isset($this->counters[$tag])) {
			$this->counters[$tag] = 1;
		} else {
			$this->counters[$tag] ++;
		}	
	}

	/**
	 * updateBlockCounter
	 *
	 * Updates counter for a given block tag inside a template
	 *
	 * @param string $b tag name
	 * @access public
	 * @return void
	 */
	public function updateBlockCounters($b) {
		$this->updateCounters('b',$b);
	}

	/**
	 * updateBlockCounter
	 *
	 * Updates counter for a given value tag inside a template
	 *
	 * @param string $b tag name
	 * @access public
	 * @return void
	 */
	public function updateValueCounters($b) {
		$this->updateCounters('v',$b);
	}
	
	
	/**
	 * isHacked
	 *
	 * Returns whether a tag is hacked by a defined field 
	 * (field condition is not run)
	 *
	 * @param string $type tag type ('b': block, 'v' : value)
	 * @param string $mode url mode
	 * @param string $b tag name
	 * @access public
	 * @return boolean, true if tag is hacked
	 */
	public function isHacked($type,$mode,$b) {
		$tag = $type.":".$b;
		if (!isset($this->hackedIndex[$tag]))
			return false;
		foreach ($this->hackedIndex[$tag] as $hack) {
			if (!$hack->enabled)
				continue;
			if (empty($hack->modes) || in_array($mode,$hack->modes)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * isBlockHacked
	 *
	 * Returns whether a block tag is hacked by a defined field 
	 * (field condition is not run)
	 *
	 * @param string $mode url mode
	 * @param string $b tag name
	 * @access public
	 * @return boolean, true if tag is hacked
	 */	
	public function isBlockHacked($mode,$b) {
		return $this->isHacked('b',$mode,$b);
	}

	/**
	 * isValueHacked
	 *
	 * Returns whether a value tag is hacked by a defined field 
	 * (field condition is not run)
	 *
	 * @param string $mode url mode
	 * @param string $b tag name
	 * @access public
	 * @return boolean, true if tag is hacked
	 */	
	public function isValueHacked($mode,$b) {
		return $this->isHacked('v',$mode,$b);
	}

	/**
	 * getHack
	 *
	 * Returns a hack field, given its id
	 *
	 * @param string $id tag ID
	 * @access public
	 * @return tagHack the hack field
	 */	
	public function getHack($id) {
		if (!isset($this->tagHacks[$id]))
			throw new Exception ('ID not found');
		return $this->tagHacks[$id];
	}
	
	/**
	 * newHack
	 *
	 * Creates a new hack field
	 *
	 * @access public
	 * @return tagHack the hack field
	 */	
	public function newHack() {
		$hack = new tagHack();
		$hack->modes=array();
		$hack->attr=array();
		$hack->id=-1;
		$hack->enabled=false;
		$hack->override=false;
		$hack->type='b';
		$hack->cond=array('position'=>'');
		return $hack;
	}
	
	/**
	 * updateHack
	 *
	 * updates (or inserts) a hack field in the plugin settings
	 *
	 * @param hackTag the hack to add or update
	 * @access public
	 * @return void
	 */	
	public function updateHack($hack) {
		if ($hack->id == -1) {
			// New hack => append it to the list
			$hack->id = count($this->tagHacks);
		}
		$this->tagHacks[$hack->id]=$hack;
		$this->store();
	}

	/**
	 * delete
	 *
	 * Removes a hack field, given its id
	 *
	 * @param string $ids ids list, or single id
	 * @access public
	 * @return void
	 */	
	public function delete($ids) {
		if (!is_array($ids)) {
			$ids=array($ids);
		}
		foreach ($ids as $id) {
			if (!isset($this->tagHacks[$id]))
				continue;
			unset($this->tagHacks[$id]);
		}
		$this->store();
	}
	
	/**
	 * getAll
	 *
	 * returns all hacks
	 *
	 * @access public
	 * @return the hacks table
	 */	
	public function getAll() {
		return $this->tagHacks;
	}

	/**
	 * setEnabled
	 *
	 * Enables or disables a hack list
	 *
	 * @param string $ids ids list, or single id
	 * @param boolean $enabled true to enable, false to disable
	 * @access public
	 * @return void
	 */	
	public function setEnabled($ids,$enabled=true) {
		if (!is_array($ids)) {
			$ids=array($ids);
		}
		foreach ($ids as $id) {
			if (!isset($this->tagHacks[$id]))
				continue;
			$this->tagHacks[$id]->enabled = $enabled;
		}
		$this->store();
	}	
	/**
	 * checkRange
	 *
	 * checks a value against a range set
	 *
	 * @param string $cond range set 
	 *        ex : "1,2,3,4-10" or "1-" or "" for any value, or "1-10" for any value between 1 and 10
	 * @param string $value the value tu check
	 * @access public
	 * @return void
	 */	
	private function checkRange($cond,$value) {
		foreach (explode(',',$cond) as $range) {
			if (strpos($range,'-')) {
				if (is_numeric($range) && $value = $range)
					return true;
				elseif (trim($range) == '')
					return true;
			} else {
				$limits = explode('-',$range);
				$ret=true;
				if (is_numeric($limits[0]) && $value < $limits[0])
					$ret=false;
				if (is_numeric($limits[1]) && $value > $limits[1])
					$ret=false;
				if ($ret)
					return true;
			}
		}
	}
	
	/**
	 * hack
	 *
	 * Hacks a tag, by adding attributes
	 *
	 * @param string $mode url mode
	 * @param string $tag tag name
	 * @param string $type tag type ('b': block, 'v' : value)
	 * @param array $attr current tag attributes
	 * @access public
	 * @return void
	 */	
	private function hack($mode,$type,$tag,$attr) {
		$index = $type.":".$tag;
		if (!isset($this->hackedIndex[$index]))
			return;
		$pos = $this->getCounter($type,$tag);
		if (!$this->checkRange($hack->cond['position'],$pos))
			return;
		foreach ($this->hackedIndex[$index] as $hack) {
			if (!$hack->enabled)
				continue;
			if (!empty($hack->modes) && (!in_array($mode,$hack->modes)))
				continue;
		
			foreach ($hack->attr as $k=>$v) {
				if (!isset($attr[$k]) || $hack->override)
					$attr[$k]=$v;
			}
		}
	}

	/**
	 * hack
	 *
	 * Hacks a block tag, by adding attributes
	 *
	 * @param string $mode url mode
	 * @param string $tag tag name
	 * @param array $attr current tag attributes
	 * @access public
	 * @return void
	 */	
	public function hackBlock($mode,$tag,$attr) {
		$this->hack($mode,'b',$tag,$attr);
	}
	
	/**
	 * hackValue
	 *
	 * Hacks a value tag, by adding attributes
	 *
	 * @param string $mode url mode
	 * @param string $tag tag name
	 * @param array $attr current tag attributes
	 * @access public
	 * @return void
	 */	
	public function hackValue($mode,$tag,$attr) {
		$this->hack($mode,'v',$tag,$attr);
	}
	
	
	/**
	 * arrayToValues
	 *
	 * Converts an associative array into a string 
	 * (for editing purpose inside a form)
	 * The resulting string contains lines such as key=value 
	 * for each array item
	 *
	 * @param array $array to convert
	 * @access public
	 * @return string the converted string
	 */	
	public function arrayToValues($array) {
		$res = '';
		if (is_array($array)) {
			foreach ($array as $k => $v) {
				$res .= "$k=$v\n";
			}
		}
		return $res;
	}
	
	/**
	 * arrayToValues
	 *
	 * Converts a string into an associative array
	 * The string must contain expressions such as :
	 * key1=value1
	 * key2=value2
	 * value3
	 * ...
	 * The resulting array will be 
	 * array('key1'=>'value1','key2'=>'value2','value3'=>'value3');
	 *
	 * @param string $string to convert
	 * @access public
	 * @return string the converted array
	 */	
	public function valuesToArray($values) {
		$arr = array();
		$lines = explode("\n",$values);
		foreach ($lines as $line) {
			$entries=explode("=",$line);
			if (sizeof($entries)==1) {
				$key = $desc = trim($entries[0]);
			} else {
				$key = trim($entries[0]);
				$desc = trim($entries[1]);
			}
			if ($key != '')
				$arr[$key]=$desc;
		}
		return $arr;
	}
}
?>
