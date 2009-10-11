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

class dbSpyDbMysql {

	protected $name; #name of current table
	protected $sql_request; #array of sql request done during script execution

	protected $reference_action = array('CASCADE','SET NULL','NO ACTION','RESTRICT'); #reference key ON DELETE and ON UPDATE action allowed
	protected $field_type = array('smallint','integer','bigint','real','float','numeric','date','time','timestamp','char','varchar','text'); #field type allowed

	public function __construct($name,$prefix,$con)
	{
		$this->name = $name;
		$this->con = $con;
		$this->prefix = $prefix;
		self::setTableStatus();
	}

	public function getSqlRequest($last=FALSE)
	{
		return ($last) ? current($this->sql_request) : $this->sql_request;
	}

	public function getTableName()
	{
		return $this->name;
	}

	public function getTableStatus()
	{
		return $this->info;
	}

	public function getSize()
	{
		return $this->info['size'];
	}

	public function getRowsNum()
	{
		return self::setSqlRequest('SELECT count(*) FROM '.$this->name)->f(0);
	}

	public function getKeys($type='')
	{
		switch ($type) {
			case 'primary': $where = 'Key_name = \'PRIMARY\''; break;
			case 'unique': $where = 'Non_unique = 0 AND Key_name != \'PRIMARY\''; break;
			default: $where = 'Non_unique = 0 OR Key_name = \'PRIMARY\''; break;
		}
		$rs = self::setSqlRequest('SHOW INDEX FROM '.$this->name.' WHERE '.$where);

		$t = array();
		$res = array();
		while ($rs->fetch())
		{
			$key_name = $rs->f('Key_name');
			$unique = $rs->f('Non_unique') == 0;
			$seq = $rs->f('Seq_in_index');
			$col_name = $rs->f('Column_name');

			$t[$key_name]['cols'][$seq] = $col_name;
			$t[$key_name]['unique'] = $unique;
		}

		foreach ($t as $name => $idx)
		{
			ksort($idx['cols']);
			
			$res[] = array(
				'name' => $name,
				'primary' => $name == 'PRIMARY',
				'unique' => $idx['unique'],
				'cols' => array_values($idx['cols'])
			);
		}
		return $res;
	}

	public function getIndexes()
	{
		$rs = self::setSqlRequest('SHOW INDEX FROM '.$this->name.' WHERE Non_unique != 0 AND Key_name != \'PRIMARY\'');
		$t = array();
		$res = array();
		while ($rs->fetch())
		{
			$key_name = $rs->f('Key_name');
			$seq = $rs->f('Seq_in_index');
			$col_name = $rs->f('Column_name');
			$type = $rs->f('Index_type');

			$t[$key_name]['cols'][$seq] = $col_name;
			$t[$key_name]['type'] = $type;
		}
		foreach ($t as $name => $idx)
		{
			ksort($idx['cols']);
			
			$res[] = array(
				'name' => $name,
				'type' => $idx['type'],
				'cols' => $idx['cols']
			);
		}
		return $res;
	}

	public function getReferences()
	{
		// bad hack!
		// nut wait for future version of MySql with INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
		$rs = self::setSqlRequest('SHOW CREATE TABLE '.$this->name);
		$s = $rs->f(1);
		$res = array();
		
		$n = preg_match_all('/^\s*CONSTRAINT\s+`(.+?)`\s+FOREIGN\s+KEY\s+\((.+?)\)\s+REFERENCES\s+`(.+?)`\s+\((.+?)\)(.*?)$/msi',$s,$match);
		if ($n > 0)
		{
			foreach ($match[1] as $i => $name)
			{
				# Columns transformation
				$t_cols = str_replace('`','',$match[2][$i]);
				$t_cols = explode(',',$t_cols);
				$r_cols = str_replace('`','',$match[4][$i]);
				$r_cols = explode(',',$r_cols);
				
				# ON UPDATE|DELETE
				$on = trim($match[5][$i],', ');
				$on_delete = null;
				$on_update = null;
				if ($on != '') {
					if (preg_match('/ON DELETE (.+?)(?:\s+ON|$)/msi',$on,$m)) {
						$on_delete = strtolower(trim($m[1]));
					}
					if (preg_match('/ON UPDATE (.+?)(?:\s+ON|$)/msi',$on,$m)) {
						$on_update = strtolower(trim($m[1]));
					}
				}
				
				$res[] = array (
					'name' => $name,
					'c_cols' => $t_cols,
					'p_table' => $match[3][$i],
					'p_cols' => $r_cols,
					'update' => $on_update,
					'delete' => $on_delete
				);
			}
		}
		return $res;
	}

