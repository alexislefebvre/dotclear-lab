<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2 Gallery plugin.
#
# Copyright (c) 2004-2008 Bruno Hondelatte, and contributors. 
# Many, many thanks to Olivier Meunier and the Dotclear Team.
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dcGalleryThemeAdapter
{
	protected $core;
	protected $theme;
	protected $tag_tpl;
	protected $post_tpl;

	//protected $tag_block = '<tpl:(%1$s)(?:(\s+.*?)>|>)(.*)</tpl:%1$s>';
	protected $tag_block = '<tpl:(%1$s)(?:(\s+.*?)>|>)((?:.(?!/tpl:%1$s>))*.)</tpl:%1$s>';

	public function __construct($core,$theme)
	{
		$this->core =& $core; 
		$this->core->themes = new dcThemes ($this->core);
		$this->core->themes->loadModules ($this->core->blog->themes_path, null);
		$this->theme = $this->core->themes->getModules($theme);
		$this->post_tpl = $this->theme['root']."/tpl/post.html";
		$this->tag_tpl = $this->theme['root']."/tpl/tag.html";
		$this->gal_theme_dir = $this->theme['root']."/tpl/gal_simple";
	}


	private function process_replace ($replacements, &$f) {
		foreach ($replacements as $r) {
			switch (count($r)) {
			case 2:
				$f = preg_replace('#'.$r[0].'#ms',$r[1],$f);
				break;
			case 3:
				$f = preg_replace_callback('#'.$r[0].'#ms',$r[1],$f,$r[2]);
				break;
			}
		}
	}

	public function checkTheme($force=false) {
		if (!$this->theme['root_writable']) {
			$this->core->error->add (sprintf(__('Theme %s is not writeable'),
				html::escapeHTML($this->theme['name'])));
			return false;
		}
		if (file_exists($this->gal_theme_dir) && !$force) {
			$this->core->error->add(sprintf(__('Theme "%s" already contains gallery template files'),
				html::escapeHTML($this->theme['name'])));
			return false;
		}
		if (!file_exists($this->post_tpl) && !file_exists($this->tag_tpl)) {
			$this->core->error->add(sprintf(__('Theme "%s" does not need to be adapted, no html templates are overriden from default theme.'),
				html::escapeHTML($this->theme['name'])));
			return false;
		}
		return true;
		
	}

	public function generateImageTpl($dir) {
		$replacements = array(
			array('(</head>)','{{tpl:GalleryStyleURL}}'."\n".'$1'),
			array(sprintf($this->tag_block,"Attachments"),''),
			array('<div class=\"[^"]*post-content[^"]*\">{{tpl:EntryContent}}</div>','{{tpl:GalleryInclude src="image_item.html"}}'),
			array('<tpl:EntryIf extended=\"1\">((?:.(?!/tpl:EntryIf>))*.)</tpl:EntryIf>',''),
			array('tpl:Entry(Previous|Next)','tpl:GalleryItem$1'),
			array('(<tpl:GalleryItemPrevious.*){{tpl:EntryURL}}(.*</tpl:GalleryItemPrevious>)','$1{{tpl:GalleryItemURL}}$2'),
			array('(<tpl:GalleryItemNext.*){{tpl:EntryURL}}(.*</tpl:GalleryItemNext>)','$1{{tpl:GalleryItemURL}}$2'),
			array('{{tpl:BlogFeedURL type=\"([^"]*)\"}}/comments','{{tpl:GalleryItemFeedURL type=\"$1\"}}/comments'),
			array('(<link)([^<]*)href=\"{{tpl:EntryURL}}\"','$1$2href="{{tpl:GalleryItemURL}}"'),
			array('post\'s comments','image\'s comments'),
			array('(<div id=\"content\">)',"$1\n <p id=\"gallink\">\n <tpl:GalleryItemGallery>\n <a href=\"{{tpl:EntryURL}}\" title=\"Retour à la galerie\">&#171; Retour à la galerie [{{tpl:EntryTitle}}]</a>\n </tpl:GalleryItemGallery>\n </p>")
			/*array('{{tpl:EntryURL}}','{{tpl:GalleryItemURL}}')*/
			);
		$f = file_get_contents($this->post_tpl);
		$this->process_replace($replacements, $f);
		return file_put_contents($dir."/image.html",$f);
	}

	public function generateGalleryTpl($dir) {
		$replacements = array(
			array('(<link rel=\"alternate\")',"<link rel=\"alternate\" type=\"application/rss+xml\" title=\"RSS 2.0\" href=\"{{tpl:GalleryFeedURL type=\"mediarss\"}}/{{tpl:EntryID}}\" />\n  \$1"),
			array('(</head>)','{{tpl:GalleryStyleURL}}'."\n".'$1'),
			array(sprintf($this->tag_block,"Attachments"),''),
			array('<div class=\"[^"]*post-content[^"]*\">{{tpl:EntryContent}}</div>','{{tpl:GalleryInclude src="gallery_item.html"}}'),
			array('<tpl:EntryIf extended=\"1\">((?:.(?!/tpl:EntryIf>))*.)</tpl:EntryIf>',''),
			array('tpl:Entry(Previous|Next)','tpl:GalleryEntry$1'),
			array('{{tpl:BlogFeedURL type=\"([^"]*)\"}}/comments','{{tpl:GalleryFeedURL type=\"$1\"}}/comments'),
			array('post\'s comments','gallery\'s comments')
			);
		$f = file_get_contents($this->post_tpl);
		$this->process_replace($replacements, $f);
		return file_put_contents($dir."/gallery.html",$f);
	}

	public function generateGalleriesTpl($dir) {
		$replacements = array(
			array('tpl:Entries([ |>])','tpl:GalleryEntries$1'),
			array('{{tpl:lang Tag}} - {{tpl:MetaID}}','{{tpl:lang Galleries}}'),
			array('{{tpl:lang Tag}} - {{tpl:TagID}}','{{tpl:lang Galleries}}'),
			array('<tpl:EntryIf has_attachment=\"1\">([^"<tpl:EntryIf"]*?)</tpl:EntryIf>',''),
			array(sprintf($this->tag_block,"Attachments"),''),
			array('(</head>)','{{tpl:GalleryStyleURL}}'."\n".'$1'),
			array('dc-tag','dc-galleries'),
			array('({{tpl:lang)([^}]*)entries([}]*}})','$1$2galleries$3'),
			array('(<tpl:GalleryEntries>)',"\$1   <tpl:EntryIfNewCat>\n <p class=\"gallery-cat\">{{tpl:EntryCategoryWithNull}}</p>\n </tpl:EntryIfNewCat>"),
			array('(<tpl:GalleryEntries no_content=\"1\">)',
			"<tpl:Categories>\n  <link rel=\"section\" href=\"{{tpl:CategoryURL}}\" title=\"{{tpl:CategoryTitle encode_html=\"1\"}}\" />\n  </tpl:Categories>\n\n  \$1"),
			/*array('tags','galleries'),*/
			array('This tag','This gallery'),
			array('({{tpl:EntryTitle encode_html=\"1\"}})','$1 ({{tpl:GalleryItemCount}})'),
			array('{{tpl:lang Tag}} : {{tpl:MetaID}}','{{tpl:lang Galleries}}'),
			array('{{tpl:lang Tag}} : {{tpl:TagID}}','{{tpl:lang Galleries}}'),
			array('TagFeedURL','GalleryFeedURL'),
			array('<h2([^>])*class="post-title"([^>])*>',"<tpl:Attachments>\n<img src=\"{{tpl:GalleryAttachmentThumbURL size=\"sq\" bestfit=\"yes\"}}\" alt=\"{{tpl:AttachmentTitle}}\" style=\"float: left;\"/>\n</tpl:Attachments>\n<h2\$1 class=\"post-title\"\$2>"));
		$f = file_get_contents($this->tag_tpl);
		$this->process_replace($replacements, $f);
		return file_put_contents($dir."/galleries.html",$f);
	}

	public function generateAllTemplates($force=false) {
		if (!$this->checkTheme($force))
			return false;	
		try {
			files::makeDir($this->gal_theme_dir);
		} catch (Exception $e) {
			if (! (file_exists($this->gal_theme_dir) && $force))
			{
				$this->core->error->add (
					__("Could not create directory %s. Error is : %s",
					$this->gal_theme_dir,
					$e->getMessage()));
				return false;
			}
		}
		$errormsg = __("Could not generate %s template. Aborting.");
		if (!$this->generateImageTpl($this->gal_theme_dir)) {
			$this->core->error->add (sprintf($errormsg,
			html::escapeHTML($this->gal_theme_dir."/image.html")));
			return false;
		}
		if (!$this->generateGalleryTpl($this->gal_theme_dir)) {
			$this->core->error->add (sprintf($errormsg,
			html::escapeHTML($this->gal_theme_dir."/gallery.html")));
			return false;
		}
		if (!$this->generateGalleriesTpl($this->gal_theme_dir)) {
			$this->core->error->add (sprintf($errormsg,
			html::escapeHTML($this->gal_theme_dir."/galleries.html")));
			return false;
		}
		return true;
	}
}
?>