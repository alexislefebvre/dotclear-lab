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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$core->addBehavior('adminBlogPreferencesHeaders', array('myFavicon', 'adminBlogPreferencesHeaders'));
$core->addBehavior('adminBlogPreferencesForm',array('myFavicon','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('myFavicon','adminBeforeBlogSettingsUpdate'));

class myFavicon
{
	public static function adminBlogPreferencesHeaders()
	{
		return '<script type="text/javascript" src="index.php?pf=myfavicon/blog_pref.js"></script>';
	}
	
	public static function adminBlogPreferencesForm($core,$settings=false)
	{
		# Dotclear <=2.0-beta7 compatibility
		if ($settings === false) {
			$settings = $core->blog->settings;
		}
		
		if (version_compare(DC_VERSION,'2.2-beta1','<')) {
			$favicon_url = $settings->favicon_url;
			$favicon_ie_url = $settings->favicon_ie_url;
		}
		else {
			$favicon_url = $settings->myfavicon->favicon_url;
			$favicon_ie_url = $settings->myfavicon->favicon_ie_url;
		}
		
		echo
		'<div class="fieldset"><h4>Favicon</h4>'.
		'<p><label class="classic">'.
			form::checkbox('favicon_enable','1',(!empty($favicon_url) || !empty($favicon_ie_url))).
			__('Enable favicon').'</label></p>'.
		'<div id="favicon_config">'.
		'<p><label>'.__('Favicon URL for IE:').' '.
			form::field('favicon_ie_url',40,255,html::escapeHTML($favicon_ie_url)).'</label></p>'.
		'<p class="form-note warn">'
			.__('Please note, IE compatibility works only with ".ico" format.').'</p>'.
		'<p><label>'.__('Favicon URL:').' '.
			form::field('favicon_url',40,255,html::escapeHTML($favicon_url)).'</label></p>'.
		'</div></div>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$favicon_url = empty($_POST['favicon_url']) ? '' : $_POST['favicon_url'];
		$favicon_ie_url = empty($_POST['favicon_ie_url']) ? '' : $_POST['favicon_ie_url'];
		
		if (version_compare(DC_VERSION,'2.2-beta1','<')) {
			$settings->setNameSpace('myfavicon');
			$settings->put('favicon_url',$favicon_url);
			$settings->put('favicon_ie_url',$favicon_ie_url);
			$settings->setNameSpace('system');
		}
		else {
			$settings->addNameSpace('myfavicon');
			$settings->myfavicon->put('favicon_url',$favicon_url);
			$settings->myfavicon->put('favicon_ie_url',$favicon_ie_url);
			$settings->addNameSpace('system');
		}
	}
}
?>
