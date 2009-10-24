<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is not part of DotClear.
# Copyright (c) 2005 Alexandre LEGOUT aka LAlex and gtraxx. All rights
# reserved.
#
# dcGeshi is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# dcGeshi is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with dcGeshi; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# dcGeshi uses a free software under the GNU General Public License. See license
# infos on the class.dc.geshi.php file
#
# dcGeshi uses a free icon under the creativecommons. See license
# infos on the iconpack , http://www.clashdesign.net

# ***** END LICENSE BLOCK *****
	if (!defined('DC_RC_PATH')) { return; }
	//$GLOBALS['core']->tpl->setPath($GLOBALS['core']->tpl->getPath(), dirname(__FILE__).'/_publicgeshi');
	//$__autoload['dcGeshi'] = DC_ROOT.'/inc/public/rs.extension.php';
	require_once(DC_ROOT.'/plugins/dcGeshi/class.geshi.php');
	
	$core->addBehavior('coreBlogGetPosts',array('rsExtendGeshi','coreBlogGetPosts'));
	$core->addBehavior('coreBlogGetComments',array('rsExtendGeshi','coreBlogGetComments'));

	class rsExtendGeshi {

		public static function coreBlogGetPosts(&$rs)
		{
			$rs->extend('rsExtGeshiPost');
		}
		
		public static function coreBlogGetComments(&$rs)
		{
			$rs->extend('rsExtGeshiComment');
		}
		
	}
	
	class CommonGeshi {
		public static function parseCode($match) {
			$curGeshi = new Geshi(html_entity_decode(trim($match[2])), $match[1]);
//			$curGeshi->enable_classes();
			$curGeshi->set_header_type(GESHI_HEADER_NONE);
			return "<code class=\"" . $match[1] . "\">" . $curGeshi->parse_code() . "</code>";
		}
	}

	class rsExtGeshiPost extends rsExtPostPublic
	{
		public static function getContent(&$rs,$absolute_urls=false)
		{

			$c = parent::getContent($rs,$absolute_urls);
			
			return preg_replace_callback("#<" . "pre>\s*?\[(\w*?)](.*?)</" . "pre>#is", array('CommonGeshi', 'parseCode'), $c);
			
		}
		
	}
	
	class rsExtGeshiComment extends rsExtCommentPublic
	{
		public static function getContent(&$rs,$absolute_urls=false)
		{

			$c = parent::getContent($rs,$absolute_urls);
			
			return preg_replace_callback("#<" . "pre>\s*?\[(\w*?)](.*?)</" . "pre>#is", array('CommonGeshi', 'parseCode'), $c);
			
		}
		
	}
	//'<script type="text/javascript" src="'.$core->blog->getQmarkURL().'pf=js/plop.js'.'"></script>'
	$url = $core->blog->getQmarkURL().'pf='.basename(dirname(__FILE__));
	$GLOBALS['core']->tpl->setPath($GLOBALS['core']->tpl->getPath(), dirname(__FILE__).'/_publicgeshi');
	?>