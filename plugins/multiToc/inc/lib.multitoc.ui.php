<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of plugin multiToc for Dotclear 2.
# Copyright (c) 2008 Thomas Bouron and contributors.
#
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class multitocUi
{	
	public static function form($type = 'alpha', $settings)
	{
		global $core;
		
		switch($type)
		{
			case 'cat':
				$legend = __('TOC by category');
				$active = __('Enable TOC by category');
				break;
			case 'tag':
				$legend = __('TOC by tags');
				$active = __('Enable TOC by tags');
				break;
			case 'alpha':
				$legend = __('TOC by alpha list');
				$active = __('Enable TOC by alpha list');
				break;
			default:
				$legend = __('TOC by category');
				$active = __('Enable TOC by category');
				break;
		}

		$res = 
			'<fieldset>'.
			'<legend>'.$legend.'</legend>'.
			'<p class="field">'.
			form::checkbox('enable_'.$type,1,$settings[$type]['enable']).
			'<label class="classic" for="active_"'.$type.'>&nbsp;'.$active.'</label>'.
			'</p>'.
			'<p class="field">'.
			form::checkbox('display_nb_entry_'.$type,1,$settings[$type]['display_nb_entry']).
			'<label class="classic" for="display_nb_entry_"'.$type.'>&nbsp;'.__('Display entry number of each group').'</label>'.
			'</p>'.
			'<p class="field">'.
			form::checkbox('display_date_'.$type,1,$settings[$type]['display_date']).
			'<label class="classic" for="display_date_"'.$type.'>&nbsp;'.__('Display date').'</label>'.
			'</p>'.
			'<p class="field">'.
			'<label class="classic" for="format_date_"'.$type.'">'.__('Format date :').'</label>'.
			form::field('format_date_'.$type,40,255,$settings[$type]['format_date']).
			'</p>'.
			'<p class="field">'.
			form::checkbox('display_author_'.$type,1,$settings[$type]['display_author']).
			'<label class="classic" for="display_author_"'.$type.'>&nbsp;'.__('Display author').'</label>'.
			'</p>'.
			'<p class="field">'.
			form::checkbox('display_nb_com_'.$type,1,$settings[$type]['display_nb_com']).
			'<label class="classic" for="display_nb_com_"'.$type.'>&nbsp;'.__('Display comment number').'</label>'.
			'</p>'.
			'<p class="field">'.
			form::checkbox('display_nb_tb_'.$type,1,$settings[$type]['display_nb_tb']).
			'<label class="classic" for="display_nb_tb_"'.$type.'>&nbsp;'.__('Display trackback number').'</label>'.
			'</p>'.
			'</fieldset>';

		echo $res;
	}	
}