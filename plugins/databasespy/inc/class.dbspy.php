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

if (!defined('DC_CONTEXT_ADMIN')){return;}

class dbSpy
{
	public $core;

	protected $con; #connexion object
	protected $prefix; #table prefix
	private $default_settings_list = array();
	protected $settings; #dbSpy setting (from core->blog->setting)
	protected $tables = array(); #tables name list
	protected $table = array(); #array of objects table
	protected $cache_path = ''; # Dotclear style path of dbSpy cache folder

	public function __construct($core,$default_settings_list)
	{
		$this->default_settings_list = $default_settings_list;
		$this->core = $core;
		$this->con = $core->con;
		$this->prefix = $core->prefix;
		$this->schema = dbSchema::init($this->con);
		$this->tables = $this->schema->getTables();
		$this->core->blog->settings->setNamespace('databasespy');

		$this->cache_path = $this->core->plugins->moduleInfo('databasespy','root').'/cache/';
	}

	# Get dbSpy settings (from $core->blog->settings)
	public function getSettings($setting='')
	{
		if (empty($this->settings))
		{
			if (empty($this->default_settings_list))
			{
				throw new Exception('Unable to load settings list.');
				exit(1);
			}
			$this->core->blog->settings->setNamespace('databasespy');

			foreach($this->default_settings_list AS $key => $val)
			{
				$this->settings[$key] = $this->core->blog->settings->get('databasespy_'.$key);
				if (null === $this->settings[$key])
				{
					$this->settings[$key] = $this->default_settings_list[$key];
				}
			}
		}

		if (!empty($setting))
		{
			return isset($this->settings[$setting]) ? $this->settings[$setting] : array();
		}
		else
		{
			return $this->settings;
		}
	}

	# Set dbSpy settings (in $core->blog->settings)
	public function setSettings($settings)
	{
		$old_settings = $this->getSettings();
		$this->core->blog->settings->setNamespace('databasespy');
		foreach($old_settings AS $key => $val)
		{
			if (isset($settings[$key]))
			{
				$this->settings[$key] = $settings[$key];
				$this->core->blog->settings->put('databasespy_'.$key,$this->settings[$key],'string','',true,true);
			}
		}
		return $this->settings;
	}
	# Empty dbSpy cache folder
	private function cleanCache()
	{
		$dir = $this->cache_path;

	    if(!$dir_handle = @opendir($dir)) return;

	    while (false !== ($obj = readdir($dir_handle)))
		{
			if($obj == '.' || $obj == '..') continue;
	        @unlink($dir.'/'.$obj);
	    }
        closedir($dir_handle);
	}
	# Get some info from database
	public function getDatabaseInfo()
	{
		return array(
			'version' 	=> $this->con->version(),
			'driver' 	=> $this->con->driver(),
			'database' 	=> $this->con->database(),
			'link' 		=> $this->con->link(),
			'tables' 	=> $this->tables
		);
	}

	# Get size and nb rows from tables
	public function getTables()
	{
		foreach($this->tables AS $table)
		{
			$res[$table] = $this->table($table)->getTableStatus();
			//$res[$table]['rows'] = $this->table($table)->getRowsNum();
			//$res[$table]['size'] = $this->table($table)->getSize();
		}
		return $res;
	}

	# Say if table exists on database
	public function isTable($table)
	{
		return in_array($table,$this->tables);
	}

	# Delete a table (if empty and as now foreign key)
	public function deleteTable($table)
	{
		if (!$this->isTable($table))
		{
			throw new Exception('Unknow table "'.$table.'".');
			exit(1);
		}
		$count_row = $this->con->select("SELECT count(*) FROM ".$table)->f(0);
		if ($count_row > 0)
		{
			throw new Exception('Table "'.$table.'" not empty.');
			exit(1);
		}
		$this->con->execute("DROP TABLE ".$table);
	}

	# Vacuum a table
	public function emptyTable($table)
	{
		if (!$this->isTable($table))
		{
			throw new Exception('Unknow table "'.$table.'".');
			exit(1);
		}
		$this->con->execute('DELETE FROM '.$table);
	}

