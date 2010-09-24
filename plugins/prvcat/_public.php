<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# Copyright (c) 2010 Arnaud Renevier
# published under the modified BSD license.
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class prvCatPublic extends dcUrlHandlers {
    protected static function urlinfos($url) {
        $type = $args = '';
        if ($url->mode == 'path_info') {
            $part = substr($_SERVER['PATH_INFO'],1);
        } else {
            $part = '';
            $qs = $url->parseQueryString();
            if (!empty($qs)) {
                list($k,$v) = each($qs);
                if ($v === null) {
                    $part = $k;
                 }
            }
        }
        $url->getArgs($part,$type,$args);
        return array('type' => $type,
                     'args' => $args);
    }

    public static function beforeDocumentCallback(dcCore $core) {
        $infos = self::urlinfos($core->url);
        $args = $infos['args'];
        self::getPageNumber($args);
        if ($infos['type'] == 'category') {
            $category = $core->blog->getCategories(array('cat_url' => $args));
            $cat_id = $category->cat_id;
            $perms = new prvCatPermMgr($core->con, $core->prefix);
            if (is_null($cat_id) or  (!($perms->isprivate($cat_id)))) {
                return;
            }
            if ($perms->isallowed($cat_id)) {
                $core->blog->withoutPassword(false);
                return;
            } 
            $core->tpl->setPath($core->tpl->getPath(), dirname(__FILE__).'/default-templates');
            global $_ctx;
            $_ctx->categories = $category;
            self::load('prvcat-password.html','text/html',false);
            exit;
        } else if ($infos['type'] == 'feed') {
            if (strpos($args, "category/") === 0) {
                $id = substr($infos['args'], strlen("category/"));
                $perms = new prvCatPermMgr($core->con, $core->prefix);
                $cat_id = $perms->getcatidforuuid($id);
                if ($cat_id and $perms->isprivate($cat_id)) {

                    $category = $core->blog->getCategories(array('cat_id' => $cat_id));
                    global $_ctx;
                    $_ctx->categories = $category;
                    $core->blog->withoutPassword(false);

                    // FIXME: we cannot get feed typen in CategoryFeedURL
                    // callback, so we just use atom
                    self::load('atom.xml','application/atom+xml',true);
                    exit;
                }
            }
        }
    }

    public static function beforeContentFilterCallback(dcCore $core, $tag, $args) {
        # used to give link to hashed url when reaching private category page
        if ($tag != "CategoryFeedURL") {
            return;
        }
        if ($core->url->type != "category") {
            return;
        }

        global $_ctx;
        $cat_id = $_ctx->categories->cat_id;

        $perms = new prvCatPermMgr($core->con, $core->prefix);
        if (is_null($cat_id) or (!($perms->isprivate($cat_id))) or (!($perms->isallowed($cat_id)))) {
            return;
        }
        $uuid = $perms->getuuid($cat_id);
        $args[0] = $core->blog->url.$core->url->getBase("feed")."/category/".$uuid;
    }

    public static function load($tpl,$content_type='text/html',$http_cache=true,$http_etag=true) {
        self::serveDocument($tpl,$content_type,$http_cache,$http_etag);
    }
}

$core->addBehavior('publicBeforeDocument', array('prvCatPublic', 'beforeDocumentCallback'));
$core->addBehavior('publicBeforeContentFilter', array('prvCatPublic', 'beforeContentFilterCallback'));
?>
