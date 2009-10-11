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

class dbSpyExportCSV {

	public $settings = array(
		'ext' => '.csv',
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

	public static function nl($text,$nl='\n',$chr='')
	{
		if (empty($nl)) { $nl = array('\n','\r','\r\n'); }
		if (empty($chr)) { $chr = chr(10); }
		return str_replace($nl,$chr,$text);
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
		if ($this->settings['view_field']) {
			$rs = $this->table->getFields();
			foreach($rs AS $key => $val) {
				$this->res .= $key.';';
			}
			$this->res .= '\n';
		}
		$rs = $this->table->getRows(0,$this->settings['max_rows']);
		foreach($rs AS $key => $row) {
			foreach($row AS $field => $val) {
				if (is_numeric($field)) { break; }
				if (is_null($val) && $this->settings['convert_null'] === TRUE) {
					$val = 'NULL';
				}
				$this->res .= $val.';';
				$this->error();
			}
			$this->res .= '\n';
		}
	}

	public function get()
	{
		return self::nl($this->res);
	}

	public function save($path,$name)
	{
		if ('/' != substr($path,-1,1)) { $path .= '/'; }
		$full_path = $path.$name.$this->settings['ext'];
		$done = @file_put_contents($full_path,self::nl2chr($this->res,$this->settings['nl']));
		if (!$done) {
			throw new Exception(__('Unable to write file:').' '.$full_path);
			exit(1);
		}
		if ($done) {
			ob_end_clean();
			ob_start();
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=".basename($full_path));
			header("Accept-Ranges: bytes");
			header("Content-Length: ".filesize($full_path));
			@readfile($full_path);
			ob_end_flush();
			exit(1);
		}
	}
}

?>