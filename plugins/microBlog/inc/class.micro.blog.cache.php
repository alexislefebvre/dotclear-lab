<?php
class microBlogCache
{
	/**
	 * Chemin vers le répertoire de stockage du cache
	 * 
	 * @var string
	 */
	private $cacheDir;
	
	/**
	 * Durée de vie maximum d'un cache en seconde
	 * 
	 * @var int
	 */
	private $cacheMaxLifeTime;
	
	public function __construct($maxLifeTime = 3600)
	{
		$this->cacheDir = DC_TPL_CACHE."/microblog";
		
		if(!is_dir($this->cacheDir))
			mkdir($this->cacheDir, 0777);
	}
	
	/**
	 * Permet de redéfinir la durée de vie maximum des caches
	 * 
	 * @param $lifetime int La nouvelle durée de vie en seconde
	 */
	public function setMaxLifeTime($lifetime)
	{
		$this->cacheMaxLifeTime = (int)$lifetime;
	}
	
	/**
	 * Crée un cache contenant $val
	 * 
	 * @param $key string
	 * @param $val mixed
	 * @return bool
	 */
	public function set($key, $val)
	{
		$path = $this->cacheDir."/".md5($key);
		$val = serialize($val);
		
		$size = file_put_contents($path, $val);
		chmod($path, 0777);
		clearstatcache();
		
		return $size > 0;
	}
	
	/**
	 * Retourne le contenu d'un cache s'il n'a pas expiré
	 * 
	 * @param $key
	 * @return mixed
	 */
	public function get($key)
	{
		$path = $this->cacheDir."/".md5($key);
		
		if(!file_exists($path)) return NULL;
		
		$lt = $this->lifetime($key);
		if($lt > $this->cacheMaxLifeTime){
			$this->delete($key);
			return NULL;
		}
		
		$val = file_get_contents($path);
		
		return unserialize($val);
	}
	
	/**
	 * Supprime un cache
	 * 
	 * @param $key
	 * @return bool
	 */
	public function delete($key)
	{
		$path = $this->cacheDir."/".md5($key);
		
		if(!file_exists($path)) return true;
		
		$out = unlink($path);
		clearstatcache(true, $path);
		
		return $out;
	}
	
	/**
	 * Donne la durée de vie d'un cache en seconde
	 * 
	 * @param $key
	 * @return int
	 */
	public function lifetime($key)
	{
		$path = $this->cacheDir."/".md5($key);
		
		if(!file_exists($path)) return 0;
		
		$out = time() - filemtime($path);
	}
}