<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of feedburner, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class feedburnerParser
{
	protected $xml;
	protected $datas;
	protected $error;

	/**
	 * Feedburner object's constructor : Return false if a problem happend
	 *
	 * @param:	string	data
	 */
	public function __construct($data)
	{
		$this->xml = @simplexml_load_string($data);
		$this->datas = array();
		$this->error = array();

		if (!$this->xml) {
			return false;
		}

		$this->_parse();

		unset($data);
		unset($this->xml);
	}

	/**
	 * Parse xml data
	 */
	protected function _parse()
	{
		if ($this->xml)
		{
			$attr = $this->xml->attributes();

			if ($attr['stat'] == 'ok') {
				$this->datas = $this->getChildren($this->xml->feed->children());
			}
			else {
				$this->error = $this->getAttributes($this->xml->err);
			}
		}
	}

	/**
	 * Get node attributes
	 *
	 * @param:	simpleXML	xml
	 *
	 * @return:	array
	 */
	protected function getAttributes($xml)
	{
		$res = array();

		foreach ($xml->attributes() as $k => $v) {
			$res[$k] = (string)$v;
		}

		return $res;
	}

	/**
	 * Get children node
	 *
	 * @param:	simpleXML	xml
	 *
	 * @return:	array
	 */
	protected function getChildren($xml)
	{
		foreach ($xml as $node)
		{
			$children = array();

			$attr = $this->getAttributes($node);

			if (count($node->children()) > 0) {
				$children[$node->children()->getName()] = $this->getChildren($node->children());
			}

			$res[] = array_merge($attr,$children);
		}

		return $res;
	}

	/**
	 * Return API datas
	 *
	 * @return:	array
	 */
	public function getDatas()
	{
		return $this->datas;
	}

	/**
	 * Return API error
	 *
	 * @return:	array
	 */
	public function getError()
	{
		return $this->error;
	}

}

?>