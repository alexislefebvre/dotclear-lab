<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009-2015 Tomtom and contributors
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) {return;}

class multiTocPost
{
	protected $p_h = '/<h([1-6])>(.*)<\/h\\1>/';
	protected $p_t = '/<p>::TOC::<\/p>/';
	protected $p_r_a = '<a href="%1$s#%2$s">%3$s</a>';
	protected $p_r_h = '<h%1$s%2$s>%3$s</h%1$s>';
	protected $p_r_t = '<div class="post-toc">%s</div>';
	protected $tree = array();
	protected $num = array();
	protected $count = false;
	protected $rs;
	
	public function __construct($rs)
	{
		$s = unserialize($rs->core->blog->settings->multiToc->multitoc_settings);
		
		$this->rs = $rs;
		$this->count = $s['post']['numbering'];
		$this->getTree();
	}
	
	public function process($c)
	{
		if (preg_match($this->p_t,$c)) {
			$c = preg_replace($this->p_t,sprintf($this->p_r_t,$this->getToc()),$c);
		}
		
		$c = preg_replace_callback($this->p_h,array($this,'replaceTitles'),$c);
		
		return $c;
	}
	
	protected function getTree()
	{
		preg_match_all($this->p_h,$this->rs->post_excerpt_xhtml.$this->rs->post_content_xhtml,$matches);
		
		$levels = $matches[1];
		$titles = $matches[2];
		$offset = min($levels);
		$key = array('','','','','');
		$count = array(0,0,0,0,0,0);
		
		foreach ($levels as $k => $v) {
			$title = "$titles[$k]";
			
			if ($title === 'Notes') { continue; }
			
			$dim = $v - $offset;
			
			switch ($dim) {
				case 0:
					$key[0] = $title;
					$this->tree[$title] = null;
					$this->num[$title] = count($this->tree);
					break;
				case 1:
					$key[1] = $title;
					$this->tree[$key[0]][$title] = null;
					$this->num[$title] = 
						count($this->tree).'.'.
						count($this->tree[$key[0]]);
					break;
				case 2:
					$key[2] = $title;
					$this->tree[$key[0]][$key[1]][$title] = null;
					$this->num[$title] = 
						count($this->tree).'.'.
						count($this->tree[$key[0]]).'.'.
						count($this->tree[$key[0]][$key[1]]);
					break;
				case 3:
					$key[3] = $title;
					$this->tree[$key[0]][$key[1]][$key[2]][$title] = null;
					$this->num[$title] =
						count($this->tree).'.'.
						count($this->tree[$key[0]]).'.'.
						count($this->tree[$key[0]][$key[1]]).'.'.
						count($this->tree[$key[0]][$key[1]][$key[2]]);
					break;
				case 4:
					$key[4] = $title;
					$this->tree[$key[0]][$key[1]][$key[2]][$key[3]][$title] = null;
					$this->num[$title] =
						count($this->tree).'.'.
						count($this->tree[$key[0]]).'.'.
						count($this->tree[$key[0]][$key[1]]).'.'.
						count($this->tree[$key[0]][$key[1]][$key[2]]).'.'.
						count($this->tree[$key[0]][$key[1]][$key[2]][$key[3]]);
					break;
				case 5:
					$this->tree[$key[0]][$key[1]][$key[2]][$key[3]][$key[4]][$title] = null;
					$this->num[$title] =
						count($this->tree).'.'.
						count($this->tree[$key[0]]).'.'.
						count($this->tree[$key[0]][$key[1]]).'.'.
						count($this->tree[$key[0]][$key[1]][$key[2]]).'.'.
						count($this->tree[$key[0]][$key[1]][$key[2]][$key[3]]).'.'.
						count($this->tree[$key[0]][$key[1]][$key[2]][$key[3]][$key[4]]);
					break;
			}
		}
	}
	
	protected function getToc($tree = null)
	{
		$res = array();
		
		if (is_null($tree)) {
			$tree = $this->tree;
		}
		
		foreach ($tree as $title => $child)
		{
			$url = $this->rs->getURL().'#'.text::tidyURL($title);
			if ($this->count) {
				if (array_key_exists($title,$this->num)) {
					$title = sprintf('%s: %s',$this->num[$title],$title);
				}
			}
			
			$link = sprintf('<a href="%1$s">%2$s</a>',$url,$title);
			
			if (is_array($child)) {
				$link .= $this->getToc($child);
			}
			array_push($res,sprintf('<li>%s</li>',$link));
		}
		
		return sprintf('<ul>%s</ul>',implode("\n",$res));
	}
	
	protected function replaceTitles($matches)
	{
		$num = array_key_exists($matches[2],$this->num) ? $this->num[$matches[2]] : '';
		
		return sprintf(
			'<h%1$s%2$s>%3$s</h%1$s>',$matches[1],
			' id="'.text::tidyURL($matches[2]).'"',
			sprintf(
				(array_key_exists($matches[2],$this->num) && $this->count ? '%2$s: %1$s' : '%1$s'),
				$matches[2],$num
			)
		);
	}
}

