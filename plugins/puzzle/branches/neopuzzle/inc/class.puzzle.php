<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Puzzle, a plugin for Dotclear.
# 
# Copyright (c) 2009 kévin lepeltier
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

class puzzle {
	
	protected $core;
	protected $tab;
	protected $url;
	protected $settings;
	protected $msg;
	protected $data;
	
	protected $allTab = array('active'=>'Active', 'make'=>'Make');
	
	public function __construct( $core, $tab = null, $p_url = null ) {
		
		$this->core =& $core;
		$this->tab = $tab;
		$this->url = $p_url;
		$this->data = array();
		
		$this->settings =& $core->blog->settings;
		$this->settings->setNameSpace('puzzle');
		//$this->settings->drop('puzzle_heightgutter');
		
		$this->data['puzzle_format'] = array();
		$cats = explode( '|', $this->settings->puzzle_format );
		foreach ($cats as $values) {
			$values = explode( ';', $values );
			$this->data['puzzle_format'][array_shift($values)] = $values;
		}
		
		$this->data['puzzle_parts'] = array();
		$cats = explode( '}', $this->settings->puzzle_parts );
		foreach ($cats as $values) {
			$values = explode( '|', $values );
			$cat = array_shift($values);
			$this->data['puzzle_parts'][$cat] = array();
			foreach ($values as $part) {
				$part = explode( ';', $part );
				$this->data['puzzle_parts'][$cat][] = $part;
			}
		}
		
		switch ($tab) {
			case 'active' 	: $params = $this->tabActive(); break;
			case 'make' 	: $params = $this->tabMake(); break;
		}
		
		if( isset($tab) )
			$this->prepareHtmlFor( $this->tab, $params );
		
	}
	
	public function tabActive() {
		
		if (!empty($_POST['saveconfig']))
			$this->tabActiveSave();
		
		$res = ''.
		'<p><input type="submit" name="saveconfig" value="'.__('Save configuration').'" /></p>'.
		
		'<table>'.
		'  <CAPTION>'.__('Enable Puzzle for:').'</CAPTION>'.
		'  <tbody>'.
		
		'    <tr>'.
		'      <th>'.form::checkbox(array('enable[]'),'home', in_array('home', $this->puzzle_active)).'</th>'.
		'      <th><label for="enable[]">'.__('Home').'</label></th>'.
		'    </tr>';
		
		$categories = array();
		$rs = $this->core->blog->getCategories();
		while ($rs->fetch())
			$res .= ''.
			'    <tr>'.
			'      <td>'.form::checkbox(array('enable[]'),$rs->cat_id, in_array($rs->cat_id, $this->puzzle_active)).'</td>'.
			'      <td><label for="enable[]">'.$rs->cat_title.'</label></td>'.
			'    </tr>';
		unset($rs);
		
		$res .= ''.
		'  </tbody>'.
		'</table>';
		
		$params['content'] = $res;
		
		return $params;
		
	}
	
	public function tabActiveSave() {
		
		foreach ($_POST['enable'] as $id=>$cat)
			$ar[] = $cat;
		
		$this->puzzle_active = $ar;
		
		$this->core->emptyTemplatesCache();
		
	}
	
