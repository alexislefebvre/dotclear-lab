<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */

/***************************************************************\
 *  This is Email Optionnel, a plugin for DotClear.            *
 *                                                             *
 *  Copyright (c) 2006-2007                                    *
 *  Oleksandr Syenchuk                                         *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with Email Optionnel (see COPYING.txt);      *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

# WARNING :
# Email Optionnel is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

class emailOptionnelBehaviors
{
	public static function adminBlogPreferencesForm(&$core)
	{
		$emailOptionnel = $core->blog->settings->get('emailoptionnel') ? true : false;
		echo "<fieldset><legend>".__('Optional e-mail address')."</legend>\n".
			"<p><label class=\"classic\">".form::checkbox('emailOptionnel','1',$emailOptionnel)."\n".
			__('Make e-mail address optionnal in comments')."</label></p>\n".
			"</fieldset>\n";
	}
	
	public static function adminBeforeBlogSettingsUpdate(&$blog_settings)
	{
		$emailOptionnel = isset($_POST['emailOptionnel']) && $_POST['emailOptionnel'] == '1' ? true : false;

		$blog_settings->setNameSpace('emailoptionnel');
		$blog_settings->put(
			'emailoptionnel',
			$emailOptionnel,
			'boolean',
			'Make e-mail address optionnal in comments');
		$blog_settings->setNameSpace('system');
	}
	
	public static function publicPrepend(&$core)
	{
		$emailOptionnel = $core->blog->settings->get('emailoptionnel') ? true : false;
		if ($emailOptionnel && empty($_POST['c_mail']) && empty($_POST['preview'])) {
			$_POST['c_mail'] = 'invalid@invalid';
		}
	}

	public static function publicBeforeCommentCreate(&$cur)
	{
		global $core;
		
		$emailOptionnel = $core->blog->settings->get('emailoptionnel') ? true : false;

		if ($emailOptionnel && $cur->comment_email == 'invalid@invalid')
		{
			$_ctx =& $GLOBALS['_ctx'];
			
			# désactive l'affichage du mail dans le template
			$_ctx->comment_preview['mail'] = '';
			
			# n'enregistre pas de mail dans la BDD
			$cur->comment_email = '';
			
			# n'enregistre pas le mail dans le cookie
			if (!empty($_POST['c_remember']))
			{
				$c_cookie = array(
					'name' => $cur->comment_author,
					'mail' => $cur->comment_email,
					'site' => $cur->comment_site);
				$c_cookie = serialize($c_cookie);
				setcookie('comment_info',$c_cookie,strtotime('+3 month'),'/');
			}
		}
	}
}
?>
