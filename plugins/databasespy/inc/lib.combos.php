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

$combo_reference_action = array(
	'CASCADE',
	'SET NULL',
	'NO ACTION',
	'RESTRICT'
);
$combo_field_type = array(
	'smallint',
	'integer',
	'bigint',
	'real',
	'float',
	'numeric',
	'date',
	'time',
	'timestamp',
	'char',
	'varchar',
	'text'
);
$combo_export_type = array(
	__('the content') 			=> 'content',
	__('the structure') 		=> 'structure',
	__('structure and content') => 'all'
	);
$combo_export_format = array(
	__('SQL') 	=> 'sql',
	__('XML') 	=> 'xml',
	__('HTML') 	=> 'html',
	__('PDF') 	=> 'pdf',
	__('CSV') 	=> 'csv'
	);
$combo_export_name = array(
	'yyyy-mm-dd' 					=> '%Y%-%m%-%d%',
	'yyyy-mm-dd-table_name' 		=> '%Y%-%m%-%d%-%tb_name%',
	'yyyy-mm-dd-type-table_name' 	=> '%Y%-%m%-%d%-%type%-%tb_name%',
	'type-table_name' 				=> '%type%-%tb_name%',
	'table_name' 					=> '%tb_name%'
	);
$combo_export_action = array(
	__('View') => 'view',
	__('Save') => 'save'
	);
$combo_settings = array(
	'delete_table' 	=> __('Allowed delete table'),
	'show_request' 	=> __('database request'),
	'picture' 		=> __('Picture instead text form'),
	'colorize' 		=> __('Colored fields'),
	'chunk' 		=> __('Chunked long text field'),
	'chunk_len' 	=> __('Chars for chunk fields'),
	'nb_per_page'	=> __('Rows per page'),
	'export_type' 	=> __('Export file type'),
	'export_format' => __('Export file format'),
	'export_name' 	=> __('Export file name')
	);
$combo_settings_install = array(
	'delete_table' 	=> FALSE,
	'show_request' 	=> FALSE,
	'picture' 		=> TRUE,
	'colorize' 		=> TRUE,
	'chunk' 		=> TRUE,
	'chunk_len' 	=> 80,
	'nb_per_page'	=> 20,
	'export_type' 	=> 'all',
	'export_format' => 'SQL',
	'export_name' 	=> '%Y%-%m%-%d%-%type%-%tb_name%'
	);
$combo_settings_type = array(
	'delete_table' 	=> array(__('Without') => FALSE,__('With') => TRUE),
	'show_request' 	=> array(__('Hide') => FALSE,__('Show') => TRUE),
	'picture' 		=> array(__('Without') => FALSE,__('With') => TRUE),
	'colorize' 		=> array(__('Without') => FALSE,__('With') => TRUE),
	'chunk' 		=> array(__('Without') => FALSE,__('With') => TRUE),
	'chunk_len'		=> array('40' => 40,'60' => 60,'80' => 80,'100' => 100,'150' => 150,'200' => 200),
	'nb_per_page'	=> array('10' => 10,'20' => 20,'30' => 30,'40' => 40,'50' => 50,'100' => 100),
	'export_type' 	=> $combo_export_type,
	'export_format' => $combo_export_format,
	'export_name' 	=> $combo_export_name
	);
$combo_settings_break_title = array(
	'delete_table'	=> __('Auth settings'),
	'picture' 		=> __('Table style'),
	'export_type' 	=> __('Export table'),
	'import_type' 	=> __('Import table')
	);
$combo_menu = array(
	__('Settings') 		=> 'settings',
	__('Summary') 		=> 'summary',
	__('Content') 		=> 'content',
	__('Structure') 	=> 'structure',
	__('Insert row') 	=> 'insert',
	__('Create table') 	=> 'create',
	__('Export table') 	=> 'export'
	);
$combo_type = array(
	'smallint' 		=> 'smallint',
	'integer' 		=> 'integer',
	'bigint' 		=> 'bigint',
	'real' 			=> 'real',
	'float' 		=> 'float',
	'numeric' 		=> 'numeric',
	'date' 			=> 'date',
	'time' 			=> 'time',
	'timestamp' 	=> 'timestamp',
	'char' 			=> 'char',
	'varchar' 		=> 'varchar',
	'text' 			=> 'text'
	);
$combo_null = array(
	'Not null' 		=> 'NOT NULL',
	'Null' 			=> 'NULL'
	);
$combo_table_row_action = array(
	__('View content') 			=> 'content',
	__('View structure') 		=> 'structure',
	__('Add field') 		=> 'field',
	__('Add foreign key') 	=> 'reference',
	__('Insert row') 			=> 'insert',
	__('Empty') 			=> 'empty',
	__('Delete') 			=> 'delete',
	__('Export') 			=> 'export'
	);
$combo_table_col_action = array(
	__('Empty') 	=> 'empty',
	__('Delete') 	=> 'delete'
	);
$combo_struct_row_action = array(
	__('Edit') 		=> 'edit',
	__('Primary') 	=> 'primary',
	__('Unique') 	=> 'unique',
	__('Index') 	=> 'index',
	__('Delete') 	=> 'delete'
	);
$combo_struct_col_action = array(
	__('Primary') 	=> 'primary',
	__('Unique') 	=> 'unique',
	__('Index') 	=> 'index',
	__('Delete') 	=> 'delete'
	);
$combo_key_row_action = array(
	__('Delete') 	=> 'del_key'
	);
$combo_key_col_action = array(
	__('Delete') 	=> 'del_key'
	);
$combo_index_row_action = array(
	__('Delete') 	=> 'del_index'
	);
$combo_index_col_action = array(
	__('Delete') 	=> 'del_index'
	);
$combo_reference_row_action = array(
	__('Delete') 	=> 'del_reference'
	);
$combo_reference_col_action = array(
	__('Delete') 	=> 'del_reference'
	);
$combo_content_row_action = array(
	__('Edit') 		=> 'edit',
	__('Delete') 	=> 'delete'
	);
$combo_content_col_action = array(
	__('Edit') 		=> 'edit',
	__('Delete') 	=> 'delete'
	);
?>