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

require dirname(__FILE__).'/_widgets.php';

// loading librairies
require_once dirname(__FILE__).'/inc/class.captcha.php';
require_once dirname(__FILE__).'/inc/class.newsletter.settings.php';
require_once dirname(__FILE__).'/inc/class.newsletter.tools.php';
require_once dirname(__FILE__).'/inc/class.newsletter.plugin.php';
require_once dirname(__FILE__).'/inc/class.newsletter.core.php';
require_once dirname(__FILE__).'/inc/class.newsletter.letter.php';

// adding templates
$core->tpl->addValue('Newsletter', array('tplNewsletter', 'Newsletter'));
$core->tpl->addValue('NewsletterPageTitle', array('tplNewsletter', 'NewsletterPageTitle'));
$core->tpl->addValue('NewsletterTemplateNotSet', array('tplNewsletter', 'NewsletterTemplateNotSet'));
$core->tpl->addBlock('NewsletterBlock', array('tplNewsletter', 'NewsletterBlock'));
$core->tpl->addBlock('NewsletterMessageBlock', array('tplNewsletter', 'NewsletterMessageBlock'));
$core->tpl->addBlock('NewsletterFormBlock', array('tplNewsletter', 'NewsletterFormBlock'));
$core->tpl->addValue('NewsletterFormSubmit', array('tplNewsletter', 'NewsletterFormSubmit'));
$core->tpl->addValue('NewsletterFormRandom', array('tplNewsletter', 'NewsletterFormRandom'));
$core->tpl->addValue('NewsletterFormCaptchaImg', array('tplNewsletter', 'NewsletterFormCaptchaImg'));
$core->tpl->addValue('NewsletterFormCaptchaInput', array('tplNewsletter', 'NewsletterFormCaptchaInput'));
$core->tpl->addValue('NewsletterFormLabel', array('tplNewsletter', 'NewsletterFormLabel'));
$core->tpl->addValue('NewsletterMsgPresentationForm', array('tplNewsletter', 'NewsletterMsgPresentationForm'));
$core->tpl->addBlock('NewsletterIfUseDefaultFormat',array('tplNewsletter','NewsletterIfUseDefaultFormat'));
$core->tpl->addValue('NewsletterFormFormatSelect', array('tplNewsletter', 'NewsletterFormFormatSelect'));
$core->tpl->addValue('NewsletterFormActionSelect', array('tplNewsletter', 'NewsletterFormActionSelect'));
$core->tpl->addBlock('NewsletterIfUseCaptcha',array('tplNewsletter','NewsletterIfUseCaptcha'));

$core->tpl->addBlock('NewsletterEntries',array('tplNewsletter','NewsletterEntries'));
//$core->tpl->addBlock('NewsletterEntryNext',array('tplNewsletter','NewsletterEntryNext'));
//$core->tpl->addBlock('NewsletterEntryPrevious',array('tplNewsletter','NewsletterEntryPrevious'));

// adding behaviors
$core->addBehavior('publicBeforeContentFilter', array('dcBehaviorsNewsletterPublic', 'translateKeywords'));
$core->addBehavior('publicHeadContent', array('dcBehaviorsNewsletterPublic', 'publicHeadContent'));

$core->addBehavior('publicAfterUserCreate', array('dcBehaviorsNewsletterPublic', 'newsletterUserCreate'));

if($core->plugins->moduleExists('dotajax') && !isset($core->plugins->getDisabledModules['dotajax'])) {
	$core->pubrest->register('newsletter','dcNewsletterWidgetRest');
}

