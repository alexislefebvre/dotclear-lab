<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of lePluginDuJour, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 lipki and contributors
# kevin@lepeltier.info
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

class dclePluginDuJour {
	
	public static function lePluginDuJourDashboard($core,$icons) {

		if ($core->auth->isSuperAdmin()) {

			$day = $core->blog->settings->leplugindujour->day;
			$plugin = $core->blog->settings->leplugindujour->plugin;
			$lePluginDuJour = new dcLePluginDuJour($core);
			$lePluginDuJour->check();

			$avail_plugins = $lePluginDuJour->getModules('plugins');
			if( $day != date("j, n, Y") ) {
				$day = date("j, n, Y");
				$plugin = array_rand($avail_plugins);
			}
			$avail_plugin = $avail_plugins[$plugin];
			
			$txt_plugin = 
				'<div class="message" style="background:url(http://media.dotaddict.org/pda/dc2/'.html::escapeHTML($avail_plugin['id']).'/icon.png) 8px 6px no-repeat;">'.
				'<h3 style="color:#cccccc;">'.html::escapeHTML($avail_plugin['label']).'</h3>'.
				'<p><em>'.html::escapeHTML($avail_plugin['desc']).'</em></p>'.
				'<p>'.__('by').' '.html::escapeHTML($avail_plugin['author']).'<br />'.
				'( <a href="'.$avail_plugin['details'].'" class="learnmore modal">'.__('More details').'</a> )</p></div>';
			

			$doc_links = $icons->offsetGet(0);
			$news = $icons->offsetGet(1);
			$icons->offsetSet(0, array($txt_plugin));
			$icons->offsetSet(1, $doc_links);
			$icons->offsetSet(2, $news);
			
			$core->blog->settings->leplugindujour->put('day', $day);
			$core->blog->settings->leplugindujour->put('plugin', $plugin);
		}
	}
	
	public static function adminEnabledPlugin($core, $settings) {
		echo '<p><label class="classic">'.
		form::checkbox('leplugindujour_enabled','1',$settings->leplugindujour->enabled).
		__('Enable Le Plugin Du Jour').'</label></p>'.
		'<p class="form-note">'.$core->plugins->moduleInfo('lePluginDuJour','desc').'</p>';
	}
	
	public static function adminBeforeBlogSettingsUpdate($settings) {
		$settings->addNameSpace('leplugindujour');
		$settings->leplugindujour->put('enabled',!empty($_POST['leplugindujour_enabled']),'boolean');
	}

	public static function initWidgets($widgets) {
		$widgets->create('lePluginDuJour',__('Le Plugin Du Jour'), array('dcLePluginDuJour','widget'));

		$widgets->lePluginDuJour->setting('title',__('Title:'), 'Le Plugin Du Jour','text');
		$widgets->lePluginDuJour->setting('icon',__('icon'), true,'check');

	}
	
	public static function widget($widget) {
	
		global $core;
		
		$plugin = $core->blog->settings->leplugindujour->plugin;
		$lePluginDuJour = new dcLePluginDuJour($core);
		$lePluginDuJour->check();

		$avail_plugins = $lePluginDuJour->getModules('plugins');
		$avail_plugin = $avail_plugins[$plugin];

		$res = 
			'<div class="lePluginDuJour">'.
			'<h2>'.$widget->title.'</h2>'.
			'<h3 style="background:url(http://media.dotaddict.org/pda/dc2/'.html::escapeHTML($avail_plugin['id']).'/icon.png) 8px 6px no-repeat;padding-left: 27px;">'.html::escapeHTML($avail_plugin['label']).'</h3>'.
			'<p><em>'.html::escapeHTML($avail_plugin['desc']).'</em></p>'.
			'<p>'.__('by').' '.html::escapeHTML($avail_plugin['author']).'<br />'.
			'( <a href="'.$avail_plugin['details'].'" class="learnmore modal">'.__('More details').'</a> )</p></div>';

		return $res;
	}
	
	
	protected $core;
	
	# Set via plugin's settings
	protected $themes_xml;
	protected $plugins_xml;

	# Raw datas
	protected $modules;
	
