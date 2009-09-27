<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Micro-Blogging, a plugin for Dotclear.
# 
# Copyright (c) 2009 Jeremie Patonnier
# jeremie.patonnier@gmail.com
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

/**
 * Main class for the Micro-Blogging plugin
 * 
 * @author jeremie
 * @package microBlog
 */
class microBlog
{
	/**
	 * Class unique instance
	 * 
	 * SINGLETON
	 * 
	 * @var microBlog
	 */
	private static $instance;
	
	/**
	 * DB connector
	 * 
	 * @var dbLayer
	 */
	private $db;
	
	/**
	 * DB prefix;
	 * 
	 * @var string
	 */
	private $db_prefix;
	
	/**
	 * Stack of the services access instances
	 * 
	 * @var array
	 */
	static private $sAccess = array();
	
	/**
	 * Stack of available services
	 * 
	 * @var array
	 */
	static private $sType = array();
	
	/**
	 * Default note use to send automatic note
	 * 
	 * It's possible to use the following constants:
	 * %url% : URL of the blog post
	 * %title% : title of the blog post
	 * %blog% : name of the blog
	 * 
	 * @var string
	 */
	static private $dNote = 'New blog post: %url%';
	
	
	//// METHODES PUBLICS ///////////////////////////////////
	
	/**
	 * Access to the microBlog object
	 * 
	 * SINGLETON
	 * 
	 * @return microBlog
	 */
	public static function init(dcCore $core)
	{
		if (is_null(self::$instance)){
			self::$instance = new microBlog($core->con, $core->prefix);
		}
		
		return self::$instance;
	}
	
	
	/**
	 * Class constructor
	 * 
	 * @param $db dbLayer DB Access object
	 * @param $dbPrefix string DB prefix
	 */
	private function __construct(dbLayer $db, $db_prefix = "")
	{
		$this->db        = $db;
		$this->db_prefix = $db_prefix;
		
		if (empty(self::$sType))
		{
			$d = new DirectoryIterator(dirname(__FILE__).'/services/');

			foreach ($d as $file)
			{
				if (!$file->isFile()) continue;
	
				$fileName = $file->getFilename();
	
				if (preg_match('#class.mb.[a-z]+.php#',$fileName)){
					$type  = substr($fileName,9,-4);
					$class = 'mb'.ucFirst($type);
					self::$sType[$type] = eval('return '.$class.'::getServiceName();'); 
				}
			}
		}
	}
	
	
	/**
	 * Give the list of the available services types
	 * 
	 * @return array
	 */
	public function getServicesType()
	{
		return self::$sType;
	}
	
	
	/**
	 * Add a micro-blogging service
	 * 
	 * TODO ADD THE USED OF API_KEY
	 * 
	 * @param $serviceType string the global service ID
	 * @param $user string
	 * @param $pwd string
	 * @return bool
	 */
	public function addService($serviceType, $user, $pwd)
	{
		$serviceType = $this->db->escape($serviceType);
		$user        = $this->db->escape($user);
		$pwd         = $this->db->escape($pwd);
		
		$query = "INSERT INTO ".$this->db_prefix."MB_services (id, service, user, pwd) "
		       . "VALUES ('" .md5($serviceType.$user). "','".$serviceType."','".$user."','".$pwd."')";
		
		return $this->db->execute($query);
	}
	
	
	/**
	 * Delete a micro-blogging service
	 * 
	 * @param $serviceId string The service unique ID
	 * @return bool
	 */
	public function deleteService($serviceId)
	{
		$serviceId = $this->db->escape($serviceId);
		
		$query = "DELETE FROM ".$this->db_prefix."MB_services WHERE id = '".$serviceId."'";
		
		return $this->db->execute($query);
	}
	
	
	/**
	 * Update a service parameters
	 * 
	 * The current list of allowed parameters are :
	 * 
	 * ARRAY(
	 * 	isActive => bool
	 *   sendNoteOnNewBlogPost => bool
	 * )
	 * 
	 * @param $serviceId string The service unique ID
	 * @param $param array The parameters list
	 * @return bool
	 */
	public function updateServiceParams($serviceId, $param)
	{
		if (is_array($param))
			$param = $this->paramConverter($param);
		
		if (!is_int($param) || $param > 65535)
			return false;
		
		$serviceId = $this->db->escape($serviceId);
		
		$query = "UPDATE ".$this->db_prefix."MB_services SET params='" 
		       . $param . "' WHERE id = '" . $serviceId . "'";
		       
		return $this->db->execute($query);
	}
	
	
	/**
	 * Give the list of all the available services
	 * 
	 * @return record
	 */
	public function getServicesList()
	{
		$query = "SELECT id,service,user FROM ".$this->db_prefix."MB_services ORDER BY service,user";
		
		return $this->db->select($query);
	}
	
	
	/**
	 * Return the parameters of a given service
	 * 
	 * @param $serviceId string The service unique ID
	 * @return array
	 */
	public function getServiceParams($serviceId)
	{
		$serviceId = $this->db->escape($serviceId);
		
		$query = "SELECT params FROM ".$this->db_prefix."MB_services WHERE id = '".$serviceId."'";
		
		$r = $this->db->select($query);
		
		if ($r->count() == 0)
			throw new microBlogException('Unknown service', __LINE__);
		if ($r->count() > 1)
			throw new microBlogException('Corupt services list', __LINE__);
			
		return $this->paramConverter((int)$r->params);
	}
	
	
	/**
	 * Allow to get an Object to access to a given service
	 * 
	 * @param $serviceId string L'identifiant du service
	 * @return microBlogService
	 */
	public function getServiceAccess($serviceId)
	{
		if (!array_key_exists($serviceId, self::$sAccess))
		{
			$s   = $this->getServiceLogin($serviceId);
			$ser = 'mb'.ucFirst($s->service);
			$t   = new $ser($s->user, $s->pwd);
		
			if (!is_subclass_of($t, 'microBlogService'))
				throw new microBlogException('Unknown service', __LINE__);
			
			self::$sAccess[$serviceId] = $t;
		}
		
		return self::$sAccess[$serviceId];
	}
	