class tplNewsletter
{
	/**
	 * Select the newsletter to send
	 *
 	 * @return:	string	msg
	 */
	public static function Newsletter()
	{
		global $core;
		
		if (isset($GLOBALS['newsletter']['cmd'])) 
			$cmd = (string) html::clean($GLOBALS['newsletter']['cmd']);
		else 
			$cmd = 'about';
      
		if (isset($GLOBALS['newsletter']['email'])) 
			$email = (string) html::clean($GLOBALS['newsletter']['email']);
		else 
			$email = null;
      
		if (isset($GLOBALS['newsletter']['code'])) 
			$code = (string) html::clean($GLOBALS['newsletter']['code']);
		else 
			$code = null;

		if (isset($GLOBALS['newsletter']['modesend'])) 
			$modesend = (string) html::clean($GLOBALS['newsletter']['modesend']);
		else 
			$modesend = null;

		switch ($cmd) {
			case 'test':
				$msg = __('Test display template');
				break;

			case 'about':
				$msg = '<ul><strong>'.__('About Newsletter ...').'</strong>';
				$msg .= '<li>'.__('Version').' : ' . newsletterPlugin::dcVersion().'</li>';
				$msg .= '<li>'.__('Author').' : ' . newsletterPlugin::dcAuthor().'</li>';
				$msg .= '<li>'.__('Description').' : ' . newsletterPlugin::dcDesc().'</li>';
				$msg .= '</ul>';
				
				$msg = html::escapeHTML($msg);
				break;

			case 'confirm':
				if ($email == null || $code == null)
					$msg = __('Missing informations');
				else {
					$rs = newsletterCore::getemail($email);
					if ($rs == null || $rs->regcode != $code) 
						$msg = __('Your subscription code is invalid.');
					else if ($rs->state == 'enabled') 
						$msg = __('Account already confirmed.');
					else {
						newsletterCore::send($rs->subscriber_id,'enable');
						$msg = __('Your subscription is confirmed.').'<br />'.__('You will soon receive an email.');
					}
				}
				break;

			case 'enable':
				if ($email == null)
					$msg = __('Missing informations');
				else {
					$rs = newsletterCore::getemail($email);
					if ($rs == null) 
						$msg = __('Unable to find your account informations.');
					else if ($rs->state == 'enabled') 
						$msg = __('Account already enabled.');
					else {
						newsletterCore::send($rs->subscriber_id,'enable');
						$msg = __('Your account is enabled.').'<br />'.__('You will soon receive an email.');
					}
				}
				break;

			case 'disable':
				if ($email == null)
					$msg = __('Missing informations');
				else {
					$rs = newsletterCore::getemail($email);
					if ($rs == null) 
						$msg = __('Unable to find your account informations.');
					else if ($rs->state == 'disabled') 
						$msg = __('Account already disabled.');
					else {
						newsletterCore::send($rs->subscriber_id,'disable');
						$msg = __('Your account is disabled.').'<br />'.__('You will soon receive an email.');
					}
				}
				break;

			case 'suspend':
				if ($email == null)
					$msg = __('Missing informations');
				else {
					$rs = newsletterCore::getemail($email);
					if ($rs == null) 
						$msg = __('Unable to find you account informations.');
					else if ($rs->state == 'suspended') 
						$msg = __('Account already suspended.');
					else {
						newsletterCore::send($rs->subscriber_id,'suspend');
						$msg = __('Your account is suspended.').'<br />'.__('You will soon receive an email.');
					}
				}
				break;

			case 'changemode':
				if ($email == null)
					$msg = __('Missing informations');
				else {
					$rs = newsletterCore::getemail($email);
					if ($rs == null) 
						$msg = __('Unable to find you account informations.');
					else {
						newsletterCore::send($rs->subscriber_id,'changemode');
						$msg = __('Your sending format is').$modesend.'<br />'.__('You will soon receive an email.');
					}
				}
				break;

			case 'submit':
				$email = (string)html::clean($_POST['nl_email']);
				$option = (string)html::clean($_POST['nl_option']);
				//$modesend = (string)html::clean($_POST['nl_modesend']);
				$check = true;
				$newsletter_settings = new newsletterSettings($core);
				if ($newsletter_settings->getCaptcha()) {
					$captcha = (string)html::clean($_POST['nl_captcha']);
					$read = Captcha::read();
					if ($read != $captcha) {
						$check = false;
						$ca = new Captcha(80, 30, 5);
						$ca->generate();
						$ca->file();
						$ca->write();
					}
				}

				if (!$check) {
					$msg = __('Bad captcha code');
				} else switch ($option) {
					case 'subscribe':
						$msg = newsletterCore::accountCreate($email,null,$modesend);
						break;
					
					case 'unsubscribe':
						$msg = newsletterCore::accountDelete($email);
						break;

					case 'suspend':
						$msg = newsletterCore::accountSuspend($email);
						break;

					case 'resume':
						$msg = newsletterCore::accountResume($email);
						break;

					case 'changemode':
						$msg = newsletterCore::accountChangeMode($email,$modesend);
						break;

					default:
						$msg = __('Error in formular.').' option = '.$option;
						//$msg = __('Error in formular.');
						break;
				}
				break;

			default:
				$msg = '';
				break;
		}

		return $msg;
	}

	/**
	* title page
	*/
	public static function NewsletterPageTitle()
	{
		global $core;
		$newsletter_settings = new newsletterSettings($core);
		return $newsletter_settings->getFormTitlePage();
	}	

	/**
	* indicate to the user that the page newsletter has not been initialized
	*/
	public static function NewsletterTemplateNotSet()
	{
		return '<?php echo newsletterCore::TemplateNotSet(); ?>';
	}

	public static function NewsletterBlock($attr, $content)
	{
		return $content;
	}

