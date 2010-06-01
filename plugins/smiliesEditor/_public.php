<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of smiliesEditor, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

if (!version_compare(DC_VERSION,'2.2-alpha','>=')) { 
	$core->blog->settings->setNamespace('smilieseditor'); 
	$s =& $core->blog->settings; 
} else { 
	$core->blog->settings->addNamespace('smilieseditor'); 
	$s =& $core->blog->settings->smilieseditor; 
}

$core->addBehavior('publicHeadContent',array('smiliesBehavior','publicHeadContent'));
$core->addBehavior('publicCommentFormAfterContent',array('smiliesBehavior','publicCommentFormAfterContent'));

if ($s->smilies_preview_flag)
{
	$core->addBehavior('publicBeforeCommentPreview',array('smiliesBehavior','publicBeforeCommentPreview'));
}

class smiliesBehavior
{
	public static function publicHeadContent()
	{
		global $core;
		if (!version_compare(DC_VERSION,'2.2-alpha','<')) { 
			$use_smilies = (boolean) $core->blog->settings->system->use_smilies; 
			$smilies_bar_flag = (boolean) $core->blog->settings->smilieseditor->smilies_bar_flag;
		} else { 
			$use_smilies =  (boolean) $core->blog->settings->use_smilies; 
			$smilies_bar_flag = (boolean) $core->blog->settings->smilies_bar_flag;
		}
		
		if ($smilies_bar_flag  && $use_smilies) {
			$js = html::stripHostURL($core->blog->getQmarkURL().'pf=smiliesEditor/js/smile.js');
			echo "\n".'<script type="text/javascript" src="'.$js.'"></script>'."\n";
		}
		else {
			return;
		}
	}
	
	public static function publicCommentFormAfterContent()
	{
		global $core;
		
		if (!version_compare(DC_VERSION,'2.2-alpha','<=')) { 
			$use_smilies = (boolean) $core->blog->settings->system->use_smilies; 
			$smilies_bar_flag = (boolean) $core->blog->settings->smilieseditor->smilies_bar_flag;
		} else { 
			$use_smilies =  (boolean) $core->blog->settings->use_smilies; 
			$smilies_bar_flag = (boolean) $core->blog->settings->smilies_bar_flag;
		}
		if (!$smilies_bar_flag || !$use_smilies) {
			return;
		}
		
		
		$sE = new smiliesEditor($core);
		$smilies = $sE->getSmilies();
		$field = '<p class="field smilies"><label>'.(__('Smilies')).'&nbsp;:</label><span>%s</span></p>';
		
		$res = '';
		foreach ($smilies as $smiley) {
			if ($smiley['onSmilebar']) {
				$res .= ' <img class="smiley" src="'.$sE->smilies_base_url.$smiley['name'].'" alt="'.
				html::escapeHTML($smiley['code']).'" title="'.html::escapeHTML($smiley['code']).'" onclick="javascript:InsertSmiley(\'c_content\', \''.
				html::escapeHTML($smiley['code']).' \');" style="cursor:pointer;" />';
			}
		}
		
		if ($res != '')
		{
			echo sprintf($field,$res);
		}
		
	}
	
	public static function publicBeforeCommentPreview()
	{
		global $core, $_ctx;
		
		$GLOBALS['__smilies'] = context::getSmilies($core->blog);
		$_ctx->comment_preview['content'] = context::addSmilies($_ctx->comment_preview['content']);
	}
}
?>
