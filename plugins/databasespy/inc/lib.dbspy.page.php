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

class LibDbSpyPage {

	private $settings = array();
	private $error = '';
	private $warning = '';
	private $sql = '';
	private $title = '';
	private $head = array();
	private $menu = array();
	private $content = array();
	private $page = '';
	private $liste = array();
	private $pager = array();
	private $table = array();
	public $colors = array(
		'black','blue','orange','red','green','purple','lightgrey','pink','lightblue'
	);

	public function __construct($settings)
	{
		$this->settings = $settings;
	}
	# Set Error message list
	public function error($str)
	{
		$this->error .= $str;
	}
	# Get formated error message list
	private function getError()
	{
		if (empty($this->error)) return '';

		return '<div class="error"><strong>'.__('Error:').'</strong>'.$this->error.'</div>';
	}
	# Set warning message list
	public function warning($str)
	{
		$this->warning .= text::toUTF8($str);
	}
	# Get formated warning message list
	private function getWarning()
	{
		if (empty($this->warning)) return '';

		return '<div class="message">'.$this->warning.'</div>';
	}
	# Set Sql text
	public function setSql($str)
	{
		$this->sql .= '- '.$str.'<br />';
	}
	# Get sql texts
	private function getSql()
	{
		if (empty($this->sql)) return;

		return '<div class="about"><h2>'.__('sql requests').':</h2>'.$this->sql.'</div>';
	}
	# Set page title
	public function title($str)
	{
		$this->title .= $str;
	}
	# Get formated page title
	private function getTitle()
	{
		if (!empty($this->title))
		{
			return '<h2>'.$this->title.'</h2>';
		}
		return '<h2>'.__('Database spy').'</h2>';
	}
	# Set menu list
	public function menu($id,$name,$url)
	{
		$this->menu[$id] = array('name'=>$name,'url'=>$url);
	}
	# Get formated menu list
	private function getMenu($tab)
	{
		if (empty($this->menu)) return;

		$res = '';
		foreach($this->menu AS $id => $v)
		{
			if ($tab == $id)
			{
				$res .= '<div class="multi-part" id="tab_'.$v['id'].'" title="'.$v['name'].'"></div>';
			}
			else
			{
				$res .= '<p><a href="'.$v['url'].'" class="multi-part">'.$v['name'].'</a></p>';
			}
		}
		return $res;
	}
	public function head($name,$str)
	{
		$this->head[$name] .= $str;
	}
	private function getHead($name)
	{
		return $this->head[$name];
	}
	# STATIC Get picture instead name
	public static function getPicture($name,$float=TRUE)
	{
		if (file_exists(dirname(__FILE__).'/img/'.$name.'.png'))
		{
			return '<img alt="" class="pict" style="'.(($float)?'float:left;':'').'" src="index.php?pf=databasespy/inc/img/'.$name.'.png" />';
		}
		else
		{
			return '<span>'.__($name).'</span> ';
		}
	}
	# STATIC Create html table draw input
	public static function getInput($value,$name,$content,$picture=FALSE)
	{
		$rs = '';
		foreach($content AS $k => $v)
		{
			$rs .= '<input type="submit" name="'.$name.'['.$v.']['.$value.']" value="'.$k.'" title="'.$k.'" ';
			if ($picture)
			{
				$rs .= 'class="dbs-picture-action" style="background: transparent url(index.php?pf=databasespy/inc/img/a_'.$v.'.png) no-repeat 1px 1px;" ';
			}
			$rs .= '/> ';
		}
		return '<p style="display: inline;">'.$rs.'</p>';
	}

