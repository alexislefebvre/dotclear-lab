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

class multitocUi
{	
	public static function form($type = 'cat', $settings)
	{
		global $core;

		$order_entry_data = array(
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

		$res = 
			'<fieldset>'.
			'<legend>'.$legend.'</legend>'.
			'<p><label class="classic">'.
			form::checkbox('enable_'.$type,1,$settings[$type]['enable']).$enable.
			'</label></p>'.
			'<p><label>'.
			$order_group.
			form::combo(array('order_group_'.$type),$order_group_data,$settings[$type]['order_group']).
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_nb_entry_'.$type,1,$settings[$type]['display_nb_entry']).
			__('Display entry number of each group').
			'</label></p>'.
			'<p><label>'.
			$order_entry.
			form::combo(array('order_entry_'.$type),$order_entry_data,$settings[$type]['order_entry']).
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_date_'.$type,1,$settings[$type]['display_date']).
			__('Display date').
			'</label></p>'.
			'<p><label>'.
			__('Format date :').
			form::field('format_date_'.$type,40,255,$settings[$type]['format_date']).
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_author_'.$type,1,$settings[$type]['display_author']).
			__('Display author').
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_cat_'.$type,1,$settings[$type]['display_cat']).
			__('Display category').
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_nb_com_'.$type,1,$settings[$type]['display_nb_com']).
			__('Display comment number').
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_nb_tb_'.$type,1,$settings[$type]['display_nb_tb']).
			__('Display trackback number').
			'</label></p>'.
			'<p><label class="classic">'.
			form::checkbox('display_tag_'.$type,1,$settings[$type]['display_tag']).
			__('Display tags').
			'</label></p>'.
			'</fieldset>';

		echo $res;
	}
}