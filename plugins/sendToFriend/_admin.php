<?php
/* BEGIN LICENSE BLOCK
This file is part of SendToFriend, a plugin for Dotclear.

Julien Appert
brol contact@brol.info

Licensed under the GPL version 2.0 license.
A copy of this license is available in LICENSE file or at
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
END LICENSE BLOCK */

if (!defined('DC_CONTEXT_ADMIN')) {return;}

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

$_menu['Plugins']->addItem(__('Send to friend'),'plugin.php?p=sendToFriend','index.php?pf=sendToFriend/icon.png',
				preg_match('/plugin.php\?p=sendToFriend(&.*)?$/',$_SERVER['REQUEST_URI']),
				$core->auth->check('usage,contentadmin',$core->blog->id));
				
	$core->addBehavior('pluginsBeforeDelete', array('sendtofriend_behavior', 'pluginsBeforeDelete'));				
				
class sendtofriend_behavior {						
   public static function pluginsBeforeDelete($plugin) {
	        global $core;
		if($plugin['id'] == 'sendToFriend'){
			$sql = 'DELETE FROM '.$core->prefix.'version WHERE module = \'sendtofriend\' ';
				$core->con->execute($sql);	
			$sql = 'DELETE FROM '.$core->prefix.'setting WHERE setting_ns = \'sendtofriend\' ';
				$core->con->execute($sql);		
			return true;
		}
	    }		
}						
?>
