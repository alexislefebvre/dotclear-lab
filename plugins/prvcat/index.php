<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# Copyright (c) 2010 Arnaud Renevier
# published under the modified BSD license.
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }
    dcPage::check('categories');
?>
<html>
<head>
<title><?php echo (__('private categories'))?></title>
  <?php
  echo dcPage::jsLoad('index.php?pf=prvcat/js/config.js');
  ?>
</head>
<body>

    <h2><?php echo html::escapeHTML($core->blog->name).' &rsaquo; '.  __('private categories'); ?></h2>
    <?php
    $categories = $core->blog->getCategories()->rows();

    if (empty($categories)) {
        echo '<p>'.__('No category yet.').'</p>';
    } else {
        $perms = new prvCatPermMgr($core->con, $core->prefix);

        $hasupdate = false;
        if (isset($_POST['prvcat_upd'])) {
            $hasupdate = true;
            $hasprivate = false;
            $pwdmismatch = false;
            $pwdmissing = false;

            foreach ($categories as $cat) {
                $catid = 'prvcat_'.$cat['cat_id'];
                if (isset($_POST[$catid]) and ($_POST[$catid] == "private")) {
                    $hasprivate = true;
                    break;
                }
            }

            if ($hasprivate) {
                if ($_POST['prvcat_pwd'] != $_POST['prvcat_pwd_confirm']) {
                    $pwdmismatch = true;
                } else if (!$_POST['prvcat_pwd']) {
                    $pwdmissing = false;
                }
            }

            if (!$pwdmismatch and !$pwdmissing) {
                $perms->setpassword($_POST['prvcat_pwd']);
                foreach ($categories as $cat) {
                    $catid = 'prvcat_'.$cat['cat_id'];
                    $perms->setprivate($cat['cat_id'], 
                                       (isset($_POST[$catid]) and ($_POST[$catid] == "private")),
                                       $_POST['prvcat_pwd']);
                }
            }
        } else {
            $pwdmismatch = $pwdmissing = false;
        }

        echo '<form id="prvcat-form" action="'.$p_url.'" method="post">'.
             '<fieldset>'.
             '<legend>'.__('Categories list').'</legend>';

        if ($hasupdate and !$pwdmismatch and !$pwdmissing) {
            echo '<p class="message">'.__('Configuration successfully updated').'</p>';
        }

        $ref_level = $level = $categories[0]['level'];
        foreach ($categories as $cat) {
            if ($pwdmissing) {
                $check_private = ($_POST['prvcat_'.$cat['cat_id']] == "private");
            } else {
                $check_private = $perms->isprivate($cat['cat_id']);
            }

            $li_content = html::escapeHTML($cat['cat_title']).'<br />'.
                          form::checkbox('prvcat_'.$cat['cat_id'], 'private', $check_private).
                          __('make private');

            if ($cat['level'] > $level) {
                echo str_repeat('<ul><li>',$cat['level'] - $level);
            } else if ($cat['level'] < $level) { 
                echo str_repeat('</li></ul>',-($cat['level'] - $level));
            }
            if ($cat['level'] <= $level) {
                echo '</li><li>';
            }
            echo $li_content;

            $level = $cat['level'];
        }
        if ($ref_level - $level < 0) {
            echo str_repeat('</li></ul>',-($ref_level - $level));
        }

        if ($pwdmismatch) {
            $label = __('Passwords do not match');
            $class = 'error';
        } else if ($pwdmissing) {
            $label = __('You need to enter a password');
            $class = 'error';
        } else {
            $label = __('Password to access private categories');
            $class = '';
        }

        # we use only one password for all categories, first because it's
        # probably more ergonomic for user. Also, browsers password managers do
        # not handle well different password in the same page. 
        # FIXME: We still save in the database one password for each private
        # category (it will be the same for all categories).
        echo '<label class="'.$class.'">'.$label.
              form::password('prvcat_pwd', 20, 32).
              '</label>';
        echo '<label class="'.$class.'">'._('Confirm password').
              form::password('prvcat_pwd_confirm', 20, 32).
              '</label>';
        echo '<input type="submit" name="prvcat_upd" value="'.__('Save').'" />'.
            $core->formNonce().
            '</fieldset>'.
             '</form>';
    }

dcPage::helpBlock('prvcat');
?>

</body>
</html>
