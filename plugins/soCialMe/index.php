<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}

	'reader' => array(
		'title' => __('Reader'),
		'description' => __('Add feeds and other stream from social networks to your blog.'),
		'ns' => 'soCialMeReader',
		'parts' => array(
			'stream' => __('Streams'),
			'service' => __('Services'),
			'setting' => __('Settings')
		),
		'common' => array('service','setting')
	)
		<p><label class="classic">'.form::checkbox(array('s_'.$page),'1',$s[$page]).__('Enable this part').'</label></p>
		</div></div>';