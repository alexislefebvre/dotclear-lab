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

/** ==================================================
	administration
================================================== */

class adminNewsletter
{
	/**
	* installation du plugin
	*/
	public static function Install()
	{
		// test de possibilité d'installation
		if (!dcNewsletter::isAllowed()) 
			return false;

		// création du schéma
		global $core;
		try {
			// création du schéma de la table
			$_s = new dbStruct($core->con, $core->prefix);
			require dirname(__FILE__).'/db-schema.php';

			$si = new dbStruct($core->con, $core->prefix);
			$changes = $si->synchronize($_s);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

		// activation des paramètres par défaut
		pluginNewsletter::defaultsSettings();

		return true;
	}

	/**
	* désinstallation du plugin
	*/
	public static function Uninstall()
	{
		// désactivation du plugin et sauvegarde de toute la table
		pluginNewsletter::Inactivate();
		adminNewsletter::Export(false);

		// suppression du schéma
		global $core;
		try {
			$con = &$core->con;

			$strReq =
				'DROP TABLE '.
				$core->prefix.pluginNewsletter::pname();

			$rs = $con->execute($strReq);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}

		// suppression des paramètres par défaut
		pluginNewsletter::deleteSettings();
		pluginNewsletter::delete_version();
	}

	/**
	* export du contenu du schéma
	*/
	public static function Export($onlyblog = true, $outfile = null)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;

			// générer le contenu du fichier à partir des données
			if (isset($outfile)) {
				$filename = $outfile;
			} else {
				if ($onlyblog) 
					$filename = $core->blog->public_path.'/'.$blogid.'-'.pluginNewsletter::pname().'.dat';
				else 
					$filename = $core->blog->public_path.'/'.pluginNewsletter::pname().'.dat';
			}

			$content = '';
			$datas = dcNewsletter::getRawDatas($onlyblog);
			if (is_object($datas) !== FALSE) {
				$datas->moveStart();
				while ($datas->fetch())
				{
					$elems = array();

					// génération des élements de données
					$elems[] = $datas->subscriber_id;
					$elems[] = base64_encode($datas->blog_id);
					$elems[] = base64_encode($datas->email);
					$elems[] = base64_encode($datas->regcode);
					$elems[] = base64_encode($datas->state);
					$elems[] = base64_encode($datas->subscribed);
					$elems[] = base64_encode($datas->lastsent);
					$elems[] = base64_encode($datas->modesend);

					// génération de la ligne de données exportées(séparateur -> ;)
					$line = implode(";", $elems);
                    	$content .= "$line\n";
				}
			}

			// écrire le contenu dans le fichier
			@file_put_contents($filename, $content);
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* import du contenu du schéma
	*/
	public static function Import($onlyblog = true, $infile = null)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;
        
			// lire le contenu du fichier à partir des données
			if (isset($infile)) 
				$filename = $infile;
			else {
				if ($onlyblog) 
					$filename = $core->blog->public_path.'/'.$blogid.'-'.pluginNewsletter::pname().'.dat';
				else 
					$filename = $core->blog->public_path.'/'.pluginNewsletter::pname().'.dat';
			}

			// ouverture du fichier
			$content = '';
			$fh = @fopen($filename, "r");
			if ($fh === FALSE) 
				return false;
			else {
				// boucle de lecture sur les lignes du fichier
				$err = false;
				while (!feof($fh)) {
					// lecture d'une ligne du fichier
					$l = @fgetss($fh, 4096);
					if ($l != FALSE) {
						// sécurisation du contenu de la ligne et décomposition en élements (séparateur -> ;)
						$line = (string) html::clean((string) $l);
						$elems = explode(";", $line);

						// traitement des données lues
						$subscriber_id = $elems[0];
						$blog_id = base64_decode($elems[1]);
						$email = base64_decode($elems[2]);
						$regcode = base64_decode($elems[3]);
						$state = base64_decode($elems[4]);
						$subscribed = base64_decode($elems[5]);
						$lastsent = base64_decode($elems[6]);
						$modesend = base64_decode($elems[7]);

						dcNewsletter::add($email, $regcode, $modesend);
						$id = dcNewsletter::getEmail($email);
						if ($id != null) {
							dcNewsletter::update($id, null, $state, $null, $subscribed, $lastsent, $modesend);
						}
					}
				}

				// fermeture du fichier
				@fclose($fh);

				if ($err) 
					return false;
				else 
					return true;
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
    
	/**
	* formulaire d'adaptation de template
	*/
	public static function Adapt($theme = null)
	{
		// prise en compte du plugin installé
		if (!pluginNewsletter::isInstalled()) 
			return;

		if ($theme == null) 
			echo __('No template to adapt.');
		else {
			global $core;
			try {
			
				$blog = &$core->blog;
				$settings = &$blog->settings;

				// fichier source
				//$sfile = 'post.html';
				$sfile = 'home.html';
				$source = $blog->themes_path.'/'.$theme.'/tpl/'.$sfile;

				// fichier de template
				$tfile = 'template.newsletter.html';
				$template = dirname(__FILE__).'/default-templates/'.$tfile;
						
				// fichier destination
				$dest = $blog->themes_path.'/'.$theme.'/tpl/'.'subscribe.newsletter.html';
				
				
				if (!@file_exists($source)) {			// test d'existence de la source
					$msg = $sfile.' '.__('is not in your theme folder.').' ('.$blog->themes_path.')';
					$core->error->add($msg);
					return;
				} else if (!@file_exists($template)) { 	// test d'existence du template source
					$msg = $tfile.' '.__('is not in the plugin folder.').' ('.dirname(__FILE__).')';
					$core->error->add($msg);
					return;
				} else if (!@is_readable($source)) { 	// test si le fichier source est lisible
					$msg = $sfile.' '.__('is not readable.');
					$core->error->add($msg);
					return;
				} else {
					// lecture du contenu des fichiers template et source
					$tcontent = @file_get_contents($template);
					$scontent = @file_get_contents($source);
					
					// definition des remplacements
					switch ($theme) {
						case 'noviny':
						{
							// traitement du theme particulier noviny
							$patterns[0] = '/<div id=\"overview\" class=\"grid-l\">[\S\s]*<div id=\"extra\"/';
							$replacements[0] = '<div class="grid-l">'. "\n" .'<div class="post">'. "\n" . $tcontent . "\n" .'</div>'. "\n" . '</div>'. "\n" .'<div id="extra"';
							$patterns[1] = '/<title>.*<\/title>/';
							$replacements[1] = '<title>{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}</title>';
							$patterns[2] = '/dc-home/';
							$replacements[2] = 'dc-newsletter';
							$patterns[3] = '/<meta name=\"dc.title\".*\/>/';
							$replacements[3] = '<meta name="dc.title" content="{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}" />';
							$patterns[4] = '/<div id=\"lead\" class="grid-l home-lead">[\S\s]*<div id=\"meta\"/';
							$replacements[4] = '<div id="lead" class="grid-l">'. "\n\t" .'<h2>{{tpl:NewsletterPageTitle encode_html="1"}}</h2>'. "\n\t" .'</div>'. "\n\t" . '<div id="meta"';
							$patterns[5] = '/<div id=\"meta\" class=\"grid-s\">[\S\s]*{{tpl:include src=\"inc_meta.html\"}}/';
							$replacements[5] = '<div id="meta" class="grid-s">'. "\n\t" .'{{tpl:include src="inc_meta.html"}}';
							break;
						}
						default:
						{
							$patterns[0] = '/<tpl:Entries>[\S\s]*<\/tpl:Entries>/';
							$replacements[0] = $tcontent;
							$patterns[1] = '/<title>.*<\/title>/';
							$replacements[1] = '<title>{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}</title>';
							$patterns[2] = '/dc-home/';
							$replacements[2] = 'dc-newsletter';
							$patterns[3] = '/<meta name=\"dc.title\".*\/>/';
							$replacements[3] = '<meta name="dc.title" content="{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}" />';
							$patterns[4] = '/<tpl:Entries no_content=\"1\">[\S\s]*<\/tpl:Entries>/';
							$replacements[4] = '';
						}
					}


					$count = 0;
					$scontent = preg_replace($patterns, $replacements, $scontent, 1, $count);
					//$core->error->add('Nombre de remplacements : ' . $count); 

					// suppression des lignes vides et des espaces de fin de ligne
					///*
					$a2 = array();
					$tok = strtok($scontent, "\n\r");
					while ($tok !== FALSE)
					{
						$l = rtrim($tok);
						if (strlen($l) > 0)
						    $a2[] = $l;
						$tok = strtok("\n\r");
					}
					$c2 = implode("\n", $a2);
					$scontent = $c2;
					//*/

					// écriture du fichier de template
					/*
					if ((@file_exists($dest) && @is_writable($dest)) || @is_writable($blog->themes_path)) {
	                    	$fp = @fopen($dest, 'w');
	                    	@fputs($fp, $content);
	                    	@fclose($fp);
	                    	$msg = __('Template created.');
	                	} else {
	                		$msg = __('Unable to write file.');
	                	}
	                	*/

					@file_put_contents($dest,$scontent);
					$msg = __('Template created.');

				}

				return $msg;
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}    
}

/** ==================================================
	onglets de la partie d'administration
================================================== */

class tabsNewsletter
{
	/**
	* paramétrage du plugin
	*/
	public static function Settings()
	{
		// prise en compte du plugin installé
		if (!dcNewsletter::isInstalled()) 
			return;

		global $core;
		try {
			$blog = &$core->blog;
			$auth = &$core->auth;

			$mode_combo = array(__('text') => 'text',
							__('html') => 'html');

			// paramétrage de l'état d'activation du plugin
			if (pluginNewsletter::isActive()) 
				$pactive = 'checked';
			else 
				$pactive = '';

			if ($auth->isSuperAdmin())
				$sadmin = true;
			else 
				$sadmin = false;

			$feditorname = pluginNewsletter::getEditorName();
			$feditoremail = pluginNewsletter::getEditorEmail();
			$f_introductory_msg = pluginNewsletter::getIntroductoryMsg();
			$f_concluding_msg = pluginNewsletter::getConcludingMsg();
			$f_msg_presentation_form = pluginNewsletter::getMsgPresentationForm();
			$f_presentation_msg = pluginNewsletter::getPresentationMsg();
			$f_presentation_posts_msg = pluginNewsletter::getPresentationPostsMsg();
			$fmode = pluginNewsletter::getSendMode();
			$fmaxposts = pluginNewsletter::getMaxPosts();
			$fautosend = pluginNewsletter::getAutosend();
			$fcaptcha = pluginNewsletter::getCaptcha();
			$f_view_content_post = pluginNewsletter::getViewContentPost();
			$f_size_content_post = pluginNewsletter::getSizeContentPost();
			
			$f_txt_intro_confirm = pluginNewsletter::getTxtIntroConfirm();
			$f_txtConfirm = pluginNewsletter::getTxtConfirm();
			$f_txt_intro_disable = pluginNewsletter::getTxtIntroDisable();
			$f_txtDisable = pluginNewsletter::getTxtDisable();
			$f_txt_intro_enable = pluginNewsletter::getTxtIntroEnable();
			$f_txtEnable = pluginNewsletter::getTxtEnable();
			$f_txt_intro_suspend = pluginNewsletter::getTxtIntroSuspend();
			$f_txtSuspend = pluginNewsletter::getTxtSuspend();

			/* for 3.5.1 : add period
			$fperiod = pluginNewsletter::getPeriod();
			//*/
			
			echo

			'<form action="plugin.php" method="post" name="state">'.
				'<fieldset>'.
					'<legend>'. __('Plugin state').'</legend>'.
					'<p class="field">'.
					form::checkbox('active', 1, $pactive).
					'<label class="classic" for="active">'.__('Activate plugin').'</label>'.
					'</p>'.
				'</fieldset>'.
				'<p>'.
					'<input type="submit" value="'.__('Save').'" /> '.
					'<input type="reset" value="'.__('Cancel').'" /> '.
				'</p>'.
				$core->formNonce().
				form::hidden(array('p'),pluginNewsletter::pname()).
				form::hidden(array('op'),'state').
			'</form>';
			
			if (pluginNewsletter::isActive()) {
			
				// gestion des paramètres du plugin
				echo	
				'<form action="plugin.php" method="post" name="settings">'.
					
					'<fieldset>'.
						'<legend>'. __('Settings').'</legend>'.
	
						'<table class="minimal dragable">'.
						'<thead>'.
						'<tr>'.
	  						'<th>'.__('Name').'</th>'.
	  						'<th>'.__('Value').'</th>'.
						'</tr>'.
						'</thead>'.
						'<tbody id="classes-list">'.

						'<tr class="line">'.
						'<td><label class="required" title="'.__('Required field').'">'. __('Editor name').'</td>'.
						'<td>'.form::field(array('feditorname'), 50,255, $feditorname).'</td>'.
						'</tr>'.
						'<tr class="line">'.
						'<td><label class="required" title="'.__('Required field').'">'. __('Editor email').'</td>'.
						'<td>'.form::field(array('feditoremail'), 50,255, $feditoremail).'</td>'.
						'</tr>'.
						'<tr class="line">'.
						'<td><label class="classic">'. __('Presentation message').'</td>'.
						'<td>'.form::field(array('f_presentation_msg'), 50,255, $f_presentation_msg).'</td>'.
						'</tr>'.
						'<tr class="line">'.
						'<td>'. __('Presentation message for posts').'</td>'.
						'<td>'.form::field(array('f_presentation_posts_msg'), 50,255, $f_presentation_posts_msg).'</td>'.
						'</tr>'.
						'</tr>'.
						'<tr class="line">'.
						'<td>'. __('Introductory confirm message').'</td>'.
						'<td>'.form::field(array('f_txt_intro_confirm'), 50,255, $f_txt_intro_confirm).'</td>'.
						'</tr>'.
						'<tr class="line">'.
						'<td>'. __('Title confirmation link').'</td>'.
						'<td>'.form::field(array('f_txtConfirm'), 50,255, $f_txtConfirm).'</td>'.
						'</tr>'.
						'<tr class="line">'.
						'<td>'. __('Introductory disable message').'</td>'.
						'<td>'.form::field(array('f_txt_intro_disable'), 50,255, $f_txt_intro_disable).'</td>'.
						'</tr>'.
						'<tr class="line">'.
						'<td>'. __('Title disable link').'</td>'.
						'<td>'.form::field(array('f_txtDisable'), 50,255, $f_txtDisable).'</td>'.
						'</tr>'.
						'<tr class="line">'.
						'<td>'. __('Introductory enable message').'</td>'.
						'<td>'.form::field(array('f_txt_intro_enable'), 50,255, $f_txt_intro_enable).'</td>'.
						'</tr>'.
						'<tr class="line">'.
						'<td>'. __('Title enable link').'</td>'.
						'<td>'.form::field(array('f_txtEnable'), 50,255, $f_txtEnable).'</td>'.
						'</tr>'.
						'<tr class="line">'.
						'<td>'. __('Introductory suspend message').'</td>'.
						'<td>'.form::field(array('f_txt_intro_suspend'), 50,255, $f_txt_intro_suspend).'</td>'.
						'</tr>'.
						'<tr class="line">'.
						'<td>'. __('Title suspend link').'</td>'.
						'<td>'.form::field(array('f_txtSuspend'), 50,255, $f_txtSuspend).'</td>'.
						'</tr>'.

						'</tbody>'.
						'</table>'.
						
						'<p class="area"><label>'. __('Introductory message').' : '.
						form::textarea(array('f_introductory_msg'),30,4,html::escapeHTML($f_introductory_msg)).
						'</label></p>'.
						'<p class="area"><label class="classic">'. __('Concluding message').' : '.
						form::textarea(array('f_concluding_msg'),30,4, html::escapeHTML($f_concluding_msg)).
						'</label></p>'.

						'<p class="area"><label>'. __('Message presentation form').' : '.
						form::textarea(array('f_msg_presentation_form'),30,4,html::escapeHTML($f_msg_presentation_form)).
						'</label></p>'.

						/* for 3.5.1 : add period
						'<p class="field">'.
						form::checkbox('fperiod',1,$fperiod).
						'<label class="classic" for="fperiod">'.__('Period sending').
						'</label></p>'.
						*/

				'</fieldset>'.

				'<fieldset>'.
					'<legend>'. __('Advanced Settings').'</legend>'.
						'<p class="field">'.
						form::checkbox('fcaptcha',1,$fcaptcha).
						'<label class="classic" for="fcaptcha">'.__('Captcha').'</label></p>'.
						'<p><label class="classic" for="fmode">'. __('Mode').' : '.
						form::combo(array('fmode'), $mode_combo, $fmode).
						'</label></p>'.
						'<p class="field">'.
						form::checkbox('fautosend',1,$fautosend).
						'<label class="classic" for="fautosend">'.__('Automatic send').
						'</label></p>'.
						'<p><label class="classic" for="fmaxposts">'. __('Maximum posts').' : '.
						form::field(array('fmaxposts'),4,4, $fmaxposts).
						'</label></p>'.
						'<p class="field">'.
						form::checkbox('f_view_content_post',1,$f_view_content_post).
						'<label class="classic" for="f_view_content_post">'.__('View contents posts').
						'</label></p>'.
						'<p><label class="classic" for="f_size_content_post">'. __('Size contents posts').' : '.
						form::field(array('f_size_content_post'),4,4, $f_size_content_post).
						'</label></p>'.
				'</fieldset>'.

				// boutons du formulaire
				'<p>'.
					'<input type="submit" name="save" value="'.__('Save').'" /> '.
					'<input type="reset" name="reset" value="'.__('Cancel').'" /> '.
					'<input type="button" value="'.__('Defaults').'" onclick="pdefaults(); return false" />'.
				'</p>'.
	               $core->formNonce().
				form::hidden(array('p'),pluginNewsletter::pname()).
				form::hidden(array('op'),'settings').
				'</form>';
			}
			
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* Maintenance du plugin
	*/
	public static function Maintenance()
	{
		// prise en compte du plugin installé
		if (!dcNewsletter::isInstalled()) 
			return;

		global $core;
		try {
			$blog = &$core->blog;
			$auth = &$core->auth;
			$url = &$core->url;
			$themes = &$core->themes;
			$blogurl = &$blog->url;
			$urlBase = http::concatURL($blogurl, $url->getBase('newsletter'));			

			//$core->themes = new dcModules($core);
			$core->themes = new dcThemes ($core);
			$core->themes->loadModules($blog->themes_path, NULL);
			//$theme = $blog->settings->theme;
			$theme = $themes->getModules($theme);
			$bthemes = array();
			foreach ($themes->getModules() as $k => $v)
			{
				if (file_exists($blog->themes_path . '/' . $k . '/tpl/post.html'))
					$bthemes[html::escapeHTML($v['name'])] = $k;
			}			

			if (pluginNewsletter::isActive()) {

				$sadmin = (($auth->isSuperAdmin()) ? true : false);
	
				echo
				// export/import pour le blog
				'<form action="plugin.php" method="post" name="impexp">'.
					'<fieldset>' .
						'<legend>'.__('Import/Export subscribers list').'</legend>'.
						'<p>'.
						'<label class="classic">'.
						form::radio(array('type'),'blog',(!$sadmin) ? true : false).__('This blog only').
						'</label>'.
						'<br />'.
						'<label class="classic">'.
						form::radio(array('type'),'all',($sadmin) ? true : false,'','',(!$sadmin) ? true : false).__('All datas').
						'</label>'.
						'</p>'.
						'<p>'.
						'<input type="submit" value="'.__('Export').'" />'.
						'<input type="button" value="'.__('Import').'" onclick="pimport(); return false" />'.
						form::hidden(array('p'),pluginNewsletter::pname()).
						form::hidden(array('op'),'export').
						$core->formNonce().					
						'</p>'.
					'</fieldset>'.
				'</form>'.
				
				// adaptation du template
				///*
				'<form action="plugin.php" method="post" name="adapt">'.
					'<fieldset>' .
						'<legend>'.__('Adapt the template for the theme').'</legend>'.
						'<p><label class="classic" for="fthemes">'. __('Theme name').' : '.
						form::combo('fthemes', $bthemes, $theme).
						'</label></p>'.
						'<p>'.
						'<input type="submit" value="'.__('Adapt').'" />'.
						form::hidden(array('p'),pluginNewsletter::pname()).
						form::hidden(array('op'),'adapt').
						$core->formNonce().					
						'</p>'.
						'<p>'.
						'<a href="'.$urlBase.'/test'.'">'.__('Clic here to test the template.').'</a>'.
						'</p>'.
					'</fieldset>'.
				'</form>'.
				//*/
				
				// gestion de la mise à jour
				/* désactivée => redondance avec dotaddict : à supprimer
				if ($sadmin)
				{
			        echo
					'<fieldset>' .
					'<legend>'.__('Check for plugin update').'</legend>'.
						'<form action="plugin.php" method="post" name="update">'.
	                        $core->formNonce().
							form::hidden(array('p'),pluginNewsletter::pname()).
							form::hidden(array('op'),'update').
							'<p></p>'.
							'<p>'.
								'<input type="submit" value="'.__('Check').'" />'.
							'</p>'.
						'</form>'.
					'</fieldset>'.
				}
				//*/				

				// Nettoyage de la base
				'<form action="plugin.php" method="post" name="erasingnewsletter">'.
					'<fieldset>' .
						'<legend>'.__('Erasing all informations about newsletter in database').'</legend>'.
						'<p>'.__('Be careful, please backup your database before erasing').
						'</p>'.
						'<p>'.
						'<input type="submit" value="'.__('Erasing').'" />'.
						form::hidden(array('p'),pluginNewsletter::pname()).
						form::hidden(array('op'),'erasingnewsletter').
						$core->formNonce().					
						'</p>'.
					'</fieldset>'.
				'</form>'.
				
				'';
				
			} else {
				echo
				'<fieldset>' .
					'<p><label class="classic">'. __('Activate plugin before using it').
					'</label></p>'.
				'</fieldset>';
			}

		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}


	/**
	* liste des abonnés du blog
	*/
	public static function ListBlog()
	{
		// prise en compte du plugin installé
		if (!dcNewsletter::isInstalled()) 
			return;

		$datas = dcNewsletter::getlist();
		if (!is_object($datas)) {
			echo __('No subscriber for this blog.');
		} else {
			global $core;
			try {
				$blog = &$core->blog;
				$settings = &$blog->settings;

				// début du tableau et en-têtes
				echo
				'<form action="plugin.php" method="post" name="listblog">' .
					$core->formNonce() .
					form::hidden(array('p'),pluginNewsletter::pname()).
					form::hidden(array('op'),'remove').
					form::hidden(array('id'),'').
					'<table class="clear" id="userslist">'.
						'<tr>'.
							'<th>&nbsp;</th>'.
							'<th class="nowrap">'.__('Subscriber').'</th>' .
							'<th class="nowrap">'.__('Subscribed').'</th>' .
							'<th class="nowrap">'.__('Status').'</th>' .
							'<th class="nowrap">'.__('Last sent').'</th>' .
							'<th class="nowrap">'.__('Mode send').'</th>' .
							'<th class="nowrap" colspan="2">'.__('Edit').'</th>'.
						'</tr>';

				// parcours la liste pour l'affichage
				$datas->moveStart();
				while ($datas->fetch()) {
					$k = (integer)$datas->subscriber_id;
					$editlink = 'onclick="ledit('.$k.'); return false"';
					$guilink = '<a href="#" '.$editlink.' title="'.__('Edit subscriber').'"><img src="images/edit-mini.png" alt="'.__('Edit subscriber').'" /></a>';

					if ($datas->subscribed != null) 
						$subscribed = dt::dt2str('%d/%m/%Y', $datas->subscribed).' '.dt::dt2str('%H:%M', $datas->subscribed);
					else 
						$subscribed = __('Never');
					
					if ($datas->lastsent != null) 
						$lastsent = dt::dt2str('%d/%m/%Y', $datas->lastsent).' '.dt::dt2str('%H:%M', $datas->lastsent);
					else 
						$lastsent = __('Never');

					echo
					'<tr class="line">'.
						'<td>'.form::checkbox(array('subscriber['.html::escapeHTML($k).']'), 1).'</td>'.
						'<td class="maximal nowrap">'.html::escapeHTML(text::cutString($datas->email, 50)).'</td>'.
						'<td class="minimal nowrap">'.html::escapeHTML(text::cutString($subscribed, 50)).'</td>'.
						'<td class="minimal nowrap">'.html::escapeHTML(text::cutString(__($datas->state), 50)).'</td>'.
						'<td class="minimal nowrap">'.html::escapeHTML(text::cutString($lastsent, 50)).'</td>'.
						'<td class="minimal nowrap">'.html::escapeHTML(text::cutString(__($datas->modesend), 10)).'</td>'.
						'<td class="status">'.$guilink.'</td>'.
					'</tr>';
				}

				$bstates = array();
				$bstates['-'] = '-';
				$bstates[__('Suspend')] = 'suspend';
				$bstates[__('Disable')] = 'disable';
				$bstates[__('Enable')] = 'enable';
				$bstates[__('Last sent')] = 'lastsent';

				$bmails = array();
				$bmails[__('Newsletter')] = 'send';
				$bmails[__('Confirmation')] = 'sendconfirm';
				$bmails[__('Suspension')] = 'sendsuspend';
				$bmails[__('Deactivation')] = 'senddisable';
				$bmails[__('Activation')] = 'sendenable';
				//$bmails[__('Changing format')] = 'sendchangemode';

				$bmodes = array();
				$bmodes['-'] = '-';
				$bmodes[__('html')] = 'changemodehtml';
				$bmodes[__('text')] = 'changemodetext';

				// fermeture du tableau
				echo
				'</table>'.
				'<p>'.
				'<a class="small" href="'.html::escapeHTML(pluginNewsletter::admin()).'">'.__('refresh').'</a> - ' .
				'<a class="small" href="#" onclick="checkAll(\'userslist\'); return false">'.__('check all').'</a> - ' .
				'<a class="small" href="#" onclick="uncheckAll(\'userslist\'); return false">'.__('uncheck all').'</a> - ' .
				'<a class="small" href="#" onclick="invertcheckAll(\'userslist\'); return false">'.__('toggle check all').'</a></p>'.			    

				'<p>'.
				'<input type="submit" value="'.__('Delete').'" /><br /><br />'.
				'<label for "fstates">'.__('Set state').'&nbsp;:</label>'.
				form::combo('fstates', $bstates).'<input type="button" value="'.__('Set').'" onclick="lset(); return false" />'.
				'<label for "fmails">'.__('Mail to send').'&nbsp;:</label>'.
				form::combo('fmails', $bmails).'<input type="button" value="'.__('Send').'" onclick="lsend(); return false" />'.
				///*
				'<label for "fmodes">'.__('Set format').'&nbsp;:</label>'.
				form::combo('fmodes', $bmodes).'<input type="button" value="'.__('Change').'" onclick="lchangemode(); return false" />'.
				//*/
				'</p></form>';
				
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}

	/**
	* formulaire d'ajout d'un abonné
	*/
	public static function AddEdit()
	{
		// prise en compte du plugin installé
		if (!pluginNewsletter::isInstalled()) return;

		global $core;
		try
		{
			$blog = &$core->blog;
			$settings = &$blog->settings;
			$allowed = true;
			$mode_combo = array(__('text') => 'text',
							__('html') => 'html');

			// test si ajout ou édition
			if (!empty($_POST['id']))
			{
				$id = (integer)$_POST['id'];
				$datas = dcNewsletter::get($id);
				if ($datas == null) 
					$allowed = false;
				else {
					$email = $datas->f('email');
					$subscribed = $datas->f('subscribed');
					$lastsent = $datas->f('lastsent');
					$modesend = $datas->f('modesend');
					$regcode = $datas->f('regcode');
					$state = $datas->f('state');
					$form_title = __('Edit a subscriber');
					$form_op = 'edit';
					$form_libel = __('Update');
					$form_id = '<input type="hidden" name="id" value="'.$id.'" />';

               		if ($subscribed != null) 
						$subscribed = dt::dt2str($settings->date_format, $subscribed).' @'.dt::dt2str($settings->time_format, $subscribed);
					else 
						$subscribed = __('Never');
               
					if ($lastsent != null) 
						$lastsent = dt::dt2str($settings->date_format, $lastsent).' @'.dt::dt2str($settings->time_format, $lastsent);
					else 
						$lastsent = __('Never');

					$form_update =
					'<br /><br /><label for "fsubscribed">'.__('Subscribed:').'</label>'.
					form::field(array('fsubscribed'),50,255, $subscribed,'','',true).
					'<br /><br /><label for "flastsent">'.__('Last sent:').'</label>'.
					form::field(array('flastsent'),50,255, $lastsent,'','',true).
					'<br /><br /><label for "fmodesend">'. __('Mode send').' : </label>'.
					form::combo(array('fmodesend'), $mode_combo, $modesend).
					'<br /><br /><label for "fregcode">'.__('Registration code:').'</label>'.					
					form::field(array('fregcode'),50,255, $regcode,'','',true).
					'<br /><br /><label for "fstate">'.__('Status:').'</label>'.
					'<label class="classic">'.form::radio(array('fstate'),'pending', $state == 'pending').__('pending').'</label><br />'.
					'<label class="classic">'.form::radio(array('fstate'),'enabled', $state == 'enabled').__('enabled').'</label><br />'.
					'<label class="classic">'.form::radio(array('fstate'),'suspended', $state == 'suspended').__('suspended').'</label><br />'.
					'<label class="classic">'.form::radio(array('fstate'),'disabled', $state == 'disabled').__('disabled').'</label><br />';

				}
			} else {
				$id = -1;
				$email = '';
				$subscribed = '';
				$lastsent = '';
				$modesend = '';
				$status = '';
				$form_title = __('Add a subscriber');
				$form_op = 'add';
				$form_libel = __('Save');
				$form_id = '';
				$form_update = '';
			}

			if (!$allowed) 
				echo __('Not allowed.');
			else
				echo
				'<fieldset>'.
					'<legend>'.$form_title.'</legend>'.
					'<form action="plugin.php" method="post" name="addedit">'.
						$core->formNonce().
						form::hidden(array('p'),pluginNewsletter::pname()).
						form::hidden(array('op'),$form_op).
						$form_id.
						'<p>'.
							'<label for "femail">'.__('Email:').'</label>'.
							form::field(array('femail'),50,255, $email).
							$form_update.
						'</p>'.
						'<p>'.
							'<input type="submit" value="'.$form_libel.'" />'.
							'<input type="reset" value="'.__('Cancel').'" />'.
						'</p>'.
					'</form>'.
				'</fieldset>';
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
}

?>
