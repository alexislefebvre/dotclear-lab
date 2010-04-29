<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# Copyright (c) 2010 Arnaud Renevier
# published under the modified BSD license.
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Plugins']->addItem(
    # nom du lien (en anglais)
    __('private categories'),
    # URL de base de la page d'administration
    'plugin.php?p=prvcat',
    # URL de l'image utilisée comme icône
    'index.php?pf=prvcat/icon.png',
    # expression régulière de l'URL de la page d'administration
    preg_match('/plugin.php\?p=prvcat(&.*)?$/',
        $_SERVER['REQUEST_URI']),
    # persmissions nécessaires pour afficher le lien
    $core->auth->check('categories',$core->blog->id));

class prvCatAdmin {
    public static function afterPostCreateCallback($cur_post, $post_id) {
        $old_pwd = $cur_post->post_password;
        self::update($cur_post, $post_id);
        $new_pwd = $cur_post->post_password;
        if ($old_pwd != $new_pwd) {
            // we need to store password in database ourselve
            global $core;
            $cur = $core->con->openCursor($core->prefix.'post');
            $cur->post_password = $cur_post->post_password;
            $cur->update('WHERE post_id=\''.
                         $core->con->escape($post_id).
                         '\'');
        }
    }

    public static function beforePostUpdateCallback($cur_post, $post_id) {
        global $core;
        $query = 'SELECT cat_id FROM '.$core->prefix.'post'.
                 ' WHERE post_id = \''.
                 $core->con->escape($post_id).
                 '\'';
        $old_cat_id = $core->con->select($query)->cat_id;
        $new_cat_id = $cur_post->cat_id;
        if ($old_cat_id != $new_cat_id) {
            $perms = new prvCatPermMgr($core->con, $core->prefix);
            $old_is_private = $perms->isprivate($old_cat_id);
            $new_is_private = $perms->isprivate($new_cat_id);
            if ($old_is_private == $new_is_private) {
                # if both categories are private, password should not change.
                # If user manually set password while changing from one private
                # category to another private one, we don't garantee a behaviour
                return;
            }
            if ($old_is_private && !$new_is_private) {
                $cur_post->post_password = $perms->getoldpwd($post_id);
                $query = ('DELETE FROM '.$core->prefix.prvCatPermMgr::oldpwd_dbname.
                      ' WHERE post_id=\''.
                      $core->con->escape($post_id).
                      '\'');
                $core->con->execute($query);
                return;
            } else { # $new_is_private && !$old_is_private
                self::update($cur_post, $post_id);
            }
        } else {
            self::update($cur_post, $post_id);
        }
    }

    private static function update($cur_post, $post_id) {
        # when user sets a password for a post in private category, store that password in old_password table
        $cat_id = $cur_post->cat_id;
        if (!isset($cat_id)) {
            return;
        }
        global $core;
        $perms = new prvCatPermMgr($core->con, $core->prefix);
        if (!$perms->isprivate($cat_id)) {
            return;
        }
        $password = $cur_post->post_password;
        $cat_password = $perms->getpassword();
        if ($password == $cat_password) {
            # password set for post is the same as category password
            return;
        }

        # post_password will be category password
        $perms->setoldpwd($post_id, $password);
        $cur_post->post_password = $cat_password;
    }
}

$core->addBehavior('adminBeforePostUpdate', array('prvCatAdmin', 'beforePostUpdateCallback'));
# we need to use *after* behavior to get post_id
$core->addBehavior('adminAfterPostCreate', array('prvCatAdmin', 'afterPostCreateCallback'));
?>
