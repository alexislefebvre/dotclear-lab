<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'My Favicon', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2008                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Favicon' (see COPYING.txt);         *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

$core->addBehavior('adminBlogPreferencesHeaders', array('myFavicon', 'adminBlogPreferencesHeaders'));
$core->addBehavior('adminBlogPreferencesForm',array('myFavicon','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('myFavicon','adminBeforeBlogSettingsUpdate'));

class myFavicon
{
	public static function adminBlogPreferencesHeaders()
	{
		return '<script type="text/javascript" src="index.php?pf=myfavicon/blog_pref.js"></script>';
	}
	
	public static function adminBlogPreferencesForm(&$core,&$settings=false)
	{
		# Dotclear <=2.0-beta7 compatibility
		if ($settings === false) {
			$settings = $core->blog->settings;
		}
		
		$favicon_url = $settings->favicon_url;
		
		echo
		'<fieldset><legend>Favicon</legend>'.
		'<p><label class="classic">'.
			form::checkbox('favicon_enable','1',!empty($favicon_url)).
			__('Enable favicon').'</label></p>'.
		'<p id="favicon_config"><label>'.__('Favicon URL:').' '.
			form::field('favicon_url',40,255,html::escapeHTML($favicon_url)).'</label></p>'.
		'</fieldset>';
	}
	
	public static function adminBeforeBlogSettingsUpdate(&$settings)
	{
		$settings->setNameSpace('myfavicon');
		$settings->put('favicon_url',$_POST['favicon_url']);
		$settings->setNameSpace('system');
	}
}
?>
