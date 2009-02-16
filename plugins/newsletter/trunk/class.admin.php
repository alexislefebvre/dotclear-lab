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
		if (!dcNewsletter::isAllowed()) return false;

		// création du schéma
		global $core;
        try
        {
			// création du schéma de la table
		    $_s = new dbStruct($core->con, $core->prefix);
		    require dirname(__FILE__).'/db-schema.php';

		    $si = new dbStruct($core->con, $core->prefix);
		    $changes = $si->synchronize($_s);
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }

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
		pluginNewsletter::Export(false);

		// suppression du schéma
		global $core;
        try
        {
	        $con = &$core->con;

			$strReq =
				'DROP TABLE '.
				$core->prefix.pluginNewsletter::pname();

			$rs = $con->execute($strReq);
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }

		// suppression des paramètres par défaut
		pluginNewsletter::deleteSettings();
	}

	/**
	* export du contenu du schéma
	*/
	public static function Export($onlyblog = true, $outfile = null)
	{
		global $core;
        try
        {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;

			// générer le contenu du fichier à partir des données
			if (isset($outfile)) $filename = $outfile;
			else
			{
				if ($onlyblog) $filename = $core->blog->public_path.'/'.$blogid.'-'.pluginNewsletter::pname().'.dat';
				else $filename = $core->blog->public_path.'/'.pluginNewsletter::pname().'.dat';
			}

			$content = '';
			$datas = dcNewsletter::getRawDatas($onlyblog);
			if (is_object($datas) !== FALSE)
			{
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

					// génération de la ligne de données exportées(séparateur -> ;)
					$line = implode(";", $elems);
                    $content .= "$line\n";
				}
			}

			// écrire le contenu dans le fichier
			@file_put_contents($filename, $content);
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
 	}

	/**
	* export du contenu du schéma
	*/
	public static function Import($onlyblog = true, $infile = null)
	{
		global $core;
        try
        {
			$blog = &$core->blog;
			$blogid = (string)$blog->id;
        
			// lire le contenu du fichier à partir des données
			if (isset($infile)) $filename = $infile;
			else
			{
				if ($onlyblog) $filename = $core->blog->public_path.'/'.$blogid.'-'.pluginNewsletter::pname().'.dat';
				else $filename = $core->blog->public_path.'/'.pluginNewsletter::pname().'.dat';
			}

			// ouverture du fichier
	        $content = '';
            $fh = @fopen($filename, "r");
            if ($fh === FALSE) return false;
			else
			{
				// boucle de lecture sur les lignes du fichier
	            $err = false;
	            while (!feof($fh))
	            {
					// lecture d'une ligne du fichier
	                $l = @fgetss($fh, 4096);
	                if ($l != FALSE)
	                {
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

						dcNewsletter::add($email, $regcode);
						$id = dcNewsletter::getEmail($email);
						if ($id != null) dcNewsletter::update($id, null, $state, $null, $subscribed, $lastsent);
					}
				}

				// fermeture du fichier
	            @fclose($fh);

	            if ($err) return false;
				else return true;
			}
        }
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
    
	/**
	* formulaire d'adaptation de template
	*/
	public static function Adapt($theme = null)
	{
		// prise en compte du plugin installé
		if (!pluginNewsletter::isInstalled()) return;

        if ($theme == null) echo __('No template to adapt.');
        else
        {
			global $core;
			try
			{
				$blog = &$core->blog;
				$settings = &$blog->settings;

				// fichier source
	            $sfile = 'post.html';
	            $source = $blog->themes_path.'/'.$theme.'/'.$sfile;

				// fichier de template
	            $tfile = 'template.newsletter.html';
	            $template = dirname(__FILE__).'/'.$tfile;

				// fichier destination
	            $dest = $blog->themes_path.'/'.$theme.'/'.'newsletter.html';

				// test d'existance de la source
	            if (!@file_exists($source)) $msg = $sfile.' '.__('is not in your theme folder.').' ('.$blog->themes_path.')';

				// test d'existence du template source
				else if (!@file_exists($template)) $msg = $tfile.' '.__('is not in the plugin folder.').' ('.dirname(__FILE__).')';

				// test si le fichier source est lisible
	            else if (!@is_readable($source)) $msg = $sfile.' '.__('is not readable.');

				else
				{
					// lecture du contenu des fichiers template et source
					$tcontent = @file_get_contents($template);
					$scontent = @file_get_contents($source);

					// remplace les informations de template sur les entrées
	                $content = preg_replace('/<title>.*<\/title>/', '<title>{{tpl:NewsletterPageTitle}}</title>', $scontent);
	                $content = preg_replace('/<tpl:Entry((?!<tpl)[a-zA-Z0-9\s_:="<>{}\[\]\.\/\\-])*<\/tpl:Entry((?!<)[a-zA-Z0-9_-])*>/', '', $content);
	                $content = preg_replace('/{{tpl:PostUpdateViewCount}}/', '', $content);
	                $content = preg_replace('/{{tpl:Entry[a-zA-Z0-9\s_:-="]*}}/', '', $content);
	                $content = preg_replace('/<div id="main">[\S\s]*<div id="sidebar">/', '<div id="main">' . "\n" . '<div id="content">' . "\n\n" . $tcontent . "\n\n" . '</div>' . "\n" . '</div>' . "\n\n" . '<div id="sidebar">', $content);

					// suppression des lignes vides et des espaces de fin de ligne
	                $a2 = array();
	                $tok = strtok($content, "\n\r");
	                while ($tok !== FALSE)
	                {
	                    $l = rtrim($tok);
	                    if (strlen($l) > 0)
	                        $a2[] = $l;
	                    $tok = strtok("\n\r");
	                }
	                $c2 = implode("\n", $a2);
	                $content = $c2;

					// écriture du fichier de template
	                if ((@file_exists($dest) && @is_writable($dest)) || @is_writable($blog->themes_path))
	                {
	                    $fp = @fopen($dest, 'w');
	                    @fputs($fp, $content);
	                    @fclose($fp);
	                    $msg = __('Template created.');
	                }
	                else $msg = __('Unable to write file.');
				}

				return $msg;
			}
		    catch (Exception $e) { $core->error->add($e->getMessage()); }
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
		if (!dcNewsletter::isInstalled()) return;

		global $core;
		try {
			$blog = &$core->blog;
	      $auth = &$core->auth;
			$url = &$core->url;
			$themes = &$core->themes;
			$blogurl = &$blog->url;
			$urlBase = http::concatURL($blogurl, $url->getBase('newsletter'));
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
      	$fmode = pluginNewsletter::getSendMode();
      	$fmaxposts = pluginNewsletter::getMaxPosts();
			$fautosend = pluginNewsletter::getAutosend();
			$fcaptcha = pluginNewsletter::getCaptcha();
			/* for 3.5.1 : add period
			$fperiod = pluginNewsletter::getPeriod();
			//*/

      	$core->themes = new dcModules($core);
      	$core->themes->loadModules($blog->themes_path, NULL);
      	$theme = $blog->settings->theme;
			$bthemes = array();
			foreach ($themes->getModules() as $k => $v)
			{
				if (file_exists($blog->themes_path . '/' . $k . '/post.html'))
					$bthemes[html::escapeHTML($v['name'])] = $k;
			}
			
	        echo
			'<fieldset>' .

			'<form action="plugin.php" method="post" name="state">'.
				$core->formNonce().
				form::hidden(array('p'),pluginNewsletter::pname()).
				form::hidden(array('op'),'state').
				'<fieldset>'.
					'<legend>'. __('Plugin state').'</legend>'.
					'<p class="field">'.
					form::checkbox('active', 1, $pactive).
					'<label class=" classic" for="active">'.__('Activate plugin').'</label></p>'.
				'<p>'.
					'<input type="submit" value="'.__('Save').'" />'.
					'<input type="reset" value="'.__('Cancel').'" />'.
				'</p>'.
				'</fieldset>'.
			'</form>'.
			
			// gestion des paramètres du plugin
			'<fieldset>' .
			'<legend>'.__('Settings').'</legend>'.
				'<form action="plugin.php" method="post" name="settings">'.
               $core->formNonce().
					form::hidden(array('p'),pluginNewsletter::pname()).
					form::hidden(array('op'),'settings').
					'<p>'.
						'<p><label class="classic">'. __('Editor name').' : <br />'.
						form::field(array('feditorname'), 50,255, $feditorname).
						'</label></p>'.
						'<p><label class="classic">'. __('Editor email').' : <br />'.
						form::field(array('feditoremail'), 50,255, $feditoremail).
						'</label></p>'.
						'<p><label class="classic" for="fmaxposts">'. __('Maximum posts').' : '.
						form::field(array('fmaxposts'),4,4, $fmaxposts).
						'</label></p>'.
						'<p class="field">'.
						form::checkbox('fautosend',1,$fautosend).
						'<label class="classic" for="fautosend">'.__('Automatic send').
						'</label></p>'.
						'<p class="field">'.
						form::checkbox('fcaptcha',1,$fcaptcha).
						'<label class="classic" for="fcaptcha">'.__('Captcha').'</label></p>'.
						'<p><label class="classic" for="fmode">'. __('Mode').' : '.
						form::combo(array('fmode'), $mode_combo, $fmode).
						'</label></p>'.
						/* for 3.5.1 : add period
						'<p class="field">'.
						form::checkbox('fperiod',1,$fperiod).
						'<label class="classic" for="fperiod">'.__('Period sending').
						'</label></p>'.
						*/
					'</p>'.
					'<p>'.
						'<input type="submit" value="'.__('Save').'" />'.
						'<input type="reset" value="'.__('Cancel').'" />'.
						'<input type="button" value="'.__('Defaults').'" onclick="pdefaults(); return false" />'.
					'</p>'.
				'</form>'.
			'</fieldset>'.

			// adaptation du template
			/* fonction à valider pour une version ultérieure : désactivée en attendant ...
			'<fieldset>' .
			'<legend>'.__('Theme template adaptation').'</legend>'.
				'<form action="plugin.php" method="post" name="adapt">'.
					$core->formNonce().
					form::hidden(array('p'),pluginNewsletter::pname()).
					form::hidden(array('op'),'adapt').
					'<p>'.
						'<p><label class="classic" for="fthemes">'. __('Theme name').' : '.
						form::combo('fthemes', $bthemes, $theme).
						'</label></p>'.
						'<input type="submit" value="'.__('Adapt').'" />'.
					'</p>'.
					'<p>'.
						'<a href="'.$urlBase.'/test'.'">'.__('Clic here to test the template.').'</a>'.
					'</p>'.
					'</form>'.
			'</fieldset>'.
			//*/
			
			// export/import pour le blog
			'<fieldset>' .
			'<legend>'.__('Import/Export subscribers list').'</legend>'.
				'<form action="plugin.php" method="post" name="impexp">'.
					$core->formNonce().
					form::hidden(array('p'),pluginNewsletter::pname()).
					form::hidden(array('op'),'export').
					'<label class="classic">'.form::radio(array('type'),'blog',(!$sadmin) ? true : false).__('This blog only').'</label><br />'.
					'<label class="classic">'.form::radio(array('type'),'all',($sadmin) ? true : false,'','',(!$sadmin) ? true : false).__('All datas').'</label>'.
					'<p></p>'.
					'<p>'.
						'<input type="submit" value="'.__('Export').'" />'.
						'<input type="button" value="'.__('Import').'" onclick="pimport(); return false" />'.
					'</p>'.
				'</form>'.
			'</fieldset>';

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
				'</fieldset>';
			}
			//*/
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}

	/**
	* notice d'utilisation
	*/
	/* déplacée dans l'aide en ligne
	public static function Usage()
	{
        echo
		'<fieldset>' .
		'<legend>'.__('Gestion').'</legend>'.
		'</fieldset>'.
		'<fieldset>' .
		'<legend>'.__('Settings').'</legend>'.
			__('Change settings:').'<br /><a href="'.pluginNewsletter::admin().'&tab=settings" title="'.__('Clic here to edit your settings.').'">'.__('Clic here to edit your settings.').'</a>'.
		'</fieldset>'.
		'<fieldset>' .
		'<legend>'.__('Widget').'</legend>'.
			__('Widget manager:').'<br /><a href="'.pluginNewsletter::urlwidgets().'" title="'.__('Clic here to edit your widgets.').'">'.__('Clic here to edit your widgets.').'</a>'.
		'</fieldset>'.
		'<fieldset>' .
		'<legend>'.__('Theme').'</legend>'.
			__('Theme integration:').
			'<ul>'.
				'<li>'.
					__('You can add this code into your theme to display the Newsletter subscription form:').'<br />'.
					'<span class="tpl">{{tpl:NewsletterSubscription}}</span>'.
				'</li>'.
			'</ul>'.
		'</fieldset>';
	}
	//*/

	/**
	* à propos du plugin
	*/
	/* déplacée dans l'aide en ligne	
	public static function About()
	{
        echo
		'<fieldset>' .
		'<legend>'.__('Authors').'</legend>'.
			'<ul>'.
				'<li>'.'<a href="http://phoenix.cybride.net/" title="Olivier Le Bris">Olivier Le Bris</a>'.'</li>'.
			'</ul>'.
		'</fieldset>'.
		'<fieldset>' .
		'<legend>'.__('Licence').'</legend>'.
			'<ul>'.
				'<li>'.
                    '<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/2.0/fr/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-nc-sa/2.0/fr/88x31.png" /></a>'.
				'</li>'.
				'<li>'.
                    __('This work is released under a ').'<a rel="license" href="http://creativecommons.org/licenses/by-nc-sa/2.0/fr/">'.__('Creative Commons contract').'</a>.'.
				'</li>'.
			'</ul>'.
		'</fieldset>'.
		'<fieldset>' .
		'<legend>'.__('Greets').'</legend>'.
			'<ul>'.
			'<li>'.__('Flannelle (http://flannelle.cybride.net/)').'</li>'.
			'<li>'.__('Mumu (http://www.chez-mumu.com/blog/)').'</li>'.
			'<li>'.__('Guizbizet (http://guillaumebizet.free.fr/)').'</li>'.
			'<li>'.__('kinanveu').'</li>'.
			'<li>'.__('baboon, Richard, brol, Vincent, Nathalie').'</li>'.
			'<li>'.__('k-netweb.net, for Contact2 plugin for Dotclear 2').'</li>'.
			'<li>'.__('All the peoples who tested it !').'</li>'.
			'</ul>'.
		'</fieldset>'.
		'<fieldset>' .
		'<legend>'.__('Usefull informations').'</legend>'.
			'<ul>'.
				'<li>'.__('Support:').' <a href="http://www.cybride.net/redirect/support/'.pluginNewsletter::pname().'" title="'.__('Clic here to go to the support.').'">'.__('Clic here to go to the support.').'</a>'.'</li>'.
				'<li>'.__('Files:').' '.__('Read authors.txt and changelog.txt in the doc folder.').'</a>'.'</li>'.
				'<li>'.__('Dotclear:').' <a href="http://www.dotclear.net/" title="'.__('Clic here to go to Dotclear.').'">'.__('Clic here to go to Dotclear.').'</a>'.'</li>'.
			'</ul>'.
		'</fieldset>';
	}
	//*/

	/**
	* liste des abonnés du blog
	*/
	public static function ListBlog()
	{
		// prise en compte du plugin installé
		if (!dcNewsletter::isInstalled()) return;

		$datas = dcNewsletter::getlist();
      if (!is_object($datas))
      {
      	echo __('No subscriber for this blog.');
      } else {
			global $core;
		    try
		    {
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
						    '<th class="nowrap" colspan="2">'.__('Edit').'</th>'.
					    '</tr>';

			    // parcours la liste pour l'affichage
                $datas->moveStart();
                while ($datas->fetch())
                {
				    $k = (integer)$datas->subscriber_id;
				    $editlink = 'onclick="ledit('.$k.'); return false"';
                    $guilink = '<a href="#" '.$editlink.' title="'.__('Edit subscriber').'"><img src="images/edit-mini.png" alt="'.__('Edit subscriber').'" /></a>';

					if ($datas->subscribed != null) $subscribed = dt::dt2str('%d/%m/%Y', $datas->subscribed).' '.dt::dt2str('%H:%M', $datas->subscribed);
                    else $subscribed = __('Never');
					if ($datas->lastsent != null) $lastsent = dt::dt2str('%d/%m/%Y', $datas->lastsent).' '.dt::dt2str('%H:%M', $datas->lastsent);
                    else $lastsent = __('Never');

                    echo
				    '<tr class="line">'.
					    '<td>'.form::checkbox(array('subscriber['.html::escapeHTML($k).']'), 1).'</td>'.
					    '<td class="maximal nowrap">'.html::escapeHTML(text::cutString($datas->email, 50)).'</td>'.
					    '<td class="minimal nowrap">'.html::escapeHTML(text::cutString($subscribed, 50)).'</td>'.
					    '<td class="minimal nowrap">'.html::escapeHTML(text::cutString(__($datas->state), 50)).'</td>'.
					    '<td class="minimal nowrap">'.html::escapeHTML(text::cutString($lastsent, 50)).'</td>'.
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
                $bmails[__('Confirm')] = 'sendconfirm';
                $bmails[__('Suspend')] = 'sendsuspend';
                $bmails[__('Disable')] = 'senddisable';
                $bmails[__('Enable')] = 'sendenable';

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
				'<label for "fstates">'.__('Set state:').'</label>'.
				form::combo('fstates', $bstates).'<input type="button" value="'.__('Set').'" onclick="lset(); return false" />'.
				'<label for "fmails">'.__('Mail to send:').'</label>'.
				form::combo('fmails', $bmails).'<input type="button" value="'.__('Send').'" onclick="lsend(); return false" />'.
			    '</p></form>';
			}
	        catch (Exception $e) { $core->error->add($e->getMessage()); }
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
		}
	    catch (Exception $e) { $core->error->add($e->getMessage()); }
	}
}

?>
