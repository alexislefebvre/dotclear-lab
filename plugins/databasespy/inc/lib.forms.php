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

if ('form' == $_m)
{
	# Settings
	if ($_a == 'settings')
	{
		# Update settings
		if (isset($_POST['settings']))
		{
			try
			{
				$spy->setSettings($_POST['settings']);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		if (!$core->error->flag())
		{
			$msg = __('Configuration successfully updated');
		}
		$_m = 'settings';
		$_t = '';
	}
	# Export
	if ($_a == 'export')
	{
		if (isset($_POST['save']) && isset($_POST['table']) && isset($_POST['type']) && isset($_POST['format']))
		{
			# View Export table
			if ('view' == $_POST['save'])
			{
				try
				{
					$export_file = $spy->exportTable($_POST['table'],$_POST['type'],$_POST['format']);
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
				if ($core->error->flag())
				{
					unset($export_file);
					$_m = 'export';
					$_t = '';
				}
				else
				{
					$_m = 'export';
					$_t = $_POST['table'];
				}
			}
			# Save Export table
			elseif ('save' == $_POST['save'])
			{
				try
				{
					$export_url = $spy->exportTable($_POST['table'],$_POST['type'],$_POST['format'],TRUE);
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
				if (!$core->error->flag())
				{
					header('Location: '.$p_url.'&amp;m=export');
					//$msg = __('Export successfully done');
				}
				$_m = 'export';
				$_t = '';
			}
			else
			{
				$_m = 'export';
				$_t = $_POST['table'];
			}
		}
	}
	# Tables summary
	if ($_a == 'table')
	{
		# View content
		if (isset($_POST['row']['export']))
		{
			$_m = 'export';
			$_t = key($_POST['row']['export']);
		}
		# View content
		elseif (isset($_POST['row']['content']))
		{
			$_m = 'content';
			$_t = key($_POST['row']['content']);
		}
		# View structure
		elseif (isset($_POST['row']['structure']))
		{
			$_m = 'structure';
			$_t = key($_POST['row']['structure']);
		}
		# add field to structure
		elseif (isset($_POST['row']['field']))
		{
			$_m = 'field';
			$_t = key($_POST['row']['field']);
		}
		# add foreign key to structure
		elseif (isset($_POST['row']['reference']))
		{
			$_m = 'reference';
			$_t = key($_POST['row']['reference']);
		}
		# New row
		elseif (isset($_POST['row']['insert']))
		{
			$_m = 'insert';
			$_t = key($_POST['row']['insert']);
		}
		# Disable delete table
		elseif (!$settings['delete_table'] && (isset($_POST['row']['delete']) || isset($_POST['col']['delete'])))
		{
			$msg = '"Delete table" forbbiden';
			$_POST['table'] = '';
			$_m = 'summary';
			$_t = '';
		}
		# Delete table 
		elseif ($settings['delete_table'] && isset($_POST['row']['delete']))
		{
			try
			{
				$spy->deleteTable(key($_POST['row']['delete']));
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
			if (!$core->error->flag())
			{
				if (isset($_POST['redir']))
				{
					http::redirect($_POST['redir']);
				}
				else
				{
					//must reload page when delete table
					http::redirect($p_url.'&amp;m=summary');
				}
			}
			$_m = 'summary';
			$_t = '';
		}
		# Delete tables
		elseif ($settings['delete_table'] && isset($_POST['entries']) && isset($_POST['key']) && isset($_POST['col']['delete']))
		{
			$entries = array_intersect_key($_POST['entries'],$_POST['key']);
			foreach($entries AS $k => $v)
			{
				try
				{
					$spy->deleteTable($v);
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
			}
			if (!$core->error->flag())
			{
				//must reload page when delete table
				http::redirect($p_url.'&amp;m=summary');
			}
			$_m = 'summary';
			$_t = '';
		}
		# Empty table
		elseif (isset($_POST['row']['empty']))
		{
			try
			{
				$spy->emptyTable(key($_POST['row']['empty']));
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
			if (!$core->error->flag() && isset($_POST['redir']))
			{
					http::redirect($_POST['redir']);
			}
			$_m = 'summary';
			$_t = '';//$_POST['row']['empty'];
		}
		# Empty tables
		elseif (isset($_POST['entries']) && isset($_POST['key']) && isset($_POST['col']['empty']))
		{
			$entries = array_intersect_key($_POST['entries'],$_POST['key']);
			foreach($entries AS $k => $v)
			{
				try
				{
					$spy->emptyTable($v);
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
				$_m = 'summary';
				$_t = '';
			}
		}
		else
		{
			$_m = 'summary';
		}
	}

	# Table structure
	if ($_a == 'structure')
	{
		# Create one key on one col
		if (isset($_POST['table']) && (isset($_POST['row']['primary']) || isset($_POST['row']['unique']) || isset($_POST['row']['index'])))
		{
			try
			{
				if ($_POST['row']['primary'])
				{
					$_T_->setPrimary(key($_POST['row']['primary']));
				}
				elseif ($_POST['row']['unique'])
				{
					$_T_->setUnique(key($_POST['row']['unique']));
				}
				elseif ($_POST['row']['index'])
				{
					$_T_->setIndex(key($_POST['row']['index']));
				}
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
			$_m = 'structure';
			$_t = $_POST['table'];
		}
		# Create one key on multiple cols
		elseif (isset($_POST['table']) && isset($_POST['entries']) && isset($_POST['key']) && (isset($_POST['col']['primary']) || isset($_POST['col']['unique']) || isset($_POST['col']['index'])))
		{
			$entries = array_intersect_key($_POST['entries'],$_POST['key']);
			try
			{
				if ($_POST['col']['primary'])
				{
					$_T_->setPrimary($entries);
				}
				elseif ($_POST['col']['unique'])
				{
					$_T_->setUnique($entries);
				}
				elseif ($_POST['col']['index'])
				{
					$_T_->setIndex($entries);
				}
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
			$_m = 'structure';
			$_t = $_POST['table'];
		}
		# Delete one key
		elseif (isset($_POST['table']) && isset($_POST['row']['del_key']))
		{
			try
			{
				$_T_->unsetKey(key($_POST['row']['del_key']));
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
			$_m = 'structure';
			$_t = $_POST['table'];
		}
		# Delete multiple keys
		elseif (isset($_POST['table']) && isset($_POST['key']) && isset($_POST['col']['del_key']))
		{
			foreach($_POST['key'] AS $key)
			{
				try
				{
					$_T_->unsetKey($key);
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
			}
			$_m = 'structure';
			$_t = $_POST['table'];
		}
		# Delete one index
		elseif (isset($_POST['table']) && isset($_POST['row']['del_index']))
		{
			try
			{
				$_T_->unsetIndex(key($_POST['row']['del_index']));
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
			$_m = 'structure';
			$_t = $_POST['table'];
		}
		# Delete multiple indexes
		elseif (isset($_POST['table']) && isset($_POST['key']) && isset($_POST['col']['del_index']))
		{
			foreach($_POST['key'] AS $key)
			{
				try
				{
					$_T_->unsetIndex($key);
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
			}
			$_m = 'structure';
			$_t = $_POST['table'];
		}
		# Delete one reference
		elseif (isset($_POST['table']) && isset($_POST['row']['del_reference']))
		{
			try
			{
				$_T_->unsetReference(key($_POST['row']['del_reference']));
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
			$_m = 'structure';
			$_t = $_POST['table'];
		}
		# Delete multiple references
		elseif (isset($_POST['table']) && isset($_POST['key']) && isset($_POST['col']['del_reference']))
		{
			foreach($_POST['key'] AS $key)
			{
				try
				{
					$_T_->unsetReference($key);
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
			}
			$_m = 'structure';
			$_t = $_POST['table'];
		}
		# Redirect
		else
		{
			$_m = 'structure';
			$_t = (isset($_POST['table']))?$_POST['table']:'';
		}
	}
	# Table content
	if ($_a == 'content')
	{
		# Edit multiple rows
		if (isset($_POST['table']) && isset($_POST['entries']) && isset($_POST['key']) && isset($_POST['col']['edit']))
		{
			$entries = array_intersect_key($_POST['entries'],$_POST['key']);
			$_m = 'content';
			$_t = $_POST['table'];
		}
		# Edit one row
		elseif (isset($_POST['table']) && isset($_POST['entries']) && isset($_POST['row']['edit']))
		{
			$entries = array(0 => $_POST['entries'][key($_POST['row']['edit'])]);
			$_m = 'content';
			$_t = $_POST['table'];
		}
		# Save edited row
		elseif (isset($_POST['table']) && isset($_POST['primary']) && isset($_POST['key']) && isset($_POST['save']))
		{
			foreach($_POST['key'] AS $k => $v)
			{
				try
				{
					$_T_->setRow($_POST['key'][$k],$_POST['primary'][$k]);
				}
				catch (Exception $e) {
					$core->error->add($e->getMessage());
				}
			}
			$_m = 'content';
			$_t = $_POST['table'];
		# Delete multiple row
		}
		elseif (isset($_POST['table']) && isset($_POST['entries']) && isset($_POST['key']) && isset($_POST['col']['delete']))
		{
			$rows = array_intersect_key($_POST['entries'],$_POST['key']);
			foreach($rows AS $row)
			{
				try
				{
					$_T_->unsetRow($row);
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
			}
			$_m = 'content';
			$_t = $_POST['table'];
		# Delete one row
		} elseif (isset($_POST['table']) && isset($_POST['entries']) && isset($_POST['row']['delete'])) {
			$row = $_POST['entries'][key($_POST['row']['delete'])];
			try
			{
				$_T_->unsetRow($row);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
			$_m = 'content';
			$_t = $_POST['table'];
		}
		# Redirect
		else
		{
			$_m = 'content';
			$_t = (isset($_POST['table']))?$_POST['table']:'';
		}
	}
	# New row
	if ($_a == 'insert')
	{
		if (isset($_POST['table']) && isset($_POST['save']) && isset($_POST['key']))
		{
			try
			{
				$_T_->setRow($_POST['key']);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
			if (!$core->error->flag())
			{
				$_m = 'content';
				$_t = $_POST['table'];
			}
			else
			{
				$_m = 'insert';
				$_t = $_POST['table'];
				$key = $_POST['key'];
			}
		}
		else
		{
			$_m = 'insert';
			$_t = (isset($_POST['table']))?$_POST['table']:'';
		}
	}
	# Table create
	if ($_a == 'create')
	{
		if (isset($_POST['table']) && !empty($_POST['table']))
		{
			$_t = str_replace($core->prefix,'',$_POST['table']);
			$create_num_fields = ($_POST['num_fields'] < 100 && $_POST['num_fields'] > 0)?$_POST['num_fields']:2;
		}
		else
		{
			$_m = 'create';
			$_t = '';
		}

		if (isset($_t) && !empty($_t) && FALSE === $spy->isTable($core->prefix.$_t) && isset($_POST['fields']) && isset($_POST['num_fields']))
		{
			foreach($_POST['fields'] AS $field)
			{
				try
				{
					$_T_->setField($field['field'],$field['type'],$field['len'],$field['null'],$field['default']);
				}
				catch (Exception $e)
				{
					$core->error->add($e->getMessage());
				}
			}
			try
			{
				$spy->v2eTableStructure($_T_);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}

			if (!$core->error->flag())
			{
				http::redirect($p_url.'&amp;m=structure&amp;t='.$core->prefix.$_t);
			}
			else
			{
				$_m = 'create';
			}
		}
		else
		{
			$_m = 'create';
		}
	}
}
?>