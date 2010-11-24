<?php

/**
 *
 */
class dcPublicWidget {

    protected static function serveWidget($tpl,
            $content_type='text/html', $http_cache=true,
            $http_etag=true) {

        $_ctx =& $GLOBALS['_ctx'];
        $core =& $GLOBALS['core'];

        if ($_ctx->nb_entry_per_page === null) {
            $_ctx->nb_entry_per_page =
                $core->blog->settings->nb_post_per_page;
        }

        $tpl_file = $core->tpl->getFilePath($tpl);

        if (!$tpl_file) {
            throw new Exception('Unable to find template');
        }

        if ($http_cache) {
            $GLOBALS['mod_files'][] = $tpl_file;
            http::cache($GLOBALS['mod_files'],$GLOBALS['mod_ts']);
        }

        $result = new arrayObject;

        header('Content-Type: '.$content_type.'; charset=UTF-8');
        $result['content'] = $core->tpl->getData($tpl);
        $result['content_type'] = $content_type;
        $result['tpl'] = $tpl;
        $result['blogupddt'] = $core->blog->upddt;

        if ($http_cache && $http_etag) {
            http::etag($result['content'],http::getSelfURI());
        }

        return $result['content'];
    }
}
?>