	/**
	 * "Look, Ma ! That's what we call a constructor."
	 * Nothing more to say about it for now. :-)
	 *
	 */
	public function __construct($core)
	{
		$this->core = $core;
		
		# Settings compatibility test
		$s = $core->blog->settings->leplugindujour;
		$this->themes_xml 	=  $s->themes_xml;
		$this->plugins_xml 	=  $s->plugins_xml;
		$this->modules = array(
			'plugins'	=> array(
				'new' 	=> array(),
				'update' 	=> array()
			),
			'themes'	=> array(
				'new' 	=> array(),
				'update' 	=> array()
			)
		);
	}
	
	/**
	 * Check new/updated plugins availability
	 * Plugins already installed but marked as disabled will be ignored.
	 * Results of this method are stored as two subarrays 'new' and 'update' in $modules['plugins'].
	 *
	 * @param		boolean	$force	Forces datas refresh if true (default to false)
	 * 	 	 
	 * @return	boolean	Reports operatio status :
	 *					- true if operation was successful,
	 *					- false otherwise, you may check internal error trace.
	 *
	 */
	protected function checkPlugins($force = false)
	{
		if (!$this->plugins_xml) {
			return false;
		}
		try {
			if (($parser = daModulesReader::quickParse($this->plugins_xml,DC_TPL_CACHE,$force)) === false) {
				return false;
			}
			
			$raw_datas = $parser->getModules();
			
			uasort($raw_datas,array('self','sort'));
			
			# On se débarasse des plugins désactivés.
			$skipped = array_keys($this->core->plugins->getDisabledModules());
			foreach ($skipped as $p_id) {
				if (isset($raw_datas[$p_id])) {
					unset($raw_datas[$p_id]);
				}
			}
			
			# On vérifie les mises à jour
			$updates = array();
			$current = $this->core->plugins->getModules();
			foreach ($current as $p_id => $p_infos) {
				if (isset($raw_datas[$p_id])) {
					if (self::da_version_compare($raw_datas[$p_id]['version'],$p_infos['version'],'>')) {
						$updates[$p_id] = $raw_datas[$p_id];
						$updates[$p_id]['root'] = $p_infos['root'];
						$updates[$p_id]['root_writable'] = $p_infos['root_writable'];
						$updates[$p_id]['current_version'] = $p_infos['version'];
					}
					unset($raw_datas[$p_id]);
				}
			}
			
			$this->modules['plugins'] = array(
				'new'	=> $raw_datas,
				'update'	=> $updates
			);

			return true;
		}
		catch (Exception $e) {
			# Probablement à compléter.
			return false;
		}
	}
	
	/**
	 * Check new/updated themes availability
	 * Results of this method are stored as two subarrays 'new' and 'update' in $modules['themes'].
	 *
	 * @param		boolean	$force	Forces datas refresh if true (default to false)
	 * 	 	 
	 * @return	boolean	Reports operatio status :
	 *					- true if operation was successful,
	 *					- false otherwise, you may check internal error trace.
	 *
	 */
	protected function checkThemes($force = false)
	{
		if (!$this->themes_xml) {
			return false;
		}
		try {
			if (($parser = daModulesReader::quickParse($this->themes_xml,DC_TPL_CACHE,$force)) === false) {
				return false;
			}
			
			$raw_datas = $parser->getModules();
			
			uasort($raw_datas,array('self','sort'));
			
			# On vérifie les mises à jour
			$updates = array();
			$core_themes = new dcModules($this->core);
			$core_themes->loadModules($this->core->blog->themes_path,null);
			$current = $core_themes->getModules();
			foreach ($current as $p_id => $p_infos) {
				if (isset($raw_datas[$p_id])) {
					if (self::da_version_compare($raw_datas[$p_id]['version'],$p_infos['version'],'>')) {
						$updates[$p_id] = $raw_datas[$p_id];
						$updates[$p_id]['root'] = $p_infos['root'];
						$updates[$p_id]['root_writable'] = $p_infos['root_writable'];
						$updates[$p_id]['current_version'] = $p_infos['version'];
					}
					unset($raw_datas[$p_id]);
				}
			}
			
			$this->modules['themes'] = array(
				'new'	=> $raw_datas,
				'update'	=> $updates
			);
			
			return true;
		}
		catch (Exception $e) {
			# Probablement à compléter.
			return false;
		}
	}
	
