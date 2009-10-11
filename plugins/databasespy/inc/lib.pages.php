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

switch ($_m)
{
	/** Summary **/

	# Tables list
	case 'summary':
	$sum = $spy->getDatabaseInfo();
	$tables = $spy->getTables();
	$total_rows = $total_size = 0;
	foreach($tables AS $key => $table)
	{
		$total_rows += $table['rows'];
		$total_size += $table['size'];
		$tables[$key]['size'] = files::size($table['size']);
	}
	$P->table(__('Summary'),'','',$tables,array(),array('action' => 'table'),$combo_table_row_action,$combo_table_col_action,FALSE);
	$P->content(__('Summary'),'
		<div class="about">'.
		str_replace(
			array('%server%', '%version%', '%database%', '%prefix%'),
			array($sum['driver'], $sum['version'], $sum['database'], $core->prefix), 
			__("This DotClear database '%database%' is install on %server% server version %version% and it use prefix '%prefix%'.")
		).
		'<br />'.
		str_replace(
			array('%tables%', '%rows%', '%size%'),
			array(count($tables), $total_rows, files::size($total_size)),
			__('There are %tables% tables with %rows% rows for a size of %size%')
		).
		'</div>'
	);
	break;

	/** Content **/

	# Table content
	case 'content':
	if (empty($_t))
	{
		$P->info(__('Content'),__('Choose table to view:'));
		$tables = $spy->getTables();
		foreach($tables AS $key => $val)
		{
			$P->liste(__('Content'),$key,$p_url.'&amp;m=content&amp;t='.$key);
		}
		$P->content(__('Content'));
		break;
	}
	# show content
	if (!isset($entries))
	{
		$P->head(__('Content'),'<h3>'.__('Table:').' '.$_t.'</h3>');

		# Menu
		$menu = array_diff_key($combo_table_row_action, array(__('Content') => ''));
		$P->head(__('Content'),
			'<form method="post" action="'.$p_url.'&amp;m=form">'.
			$P->getInput($_t,'row',$menu,TRUE).
			'<p>'.
			$P->hiddens(array('table'=>$_t,'action'=>'table','redir'=>$p_url.'amp;m=contentamp;t='.$_t),true).
			'</p>'.
			'</form>'
		);

		# Test if table is empty
		$yena = $_T_->getRowsNum();
		if ($yena == 0)
		{
			$P->info(__('Content'),__('This table is empty'));
		}
		else
		{
			# Retrieve items from $_GET
			$start = (!empty($_GET['st']) && $_GET['st'] > 0)?$_GET['st']:1;
			$order_col = (!empty($_GET['oc']))?$_GET['oc']:'';
			$order_way = (!empty($_GET['ow']))?$_GET['ow']:'ASC';

			# Retrieve info from table
			$primary = $_T_->getPrimary();
			$structures = $_T_->getFields();
			$contents = $_T_->getRows((($start - 1) * $settings['nb_per_page']),$settings['nb_per_page'],$order_col,$order_way);

			# Set warning if no primary key
			if (empty($primary))
			{
				$P->info(__('Content'),__("There is no primary key on this table, DatabaseSpy can't perform some actions on it."));
			}

			# Construct navigation
			$P->pager(__('Content'),$p_url.'&amp;m=content&amp;t='.$_t.'&amp;st='.$start.'&amp;oc='.$order_col.'&amp;ow='.$order_way,'st',$start,$yena);

			# Construct table
			$form_hidden = array(
				'table' => $_t,
				'action' => 'content',
				'start' => $start,
				'order_col' => $order_col,
				'order_way' => $order_way,
				'redir' => $p_url.'&amp;m=content&amp;t='.$_t.'&amp;st='.$start.'&amp;oc='.$order_col.'&amp;ow='.$order_way
			);
			foreach($structures AS $k =>$v)
			{
				$field[$k] = '<a href="'.$p_url.'&amp;m=content&amp;t='.$_t.'&amp;st='.$start.'&amp;oc='.$k.'&amp;ow='.(($order_col == $k && $order_way == 'ASC')?'DESC':'ASC').'">'.$k.'</a>';
			}
			foreach($contents AS $name => $content)
			{
				foreach($content AS $key => $value)
				{
					// Erase numeric key
					if (is_numeric($key)) continue;
					$clean_contents[$name][$key] = htmlspecialchars($value);
				}
			}
			$P->table(__('Content'),'',$field,$clean_contents,$primary,$form_hidden,$combo_content_row_action,$combo_content_col_action,TRUE);
		}
		$P->content(__('Content'));
	}
	# Edit content
	elseif (!empty($entries))
	{
		$P->head(__('Edit'),'<h3>'.__('Table:').' '.$_t.'</h3>');
		$P->content(__('Edit'),'<form method="post" action="'.$p_url.'&amp;m=form">');

		$k = 0;
		$primary = $_T_->getPrimary();
		$structures = $_T_->getFields();
		foreach($entries AS $entrie)
		{
			$P->content(__('Edit'),
				'<table>'.
				'<thead><tr><th>'.__('Field').'</th><th>'.__('Type').'</th><th>'.__('Value').'</th></tr></thead>'.
				'<tbody>'
			);
			$content = $_T_->getRows(0,1,'','',$entrie);

			foreach($content[0] AS $name => $value)
			{
				//"bug" $content contient l'id et le nom des champs)
				$value = html::escapeHTML($value);
				if (is_numeric($name)) continue;
				$hidden = '';
				if (in_array($name,$primary))
				{
					$hidden = form::hidden(array('primary['.$k.']['.$name.']'),$value);
				}
				$P->content(__('Edit'),
					'<tr class="line">'.
					'<th>'.$hidden.$name.'</th>'.
					'<td>'.$structures[$name]['type'].(($structures[$name]['type'] != 'longtext' && $structures[$name]['type'] != 'datetime')?' ('.$structures[$name]['len'].')':'').'</td>'.
					'<td>'.(($structures[$name]['type'] == 'longtext')?form::textarea(array('key['.$k.']['.$name.']'),50,10,$value):form::field(array('key['.$k.']['.$name.']'),50,$structures[$name]['len'],$value)).'</td>'.
					'</tr>'
				);
			}
			$P->content(__('Edit'),'</tbody></table>'.$P->getInput('save','save',array(__('Save') => 'save'),$settings['picture']).'<hr />');
			$k++;
		}
		$P->content(__('Edit'),
			'<p>'.
			$P->hiddens(
				array(
					'table'=>$_t,
					'action'=>'content',
					'start'=>$_POST['start'],
					'order_col'=>$_POST['order_col'],
					'order_way'=>$_POST['order_way'],
					'redir'=>html::escapeHTML($_POST['redir'])
				)
				,true
			).
			'</p>'.
			'</form>'
		);
	}
	break;

	/** Insert Row **/

	# New row
	case 'insert':
	if (empty($_t))
	{
		$P->info(__('Insert row'),__('Choose table to view:'));
		$tables = $spy->getTables();
		foreach($tables AS $key => $val)
		{
			$P->liste(__('Insert row'),$key,$p_url.'&amp;m=insert&amp;t='.$key);
		}
		$P->content(__('Insert row'));
		break;
	}

	$P->head(__('Insert row'),'<h3>'.__('Table:').' '.$_t.'</h3>');
	# Menu
	$menu = array_diff_key($combo_table_row_action, array(__('Insert') => ''));
	$P->head(__('Insert row'),
		'<form method="post" action="'.$p_url.'&amp;m=form">'.
		$P->getInput($_t,'row',$menu,TRUE).
		'<p>'.
		$P->hiddens(array('table'=>$_t,'action'=>'table','redir'=>$p_url.'&amp;m=insert&amp;t='.$_t),true).
		'</p></form>'
	);

	$P->content(__('Insert row'),
		'<form method="post" action="'.$p_url.'&amp;m=form">'.
		'<table>'.
		'<thead><tr><th>'.__('Field').'</th><th>'.__('Type').'</th><th>'.__('Value').'</th></tr></thead>'.
		'<tbody>'
	);

	$structures = $_T_->getFields();
	$next_id = $_T_->getNextId();
	foreach($structures AS $name => $value)
	{
		$value = html::escapeHTML($value);
		$field = '';
		if (isset($next_id[$name]) && !empty($next_id[$name]))
		{
			$field = $next_id[$name];
		}
		elseif ((!isset($next_id[$name]) || empty($next_id[$name])) && !empty($structures[$name]['default']))
		{
			$field = substr($structures[$name]['default'],1,-1);
		}
		$P->content(__('Insert row'),
			'<tr class="line">'.
			'<th>'.$name.'</th>'.
			'<td>'.$structures[$name]['type'].(($structures[$name]['type'] != 'longtext' && $structures[$name]['type'] != 'datetime')?' ('.$structures[$name]['len'].')':'').'</td>'.
			'<td>'.(($structures[$name]['type'] == 'longtext')?form::textarea(array('key['.$name.']'),50,10,$field):form::field(array('key['.$name.']'),50,$structures[$name]['len'],$field)).'</td>'.
			'</tr>'
		);
	}
	$P->content(__('Insert row'),
		'</tbody></table><p><input type="submit" name="save" value="'.__('Save').'" />'.
		$P->hiddens(array('table'=>$_t,'action'=>'insert','redir'=>$_POST['redir']),true).
		'</p><hr /></form>'
	);
	break;

	/** Structure **/

	# Table structure
	case 'structure':
	if (empty($_t))
	{
		$P->info(__('Structure'),__('Choose table to view:'));
		$tables = $spy->getTables();
		foreach($tables AS $key => $val)
		{
			$P->liste(__('Structure'),$key,$p_url.'&amp;m=structure&amp;t='.$key);
		}
		$P->content(__('Structure'));
		break;
	}

	$P->head(__('Structure'),'<h3>'.__('Table:').' '.$_t.'</h3>');
	# Menu
	$menu = array_diff_key($combo_table_row_action, array(__('Structure') => ''));
	$P->head(__('Structure'),
		'<form method="post" action="'.$p_url.'&amp;m=form">'.
		$P->getInput($_t,'row',$menu,TRUE).
		'<p>'.
		$P->hiddens(array('table'=>$_t,'action'=>'table','redir'=>$p_url.'&amp;m=structure&amp;t='.$_t),true).
		'</p>'.
		'</form>'
	);

	# Set warning if no primary key
	$primary = $_T_->getPrimary();
	if (empty($primary))
	{
		$P->info(__('Structure'),__("There is no primary key on this table, DatabaseSpy can't perform some actions on it."));
	}
	
	$structures = $_T_->getFields();
	if (!empty($structures))
	{
		$P->table(
			__('Structure'),
			'Structure',
			'',
			$structures,
			'',
			array('action' => 'structure', 'table' => $_t),
			$combo_struct_row_action,
			$combo_struct_col_action,
			FALSE
		);
	}

	$keys = $_T_->getKeys();
	if (!empty($keys))
	{
		$P->table(
			__('Structure'),
			'Keys',
			'',
			$keys,
			'',
			array('action' => 'structure', 'table' => $_t),
			$combo_key_row_action,
			$combo_key_col_action,
			TRUE
		);
	}

	$indexes = $_T_->getIndexes();
	if (!empty($indexes))
	{
		$P->table(
			__('Structure'),
			'Indexes',
			'',
			$indexes,
			'',
			array('action' => 'structure', 'table' => $_t),
			$combo_index_row_action,
			$combo_index_col_action,
			TRUE
		);
	}

	$references = $_T_->getReferences();
	if (!empty($references))
	{
		$P->table(
			__('Structure'),
			'References',
			'',
			$references,
			'',
			array('action' => 'structure', 'table' => $_t),
			$combo_reference_row_action,
			$combo_reference_col_action,
			TRUE
		);
	}
	$P->content(__('Structure'));
	break;

	/** New field **/

	# New field
	case 'field':
	if (empty($_t))
	{
		$P->info(__('Add field'),__('Choose table to view:'));
		$tables = $spy->getTables();
		foreach($tables AS $key => $val)
		{
			$P->liste(__('Add field'),$key,$p_url.'&amp;m=structure&amp;t='.$key);
		}
		$P->content(__('Add field'));
		break;
	}
	$P->head(__('Add field'),'<h3>'.__('Table:').' '.$_t.'</h3>');
	# Menu
	$menu = array_diff_key($combo_table_row_action, array(__('Add field') => ''));
	$P->head(__('Add field'),
		'<form method="post" action="'.$p_url.'&amp;m=form">'.
		$P->getInput($_t,'row',$menu,TRUE).
		'<p>'.
		$P->hiddens(array('table'=>$_t,'action'=>'foreigne','redir'=>$p_url.'&amp;m=structure&amp;t='.$_t),true).
		'</p>'.
		'</form>'
	);

	$P->content(__('Add field'),
		'<form method="post" action="'.$p_url.'&amp;m=form">'.
		'<table><thead><tr><th>'.__('Field').'</th><th>'.__('Type').'</th><th>'.__('Len').'</th><th>'.__('Null').'</th><th>'.__('Default').'</th><tr></thead>'.
		'<tbody><tr>'.
		'<td>'.form::field(array('field[field]'),30,20,$_POST['field']['field']).'</td>'.
		'<td>'.form::combo(array('field[type]'),$combo_type,$_POST['field']['type']).'</td>'.
		'<td>'.form::field(array('field[len]'),10,20,$_POST['field']['len']).'</td>'.
		'<td>'.form::combo(array('field[null]'),$combo_null,$_POST['field']['null']).'</td>'.
		'<td>'.form::field(array('field[default]'),30,255,$_POST['field']['default']).'</td>'.
		'</tr></tbody></table>'.
		'<p>'.
		$P->hiddens(array('table'=>$_t,'action'=>'field','redir'=>$p_url.'&amp;m=insert'),true).
		'</p>'.
		libDbSpyPage::getInput('save','save',array(__('Save') => 'save'),$settings['picture']).
		'</form>'
	);
	break;

	/** Add foreign key **/

	# New foreign key
	case 'reference':
	if (empty($_t))
	{
		$P->info(__('Add foreign key'),__('Choose table to view:'));
		$tables = $spy->getTables();
		foreach($tables AS $key => $val)
		{
			$P->liste(__('Add foreign key'),$key,$p_url.'&amp;m=structure&amp;t='.$key);
		}
		$P->content(__('Add foreign key'));
		break;
	}

	$P->head(__('Add foreign key'),'<h3>'.__('Table:').' '.$_t.'</h3>');
	# Menu
	$menu = array_diff_key($combo_table_row_action, array(__('Add foreign key') => ''));
	$P->head(__('Add foreign key'),
		'<form method="post" action="'.$p_url.'&amp;m=form">'.
		$P->getInput($_t,'row',$menu,TRUE).
		'<p>'.
		$P->hiddens(array('table'=>$_t,'action'=>'foreign','redir'=>$p_url.'&amp;m=structure&amp;t='.$_t),true).
		'</p>'.
		'</form>'
	);

	if (!empty($_t) && !isset($foreign))
	{
		$P->content(__('Add foreign key'),'<form method="post" action="'.$p_url.'&amp;m=form">');
		$tables = $spy->getTables();
		$p_cols = $c_cols = array();
		foreach($tables AS $key => $val)
		{
			$indexes = $spy->table($key)->getIndexes();
			foreach($indexes AS $index)
			{
				if (count($index['cols']) == 1)
				{
					if ($key == $_t)
					{
						$p_cols[] = $_t.'->'.$index['cols'][1];
					}
					else
					{
						$c_cols[] = $key.'->'.$index['cols'][1];
					}
				}
			}
		}
		if (!empty($p_cols) && !empty($c_cols))
		{
			$P->content(__('Add foreign key'),
				'<p>'.
				__('Table:').' '.form::combo(array('p_cols'),array_flip($p_cols)).' '.
				__('On:').' '.form::combo(array('c_cols'),array_flip($c_cols)).' '.
				__('ON DELETE').' '.form::combo(array('on_delete'),array_flip($combo_reference_action)).' '.
				__('ON UPDATE').' '.form::combo(array('on_update'),array_flip($combo_reference_action)).' '
			);
		}
		else
		{
			$P->info(__('Add foreign key'),__('You must have at least 2 tables with an index on one column'));
		}
		$P->content(__('Add foreign key'),
			'<p>'.
			$P->hiddens(array('table'=>$_t,'action'=>'foreign','redir'=>$p_url.'&amp;m=foreign&amp;t='.$_t),true).
			'</p>'.
			libDbSpyPage::getInput('save','save',array(__('Save') => 'save'),$settings['picture']).
			'</form>'
		);
	}
	$P->content(__('Add foreign key'));
	break;

	/** Create table **/

	# New table
	case 'create':
	if (empty($_t))
	{
		$P->content(__('Create table'),'<form method="post" action="'.$p_url.'&amp;m=form">
		<p style="display: inline">'.__('Name').': '.$core->prefix.form::field(array('table'),30,20,'my_table').' '.__('Number of fields').': '.form::field(array('num_fields'),2,2,2));
	}
	else
	{
		$P->content(__('Create table'),
			'<form method="post" action="'.$p_url.'&amp;m=form">'.
			'<table>'.
			'<thead><tr><th></th><th>'.__('Name').'</th><th>'.__('Type').'</th><th>'.__('Len').'</th><th>'.__('Null').'</th><th>'.__('Default').'</th></tr>'.
			'<tbody>'
		);
		for ($i = 0; $i < $create_num_fields; $i++)
		{
			$P->content(__('Create table'),
				'<tr>'.
				'<th>'.$i.'</th>'.
				'<td>'.form::field(array('fields['.$i.'][field]'),30,20,$_POST['fields'][$i]['field']).'</td>'.
				'<td>'.form::combo(array('fields['.$i.'][type]'),$combo_type,$_POST['fields'][$i]['type']).'</td>'.
				'<td>'.form::field(array('fields['.$i.'][len]'),10,20,$_POST['fields'][$i]['len']).'</td>'.
				'<td>'.form::combo(array('fields['.$i.'][null]'),$combo_null,$_POST['fields'][$i]['null']).'</td>'.
				'<td>'.form::field(array('fields['.$i.'][default]'),30,255,$_POST['fields'][$i]['default']).'</td>'.
				'</tr>'
			);
		}
		$P->content(__('Create table'),
			'</tbody></table>'.
			'<p>'.
			$P->hiddens(array('table'=>$_t,'num_fields'=>$create_num_fields)).
			'</p>'
		);
	}
	$P->content(__('Create table'),
		'<p>'.
		$P->hiddens(array('action'=>'create','redir'=>$p_url.'&amp;m=insert'),true).
		'</p>'.
		libDbSpyPage::getInput('save','save',array(__('Save') => 'save'),$settings['picture']).
		'</form>'
	);
	break;

	/** Export **/

	# Table sexport
	case 'export':
	if (!isset($export_file) && !isset($export_url))
	{
		$tables = $spy->getTables();
		foreach($tables AS $key => $val)
		{
			$array_tables[$key] = $key;
		}
		$P->content(__('Export table'),
			'<form method="post" action="'.$p_url.'&amp;m=form">'.
			'<p>'.
			str_replace(
				array('%table%', '%action%', '%type%', '%format%'),
				array(
					form::combo(array('table'),$array_tables,$_t),
					form::combo(array('save'),$combo_export_action,'view'),
					form::combo(array('type'),$combo_export_type,$settings['export_type']),
					form::combo(array('format'),$combo_export_format,$settings['export_format'])
					),
				__('%action% %type% of %table% in %format% format')
			).
			$P->hiddens(array('action'=>'export','redir'=>$p_url.'&amp;m=export&amp;t='.$_t),true).
			'<input type="submit" value="'.__('ok').'" /></p>'.
			'</form>'
		);
	}
	elseif (isset($export_file))
	{
		$P->content(__('Export table'),'<div class="about"><pre>'.htmlentities($export_file).'</pre></div>');
	}
	elseif (isset($export_url))
	{
		$P->content(__('Export table'),'...');
	}
	break;

	/** Settings **/

	# dbSpy setting
	case 'settings':
	$settings = $spy->getSettings();
	$P->info(__('Settings'),__('All settings are global'));
	$P->content(__('Settings'),'<form method="post" action="'.$p_url.'&amp;m=form">');

	foreach($settings AS $key => $val)
	{
		if (isset($combo_settings_break_title[$key]))
		{
			$P->content(__('Settings'),'<h3>'.$combo_settings_break_title[$key].'</h3>');
		}
		if (is_array($combo_settings_type[$key]))
		{
			$P->content(__('Settings'),'<p>'.$combo_settings[$key].'<br />'.form::combo(array('settings['.$key.']',$key),$combo_settings_type[$key],$val).'</p>');
		}
		else
		{
			if (empty($settings[$key])) { $settings[$key] = $combo_settings_type[$key]; }
			$P->content(__('Settings'),'<p>'.$combo_settings[$key].'<br /'.form::field(array('settings['.$key.']',$key),50,255,$settings[$key]).'</p>');
		}
	}
	$P->content(__('Settings'),
		'<p>'.
		$P->hiddens(array('settings[confirm]'=>'confirm','action'=>'settings','redir'=>$p_url.'&amp;m=settings'),true).
		'</p>'.
		libDbSpyPage::getInput('save','save',array(__('Save') => 'save'),$settings['picture']).
		'</form>'
	);
	break;
}
?>