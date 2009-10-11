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

class dbSpyExportSQL {

	public $settings = array(
		'ext' => '.sql',
		'nl' => '!n',
		'chr' => array('\r\n',''),
		'max_len' => 1000000,
		'max_rows' => 10000,
		'convert_null' => TRUE,
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
		$this->res =
			'-- Dotclear DatabaseSpy SQL Dump!n'.
			'-- version 0.1!n'.
			'-- !n'.
			'-- '.date('r').'!n'.
			'-- '.$type.' of table '.$tb_name.'!n!n'.
			'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";!n!n'.
			'-- !n'.
			'-- Database: `'.$db_name.'`!n'.
			'-- !n!n'.
			'-- --------------------------------------------------------!n!n';
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
			throw new Exception('Export class max file size exceded');
			exit(1);
		}
	}

	private function buildStructure()
	{
		$this->res .= 
			'-- !n'.
			'-- Table structure for table `'.$this->tb_name.'`!n'.
			'-- !n!n'.
			'CREATE TABLE "'.$this->tb_name.'" (!n';
		#fields
		$rows = $this->table->getFields();
		foreach($rows AS $name => $row) {
			$this->res .= '  "'.$name.'" ';
			if (empty($row['len'])) {
				$this->res .= $row['type'].' ';
			} else {
				$this->res .= $row['type'].'('.$row['len'].') ';
			}
			if ($row['type'] == 'varchar' || $row['type'] == 'longtext') {
				$this->res .= 'collate utf8_bin ';
			}
			if ($row['null']) {
				$this->res .= 'default NULL';
			} else {
				$this->res .= 'NOT NULL';
				if (!empty($row['default'])) {
					$this->res .= ' default '.$row['default'];
				}
			}
			$this->res .= ',!n';
		}
		#keys
		$rows = $this->table->getKeys();
		if (!empty($rows)) {
			foreach($rows AS $row) {
// Todo: see compatibility ?
				if ($row['primary']) {
					$this->res .= '  PRIMARY KEY ';
				} else {
					$this->res .= '  UNIQUE KEY "'.$row['name'].'" ';
				}
				$this->res .= '(';
				if (!is_array($row['cols'])) { $row['cols'] = array($row['cols']); }
				foreach($row['cols'] AS $col) {
					$this->res .= '"'.$col.'",';
				}
				$this->res = substr($this->res,0,-1).'),!n';
			}
		}
		#indexes
		$rows = $this->table->getIndexes();
		if (!empty($rows)) {
			foreach($rows AS $row) {
				$this->res .= '  KEY "'.$row['name'].'" (';
				if (!is_array($row['cols'])) { $row['cols'] = array($row['cols']); }
				foreach($row['cols'] AS $col) {
					$this->res .= '"'.$col.'",';
				}
				$this->res = substr($this->res,0,-1).'),!n';
			}
		}
		$this->res = substr($this->res,0,-3).'!n';
		$this->res .= ');!n!n';
	}

	private function buildConstraints()
	{
		#foreign
		$rows = $this->table->getReferences();
		if (!empty($rows)) {
			$this->res .=
				'-- !n'.
				'-- Constraints for table `'.$this->tb_name.'`!n'.
				'-- !n!n'.
				'ALTER TABLE `'.$this->tb_name.'` !n';
			foreach($rows AS $row) {
				$this->res .= '  ADD CONSTRAINT "'.$row['name'].'" FOREIGN KEY (';
				foreach($row['c_cols'] AS $c_col) {
					$this->res .= '"'.$c_col.'",';
				}
				$this->res = substr($this->res,0,-1).') REFERENCES "'.$row['p_table'].'" (';
				foreach($row['p_cols'] AS $p_col) {
					$this->res .= '"'.$p_col.'",';
				}
				$this->res = substr($this->res,0,-1).') ON DELETE '.strtoupper($row['delete']).' ON UPDATE '.strtoupper($row['update']).',!n';
			}
			$this->res = substr($this->res,0,-3).';!n!n';
		}
	}

	private function content()
	{
		$rows = $this->table->getRows(0,$this->settings['max_rows']);
		if (!empty($rows)) {
			$this->res .= 
				'-- !n'.
				'-- Dumping data for table `'.$this->tb_name.'`!n'.
				'-- !n!n'.
				'INSERT INTO `'.$this->tb_name.'` (';
			foreach($rows AS $row) {
				foreach($row AS $name => $val) {
					if (is_numeric($name)) { break; }
					$this->res .= "`$name`, ";
				}
				break;
			}
			$this->res = substr($this->res,0,-2).') VALUES !n';
			foreach($rows AS $row) {
				$this->res .= '  (';
				foreach($row AS $val) {
					if (is_null($val) && $this->settings['convert_null'] === TRUE) {
						$this->res .= "NULL, ";
					} else {
						$this->res .= "'".self::chr2nl(text::toUTF8($val),$this->settings['chr'])."', ";
					}
					$this->error();
				}
				$this->res = substr($this->res,0,-2).'),!n';
			}
			$this->res = substr($this->res,0,-3).';!n!n';
		}
	}

	private function structure()
	{
		self::buildStructure();
		self::buildConstraints();
	}

	private function all()
	{
		self::buildStructure();
		self::content();
		self::buildConstraints();
	}

	public function get()
	{
		return self::nl2chr($this->res,$this->settings['nl']);
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