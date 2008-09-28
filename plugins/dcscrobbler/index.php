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
  
  http::redirect($p_url.'&rst=1');

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
</head>

<body>
<h2><?php echo __('dcScrobbler'); ?></h2>
<?php
// Affichage d'un message d'erreur ou d'état si défini.
if (!empty($_GET['rset']))
  echo '<p class="message">'. __('Configuration has been reset to default').'</p>';

if(!empty($_GET['cfg']))
  echo '<p class="message">'.__('New configuration saved').'</p>';

// Affichage du formulaire de modification des paramètres de configuration
echo
'<form action="'.$p_url.'" method="post">'.
'<fieldset><legend>Configuration</legend>'.
/* Champs 1 : username */
'<p ><label>'.__('AudioScrobbler username').' '.
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
'<fieldset><legend>'.__('Reset configuration').'</legend>'.
form::hidden(array('clear'),'clear').
form::hidden(array('p'),'dcscrobbler').
'<p><input type="submit" name="submit" value="'.__('Restore default settings').'" />'.$core->formNonce().'</p>'.
'</fieldset></form>';

?>
</body>
</html>