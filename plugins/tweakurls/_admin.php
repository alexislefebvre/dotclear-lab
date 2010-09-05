<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Tweak URLs', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2010                                         *
 *  xave and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Favicon' (see COPYING.txt);         *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$core->addBehavior('adminBlogPreferencesForm',array('tweakurlsAdminBehaviours','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('tweakurlsAdminBehaviours','adminBeforeBlogSettingsUpdate'));
$core->addBehavior('adminAfterPostCreate',array('tweakurlsAdminBehaviours','adminAfterPostSave'));
$core->addBehavior('adminAfterPageUpdate',array('tweakurlsAdminBehaviours','adminAfterPostSave'));
$core->addBehavior('adminAfterPageCreate',array('tweakurlsAdminBehaviours','adminAfterPostSave'));
$core->addBehavior('adminAfterPostUpdate',array('tweakurlsAdminBehaviours','adminAfterPostSave'));
$core->addBehavior('adminAfterCategoryCreate',array('tweakurlsAdminBehaviours','adminAfterCategorySave'));
$core->addBehavior('adminAfterCategoryUpdate',array('tweakurlsAdminBehaviours','adminAfterCategorySave'));
$core->addBehavior('adminPostsActionsCombo',array('tweakurlsAdminBehaviours','adminPostsActionsCombo'));
$core->addBehavior('adminPagesActionsCombo',array('tweakurlsAdminBehaviours','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('tweakurlsAdminBehaviours','adminPostsActions'));
$core->addBehavior('adminPostsActionsContent',array('tweakurlsAdminBehaviours','adminPostsActionsContent'));

class tweakurlsAdminBehaviours
{
	public static function tweakurls_combo()
	{
		return array(
			__('default mode') => 'default',
			__('clean all diacritics') => 'nodiacritic',
			__('Lowercase') => 'lowercase',
			__('Much more tidy') => 'mtidy'
		);
	}
	
	public static function adminBlogPreferencesForm($core,$settings)
	{
		$tweekurls_settings = tweakurlsSettings($GLOBALS['core']);
		
		# URL modes
		$tweakurls_combo = self::tweakurls_combo();
		echo
		'<fieldset><legend>Tweak URLs</legend>'.
		'<div>'.
		'<p><label>'.
		__('Posts URL type:')." ".
		form::combo('tweakurls_posturltransform',$tweakurls_combo,$tweekurls_settings->tweakurls_posturltransform).
		'</label></p>'.
		'<p><label>'.
		__('Categories URL type:')." ".
		form::combo('tweakurls_caturltransform',$tweakurls_combo,$tweekurls_settings->tweakurls_caturltransform).
		'</label></p>'.
		'</div>'.
		'</fieldset>';
	}
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$tweekurls_settings = tweakurlsSettings($GLOBALS['core']);
		$tweekurls_settings->put('tweakurls_posturltransform',$_POST['tweakurls_posturltransform']);
		$tweekurls_settings->put('tweakurls_caturltransform',$_POST['tweakurls_caturltransform']);
	}

	public static function adminAfterPostSave ($cur,$id=null)
	{
		global $core;
		
		$tweekurls_settings = tweakurlsSettings($core);
		$posturltransform = $tweekurls_settings->tweakurls_posturltransform;
		
		if (isset($_POST['post_url'])||empty($_REQUEST['id']))
		{
			switch ($posturltransform)
			{
				case 'nodiacritic':
					$cur->post_url = text::str2URL($cur->post_url);
					break;
				case 'lowercase':
					$cur->post_url = strtolower(text::str2URL($cur->post_url));
					break;
				case 'mtidy':
					$wildcard = (string) $tweekurls_settings->tweakurls_mtidywildcard;
					$remove = (string) $tweekurls_settings->tweakurls_mtidyremove;
					$cur->post_url = self::mtidy($cur->post_url,$wildcard,$remove);
					break;
			}
			$core->blog->updPost($id,$cur);
		}
	}

	public static function adminAfterCategorySave ($cur,$id=null)
	{
		global $core;
		
		$tweekurls_settings = tweakurlsSettings($core);
		$caturltransform = $tweekurls_settings->tweakurls_caturltransform;
		
		if (isset($_POST['cat_url'])||empty($_REQUEST['id']))
		{
			switch ($caturltransform)
			{
				case 'nodiacritic':
					$cur->cat_url = text::str2URL($cur->cat_url);
					break;
				case 'lowercase':
					$cur->cat_url = strtolower(text::str2URL($cur->cat_url));
					break;
				case 'mtidy':
					$wildcard = (string) $tweekurls_settings->tweakurls_mtidywildcard;
					$remove = (string) $tweekurls_settings->tweakurls_mtidyremove;
					$cur->cat_url = self::mtidy($cur->cat_url,$wildcard,$remove);
					break;
			}
			$core->blog->updCategory($id,$cur);
		}
	}
	
	public static function adminPostsActionsCombo($combo_action)
	{
		global $core;
		
		if ($core->auth->check('admin',$core->blog->id))
		{
			$combo_action[0][__('Change')][__('Clean URLs')] = 'cleanurls';
		}
	}
	
	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'confirmcleanurls' && $core->auth->check('admin',$core->blog->id)
		&& !empty($_POST['posturltransform']) && $_POST['posturltransform'] != 'default')
		{
			$tweekurls_settings = tweakurlsSettings($core);
			$wildcard = (string) $tweekurls_settings->tweakurls_mtidywildcard;
			$remove = (string) $tweekurls_settings->tweakurls_mtidyremove;
			
			try
			{
				while ($posts->fetch())
				{
					$cur = $core->con->openCursor($core->prefix.'post');
					$cur->post_url = $posts->post_url;

					switch ($_POST['posturltransform'])
					{
						case 'nodiacritic':
							$cur->post_url = text::str2URL($cur->post_url);
							break;
						case 'lowercase':
							$cur->post_url = strtolower(text::str2URL($cur->post_url));
							break;
						case 'mtidy':
							$cur->post_url = self::mtidy($cur->post_url,$wildcard,$remove);
							break;
					}
					if ($cur->post_url != $posts->post_url) {
						$cur->update('WHERE post_id = '.(integer) $posts->post_id);
					}
				}
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}
	
	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action == 'cleanurls')
		{
			echo
			'<form action="posts_actions.php" method="post">'.
			'<h2>Tweak URLs</h2>'.
			'<p>'.
			__('By changing the URLs, you understand that the old URLs will never be accessible.').'<br />'.
			__('Internal links between posts will not work either.').'<br />'.
			__('The changes are irreversible.').'</p>'.
			'<p><label>'.__('Posts URL type:').' '.
			form::combo('posturltransform',self::tweakurls_combo(),'default').
			'</label></p>'.
			'<p>'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'confirmcleanurls').
			'<input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
	}
	
	/**
	* Huge clean of a string
	*
	* Returns lowercase alphanumeric string, 
	* with last exotic chars $remove replaced by $wildcard.
	*
	* @author JcDenis
	* @update 2010-09-05 12:00:00
	*
	* @param string	$str	String to clean
	* @param string	$wildcard	Char to use for replacement
	* @param string	$remove	Last exotic chars to replace
	* @return string
	*/
	public static function mtidy($str,$wildcard='-',$remove="_ ':[]-")
	{
		$quoted_wildcard = preg_quote($wildcard);
		$quoted_remove = preg_quote($remove);
		// Tidy lowercase
		$str = strtolower(text::str2URL($str));
		// Replace last exotic $remove chars by $wildcard
		$str = preg_replace('/['.$quoted_remove.']/',$wildcard,$str);
		// Remove double $wildcard
		$str = preg_replace('/(['.$quoted_wildcard.']{2,})/',$wildcard,$str);
		// Remove end $wildcard
		return rtrim($str,$wildcard);
	}
}
?>