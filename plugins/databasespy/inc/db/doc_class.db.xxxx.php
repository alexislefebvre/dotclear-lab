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

/**
* This class is called from class dbSpy => table()
*/
class dbSpyDbXxxx {

	protected $name; #name of current table
	protected $sql_request; #array of sql request done during script execution

	protected $reference_action = array('CASCADE','SET NULL','NO ACTION','RESTRICT'); #reference key ON DELETE and ON UPDATE action allowed
	protected $field_type = array('smallint','integer','bigint','real','float','numeric','date','time','timestamp','char','varchar','text'); #field type allowed

	public function __construct($name,$prefix,$con)
	{
		/*
		*
		*/
	}

	public function getSqlRequest($last=FALSE)
	{
		/*
		*
		*/
	}

	public function getTableStatus()
	{
		/*
		*
		*/
	}

	public function getSize()
	{
		/*
		*
		*/
	}

	public function getRowsNum()
	{
		/*
		*
		*/
	}

	public function getKeys($type='')
	{
		/*
		*
		*/
	}

	public function getIndexes()
	{
		/*
		*
		*/
	}

	public function getReferences()
	{
		/*
		*
		*/
	}

	public function getUniques()
	{
		/*
		*
		*/
	}

	public function getPrimary()
	{
		/*
		*
		*/
	}

	public function getFields()
	{
		/*
		*
		*/
	}

	public function getRows($start=0,$limit=20,$order_col='',$order_way='ASC',$where=array())
	{
		/*
		*
		*/
	}

	public function getNextId()
	{
		/*
		*
		*/
	}
#set
	private function setSqlRequest($str)
	{
		/*
		*
		*/
	}

	public function setTableStatus()
	{
		/*
		*
		*/
	}

	public function setKey($type,$cols)
	{
		/*
		*
		*/
	}

	public function setIndex($cols)
	{
		/*
		*
		*/
	}

	public function setReference($c_cols,$p_table,$p_cols,$update,$delete)
	{
		/*
		*
		*/
	}

	public function setUnique($cols)
	{
		/*
		*
		*/
	}

	public function setPrimary($cols)
	{
		/*
		*
		*/
	}

	public function setField($name,$type,$len,$null=true,$default=false)
	{
		/*
		*
		*/
	}

	public function setRow($row,$where=array())
	{
		/*
		*
		*/
	}
#unset
	public function unsetKey($name)
	{
		/*
		*
		*/
	}

	public function unsetIndex($name)
	{
		/*
		*
		*/
	}

	public function unsetReference($name)
	{
		/*
		*
		*/
	}

	public function unsetUnique($name)
	{
		/*
		*
		*/
	}

	public function unsetPrimary($name)
	{
		/*
		*
		*/
	}

	public function unsetField($name)
	{
		/*
		*
		*/
	}

	public function unsetRow($where)
	{
		/*
		*
		*/
	}

# alter
	public function alterKey($name,$cols)
	{
		/*
		*
		*/
	}

	public function alterIndex($name,$cols)
	{
		/*
		*
		*/
	}

	public function alterUnique($name,$cols)
	{
		/*
		*
		*/
	}

	public function alterPrimary($name,$cols)
	{
		/*
		*
		*/
	}

	public function alterField($name,$new_name,$type,$len,$null,$default)
	{
		/*
		*
		*/
	}
	
	public function alterReference($name,$c_cols,$p_table,$p_cols,$update,$delete)
	{
		/*
		*
		*/
	}
}


?>