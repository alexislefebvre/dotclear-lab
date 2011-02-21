<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of soCialMe, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2011 JC Denis and contributors
# jcdenis@gdwd.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')){return;}
dcPage::check('admin');# Plugin pages definitions
$nav = array();
foreach(soCialMeUtils::getParts() as $part => $ns)
{
	eval('$nav[$part] = '.$ns.'::adminNav();');
}
# Request URI$request_page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : null;$request_part = !empty($_REQUEST['part']) ? $_REQUEST['part'] : null;$request_lib = !empty($_REQUEST['lib']) ? $_REQUEST['lib'] : null;$request_section = !empty($_REQUEST['section']) ? $_REQUEST['section'] : null;$request_act = !empty($_REQUEST['act']) ? $_REQUEST['act'] : null;# Other pageif (isset($nav[$request_page])){	# Shortcut	$page = $nav[$request_page];	$page['setting'] = $core->blog->settings->{$page['ns']};	# Load all services class	try	{		$page['class'] = new $page['ns']($core);	}	catch (Exception $e)	{		$core->error->add($e->getMessage());	}		# Set default part	if (!isset($page['parts'][$request_part]))	{		$request_part = $_REQUEST['part'] = key($page['parts']);	}		# user a common page for all parts	if (in_array($request_part,$page['common']))	{		$index = dirname(__FILE__).'/inc/lib.index.'.$request_part.'.php';	}	# Load another page	else	{		$index = dirname(__FILE__).'/inc/index.'.$request_page.'.'.$request_part.'.php';	}		if (file_exists($index))	{		require $index;	}}# Home page of pluginelse{	# Read settings	foreach($nav as $page => $v)	{		$s[$page] = (boolean) $core->blog->settings->{$v['ns']}->active;	}		# Save settings	if ($request_act == 'save')	{		try		{			foreach($nav as $page => $v)			{
				$s[$page] = !empty($_POST['s_'.$page]);				$core->blog->settings->{$v['ns']}->put('active',$s[$page],'boolean');			}						$core->blog->triggerBlog();						http::redirect($p_url);		}		catch (Exception $e)		{			$core->error->add($e->getMessage());		}	}
	elseif ($request_act == 'super' && $core->auth->isSuperAdmin())
	{
		try
		{
			$s_debug = (string) $_POST['s_debug'];
			$s_log_timeout = abs((integer) $_POST['s_log_timeout']);
			$s_cache_timeout = abs((integer) $_POST['s_cache_timeout']);
			
			if ($s_log_timeout < 60) $s_log_timeout = 604800;
			if ($s_cache_timeout < 60) $s_cache_timeout = 900;
			
			$core->blog->settings->soCialMe->put('debug',$s_debug,'string');
			$core->blog->settings->soCialMe->put('log_timeout',$s_log_timeout,'integer');
			$core->blog->settings->soCialMe->put('cache_timeout',$s_cache_timeout,'integer');
			
			http::redirect($p_url);
		}
		catch (Exception $e)
		{
			$core->error->add($e->getMessage());
		}
	}	# soCialMe menu	echo soCialMeAdmin::top().'	<form id="main-form" method="post" action="'.soCialMeAdmin::link(1,'writer').'">	<div class="clear two-cols">';		$i = 0;	foreach($nav as $page => $v)	{		if ($i == 2) {			$i = 0;			echo '</div><div class="clear two-cols">';		}		$i++;				echo '		<div class="col"><div class="socialbox">		<h4><a href="'.soCialMeAdmin::link(1,$page).'" title="'.__('Administrate').'">'.$v['title'].'</a></h4>		<p>'.$v['description'].'</p>
		<p><label class="classic">'.form::checkbox(array('s_'.$page),'1',$s[$page]).__('Enable this part').'</label></p>
		</div></div>';	}		echo 	'</div>'.	'<p class="clear">&nbsp;</p>'.	'<div class="clear">'.	'<p><input type="submit" name="save" value="'.__('save').'" />'.	$core->formNonce().	form::hidden(array('p'),'soCialMe').	form::hidden(array('page'),'').	form::hidden(array('part'),'').	form::hidden(array('act'),'save').	'</p></div>'.	'</form>';/*	# so.cial.me news	$feed_reader = new feedReader;	$feed_reader->setCacheDir(DC_TPL_CACHE);	$feed_reader->setTimeout(2);	$feed_reader->setUserAgent('Dotclear - http://www.dotclear.org/');	$feed = $feed_reader->parse(SOCIALME_RSS_NEWS);		if ($feed->items)	{		echo '<hr class="clear" /><h3>'.__('Latest news from so.cial.me').'</h3><dl id="news">';		$i = 1;		foreach ($feed->items as $item)		{			$dt = isset($item->link) ? '<a href="'.$item->link.'">'.$item->title.'</a>' : $item->title;						if ($i < 3) {				echo 				'<dt>'.$dt.'</dt>'.				'<dd><p><strong>'.dt::dt2str('%d %B %Y',$item->pubdate,'Europe/Paris').'</strong>: '.				'<em>'.text::cutString(html::clean($item->content),350).'...</em></p></dd>';			} else {				echo 				'<dt>'.$dt.'</dt>'.				'<dd>'.dt::dt2str('%d %B %Y',$item->pubdate,'Europe/Paris').'</dd>';			}			$i++;			if ($i > 7) { break; }		}		echo  '</dl>';	}
