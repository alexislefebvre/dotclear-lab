<?php
interface iMicroBlogService{
	
	/**
	 * Donne le nom générique du service
	 * 
	 * @return string
	 */
	public static function getServiceName();
	
	/**
	 * Indique si le service requiert une API_KEY en plus du couple login/pwd
	 * 
	 * @return bool
	 */
	public static function requireKey();
	
	/**
	 * Donne l'identifiant unique de l'accès au service
	 * 
	 * @return string
	 */
	public function getServiceId();
}


/**
 * Classe abstraite d'accès à un service de micro-blogging
 */
abstract class microBlogService implements iMicroBlogService
{
	/**
	 * Le nom d'utilisateur du service de micro-blogging
	 * @var string
	 */
	protected $user;

	/**
	 * Mot de passe de l'utiliateur du service de micro-blogging
	 * @var string
	 */
	protected $pwd;
	
	/**
	 * L'identifiant unique de l'accès au service
	 * @var string
	 */
	protected $serviceId;

	/**
	 * Constructeur de la classe
	 * 
	 * @param $user string;
	 * @param $pwd string;
	 */
	public function __construct($user, $pwd)
	{
		$this->user = (string)$user;
		$this->pwd  = (string)$pwd;
	}
	
	// TODO A IMPLEMENTER
	static public function sanitize($txt){return $txt;}
	
	/**
	 * Donne l'identifiant unique de l'accès au service
	 * 
	 * @return string
	 */
	public function getServiceId()
	{
		return $this->serviceId;
	}
	
	
	//// METHODES ABSTRAITES ////////////////////////////
	
	/**
	 * Permet d'envoyer une Micro note
	 *
	 * @param $txt string Le texte de la note
	 * @return bool
	 */
	abstract public function sendNote($txt);
	
	/**
	 * Permet de récupérer le flux des notes d'un utilisateur donné
	 *
	 * @param $limit int Le nombre de notes à récupérer par page
	 * @param $page int la page de résultat à récupérer
	 * @param $since int le timestamp de la note la plus vieille à récupérer
	 * @param $user string L'ID de l'utilisateur dont on veux le flux de notes
	 * @return array
	 */
	abstract public function getUserTimeline($limit = 20, $page = 1, $since = NULL, $user = NULL);
	
	/**
	 * Permet de récupérer le flux des notes des amis de l'utilisateur du service
	 *
	 * @param $limit int Le nombre de notes à récupérer par page
	 * @param $page int la page de résultat à récupérer
	 * @param $since int le timestamp de la note la plus vieille à récupérer
	 * @return array
	 */
	abstract public function getFriendsTimeline($limit = 20, $page = 1, $since = NULL);
	
	/**
	 * Permet de chercher des notes sur le service de MicroBlog
	 *
	 * @param $query string La requête de recherche
	 * @param $limit int Le nombre de notes à récupérer par page
	 * @param $page int la page de résultat à récupérer
	 * @param $since int le timestamp de la note la plus vieille à récupérer
	 * @return array
	 */
	abstract public function search($query, $limit = 20, $page = 1, $since = NULL);
}