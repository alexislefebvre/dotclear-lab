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

class dbSpyDbVirtual {

	protected $name; #name of current table

	protected $reference_action = array('CASCADE','SET NULL','NO ACTION','RESTRICT'); #reference key ON DELETE and ON UPDATE action allowed
	protected $field_type = array('smallint','integer','bigint','real','float','numeric','date','time','timestamp','char','varchar','text'); #field type allowed

	protected $size; #sizeof current table
	protected $rows_num; #rows num of current table
	protected $keys = array(); #keys of current table
	protected $indexes = array(); #indexes keys of current table
	protected $references = array(); #references keys of current table
	protected $uniques = array(); #uniques keys of current table
	protected $primary = array(); #primary key of current table
	protected $fields = array(); #fields of current table
	protected $rows = array(); #rows of current table if it's new table

	public function __construct($name,$prefix,$con)
	{
		$this->name = $name;
		$this->prefix = $prefix;
		self::setTableStatus();
	}
#get
	public function getSqlRequest()
	{
		return array('Work on Virtual table, no request on database');
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
		return $this->rows_num;
	}

	public function getKeys()
	{
		return $this->keys;
	}

	public function getIndexes()
	{
		return $this->indexes;
	}

	public function getReferences()
	{
		return $this->references;
	}

	public function getUniques()
	{
		return $this->uniques;
	}

	public function getPrimary()
	{
		return $this->primary;
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getRows($start=0,$limit=20,$order_col='',$order_way='ASC',$where=array())
	{
		$rows = $this->rows;
		if (!empty($order_col)) {
			$way = ('ASC' == $order_way)?'SORT_ASC':'SORT_DESC';
			foreach($rows AS $key => $row) {
				$sort[$k] = $row[$order_col];
			}
			array_multisort($sort,$way,$rows);
		}
		foreach($rows AS $key => $row) {
			$diff = FALSE;
			foreach($where AS $k => $v) {
				if ($row[$k] != $v) { $diff = TRUE; break; }
			}
			if (!$diff) {
				$count_start++;
				if ($count_start > $start) {
					$res[] = $row;
					$count_limit++;
				}
			}
			if ($count_limit == $limit) { break; }
		}
		return $res;
	}

	public function getNextId()
	{
		return '';
	}
#set
	private function setSqlRequest()
	{
		return '';
	}

	public function setTableStatus()
	{
		$this->info = array(
			'rows' 		=> self::getRowsNum(),
			'size' 		=> 0,
			'create_date' 	=> date('c'),
			'charset' 	=> 'utf8'
			);
	}

	public function setKey($type='',$cols=array())
	{
		if (empty($cols)) { return ''; }
		if (!is_array($cols)) { $cols = array($cols); }

		if ($type == 'primary') {	
			$name = 'PRIMARY';
			$primary = TRUE;
			$unique = TRUE;
		} elseif($type == 'unique') {
			$name = $this->prefix.'uk';
			foreach($cols AS $col) {
				$name .= '_'.$col;
			}
			$primary = FALSE;
			$unique = TRUE;
		} else {
			return '';
		}

		//new key value
		$new_key = array(
			'name' => $name,
			'primary' => $primary,
			'unique' => $unique,
			'cols' => $cols
			);

		//unset old key with same name
		$this->unsetKey($name);

		//set new key
		$this->keys[] = $new_key;
	}

	public function setIndex($cols)
	{
		if (empty($cols)) { return ''; }
		if (!is_array($cols)) { $cols = array($cols); }

		$name = $this->prefix.'idx_'.substr($this->name,strlen($this->prefix));
		foreach($cols AS $col) {
			$name .= '_'.$col;
		}

		$new_index = array(
			'name' => $name,
			'type' => 'BTREE',
			'cols' => $cols
			);

		$this->unsetIndex($name);

		$this->indexes[] = $new_index;

		return $this->indexes;
	}

	public function setReference($c_cols,$p_table,$p_cols,$update='CASCADE',$delete='CASCADE')
	{
		if (empty($c_cols) || empty($p_table) || empty($p_cols)) { return ''; }
		if (!in_array($update,$this->reference_action) || !in_array($delete,$this->reference_action)) { return ''; }
		if (!is_array($c_cols)) { $c_cols = array($c_cols); }
		if (!is_array($p_cols)) { $p_cols = array($p_cols); }

		$name = $this->prefix.'fk_'.substr($this->name,strlen($this->prefix)).'_'.$p_table;

		$new_reference = array(
			'name' => $name,
			'c_cols' => $c_cols,
			'p_table' => $p_table,
			'p_cols' => $p_cols,
			'update' => $update,
			'delete' => $delete
			);

		$this->unsetReference($name);

		$this->references[] = $new_reference;

		return $this->references;
	}

	public function setUnique($cols)
	{
		return $this->setKey('unique',$cols);
	}

	public function setPrimary($cols)
	{
		return $this->setKey('primary',$cols);
	}

	public function setField($name,$type,$len,$null=true,$default=false)
	{
//pb avec null
		if (!in_array($type,$this->field_type)) {
			throw new Exception('Invalid field type '.$type);
		}
		if ($null) { $default = ''; }
		if (empty($default)) { $default = FALSE; } else { $default = '\''.$default.'\''; }
		$len = (integer) $len;
		$null = (boolean) $null;

		//unset old field with same name
		$this->unsetField($name);

		//set new field
		$this->fields[$name] = array(
			'type' => $type,
			'len' => $len,
			'default' => $default,
			'null' => $null
		);
		//print_r($this->fields);exit;

		return $this->fields;
	}
//a refaire
	public function setRow($row)
	{
		$fields = $this->getFields();
		$next_id = $this->getNextId();
		$primary = array_search(key($next_id),$row);

		if (FALSE === $primary) {
			$this->rows[] = array_merge(array($this->con->escape(key($next_id)) => current($next_id)), $row);
		} else {
			$find = array_search($primary,$this->rows);
			if (FALSE !== $find) {
				$this->rows[$find] = array_merge($this->rows[$find],$rows);
			}
		}
	}
#unset
	public function unsetKey($name)
	{
		$keys = $this->getKeys();
		$new_keys = array();
		foreach($keys AS $key) {
			if ($key['name'] != $name) {
				$new_keys[] = $key;
			}
		}
		return $this->keys = $new_keys;
	}

	public function unsetIndex($name)
	{
		$indexes = $this->getIndexes();
		$new_indexes = array();
		foreach($indexes AS $index) {
			if ($index['name'] != $index) {
				$new_indexes[] = $index;
			}
		}
		return $this->indexes = $new_indexes;
	}

	public function unsetReference($name)
	{
		$references = $this->getReferences();
		$new_references = array();
		foreach($references AS $reference) {
			if ($reference['name'] != $reference) {
				$new_references[] = $reference;
			}
		}
		return $this->references = $new_references;
	}

	public function unsetUnique($name)
	{
		return $this->unsetKey($name);
	}

	public function unsetPrimary()
	{
		return $this->unsetKey('PRIMARY');
	}

	public function unsetField($name)
	{
		$fields = $this->getFields();
		return $this->fields = array_diff_key($fields,array($name => ''));
	}
//a refaire
	public function unsetRow($where)
	{
		$primary = $this->getPrimary();
		$find = array_search(array($primary => $primary_id),$this->rows);
		if (FALSE !== $find) {
			unset($this->rows[$find]);
		}
	}
}

?>