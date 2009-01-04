<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

// chargement des librairies
require_once dirname(__FILE__).'/class.plugin.php';
require_once dirname(__FILE__).'/class.modele.php';

// ajoute de templates
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

// ajout de la gestion des url
$core->url->register('newsletter', 'newsletter', '^newsletter/(.+)$', array('urlNewsletter', 'newsletter'));

// widget
class WidgetsNewsletter
{
	/**
	* initialisation du widget
	*/
    static public function widget($w)
    {
		global $core;
		try
		{
			// prise en compte de l'état d'activation du plugin
			if (!pluginNewsletter::isActive()) return;

			// prise en compte paramètre: uniquement sur la page d'accueil
			$url = &$core->url;
	        if ($w->homeonly && $url->type != 'default') return;

			// paramétrage des variables
			$plugin_name = __('Newsletter');
			$title = ($w->title) ? html::escapeHTML($w->title) : $plugin_name;
			$showTitle = ($w->showtitle) ? true : false;
			$subtitle = ($w->subtitle) ? html::escapeHTML($w->subtitle) : __('Subscribe');
			$text = '';

			// mise en place du contenu du widget dans $text
	        if ($w->inwidget)
	        {
                $link = dcNewsletter::url('submit');
	            $text .=
					'<form action="'.$link.'" method="post" id="nl_form">'.
					'<fieldset>'.
						$core->formNonce().
						form::hidden(array('nl_random'),dcNewsletter::getRandom()).
						
						'<label for="nl_email">'.__('Email:').'</label>'.
						form::field(array('nl_email','nl_email'),30,255);

						if (pluginNewsletter::getCaptcha())
						{
							require_once dirname(__FILE__).'/class.captcha.php';							
							
							$as = new Captcha(80, 30, 5);
							$as->generate();
							$as->file();
							$as->write();
						
							$text .=
							'<label for="nl_captcha">'.__('Captcha:').'</label>'.
							form::field(array('nl_captcha','nl_captcha'),30,255, '', 'style="width:90px; vertical-align:top;').
							'<img src="'.Captcha::www().'/captcha.img.png" style="vertical-align: middle;" alt="'.__('Captcha').'" />';
						}

	            $text .=
						'<label>'.form::radio(array('nl_option'),'subscribe', true).__('Subscribe').'</label><br />'.
						'<label>'.form::radio(array('nl_option'),'unsubscribe').__('Unsubscribe').'</label><br />'.
						'<input type="submit" name="nl_submit" id="nl_submit" value="'.__('Ok').'" />'.
					'</fieldset>'.
					'</form>';
	        }
	        else
	        {
                $link = dcNewsletter::url('form');
	            if ($w->insublink)
	            {
	                $title = str_replace('%I', '<img src="?pf=newsletter/icon.png" alt="" width="16" height="16" style="margin: 0 3px; vertical-align: middle;" />', $title);
	                $text = $w->text ? $w->text : '';

	                $text .= '<ul><li><a href="'.$link.'">'.$subtitle.'</a></li></ul>';
	            }
	            else
	            {
	                $title = '<a href="'.$link.'">'.$title.'</a>';
	                $title = str_replace('%I', '</a><img src="?pf=newsletter/icon.png" alt="newsletter" width="16" height="16" style="margin:0 3px; vertical-align:middle;" /> <a href="'.$link.'">', $title);
	            }
	        }

			
			// renvoi du code à afficher pour le widget
	        if ($showTitle === true) $title = '<h2>'.$title.'</h2>';
	        else $title = '';
	        return "\n".'<div id="'.pluginNewsletter::pname().'">'.$title.$text.'</div>'."\n";
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
}

// gestionnaire d'url
class urlNewsletter extends dcUrlHandlers
{
    // gestion des paramètres
    static public function newsletter($args)
    {
		global $core;
		try
		{
			// tests des arguments
	        if (empty($args) || $args == '') self::p404();

			// initialisation des variables
			$tpl = &$core->tpl;
			$cmd = null;
            $GLOBALS['newsletter']['cmd'] = null;
            $GLOBALS['newsletter']['msg'] = false;
            $GLOBALS['newsletter']['form'] = false;
            $GLOBALS['newsletter']['email'] = null;
            $GLOBALS['newsletter']['code'] = null;

			// préparation de l'utilisation du moteur de template
			$tpl->setPath($tpl->getPath(), dirname(__FILE__));
			$file = $tpl->getFilePath('newsletter.html');

			// décomposition des arguments et aiguillage
	        $params = explode('/', $args);
			if (isset($params[0]) && !empty($params[0])) $cmd = (string)html::clean($params[0]);
			else $cmd = null;
	        if (isset($params[1]) && !empty($params[1])) $email = base64_decode( (string)html::clean($params[1]) );
			else $email = null;
	        if (isset($params[2]) && !empty($params[2])) $regcode = (string)html::clean($params[2]);
			else $regcode = null;			
            switch ($cmd)
			{
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
				{
					if ($email == null) self::p404();
					$GLOBALS['newsletter']['msg'] = true;
				}
				break;
			}

			$GLOBALS['newsletter']['cmd'] = $cmd;
			$GLOBALS['newsletter']['email'] = $email;
			$GLOBALS['newsletter']['code'] = $regcode;

			// utilise le moteur de template pour générer la page pour le navigateur
			files::touch($file);
			header('Pragma: no-cache');
			header('Cache-Control: no-cache');
            self::serveDocument('newsletter.html', 'text/html', false, false);
            exit;
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
    }
}

?>