	public function getUniques()
	{
		$keys = $this->getKeys('unique');
		foreach($keys AS $key) {
				$uniques[] = $key;
		}
		return $uniques;
	}

	public function getPrimary()
	{
		$keys = $this->getKeys('primary');
		return $keys[0]['cols'];
	}

	public function getFields()
	{
		$rs = self::setSqlRequest('SHOW COLUMNS FROM '.$this->name);

		$res = array();
		while ($rs->fetch())
		{
			$field = trim($rs->f('Field'));
			$type = trim($rs->f('Type'));
			$null = strtolower($rs->f('Null')) == 'yes';
			$default = $rs->f('Default');

			$len = null;
			if (preg_match('/^(.+?)\(([\d,]+)\)$/si',$type,$m)) {
				$type = $m[1];
				$len = (integer) $m[2];
			}

			if ($default != '' && !is_numeric($default)) {
				$default = "'".$default."'";
			}

			$res[$field] = array(
				'type' => $type,
				'len' => $len,
				'null' => $null,
				'default' => $default
			);
		}
		return $res;
	}

	public function getRows($start=0,$limit=20,$order_col='',$order_way='ASC',$where=array())
	{
		$sql = 'SELECT * FROM '.$this->con->escape($this->name).' ';
		foreach($where AS $k => $v) {
			//option $where = array(field => value,...) and only work on AND mode
			$sql .= (!isset($first))?'WHERE ':'AND '; $first = 1;
			$sql .= ((null) == $v)?$this->con->escape($k)." IS NULL ":$this->con->escape($k)." = '".$this->con->escape($v)."' ";
		}
		$sql .= ' '.((!empty($order_col))?" ORDER BY ".$order_col." ".$order_way:'');
		$sql .= ' LIMIT '.$start.','.$limit;

		return self::setSqlRequest($sql)->rows();
	}

	public function getNextId()
	{
		$fields = $this->getFields();
		$primary = $this->getPrimary();

		if (!empty($primary)) {
			$key = $ord = '';
			foreach($primary AS $p) {
				$key .= $this->con->escapeSystem($p).", ";
				$ord .= $this->con->escapeSystem($p)." DESC, ";
			}
			$res = self::setSqlRequest('SELECT '.substr($key,0,-2).' FROM '.$this->name.' ORDER BY '.substr($ord,0,-2).' LIMIT 1')->rows();

			foreach($primary AS $p) {
				if (is_numeric($res[0][$p])) {
					$next_id[$p] = $res[0][$p] + 1;
				} else {
					$next_id[$p] = substr($fields[$p]['default'],1,-1);
				}
			}
			return $next_id;
		}
		return '';
	}
#set
	private function setSqlRequest($str)
	{
		$this->sql_request[] = $str;
		$type = substr(trim($str),0,4);
		return ('SELE' == $type || 'SHOW' == $type) ? $this->con->select($str) : $this->con->execute($str);
	}

	public function setTableStatus()
	{
		$rs = self::setSqlRequest('SHOW TABLE STATUS LIKE \''.$this->name.'\'');
		if ($this->name != $rs->f('Name')) {
			throw new Exception('Invalid table name');
			exit(1);
		}
		$this->info = array(
			'rows' 		=> self::getRowsNum(),
			'size' 		=> $rs->f('Data_length')+$rs->f('Index_length'),
			'create_date' 	=> $rs->f('Create_time'),
			'charset' 	=> $rs->f('Collation')
			);
	}

