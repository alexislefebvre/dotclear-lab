<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Empreinte, a plugin for Dotclear.
# 
# Copyright (c) 2007,2008,2011 Alex Pirine <alex pirine.fr>
# 
# Licensed under the GPL version 2.0 license.
# A copy is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['core']->addBehavior('templateBeforeValue',array('publicEmpreinte','templateBeforeValue'));

class publicEmpreinte
{
	public static $c_info = array();
	
	public static function publicBeforeCommentCreate(&$cur)
	{
		global $core;
		
		if (!empty($_POST['no_empreinte'])
		&& $core->blog->settings->empreinte->allow_disable) {
			return;
		}
		
		empreinte::getUserAgentInfo($browser,$system);
		
		$cur->comment_browser = $browser;
		$cur->comment_system = $system;
	}
	
	public static function coreBlogGetComments(&$c_rs)
	{
		$ids = array();
		while ($c_rs->fetch())
		{
			if (!$c_rs->comment_trackback) {
				$ids[] = $c_rs->comment_id;
			}
		}
		if (empty($ids)) {
			return;
		}
		
		$ids = implode(', ',$ids);
		
		$strReq =
		'SELECT comment_id, comment_browser, comment_system '.
		'FROM '.$c_rs->core->prefix.'comment '.
		'WHERE comment_id  IN ('.$ids.')';
		$rs = $c_rs->core->con->select($strReq);
		
		while ($rs->fetch())
		{
			self::$c_info[$rs->comment_id] = array(
				'browser'=>$rs->comment_browser,
				'system'=>$rs->comment_system
				);
		}
		
		$c_rs->extend('rsExtCommentEmpreinte');
	}
	
	public static function templateBeforeValue(&$core,$id,$attr)
	{	
		if ($id == 'include' && isset($attr['src']) && $attr['src'] == '_head.html') {
			return
			'<?php if ($core->blog->settings->empreinte->allow_disable): ?>'.
			'<script type="text/javascript" src="<?php echo $core->blog->getQmarkURL();?>pf=empreinte/js/post.js"></script>'."\n".
			'<script type="text/javascript">'."\n".
			'//<![CDATA['."\n".
			"var post_no_empreinte_str ='<?php echo html::escapeJS(__('Do not save informations about my browser')); ?>';\n".
			"var post_empreinte_checkbox_style_str = '<?php if (\$core->blog->settings->empreinte->checkbox_style) { echo html::escapeJS(' style=\"'.\$core->blog->settings->empreinte->checkbox_style.'\"'); } ?>';\n".
			'//]]>'."\n".
			'</script>'."\n".
			'<?php endif; ?>';
		}
	}
}
?>