	public static function NewsletterMessageBlock($attr, $content)
	{
		$text = '<?php echo "'.
			'<form action=\"'.newsletterCore::url('form').'\" method=\"post\" id=\"comment-form\" class=\"newsletter\">'.
			'<fieldset>'.
			'<p class=\"field\">'.
			'" ?>'.
			html::decodeEntities($content).
			'<?php echo "'.
			'</p>'.
			'<p>'.
			'<input type=\"submit\" name=\"nl_back\" id=\"nl_back\" value=\"'.__('Back').'\" class=\"submit\" />'.
			'</p>'.
			'</fieldset>'.
			'</form>'.
			'" ?>';

		return '<?php if (!empty($GLOBALS[\'newsletter\'][\'msg\'])) { ?>'.$text.'<?php } ?>';
	}

	public static function NewsletterFormBlock($attr, $content)
	{
		return '<?php	if (!empty($GLOBALS[\'newsletter\'][\'form\'])) { ?>'.$content.'<?php } ?>';
	}

	public static function NewsletterFormSubmit()
	{
		return '<?php echo newsletterCore::url(\'submit\'); ?>';
	}

	public static function NewsletterFormRandom()
	{
		return '<?php  echo "'.newsletterTools::getRandom().'" ?>';
	}

	public static function NewsletterFormCaptchaImg()
	{
		return '<?php echo "<img src=\"'.Captcha::www().'/captcha.img.png\" style=\"vertical-align: middle;\" alt=\"'.__('Captcha').'\" />" ?>';
	}

	public static function NewsletterFormCaptchaInput()
	{
		return '<?php echo "<p><input type=\"text\" name=\"nl_captcha\" id=\"nl_captcha\" value=\"\" style=\"width:90px; vertical-align:top;\" /></p>" ?>';
	}

	public static function NewsletterFormLabel($attr, $content)
	{
		switch ($attr['id'])
		{
			case 'ok':
				return '<?php echo __(\'Send\') ?>';

			case 'subscribe':
				return '<?php echo __(\'Subscribe\') ?>';

			case 'unsubscribe':
				return '<?php echo __(\'Unsubscribe\') ?>';

			case 'suspend':
				return '<?php echo __(\'Suspend\') ?>';
				// __('Suspend') 

			case 'resume':
				return '<?php echo __(\'Resume\') ?>';
				// __('Resume') 

			case 'nl_email':
				return '<?php echo __(\'Email\') ?>';

			case 'nl_option':
				return '<?php echo __(\'Action\') ?>';

			case 'nl_captcha':
				return '<?php echo  \'<label for="nl_captcha">\'. __(\'Captcha\') .\'</label>\' ?>';

			case 'nl_submit':
				return '';

			case 'html':
				return '<?php echo __(\'html\') ?>';

			case 'text':
				return '<?php echo __(\'text\') ?>';
				// __('text') 

			case 'nl_modesend':
				return '<?php echo __(\'Format\') ?>';

			case 'changemode':
				return '<?php echo __(\'Change format\') ?>';
				// __('Change format') 

			case 'back':
				return '<?php echo __(\'Back\') ?>';

		}
	}

	public static function NewsletterMsgPresentationForm()
	{
		global $core;
		$newsletter_settings = new newsletterSettings($core);
		return $newsletter_settings->getMsgPresentationForm();
	}

	public static function NewsletterIfUseDefaultFormat($attr,$content)
	{
		global $core;
		$newsletter_settings = new newsletterSettings($core);
		return (!$newsletter_settings->getUseDefaultFormat()? $content : '');
	}

	public static function NewsletterFormFormatSelect($attr,$content)
	{
		$text = '<?php echo "'.
			'<label for=\"nl_modesend\">'.__('Format').'&nbsp;:</label>'.
			'<select style=\"border:1px inset silver; width:150px;\" name=\"nl_modesend\" id=\"nl_modesend\" size=\"1\">'.
			'<option value=\"html\" selected=\"selected\">'.__('html').'</option>'.
			'<option value=\"text\">'.__('text').'</option>'.
			'</select>'.
			'" ?>';

		return $text;
	}

	public static function NewsletterFormActionSelect($attr,$content)
	{
		global $core;
		$newsletter_settings = new newsletterSettings($core);
		
		$text = '<?php echo "'.
		'<label for=\"nl_option\">'.__('Action').'&nbsp;:</label>'.
		'<select style=\"border:1px inset silver; width:150px;\" name=\"nl_option\" id=\"nl_option\" size=\"1\">'.
		'<option value=\"subscribe\" selected=\"selected\">'.__('Subscribe').'</option>';
		
		if(!$newsletter_settings->getUseDefaultFormat()) {
			$text .= '<option value=\"changemode\">'.__('Change format').'</option>';
		}
		
		if($newsletter_settings->getCheckUseSuspend()) {
			$text .= '<option value=\"suspend\">'.__('Suspend').'</option>';
		}
		
		$text .= '<option value=\"resume\">'.__('Resume').'</option>'.
		'<option value=\"\">---</option>'.
		'<option value=\"unsubscribe\">'.__('Unsubscribe').'</option>'.
		'</select>'.
		'" ?>';
		return $text;
	}	

