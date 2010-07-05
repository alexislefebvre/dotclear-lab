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
	public function __construct($core)
	{
		$this->core			= $core;
		$this->primary_xml 		= $core->blog->settings->feedburner->feedburner_primary_xml;
		$this->secondary_xml 	= $core->blog->settings->feedburner->feedburner_secondary_xml;
		$this->proxy			= $core->blog->settings->feedburner->feedburner_proxy;
		$this->feeds			= unserialize($core->blog->settings->feedburner->feedburner_feeds);
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
		$parser = false;

		$urls = array(
			'primary' => $this->primary_xml.$url,
			'secondary' => $this->secondary_xml.$url
		);

		$flag = false;

		foreach ($urls as $k => $v) { 
			if ($flag) {
				break;
			}
			try {
				$parser = feedburnerReader::quickParse($v,DC_TPL_CACHE,$this->proxy);
				$flag = true;
			}
			catch (Exception $e) {
				$tab = explode(':',$e->getMessage());
				$this->errors = array(
					'code' => (isset($tab[0]) ? trim($tab[0]) : ''),
					'msg' => (isset($tab[1]) ? trim($tab[1]) : '')
				);
				$flag = false;
			}
		}

		if ($parser === false) {
			$this->errors = array(
				'code' => 0,
				'msg' => __('Impossible to retrieve the feed statistics')
			);
			return false;
		}
		else {
			$this->datas = $parser->getDatas();
			$this->errors = $parser->getError();
			return true;
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

	/**
	 * Returns feed list
	 *
	 * @return:	array
	 */
	public function getFeeds()
	{
		return $this->feeds;
	}

	/**
	 * Returns csv data for amstock
	 *
	 * @return:	csv
	 */
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