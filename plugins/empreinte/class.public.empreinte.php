<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Empreinte', a plugin for Dotclear 2               *
 *                                                             *
 *  Copyright (c) 2007,2008                                    *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'Empreinte' (see COPYING.txt);          *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_RC_PATH')) { return; }

$GLOBALS['core']->addBehavior('templateBeforeValue',array('publicEmpreinte','templateBeforeValue'));

class publicEmpreinte
{
	public static $c_info = array();
	
	public static function publicBeforeCommentCreate(&$cur)
	{
		global $core;
		
		if (!empty($_POST['no_empreinte'])
		&& $core->blog->settings->empreinte_allow_disable) {
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
			'<?php if ($core->blog->settings->empreinte_allow_disable): ?>'.
			'<script type="text/javascript" src="<?php echo $core->blog->getQmarkURL();?>pf=empreinte/js/post.js"></script>'."\n".
			'<script type="text/javascript">'."\n".
			'//<![CDATA['."\n".
			"var post_no_empreinte_str ='<?php echo html::escapeJS(__('Do not save informations about my browser')); ?>';\n".
			"var post_empreinte_checkbox_style_str = '<?php if (\$core->blog->settings->empreinte_checkbox_style) { echo html::escapeJS(' style=\"'.\$core->blog->settings->empreinte_checkbox_style.'\"'); } ?>';\n".
			'//]]>'."\n".
			'</script>'."\n".
			'<?php endif; ?>';
		}
	}
}
?>