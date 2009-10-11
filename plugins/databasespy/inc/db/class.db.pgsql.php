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

class dbSpyDbPgsql {

	protected $name; #name of current table

	protected $reference_action = array('CASCADE','SET NULL','NO ACTION','RESTRICT'); #reference key ON DELETE and ON UPDATE action allowed
	protected $field_type = array('smallint','integer','bigint','real','float','numeric','date','time','timestamp','char','varchar','text'); #field type allowed

	public function __construct($name,$prefix,$con)
	{
		$this->name = $name;
		$this->con = $con;
		$this->prefix = $prefix;
		$this->schema = dbSchema::init($con);
		$tables = $this->schema->getTables();
		if (!in_array($name,$tables)) {
			throw new Exception('Invalid table name');
		}
	}

	public function getSize()
	{
		//inspired from plugin "Informations" by Moe (http://gniark.net/)
		return $this->con->select('SELECT relname, relpages * 8192 AS length FROM pg_class WHERE relname LIKE \''.$this->name.'\';')->f('length');
	}

	public function getRowsNum()
	{
		return $this->con->select("SELECT count(*) FROM ".$this->name)->f(0);
	}

	public function getKeys()
	{
		return $this->schema->getKeys($this->name);
	}

	public function getIndexes()
	{
		return $this->schema->getIndexes($this->name);
	}

	public function getReferences()
	{
		return $this->schema->getReferences($this->name);
	}

	public function getUniques()
	{
		$keys = $this->schema->getKeys($this->name);
		foreach($keys AS $key) {
			if ($key['unique']) {
				$uniques[] = $key;
			}
		}
		return $uniques;
	}

	public function getPrimary()
	{
		$keys = $this->schema->getKeys($this->name);
		foreach($keys AS $key) {
			if ($key['primary']) {
				$primary = $key['cols'];
			}
		}
		return $primary;
	}

	public function getFields()
	{
		return $this->schema->getColumns($this->name);
	}

	public function getRows($start=0,$limit=20,$order_col='',$order_way='ASC',$where=array())
	{
		$strReq = 'SELECT * FROM '.$this->con->escape($this->name).' ';
		foreach($where AS $k => $v) {
			//option $where = array(field => value,...) and only work on AND mode
			$strReq .= (!isset($first))?'WHERE ':'AND '; $first = 1;
			$strReq .= ((null) == $v)?$this->con->escape($k)." IS NULL ":$this->con->escape($k)." = '".$this->con->escape($v)."' ";
		}
		$strReq .= ' '.((!empty($order_col))?" ORDER BY ".$order_col." ".$order_way:'');
		$strReq .= ' LIMIT '.$start.','.$limit;

		return $this->con->select($strReq)->rows();
	}

	public function getNextId()
	{
		$primary = $this->getPrimary();

		if (!empty($primary)) {
			$key = $ord = '';
			foreach($primary AS $p) {
				$key .= $this->con->escapeSystem($p).", ";
				$ord .= $this->con->escapeSystem($p)." DESC, ";
			}
			$res = $this->con->select("SELECT ".substr($key,0,-2)." FROM ".$this->name." ORDER BY ".substr($ord,0,-2)." LIMIT 1")->rows();

			foreach($primary AS $p) {
				$next_id[$p] = (is_numeric($res[0][$p]))?$res[0][$p] + 1:$res[0][$p];
			}

			return $next_id;
		}
		return '';
	}
#set
	public function setKey($type,$cols)
	{
		if (empty($cols)) { return ''; }
		if (!is_array($cols)) { $cols = array($cols); }

		if ($type == 'PRIMARY') {
			$this->schema->createPrimary($this->name,'PRIMARY',$cols);
		} else {
			$name = $this->prefix.'uk';
			foreach($cols AS $col) {
				$name .= '_'.$col;
			}
			$this->schema->createUnique($this->name,$name,$cols);
		}
	}