	public static function NewsletterIfUseCaptcha($attr,$content)
	{
		global $core;
		$newsletter_settings = new newsletterSettings($core);
		if (!empty($GLOBALS['newsletter']['form']) && $newsletter_settings->getCaptcha()) {
			$ca = new Captcha(80, 30, 5);
			$ca->generate();
			$ca->file();
			$ca->write();
		}
		return ($newsletter_settings->getCaptcha()? $content : '');		
	}

	
	/* NewslettersEntries -------------------------------------------- */
	/*dtd
	<!ELEMENT tpl:NewslettersEntries - - -- Blog NewslettersEntries loop -->
	<!ATTLIST tpl:NewslettersEntries
	lastn	CDATA	#IMPLIED	-- limit number of results to specified value
	disabled -- author	CDATA	#IMPLIED	-- get entries for a given user id
	disabled -- category	CDATA	#IMPLIED	-- get entries for specific categories only (multiple comma-separated categories can be specified. Use "!" as prefix to exclude a category)
	disabled -- no_category	CDATA	#IMPLIED	-- get entries without category
	disabled -- no_context (1|0)	#IMPLIED  -- Override context information
	sortby	(title|selected|author|date|id)	#IMPLIED	-- specify entries sort criteria (default : date) (multiple comma-separated sortby can be specified. Use "?asc" or "?desc" as suffix to provide an order for each sorby)
	order	(desc|asc)	#IMPLIED	-- specify entries order (default : desc)
	disabled -- no_content	(0|1)	#IMPLIED	-- do not retrieve entries content
	selected	(0|1)	#IMPLIED	-- retrieve posts marked as selected only (value: 1) or not selected only (value: 0)
	disabled -- url		CDATA	#IMPLIED	-- retrieve post by its url
	disabled -- type		CDATA	#IMPLIED	-- retrieve post with given post_type (there can be many ones separated by comma)
	disabled -- age		CDATA	#IMPLIED	-- retrieve posts by maximum age (ex: -2 days, last month, last week)
	ignore_pagination	(0|1)	#IMPLIED	-- ignore page number provided in URL (useful when using multiple tpl:Entries on the same page)
	>
	*/	
	public static function NewsletterEntries($attr,$content)
	{
		global $core;
		$newsletter_settings = new newsletterSettings($core);
		
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";

		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "\$params['limit'] = ".$newsletter_settings->getNbNewslettersPerPublicPage().";\n";
		}
		
		if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
			$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
		} else {
			$p .= "\$params['limit'] = array(0, \$params['limit']);\n";
		}

		$p .= "\$params['post_type'] = 'newsletter';\n";

		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'title': $sortby = 'post_title'; break;
				case 'selected' : $sortby = 'post_selected'; break;
				case 'author' : $sortby = 'user_id'; break;
				case 'date' : $sortby = 'post_dt'; break;
			}
		} else {
			$sortby = $newsletter_settings->getNewslettersPublicPageSort();
		}
		
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		} else {
			$order = $newsletter_settings->getNewslettersPublicPageOrder();
		}

		$p .= "\$params['order'] = '".$sortby." ".$order."';\n";

		if (!empty($attr['url'])) {
			$p .= "\$params['post_url'] = '".addslashes($attr['url'])."';\n";
		}
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		if (isset($attr['selected'])) {
			$p .= "\$params['post_selected'] = ".(integer) (boolean) $attr['selected'].";";
		}

		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['sql'] = \"AND P.post_lang = '\".\$core->blog->con->escape(\$_ctx->langs->post_lang).\"' \"; ".
			"}\n";
		}

		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->posts = $core->blog->dcNewsletter->getNewsletters($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
		
		return $res;

	}
	
	
}

class publicWidgetsNewsletter
{
	/**
	 * initialize widget
	 * @param $w
	 * @return String
	 */
	public static function initWidgets($w)	
	{
		global $core;
		
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$blog_settings =& $core->blog->settings->newsletter;
			$system_settings =& $core->blog->settings->system;
		} else {
			$blog_settings =& $core->blog->settings;
			$system_settings =& $core->blog->settings;
		}
		
