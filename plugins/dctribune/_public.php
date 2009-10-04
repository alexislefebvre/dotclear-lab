<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of dctribune, a plugin for Dotclear 2.
# 
# Copyright (c) 2009 Osku  and contributors
# Many thanks to Pep, Tomtom and JcDenis
# Originally from Antoine Libert
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if ($core->blog->settings->tribune_flag)
{
	$core->addBehavior('publicHeadContent',array('publicTribune','publicHeadContent'));
}

$core->tpl->addBlock('TriTribune',array('tribuneTemplate','Tribune'));
$core->tpl->addValue('TribAuthor',array('tribuneTemplate','TribuneAuthor'));
$core->tpl->addValue('TribId',array('tribuneTemplate','TribuneId'));
$core->tpl->addValue('TribMessage',array('tribuneTemplate','TribuneMessage'));
$core->tpl->addValue('TribDate',array('tribuneTemplate','TribuneDate'));

$_ctx->tribune = new dcTribune($core);

class urlTribune extends dcUrlHandlers
{
	public static function tribuneHandler($args)
	{
		global $core, $_ctx;
		$params = new ArrayObject();
		$tribune = new dcTribune($core);
		
		//throw new Exception ($post['tribnick']);
		if (!empty($_POST))
		{
			if (!empty($_POST['tribnick']) && !empty($_POST['tribmsg']))
			{
				$nick= $_POST['tribnick'];
				$msg = $_POST['tribmsg'];

				$not_spam = true;
				# Check message form spam

				if (class_exists('dcAntispam') && isset($core->spamfilters))
				{
					# Fake cursor to check spam
					$cur = $core->con->openCursor('foobar');
					$cur->comment_trackback = 0;
					$cur->comment_author = $nick;
					$cur->comment_email = '';
					$cur->comment_site = '';
					$cur->comment_ip = http::realIP();
					$cur->comment_content = $msg;
					$cur->post_id = 0; // That could break things...
					$cur->comment_status = 1;
				    
					@dcAntispam::isSpam($cur);
				    
					if ($cur->comment_status == -2) {
						unset($cur);
						$not_spam = false;
						$_ctx->form_error = (__('Don\'t lie, you tried to spam...'));
				    }
					unset($cur);
				}
				
				if ($not_spam)
				{
					$msg = text::cutString($msg ,$core->blog->settings->tribune_message_length);
					$msg = $core->HTMLfilter($msg);
					
					if ($core->blog->settings->tribune_syntax_wiki)
					{
						$core->addBehavior('coreInitWikiComment',array('tribuneBehaviors','coreInitWikiMessage'));
						$core->initWikiComment();
						/// coreInitWikiPost
						$msg = $core->wikiTransform($msg);
					}
					else 
					{
						$msg = $tribune->cleanMsg($msg);
					}
					
					$cur = $core->con->openCursor($core->prefix.'tribune');
					$cur->tribune_nick = $core->HTMLfilter($nick);
					$cur->tribune_msg = $msg;
					try
					{
						$add = $tribune->addMsg($cur);
					}
					catch (Exception $e)
					{
						$_ctx->form_error = $e->getMessage();
					}			
				}
			}
			else if  (isset($_POST['tribnick']) && $_POST['tribnick']  == '' )
			{
				$_ctx->form_error = (__('Anonymous ?'));
			}
			
			else if  (isset($_POST['tribmsg']) && $_POST['tribmsg']  == '' )
			{
				$_ctx->form_error = (__('Nothing to say ?'));
			}
			
			// get Tribune from base with config 
			$params['limit'] = 5;
			//$params['order'] = $sort;
			$params['tribune_state'] = 1;
			$_ctx->tribune = $core->tribune->getMsgs($params);
			   
			$core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
			$result = new ArrayObject;
			    
			header('Content-Type: text/html; charset=UTF-8');
			$result['content'] = $core->tpl->getData('tribune.html');
			echo $result['content'];
		}
		else
		{
			//404 if url tribune.
			self::p404();
		}
	}
}

class publicTribune
{
	public static function publicHeadContent($core)
	{
		global $core;

		$js = html::stripHostURL($core->blog->getQmarkURL().'pf=dctribune/js/jquery.tribune.js');
		
		echo 
		"\n<!-- JS for Tribune --> \n".
		'<script type="text/javascript" src="'.$js.'"></script>'."\n".
		"<script type=\"text/javascript\"> \n".
		"//<![CDATA[\n".
		" \$(function(){if(!document.getElementById){return;} \n".
		" \$.fn.tribune.defaults.service_url = '".html::escapeJS($core->blog->url.$core->url->getBase('tribune'))."'; \n".
		" \$.fn.tribune.defaults.refresh_delay = '".html::escapeJS($core->blog->settings->tribune_refresh_time)."' ; \n".
		" \$.fn.tribune.defaults.verbose = ".(DC_DEBUG ? 1 : 0)."; \n".
		" \$('.tribune').tribune(); \n".
		" })\n".
		"//]]>\n".
		"</script>\n";
	}
}

class tplTribune
{
	# Widget function
	public static function tribuneWidget(&$w)
	{
		global $core;
  
		if ($w->homeonly && $core->url->type != 'default' || !$core->blog->settings->tribune_flag) {
			return;
		}
		
		if (!empty($_COOKIE['comment_info'])) {
			$cookie_info = explode("\n",$_COOKIE['comment_info']);
			if (count($cookie_info) == 3) {
                                $c_cookie = array(
                                        'name'=>$cookie_info[0],
                                        'mail'=>$cookie_info[1],
                                        'site'=>$cookie_info[2]
                                );
                        }

		}
		
		# Récupération du pseudo à partir du cookie, sinon du POST ou sinon affichage d'un message
		if (!empty($c_cookie['name']))
		{ 
			$nick_v = $c_cookie['name']; 
			$disabled = true;
		} else if (!empty($_POST['tribnick']))
		{ 
			$nick_v = html::escapeHTML($_POST['tribnick']); 
			$disabled = true;
		} else { 
			$nick_v = '';
			$disabled = false;
		}
		
		$tribune_area = '<div class="tribune">'.__('Loading Chatbox...').'</div>';

		# Retourne le formulaire
		$str = '<div class="litribune">'.
		($w->title ? '<h2>'.html::escapeHTML($w->title).'</h2>' : '').
		'<form action="" method="post" id="tribunelibreform">'.
		($w->formbefore ? $tribune_area: '').
		'<fieldset>'.
			'<p class="field"><label for="tribnick">'.html::escapeHTML($w->nick).'</label>'.form::field('tribnick', '12', '50', $nick_v).'</p>'.
			'<p style="display:none"><input name="f_message" type="text" size="20" maxlength="255" value="" /></p>'.
			'<p class="field"><label for="tribmsg">'.html::escapeHTML($w->message).'</label>'.form::textarea('tribmsg',20,3).'</p>'.
			'<p class="buttons"><input type="submit" class="tribunesubmit" name="tribunesubmit" value="'.html::escapeHTML($w->button).'"/></p>'.
		'</fieldset>'.
		'</form>'.
		(!$w->formbefore ? $tribune_area: '').
		'</div>';
		
		return $str;
	}
}
?>