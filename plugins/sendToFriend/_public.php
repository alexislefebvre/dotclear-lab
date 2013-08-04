<?php
/* BEGIN LICENSE BLOCK
This file is part of SendToFriend, a plugin for Dotclear.

Julien Appert
brol contact@brol.info

Licensed under the GPL version 2.0 license.
A copy of this license is available in LICENSE file or at
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
END LICENSE BLOCK */

if (!defined('DC_RC_PATH')) {return;}

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/public');

$core->url->register('envoyer','envoyer','^envoyer/([0-9]*)',array('tplEnvoyer','load'));
class sdf{
	public $id = null;
	public $title = '';
	public $url = '';
	public $abstract = '';
	public $senderName = '';
	public $senderEmail = '';
	public $receiverName = '';
	public $receiverEmail = '';	
	public $error = array();
	public $message = array();
}
class tplEnvoyer extends dcUrlHandlers
{
	public static function load($args) {
		global $core, $_ctx;

		$settings = new dcSettings($core,$core->blog->id);
		$settings->addNamespace('sendtofriend');
		
		if (empty($args) || $args == '') self::p404();
		
		$GLOBALS['envoyer'] = array();
		$aParams = array();
		$aParams['post_id'] = (int)$args;
    $aParams['post_type'] = '';
		$rs = $core->blog->getPosts($aParams);
		if(!$rs->isEmpty()){
			while ($rs->fetch()) {
					$_ctx->sdf = new sdf();					
					$_ctx->sdf->id = (int)$args;
					$_ctx->sdf->title = $rs->post_title;
					$_ctx->sdf->url = $rs->getURL();
					$_ctx->sdf->abstract = tplEnvoyer::getAbstract($rs->post_excerpt_xhtml,$rs->post_content_xhtml);
					if(isset($_POST['submit'])){
						try{					
								if(strlen(trim($_POST['yourName']))==0){
									$_ctx->sdf->error[] =  __('Your name is required');
								}
								if(strlen(trim($_POST['yourEmail']))==0){
									$_ctx->sdf->error[] =  __('Your email is required');
								}	
								if(strlen(trim($_POST['yourFriendName']))==0){
									$_ctx->sdf->error[] =  __('Your friend name is required');
								}
								if(strlen(trim($_POST['yourFriendEmail']))==0){
									$_ctx->sdf->error[] =  __('Your friend email is required');
								}
								if(count($_ctx->sdf->error)>0){
									throw new Exception();
								}
								else{
										$_ctx->sdf->senderName = $_POST['yourName'];
										$_ctx->sdf->senderEmail = $_POST['yourEmail'];
										$_ctx->sdf->receiverName = $_POST['yourFriendName'];
										$_ctx->sdf->receiverEmail = $_POST['yourFriendEmail'];

										$headers = array(
											'From: '.mail::B64Header($_POST['yourName']).' <'.$_POST['yourEmail'].'>',
											'Content-Type: text/plain; charset=UTF-8;',
											'X-Originating-IP: '.http::realIP(),
											'X-Mailer: Dotclear',
											'X-Blog-Id: '.mail::B64Header($core->blog->id),
											'X-Blog-Name: '.mail::B64Header($core->blog->name),
											'X-Blog-Url: '.mail::B64Header($core->blog->url)
										);
																				
										$subject = mail::B64Header(tplEnvoyer::EnvoyerSearchReplace($settings->sendtofriend->get('sendtofriend_subject')));
										$sBody = tplEnvoyer::EnvoyerSearchReplace($settings->sendtofriend->get('sendtofriend_content'));
										$aFriendEmails = explode(';', $_POST['yourFriendEmail']);	
										if(count($aFriendEmails)==1){
											$aFriendEmails = explode(',', $_POST['yourFriendEmail']);	
										}
										foreach ($aFriendEmails as $email) {
											mail::sendMail($email,$subject,$sBody,$headers);
										}						
										$_ctx->sdf->message = array(__('email sent'));	
								}
							}
							catch (Exception $e)
							{
								if(strlen($e->getMessage())>0){
									$_ctx->sdf->message = array(__('email not sent'));
								}
								else{
									$_ctx->sdf->message = $_ctx->sdf->error;
								}
							}						
					}
			}
		}
		else{
			self::p404();
		}		
		$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
		self::serveDocument('sendtofriend.html');
		exit;				
	}
	
	public function getAbstract($sExcerpt, $sContent){
		global $core, $_ctx;	
		$settings = new dcSettings($core,$core->blog->id); 
		$settings->addNamespace('sendtofriend');
		$sContenuFinal = ''; 
		if($settings->sendtofriend->get('sendtofriend_abstractType') == 'firstWords'){
			$iNb = $settings->sendtofriend->get('sendtofriend_firstWords');
			$sContenu = str_replace('\"','&quot;',strip_tags($sExcerpt.' '.$sContent));
			$aContenu = explode(' ',$sContenu);
			if(count($aContenu)>$iNb)
			{
				$sContenuFinal = '';
				foreach($aContenu as $iKey=>$sContenu)
				{
					if($iKey==$iNb) break;
					$sContenuFinal .= $sContenu.' ';
				}
				$sContenuFinal .= '(...)';
			}
			else
			{
				$sContenuFinal = $sContenu;
			}
		}
		elseif($settings->sendtofriend->get('sendtofriend_abstractType') == 'abstract'){
			$sContenuFinal = str_replace('\"','&quot;',strip_tags($sExcerpt));
		}
		return $sContenuFinal;	
	}
	
	public function EnvoyerSearchReplace($str){
		global $core,$_ctx;
		$in = array('%post-title%','%post-url%','%post-abstract%','%sender-name%','%sender-email%','%receiver-name%','%receiver-email%');
		$out = array(
			$_ctx->sdf->title,
			$_ctx->sdf->url,
			$_ctx->sdf->abstract,
			$_ctx->sdf->senderName,
			$_ctx->sdf->senderEmail,
			$_ctx->sdf->receiverName,
			$_ctx->sdf->receiverEmail
		);
		return str_replace($in,$out,$str);
	
	}
	
	public static function EnvoyerPostId() {	
		return '<?php echo $_ctx->sdf->id; ?>';
	}		
	public static function EnvoyerPostTitle() {		
		return '<?php echo $_ctx->sdf->title; ?>';
	}	
	public static function EnvoyerPostUrl() {		
		return '<?php echo $_ctx->sdf->url; ?>';
	}		
	
	public static function EnvoyerMessage() {
		$retour = '<?php
			$sMessages = "";
			if(count($_ctx->sdf->message)>0){
				$aMessages = $_ctx->sdf->message;
				$sMessages = "<ul>";
				foreach($aMessages as $sMessage){
					$sMessages .="<li>".$sMessage."</li>";
				}
				$sMessages .= "</ul>";
			}
			echo $sMessages;
		?>';	
		return $retour;
	}
}		
	
$core->tpl->addValue('EnvoyerPostId',array('tplEnvoyer','EnvoyerPostId'));
$core->tpl->addValue('EnvoyerPostTitle',array('tplEnvoyer','EnvoyerPostTitle'));
$core->tpl->addValue('EnvoyerPostUrl',array('tplEnvoyer','EnvoyerPostUrl'));
$core->tpl->addValue('EnvoyerMessage',array('tplEnvoyer','EnvoyerMessage'));
?>
