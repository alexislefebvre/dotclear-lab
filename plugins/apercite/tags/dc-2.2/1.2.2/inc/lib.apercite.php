<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Apercite, a plugin for Dotclear.
# 
# Copyright (c) 2009 Francis Besset
# francis.besset@free.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { return; }

class AperciteApi
{
  const APERCITE_URI        = 'www.apercite.fr';
  const APERCITE_PORT       = 80;
  const APERCITE_URI_UPDATE = '/api/maj-apercite/%1$s/%2$s/adresse/%3$s/%4$s/%5$s';

  public static function doUpdate($version, $login, $apiKey, $javascript, $java, $uri)
  {
    $javascript = ($javascript || $javascript === null ? 'oui' : 'non');
    $java = ($java || $java === null ? 'oui' : 'non');

    $path = sprintf(self::APERCITE_URI_UPDATE, $login, $apiKey, $javascript, $java, $uri);

    $o = new netHttp(self::APERCITE_URI, self::APERCITE_PORT);
    $o->setUserAgent('Clearbricks HTTP Client - DotClear 2 - '.$version);

    return $o->get($path);
  }
}

class aperciteBehaviors
{
  public static function aperciteForm()
  {
    echo '<h3 class="apercite-services">'.__('Apercite').'</h3>';

    $core =& $GLOBALS['core'];
    if ($core->blog->settings->apercite->enabled)
    {
      echo
        '<p class="apercite-services">
            <label class="classic">
              '.form::checkbox('apercite_update',1).' '.__('Apercite update thumbnails').'
            </label>
        </p>';
    }
    else
    {
      echo
        '<p class="apercite-services">'.__('Apercite disabled').'</p>';
    }
  }

  public static function doUpdate($cur)
  {
    if (empty($_POST['apercite_update'])) { return; }

    $core = $GLOBALS['core'];
    if (!$core->blog->settings->apercite->enabled) { return; }

    $login  = $core->blog->settings->apercite->login;
    $apiKey = $core->blog->settings->apercite->apiKey;
    if (empty($login) OR empty($apiKey)) { return; }

    $post = $cur->getField('post_excerpt_xhtml').$cur->getField('post_content_xhtml');
    if (preg_match_all('#<a(?:.*)href="((?:(?:https?://)?(?:(?:[-_a-zA-Z0-9]+\.)*(?:[-a-zA-Z0-9]{1,63})\.(?:[a-zA-Z]{2,4})|(?:(?:[0-1]|[0-9]{2}|1[0-9]{2}|2(?:[0-4][0-9]|5[0-5]))\.){3}(?:[0-1]|[0-9]{2}|1[0-9]{2}|2(?:[0-4][0-9]|5[0-5]))))?(?:\:[0-9]{0,5})?(?:/(?:[^"])*)?)#', $post, $match)) {
      if (isset($match[1]) && is_array($match[1]))
      {
        $tab_uri = array();
        foreach ($match[1] as $k => $v)
        {
          if (!empty($v))
          {
            $update = true;
            if ($v{0} == '/')
            {
              if ($core->blog->settings->apercite->localLink || $core->blog->settings->apercite->localLink === null)
              {
                $v = $core->blog->host.$v;
              }
              else
              {
                $update = false;
              }
            }

            if ($update && !in_array($v, $tab_uri))
            {
              $tab_uri[] = $v;

              AperciteApi::doUpdate(
                $core->getVersion('apercite'),
                $core->blog->settings->apercite->login,
                $core->blog->settings->apercite->apiKey,
                $core->blog->settings->apercite->javascript,
                $core->blog->settings->apercite->java,
                $v
              );
            }
          }
        }
      }
    }
  }
}
?>