	public function setIndex($cols)
	{		
		if (empty($cols)) { return ''; }
		if (!is_array($cols)) { $cols = array($cols); }

		$name = $this->prefix.'idx_'.substr($this->name,strlen($this->prefix));
		foreach($cols AS $col) {
			$name .= '_'.$col;
		}
		$this->schema->createIndex($this->name,$name,'BTREE',$cols);
	}

	public function setReference($c_cols,$p_table,$p_cols,$update,$delete)
	{
		if (empty($c_cols) || empty($p_table) || empty($p_cols)) { return ''; }
		if (!in_array($update,$this->reference_action) || !in_array($delete,$this->reference_action)) { return ''; }
		if (!is_array($c_cols)) { $c_cols = array($c_cols); }
		if (!is_array($p_cols)) { $p_cols = array($p_cols); }

		$name = $this->prefix.'fk_'.substr($this->name,strlen($this->prefix)).'_'.$p_table;

		$this->schema->createReference($name,$this->name,$c_cols,$p_table,$p_cols,$update,$delete);
	}

	public function setUnique($cols)
	{
		$this->setKey('unique',$cols);
	}

	public function setPrimary($cols)
	{
		$this->setKey('PRIMARY',$cols);
	}

	public function setField($name,$type,$len,$null=true,$default=false)
	{
		if (!in_array($type,$this->field_type)) {
			throw new Exception('Invalid field type '.$type);
		}
		if (empty($default)) { $default = FALSE; }
		$len = (integer) $len;
		$null = (boolean) $null;

		$this->schema->createField($this->name,$name,$type,$len,$null,$default);
	}

	public function setRow($row)
	{
		$fields = $this->getFields();
		$next_id = $this->getNextId();
		$primary = array_intersect_key($next_id,$row);

		//new row
		if (!empty($primary)) {

			$new_key = $new_val = '';
			foreach($next_id AS $k => $v) {
				$new_key .= $this->con->escape($k).", ";
				$new_val .= "'".$v."', ";
			}
			foreach($row AS $key => $val) {
				$new_key .= $this->con->escape($key).", ";
				$new_val .= (TRUE === $fields[$key]['null'] && empty($val))?'NULL, ':"'".$this->con->escape($val)."', ";
			}
			$this->con->execute("INSERT INTO ".$this->name." (".substr($new_key,0,-2).") VALUES (".substr($new_val,0,-2).")");
		//update row
		} else {
			$new = $where = '';
			foreach($row AS $key => $val) {
				if (array_key_exists($key,$next_id)) {
					$where .= ((!empty($where))?'AND ':'').$this->con->escape($key)." = '".$val."' ";
				} else {
					$new .= "`".$this->con->escape($key)."` = ". ((TRUE === $fields[$key]['null'] && empty($val))?"NULL, ":"'".$this->con->escape($val)."', ");
				}
			}
			if (!empty($new) && !empty($where)) {
				$this->con->execute("UPDATE ".$this->name." SET ".substr($new,0,-2)." WHERE ".$where);
			}
		}
	}
#unset
	public function unsetKey($name)
	{
		$this->con->execute("ALTER TABLE ".$this->con->escape($this->name)." DROP CONSTRAINT ".$this->con->escapeSystem($name));
	}

	public function unsetIndex($name)
	{
		$this->con->execute("DROP INDEX ".$this->con->escapeSystem($name));
	}

	public function unsetReference($name)
	{
		$this->con->execute("ALTER TABLE ".$this->con->escape($this->table)." DROP CONSTRAINT ".$this->con->escapeSystem($name));
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
		$this->con->execute("ALTER TABLE ".$this->con->escape($this->table)." DROP COLUMN ".$this->con->escapeSystem($name));
	}

	public function unsetRow($where)
	{
		$str_where = '';
		foreach($where AS $key => $val) {
			$str_where .= ((!empty($where))?'AND ':'').$this->con->escape($key)." = '".$val."' ";
		}
		if (!empty($where)) {
			$this->con->execute("DELETE FROM ".$this->name." WHERE ".$str_where);
		}
	}
}


?>