	public function tabMake() {
		
		if (!empty($_REQUEST['cat']) ) {
			
			$cat_title = __('Home');
			$cat_id = 'home';
			if( $_REQUEST['cat'] != 'home' ) {
				$rs = $this->core->blog->getCategory( $_REQUEST['cat'] );
				$cat_title = html::escapeHTML($rs->cat_title);
				$cat_id = $rs->cat_id;
			}
			
			if (!empty($_POST['saveconfig']))
				$this->tabMakeSave($cat_id);
			
			$puzzle_format_cat = $this->{'puzzle_format_'.$cat_id};
		
			$res = ''.
			'<p><a href="'.$this->url.'&amp;tab='.$this->tab.'">'.__('Change category').'</a></p>'.
			'<div class="two-cols clear">'.
			'  <div class="col">'.
			
			'<fieldset>'.
			'  <legend>'.__('Define the Puzzle for').' '.__($cat_title).'</legend>'.
			'      <div class="two-cols clear">'.
			'        <div class="col">'.
			'          <fieldset>'.
			'            <legend>'.__('Horizontal').'</legend>'.
			'              <p><label>'.__('Number column max').form::field('puzzle_nbcol',4,2, $puzzle_format_cat[0]).'</p>'.
			'              <p><label>'.__('Size of gutters in %').form::field('puzzle_widthgutter',4,4, $puzzle_format_cat[1]).'</p>'.
			'          </fieldset>'.
			'        </div>'.
			'        <div class="col">'.
			'          <fieldset>'.
			'            <legend>'.__('Vertical').'</legend>'.
			'              <p><label>'.__('Lines height in px').form::field('puzzle_heightline',4,3, $puzzle_format_cat[2]).'</p>'.
			'              <p><label>'.__('Size of gutters in px').form::field('puzzle_heightgutter',4,2, $puzzle_format_cat[3]).'</p>'.
			'          </fieldset>'.
			'        </div>'.
			'      </div>'.
			'</fieldset>'.
			
			'<p><input type="submit" name="saveconfig" value="'.__('Save configuration').'" /></p>'.
			
			'<fieldset>'.
			'  <legend>'.__('Make the Puzzle for').' '.__($cat_title).'</legend>'.
			'      <table class="dragable">'.
			'        <thead>'.
			'          <tr>'.
			'            <th>'.__('ID').'</th>'.
			'            <th>'.__('Column').'</th>'.
			'            <th>'.__('Line').'</th>'.
			'            <th>'.__('Post').'</th>'.
			'            <th>'.__('Float right').'</th>'.
			'          </tr>'.
			'        </thead>'.
			'        <tbody id="puzzle-list">';
			
			$puzzle_parts_cat = $this->{'puzzle_parts_'.$cat_id};
			
			$posts = array();
			if( $cat_id != 'home' )
				$rs = $this->core->blog->getPosts(array('cat_id'=>$cat_id));
			else	$rs = $this->core->blog->getPosts();
			$posts['Supprimer'] = 0;
			while ($rs->fetch()) $posts[html::escapeHTML($rs->post_title)] = $rs->post_id;
			unset($rs);
			
			foreach ($puzzle_parts_cat as $id=>$part) {
				$res .= ''.
				'    <tr class="line" id="l_'.$id.'">'.
				'      <td class="handle minimal">'.form::field(array('order['.$id.']'),2,2,$id).'</td>'.
				'      <td class="minimal">'.form::field(array('col[]'),2,2,$part[0]).'</td>'.
				'      <td class="minimal">'.form::field(array('lig[]'),2,2,$part[1]).'</td>'.
				'      <td class="nowrap">'.form::combo('post[]',$posts,$part[2],'',null,false,'style="width:100%;"').'</td>'.
				'      <td class="nowrap">'.form::checkbox(array('right[]'),$part[2],$part[3]).'</td>'.
				'    </tr>';
			}
			
			$res .= ''.
			'          <tr class="line" id="l_'.++$id.'">'.
			'            <td class="handle minimal">'.form::field(array('order['.$id.']'),2,2,$id).'</td>'.
			'            <td class="minimal">'.form::field(array('col[]'),2,2).'</td>'.
			'            <td class="minimal">'.form::field(array('lig[]'),2,2).'</td>'.
			'            <td class="nowrap">'.form::combo('post[]',$posts,0,'',null,false,'style="width:100%;"').'</td>'.
			'            <td class="nowrap">'.__('Fill to create a new part').'</td>'.
			'          </tr>'.
				'        </tbody>'.
			'      </table>'.
			'</fieldset>'.
			
			'    </div>'.
			'    <div class="col" style="background: #eee;position:relative;">';
			
			foreach ($puzzle_parts_cat as $id=>$part) {
				foreach ($posts as $titl=>$post) if($post == $part[2])
					$title = $titl;
				$resb .= ''.
				'<div style="width:'.($part[0]*100/$puzzle_format_cat[0]-$puzzle_format_cat[1]-2).'%;'.
				'      height:'.(($part[1]*$puzzle_format_cat[2]+$puzzle_format_cat[3]*($part[1]-1))/2-10).'px;'.
				'      outline:1px solid #000;'.
				'      margin: 0 '.$puzzle_format_cat[1].'% '.($puzzle_format_cat[3]/2).'px 0;'.
				'      padding: 5px 1%;'.
				'      float:'.($part[3]? 'right':'left').';">'.
				$title.
				'</div>';
				
				$nb += $part[0]*$part[1];
				if($part[1] > $nbligne) $nbligne = $part[1];
			}
			
			if( empty($nb) || $nb == 0 ) $nb = $puzzle_format_cat[0]*5;
			if(($puzzle_format_cat[0]*$nbligne) > $nb) $nb = $puzzle_format_cat[0]*$nbligne;
			
			for ($i=0; $i < $nb; $i++) {
				$res .= ''.
				'<div style="width:'.(100/$puzzle_format_cat[0]-$puzzle_format_cat[1]).'%;'.
				'            height:'.($puzzle_format_cat[2]/2).'px;'.
				'            margin: 0 '.$puzzle_format_cat[1].'% '.($puzzle_format_cat[3]/2).'px 0;'.
				'            outline:1px solid #e8e8e8;'.
				'            background:#e8e8e8;'.
				'  float:left;">'.
				'</div>';
			}
			
			$res .= '<div style="position:absolute;top:0;left:0;width:100%;">'.$resb.'</div>'.
			
			'    </div>'.
			'  </div>'.
			
			'<p style="clear: both;">'.form::hidden('puzzle_order','').'</p>';
			
			$params['header'] = dcPage::jsToolMan().dcPage::jsLoad('index.php?pf=puzzle/js/_lists.js');
			$params['query'][] = 'cat='.$cat_id;
			$params['querybis'][] = 'cat='.$cat_id;
			$params['content'] = $res;
			
		} else
			$params = $this->settingsFor();
		
		return $params;
	}
	
