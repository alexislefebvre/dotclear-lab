<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009 Benoit de Marne
# benoit.de.marne@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

// Rights management
if (!defined('DC_CONTEXT_ADMIN')) { return; }

// Admin menu integration
$_menu['Plugins']->addItem('Newsletter',
	'plugin.php?p=newsletter',
	'index.php?pf=newsletter/icon.png',
	preg_match('/plugin.php\?p='.newsletterPlugin::pname().'(&.*)?$/', $_SERVER['REQUEST_URI']),
	$core->auth->check('usage,admin', $core->blog->id)
	);

// Adding behaviors
$core->addBehavior('pluginsBeforeDelete', array('dcBehaviorsNewsletter', 'pluginsBeforeDelete'));
$core->addBehavior('adminAfterPostCreate', array('dcBehaviorsNewsletter', 'adminAutosend'));
$core->addBehavior('adminAfterPostUpdate', array('dcBehaviorsNewsletter', 'adminAutosend'));

// Adding import/export behavior
$core->addBehavior('exportFull',array('dcBehaviorsNewsletter','exportFull'));
$core->addBehavior('exportSingle',array('dcBehaviorsNewsletter','exportSingle'));
$core->addBehavior('importInit',array('dcBehaviorsNewsletter','importInit'));
$core->addBehavior('importFull',array('dcBehaviorsNewsletter','importFull'));
$core->addBehavior('importSingle',array('dcBehaviorsNewsletter','importSingle'));

// Dynamic method
$core->rest->addFunction('letterGetSubscribersUp', array('newsletterRest','letterGetSubscribersUp'));
$core->rest->addFunction('prepareALetter', array('newsletterRest','prepareALetter'));
$core->rest->addFunction('sendALetter', array('newsletterRest','sendALetter'));
$core->rest->addFunction('sendLetter', array('newsletterRest','sendLetter'));
$core->rest->addFunction('sendLetterBySubscriber', array('newsletterRest','sendLetterBySubscriber'));

// Loading widget
require dirname(__FILE__).'/_widgets.php';
require dirname(__FILE__).'/inc/class.newsletter.mail.php';

// Define behaviors
class dcBehaviorsNewsletter
{
	/**
	* Before delete plugin
	*/
	public static function pluginsBeforeDelete($plugin)
	{
		$name = (string) $plugin['name'];
		if (strcmp($name, newsletterPlugin::pname()) == 0) {
         		require dirname(__FILE__).'/inc/class.newsletter.admin.php';
			newsletterAdmin::uninstall();
		}
	}
    
	/**
	* Automatic send after create post
	*/
	public static function adminAutosend($cur, $post_id)
	{
		newsletterCore::autosendNewsletter();
	}

	/**
	* Behaviors export
	*/
	public static function exportFull($core,$exp)
	{
		$exp->exportTable('newsletter');
	}

	public static function exportSingle($core,$exp,$blog_id)
	{
		$exp->export('newsletter',
	    		'SELECT subscriber_id, blog_id, email, regcode, state, subscribed, lastsent, modesend '.
	    		'FROM '.$core->prefix.'newsletter N '.
	    		"WHERE N.blog_id = '".$blog_id."'"
		);
	}

	/**
	* Behaviors import
	*/
	public static function importInit($bk,$core)
	{
		$bk->cur_newsletter = $core->con->openCursor($core->prefix.'newsletter');
	}

	public static function importSingle($line,$bk,$core)
	{
		if ($line->__name == 'newsletter') {
			
			$cur = $core->con->openCursor($core->prefix.'newsletter');
			
			$bk->cur_newsletter->subscriber_id	= (integer) $line->subscriber_id;
			$bk->cur_newsletter->blog_id 		= (string) $core->blog_id;
			$bk->cur_newsletter->email 		= (string) $line->email;
			$bk->cur_newsletter->regcode 		= (string) $line->regcode;
			$bk->cur_newsletter->state 		= (string) $line->state;
			$bk->cur_newsletter->subscribed 	= (string) $line->subscribed;
			$bk->cur_newsletter->lastsent 	= (string) $line->lastsent;
			$bk->cur_newsletter->modesend 	= (string) $line->modesend;
			
			newsletterCore::add($bk->cur_newsletter->email, (string) $core->blog_id, $bk->cur_newsletter->regcode, $bk->cur_newsletter->modesend);

			$subscriber = newsletterCore::getEmail($bk->cur_newsletter->email);
			if ($subscriber != null) {
				newsletterCore::update($subscriber->subscriber_id, 
					$bk->cur_newsletter->email, 
					$bk->cur_newsletter->state, 
					$bk->cur_newsletter->regcode, 
					$bk->cur_newsletter->subscribed, 
					$bk->cur_newsletter->lastsent, 
					$bk->cur_newsletter->modesend
				);
			}
		}
	}

