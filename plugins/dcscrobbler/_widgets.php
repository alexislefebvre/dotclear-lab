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
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('dcScrobblerBehavior','initWidgets'));
$core->addBehavior('pluginBeforeDelete',array('dcScrobblerBehavior','uninstall'));

class dcScrobblerBehavior
{
  public static function uninstall($plugin_id)
  {
    global $core;

    $core->blog->settings->addNameSpace('dcscrobbler');
    $tmp = array();
    foreach ($core->blog->settings->dcscrobbler->dumpSettings() as $k => $v)
      $tmp[$v['ns']][$k] = $v;
  
    foreach ($tmp as $ns => $s) {
      if ($ns === 'dcscrobbler')
        foreach ($s as $k => $v)
          $core->blog->settings->dcscrobbler->drop($k);
      $core->blog->triggerBlog();
    }
    
  }


  public static function initWidgets(&$widgets)
  {
    $widgets->create('dcscrobbler',__('dcScrobbler'),
                     array('dcScrobblerWidget','widget'));

    $widgets->dcscrobbler->setting('title',__('Title :'),'');

    $widgets->dcscrobbler->setting('feed',__('Source :'),'rtracks','combo',
                                   array(__('Recent Tracks')  => 'recenttracks',
                                         __('Weekly Top Artists') => 'weeklyartistchart',
                                         __('Weekly Top Tracks')  => 'weeklytrackchart',
                                         __('Top Artists')    => 'topartists',
                                         __('Top Tracks')     => 'toptracks'));

    $widgets->dcscrobbler->setting('count',__('Limit (leave empty for no limit):'),'');
    $widgets->dcscrobbler->setting('showlogo',__('Show Last.fm logo'),1,'check');
    $widgets->dcscrobbler->setting('homeonly',__('Home page only'),1,'check');
  }
}

?>