	public function setKey($type,$cols)
	{
		if (empty($cols)) return;
		if (!is_array($cols)) { $cols = array($cols); }
		$c = array();
		$name = '';
		foreach ($cols as $v) {
			$c[] = $this->con->escapeSystem($v);
			$name .= '_'.$v;
		}		
		if ($type == 'primary') {
			$key = 'PRIMARY KEY ('.implode(',',$c).') '; 
		} else {
			$key = 'UNIQUE KEY '.$this->con->escapeSystem($this->prefix.'uk'.$name).' ('.implode(',',$c).') ';
			$name = $this->prefix.'uk';
		}
		self::setSqlRequest('ALTER TABLE '.$this->name.' ADD CONSTRAINT '.$key);
	}

	public function setIndex($cols)
	{		
		if (empty($cols)) { return ''; }
		if (!is_array($cols)) { $cols = array($cols); }

		$name = $this->prefix.'idx_'.substr($this->name,strlen($this->prefix));

		$c = array();
		foreach ($cols as $v) {
			$c[] = $this->con->escapeSystem($v);
			$name .= '_'.$v;
		}
		
		self::setSqlRequest('ALTER TABLE '.$this->name.' '.
			'ADD INDEX '.$this->con->escapeSystem($name).' USING BTREE ('.implode(',',$c).') ');
	}

	public function setReference($c_cols,$p_table,$p_cols,$update,$delete)
	{
		if (empty($c_cols) || empty($p_table) || empty($p_cols)) { return ''; }
		if (!in_array($update,$this->reference_action) || !in_array($delete,$this->reference_action)) { return ''; }
		if (!is_array($c_cols)) { $c_cols = array($c_cols); }
		if (!is_array($p_cols)) { $p_cols = array($p_cols); }

		$name = $this->prefix.'fk_'.substr($this->name,strlen($this->prefix)).'_'.$p_table;

		$c = array();
		$p = array();
		foreach ($c_cols as $v) {
			$c[] = $this->con->escapeSystem($v);
		}
		foreach ($p_cols as $v) {
			$p[] = $this->con->escapeSystem($v);
		}
		
		$sql =
		'ALTER TABLE '.$this->name.' '.
		'ADD CONSTRAINT '.$name.' FOREIGN KEY '.
		'('.implode(',',$c).') '.
		'REFERENCES '.$this->con->escapeSystem($p_table).' '.
		'('.implode(',',$p).') ';
		
		if ($update) {
			$sql .= 'ON UPDATE '.$update.' ';
		}
		if ($delete) {
			$sql .= 'ON DELETE '.$delete.' ';
		}
		self::setSqlRequest($sql);
	}

	public function setUnique($cols)
	{
		$this->setKey('unique',$cols);
	}

	public function setPrimary($cols)
	{
		$this->setKey('primary',$cols);
	}

	public function setField($name,$type,$len,$null=true,$default=false)
	{
		if (!in_array($type,$this->field_type)) {
			throw new Exception('Invalid field type '.$type);
		}
		if (empty($default)) { $default = FALSE; }
		$len = (integer) $len;
		$null = (boolean) $null;

		$type = $this->schema->udt2dbt($type,$len,$default);
		$len = (integer) $len > 0 ? '('.(integer) $len.')' : '';
		$null = $null ? 'NULL' : 'NOT NULL';
		
		if ($default === null) {
			$default = 'DEFAULT NULL';
		} elseif ($default !== false) {
			$default = 'DEFAULT '.$default;
		} else {
			$default = '';
		}
		
		self::setSqlRequest('ALTER TABLE '.$this->name.' '.
			'ADD COLUMN '.$this->con->escapeSystem($name).' '.$type.$len.' '.$null.' '.$default);
	}