	/**
	 * Get informations about available new//updated themes/plugins.
	 * Probably the first method to invoke after instanciation.
	 * 
	 * @param		boolean	$force	Forces datas refresh if true (default to false)
	 *
	 */
	public function check($force = false)
	{
		if (!$this->checkPlugins($force)) {
			return false;
		}
		if (!$this->checkThemes($force)) {
			return false;
		}
		return true;
	}
	
	/**
	 * Retrieve a specific module list
	 *
	 * @param		string	$type	The type of modules wanted ('plugins' or 'themes')
	 * @param		boolean	$update	Flag to choose between new or updated modules (default to false - new modules list)
	 *
	 * @return	mixed 	The matching modules entries as an array, if any. Or a boolean set to false.
	 *
	 */
	public function getModules($type, $update = false)
	{
		$type = ($type == 'themes') ? 'themes' : 'plugins';
		$what = ($update) ? 'update' : 'new';
		if (isset($this->modules[$type][$what])) {
			return $this->modules[$type][$what];
		}
		return false;
	}
	
	/**
	 * Search a string in module id, label and description.
	 * Search is case-insensitive and can be apply to available themes or plugins
	 *
	 * @param		string	$search	The search string
	 * @param		string	$type	The type of targeted modules ('plugins' or 'themes')
	 *
	 * @return 	array 	An array of matching modules entries
	 *
	 */
	public function search($search,$type = 'plugins')
	{
		$type = ($type == 'themes') ? 'themes' : 'plugins';
		$result = array();
		
		foreach ($this->modules[$type]['new'] as $module)
		{
			if ( preg_match('/'.$search.'/i',$module['id']) ||
				preg_match('/'.$search.'/i',$module['label']) ||
				preg_match('/'.$search.'/i',$module['desc']))
			{
				$result[] = $module;
			}
		}
		return $result;
	}
	
	/**
	 * Helper method to fetch and install a ZIP package.
	 *
	 * @param		string	$url			Source file URL
	 * @param		string	$dest		Target file destination
	 * @param		dcModules	$coreModules	Target modules stack
	 *
	 * @return	integer	Basic operation status code : 
	 *					- 1 : everything's all right,
	 *					- 2 : tempfile couldn't be deleted.
	 *
	 */
	public function processPackage($url,$dest,dcModules $coreModules)
	{
		try {
			$client = netHttp::initClient($url,$path);
			$client->setUserAgent(self::getUserAgent());
			$client->useGzip(false);
			$client->setPersistReferers(false);
			$client->setOutput($dest);
			$client->get($path);
		}
		catch (Exception $e) {
			throw new Exception(__('An error occurred while downloading the file.'));
		}
		
		unset($client);
		$ret_code = dcModules::installPackage($dest,$coreModules);
		
		return $ret_code;
	}
	
	/**
	 * Helper method to get user agent according to DC and lePluginDuJour version
	 *
	 * @return	string	lePluginDuJour user agent
	 *
	 */
	public static function getUserAgent()
	{
		$m_version = $GLOBALS['core']->plugins->moduleInfo('lePluginDuJour','version');
		return sprintf('lePluginDuJour/%s (Dotclear/%s)',$m_version,DC_VERSION);
	}
	
	/**
	 * Helper method to compare correctly version.
	 *
	 * @param		string	$v1			Version of first module
	 * @param		string	$v2			Version of second module
	 * @param		string	$op			Operator
	 *
	 * @return	boolean	True if test of version is correct according to operator
	 *
	 */
	private static function da_version_compare($v1,$v2,$op)
	{
		$v1 = preg_replace('!-r(\d+)$!','-p$1',$v1);
		$v2 = preg_replace('!-r(\d+)$!','-p$1',$v2);
		return version_compare($v1,$v2,$op);
	}
	
	/**
	 * Helper method to sort module list.
	 *
	 * @param		array	$a			First module
	 * @param		array	$b			Second module
	 *
	 * @return	integer
	 *
	 */
	private static function sort($a,$b)
	{
		$c = strtolower($a['id']); 
		$d = strtolower($b['id']); 
		if ($c == $d) { 
			return 0; 
		} 
		return ($c < $d) ? -1 : 1; 
	}
}

?>
