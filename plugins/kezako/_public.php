<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of kezako, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Jean-Claude Dubacq, Franck Paul and contributors
# carnet.franck.paul@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

$core->blog->settings->addNameSpace('kezako');
if ($core->blog->settings->kezako->kezako_usecat) {
	$core->tpl->addValue('CategoryDescription',array('kezakoPublic','CategoryDescription'));
}

$core->tpl->addBlock('TagDescription',array('kezakoPublic','TagDescription'));

class kezakoPublic {
	
	public static function CategoryDescription($attr) {

		if (isset($attr['force_lang'])) {
			$langarray='null';
		} else {
			$langarray='(isset($core->lang_array)?$core->lang_array:null)';
		}

		$f = $GLOBALS['core']->tpl->getFilters($attr);
		$p='echo '.
			sprintf($f,'(kezakoPublic::getDescription($_ctx->categories->cat_id,\'category\',\'cat\',$core->blog->settings->system->lang,'.$langarray.'))').';';

		return '<?php '.$p.' ?>';
	}

	public static function TagDescription($attr,$content) {

		if (isset($attr['force_lang'])) {
			$langarray='null';
		} else {
			$langarray='$core->lang_array';
		}

		$p='$a=kezakoPublic::getDescription($_ctx->meta->meta_id,\'metadata\',$_ctx->meta->meta_type,$core->blog->settings->system->lang,'.$langarray.');';
		$p.="if (\$a):\n echo \$a;\nelse:\n?>".$content."<?php endif;";

		return '<?php '.$p.' ?>';
	}

	public static function getDescription($id,$type,$subtype,$lang,$langarray=null) {

		global $core;
		global $_ctx;

		$blog_id=$core->blog->id;

		$whereReq=
            'WHERE thing_type = \''.$core->con->escape($type).
            '\' AND thing_subtype = \''.$core->con->escape($subtype);

		if ($langarray == null) {
			$whereReq.='\' AND thing_lang = \''.$core->con->escape($lang);
		}

		$whereReq.=
            '\' AND thing_id = \''.$core->con->escape($id).
            '\' AND blog_id = \''.$core->con->escape($core->blog->id).'\'';

		$strReq='FROM '.$core->prefix.'kezako '.$whereReq;

		$rs = $core->con->select('SELECT thing_lang, thing_text '.$strReq);
		if (!$rs || $rs->isEmpty()) {
			// By default, behave as if the plugin were not there
			if ($type=='category') {
				return $_ctx->categories->cat_desc;
			}
			return '';
		}

		if ($langarray == null) {
			$desc=$rs->thing_text;
			return $desc;
		}

		$langs=array();
		while ($rs->fetch()) {
			$langs[$rs->thing_lang]=$rs->thing_text;
		}
		foreach ($langarray as $k => $v) {
			if (isset($langs[$v])) {
				return ($langs[$v]);
			}
		}
		return '';
	}
}
?>