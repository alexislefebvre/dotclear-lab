<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of dcOpenSearch, a plugin for Dotclear.
# 
# Copyright (c) 2009 Tomtom and Pierre Ammeloot
# http://blog.zenstyle.fr | http://pierre.ammeloot.fr
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class dcEngineMeta extends dcSearchEngine
{
	public $type;
	public $name;
	public $label;
	public $description;
	
	protected $has_gui = true;
	
	protected function setInfo()
	{
		$this->type = 'meta';
		$this->label = __('Meta data');
		$this->description = __('Meta search engine');
	}
	
	public function getResults($q = '',$count_only = false)
	{
		$res = $params = array();
		
		$params['post_type'] = '';
		
		$params['from'] = ', '.$this->core->prefix.'meta M ';
		
		$params['sql'] = 'AND M.post_id = P.post_id ';
		$params['sql'] .= "AND LOWER(M.meta_id) LIKE LOWER('%".$this->core->con->escape($q)."%') ";
		foreach (explode(',',$this->getEngineConfig('type')) as $k => $v) {
			$params['sql_meta_type'][] = "M.meta_type = '".$this->core->con->escape(trim($v))."'";
		}
		$params['sql'] .= 'AND ('.implode(' OR ',$params['sql_meta_type']).')';
		$params['limit'] = $this->getLimit();
		
		unset($params['sql_meta_type']);
		
		$rs = $this->core->blog->getPosts($params,$count_only);
		
		if ($count_only) {
			return $rs->f(0);
		}
		
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
				'search_cat_id' => $rs->cat_id,
				'search_cat_title' => $rs->cat_title,
				'search_cat_url' => $rs->cat_url,
	 			'search_dt' => $rs->post_creadt,
	 			'search_tz' => $rs->post_tz,
				'search_content' => $content,
				'search_comment_nb' => $rs->nb_comment,
				'search_trackback_nb' => $rs->nb_trackback,
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
		$type =  $this->getEngineConfig('type') !== null ? $this->getEngineConfig('type') : 'tag';
		
		if (isset($_POST['save']))
		{
			try {
				$this->addEngineConfig('display',$_POST['display']);
				$this->addEngineConfig('type',$_POST['type']);
				$this->saveEngineConfig();
				http::redirect($url.'&config=1');
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		
		if (!empty($_GET['config'])) {
			$res .= '<p class="message">'.__('Configuration has been successfully saved').'</p>';
		}
		
		$res .=
		'<form action="'.html::escapeURL($url).'" method="post">'.
		'<fieldset><legend>'.__('General').'</legend>'.
		'<p><label>'.__('Content type:').
		form::combo('display',$value,$this->getEngineConfig('display')).
		'</label></p>'.
		'<p><label class="required">'.__('Meta type:').
		form::field('type',30,255,$type).'</label></p>'.
		'<p class="form-note">'.__('Entrer meta types separated by coma').'</p>'.
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