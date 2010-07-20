<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcWikipedia, a plugin for Dotclear.
# 
# Copyright (c) 2009-2010 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcWikipediaParser
{
	protected $xml;
	protected $items;

	public function __construct($data)
	{
		$this->xml = @simplexml_load_string($data);

		if (!$this->xml) {
			return false;
		}

		$this->_parse();
		unset($data);
		unset($this->xml);
	}

	protected function _parse()
	{
		if ($this->xml)
		{
			if (empty($this->xml->Section)) {
				return;
			}

			foreach ($this->xml->Section->Item as $i)
			{
				$item = array();

				$item['value']		= (string) $i->Text;
				$item['desc']		= (string) $i->Description;
				$item['uri']		= (string) $i->Url;

				$this->items[$item['value']] = $item;
			}
		}
	}

	public function getItems()
	{
		return $this->items;
	}
}

?>