	/**
	* function setRow
	*
	* $row, array, Row to insert or update, array(feild_name => value)
	* $where, array, Primary key value of row to edit, array(field_name => value)
	*
	* Insert or update one row at a time
	*/
	public function setRow($row,$where=array())
	{
		# Retrieve keys info
		$fields = $this->getFields();

		# Insert
		if (empty($where)) {
			$new_key = $new_val = '';
			foreach($row AS $key => $val) {
				$new_key .= $this->con->escape($key).", ";
				$new_val .= (TRUE === $fields[$key]['null'] && empty($val))?'NULL, ':"'".$this->con->escape($val)."', ";
			}
			self::setSqlRequest('INSERT INTO '.$this->name.' ('.substr($new_key,0,-2).') VALUES ('.substr($new_val,0,-2).')');

		# Update
		} else {
			$new =$req_where = '';
			foreach($where AS $key => $val) {
				$req_where .= ((!empty($req_where))?'AND ':'').$this->con->escape($key)." = '".$val."' ";
			}
			foreach($row AS $key => $val) {
				$new .= "`".$this->con->escape($key)."` = ". ((TRUE === $fields[$key]['null'] && empty($val))?"NULL, ":"'".$this->con->escape($val)."', ");
			}
			if (!empty($new) && !empty($req_where)) {
				self::setSqlRequest('UPDATE '.$this->name.' SET '.substr($new,0,-2).' WHERE '.$req_where);
			}
		}
	}
#unset
	public function unsetKey($name)
	{
		($name == 'PRIMARY') ?
			self::setSqlRequest('ALTER TABLE '.$this->name.' DROP PRIMARY KEY') :
			self::setSqlRequest('ALTER TABLE '.$this->name.' DROP INDEX '.$this->con->escapeSystem($name));
	}

	public function unsetIndex($name)
	{
		self::setSqlRequest('ALTER TABLE '.$this->name.' DROP INDEX '.$this->con->escapeSystem($name));
	}

	public function unsetReference($name)
	{
		self::setSqlRequest('ALTER TABLE '.$this->name.' DROP FOREIGN KEY '.$this->con->escapeSystem($name));
	}

	public function unsetUnique($name)
	{
		$this->unsetKey($name);
	}

	public function unsetPrimary($name)
	{
		$this->unsetKey($name);
	}

	public function unsetField($name)
	{
		self::setSqlRequest('ALTER TABLE '.$this->name.' DROP COLUMN '.$this->con->escapeSystem($name));
	}

	public function unsetRow($where)
	{
		$str_where = '';
		foreach($where AS $key => $val) {
			$str_where .= ((!empty($str_where))?'AND ':'').$this->con->escape($key)." = '".$val."' ";
		}
		if (!empty($where)) {
			self::setSqlRequest('DELETE FROM '.$this->name.' WHERE '.$str_where);
		}
	}

# alter
	public function alterKey($name,$cols)
	{
		self::unsetKey($name);
		self::setKey($cols);
	}

	public function alterIndex($name,$cols)
	{
		self::unsetIndex($name);
		self::setIndex($cols);
	}

	public function alterUnique($name,$cols)
	{
		self::unsetUnique($name);
		self::setUnique($cols);
	}

	public function alterPrimary($name,$cols)
	{
		self::unsetPrimary('PRIMARY');
		self::setPrimary($cols);
	}

	public function alterField($name,$new_name,$type,$len,$null,$default)
	{
		$type = $this->schema->udt2dbt($type,$len,$default);
		$len = (integer) $len > 0 ? '('.(integer) $len.')' : '';
		$null = $null ? 'NULL' : 'NOT NULL';

		if ($default === null) {
			$default = 'DEFAULT NULL';
		} elseif ($default !== false) {
			$default = 'DEFAULT '.$default;
		} else {
			$default = '';
		}
		self::setSqlRequest('ALTER TABLE '.$this->name.' '.
			'CHANGE COLUMN '.$this->con->escapeSystem($name).' '.$this->con->escapeSystem($new_name).' '.$type.$len.' '.$null.' '.$default);
	}
	
	public function alterReference($name,$c_cols,$p_table,$p_cols,$update,$delete)
	{
		self::unsetReference($name);
		self::setReference($c_cols,$p_table,$p_cols,$update,$delete);
	}
}


?>