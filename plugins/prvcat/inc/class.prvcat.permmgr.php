<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# Copyright (c) 2010 Arnaud Renevier
# published under the modified BSD license.
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }


class prvCatPermMgr {
    const dbname = 'prvcat';
    const oldpwd_dbname = 'prvcat_oldpwd';

    const STATUS_UNKNOWN = 0;
    const STATUS_ALLOWED = 1;
    const STATUS_DENIED = 2;

    protected $connection = null;
    protected $prefix = '';
    protected $table = '';
    protected $oldpwd_table = '';

    private static $status = self::STATUS_UNKNOWN;
    private static $password;

    public function __construct(dbLayer $connection, $prefix) {
        $this->connection =& $connection;
        $this->prefix = $prefix;
        $this->table = $prefix.self::dbname;

        # oldpwd_table is used to store post_password of posts before being
        # managed by prvcat. If user has set a password to a specific post, and
        # then sets a category private, and unsets that category, we want the
        # first password to be restored.
        $this->oldpwd_table = $prefix.self::oldpwd_dbname;
    }

    public function install() {
        $s = new dbStruct($this->connection,$this->prefix);
        $table = $s->table(self::dbname);

        $table->field('cat_id','bigint',0,false)->
                field('private', 'smallint', 0, true, 0)->
                field('uuid', 'varchar', 22, true, NULL)->
                primary('pk_prvcat_cat_id', 'cat_id')->
                reference('fk_prvcat_cat_id','cat_id','category','cat_id','cascade','cascade');

        $oldpwd_table = $s->table(self::oldpwd_dbname);
        $oldpwd_table->field('post_id','bigint',0,false)->
                field('post_password', 'varchar', 32, true, 0)->
                primary('pk_prvcat_oldpwd_post_id', 'post_id')->
                reference('fk_prvcat_oldpwd_post_id','post_id','post','post_id','cascade','cascade');

        $si = new dbStruct($this->connection,$this->prefix);
        $changes = $si->synchronize($s);
    }

    public function isprivate($cat_id) {
        $query = 'SELECT private FROM '.$this->table.
                 ' WHERE cat_id = \''.
                 $this->connection->escape($cat_id).
                 '\'';
        return $this->connection->select($query)->private;
    }

    public function setoldpwd($post_id, $post_password) {
        if (is_null($post_password)) {
            $query = ('DELETE FROM '.$this->oldpwd_table.
                    ' WHERE post_id=\''.
                    $this->connection->escape($post_id).
                    '\'');
            $this->connection->execute($query);
            return;
        }

        $cur = $this->connection->openCursor($this->oldpwd_table);
        $cur->post_id = $post_id;
        $cur->post_password = $post_password;
        if (is_null($this->getoldpwd($post_id))) {
            $cur->insert();
        } else {
            $cur->update();
        }
    }

    public function getoldpwd($post_id) {
        $query = 'SELECT post_password FROM '.$this->oldpwd_table.
                 ' WHERE post_id = \''.
                 $this->connection->escape($post_id).
                 '\'';
        return $this->connection->select($query)->post_password;
    }

    public function setpassword($password) {
        global $core;
        $dc_version = $core->getVersion('core');
        if (version_compare ($dc_version, "2.2-alpha1-r1") >= 1) {
            $core->blog->settings->addNamespace('prvcat');
            $core->blog->settings->prvcat->put('password',$password,'string','prvcat password',true,false);
        } else {
            $core->blog->settings->setNamespace('prvcat');
            $core->blog->settings->put('prvcat_password', $password, 'string', '', true, true);
        }
        self::$password = $password;
    }

    public function getcatidforuuid($uuid) {
        $query = 'SELECT cat_id FROM '.$this->table.
                 ' WHERE uuid = \''.
                 $this->connection->escape($uuid).
                 '\'';
        return $this->connection->select($query)->cat_id;
    }

    public function getuuid($cat_id) {
        $query = 'SELECT uuid FROM '.$this->table.
                 ' WHERE cat_id = \''.
                 $this->connection->escape($cat_id).
                 '\'';
        return $this->connection->select($query)->uuid;
    }

