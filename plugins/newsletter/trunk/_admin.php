<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Newsletter, a plugin for Dotclear.
# 
# Copyright (c) 2009-2011 Benoit de Marne.
# benoit.de.marne@gmail.com
# Many thanks to Association Dotclear and special thanks to Olivier Le Bris
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
	$core->auth->check('newsletter,contentadmin', $core->blog->id)
	);

// Adding permission
$core->auth->setPermissionType('newsletter',__('manage newsletter'));

if ($core->auth->check('newsletter,contentadmin',$core->blog->id)) {
	// Adding behaviors
	$core->addBehavior('pluginsBeforeDelete', array('dcBehaviorsNewsletter', 'pluginsBeforeDelete'));
	$core->addBehavior('adminAfterPostCreate', array('dcBehaviorsNewsletter', 'adminAutosend'));
	$core->addBehavior('adminAfterPostUpdate', array('dcBehaviorsNewsletter', 'adminAutosendUpdate'));
	
	// Adding import/export behavior
	$core->addBehavior('exportFull',array('dcBehaviorsNewsletter','exportFull'));
	$core->addBehavior('exportSingle',array('dcBehaviorsNewsletter','exportSingle'));
	$core->addBehavior('importInit',array('dcBehaviorsNewsletter','importInit'));
	$core->addBehavior('importFull',array('dcBehaviorsNewsletter','importFull'));
	$core->addBehavior('importSingle',array('dcBehaviorsNewsletter','importSingle'));
	
	// Dynamic method
	$core->rest->addFunction('prepareALetter', array('newsletterRest','prepareALetter'));
	$core->rest->addFunction('sendLetterBySubscriber', array('newsletterRest','sendLetterBySubscriber'));
}
	
// Loading widget
require dirname(__FILE__).'/_widgets.php';
require dirname(__FILE__).'/inc/class.newsletter.mail.php';
require_once dirname(__FILE__).'/inc/class.html2text.php';

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
		global $core;

		// recupere le contenu du billet
		$params = array();
		$params['post_id'] = (integer) $post_id;

		$rs = $core->blog->getPosts($params);

		if (!$rs->isEmpty() && $rs->post_status == 1) {
			newsletterCore::autosendNewsletter((integer)$post_id);
		}
	}

	/**
	* Automatic send after update post
	*/
	public static function adminAutosendUpdate($cur, $post_id)
	{
		global $core;
		
		$newsletter_settings = new newsletterSettings($core);
		
		if($newsletter_settings->getSendUpdatePost()) {
			// recupere le contenu du billet
			$params = array();
			$params['post_id'] = (integer) $post_id;
	
			$rs = $core->blog->getPosts($params);
		
			if (!$rs->isEmpty() && $rs->post_status == 1) {
				newsletterCore::autosendNewsletter((integer)$post_id);
			}
		}
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
			$bk->cur_newsletter->email 			= (string) $line->email;
			$bk->cur_newsletter->regcode 		= (string) $line->regcode;
			$bk->cur_newsletter->state 			= (string) $line->state;
			$bk->cur_newsletter->subscribed 	= (string) $line->subscribed;
			$bk->cur_newsletter->lastsent 		= (string) $line->lastsent;
			$bk->cur_newsletter->modesend 		= (string) $line->modesend;
			
			$bk->cur_newsletter->insert();
		}
	}
}

