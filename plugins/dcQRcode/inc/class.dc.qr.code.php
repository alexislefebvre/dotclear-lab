<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
#
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class dcQRcode
{
	public $core;
	public $con;
	public $blog;
	public $table;

	public $qrc_api_url = 'http://chart.apis.google.com/chart?';
	public $qrc_api_ec_level = 'L';
	public $qrc_api_ec_margin = 2;
	public $qrc_api_out_enc = 'UTF-8';

	protected $data = '';
	protected $id = '';
	protected $type = 'URL';
	protected $size = 128;
	protected $cache_path = null;
	protected $params = array('use_mebkm'=>true);

	public function __construct($core,$cache_path=null)
	{
		$this->core = $core;
		$this->con = $core->con;
		$this->blog = $core->con->escape($core->blog->id);
		$this->table = $core->prefix.'qrcode';

		$this->cache_path = $cache_path;

		if (!$core->blog->settings->qrc_api_url)
			$this->qrc_api_url = $core->blog->settings->qrc_api_url;

		if (!$core->blog->settings->qrc_api_ec_level)
			$this->qrc_api_ec_level = $core->blog->settings->qrc_api_ec_level;

		if (!$core->blog->settings->qrc_api_ec_margin)
			$this->qrc_api_ec_margin = $core->blog->settings->qrc_api_ec_margin;

		if (!$core->blog->settings->qrc_api_out_enc)
			$this->qrc_api_out_enc = $core->blog->settings->qrc_api_out_enc;
	}

	public function setType($type='URL')
	{
		$this->type = (string) $type;
	}

	public function setSize($size=128)
	{
		$size = (integer) $size;
		$this->size = $size > 10 && $size < 550 ? $size : 128;
	}

	public function setParams($ns,$params)
	{
		$ns = (string) $ns;
		$this->params[$ns] = $params;
	}

	protected function getId()
	{
		$rs = $this->con->select(
			'SELECT * FROM '.$this->table.' '.
			"WHERE blog_id='".$this->blog."' ".
			"AND qrcode_id='".$this->id."' ".
			$this->con->limit(1));

		if (!$rs->isEmpty())
		{
			$this->id = $rs->qrcode_id;
			$this->data = $rs->qrcode_data;
			$this->size = $rs->qrcode_size;
			$this->type = $rs->qrcode_type;
			return true;
		}
		else
		{
			$this->id = null;
			$this->data = null;
			return false;
		}
	}

	protected function setId()
	{
		$rs = $this->con->select(
			'SELECT qrcode_id FROM '.$this->table.' '.
			"WHERE blog_id='".$this->blog."' ".
			"AND qrcode_size='".$this->size."' ".
			"AND qrcode_type='".$this->type."' ".
			"AND qrcode_data='".$this->data."' ".
			$this->con->limit(1));

		if ($rs->isEmpty())
		{
			$this->id = $this->con->select(
				'SELECT MAX(qrcode_id) FROM '.$this->table)->f(0) + 1;

			$cur = $this->core->con->openCursor($this->table);
			$this->core->con->writeLock($this->table);

			$cur->blog_id = $this->blog;
			$cur->qrcode_type = (string) $this->type;
			$cur->qrcode_id = (integer) $this->id;
			$cur->qrcode_data = (string) $this->data;
			$cur->qrcode_size = (integer) $this->size;

			$cur->insert();
			$this->core->con->unlock();
		}
		else
		{
			$this->id = $rs->qrcode_id;
		}
		return $this->id;
	}

	public function getArgs()
	{
		$args = array();
		$args['cht'] = 'qr';
		$args['chs'] = $this->size.'x'.$this->size;
		$args['choe'] = $this->qrc_api_out_enc;
		$args['chld'] = $this->qrc_api_ec_level.'|'.$this->qrc_api_ec_margin;
		$args['chl'] = $this->tweakData();

		return $args;
	}

	public function getImage($force_no_cache=false)
	{
		$f = $this->cache_path.'/'.$this->size.'-'.$this->id.'.png';
		if ($this->cache_path !== null && file_exists($f) && !$force_no_cache)
		{
			readfile($f);
		}
		else
		{
			try
			{
				$args = $this->getArgs();
				$path = '';
				$client = netHttp::initClient($this->qrc_api_url,$path);
				$client->setUserAgent('dcQRcode - http://dotclear.jcdenis.com/go/dcQRcode');
				$client->useGzip(false);
				$client->setPersistReferers(false);
				$client->get($path,$args);

				$response = $client->getContent();
			}
			catch (Exception $e)
			{
				throw new Exception('An error occured: '.$e->getMessage());
			}

			if ($client->getStatus() != 200) {
				throw new Exception('Failed to get content: <pre>'.print_r($args,true).'</pre>');
			}
			else
			{
				if ($this->cache_path !== null && !$force_no_cache)
				{
					file_put_contents($f,$response);
				}
				echo $response;
			}
		}
		exit;
	}

	public function cleanCache()
	{
		if (!is_dir($this->cache_path)) return null;

		return files::delTree($this->cache_path);
	}

	public function getQRcodes($p,$count_only=false)
	{
		if ($count_only)
			$strReq = 'SELECT count(Q.qrcode_id) ';

		else {
			$content_req = '';
			
			if (!empty($p['columns']) && is_array($p['columns']))
				$content_req .= implode(', ',$p['columns']).', ';

			$strReq = 'SELECT Q.qrcode_type, Q.qrcode_id, Q.qrcode_data, '.
				$content_req.'Q.qrcode_size ';
		}

		$strReq .= 'FROM '.$this->table.' Q ';

		if (!empty($p['from']))
			$strReq .= $p['from'].' ';

		$strReq .= "WHERE Q.blog_id = '".$this->blog."' ";

		if (isset($p['qrcode_type'])) {

			if (is_array($p['qrcode_type']) && !empty($p['qrcode_type']))
				$strReq .= 'AND qrcode_type '.$this->con->in($p['qrcode_type']);

			elseif ($p['qrcode_type'] != '')
				$strReq .= "AND qrcode_type = '".$this->con->escape($p['qrcode_type'])."' ";
		}

		if (isset($p['qrcode_id'])) {

			if (is_array($p['qrcode_id']) && !empty($p['qrcode_id']))
				$strReq .= 'AND qrcode_id '.$this->con->in($p['qrcode_id']);

			elseif ($p['qrcode_id'] != '')
				$strReq .= "AND qrcode_id = '".$this->con->escape($p['qrcode_id'])."' ";
		}

		if (!empty($p['qrcode_data'])) {

				$strReq .= "AND qrcode_data = '".$this->con->escape($p['qrcode_data'])."' ";
		}

		if (isset($p['qrcode_size'])) {

			if (is_array($p['qrcode_size']) && !empty($p['qrcode_size']))
				$strReq .= 'AND qrcode_size '.$this->con->in($p['qrcode_size']);

			elseif ($p['qrcode_size'] != '')
				$strReq .= "AND qrcode_size = '".$this->con->escape($p['qrcode_size'])."' ";
		}

		if (!empty($p['sql'])) 
			$strReq .= $p['sql'].' ';

		if (!$count_only) {
			$strReq .= empty($p['order']) ?
				'ORDER BY qrcode_id DESC ' :
				'ORDER BY '.$this->con->escape($p['order']).' ';
		}

		if (!$count_only && !empty($p['limit'])) 
			$strReq .= $this->con->limit($p['limit']);

		return $this->con->select($strReq);
	}

	public function delQRcode($id)
	{
		$id = (integer) $id;

		return $this->con->execute(
			'DELETE FROM '.$this->table.' '.
			"WHERE blog_id='".$this->blog."' ".
			"AND qrcode_id='".$id."' "
		);
	}

	public static function escape($str,$escape=false,$toUTF8=true)
	{
		if ($toUTF8)
		{
			$str = text::toUTF8($str);
		}
/*
		if ($escape)
		{
			$str = str_replace(
				array('е',':',';',','),
				array('ее','е:','е;','е,'),
				$str
			);
		}
//*/
		return $str;
	}

	public static function unescape($str)
	{
/*
		$str = str_replace(
			array('ее','е:','е;','е,'),
			array('е',':',';',','),
			$str
		);
//*/
		return $str;
	}

	public function decode($id)
	{
		$this->id = (integer) $id;


		# --BEHAVIOR-- dcQRcodeDecode
		$this->core->callBehavior('dcQRcodeDecode',$this,$id);


		$this->getId();

		return $this->id;
	}

	public function encode()
	{
		$num_args = func_num_args();
		$args = func_get_args();

		$data = '';

		# TXT
		if ($this->type == 'TXT' && !empty($args[0]))
		{
			$data = self::escape($args[0]);
		}
		# URL
		if ($this->type == 'URL' && $num_args > 0)
		{
			if ($this->params['use_mebkm'])
			{
				$data = 'MEBKM:TITLE:';
				$data .= !empty($args[1]) ? self::escape($args[1],true) : 'link';
				$data .= ';URL:'.self::escape($args[0],true).';';
			}
			else
			{
				$data = self::escape($args[0]);
			}
		}
		# MECARD
		if ($this->type == 'MECARD' && $num_args > 3)
		{
			$data = 'MECARD:';
			$data .= 'N:'.self::escape($args[0],true).';';
			$data .= 'ADR:'.self::escape($args[1],true).';';

			foreach($args[2] as $param)
			{
				if (!empty($param))
					$data .= 'TEL:'.self::escape($param,true).';';
			}

			foreach($args[3] as $param)
			{
				if (!empty($param))
					$data .= 'EMAIL:'.self::escape($param,true).';';
			}

			if (!empty($args[4]))
				$data .= 'URL:'.self::escape($args[4],true).';';

			if (!empty($args[5]))
				$data .= 'BDAY:'.self::escape($args[5],true).';';

			if (!empty($args[6]))
				$data .= 'NOTE:'.self::escape($args[6],true).';';

			if (!empty($args[7]))
				$data .= 'NICKNAME:'.self::escape($args[7],true).';';

			if (!empty($args[8]))
				$data .= 'TEL-AV:'.self::escape($args[8],true).';';

			if (!empty($args[9]))
				$data .= 'SOUND:'.self::escape($args[9],true).';';

			$data .= ';';
		}
		# geo
		if ($this->type == 'GEO' && $num_args > 1)
		{
			$data = 'geo:';
			$data .= str_replace(',','.',$args[0]).',';
			$data .= str_replace(',','.',$args[1]).',';
			$data .= isset($args[2]) ? $args[2] : '100';
		}
		# market (Android)
		if ($this->type == 'MARKET' && $num_args == 2)
		{
			$data = 'market://search?q=';
			$data .= $args[0].'%3A';
			$data .=  $args[0] == 'pub' ?
				'%22'.self::escape($args[0]).'%22' : self::escape($args[0]);
		}
		# iCAL
		if ($this->type == 'ICAL' && $num_args == 3)
		{
			$data = 'BEGIN:VEVENT'."\n";
			$data .= 'SUMMARY:'.self::escape($args[0])."\n";
			$data .= 'DTSTART:'.$args[1]."\n";
			$data .= 'DTEND:'.$args[2]."\n";
			$data .= 'END:VEVENT'."\n";
		}
		# i-appli (i-phone)
		if ($this->type == 'IAPPLI' && $num_args > 1)
		{
			$data = 'LAPL:';
			$data .= 'ADFURL:'.self::escape($args[0],true).';';
			$data .= 'CMD:'.self::escape($args[1],true).';';
			if (isset($args[2]))
			{
				foreach($args[2] as $param)
				{
					if (!empty($param))
						$data .= 'PARAM:'.self::escape($param,true).';';
				}
			}
			$data .= ';';
		}
		# preformed email
		if ($this->type == 'MATMSG' && $num_args == 3)
		{
			$data = 'MATMSG:';
			$data .= 'TO:'.self::escape($args[0],true).';';
			$data .= 'SUB:'.self::escape($args[1],true).';';
			$data .= 'BODY:'.self::escape($args[2],true).';';
			$data .= ';';
		}

		$this->data = $data;


		# --BEHAVIOR-- dcQRcodeEncode
		$this->core->callBehavior('dcQRcodeEncode',$this,$args);


		$this->setId();

		return $this->id;
	}

	public function tweakData()
	{


		# --BEHAVIOR-- dcQRcodeTweakData
		$this->core->callBehavior('dcQRcodeTweakData',$this);


		return $this->data;
	}
}
?>