	# Set a content menu
	public function liste($name,$key,$val='')
	{
		if (!is_array($key))
		{
			$this->liste[$name][$key] = $val;
		}
		else
		{
			$this->liste[$name] = $key;
		}
	}
	# Get content menu
	private function getListe($name)
	{
		if (!isset($this->liste[$name])) return '';

		$rs = '';
		foreach($this->liste[$name] AS $key => $val)
		{
			$rs .= '<li><a href="'.$val.'">'.$key.'</a></li>';
		}
		return '<ul>'.$rs.'</ul>';
	}
	# Set content pager
	public function pager($name,$url,$pager_name,$current_page,$nb_total_elements)
	{
		if (abs((integer) $nb_total_elements) <= abs((integer) $this->settings['nb_per_page']))
		{
			return '';
		}
		$this->pager[$name] = array(
			'url' => $url, 
			'pager_name' => $pager_name,
			'current_page' => abs((integer) $current_page),
			'nb_total_elements' => abs((integer) $nb_total_elements),
			'nb_per_page' => abs((integer) $this->settings['nb_per_page'])
		);
	}
	# Get content pager
	private function getPager($name)
	{
		if (!isset($this->pager[$name])) return '';

		$url = $this->pager[$name]['url']; 
		$pager_name = $this->pager[$name]['pager_name'];
		$current_page = $this->pager[$name]['current_page'];
		$nb_total_elements = $this->pager[$name]['nb_total_elements'];
		$nb_per_page = $this->pager[$name]['nb_per_page'];

		$res ='';
//faire des groupes (ex si il y a plus de 20 pages!)
		# Pages count
		$nb_pages = ceil($nb_total_elements/$nb_per_page);

		# Verify current page
		if ($current_page > $nb_pages || $current_page < 1)
		{
			$current_page = 1;
		}

		# Changing page ref
		if (preg_match('/[?&]'.$pager_name.'=[0-9]+/',$url))
		{
			$url = preg_replace('/([?&]'.$pager_name.'=)[0-9]+/','$1%1',$url);
		}
		elseif (preg_match('/[?]/',$url))
		{
			$url .= '&'.$pager_name.'=%1';
		}
		else
		{
			$url .= '?'.$pager_name.'=%1';
		}

		# Write nav
		if ($current_page != 1) {
			$res .= '<a href="'.str_replace('%1',1,$url).'">&lt;&lt;</a> - ';
			$res .= '<a href="'.str_replace('%1',($current_page - 1),$url).'">&lt;</a> - ';
		} else {
			$res .= '<span>&lt;&lt;</span> - ';
			$res .= '<span>&lt;</span> - ';
		}
		for($i = 1; $i <= $nb_pages; $i++)
		{
			if ($i == $current_page)
			{
				$res .= '<span >'.$this->getPicture($i,FALSE).'</span>';
			}
			else
			{
				$res .= '<a href="'.str_replace('%1',$i,$url).'">'.$this->getPicture($i,FALSE).'</a>';
			}
			if ($i != $nb_pages)
			{
				$res .= ' - ';
			}
		}
		if ($current_page != $nb_pages)
		{
			$res .= ' - <a href="'.str_replace('%1',($current_page + 1),$url).'">&gt;</a>';
			$res .= ' - <a href="'.str_replace('%1',($nb_pages),$url).'">&gt;&gt;</a>';
		}
		else
		{
			$res .= ' - <span>&gt;</span>';
			$res .= ' - <span>&gt;&gt;</span>';
		}
		return '<div class="pager">'.__('Page:').'&nbsp;'.$res.'</div>';
	}

