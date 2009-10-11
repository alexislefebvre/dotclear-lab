<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of databasespy, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dbSpyExportPDF {

	public $settings = array(
		'ext' => '.pdf',
		'nl' => '!n',
		'chr' => array('\r\n',''),
		'max_len' => 1000000,
		'max_rows' => 10000,
		'convert_null' => FALSE,
		'view_field' => FALSE
		);
	private $res = '';

	public function __construct($db_name,$tb_name,$type,$table)
	{
		if (!method_exists($this,$type)) {
			throw new Exception(__('Unsupported method:').' '.$type);
			exit(1);
		}
		$this->db_name = $db_name;
		$this->tb_name = $tb_name;
		$this->type = $type;
		$this->table = $table;
		self::$type();
	}

	public static function nl2chr($text,$nl='\n',$chr='')
	{
		if (empty($nl)) { $nl = array('\n','\r','\r\n'); }
		if (empty($chr)) { $chr = chr(10); }
		return str_replace($nl,$chr,$text);
	}

	public static function chr2nl($text,$nl='\n',$chr='')
	{
		if (empty($chr)) { $chr = array(chr(10),chr(13)); }
		if (empty($nl)) { $nl = array('\n','\r'); }
		return str_replace($chr,$nl,$text);
	}

	private function error()
	{
		if ($this->settings['max_len'] < strlen($this->res)) {
			throw new Exception('Max file size exceded');
			exit(1);
		}
	}

	private function content()
	{
		throw new Exception('Fcuntion "content" not write yet');
		exit(1);
	}

	private function structure()
	{
		throw new Exception('Function "structure" not write yet');
		exit(1);
	}

/*	private function all()
	{
		throw new Exception('Export all PDF class not write yet');
		exit(1);
	}
*/
	public function get()
	{
		throw new Exception('Function "get" not write yet');
		exit(1);
	}

	public function save($path,$name)
	{
		throw new Exception('Function "save" not write yet');
		exit(1);
	}
}

?>