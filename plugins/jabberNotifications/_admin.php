<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */

/***************************************************************\
 *  This is Jabber Notifications, a plugin for Dotclear 2      *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk, Olivier Tétard and contributors.       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along Jabber Notifications (see COPYING.txt);      *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(__('Jabber notifications'),'plugin.php?p=jabberNotifications',
			   'index.php?pf=jabberNotifications/icon.png',
			   preg_match('/plugin.php\?p=jabberNotifications(&.*)?$/',$_SERVER['REQUEST_URI']),
			   $core->auth->isSuperAdmin());

$core->addBehavior('adminUserHeaders', array('privateJabberNotifications', 'javascriptHelpers'));
$core->addBehavior('adminPreferencesHeaders', array('privateJabberNotifications', 'javascriptHelpers'));
$core->addBehavior('adminPreferencesForm', array('privateJabberNotifications', 'adminPreferencesForm'));
$core->addBehavior('adminUserForm', array('privateJabberNotifications', 'adminUserForm'));
$core->addBehavior('adminBeforeUserUpdate', array('privateJabberNotifications', 'adminBeforeUserUpdate'));
$core->addBehavior('adminBeforeUserCreate', array('privateJabberNotifications', 'adminBeforeUserUpdate'));

class privateJabberNotifications
{
	public static function adminUserForm()
	{
		global $core,$user_options;

		$jn_mode = 'user_settings';
		$jn_notify = empty($user_options['jn_notify']) ? 'never' : $user_options['jn_notify'];
		$jn_jabberid = empty($user_options['jn_jabberid']) ? '' : $user_options['jn_jabberid'];

		include dirname(__FILE__).'/forms.php';
		echo $jn_forms['user_settings'];
	}

	public static function adminPreferencesForm(&$core)
	{
		global $user_options;

		$jn_mode = 'user_settings';
		$jn_notify = empty($user_options['jn_notify']) ? 'never' : $user_options['jn_notify'];
		$jn_jabberid = empty($user_options['jn_jabberid']) ? '' : $user_options['jn_jabberid'];
		
		include dirname(__FILE__).'/forms.php';
		echo $jn_forms['user_settings'];
	}

	public static function adminBeforeUserUpdate(&$cur)
	{
		global $core,$user_options;

		
		// Vérification de la présence des paramètres
		if (empty($cur->user_options['jn_notify']))
		{
			$user_options['jn_notify'] = 'never';
			$user_options['jn_jabberid'] = '';
		}
		
		if ($_POST['jn_notify'] == 'never')
		{
			$user_options['jn_notify'] = 'never';
		}
		elseif (isset($_POST['jn_notify']))
		{
			$user_options['jn_notify'] = $_POST['jn_notify'];
			$user_options['jn_jabberid'] = $_POST['jn_jabberid'];
		}
		$cur->user_options = new ArrayObject($user_options);
		
	
		# Notifications are disabled, nothing to do
		if ($user_options['jn_notify'] == 'never') {
			return;
		}
		# Only JabberID is required
		else
		{
			if (empty($user_options['jn_jabberid'])) {
				throw new Exception(__('Jabber ID is empty'));
			}
			if (!text::isEmail($user_options['jn_jabberid'])) {
				throw new Exception(__('Invalid Jabber ID'));
			}
			if (!in_array($user_options['jn_notify'],array('entries','blog','blogs','all'))) {
				throw new Exception(__('Invalid notification status'));
			}
		}
	}

	public static function javascriptHelpers()
	{
		return '<script type="text/javascript" src="index.php?pf=jabberNotifications/private_helpers.js"></script>';
	}
}
?>