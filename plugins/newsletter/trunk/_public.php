<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of Newsletter, a plugin for Dotclear 2.
# Copyright (C) 2009 Benoit de Marne, and contributors. All rights
# reserved.
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 3
# of the License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_RC_PATH')) { return; }

// chargement des librairies
require_once dirname(__FILE__).'/class.plugin.php';
require_once dirname(__FILE__).'/class.modele.php';

// ajout des templates
$core->tpl->addValue('Newsletter', array('dcNewsletter', 'Newsletter'));
$core->tpl->addValue('NewsletterPageTitle', array('dcNewsletter', 'NewsletterPageTitle'));
$core->tpl->addValue('NewsletterTemplateNotSet', array('dcNewsletter', 'NewsletterTemplateNotSet'));
$core->tpl->addBlock('NewsletterBlock', array('dcNewsletter', 'NewsletterBlock'));
$core->tpl->addBlock('NewsletterMessageBlock', array('dcNewsletter', 'NewsletterMessageBlock'));
$core->tpl->addBlock('NewsletterFormBlock', array('dcNewsletter', 'NewsletterFormBlock'));
$core->tpl->addValue('NewsletterFormSubmit', array('dcNewsletter', 'NewsletterFormSubmit'));
$core->tpl->addValue('NewsletterFormRandom', array('dcNewsletter', 'NewsletterFormRandom'));
$core->tpl->addValue('NewsletterFormCaptchaImg', array('dcNewsletter', 'NewsletterFormCaptchaImg'));
$core->tpl->addValue('NewsletterFormCaptchaInput', array('dcNewsletter', 'NewsletterFormCaptchaInput'));
$core->tpl->addValue('NewsletterFormLabel', array('dcNewsletter', 'NewsletterFormLabel'));
$core->tpl->addValue('NewsletterMsgPresentationForm', array('dcNewsletter', 'NewsletterMsgPresentationForm'));

// ajout de la gestion des url
$core->url->register('newsletter', 'newsletter', '^newsletter/(.+)$', array('urlNewsletter', 'newsletter'));

require_once dirname(__FILE__).'/class.dc.newsletter.php';

// widget
class WidgetsNewsletter
{
	/**
	* initialisation du widget
	*/
	public static function widget($w)
	{
		global $core;
		try {
			// prise en compte de l'état d'activation du plugin
			if (!pluginNewsletter::isActive()) 
				return;

			// prise en compte paramètre: uniquement sur la page d'accueil
			$url = &$core->url;
			if ($w->homeonly && $url->type != 'default')  {
				return;
			}

			// paramétrage des variables
			$plugin_name = __('Newsletter');
			$title = ($w->title) ? html::escapeHTML($w->title) : $plugin_name;
			$showTitle = ($w->showtitle) ? true : false;
			$subscription_link = ($w->subscription_link) ? html::escapeHTML($w->subscription_link) : __('Subscription link');
			
			$text = '';

			// mise en place du contenu du widget dans $text
			if ($w->inwidget) {
				$link = dcNewsletter::url('submit');
				$text .=
				'<form action="'.$link.'" method="post" id="nl_form">'.
				$core->formNonce().
				form::hidden(array('nl_random'),dcNewsletter::getRandom()).
				'<ul>'.
				'<li><label for="nl_email">'.__('Email').'</label>&nbsp;:&nbsp;'.
				form::field(array('nl_email','nl_email'),15,255).
				'</li>'.
				'<li><label for="nl_modesend">'.__('Format').'</label>&nbsp;:&nbsp;'.
				'<select style="border:1px inset silver; width:140px;" name="nl_modesend" id="nl_modesend" size="1" maxlength="255">'.
					'<option value="html" selected="selected">'.__('html').'</option>'.
					'<option value="text">'.__('text').'</option>'.
				'</select>'.
				'<li><label for="nl_submit">'.__('Actions').'</label>&nbsp;:&nbsp;'.
				'<select style="border:1px inset silver; width:140px;" name="nl_option" id="nl_option" size="1" maxlength="255">'.
					'<option value="subscribe" selected="selected">'.__('Subscribe').'</option>'.
					'<option value="changemode">'.__('Change format').'</option>'.
					'<option value="suspend">'.__('Suspend').'</option>'.
					'<option value="resume">'.__('Resume').'</option>'.
					'<option value="">---</option>'.
					'<option value="unsubscribe">'.__('Unsubscribe').'</option>'.
				'</select>'.
				/*
				'<li><label for="nl_submit">'.__('Actions').'</label>&nbsp;:<br />'.
				form::radio(array('nl_option'),'subscribe', true).__('Subscribe').'<br />'.
				form::radio(array('nl_option'),'unsubscribe').__('Unsubscribe').'<br />'.
				//*/
				'</li>';

				if (pluginNewsletter::getCaptcha()) {
					require_once dirname(__FILE__).'/class.captcha.php';							
							
					$as = new Captcha(80, 30, 5);
					$as->generate();
					$as->file();
					$as->write();
						
					$text .=
					//'<p>'.
					'<li><label for="nl_captcha">'.__('Captcha').'</label>&nbsp;:<br />'.
					'<img src="'.Captcha::www().'/captcha.img.png" alt="'.__('Captcha').'" /><br />'.
					form::field(array('nl_captcha','nl_captcha'),9,30).
					'</li>';
					//'</p>'.
				}
				
				$text .=
				'<p><input class="submit" type="submit" name="nl_submit" id="nl_submit" value="'.__('Send').'" /></p>'.

				'</ul>'.
				//'</fieldset>'.
				'</form>';
				
			} else {
         			$link = dcNewsletter::url('form');
				if ($w->insublink) {
					$title = str_replace('%I', '<img src="?pf=newsletter/icon.png" alt="" width="16" height="16" style="margin: 0 3px; vertical-align: middle;" />', $title);
					$text = $w->text ? $w->text : '';

					$text .= '<ul><li><a href="'.$link.'">'.$subscription_link.'</a></li></ul>';
				} else {
	         			$title = '<a href="'.$link.'">'.$title.'</a>';
	            		$title = str_replace('%I', '</a><img src="?pf=newsletter/icon.png" alt="newsletter" width="16" height="16" style="margin:0 3px; vertical-align:middle;" /> <a href="'.$link.'">', $title);
				}
			}

			// renvoi du code à afficher pour le widget
			if ($showTitle === true) 
				$title = '<h2>'.$title.'</h2>';
			else 
				$title = '';
			
			return "\n".'<div class="'.pluginNewsletter::pname().'">'.$title.$text.'</div>'."\n";
			//return "\n".'<div class="categories">'.$title.$text.'</div>'."\n";
			

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
}

?>
