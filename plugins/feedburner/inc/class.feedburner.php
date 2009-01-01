<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin feedburner for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class feedburner
{
	protected $core;
	
	protected $primary_xml;
	protected $secondary_xml;
	
	protected $proxy;
	protected $datas;
	protected $errors;
	protected $feeds;
	
	/**
	 * Feedburner object constructor
	 *
	 * @param:	object	core
	 */
	public function __construct(&$core)
	{
		$this->core				=& $core;
		$this->primary_xml 		= $core->blog->settings->feedburner_primary_xml;
		$this->secondary_xml 	= $core->blog->settings->feedburner_secondary_xml;
		$this->proxy			= $core->blog->settings->feedburner_proxy;
		$this->feeds			= unserialize($core->blog->settings->feedburner_feeds);
		$this->datas			= array();
		$this->errors			= array();
	}

	/**
	 * Gets datas form feedburner API
	 *
	 * @param:	string	url
	 *
	 * @return:	boolean
	 */
	protected function getXML($url)
	{
		try {
			if (($parser = feedburnerReader::quickParse($this->primary_xml.$url,DC_TPL_CACHE,$this->proxy)) === false) {
				if (($parser = feedburnerReader::quickParse($this->secondary_xml.$url,DC_TPL_CACHE,$this->proxy)) === false) {
					return false;
				}
			}

			$this->datas = $parser->getDatas();
			$this->errors = $parser->getError();

			return true;
		}
		catch (Exception $e) {
			$tab = explode(':',$e->getMessage());
			$this->errors = array(
				'code' => trim($tab[0]),
				'msg' => trim($tab[1])
			);
			return false;
		}
	}

	/**
	 * Check feedburner statistics
	 *
	 * @param:	string	id
	 * @param:	string	mode
	 */
	public function check($id,$mode = '')
	{
		$mode = $mode != 'details' ? 'normal' : 'details';

		$dates = '2004-01-01,'.date('Y').'-'.date('n').'-'.(date('d') - 1);

		switch ($mode)
		{
			case 'details':
				$url = 'GetResyndicationData?uri=%1$s';
				break;
			case 'normal':
				$url = 'GetFeedData?uri=%1$s&dates=%2$s';
				break;
		}

		$url = sprintf($url,$id,$dates);

		$this->getXML($url);
	}

	/**
	 * Returns datas get by getXML
	 *
	 * @return:	array
	 */
	public function getDatas()
	{
		return $this->datas;
	}
	
	/**
	 * Returns errors get by getXML
	 *
	 * @return:	array
	 */
	public function getErrors()
	{
		return $this->errors;
	}
	
	public function getFeeds()
	{
		return $this->feeds;
	}
	
	public function getCsv()
	{
		header('Content-Type: text/plain');
		
		$tmp = 0;
		
		foreach ($this->datas as $k => $v) {
			if ($v['circulation'] != 0 && $v['hits'] != 0) {
				echo $v['date'].','.$v['circulation'].','.substr($tmp*100/($k+1),0,4).','.$v['hits']."\n";
				$tmp = $tmp + $v['circulation'];
				$tmp = $v['circulation'];
			}
		}
		
		exit;
	}

}

?>
