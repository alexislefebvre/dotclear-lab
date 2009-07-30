<?php
class microBlog
{
	/**
	 * Instance de la classe
	 * 
	 * SINGLETON
	 * 
	 * @var microBlog
	 */
	private static $instance;
	
	/**
	 * Connecteur à la base de données
	 * 
	 * @var dbLayer
	 */
	private $db;
	
	/**
	 * Prefix des tables de la BDD;
	 * 
	 * @var string
	 */
	private $dbPrefix;
	
	/**
	 * Liste des objets d'accès aux services
	 * 
	 * @var array
	 */
	static private $sAccess = array();
	
	/**
	 * Liste des types de services disponibles
	 * 
	 * @var array
	 */
	static private $sType = array();
	
	//// METHODES PUBLICS ///////////////////////////////////
	
	/**
	 * Accès au gestionnaire de microBlog
	 * 
	 * SINGLETON
	 * 
	 * @return microBlog
	 */
	public static function init()
	{
		if(is_null(self::$instance)){
			global $core;
			self::$instance = new microBlog($core->con, $core->prefix);
		}
		
		return self::$instance;
	}
	
	
	/**
	 * Constructeur de la class
	 * 
	 * @param $db dbLayer Objet d'accès à la BDD
	 * @param $dbPrefix string Prefix des tables de BDD
	 */
	private function __construct(dbLayer $db, $dbPrefix = "")
	{
		$this->db       = $db;
		$this->dbPrefix = $dbPrefix;
		
		if(empty(self::$sType))
		{
			$d = new DirectoryIterator(dirname(__FILE__).'/services/');

			foreach($d as $file)
			{
				if(!$file->isFile()) continue;
	
				$fileName = $file->getFilename();
	
				if(preg_match('#class.mb.[a-z]+.php#',$fileName)){
					$type  = substr($fileName,9,-4);
					$class = 'mb'.ucFirst($type);
					self::$sType[$type] = eval('return '.$class.'::getServiceName();'); 
				}
			}
		}
	}
	
	
	/**
	 * Donne la liste des types de services disponibles
	 * 
	 * @return array
	 */
	public function getServicesType()
	{
		return self::$sType;
	}
	
	
	/**
	 * Ajoute un service de micro-blogging
	 * 
	 * @param $serviceType
	 * @param $user
	 * @param $pwd
	 * @return bool
	 */
	public function addService($serviceType, $user, $pwd){
		$serviceType = $this->db->escape($serviceType);
		$user        = $this->db->escape($user);
		$pwd         = $this->db->escape($pwd);
		
		$query = "INSERT INTO ".$this->dbPrefix."MB_services (id, service, user, pwd) "
		       . "VALUES ('" .md5($serviceType.$user). "','".$serviceType."','".$user."','".$pwd."')";
		
		return $this->db->execute($query);
	}
	
	
	/**
	 * Supprime un service de micro-blogging
	 * 
	 * @param $serviceId string L'identifiant du service
	 * @return bool
	 */
	public function deleteService($serviceId)
	{
		$serviceId = $this->db->escape($serviceId);
		
		$query = "DELETE FROM ".$this->dbPrefix."MB_services WHERE id = '".$serviceId."'";
		
		return $this->db->execute($query);
	}
	
	
	/**
	 * Met à jours les paramètres du service identifié
	 * 
	 * @param $serviceId string L'identifiant du service
	 * @param $param le tableau des paramètres
	 * @return bool
	 */
	public function updateServiceParams($serviceId, $param)
	{
		if(is_array($param))
			$param = $this->paramConverter($param);
		
		if(!is_int($param) || $param > 65535)
			return false;
		
		$serviceId = $this->db->escape($serviceId);
		
		$query = "UPDATE ".$this->dbPrefix."MB_services SET params='" 
		       . $param . "' WHERE id = '" . $serviceId . "'";
		       
		return $this->db->execute($query);
	}
	
	
	/**
	 * Recupère la liste des services disponibles
	 * 
	 * @return record
	 */
	public function getServicesList()
	{
		$query = "SELECT id,service,user FROM ".$this->dbPrefix."MB_services ORDER BY service,user";
		
		return $this->db->select($query);
	}
	
	
	/**
	 * Récupère les paramètres d'un service donné
	 * 
	 * @param $serviceId string L'identifiant du service
	 * @return array
	 */
	public function getServiceParams($serviceId)
	{
		$serviceId = $this->db->escape($serviceId);
		
		$query = "SELECT params FROM ".$this->dbPrefix."MB_services WHERE id = '".$serviceId."'";
		
		$r = $this->db->select($query);
		
		if($r->count() != 1)
			throw new MicroBlogException('Unknown service', 1);
			
		return $this->paramConverter((int)$r->params);
	}
	
	
	/**
	 * Permet d'obtenir un Objet d'accès à un service donné
	 * 
	 * @param $serviceId string L'identifiant du service
	 * @return MicroBlogService
	 */
	public function getServiceAccess($serviceId)
	{
		if(!array_key_exists($serviceId, self::$sAccess))
		{
			$s   = $this->getServiceLogin($serviceId);
			$ser = 'mb'.ucFirst($s->service);
			$t   = new $ser($s->user, $s->pwd);
		
			if(!is_subclass_of($t, 'microBlogService'))
				throw new MicroBlogException('Unknown service', 2);
			
			self::$sAccess[$serviceId] = $t;
		}
		
		return self::$sAccess[$serviceId];
	}
	

	//// METHODES PRIVEES //////////////////////////////////
	
	/**
	 * Récupère les identifiants d'un service donné
	 * 
	 * @param $serviceId string L'identifiant du service
	 * @return staticRecord
	 */
	private function getServiceLogin($serviceId)
	{
		$query = "SELECT service,user,pwd FROM ".$this->dbPrefix."MB_services WHERE id='".$serviceId."'";
		
		$r = $this->db->select($query);
		
		if($r->count() != 1)
			throw new MicroBlogException('Unknown service', 3);
		
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
	 * 1     : 0000000000000001
	 * 2     : 0000000000000010
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
		
		if(is_int($input)){
			$out = array(
				'isActive'              => ($input & 1) == 1,
				'sendNoteOnNewBlogPost' => ($input & 2) == 2
			);
		}
		
		if(is_array($input))
		{
			$out = 0;
			
			if(array_key_exists('isActive', $input)
			&& $input['isActive'] == true)
				$out |= 1;
			
			if(array_key_exists('sendNoteOnNewBlogPost', $input)
			&& $input['sendNoteOnNewBlogPost'] == true) 
				$out |= 2;
		}
		
		return $out;
	}
	
}