		try {
			
			$newsletter_flag = (boolean)$blog_settings->newsletter_flag;
			
			// get state of plugin
			if (!$newsletter_flag) 
				return;

			// use only on homepage
			$url = &$core->url;
			if ($w->homeonly && $url->type != 'default')  {
				return;
			}

			$plugin_name = 'Newsletter';
			$title = ($w->title) ? html::escapeHTML($w->title) : $plugin_name;
			$showTitle = ($w->showtitle) ? true : false;
			$subscription_link = ($w->subscription_link) ? html::escapeHTML($w->subscription_link) : __('Subscription link');
			$text = '';
			$newsletter_settings = new newsletterSettings($core);

			// mise en place du contenu du widget dans $text
			if ($w->inwidget) {
				
				// if dotajax is installed
				if($core->plugins->moduleExists('dotajax') && !isset($core->plugins->getDisabledModules['dotajax'])) {
					
					$link = '';
					$text .= 
					'<form action ="" method="post" id="nl_form">'."\n".
					$core->formNonce().
					form::hidden(array('nl_random'),newsletterTools::getRandom()).
					'<p>'.$newsletter_settings->getMsgPresentationForm().'</p>'.
					
					'<p>'.
					'<label for="nl_email">'.__('Email').'</label>&nbsp;:&nbsp;'.
					form::field(array('nl_email','nl_email'),15,255).
					'</p>';
					
					if(!$newsletter_settings->getUseDefaultFormat()) {
					$text .= '<p>'.
					'<label for="nl_modesend">'.__('Format').'</label>&nbsp;:&nbsp;'.
					'<select style="border:1px inset silver; width:140px;" name="nl_modesend" id="nl_modesend" size="1">'.
						'<option value="html" selected="selected">'.__('html').'</option>'.
						'<option value="text">'.__('text').'</option>'.
					'</select></p>';
					}
	
					$text .= '<p><label for="nl_option">'.__('Actions').'</label>&nbsp;:&nbsp;'.
					'<select style="border:1px inset silver; width:140px;" name="nl_option" id="nl_option" size="1">'.
						'<option value="subscribe" selected="selected">'.__('Subscribe').'</option>';
						
					if(!$newsletter_settings->getUseDefaultFormat()) {
						$text .= '<option value="changemode">'.__('Change format').'</option>';
					}
	
					if($newsletter_settings->getCheckUseSuspend()) {
						$text .= '<option value=\"suspend\">'.__('Suspend').'</option>';
					}
	
					$text .= 
						'<option value="resume">'.__('Resume').'</option>'.
						'<option value="">---</option>'.
						'<option value="unsubscribe">'.__('Unsubscribe').'</option>'.
					'</select>'.
					'</p>';
	
					if ($newsletter_settings->getCaptcha()) {
						require_once dirname(__FILE__).'/inc/class.captcha.php';							
						$as = new Captcha(80, 30, 5);
						$as->generate();
						$as->file();
						$as->write();
							
						$text .=
						'<p><label for="nl_captcha">'.__('Captcha').'</label>&nbsp;:<br />'.
						'<img src="'.Captcha::www().'/captcha.img.png" alt="'.__('Captcha').'" /><br />'.
						form::field(array('nl_captcha','nl_captcha'),9,30).
						'</p>';
					}
					
					$text .=
					'<p><input class="submit" type="submit" name="nl_submit" id="nl_submit" value="'.__('Send').'" /></p>'.
					'</form>';

				} else {
					
					// todo : if dotajax is not installed
					$link = newsletterCore::url('submit');
					$text .=
					'<p>'.$newsletter_settings->getMsgPresentationForm().'</p>'.
					'<form action="'.$link.'" method="post" id="nl_form">'."\n".
					"<p>\n".
					$core->formNonce().
					form::hidden(array('nl_random'),newsletterTools::getRandom()).
					"</p>\n".
					'<p>'.
					'<label for="nl_email">'.__('Email').'</label>&nbsp;:&nbsp;'.
					form::field(array('nl_email','nl_email'),15,255).
					'</p>';
					
					if(!$newsletter_settings->getUseDefaultFormat()) {
					$text .= '<p><label for="nl_modesend">'.__('Format').'</label>&nbsp;:&nbsp;'.
					'<select style="border:1px inset silver; width:140px;" name="nl_modesend" id="nl_modesend" size="1">'.
						'<option value="html" selected="selected">'.__('html').'</option>'.
						'<option value="text">'.__('text').'</option>'.
					'</select></p>';
					}
	
					$text .= '<p><label for="nl_submit">'.__('Actions').'</label>&nbsp;:&nbsp;'.
					'<select style="border:1px inset silver; width:140px;" name="nl_option" id="nl_option" size="1">'.
						'<option value="subscribe" selected="selected">'.__('Subscribe').'</option>';
						
					if(!$newsletter_settings->getUseDefaultFormat()) {
						$text .= '<option value="changemode">'.__('Change format').'</option>';
					}
	
					if($newsletter_settings->getCheckUseSuspend()) {
						$text .= '<option value=\"suspend\">'.__('Suspend').'</option>';
					}
	
					$text .= 
						'<option value="resume">'.__('Resume').'</option>'.
						'<option value="">---</option>'.
						'<option value="unsubscribe">'.__('Unsubscribe').'</option>'.
					'</select>'.
					'</p>';
	
					if ($newsletter_settings->getCaptcha()) {
						require_once dirname(__FILE__).'/inc/class.captcha.php';							
						$as = new Captcha(80, 30, 5);
						$as->generate();
						$as->file();
						$as->write();
							
						$text .=
						'<p><label for="nl_captcha">'.__('Captcha').'</label>&nbsp;:<br />'.
						'<img src="'.Captcha::www().'/captcha.img.png" alt="'.__('Captcha').'" /><br />'.
						form::field(array('nl_captcha','nl_captcha'),9,30).
						'</p>';
					}
					
					$text .=
					'<p><input class="submit" type="submit" name="nl_submit" id="nl_submit" value="'.__('Send').'" /></p>'.
					'</form>';
				}

			} else {
         		
				$link = newsletterCore::url('form');
				
				if ($w->insublink) {
					$title = str_replace('%I', '<img src="?pf=newsletter/icon.png" alt="" width="16" height="16" style="margin: 0 3px; vertical-align: middle;" />', $title);
					$text = $w->text ? $w->text : '';
					$text .= '<ul><li><a href="'.$link.'">'.$subscription_link.'</a></li></ul>';
				} else {
	         		$title = '<a href="'.$link.'">'.$title.'</a>';
	            	$title = str_replace('%I', '</a><img src="?pf=newsletter/icon.png" alt="newsletter" width="16" height="16" style="margin:0 3px; vertical-align:middle;" /> <a href="'.$link.'">', $title);
				}
			}

			if ($showTitle === true) 
				$title = '<h2>'.$title.'</h2>';
			else 
				$title = '';
			
			$text .= '<div id="message"></div>';
				
			return "\n".'<div class="'.newsletterPlugin::pname().'">'.$title.$text.'<p>&nbsp;</p></div>'."\n";

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
	
	# List Newsletters Widget function
	public static function listnsltrWidget($w)
	{
		global $core,$_ctx;

		$orderby = $w->orderby;
		$orderdir = $w->orderdir;
		$order="";
		
		if ($orderby == 'date')
			$order .= 'P.post_dt ';
		else
			$order .= 'P.post_title ';		
		
		$order .= ($orderdir == 'asc') ? 'asc':'desc';
			
		if (empty($core->blog->dcNewsletter)) $core->blog->dcNewsletter = new dcNewsletter($core);

		if ($w->homeonly && $core->url->type != 'default') {
			return;
		}
		
		if (((integer)$w->limit) != 0) {
			$rsnsltr = $core->blog->dcNewsletter->getNewsletters(array("order" => $order, "limit" => array(0,(integer)$w->limit), "no_content" => true));
		} else {
			$rsnsltr = $core->blog->dcNewsletter->getNewsletters(array("order" => $order, "no_content" => true));
		}
		
		$title = $w->title ? html::escapeHTML($w->title) : 'Newsletters';
		
		$res =
		'<div class="listnsltr"><h2>'.$title.'</h2>';

		$res .= "<ul>";
		while ($rsnsltr->fetch()) {
			$nsltrLink = '<a href="'.$rsnsltr->getURL().'">'.html::escapeHTML($rsnsltr->post_title).'</a>';
			$res .= '<li class="linsltr">'.$nsltrLink.'</li>';
		}
		$res .= '</ul>';
		
		$res .= '<p class="allnsltr"><a href="'.$core->blog->url.$core->url->getBase("newsletters").'">'.
			__('All newsletters').'</a></p>';
			
		$res .= '</div>';
		
		return $res;
	}	
}

// URL handler
class urlNewsletter extends dcUrlHandlers
{
    public static function newsletter($args)
    {
		$core = $GLOBALS['core'];
		$_ctx = $GLOBALS['_ctx'];

		if($args == '') {
			# The specified Preview URL is malformed.
	      		self::p404();
	    }

		// initialisation des variables
		$flag = 0;
		$cmd = null;
		$GLOBALS['newsletter']['cmd'] = null;
		$GLOBALS['newsletter']['msg'] = false;
		$GLOBALS['newsletter']['form'] = false;
		$GLOBALS['newsletter']['email'] = null;
		$GLOBALS['newsletter']['code'] = null;
		$GLOBALS['newsletter']['modesend'] = null;

		// décomposition des arguments et aiguillage
		$params = explode('/', $args);
		if (isset($params[0]) && !empty($params[0])) 
			$cmd = (string)html::clean($params[0]);
		else 
			$cmd = null;
					      
		if (isset($params[1]) && !empty($params[1])) {
			$email = newsletterTools::base64_url_decode((string)html::clean($params[1]));
		} else
	    	$email = null;
	      
		if (isset($params[2]) && !empty($params[2])) 
			$regcode = (string)html::clean($params[2]);
		else 
			$regcode = null;			

		if (isset($params[3]) && !empty($params[3])) 
			$modesend = newsletterTools::base64_url_decode((string)html::clean($params[3]));
		else 
			$modesend = null;			

		switch ($cmd) {
			case 'test':
			case 'about':
				$GLOBALS['newsletter']['msg'] = true;
			break;

			case 'form':
				$GLOBALS['newsletter']['form'] = true;
			break;
                
			case 'submit':
				$GLOBALS['newsletter']['msg'] = true;
			break;
					
			case 'confirm':
			case 'enable':
			case 'disable':
			case 'suspend':
			case 'changemode':
			case 'resume':
			{
				if ($email == null) {
					self::p404();
				}
				$GLOBALS['newsletter']['msg'] = true;
				break;
			}
				
			default:
			{
				$flag = 1;
				self::letter($args);
				break;
			}
		}

		if (!$flag) {

			$GLOBALS['newsletter']['cmd'] = $cmd;
			$GLOBALS['newsletter']['email'] = $email;
			$GLOBALS['newsletter']['code'] = $regcode;
			$GLOBALS['newsletter']['modesend'] = $modesend;
	
			// Affichage du formulaire
			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			$file = $core->tpl->getFilePath('subscribe.newsletter.html');
			files::touch($file);
			//self::serveDocument('subscribe.newsletter.html');
			self::serveDocument('subscribe.newsletter.html','text/html',false,false);
		}
    }

