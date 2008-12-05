<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcScrobbler, a plugin for Dotclear.
# 
# Copyright (c) 2008 Boris de Laage
# bdelaage@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

# $Id: index.php 24 2006-08-23 11:53:04Z bdelaage $

if (!defined('DC_CONTEXT_ADMIN')) { return; }

require dirname(__FILE__).'/class.dc.dcscrobbler.php';

$res = '';

$core->blog->settings->setNameSpace('dcscrobbler');

// Premiere config
if (!$core->blog->settings->get('dcs_cache_validity')) {
  $core->blog->settings->put('dcs_username', '', 'string', __('Last.fm username'));
  $core->blog->settings->put('dcs_cache_validity', 120, 'integer', __('Cache validity'));

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

  $core->blog->settings->put('dcs_username', html::escapeHTML($_POST['dcss']['dcs_username']));
  $core->blog->settings->put('dcs_cache_validity', $_POST['dcss']['dcs_cache_validity']);

  $core->blog->triggerBlog();
  
  http::redirect($p_url.'&cfg=1');
  }


/* Récupération de la configuration */
$dcs['dcs_username'] = $core->blog->settings->get('dcs_username');
$dcs['dcs_cache_validity'] = $core->blog->settings->get('dcs_cache_validity');


?>


<html>
<head>
  <title><?php echo __('dcScrobbler'); ?></title>
  <?php echo dcPage::jsPageTabs($part); ?>
</head>

<body>
<?php
echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('dcScrobbler').'</h2>'.
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
/* Champs 1 : dcs_username */
'<p ><label>'.__('Last.fm username').' '.
form::field('dcss[dcs_username]', 20, 50, $dcs['dcs_username']).
'</label></p>'.

/* champs 2 : expiration du cache */
'<p><label>'.__('Cache validity (in seconds)').' '.
form::field('dcss[dcs_cache_validity]', 3, 3, $dcs['dcs_cache_validity']).
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
'<ul style="list-style: none;"><li><strong>.dcscrobbler {}</strong> : '.__('style for dcScrobbler widget').'.</li>'.
'<li><strong>.dcscrobbler .artist {}</strong> : '.__('style for artist name').'.</li>'.
'<li><strong>.dcscrobbler .title {}</strong> : '.__('style for track title').'.</li>'.
'<li><strong>.dcscrobbler .playcount {}</strong> : '.__('In Top Artists, style for played tracks count').'.</li></ul>'.
'</div>';

echo
'<div id="about" title="'.__('About').'" class="multi-part">'.
'<h2 style="background: url(index.php?pf=dcscrobbler/icon.png) no-repeat 0 0.25em; padding: 5px 0 5px 22px; margin-left: 20px;">'.__('dcScrobbler').'</h2>'.
'<ul style="list-style: none; line-height: 30px; font-weight: bold;"><li>version 2.0-RC1</li>'.
'<li>'.__('Created by').' : <a href="http://bdelaage.free.fr/">Boris de Laage</a></li>'.
'<li>'.__('Maintained by').' : <a href="http://www.oum.fr/">Oum</a></li>'.
'<li>'.__('Help and Support').' : <a href="http://forum.dotclear.net/viewtopic.php?id=20711">http://forum.dotclear.net/viewtopic.php?id=20711</a></li>'.
'<li>'.__('Sources').' : <a href="http://code.google.com/p/dcplugins/source/browse/dcscrobbler">http://code.google.com/p/dcplugins/source/browse/dcscrobbler</a></li>'.
'<li><a style="border:none;" href="http://www.audioscrobbler.net/"><img style="margin-top:20px;" src="index.php?pf=dcscrobbler/lastfm_button.png" alt="'.__('Powered by Audioscrobbler').'" title="'.__('Powered by Audioscrobbler').'"/></a></li></ul>'.
'</div>';

dcPage::helpBlock('dcscrobbler');

?>
</body>
</html>