	public function setDefaultNote($note)
	{
		self::$dNote = (string)$note;
	}
	
	public function getDefaultNote()
	{
		return self::$dNote;	
	}
	

	//// METHODES PRIVEES //////////////////////////////////
	
	/**
	 * Get loggin informations to a given service
	 * 
	 * @param $serviceId string The service unique ID
	 * @return staticRecord
	 */
	private function getServiceLogin($serviceId)
	{
		$query = "SELECT service,user,pwd FROM ".$this->db_prefix."MB_services WHERE id='".$serviceId."'";
		
		$r = $this->db->select($query);
		
		if ($r->count() == 0)
			throw new microBlogException('Unknown service', __LINE__);
		if ($r->count() > 1)
			throw new microBlogException('Corupt services list', __LINE__);
		
		return $r->toStatic();
	}
	
	
	/**
	 * Permet de convertir un tableau de param boolean en entier et vice-versa
	 * 
	 * Le tableau en entrée ou en sortie contient les clés suivante :
	 * 
	 * ARRAY(
	 *     isActive : Indique si le Service peut être utilisé ou non
	 *     sendNoteOnNewBlogPost : Indique si une Note doit être envoyer au service 
	 *                             à la publication d'un nouveau billet de blog
	 * )
	 * 
	 * Pour mémoire en valeur binaire :
	 * 
	 * 1     : 0000000000000001 -> isActive
	 * 2     : 0000000000000010 -> sendNoteOnNewBlogPost
	 * 4     : 0000000000000100
	 * 8     : 0000000000001000
	 * 16    : 0000000000010000
	 * 32    : 0000000000100000
	 * 64    : 0000000001000000
	 * 128   : 0000000010000000
	 * 256   : 0000000100000000
	 * 512   : 0000001000000000
	 * 1024  : 0000010000000000
	 * 2048  : 0000100000000000
	 * 4096  : 0001000000000000
	 * 8192  : 0010000000000000
	 * 16384 : 0100000000000000
	 * 32768 : 1000000000000000
	 * 
	 * @param $input mixed INT ou ARRAY
	 * @return mixed
	 */
	private function paramConverter($input)
	{
		$out = NULL;
		
		if (is_int($input)){
			$out = array(
				'isActive'              => ($input & 1) == 1,
				'sendNoteOnNewBlogPost' => ($input & 2) == 2
			);
		}
		
		if (is_array($input))
		{
			$out = 0;
			
			if (array_key_exists('isActive', $input)
			&& $input['isActive'] == true)
				$out |= 1;
			
			if (array_key_exists('sendNoteOnNewBlogPost', $input)
			&& $input['sendNoteOnNewBlogPost'] == true) 
				$out |= 2;
		}
		
		return $out;
	}
	
}