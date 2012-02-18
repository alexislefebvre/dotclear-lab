<?php
/**
 * @author Nicolas Frandeboeuf <nicofrand@gmail.com>
 * @version 1.0.1
 * @package feed2img
 */

if (!defined("DC_CONTEXT_ADMIN"))
    return;

// We read the plugin version
$m_version = $core->plugins->moduleInfo("feed2img", "version");

// We read the plugin version from the version database table
$i_version = $core->getVersion("feed2img");

/**
 * If the db version is greater or equals the module version, then the plugin
 * is already installed and up to date.
 */
if (version_compare($i_version,$m_version, ">="))
    return;

// Load translations of alert messages
$_lang = $core->auth->getInfo("user_lang");
$_lang = preg_match("/^[a-z]{2}(-[a-z]{2})?$/", $_lang) ? $_lang : "en";
l10n::set(dirname(__FILE__)."/locales/".$_lang."/admin");

if (version_compare(DC_VERSION, "2.3.1", "<"))
    throw new Exception('Feed2img requires Dotclear 2.3.1 or greater');

// Create the workspace
$core->auth->user_prefs->addWorkSpace('feed2img');

// Register the plugin preferences in the workspace
$core->auth->user_prefs->feed2img->put("show_blog_title", true, "boolean", __("Print the blog's title"));
$core->auth->user_prefs->feed2img->put("number_of_posts", '3', "integer", __("Number of Posts"));
$core->auth->user_prefs->feed2img->put("max_chars_by_line", "80", "integer", __("Maximum number of chars by line"));
$core->auth->user_prefs->feed2img->put("title_font_size", "13", "float", __("Title's font size"));
$core->auth->user_prefs->feed2img->put("title_x", "9", "integer", __("Title position in pixels from left"));
$core->auth->user_prefs->feed2img->put("title_y", "21", "integer", __("Title position in pixels from top"));
$core->auth->user_prefs->feed2img->put("title_color", '#1a2d52', 'string', __("Title color"));
$core->auth->user_prefs->feed2img->put("text_font_size", "10", "float", __("Text font size"));
$core->auth->user_prefs->feed2img->put("text_color", '#43403e', "string", __("Text color"));
$core->auth->user_prefs->feed2img->put("text_x", "43", "integer", __("Text position in pixels from left"));
$core->auth->user_prefs->feed2img->put("text_y", "44", "integer", __("Text position in pixels from top"));
$core->auth->user_prefs->feed2img->put("line_height", "23", "float", __("Line height"));
$core->auth->user_prefs->feed2img->put("image_source", "", "string", __("Source image name in public directory. Let it empty to use the default image"));
$core->auth->user_prefs->feed2img->put("image_output", "feed2img_output.png", "string", __("Output image name in public directory"));
$core->auth->user_prefs->feed2img->put("font", "", "string", __("Font name (only .ttf) in public directory. Let it empty to use the default font"));

$public_dir = path::real(path::fullFromRoot($core->blog->settings->public_path, DC_ROOT), false);

if (!is_file($public_dir."/feed2img_output.png"))
{
    require_once(dirname(__FILE__)."/inc/class.feed2img.php");
    $entriesSelection = new EntriesSelection();
    $entriesSelection->install();
}

$core->setVersion("feed2img", $m_version);
?>