    public static function letterpreview($args)
    {
		$core = $GLOBALS['core'];
		$_ctx = $GLOBALS['_ctx'];
		
		if (!preg_match('#^(.+?)/([0-9a-z]{40})/(.+?)$#',$args,$m)) {
			# The specified Preview URL is malformed.
			self::p404();
		}
		else
		{
			$user_id = $m[1];
			$user_key = $m[2];
			$post_url = $m[3];
			if (!$core->auth->checkUser($user_id,null,$user_key)) {
				# The user has no access to the entry.
				self::p404();
			}
			else
			{
				$_ctx->preview = true;
				self::letter($post_url);
			}
		}
    }
    
	public static function letter($args)
	{
		if ($args == '') {
			# No page was specified.
			self::p404();
		}
		else
		{
			$_ctx =& $GLOBALS['_ctx'];
			$core =& $GLOBALS['core'];
			
			$core->blog->withoutPassword(false);
			
			$params = new ArrayObject();
			$params['post_type'] = 'newsletter';
			$params['post_url'] = $args;
			
			$_ctx->posts = $core->blog->getPosts($params);
			
			$_ctx->comment_preview = new ArrayObject();
			$_ctx->comment_preview['content'] = '';
			$_ctx->comment_preview['rawcontent'] = '';
			$_ctx->comment_preview['name'] = '';
			$_ctx->comment_preview['mail'] = '';
			$_ctx->comment_preview['site'] = '';
			$_ctx->comment_preview['preview'] = false;
			$_ctx->comment_preview['remember'] = false;
			
			$core->blog->withoutPassword(true);
			
			
			if ($_ctx->posts->isEmpty())
			{
				# The specified page does not exist.
				self::p404();
			}
			else
			{
				$post_id = $_ctx->posts->post_id;
				$post_password = $_ctx->posts->post_password;
				
				# Password protected entry
				if ($post_password != '' && !$_ctx->preview)
				{
					# Get passwords cookie
					if (isset($_COOKIE['dc_passwd'])) {
						$pwd_cookie = unserialize($_COOKIE['dc_passwd']);
					} else {
						$pwd_cookie = array();
					}
					
					# Check for match
					if ((!empty($_POST['password']) && $_POST['password'] == $post_password)
					|| (isset($pwd_cookie[$post_id]) && $pwd_cookie[$post_id] == $post_password))
					{
						$pwd_cookie[$post_id] = $post_password;
						setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
					}
					else
					{
						self::serveDocument('password-form.html','text/html',false);
						return;
					}
				}
				
				
				# The entry
				$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
				//self::serveDocument('letter.html');
				self::serveDocument('letter.html');
				//self::serveDocument('subscribe.newsletter.html');
			}
		}
	}
	