	public static function importFull($line,$bk,$core)
	{
		if ($line->__name == 'newsletter') {
			
			$bk->cur_newsletter->clean();
			
			$bk->cur_newsletter->subscriber_id	= (integer) $line->subscriber_id;
			$bk->cur_newsletter->blog_id 		= (string) $line->blog_id;
			$bk->cur_newsletter->email 		= (string) $line->email;
			$bk->cur_newsletter->regcode 		= (string) $line->regcode;
			$bk->cur_newsletter->state 		= (string) $line->state;
			$bk->cur_newsletter->subscribed 	= (string) $line->subscribed;
			$bk->cur_newsletter->lastsent 	= (string) $line->lastsent;
			$bk->cur_newsletter->modesend 	= (string) $line->modesend;
			
			$bk->cur_newsletter->insert();
		}
	}
}

class newsletterRest 
{
	
	// select
	public static function prepareALetter(dcCore $core,$get,$post) {

		if (empty($get['letterId'])) {
			throw new Exception('No letter selected');
		}		
		$letterId = $get['letterId'];

		$nltr = new newsletterLetter($core,$letterId);
		//$nltr->getPostTitle();
		
		$letterTag = new xmlTag();
		$letterTag = $nltr->getXmlLetterById();
		
		/*$params = array();
		$params['post_type'] = 'newsletter';
		$params['post_id'] = $letterId;
	
		$post = $core->blog->getPosts($params);
		
		if ($post->isEmpty())
		{
			throw new Exception(__('This newsletter does not exist.'));
		}
		else
		{
			$post_id = $post->post_id;
			$post_dt = date('Y-m-d H:i',strtotime($post->post_dt));
			$post_format = $post->post_format;
			$post_password = $post->post_password;
			$post_url = $post->post_url;
			$post_lang = $post->post_lang;
			$post_title = $post->post_title;
			$post_excerpt = $post->post_excerpt;
			$post_excerpt_xhtml = $post->post_excerpt_xhtml;
			$post_content = $post->post_content;
			$post_content_xhtml = $post->post_content_xhtml;
			$post_status = $post->post_status;
			$post_position = (integer) $post->post_position;
			$post_open_comment = (boolean) $post->post_open_comment;
			$post_open_tb = (boolean) $post->post_open_tb;
		}
		*/

		// retrieve lists of active subscribers
		$subscribers_up = array();
		$subscribers_up = newsletterCore::getlist(true);

		if (empty($subscribers_up)) {
			throw new Exception('No subscribers');
		}

		$rsp = new xmlTag();

		$rsp->insertNode($letterTag);

		$subscribers_up->moveStart();		
		while ($subscribers_up->fetch()) { 
			//$core->blog->dcNewsletter->addError($subscribers_up->email);
			$subscriberTag = new xmlTag('subscriber');
			
			$subscriberTag->id=$subscribers_up->subscriber_id;
			$subscriberTag->email=$subscribers_up->email;
			
			//$subscriberTag->letter_id=$letterId;
			//$subscriberTag->letter_title=$post_title;
			
			$rsp->insertNode($subscriberTag);
		}		
		return $rsp;			
		
	}
	
	
	
	/**
	* Rest send letter
	*/	
	public static function sendLetterBySubscriber(dcCore $core,$get,$post)
	{
		// retrieve selected letter
		if (empty($post['p_letter_id'])) {
			throw new Exception('No letter selected');
		}

		// retrieve selected subscriber
		if (empty($post['p_sub_email'])) {
			throw new Exception('No subscriber selected');
		}

		if (empty($post['p_letter_subject'])) {
			throw new Exception('No subject found');
		}

		if (empty($post['p_letter_body'])) {
			throw new Exception('No body found');
		}
		
		
		
/*				p_sub_id: p_sub_id, 
				p_sub_email: p_sub_email, 
				p_letter_id: p_letter_id, 
				p_letter_subject: p_letter_subject, 
				p_letter_header: p_letter_header,
				p_letter_footer: p_letter_footer,
				p_letter_body: p_letter_body
*/		
		// send letter to user
		$mail = new newsletterMail($core);
		$mail->setMessage($post['p_sub_id'],$post['p_sub_email'],$post['p_letter_subject'],$post['p_letter_body'],'html');
		$mail->send();
		$result = $mail->getState();

		if(!$result) {
			throw new Exception($mail->getError());
		}
		
		return $result;
	}

	/**
	* Rest send letter
	*/	
	public static function sendSubscriberLetter(dcCore $core,$get,$post) {
		
		/*if (empty($post['letterId'])) {
			throw new Exception('No letter selected');
		}*/
		//$core->meta = new dcMeta($core);
		//$core->newsletter = new dcNewsletter($core);
		//$redo = $core->gallery->refreshGallery($post['galId']);
		//$redo = $core->newsletter->sendLetter($post['letterId']);
		//$result = $core->newsletter->sendSubscriberLetter();
		//$result = newsletterSubscribersList::sendSubscriberLetter();
		
		$result = newsletterSubscribersList::sendSubscriberLetter($post['letterId']);
		
		
		//$redo = true;
		/*if ($redo) {
			$rsp = new xmlTag();
			$redoTag = new xmlTag('redo');
			$redoTag->value="1";
			$rsp->insertNode($redoTag);
			return $rsp;
		} else {*/
			return $result;
		//}
	}


} // end class newsletterRest
?>