    public function setprivate($cat_id, $private) {
        global $core;
        $old = $this->isprivate($cat_id);
        if ($old == $private) {
            return;
        }
        $prvcat_cur = $this->connection->openCursor($this->table);
        $prvcat_cur->private = $private ? 1 : 0;

        if (isset($old)) {
            $prvcat_cur->update('WHERE cat_id=\''.
                         $this->connection->escape($cat_id).
                         '\'');
        } else {
            $prvcat_cur->cat_id = $cat_id;
            $uuid = uniqid('', true);
            # as we want to use this identifier in an url, removes dot it contains
            $uuid = substr($uuid, 0, 14).substr($uuid, 15);
            $prvcat_cur->uuid = $uuid;
            $prvcat_cur->insert();
        }

        $posts = $core->blog->getPosts(array('cat_id' => (integer) $cat_id));

        $cur = $this->connection->openCursor($this->prefix.'post');

        if ($private) {
            # set oldpassword
            while ($posts->fetch()) {
               if ($posts->post_password and (is_null ($this->getoldpwd($posts->post_id))) and ($posts->post_password != self::$password)) { 
                    $this->setoldpwd($posts->post_id, $posts->post_password);
                } 
            }

            # store password in dc_post
            $cur->post_password = $this->getpassword();
            $cur->update('WHERE cat_id=\''.
                         $this->connection->escape($cat_id).
                         '\'');
        } else {
            # restore old password in dc_post
            while ($posts->fetch()) {
                $cur->post_password = $this->getoldpwd($posts->post_id);
                $cur->update('WHERE post_id=\''.
                         $this->connection->escape($posts->post_id).
                         '\'');
            }

            $query = ('DELETE FROM '.$this->oldpwd_table.
                      ' WHERE post_id IN (SELECT post_id from '.
                      $this->prefix.'post '.
                      'WHERE cat_id=\''.
                      $this->connection->escape($cat_id).
                      '\')');

            $this->connection->execute($query);
        }
    }

    public function getpassword() {
        if (isset (self::$password)) {
            return self::$password;
        }
        global $core;
        $dc_version = $core->getVersion('core');
        if (version_compare ($dc_version, "2.2-alpha1-r1") >= 1) {
            self::$password = $core->blog->settings->prvcat->password;
        } else {
            self::$password = $core->blog->settings->prvcat_password;
        }
        return self::$password;
    }

    public function checkpwd($password) {
        $res = $this->getpassword();
        if ($res === null) {
            return false;
        }
        return ($res == $password);
    }

    public function isallowed($cat_id) {
        $this->statusinit();
        return (!$this->isprivate($cat_id)) or (self::$status == self::STATUS_ALLOWED);
    }

    private function statusinit() {
        if (self::$status != self::STATUS_UNKNOWN) {
            # already initialized
            return;
        }


        if (isset($_COOKIE['dc_passwd'])) {
            $pwd_cookie = unserialize ($_COOKIE['dc_passwd']);
        }

        global $core;
        $without_password = $core->blog->without_password;
        $core->blog->without_password = false;
        $posts = $core->blog->getPosts(array('sql' => ' AND post_password IS NOT NULL'));
        $core->blog->without_password = $without_password;

        if (isset($_POST['password_prvcat'])) {
            $post_password = $_POST['password_prvcat'];
            if (!$this->checkpwd($post_password)) {
                # wrong password: status becomes denied
                self::$status = self::STATUS_DENIED;
                return;
            }

            # correct password: status becomes allowed, and password is stored
            # in dc_passwd for all posts in private categories
            while ($posts->fetch()) {
                if ($posts->cat_id and $this->isprivate($posts->cat_id)) {
                    $pwd_cookie[$posts->post_id] = $post_password;
                }
            }
            setcookie('dc_passwd',serialize($pwd_cookie),0,'/');
            self::$status = self::STATUS_ALLOWED;
        } else {
            # password is not send in post request. Try to get it from cookies
            while ($posts->fetch()) {
                if ($posts->cat_id and $this->isprivate($posts->cat_id)) {
                    if (!isset($pwd_cookie[$posts->post_id]) or !$this->checkpwd($pwd_cookie[$posts->post_id])) {
                        # at least one missing or wrong password in cookies
                        self::$status = self::STATUS_DENIED;
                        return;
                    }
                }
            }
            self::$status = self::STATUS_ALLOWED;
        }

    }

}
?>