    public static function newsletters($args)
    {
    	$_ctx =& $GLOBALS['_ctx'];
    	$core =& $GLOBALS['core'];

		$n = self::getPageNumber($args);
		/*if (preg_match('#(^|/)category/(.+)$#',$args,$m)){
			$params['cat_url']=$m[2];
			$GLOBALS['_ctx']->categories = $GLOBALS['core']->blog->getCategories($params);
		}
		if (preg_match('#(^|/)nocat$#',$args,$m)){
			$GLOBALS['_ctx']->nocat = true;
		}*/
		if ($n) {
			$GLOBALS['_page_number'] = $n;
			$GLOBALS['core']->url->type = $n > 1 ? 'newsletters-page' : 'newsletters';
		}
		/*
		$GLOBALS['core']->meta = new dcMeta($GLOBALS['core']);;
		$GLOBALS['_ctx']->nb_entry_per_page= $GLOBALS['core']->blog->settings->gallery->gallery_nb_galleries_per_page;
		*/
    	
    	$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
    	self::serveDocument('newsletters.html');    	
    }
	
	
}

// Define behaviors
class dcBehaviorsNewsletterPublic
{
	public static function publicHeadContent(dcCore $core,$_ctx)
	{
		# Settings compatibility test
		if (version_compare(DC_VERSION,'2.2-alpha','>=')) {
			$blog_settings =& $core->blog->settings->newsletter;
			$system_settings =& $core->blog->settings->system;
		} else {
			$blog_settings =& $core->blog->settings;
			$system_settings =& $core->blog->settings;
		}
		
		try {
			$newsletter_flag = (boolean)$blog_settings->newsletter_flag;
			
			// prise en compte de l'état d'activation du plugin
			if (!$newsletter_flag) 
				return;		
		
			if($core->url->type == "newsletter") {
				$letter_css = new newsletterCSS($core);
				$css_style = '<style type="text/css" media="screen">';
				$css_style .= $letter_css->getLetterCSS();
				$css_style .= '</style>';
				echo $css_style;
			}

			// if dotajax is installed
			if($core->plugins->moduleExists('dotajax') && !isset($core->plugins->getDisabledModules['dotajax'])) {
				echo
				"<script type=\"text/javascript\" src=\"".
					'?pf=newsletter/js/_newsletter_pub.js">'.
				"</script>\n";

				echo 
					'<script type="text/javascript">'."\n".
					"//<![CDATA[\n".
					"please_wait = '".html::escapeJS(__('Waiting...'))."';\n".
					"\n//]]>\n".
					"</script>\n";				
			}
		
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	//public static function translateKeywords(dcCore $core, $tag, $args, $attr,$content)
	public static function translateKeywords(dcCore $core, $tag, $args)
	{
		global $_ctx;
		if($tag != 'EntryContent' //tpl value
		 || $args[0] == '' //content
		 || $args[2] // remove html
		 || $core->url->type != 'newsletter'
		) return;

		$nltr = new newsletterLetter($core,(integer)$_ctx->posts->post_id);
		$body = $args[0];
		
		$body = $nltr->rendering($body, $_ctx->posts->getURL());
		$args[0] = $nltr->renderingSubscriber($body, '');

		return;
	}
	
	/**
	 * Add entry in newsletter when an user is added in the plugin "Agora" 
	 * @param $cur
	 * @param $user_id
	 * @return unknown_type
	 */
	public static function newsletterUserCreate($cur,$user_id)
	{
		global $core;
		$newsletter_settings = new newsletterSettings($core);

		if($newsletter_settings->getCheckAgoraLink()) {
			$email = $cur->user_email;
			try {
				if (!newsletterCore::accountCreate($email)) {
					throw new Exception(__('Error adding subscriber.').' '.$email);
				}
			} catch (Exception $e) {
				throw new Exception('Plugin Newsletter : '.$e->getMessage());
			}
		}
		return;
	}
}

class dcNewsletterWidgetRest 
{
	public static function submitWidget(dcCore $core,$get,$post)
	{
		if (!isset($post['email']) || $post['email'] == '')
			throw new Exception (__('No email specified.'));
		else
			$email = $post['email'];

		if (!isset($post['option']) || $post['option'] == '')
			throw new Exception (__('No option specified.'));
		else
			$option = $post['option'];
		
		$captcha = isset($post['captcha']) ? $post['captcha'] : null;
		$modesend = isset($post['nl_modesend']) ? $post['nl_modesend'] : null;
		
		$check = true;
		$newsletter_settings = new newsletterSettings($core);
		if ($newsletter_settings->getCaptcha()) {

			if (!isset($post['captcha']) || $post['captcha'] == '')
				throw new Exception (__('No captcha specified.'));				
			
			$read = Captcha::read();
			if ($read != $captcha) {
				$check = false;
				/* generate new code
				$ca = new Captcha(80, 30, 5);
				$ca->generate();
				$ca->file();
				$ca->write();
				*/
			}
		}

		if (!$check) {
			throw new Exception (__('Bad captcha code'));
		} else switch ($option) {
			case 'subscribe':
				$msg = newsletterCore::accountCreate($email,null,$modesend);
				break;
					
			case 'unsubscribe':
				$msg = newsletterCore::accountDelete($email);
				break;

			case 'suspend':
				$msg = newsletterCore::accountSuspend($email);
				break;

			case 'resume':
				$msg = newsletterCore::accountResume($email);
				break;

			case 'changemode':
				$msg = newsletterCore::accountChangeMode($email,$modesend);
				break;

			default:
				throw new Exception (__('Error in formular.').' option = '.$option);
				break;
		}
		
		$rsp = array();
		
		$subscriberTag = array();
		$subscriberTag['email'] = $email;
		$subscriberTag['option'] = $option;
		$subscriberTag['result'] = $msg;
		
		$rsp[]=$subscriberTag;
		
		return $rsp;
	}		
}

?>