	public function tabMakeSave( $cat_id ) {
		
		$this->{'puzzle_format_'.$cat_id} = array($_POST['puzzle_nbcol'], $_POST['puzzle_widthgutter'], 
							  $_POST['puzzle_heightline'], $_POST['puzzle_heightgutter']);
		
		foreach ($_POST['col'] as $id=>$part)
			if( $_POST['post'][$id] != '' && $_POST['post'][$id] != 0 )
				$ar[] = array($_POST['col'][$id], $_POST['lig'][$id], $_POST['post'][$id], in_array($_POST['post'][$id], $_POST['right']));
		
		$this->{'puzzle_parts_'.$cat_id} = $ar;
		
	}
	
	public function prepareHtmlFor( $tab, $params ) {
		
		$res = ''.
		'<html>'.
		
		'  <head>'.
		'    <title>'.__('Puzzle').'</title>'.
		'    '.dcPage::jsPageTabs( $tab ).
		'    '.$params['header'].
		'  </head>'.
		
		'  <body>'.
		'    <h2>'.html::escapeHTML($this->core->blog->name).' &gt; '.__('Puzzle').'</h2>';
		
		if (!empty($this->msg))
			$res .= '<p class="message">'.$this->msg.'</p>';
		
		foreach ($this->allTab as $idtab=>$ntab) if( $idtab == $tab ) {
			
			$params['query'][] = 'tab='.$this->tab;
			$submit = ( empty($params['submit']) )? 'Save configuration':$params['submit'];
			$submitname = ( empty($params['submitname']) )? 'saveconfig':$params['submitname'];
			
			$res .= ''.
			'    <div class="multi-part" id="'.$tab.'" title="'.__($ntab).'">'.
			'      <form method="post" action="'.$this->url.'&amp;'.implode('&amp;', $params['query']).'">'.
			'        '.$this->core->formNonce().
			
			'        '.$params['content'].
			
			'        <p><input type="submit" name="'.$submitname.'" value="'.__($submit).'" /></p>'.
			
			'      </form>'.
			'    </div>';
		
		} else {
			
			$params['querybis'][] = 'tab='.$idtab;
			$res .= ''.
			'<a href="'.$this->url.'&amp;'.implode('&amp;', $params['querybis']).'" class="multi-part">'.__($ntab).'</a>';
		}
		
		$res .= ''.
		'  </body>'.
		'</html>';
		
		echo $res;
	}
	
	public function settingsFor() {
	
		$categories = array();
		$rs = $this->core->blog->getCategories();
		if(in_array('home', $this->puzzle_active))
			$categories['Home'] = 'home';
		while ($rs->fetch()) if(in_array($rs->cat_id, $this->puzzle_active))
			$categories[html::escapeHTML($rs->cat_title)] = $rs->cat_id;
		unset($rs);
		
		$res = ''.
		'<p><label>'.__('Settings for category').form::combo('cat',$categories,0).'</label></p>';
		
		$params['submit'] = 'Select this category';
		$params['submitname'] = 'select';
		$params['content'] = $res;
		
		return $params;
		
	}
	
	public function __set($key,$value) {
		
		switch ( $key ) {
			//case 'msg' : $this->data[$key] .= $value.'<br/>'; break;
			case 'puzzle_active' :
				$this->data['puzzle_active'] = $value;
				$this->settings->put('puzzle_active', implode('|', $this->data['puzzle_active']), 'string','Enable Puzzle');
			break;
		}
		
		if ( strpos($key, 'puzzle_format_') !== false ) {
			$key = implode('', explode('puzzle_format_', $key));
			$this->data['puzzle_format'][$key] = $value;
			
			foreach ($this->data['puzzle_format'] as $cat_id=>$key)
				$ar[] = $cat_id.';'.implode(';', $key);
			$this->settings->put('puzzle_format', implode('|', $ar), 'string','Grid Puzzle');
		}
		
		if ( strpos($key, 'puzzle_parts_') !== false ) {
			$key = implode('', explode('puzzle_parts_', $key));
			$this->data['puzzle_parts'][$key] = $value;
			
			foreach ($this->data['puzzle_parts'] as $cat_id=>$parts) {
				$ar = array();
				foreach ($parts as $part) $ar[] = implode(';', $part);
				$ar2[] = $cat_id.'|'.implode('|', $ar);
			}
			$this->settings->put('puzzle_parts', implode('}', $ar2), 'string','Parts of Puzzle');
		}
	}
	 
	public function __get($key) {
	
		switch ( $key ) {
			//case 'msg' : return $this->data[$key]; break;
			case 'puzzle_active' :
				if( empty($this->data['puzzle_active']) )
					$this->data['puzzle_active'] = explode( '|', $this->settings->puzzle_active );
				return $this->data['puzzle_active'];
			break;
		}
		
		if ( strpos($key, 'puzzle_format_') !== false ) {
			$key = implode('', explode('puzzle_format_', $key));
			return $this->data['puzzle_format'][$key];
		}
		
		if ( strpos($key, 'puzzle_parts_') !== false ) {
			$key = implode('', explode('puzzle_parts_', $key));
			return $this->data['puzzle_parts'][$key];
		}
		
		return;
	}
	
}