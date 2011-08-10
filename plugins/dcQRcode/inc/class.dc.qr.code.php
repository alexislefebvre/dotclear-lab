<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcQRcode, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')){return;}

class dcQRcode extends QRcodeCore
{
	public $core;
	public $con;
	public $blog;
	public $table;
	
	public function __construct($core,$cache_path=null)
	{
		$this->core = $core;
		$this->con = $core->con;
		$this->blog = $core->con->escape($core->blog->id);
		$this->table = $core->prefix.'qrcode';
		
		$core->blog->settings->addNameSpace('dcQRcode');
		$s = $core->blog->settings->dcQRcode;
		
		if ($cache_path)
			$this->setCachePath($cache_path);
		
		if ($s->qrc_api_url)
			$this->setApiURL($s->qrc_api_url);
		
		if ($s->qrc_api_ec_level)
			$this->setErrorCorrectionLevel($s->qrc_api_ec_level);
		
		if ($s->qrc_api_out_enc)
			$this->setOutputEncoding($s->qrc_api_out_enc);
		
		if ($s->qrc_api_ec_margin)
			$this->setMargin($s->qrc_api_ec_margin);
		
		if ($s->qrc_img_size)
			$this->setSize($s->qrc_img_size);
		
		if ($s->qrc_use_mebkm)
			$this->setMebkm($s->qrc_use_mebkm);
		
		$list = $this->core->getBehaviors('registerType');
		
		if (!empty($list)) {
			foreach($list as $callback)
			{
				try {
					list($type,$class) = call_user_func($callback);
					$this->registerType($type,$class);
				}
				catch (Exception $e) {}
			}
		}
	}
	
	protected function tweakEncode($args)
	{
		# --BEHAVIOR-- dcQRcodeEncode
		$this->core->callBehavior('dcQRcodeEncode',$this,$args);
	}
	
	protected function tweakDecode($id)
	{
		# --BEHAVIOR-- dcQRcodeDecode
		$this->core->callBehavior('dcQRcodeDecode',$this,$id);
	}
	
	public function getURL($id)
	{
		return $this->core->blog->url.$this->core->url->getBase('dcQRcodeImage').'/'.$this->getParam('prefix').$id.'.png';
	}
	
	# Retrieve qrcode info in db according to current id
	protected function getId()
	{
		$rs = $this->con->select(
			'SELECT * FROM '.$this->table.' '.
			"WHERE blog_id='".$this->blog."' ".
			"AND qrcode_id='".$this->id."' ".
			$this->con->limit(1));
		
		if (!$rs->isEmpty()) {
			$this->id = $rs->qrcode_id;
			$this->data = $rs->qrcode_data;
			$this->setSize($rs->qrcode_size);
			$this->setType($rs->qrcode_type);
			return true;
		}
		else {
			$this->id = $this->data = null;
			return false;
		}
	}
	
	# Add qrcode info in db and return id
	protected function setId()
	{
		$rs = $this->con->select(
			'SELECT qrcode_id FROM '.$this->table.' '.
			"WHERE blog_id='".$this->blog."' ".
			"AND qrcode_size = ".((integer) $this->getParam('size'))." ".
			"AND qrcode_type='".$this->getParam('type')."' ".
			"AND qrcode_data='".$this->con->escape($this->data)."' ".
			$this->con->limit(1));
		
		if ($rs->isEmpty()) {
			$this->id = $this->con->select(
				'SELECT MAX(qrcode_id) FROM '.$this->table
			)->f(0) + 1;
			
			$cur = $this->core->con->openCursor($this->table);
			$this->core->con->writeLock($this->table);
			
			$cur->blog_id = $this->blog;
			$cur->qrcode_type = (string) $this->getParam('type');
			$cur->qrcode_id = (integer) $this->id;
			$cur->qrcode_data = (string) $this->data;
			$cur->qrcode_size = (integer) $this->getParam('size');
			
			# --BEHAVIOR-- dcQRcodeBeforeCreate
			$this->core->callBehavior('dcQRcodeBeforeCreate',$cur);
			
			$cur->insert();
			$this->core->con->unlock();
			
			# --BEHAVIOR-- dcQRcodeAfterCreate
			$this->core->callBehavior('dcQRcodeAfterCreate',$cur);
			
		}
		else {
			$this->id = $rs->qrcode_id;
		}
		return $this->id;
	}
	
	# List qrcodes info from db
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
		
		$rs = $this->con->select($strReq);
		$rs->core = $this->core;
		$rs->qrc = $this;
		
		# --BEHAVIOR-- coreBlogGetPosts
		$this->core->callBehavior('blogGetQRcodes',$rs);
		
		return $rs;
	}
	
	# Delete qrcodes info from db according to its id
	public function delQRcode($id)
	{
		$id = (integer) $id;
		
		# --BEHAVIOR-- dcQRcodeBeforeDelete
		$this->core->callBehavior('dcQRcodeBeforeDelete',$id);
		
		$file = $this->imagePath($id);
		if (file_exists($file)) {
			if (!files::isDeletable($file)) {
				return false;
			}
			if (!@unlink($file)) {
				return false;
			}
		}
		
		return $this->con->execute(
			'DELETE FROM '.$this->table.' '.
			"WHERE blog_id='".$this->blog."' ".
			"AND qrcode_id='".$id."' "
		);
	}
	
	# QRC_CACHE_PATH could be set elsewhere (blog define, or multiblog config)
	public static function defineCachePath($core)
	{
		if (defined('QRC_CACHE_PATH')) {
			return;
		}
		if ($core->blog === null) {
			define('QRC_CACHE_PATH',null);
			return;
		}
		
		$custom = $core->blog->settings->dcQRcode->qrc_public_path;
		$default = $core->blog->public_path;
		
		# See if don't want cache
		if (!$core->blog->settings->dcQRcode->qrc_cache_use) {
			$qrc_cache_path = null;
		}
		# See if custom cache path exists and it is writable
		elseif (is_writable($custom)) {
			$qrc_cache_path = $custom;
		}
		# See if default cache path exists
		elseif (is_writable($default)) {
			if (!is_dir($default.'/qrc/')) {
				@mkdir($default.'/qrc/');
			}
			$qrc_cache_path = $default.'/qrc';
		}
		# Set constant
		define('QRC_CACHE_PATH',$qrc_cache_path);
	}
}
?>