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

$core->addBehavior('publicHeadContent',array('smiliesBehavior','publicHeadContent'));
$core->addBehavior('publicCommentFormAfterContent',array('smiliesBehavior','publicCommentFormAfterContent'));
if ($core->blog->settings->smilies_preview_flag)
{
	$core->addBehavior('publicBeforeCommentPreview',array('smiliesBehavior','publicBeforeCommentPreview'));
}

class smiliesBehavior
{
	public static function publicHeadContent()
	{
		global $core;
		
		if (!$core->blog->settings->smilies_bar_flag) {
			return;
		}
		
		if (!$core->blog->settings->use_smilies) {
			return;
		}
		
		$js = html::stripHostURL($core->blog->getQmarkURL().'pf=smiliesEditor/js/smile.js');
	
		echo "\n".'<script type="text/javascript" src="'.$js.'"></script>'."\n";
	}
	
	public static function publicCommentFormAfterContent()
	{
		global $core;
		
		$s = new smiliesEditor($core);
		$smilies = $s->getSmilies();
		
		$field = '<p class="field smilies"><label>'.(__('Smilies')).'&nbsp;:</label><span>%s</span></p>';
		
		if (!$core->blog->settings->smilies_bar_flag) {
			return;
		}
		
		if (!$core->blog->settings->use_smilies) {
			return;
		}
		
		$res = '';
		foreach ($smilies as $smiley) {
			if ($smiley['onSmilebar']) {
				$res .= ' <img class="smiley" src="'.$s->smilies_base_url.$smiley['name'].'" alt="'.
				html::escapeHTML($smiley['code']).'" title="'.html::escapeHTML($smiley['code']).'" onclick="javascript:InsertSmiley(\'c_content\', \''.
				html::escapeHTML(str_replace('\'', '\\\'', str_replace('\\', '\\\\', $smiley['code']))).'\');" style="cursor:pointer;" />';
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