	# Import a table structure or content from a file
	public function importTable()
	{
	
	}

	# Export a table structure or content to a file
	public function exportTable($name,$type='content',$format='',$save=FALSE)
	{
		if (!$this->isTable($name))
		{
			throw new Exception('Unknow table "'.$name.'".');
			exit(1);
		}
		if (!file_exists(dirname(__FILE__).'/export/class.export.'.strtolower($format).'.php'))
		{
			throw new Exception('Unsupported export format:'.$format);
			exit(1);
		}
		try
		{
			eval('$export = new dbSpyExport'.strtoupper($format).'($this->con->database(),$name,$type,$this->table($name));');
		}
		catch (Exception $e)
		{
			throw new Exception(__('Export').' '.$format.': '.$e->getMessage());
			exit(1);
		}
		if ($save)
		{
			self::cleanCache(); //auto clean cache folder
			$file_name = str_replace(
				array('%Y%','%m%','%d%','%type%','%tb_name%'),
				array(date(Y),date(m),date(d),$type,$name),
				$this->settings['export_name']
			);
			$export->save($this->cache_path,$file_name);
			return TRUE;
		}
		else
		{
			return $export->get();
		}
	}

	# Create an object to work on a table
	public function table($name)
	{
		$driver = in_array($name,$this->tables) ? $this->con->driver() : 'virtual';
		try
		{
			eval('$this->table[$name] = new dbSpyDb'.ucfirst($driver).'($name,$this->prefix,$this->con);');
		}
		catch (Exception $e)
		{
			throw new Exception('Unsupported database driver. Error:'.$e->getMessage());
			exit(1);
		}
		return $this->table[$name];
	}

	# Copy an existing table structure to a virtual table
	public function e2vTableStructure($name)
	{
		$e_t = $this->table($name);
		$v_t = $this->table($this->prefix.'virtual_'.$name);

		$keys = $e_t->getKeys();
		foreach($keys AS $key)
		{
			if ($key['name'] == 'PRIMARY')
			{
				$v_t->setPrimary($key['cols']);
			}
			else
			{
				$v_t->setUnique($key['cols']);
			}
		}
		$keys = $e_t->getIndexes();
		foreach($keys AS $key)
		{
			$v_t->setIndex($key['cols']);
		}
		$keys = $e_t->getReferences();
		foreach($keys AS $key)
		{
			$v_t->setReference($key['c_cols'],$key['p_table'],$key['p_cols'],$key['update'],$key['delete']);
		}
		$keys = $e_t->getFields();
		foreach($keys AS $key => $val)
		{
			$v_t->setField($key,$val['type'],$val['len'],$val['null'],$val['default']);
		}
		return $v_t;
	}

	# Create a table and its structure from virtual table
	public function v2eTableStructure($v_t)
	{
		$name = $v_t->getTableName();
		//clean name if from e2vTableStructure()
		$name = $this->prefix.str_replace($this->prefix.'virtual_','',$name);

		if ($this->isTable($name))
		{
			throw new Exception('Existing table "'.$table.'".');
			exit(1);
		}

		$fields = $v_t->getFields();
		if (!empty($fields))
		{
			$this->schema->createTable($name,$fields);

			$keys = $v_t->getKeys();
			foreach($keys AS $key)
			{
				if ($key['name'] == 'PRIMARY')
				{
					$this->schema->createPrimary($name,'PRIMARY',$key['cols']);
				}
				else
				{
					$this->schema->createUnique($name,$key['name'],$key['cols']);
				}
			}
			$keys = $v_t->getIndexes();
			foreach($keys AS $key)
			{
				$this->schema->createIndex($name,$key['name'],$key['cols']);
			}
			$keys = $v_t->getReferences();
			foreach($keys AS $key)
			{
				$this->schema->createReference($key['name'],$name,$key['c_cols'],$key['p_table'],$key['p_cols'],$key['update'],$key['delete']);
			}
			return $this->table($name);
		}
	}
}

?>