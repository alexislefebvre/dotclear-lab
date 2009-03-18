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

// gestionnaire d'url
class urlNewsletter extends dcUrlHandlers
{
    // gestion des paramètres
    public static function newsletter($args)
    {
		global $core;
		try	{
			// tests des arguments
	      	if (empty($args) || $args == '') {
	      		self::p404();
	      	}

			// initialisation des variables
			$tpl = &$core->tpl;
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
	      		$email = base64_decode( (string)html::clean($params[1]) );
	      	}
			else 
				$email = null;
	      
	      	if (isset($params[2]) && !empty($params[2])) 
	      		$regcode = (string)html::clean($params[2]);
			else 
				$regcode = null;			

	      	if (isset($params[3]) && !empty($params[3])) 
	      		$modesend = base64_decode( (string)html::clean($params[3]) );
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
						if ($email == null) 
							self::p404();
						$GLOBALS['newsletter']['msg'] = true;
					}
					break;
			}

			$GLOBALS['newsletter']['cmd'] = $cmd;
			$GLOBALS['newsletter']['email'] = $email;
			$GLOBALS['newsletter']['code'] = $regcode;
			$GLOBALS['newsletter']['modesend'] = $modesend;

			// préparation de l'utilisation du moteur de template
			$tpl->setPath($tpl->getPath(), dirname(__FILE__).'/default-templates');
			$file = $tpl->getFilePath('subscribe.newsletter.html');

			// utilise le moteur de template pour générer la page pour le navigateur
			files::touch($file);

			header('Pragma: no-cache');
			header('Cache-Control: no-cache');
	        	self::serveDocument('subscribe.newsletter.html','text/html',false,false);
	        	exit;
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
    }
}

?>