//*/

	if ($core->auth->isSuperAdmin())
	{
		$s_debug = (string) $core->blog->settings->soCialMe->debug;
		$s_log_timeout = abs((integer) $core->blog->settings->soCialMe->log_timeout);
		$s_cache_timeout = abs((integer) $core->blog->settings->soCialMe->cache_timeout);
		
		$combo_debug = array(
			__('Follow Dotclear debug mode') => 'dc',
			__('Disable logging') => 'off',
			__('Enable logging') => 'on'
		);
		
		echo '
		<form id="super-form" method="post" action="'.soCialMeAdmin::link(1,'writer').'">'.
		'<fieldset><legend>'.__('Global settings').'</legend>'.
		'<p><label>'.__('Debug mode:').'<br />'.
		form::combo(array('s_debug'),$combo_debug,$s_debug).
		'</label></p>'.
		'<p class="form-note">'.__('If enabled, services log their queries. You can install plugin called dcLog to view them.').'</p>'.
		'<p><label class="classic">'.__('Log life (in second):').'<br />'.
		form::field(array('s_log_timeout'),50,255,$s_log_timeout).
		'</label></p>'.
		'<p><label class="classic">'.__('Cache life (in second):').'<br />'.
		form::field(array('s_cache_timeout'),50,255,$s_cache_timeout).
		'</label></p>'.
		'<p class="clear">&nbsp;</p>'.
		'<div class="clear">'.
		'<p><input type="submit" name="save" value="'.__('save').'" />'.
		$core->formNonce().
		form::hidden(array('p'),'soCialMe').
		form::hidden(array('page'),'').
		form::hidden(array('part'),'').
		form::hidden(array('act'),'super').
		'</p></fieldset>'.
		'</form>';
	}
}
echo '</div>';

echo '<hr class="clear" /><div class="two-cols"><div class="col">';if (isset($nav[$request_page])){	echo '- ';	foreach($nav as $page => $v)	{		echo '<a href="'.soCialMeAdmin::link(1,$page).'" title="'.$v['description'].'">'.$v['title'].'</a> - ';	}}echo '&nbsp;</div><div class="col"><p class="right">soCialMe - '.$core->plugins->moduleInfo('soCialMe','version').'&nbsp;<img alt="'.__('soCialMe').'" src="index.php?pf=soCialMe/icon.png" /></p></div></div></body></html>';?>