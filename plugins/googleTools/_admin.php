<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Google Stuff', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2008                                         *
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

$core->addBehavior('adminBlogPreferencesForm',array('googlestuffAdminBehaviours','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('googlestuffAdminBehaviours','adminBeforeBlogSettingsUpdate'));

class googlestuffAdminBehaviours
{
	public static function adminBlogPreferencesForm($core,$settings)
	{
		echo
		'<fieldset><legend>Google Stuff</legend>'.
		'<div class="two-cols"><div class="col">'.
		'<p><label>'.
		__('Google Analytics UACCT (ID):')." ".
		form::field('googlestuff_uacct',25,50,$settings->googlestuff_uacct,3).
		'</label></p>'.
		'</div><div class="col">'.
		'<p><label>'.
		__('Google Webmaster Tools verification:')." ".
		form::field('googlestuff_verify',50,100,$settings->googlestuff_verify,3).
		'</label></p>'.
		'</div></div>'.
		'</fieldset>';
	}
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->setNameSpace('googlestuff');
		$settings->put('googlestuff_uacct',empty($_POST['googlestuff_uacct'])?"":$_POST['googlestuff_uacct'],'string');
		$settings->put('googlestuff_verify',empty($_POST['googlestuff_verify'])?"":$_POST['googlestuff_verify'],'string');
		$settings->setNameSpace('system');
	}
	
}
?>
