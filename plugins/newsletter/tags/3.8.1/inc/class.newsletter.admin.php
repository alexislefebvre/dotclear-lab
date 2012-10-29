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

/** ==================================================
	administration
================================================== */

class newsletterAdmin
{
	/**
	* uninstall plugin
	*/
	public static function uninstall()
	{
		// delete schema
		global $core;
		try {
			// delete parameters
			newsletterPlugin::deleteSettings();
			newsletterPlugin::deleteVersion();
			newsletterPlugin::deleteTableNewsletter();
			
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* export the schema content
	*/
	public static function exportToBackupFile($onlyblog = true, $format = 'dat', $outfile = null)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;

			// generate the content of file from data
			if (isset($outfile)) {
				$filename = $outfile;
			} else {
				if ($onlyblog)
					$filename = $core->blog->public_path.'/'.$blogid.'-'.newsletterPlugin::pname().'.'.$format;
				else 
					$filename = $core->blog->public_path.'/'.newsletterPlugin::pname().'.'.$format;
			}

			$content = '';
			$datas = newsletterCore::getRawDatas($onlyblog);
			if (is_object($datas) !== FALSE) {
				$datas->moveStart();
				
				if($format == 'txt') {
					while ($datas->fetch())
					{
						$elems = array();
	
						// generate component
						$elems[] = $datas->subscriber_id;
						$elems[] = $datas->blog_id;
						$elems[] = $datas->email;
						$elems[] = $datas->regcode;
						$elems[] = $datas->state;
						$elems[] = $datas->subscribed;
						$elems[] = $datas->lastsent;
						$elems[] = $datas->modesend;
	
						$line = implode(";", $elems);
	                    $content .= "$line\n";
					}
				} else {
					while ($datas->fetch())
					{
						$elems = array();
	
						// generate component
						$elems[] = $datas->subscriber_id;
						$elems[] = base64_encode($datas->blog_id);
						$elems[] = base64_encode($datas->email);
						$elems[] = base64_encode($datas->regcode);
						$elems[] = base64_encode($datas->state);
						$elems[] = base64_encode($datas->subscribed);
						$elems[] = base64_encode($datas->lastsent);
						$elems[] = base64_encode($datas->modesend);
	
						$line = implode(";", $elems);
	                    $content .= "$line\n";
					}
				}
			}

			// write in file
			if(@file_put_contents($filename, $content)) {
				return $msg = __('Datas exported in file').' '.$filename;
			} else {
				throw new Exception(__('Error during export'));
			}
			
			
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* import a backup file
	*/
	public static function importFromBackupFile($infile = null)
	{
		global $core;

		$blog = &$core->blog;
		$blogid = (string)$blog->id;
		$counter=0;
		$counter_ignore=0;
		$counter_failed=0;

		try {
			if (!empty($infile)){
        		//$core->error->add('Traitement du fichier ' . $infile);

				if(file_exists($infile) && is_readable($infile)) {
					$file_content = file($infile);		
		
					foreach($file_content as $ligne) {
						// explode line
						$line = (string) html::clean((string) $ligne);
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
				
						if (!text::isEmail($email)) {
							$core->error->add(html::escapeHTML($email).' '.__('is not a valid email address.'));
							$counter_failed++;
						} else {
							try {
							if(newsletterCore::add($email, $blog_id, $regcode, $modesend)) {
								$subscriber = newsletterCore::getEmail($email);
								if ($subscriber != null) {
								//	$core->error->add('id : '.$subscriber->subscriber_id);
									newsletterCore::update($subscriber->subscriber_id, $email, $state, $regcode, $subscribed, $lastsent, $modesend);
								}								
								$counter++;
							} else
								$counter_ignore++;
							} catch (Exception $e) { 
								 $counter_ignore++;
							} 
						}
					}				

					// message de retour
					if(0 == $counter || 1 == $counter) {
						$retour = $counter . ' ' . __('email inserted');
					} else {
						$retour = $counter . ' ' . __('emails inserted');
					}
					if(0 == $counter_ignore || 1 == $counter_ignore) {
						$retour .= ', ' . $counter_ignore . ' ' . __('email ignored');
					} else {
						$retour .= ', ' . $counter_ignore . ' ' . __('emails ignored');
					}
					if(1 == $counter_failed) {
						$retour .= ', ' . $counter_failed . ' ' . __('line incorrect');
					} else {
						$retour .= ', ' . $counter_failed . ' ' . __('lines incorrect');
					}				

					return $retour;					
				} else {
					throw new Exception(__('No file to read.'));
				}
			} else {
				throw new Exception(__('No file to read.'));
			}				
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}

	/**
	* import email addresses from a file
	*/
	public static function importFromTextFile($infile = null)
	{
		global $core;
		try {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;
			$counter=0;
			$counter_ignore=0;
			$counter_failed=0;
			$tab_mail=array();
			
			$newsletter_settings = new newsletterSettings($core);
			$modesend = $newsletter_settings->getSendMode();
                
 			if (!empty($infile)){
        		//$core->error->add('Traitement du fichier ' . $infile['name']);
				files::uploadStatus($infile);
				$filename = $infile['tmp_name'];
			
				if(file_exists($filename) && is_readable($filename)) {
					$file_content = file($filename);

					foreach($file_content as $ligne) {
						$tab_mail=newsletterTools::extractEmailsFromString($ligne);
						
						foreach($tab_mail as $an_email) {
							$email = trim($an_email);
							if (!text::isEmail($email)) {
								$core->error->add(html::escapeHTML($email).' '.__('is not a valid email address.'));
								$counter_failed++;
							} else {
								$regcode = newsletterTools::regcode();
								try {
								if(newsletterCore::add($email, $blog_id, $regcode, $modesend))
									$counter++;
								else
									$counter_ignore++;
								} catch (Exception $e) { 
									 $counter_ignore++;
								} 
							}
						}
					}
					
					// message de retour
					if(0 == $counter || 1 == $counter) {
						$retour = $counter . ' ' . __('email inserted');
					} else {
						$retour = $counter . ' ' . __('emails inserted');
					}
					if(0 == $counter_ignore || 1 == $counter_ignore) {
						$retour .= ', ' . $counter_ignore . ' ' . __('email ignored');
					} else {
						$retour .= ', ' . $counter_ignore . ' ' . __('emails ignored');
					}
					if(1 == $counter_failed) {
						$retour .= ', ' . $counter_failed . ' ' . __('line incorrect');
					} else {
						$retour .= ', ' . $counter_failed . ' ' . __('lines incorrect');
					}				
					
					return $retour;
				} else {
					throw new Exception(__('No file to read.'));
				}
			} else {
				throw new Exception(__('No file to read.'));
			}
		} catch (Exception $e) { 
			$core->error->add($e->getMessage()); 
		}
	}
    
	/**
	* formulaire d'adaptation de template
	*/
	public static function adaptTheme($theme = null)
	{
		if ($theme == null) 
			echo __('No template to adapt.');
		else {
			global $core;
			try {
				$blog = &$core->blog;
				
				// fichier source
				$sfile = 'home.html';
				$source = $blog->themes_path.'/'.$theme.'/tpl/'.$sfile;
				
				// fichier de template
				$tfile = 'template.newsletter.html';
				$template = dirname(__FILE__).'/../default-templates/'.$tfile;
						
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
							$patterns[6] = '/<h2 class=\"post-title\">{{tpl:NewsletterPageTitle encode_html=\"1\"}}<\/h2>/';
							$replacements[6] = '';
							break;
						}
						case 'hybrid':
						{
							// traitement du theme particulier hybrid
							$patterns[0] = '/<div id=\"maincontent\">[\S\s]*<div id=\"sidenav\"/';
							$replacements[0] = '<div class="maincontent">'."\n".$tcontent."\n".'</div>'."\n".'</div>'."\n".'<div id="sidenav"';
							$patterns[1] = '/<title>.*<\/title>/';
							$replacements[1] = '<title>{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}</title>';
							$patterns[2] = '/dc-home/';
							$replacements[2] = 'dc-newsletter';
							$patterns[3] = '/<script type=\"text\/javascript\">[\S\s]*<\/script>/';
							$replacements[3] = '';
							$patterns[4] = '/<meta name=\"dc.title\".*\/>/';
							$replacements[4] = '<meta name="dc.title" content="{{tpl:NewsletterPageTitle encode_html="1"}} - {{tpl:BlogName encode_html="1"}}" />';
							$patterns[5] = '/<h2 class=\"post-title\">{{tpl:NewsletterPageTitle encode_html=\"1\"}}<\/h2>/';
							$replacements[5] = '<div id="content-info">'."\n".'<h2>{{tpl:NewsletterPageTitle encode_html="1"}}</h2>'."\n".'</div>'."\n".'<div class="content-inner">';
							$patterns[6] = '/<div id=\"sidenav\">[\S\s]*<!-- end #sidenav -->/';
							$replacements[6] = '<div id="sidenav">'."\n".'</div>'."\n".'<!-- end #sidenav -->';
							$patterns[7] = '/<tpl:Categories>[\S\s]*<link rel=\"alternate\"/';
							$replacements[7] = '<link rel=alternate"';
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

					// Writing new template file
					if ((@file_exists($dest) && @is_writable($dest)) || @is_writable($blog->themes_path)) {
	                    	$fp = @fopen($dest, 'w');
	                    	@fputs($fp, $scontent);
	                    	@fclose($fp);
	                    	$msg = __('Template created.');
	                	} else {
	                		$msg = __('Unable to write file.');
	                	}

					//@file_put_contents($dest,$scontent);
					$msg = __('Template created.');

				}

				return $msg;
			} catch (Exception $e) { 
				$core->error->add($e->getMessage()); 
			}
		}
	}    
}


?>