	# Set content table form
	public function table($name,$title,$fields_title,$ceils_content,$ceils_hidden=array(),$form_hidden=array(),$rows_combo='',$table_combo='',$first_field_drop=FALSE)
	{
		if (empty($name)) return '';

		if (empty($title))
		{
			$title = '__NULL__'.((isset($this->table[$name]))?(count($this->table[$name]) + 1):1);
		}
		$this->table[$name][$title] = array(
			'fields_title' => $fields_title,
			'ceils_content' => $ceils_content,
			'ceils_hidden' => $ceils_hidden,
			'form_hidden' => $form_hidden,
			'rows_combo' => $rows_combo,
			'table_combo' => $table_combo,
			'first_field_drop' => $first_field_drop,
			'ceil_chunk' => $this->settings['chunk'],
			'ceil_chunk_len' => $this->settings['chunk_len'],
			'colorize' => $this->settings['colorize'],
			'colors' => $this->colors,
			'picturize' => $this->settings['picture'],
		);
	}
	# Get html content tables forms
	public function getTable($name)
	{
		if (!isset($this->table[$name]) || !is_array($this->table[$name])) return '';

		$rs = '';
		foreach($this->table[$name] AS $title => $table)
		{
			if (!empty($rs)) { $rs .= '<hr />'; }
			if ($title != str_replace('__NULL__','',$title)) { $title = ''; }
			$colors = ($table['colorize'])?$table['colors']:array('black');

			foreach($table['ceils_content'] AS $v)
			{
				$colspan = count($v) + 1;
				break;
			}
			if (!$table['first_field_drop']) { $colspan++; }
			if (!empty($table['rows_combo'])) { $colspan++; }
			if (!empty($table['table_combo'])) { $colspan++; }

			$rs .= '
			<form method="post" action="plugin.php?p=databasespy&amp;m=form">
			<table class="clear"><thead>';
			if (!empty($title))
			{
				$rs .= '<tr><th class="ceil_title" colspan="'.$colspan.'">'.((file_exists(dirname(__FILE__).'/img/'.strtolower($title).'.png'))?'<img alt="" style="float: left;" src="index.php?pf=databasespy/inc/img/'.strtolower($title).'.png" />':'').__($title).':</th></tr>';
			}
			$rs .= ''.
			'<tr><th class="ceil_head"></th>'.((!empty($table['table_combo']))?'<th class="ceil_head"></th>':'').((FALSE === $table['first_field_drop'])?'<th class="ceil_head"	>'.__('name').'</th>':'');

			foreach($table['ceils_content'] AS $fields)
			{
				foreach($fields AS $k => $v)
				{
					$rs .= '<th class="ceil_head">'.((isset($table['fields_title'][$k]))?$table['fields_title'][$k]:__($k)).'</th>';
				}
				break;
			}
			$rs .= '<th class="ceil_head">'.__('Action').'</th></tr></thead><tbody>';
			$i = 0;
			foreach($table['ceils_content'] AS $name => $fields)
			{
				reset($colors);
				$first = (TRUE === $table['first_field_drop'])?current($fields):$name;
				$rs .= '
				<tr class="line">
				'.((!empty($table['table_combo']))?'<th class="minimal ceil_head">'.form::checkbox(array('key['.$i.']'),$i).'</th>':'').'
				<th class="ceil_head">'.($i + 1);
				if (empty($table['ceils_hidden']))
				{
					$rs .= form::hidden(array('entries['.$i.']'),$name);
				}
				$rs .=
				'</th>
				'.((FALSE === $table['first_field_drop'])?'<td class="ceil" style="color: '.current($colors).'">'.$name.'</td>':'');

				foreach($fields AS $k => $field)
				{
					if (!current($colors)) { reset($colors); }
					$rs .= '<td class="ceil" style="color: '.next($colors).';">';

					if (!empty($table['ceils_hidden']) && in_array($k,$table['ceils_hidden']))
					{
						$rs .= form::hidden(array('entries['.$i.']['.$k.']'),$field);
					}
					if (is_array($field))
					{
//replace by implode
						$cols = '';
						foreach($field AS $v)
						{
							$cols .= html::escapeHTML($v).'<br />';
						}
						$rs .= substr($cols,0,-6);
					}
					else
					{
						if ($table['ceil_chunk']) {
							//structure name 'null' 'primary' 'unique' and field set to 1
							if (($k == 'primary' || $k == 'unique' || $k == 'null')&& !empty($field))
							{
								$n_field = '<span class="ceil_chunk">&nbsp;TRUE&nbsp;&nbsp;</span>';
							}
							// structure name 'null' 'primary' 'unique' and field empty
							elseif (($k == 'unique'|| $k == 'primary' || $k == 'null' || $k == 'len') && empty($field))
							{
								$n_field = '&nbsp;';//<span style="'.$style.'">&nbsp;FALSE&nbsp;</span>';
							}
							//null
							elseif (is_null($field))
							{
								$n_field = '<span class="ceil_chunk">&nbsp;NULL&nbsp;</span>';
							}
							//long text
							elseif (substr($field,0,$table['ceil_chunk_len']) != $field)
							{
								$size = (round(strlen($field)/1024,1) >= 1.1)?round(strlen($field)/1024,1).'Ko':round(strlen($field)).'o';
								$n_field = '<span class="ceil_chunk">&nbsp;'.$size.'&nbsp;</span>';
							}
							//as is
							else
							{
								$n_field = $field;
							}
							if (empty($n_field) && '0' != $n_field)
							{
								$n_field = '<span class="ceil_chunk">&nbsp;Empty&nbsp;</span>';
							}
							$field = '<span title="'.wordwrap(html::escapeHTML($field),100,"\n",TRUE).'">'.$n_field.'</span>';
						}
						$rs .= $field;
					}
					$rs .= '</td>';
				}
				if (!empty($table['ceils_hidden'])) { $input = $i; } else { $input = $first; }
				$rs .= '
				'.((!empty($table['rows_combo']))?'<td class="ceil nowrap">'.$this->getInput($input,'row',$table['rows_combo'],$table['picturize']).'</td>':'').'
				</tr>';
				$i++;
			}
			$rs .= '</tbody></table>'
			.((!empty($table['table_combo']))?'<p class="checkboxes-helpers">&nbsp;</p><p>'.__('Selected items action:').'</p>'.$this->getInput('all','col',$table['table_combo'],$table['picturize']):'')
			.'<p>';

			foreach($table['form_hidden'] AS $k => $v)
			{
				$rs .= form::hidden(array($k),$v);
			}
			$rs .= $GLOBALS['core']->formNonce()
			.'</p></form>';
		}
		return $rs;
	}
	# Set info of a page content
	public function info($name,$str)
	{
		$this->info[$name] = (isset($this->info[$name]))?$this->info[$name].'<br />'.$str:$str;
	}
	# Get info of a page content
	public function getInfo($name)
	{
		if (!isset($this->info[$name])) return '';

		return '<div class="info">'.$this->getPicture('Note').'&nbsp;'.$this->info[$name].'</div>';
	}
	# Set page content
	public function content($name,$str='')
	{
		@$this->content[$name] .= $str;
	}
	# Get formated page content
	private function getContent()
	{
		$res = '';
		foreach($this->content AS $name => $content)
		{
			$res .= '<h2>'.__($name).'</h2>'.$this->getHead($name).$this->getInfo($name).$this->getListe($name).$this->getPager($name).'<br />'.$content.'<br />'.$this->getTable($name).$this->getPager($name);
		}
		if (!empty($res))
		{
			return '<div class="content">'.$res.'</div>';
		}
		return '<div class="info">'.$this->getPicture('Note').'&nbsp;'.__('Nothing to show!').'</div>';
	}
	# Get full page
	public function get($tab)
	{
		return 
		$this->getTitle().
		'<p>'.__('The actions taken here are directs and irreversibles').'</p>'.
		$this->getMenu($tab).
		$this->getError().
		$this->getWarning().
		$this->getSql().
		$this->getContent();
	}

	public static function hiddens($hiddens,$formNonce=false)
	{
		$res = '';
		foreach($hiddens as $k => $v)
		{
			$res .= form::hidden(array($k),$v);
		}
		if ($formNonce)
		{
			$res .= $GLOBALS['core']->formNonce();
		}
		return $res;
	}
}

?>