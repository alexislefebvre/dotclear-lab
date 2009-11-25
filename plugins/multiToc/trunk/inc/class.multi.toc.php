<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of multiToc, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom and contributors
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class multiTocBehaviors
{
	public static function addTplPath()
	{		
		$GLOBALS['core']->tpl->setPath($GLOBALS['core']->tpl->getPath(), dirname(__FILE__).'/default-templates');
	}
	
	public static function coreBlogGetPosts($rs)
	{
		$s = unserialize($GLOBALS['core']->blog->settings->multitoc_settings);
		
		if (isset($s['post']['enable']) && $s['post']['enable']) {
			$rs->extend('rsMultiTocPost');
		}
	}
	
	public static function postHeaders()
	{
		$s = unserialize($GLOBALS['core']->blog->settings->multitoc_settings);
		
		return
			(isset($s['post']['enable']) && $s['post']['enable']) ?
			'<script type="text/javascript" src="index.php?pf=multiToc/js/post.min.js"></script>'.
			'<script type="text/javascript">'."\n".
			"//<![CDATA[\n".
			dcPage::jsVar('jsToolBar.prototype.elements.multiToc.title',__('Table of content')).
			"\n//]]>\n".
			"</script>\n" : '';
	}
}

class rsMultiTocPost
{
	protected static $p_h = '/<h([1-6])>(.*)<\/h\\1>/';
	protected static $p_t = '/<p>::TOC::<\/p>/';
	protected static $p_r_a = '<a href="%1$s#%2$s">%3$s</a>';
	protected static $p_r_h = '<h%1$s%2$s>%3$s</h%1$s>';
	protected static $tidy_titles = array();
	protected static $callback = array(self,'replaceTitles');
	
	public static function getExcerpt($rs,$absolute_urls=false)
	{
		if ($absolute_urls) {
			$c = html::absoluteURLs($rs->post_excerpt_xhtml,$rs->getURL());
		} else {
			$c = $rs->post_excerpt_xhtml;
		}
		
		if ($rs->hasToc()) {
			$toc = self::genToc($rs);
			$c = preg_replace(self::$p_t,$toc,$c);
			$c = preg_replace_callback(self::$p_h,self::$callback,$c);
		}
		
		return $c;
	}
	
	public static function getContent($rs,$absolute_urls=false)
	{
		if ($absolute_urls) {
			$c = html::absoluteURLs($rs->post_content_xhtml,$rs->getURL());
		} else {
			$c = $rs->post_content_xhtml;
		}
		
		if ($rs->hasToc()) {
			$toc = self::genToc($rs);
			$c = preg_replace(self::$p_t,$toc,$c);
			$c = preg_replace_callback(self::$p_h,self::$callback,$c);	
		}
		
		return $c;
	}
	
	public static function hasToc($rs)
	{
		if (preg_match(self::$p_t,$rs->post_excerpt_xhtml.$rs->post_content_xhtml)) {
			return true;
		}
		else {
			return false;
		}
	}
	
	protected static function genToc($rs)
	{
		preg_match_all(self::$p_h,$rs->post_excerpt_xhtml.$rs->post_content_xhtml,$matches);
		
		$levels = $matches[1];
		$titles = $matches[2];
		
		$res = '';
		
		foreach ($levels as $k => $v) {
			$delta = $k === 0 ? $v : $v - $levels[$k-1];
			
			if($delta > 0) {
				$res .= "<ul>\n";
			} elseif ($delta < 0) {
				$res .= "</li>\n";
				for($j = 0; $j < abs($delta); $j++) {
					$res .= "</ul>\n</li>\n";
				}
			} else {
				$res .= "</li>\n";
			}
			
			$res .= "<li>".sprintf(self::$p_r_a,$rs->getURL(),text::str2URL($titles[$k]),$titles[$k]);
			
			self::$tidy_titles[$titles[$k]] = text::str2URL($titles[$k]);
		}
		for($j = 0; $j < strlen($v); $j++) {
			$res .= "</li>\n</ul>";
		}
		
		return $res;
	}
	
	public static function replaceTitles($matches)
	{
		return sprintf(self::$p_r_h,$matches[1],' id="'.self::$tidy_titles[$matches[2]].'"',$matches[2]);
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
				$order_group = __('Order of tags');
				$order_entry = __('Order of entries');
				$order_group_data = array(
					__('Name up') =>  'asc',
					__('Name down') =>  'desc',
				);
				break;
			case 'alpha':
				$legend = __('TOC by alpha list');
				$enable = __('Enable TOC by alpha list');
				$order_group = __('Order of alpha list');
				$order_entry = __('Order of entries');
				$order_group_data = array(
					__('Alpha up') =>  'post_letter asc',
					__('Alpha down') =>  'post_letter desc',
				);
				break;
			case 'post':
				$legend = __('Post TOC');
				$enable = __('Enable post TOC');
				break;
			default:
				$legend = __('TOC by category');
				$enable = __('Enable TOC by category');
				$order_group = __('Order of categories');
				$order_entry = __('Order of entries');
				$order_group_data = array(
					__('No option') => '',
				);
				break;
		}

		$res = $type !== 'post' ?
			'<fieldset>'.
			'<legend>'.$legend.'</legend>'.
			'<p><label class="classic">'.
			form::checkbox('enable_'.$type,1,getSetting($type,'enable')).$enable.
			'</label></p>'.
			'<p><label>'.
			$order_group.
			form::combo(array('order_group_'.$type),$order_group_data,getSetting($type,'order_group')).
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_nb_entry_'.$type,1,getSetting($type,'display_nb_entry')).
			__('Display entry number of each group').
			'</label></p>'.
			'<p><label>'.
			$order_entry.
			form::combo(array('order_entry_'.$type),$order_entry_data,getSetting($type,'order_entry')).
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_date_'.$type,1,getSetting($type,'display_date')).
			__('Display date').
			'</label></p>'.
			'<p><label>'.
			__('Format date :').
			form::field('format_date_'.$type,40,255,getSetting($type,'format_date')).
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_author_'.$type,1,getSetting($type,'display_author')).
			__('Display author').
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_cat_'.$type,1,getSetting($type,'display_cat')).
			__('Display category').
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_nb_com_'.$type,1,getSetting($type,'display_nb_com')).
			__('Display comment number').
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_nb_tb_'.$type,1,getSetting($type,'display_nb_tb')).
			__('Display trackback number').
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_tag_'.$type,1,getSetting($type,'display_tag')).
			__('Display tags').
			'</label></p>'.
			'</fieldset>' :
			'<fieldset>'.
			'<legend>'.$legend.'</legend>'.
			'<p><label class="classic">'.
			form::checkbox('enable_'.$type,1,getSetting($type,'enable')).$enable.
			'</label></p>'.
			'<p><label>'.
			'</fieldset>';

		echo $res;
	}
}

?>