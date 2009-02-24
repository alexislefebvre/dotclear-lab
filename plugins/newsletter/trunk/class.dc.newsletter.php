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
