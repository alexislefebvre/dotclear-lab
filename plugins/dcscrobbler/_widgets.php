<?php
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('dcScrobblerBehavior','initWidgets'));
$core->addBehavior('pluginBeforeDelete',array('dcScrobblerBehavior','uninstall'));

class dcScrobblerBehavior
{
  public static function uninstall($plugin_id)
  {
    global $core;

    $core->blog->settings->setNameSpace('dcscrobbler');
    $tmp = array();
    foreach ($core->blog->settings->dumpSettings() as $k => $v)
      $tmp[$v['ns']][$k] = $v;
  
    foreach ($tmp as $ns => $s) {
      if ($ns === 'dcscrobbler')
        foreach ($s as $k => $v)
          $core->blog->settings->drop($k);
      $core->blog->triggerBlog();
    }
    
  }


  public static function initWidgets(&$widgets)
  {
    $widgets->create('dcscrobbler',__('Audioscrobbler'),
                     array('dcScrobblerWidget','widget'));

    $widgets->dcscrobbler->setting('title',__('Title:'),'');

    $widgets->dcscrobbler->setting('feed',__('Source:'),'rtracks','combo',
                                   array(__('Recent Tracks')  => 'recenttracks',
                                         __('Weekly Top Artists') => 'weeklyartistchart',
                                         __('Weekly Top Tracks')  => 'weeklytrackchart',
                                         __('Top Artists')    => 'topartists',
                                         __('Top Tracks')     => 'toptracks'));

    $widgets->dcscrobbler->setting('count',__('Limit (leave empty for no limit):'),'');
    $widgets->dcscrobbler->setting('showlogo',__('Show logo'),1,'check');
    $widgets->dcscrobbler->setting('homeonly',__('Home page only'),1,'check');
  }
}

?>