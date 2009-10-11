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

class dbSpyExportHTML {

	public $settings = array(
		'ext' => '.html',
		'nl' => '!n',
		'chr' => array('\r\n',''),
		'max_len' => 1000000,
		'max_rows' => 10000,
		'convert_null' => FALSE,
		'view_field' => TRUE
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
		$this->res = '<html>!n <head>!n'.
			'  <title>DatabaseSpy export - '.$db_name.' - '.$tb_name.' - '.$type.'</title>!n'.
			'  <style type="text/css">!n'.
			'  /*<![CDATA[*/!n'.
			'  table {!n'.
			'    margin: 5px;!n'.
			'    border-collapse: collapse;!n'.
			'    empty-cells: show;!n'.
			'    display: inline;!n'.
			'  }!n'.
			'  td, th {!n'.
			'    padding: 3px;!n'.
			'    border: 1px solid #000000;!n'.
			'    white-space: nowrap;!n'.
			'  };!n'.
			'  /*]]>*/!n'.
			'  </style>!n'.
			' <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.
			' </head>!n'.
			' <body>!n'.
			'  <h2>Database: \''.$this->db_name.'\'<br /> Table: \''.$this->tb_name.'\'</h2>!n';
		self::$type();
		$this->res .= ' </body>!n</html>!n';

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

	private function buildTable($name,$rows='',$show_key=FALSE)
	{
		if (!empty($name)) {
			$this->res .= '  <h3>'.$name.' :</h3>!n';
		}
		if (empty($rows)) {
			$this->res .= '  <h4>Table is empty</h4>!n';
			return;
		}
		$this->res .= '  <table>!n';
		# titles
		if ($this->settings['view_field']) {
			$this->res .= '   <thead>!n    <tr>!n';
			foreach($rows AS $row) {
				if ($show_key) {
					$this->res .= '     <th>name</th>!n';
				}
				foreach($row AS $name => $val) {
					if (is_numeric($name)) { break; }
					$this->res .= '     <th>'.$name.'</th>!n';
				}
				break;
			}
			$this->res .= '    </tr>!n   </thead>!n';
		}
		# contents
		$this->res .= '   <tbody>!n';
		foreach($rows AS $key => $row) {
			$this->res .= '    <tr>!n';
			if ($show_key) {
				$this->res .= '     <td>'.$key.'</td>!n';
			}
			foreach($row AS $field => $val) {
				if (is_numeric($field)) { break; }
				$this->res .= '     <td>';
				if (is_array($val)) {
					$cols = '';
					foreach($val AS $k => $v) {
						$cols .= $v.'<br />';
					}
					$this->res .= substr($cols,0,-6);
				} else {
					$this->res .= self::chr2nl(htmlspecialchars(text::toUTF8($val)),$this->settings['chr']);
				}
				$this->res .= '</td>!n';
			}
			$this->res .= '    </tr>!n';
		}
		$this->res .= '   </tbody>!n  </table>!n';
	}

	private function content()
	{
		$rows = $this->table->getRows(0,$this->settings['max_rows']);
		$this->buildTable('Content',$rows);
	}

	private function structure()
	{
		#fields
		$rows = $this->table->getFields();
		$this->buildTable('Fields',$rows,TRUE);
		#keys
		$rows = $this->table->getKeys();
		$this->buildTable('Keys',$rows);
		#indexes
		$rows = $this->table->getIndexes();
		$this->buildTable('Indexes',$rows);
		#foreign
		$rows = $this->table->getReferences();
		$this->buildTable('Foreign keys',$rows);
	}

	private function all()
	{
		self::structure();
		self::content();
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