class newsletterRest 
{
	// Prepare the xml tree
	public static function prepareALetter(dcCore $core,$get,$post) 
	{
		if (empty($get['letterId'])) {
			throw new Exception('No letter selected');
		}		
		$letterId = $get['letterId'];
		
		$nltr = new newsletterLetter($core,$letterId);
		//$nltr->getPostTitle();
		
		$letterTag = new xmlTag();
		$letterTag = $nltr->getXmlLetterById();
				
		// retrieve lists of active subscribers or selected 
		$subscribers_up = array();

		if (empty($get['subscribersId'])) {
			$subscribers_up = newsletterCore::getlist(true);	
		} else {
			$sub_tmp=array();
			$sub_tmp = explode(",", $get['subscribersId']);
			$params['subscriber_id'] = $sub_tmp;
			$params['state'] = "enabled";
			$subscribers_up = newsletterCore::getSubscribers($params);
		}

		if (empty($subscribers_up)) {
			throw new Exception('No subscribers');
		}

		$rsp = new xmlTag();
		$rsp->insertNode($letterTag);

		$subscribers_up->moveStart();		
		while ($subscribers_up->fetch()) { 
			$subscriberTag = new xmlTag('subscriber');
			$subscriberTag->id=$subscribers_up->subscriber_id;
			$subscriberTag->email=$subscribers_up->email;
			$subscriberTag->mode=$subscribers_up->modesend;
			$subscriberTag->body=$nltr->getLetterBody($subscribers_up->modesend);
			$rsp->insertNode($subscriberTag);
		}
	

		// set status to publish
		$status = 1;
		$core->blog->updPostStatus((integer) $letterId,$status);

		// set date of last sending
		$nltr_settings = new newsletterSettings($core);
		$nltr_settings->setDatePreviousSend();
		$nltr_settings->save();

		return $rsp;			
	}
		
	/**
	* Rest send letter
	* - utilisee pour l'envoi manuel : OUI
	* - utilisee pour l'envoi automatique : NON
	* - utilisee pour l'envoi automatique par declenchement manuel : OUI
	* 
	* Actions : 
	* - recuperation les champs dynamiques
	* - selectionne le mode texte ou html
	* - transforme les mots-cles pour chaque abonne
	* - transforme le mot-cle de visualisation online
	* 
	*/	
	public static function sendLetterBySubscriber(dcCore $core,$get,$post)
	{
		// retrieve selected letter
		if (empty($post['p_letter_id'])) {
			throw new Exception('No letter selected');
		}

		// retrieve selected subscriber
		if (empty($post['p_sub_email']) || empty($post['p_sub_id'])) {
			throw new Exception('No subscriber selected');
		}

		if (empty($post['p_letter_subject'])) {
			throw new Exception('No subject found');
		}

		if (empty($post['p_letter_header'])) {
			throw new Exception('No header found');
		}

		if (empty($post['p_letter_footer'])) {
			throw new Exception('No footer found');
		}
		
		if (empty($post['p_sub_mode'])) {
			throw new Exception('No mode found');
		}

		if (empty($post['p_letter_body'])) {
			throw new Exception('No body found');
		}
		
		if($post['p_sub_mode'] == 'text') {
			// define text content
			$letter_content = newsletterLetter::renderingSubscriber($post['p_letter_body'], $post['p_sub_email']);
			$convert = new html2text();
			$convert->set_html($letter_content);
			$convert->labelLinks = __('Links:');
			$letter_content = $convert->get_text();
			
		} else {
			// define html content
			$letter_content = $post['p_letter_header'];
			$letter_content .= newsletterLetter::renderingSubscriber($post['p_letter_body'], $post['p_sub_email']);
			$letter_content .= $post['p_letter_footer'];
			
			//$letter_content = newsletterLetter::mb_wordwrap($letter_content);
		}
			
		// send letter to user
		$mail = new newsletterMail($core);
		$mail->setMessage($post['p_sub_id'],$post['p_sub_email'],$post['p_letter_subject'],$letter_content,$post['p_sub_mode']);
		//throw new Exception('content='.$scontent);
		$mail->send();
		$result = $mail->getState();

		if(!$result) {
			throw new Exception($mail->getError());
		} else {
			$ls_val = newsletterCore::lastsent($post['p_sub_id']);
			if($ls_val != 1)
				throw new Exception($ls_val);
		}
		
		return $result;
	}

} // end class newsletterRest
?>