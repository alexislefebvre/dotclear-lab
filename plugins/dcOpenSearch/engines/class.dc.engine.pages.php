<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom
# http://blog.zenstyle.fr/
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcEnginePages extends dcSearchEngine
{
	public $type;
	public $name;
	public $label;
	public $description;
	public $active = true;
	
	protected $core;
	protected $has_gui = true;
	protected $gui_url = null;
	
	public function __construct($core)
	{
		parent::__construct($core);
	}
	
	protected function setInfo()
	{
		$this->type = 'page';
		$this->label = __('Pages');
		$this->description = __('Pages search engine');
	}
	
	public function getResults($q = '')
	{
		$res = array();
		
		$rs = $this->core->blog->getPosts(array('post_type' => 'page', 'search' => $q));
		
		while ($rs->fetch()) {
			if ($rs->post_excerpt_xhtml === '') {
				$content = $rs->post_content_xhtml;
			}
			elseif ($this->getEngineConfig('display') === 'both') {
				$content = $rs->post_excerpt_xhtml.$rs->post_content_xhtml;
			}
			elseif ($this->getEngineConfig('display') === 'excerpt') {
				$content = $rs->post_excerpt_xhtml;
			}
			else {
				$content = $rs->post_content_xhtml;
			}
			$res[] = array(
				'search_id' => $rs->post_id,
				'search_url' => $rs->post_url,
				'search_title' => $rs->post_title,
				'search_author_id' => $rs->user_id,
				'search_author_name' => $rs->getAuthorCN(),
				'search_author_url' => $rs->user_url,
				'search_cat_id' => null,
				'search_cat_title' => null,
				'search_cat_url' => null,
	 			'search_dt' => $rs->post_creadt,
	 			'search_tz' => $rs->post_tz,
				'search_content' => $content,
				'search_comment_nb' => $rs->nb_trackback,
				'search_trackback_nb' => $rs->nb_comment,
				'search_engine' => $this->name,
				'search_lang' => $rs->post_lang,
				'search_type' => $this->type
			);
		}
		
		return $res;	
	}
	
	public function gui($url)
	{
		$res = '';
		
		$value = array(
			__('Content only') => 'content',
			__('Excerpt when it exists') => 'excerpt',
			__('Both') => 'both'
		);
		
		if (isset($_POST['save']))
		{
			try {
				$this->addEngineConfig('display',$_POST['display']);
				$this->saveEngineConfig();
				http::redirect($url.'&config=1');
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		
		if (!empty($_GET['config'])) {
			$res .= '<p class="message">'.__('Configuration have been successfully saved.').'</p>';
		}
		
		$res .=
		'<form action="'.html::escapeURL($url).'" method="post">'.
		'<fieldset><legend>'.__('General').'</legend>'.
		'<p class="field"><label class="classic">'.form::combo('display',$value,$this->getEngineConfig('display')).' '.
		__('Content type:').'</label></p>'.
		'</fieldset>'.
		$this->core->formNonce().
		'<p><input type="submit" name="save" value="'.__('Save').'"/></p>'.
		'</form>';
		
		return $res;
	}
	
	public static function getItemAdminURL($rs)
	{
		return $GLOBALS['core']->getPostAdminURL($rs->search_type,$rs->search_id);
	}
	
	public static function getItemPublicURL($rs)
	{
		return $GLOBALS['core']->blog->url.$GLOBALS['core']->getPostPublicURL($rs->search_type,$rs->search_url);
	}
	
	public static function getItemContent($rs)
	{
		return $rs->search_content;
	}
}

?>