class multiTocUi
{	
	public static function form($type = 'cat')
	{
		global $core;
		
		$order_entry_data = array(
			__('Title up') => 'post_title asc',
			__('Title down') => 'post_title desc',
			__('Date up') => 'post_dt asc',
			__('Date down') => 'post_dt desc',
			__('Author up') => 'user_id asc',
			__('Author down') => 'user_id desc',
			__('Comments number up') => 'nb_comment asc',
			__('Comments number down') => 'nb_comment desc',
			__('Trackbacks number up') => 'nb_trackback asc',
			__('Trackbacks number down') => 'nb_trackback desc'
		);
		
		switch($type)
		{
			case 'tag':
				$legend = __('TOC by tags');
				$enable = __('Enable TOC by tags');
				$order_group = __('Order of tags:');
				$order_entry = __('Order of entries:');
				$order_group_data = array(
					__('Name up') =>  'asc',
					__('Name down') =>  'desc',
				);
				break;
			case 'alpha':
				$legend = __('TOC by alpha list');
				$enable = __('Enable TOC by alpha list');
				$order_group = __('Order of alpha list:');
				$order_entry = __('Order of entries:');
				$order_group_data = array(
					__('Alpha up') =>  'post_letter asc',
					__('Alpha down') =>  'post_letter desc',
				);
				break;
			case 'post':
				$legend = __('Post TOC');
				$enable = __('Enable post TOC');
				$numbering = __('Auto numbering');
				break;
			default:
				$legend = __('TOC by category');
				$enable = __('Enable TOC by category');
				$order_group = __('Order of categories:');
				$order_entry = __('Order of entries:');
				$order_group_data = array(
					__('No option') => '',
				);
				break;
		}
		
		if ($type !== 'post') {
			$res = 
			'<div class="fieldset">'.
			'<h4>'.$legend.'</h4>'.
			'<div class="two-cols clearfix">'.
      '<div class="col">'.
			'<p><label for="'.$type.'_enable" class="classic">'.
			form::checkbox($type.'_enable',1,multiTocUi::getSetting($type,'enable')).$enable.
			'</label></p>'.
			'<p><label for="'.$type.'_display_nb_entry" class="classic">'.
			form::checkbox($type.'_display_nb_entry',1,multiTocUi::getSetting($type,'display_nb_entry')).
			__('Display entry number of each group').
			'</label></p>'.
			'<p><label for="'.$type.'_display_date" class="classic">'.
			form::checkbox($type.'_display_date',1,multiTocUi::getSetting($type,'display_date')).
			__('Display date').
			'</label></p>'.
			'<p><label for="'.$type.'_display_author" class="classic">'.
			form::checkbox($type.'_display_author',1,multiTocUi::getSetting($type,'display_author')).
			__('Display author').
			'</label></p>'.
			'<p><label for="'.$type.'_display_cat" class="classic">'.
			form::checkbox($type.'_display_cat',1,multiTocUi::getSetting($type,'display_cat')).
			__('Display category').
			'</label></p>'.
			'<p><label for="'.$type.'_display_nb_com" class="classic">'.
			form::checkbox($type.'_display_nb_com',1,multiTocUi::getSetting($type,'display_nb_com')).
			__('Display comment number').
			'</label></p>'.
			'<p><label for="'.$type.'_display_nb_tb" class="classic">'.
			form::checkbox($type.'_display_nb_tb',1,multiTocUi::getSetting($type,'display_nb_tb')).
			__('Display trackback number').
			'</label></p>'.
			'<p><label for="'.$type.'_display_tag" class="classic">'.
			form::checkbox($type.'_display_tag',1,multiTocUi::getSetting($type,'display_tag')).
			__('Display tags').
			'</label></p>'.
			'</div><div class="col">'.
			'<p><label for="'.$type.'_order_group">'.
			$order_group.'</label>'.
			form::combo(array($type.'_order_group'),$order_group_data,multiTocUi::getSetting($type,'order_group')).
			'</p>'.
			'<p><label for="'.$type.'_order_entry">'.
			$order_entry.'</label>'.
			form::combo(array($type.'_order_entry'),$order_entry_data,multiTocUi::getSetting($type,'order_entry')).
			'</p>'.
			'<p><label for="'.$type.'_format_date">'.
			__('Format date:').'</label>'.
			form::field($type.'_format_date',40,255,multiTocUi::getSetting($type,'format_date')).
			'</p>'.
			'</div></div>'.
			'</div>' ;
		}
		else {
			$res = 
			'<div class="fieldset">'.
			'<h4>'.$legend.'</h4>'.
      '<p><label for="'.$type.'_enable" class="classic">'.
			form::checkbox($type.'_enable',1,multiTocUi::getSetting($type,'enable'),null,null,!$core->plugins->moduleExists('stacker')).$enable.
			'</label></p>'.
			'<p><label for="'.$type.'_numbering" class="classic">'.
			form::checkbox($type.'_numbering',1,multiTocUi::getSetting($type,'numbering'),null,null,!$core->plugins->moduleExists('stacker')).$numbering.
			'</label></p>'.
			'<p class="info">'.__('Those options require stacker plugin').'</p>'.
			'</div>';
			
		}
		
		return $res;
	}
	
	public static function getSetting($type,$value)
	{
		global $core;
		
		$settings	= unserialize($core->blog->settings->multiToc->multitoc_settings);
		
		return isset($settings[$type][$value]) ? $settings[$type][$value] : '';
	}
}