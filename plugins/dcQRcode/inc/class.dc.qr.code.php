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
	public $qrc_ec_level = 'L';
	public $qrc_ec_margin = 1;
	public $qrc_out_enc = 'UTF-8';

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
			$cur->qrcode_size = (string) $this->size;

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
		$args['choe'] = $this->qrc_out_enc;
		$args['chld'] = $this->qrc_ec_level.'|'.$this->qrc_ec_margin;
		$args['chl'] = $this->tweakData();

		return $args;
	}

	public function getImage()
	{
		$f = $this->cache_path.'/'.$this->size.'-'.$this->id.'.png';
		if ($this->cache_path !== null && file_exists($f))
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
				$client->setUserAgent('Dotclear - http://www.dotclear.net/');
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
				if ($this->cache_path !== null)
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

	# These 3 next function scould be overwrite to be used with your own type of data

	public function decode($id)
	{
		$this->id = (integer) $id;


		# --BEHAVIOR-- QRcodeDecode
		$this->core->callBehavior('QRcodeDecode',$this,$id);


		$this->getId();

		return $this->id;
	}

	public function encode()
	{
		$num_args = func_num_args();
		$args = func_get_args();

		# URL
		if ($this->type == 'URL' && !empty($args[0]))
		{
			$data = array(
				'URL' => $args[0],
				'TITLE' => (!empty($args[1]) ? text::toUTF8($args[1]) : '')
			);

			$this->data = serialize($data);
		}
		
		# MECARD
		if ($this->type == 'MECARD' && $num_args == 4)
		{
			$data = array(
				'N' => text::toUTF8($args[0]),
				'ADR' => text::toUTF8($args[1]),
				'TEL' => $args[2],
				'EMAIL' => $args[3]
			);

			$this->data = serialize($data);
		}
		# geo
		if ($this->type == 'GEO' && $num_args == 3)
		{
			$data = array(
				'latitude' => $args[0],
				'longitude' => $args[1],
				'altitude' => $args[2]
			);

			$this->data = serialize($data);
		}
		# market (Android)
		if ($this->type == 'MARKET' && $num_args == 2)
		{
			$data = array(
				'cat' => $args[0], //(pname,pub)
				'search' => $args[1]
			);

			$this->data = serialize($data);
		}
		# iCAL
		if ($this->type == 'ICAL' && $num_args == 3)
		{
			$data = array(
				'summary' => text::toUTF8($args[0]),
				'start-date' => $args[1],
				'end-date' => $args[2]
			);

			$this->data = serialize($data);
		}


		# --BEHAVIOR-- QRcodeEncode
		$this->core->callBehavior('QRcodeEncode',$this,$args);


		$this->setId();

		return $this->id;
	}

	public function tweakData()
	{
		# URL
		if ($this->type == 'URL')
		{
			$data_array = unserialize($this->data);

			if ($this->params['use_mebkm'])
			{
				$data = 'MEBKM:TITLE:';
				$data .= !empty($data_array['TITLE']) ? $data_array['TITLE'] : 'link';
				$data .= ';URL:'.$data_array['URL'].';';
			}
			else
			{
				$data = $data_array['URL'];
			}
			return $data;
		}
		# MECARD
		if ($this->type == 'MECARD')
		{
			$data_array = unserialize($this->data);

			$data = 'MECARD:';
			$data .= 'N:'.$data_array['N'].';';
			$data .= 'ADR:'.$data_array['ADR'].';';
			$data .= 'TEL:'.$data_array['TEL'].';';
			$data .= 'EMAIL:'.$data_array['EMAIL'].';';

			return $data;
		}
		# geo
		if ($this->type == 'GEO')
		{
			$data_array = unserialize($this->data);

			$data = 'geo:';
			$data .= $data_array['latitude'].',';
			$data .= $data_array['longitude'].',';
			$data .= $data_array['altitude'];

			return $data;
		}
		# market
		if ($this->type == 'MARKET')
		{
			$data_array = unserialize($this->data);

			$data = 'market://search?q=';
			$data .= $data_array['type'].'%3A';
			$data .=  $data_array['type'] == 'pub' ?
				'%22'.$data_array['search'].'%22' :	$data_array['search'];

			return $data;
		}
		# iCAL
		if ($this->type == 'ICAL')
		{
			$data_array = unserialize($this->data);

			$data = 'BEGIN:VEVENT'."\n";
			$data .= 'SUMMARY:'.$data_array['summary']."\n";
			$data .= 'DTSTART:'.$data_array['start-date']."\n";
			$data .= 'DTEND:'.$data_array['end-date']."\n";
			$data .= 'END:VEVENT'."\n";

			return $data;
		}


		# --BEHAVIOR-- QRcodeTweakData
		$this->core->callBehavior('QRcodeTweakData',$this);


		return $this->data;
	}
}
?>