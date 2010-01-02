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

class dcEngineMedias extends dcSearchEngine
{
	public $type;
	public $name;
	public $label;
	public $description;
	
	protected $has_gui = true;
	
	protected function setInfo()
	{
		$this->type = 'media';
		$this->label = __('Medias');
		$this->description = __('Medias search engine');
	}
	
	public function getResults($q = '',$count_only = false)
	{
		$res = array();
		
		$strReq = $count_only ? 'SELECT COUNT(M.media_id) ' :
		'SELECT M.media_id, M.media_path, M.media_title, '.
		'M.media_file, M.media_meta, M.media_dt, M.media_creadt, '.
		'M.media_upddt, M.media_private, M.user_id, '.
		'U.user_name, U.user_firstname, U.user_displayname, U.user_url ';
		
		$strReq .=
		'FROM '.$this->core->prefix.'media M '.
		'INNER JOIN '.$this->core->prefix.'user U ON (M.user_id = U.user_id) WHERE';
		
		$words = text::splitWords($q);
			
		if (!empty($words)) {
			if ($words) {
				foreach ($words as $i => $w) {
					$words[$i] = "(media_title LIKE '%".$this->core->con->escape($w)."%' OR media_file LIKE '%".$this->core->con->escape($w)."%')";
				}
				$strReq .= ' '.implode(' AND ',$words).' ';
			}
		}
		
		$strReq .= " AND media_path = '".$this->core->blog->settings->public_path."'";
		
		if (!$this->core->auth->check('media_admin',$this->core->blog->id))
		{
			$strReq .= 'AND (media_private <> 1 ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR M.user_id = '".$this->core->con->escape($this->core->auth->userID())."'";
			}
			$strReq .= ') ';
		}
		
		$strReq .= $this->getLimit(true);
		
		$rs = $this->core->con->select($strReq);
		
		if ($count_only) {
			return $rs->f(0);
		}
		
		$media = new dcMedia($this->core);
		
		while ($rs->fetch())
		{
			$content = null;
			$f = $media->getFile($rs->media_id);
			
			if ($this->getEngineConfig('display_tb') && array_key_exists($this->getEngineConfig('display_tb'),$f->media_thumb)) {
				$tb = sprintf(
					'<img src="%1$s" alt="%2$s" title="%2$s" />',
					$f->media_thumb[$this->getEngineConfig('display_tb')],
					$rs->media_title
				);
				$content .= $this->core->blog->settings->lightbox_enabled ? sprintf('<p><a href="%1$s">%2$s</a></p>',$f->file_url,$tb) : '<p>'.$tb.'</p>'; 
			}
			if ($this->getEngineConfig('display_mp3') && files::getExtension($rs->media_file) == 'mp3') {
				$content .= dcMedia::mp3player($f->file_url,$this->core->blog->url.'pf=player_mp3.swf');
			}
			if ($this->getEngineConfig('display_flv') && files::getExtension($rs->media_file) == 'flv') {
				$content .= dcMedia::mp3player($f->file_url,$this->core->blog->url.'pf=player_flv.swf');
			}
			if ($this->getEngineConfig('display_meta')) {
				if (count($f->media_meta) > 0) {
					$d = array();
					foreach ($f->media_meta as $k => $v) {
						if ((string) $v) {
							$d[] = '<li><strong>'.$k.':</strong> '.html::escapeHTML($v).'</li>';
						}
					}
					$content .= count($d) > 0 ? sprintf('<p>%1$s</p><ul>%2$s</ul>',__('Details:'),implode('',$d)) : '';
				}
			}
			
			$res[] = array(
				'search_id' => $rs->media_id,
				'search_url' => $f->file_url,
				'search_title' => $rs->media_title,
				'search_author_id' => $rs->user_id,
				'search_author_name' => dcUtils::getUserCN($rs->user_id,$rs->user_name,$rs->user_firstname,$rs->user_displayname),
				'search_author_url' => $rs->user_url,
				'search_cat_id' => null,
				'search_cat_title' => null,
				'search_cat_url' => null,
	 			'search_dt' => $rs->media_creadt,
	 			'search_tz' => $this->core->blog->settings->blog_timezone,
				'search_content' => $content,
				'search_comment_nb' => null,
				'search_trackback_nb' => null,
				'search_engine' => $this->name,
				'search_lang' => $this->core->blog->settings->lang,
				'search_type' => $this->type
			);
		}
		
		return $res;	
	}
	
	public function gui($url)
	{
		$res = '';
		
		$value = array(
			__('Disable') => 'disable',
			__('Square') => 'sq',
			__('Small') => 's',
			__('Thumbnail') => 't',
			__('Medium') => 'm'
		);
		
		if (isset($_POST['save']))
		{
			try {
				$this->addEngineConfig('display_meta',$_POST['display_meta']);
				$this->addEngineConfig('display_tb',$_POST['display_tb']);
				$this->addEngineConfig('display_mp3',$_POST['display_mp3']);
				$this->addEngineConfig('display_flv',$_POST['display_flv']);
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
		'<p class="field"><label class="classic">'.form::checkbox('display_meta',1,$this->getEngineConfig('display_meta')).' '.
		__('Display meta data:').'</label></p>'.
		'</fieldset>'.
		'<fieldset><legend>'.__('Images').'</legend>'.
		'<p class="field"><label class="classic">'.form::combo('display_tb',$value,$this->getEngineConfig('display_tb')).' '.
		__('Display thumbails:').'</label></p>'.
		'</fieldset>'.
		'<fieldset><legend>'.__('Sounds').'</legend>'.
		'<p class="field"><label class="classic">'.form::checkbox('display_mp3',1,$this->getEngineConfig('display_mp3')).' '.
		__('Display mp3 player:').'</label></p>'.
		'</fieldset>'.
		'<fieldset><legend>'.__('Videos').'</legend>'.
		'<p class="field"><label class="classic">'.form::checkbox('display_flv',1,$this->getEngineConfig('display_flv')).' '.
		__('Display flv player:').'</label></p>'.
		$this->core->formNonce().
		'</fieldset>'.
		'<p><input type="submit" name="save" value="'.__('Save').'"/></p>'.
		'</form>';
		
		return $res;
	}
	
	public static function getItemAdminURL($rs)
	{
		return 'media_item.php?id='.$rs->search_id.'&amp;popup=0&amp;post_id=';
	}
	
	public static function getItemPublicURL($rs)
	{	
		return $rs->search_url;
	}
	
	public static function getItemContent($rs)
	{
		return $rs->search_content;
	}
}

?>