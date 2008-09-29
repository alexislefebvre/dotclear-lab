<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of dcScrobbler for DotClear.
# Copyright (c) 2005-2006 Boris de Laage. All rights reserved.
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

# $Id: index.php 24 2006-08-23 11:53:04Z bdelaage $

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require dirname(__FILE__).'/class.dc.dcscrobbler.php';

$res = '';

$core->blog->settings->setNameSpace('dcscrobbler');

// Premiere config
if (!$core->blog->settings->get('cache_validity')) {
  $core->blog->settings->put('username', '', 'string', __('Last.fm username'));
  $core->blog->settings->put('cache_validity', 120, 'integer', __('Cache validity'));

  $core->blog->triggerBlog();
  http::redirect($p_url);

 }
// Clear config
if (!empty($_POST['clear'])) {

  $tmp = array();
  foreach ($core->blog->settings->dumpSettings() as $k => $v)
    $tmp[$v['ns']][$k] = $v;
  
  foreach ($tmp as $ns => $s) {
    if ($ns === 'dcscrobbler')
      foreach ($s as $k => $v)
        $core->blog->settings->drop($k);
    $core->blog->triggerBlog();
  }
  
  http::redirect($p_url.'&rset=1');

 }

if (!empty($_POST['dcss']) && is_array($_POST['dcss'])) {

  $core->blog->settings->put('username', html::escapeHTML($_POST['dcss']['username']));
  $core->blog->settings->put('cache_validity', $_POST['dcss']['cache_validity']);

  $core->blog->triggerBlog();
  
  http::redirect($p_url.'&cfg=1');
  }


/* Récupération de la configuration */
$dcs['username'] = $core->blog->settings->get('username');
$dcs['cache_validity'] = $core->blog->settings->get('cache_validity');


?>


<html>
<head>
  <title><?php echo __('dcScrobbler'); ?></title>
  <?php echo dcPage::jsPageTabs($part); ?>
</head>

<body>
<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &gt; '.__('dcScrobbler').'</h2>'.
'<div id="settings" title="'.__('Settings').'" class="multi-part">';
// Affichage d'un message d'erreur ou d'état si défini.
if (!empty($_GET['rset']))
  echo '<p class="message">'. __('Configuration has been reset to default').'</p>';

if(!empty($_GET['cfg']))
  echo '<p class="message">'.__('New configuration saved').'</p>';

// Affichage du formulaire de modification des paramètres de configuration
echo
'<p><a style="background: url(index.php?pf=dcscrobbler/icon.png) no-repeat 0 0.25em; padding: 5px 0 5px 22px;" href="http://www.last.fm/join">'.__('Sign up on Last.fm').'</a></p>'.
'<form action="'.$p_url.'" method="post">'.
'<fieldset><legend>Configuration</legend>'.
/* Champs 1 : username */
'<p ><label>'.__('Last.fm username').' '.
form::field('dcss[username]', 20, 50, $dcs['username']).
'</label></p>'.

/* champs 2 : expiration du cache */
'<p><label>'.__('Cache validity (in seconds)').' '.
form::field('dcss[cache_validity]', 3, 3, $dcs['cache_validity']).
'</label></p>'.
'<p><strong>Note: </strong>'.__('cache validity shouldn\'t be less than 60 seconds.').'</p>'.

form::hidden(array('p'),'dcscrobbler').
'<p><input type="submit" name="submit" value="'.__('Save changes').'" />'.$core->formNonce().'</p>'.
'</fieldset></form>';

//
echo
'<form action="'.$p_url.'" method="post">'.
'<fieldset><legend>'.__('Reset').'</legend>'.
form::hidden(array('clear'),'clear').
form::hidden(array('p'),'dcscrobbler').
'<p><input type="submit" name="submit" value="'.__('Restore default settings').'" />'.$core->formNonce().'</p>'.
'</fieldset></form>'.
'</div>';

echo
'<div id="display" title="'.__('Display').'" class="multi-part">'.
'<h2>'.__('Display settings').' :</h2>'.
'<p>'.__('You can set widget display by using the following classes in your style.css file').' :</p>'.
'<ul style="list-style: none;"><li><strong>.dcscrobbler {}</strong> : '.__('style dcScrobbler widget').'.</li>'.
'<li><strong>.dcscrobbler .artist {}</strong> : '.__('style for artist name').'.</li>'.
'<li><strong>.dcscrobbler .title {}</strong> : '.__('style for track title').'.</li>'.
'<li><strong>.dcscrobbler .playcount {}</strong> : '.__('In Top Artists, style for played tracks count').'.</li></ul>'.
'</div>';

echo
'<div id="about" title="'.__('About').'" class="multi-part">'.
'<h2 style="background: url(index.php?pf=dcscrobbler/icon.png) no-repeat 0 0.25em; padding: 5px 0 5px 22px; margin-left: 20px;">'.__('dcScrobbler').'</h2>'.
'<ul style="list-style: none; line-height: 30px; font-weight: bold;"><li>version 1.0.3</li>'.
'<li>'.__('Created by').' : <a href="http://bdelaage.free.fr/">Boris de Laage</a></li>'.
'<li>'.__('Maintained by').' : <a href="http://www.oum.fr/">Oum</a></li>'.
'<li>'.__('Help and Support').' : <a href="http://forum.dotclear.net/viewtopic.php?id=20711">http://forum.dotclear.net/viewtopic.php?id=20711</a></li>'.
'<li>'.__('Updates').' : <a href="http://dcplugins.googlecode.com/">dcPlugins</a></li>'.
'<li><a style="border:none;" href="http://www.audioscrobbler.net/"><img style="margin-top:20px;" src="index.php?pf=dcscrobbler/lastfm_button.png" alt="'.__('Powered by Audioscrobbler').'" title="'.__('Powered by Audioscrobbler').'"/></a></li></ul>'.
'</div>';

?>
</body>
</html>