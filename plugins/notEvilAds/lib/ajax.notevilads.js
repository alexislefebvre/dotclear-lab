/***************************************************************\
 *  This is 'Not Evil Ads', a PHP script for websites          *
 *                                                             *
 *  Copyright (c) 2007                                         *
 *  Oleksandr Syenchuk and contributors.                       *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along 'Not evil ads' (see COPYING.txt);            *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

// Récupère getHTTPObject, par OpenWeb Group - openweb.eu.org
function notEvilAdsGetHTTPObject()
{
	var xmlhttp = false;

	/* Compilation conditionnelle d'IE */
	/*@cc_on
	@if (@_jscript_version >= 5)
	try
	{
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch (e)
	{
		try
		{
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch (E)
		{
			xmlhttp = false;
		}
	}
	@else
	xmlhttp = false;
	@end @*/

	/* on essaie de créer l'objet si ce n'est pas déjà fait */
	if (!xmlhttp && typeof XMLHttpRequest != 'undefined')
	{
		try
		{
			xmlhttp = new XMLHttpRequest();
		}
		catch (e)
		{
			xmlhttp = false;
		}
	}
	return xmlhttp;
}


// Met à jour le statut d'affichage des pubs
function notEvilAdsSetStatus(status)
{
	if (status)
		notEvilAds_status = 1;
	else
		notEvilAds_status = 0;
	
	// Créer un cookie suffit pour conserver les paramètres pour la session suivante
	var date = new Date();
	date.setTime(date.getTime()+(notEvilAds_cookiedays*86400000));
	var expires = "; expires="+date.toGMTString();
	var path = "; path="+notEvilAds_cookiepath;
	var domain = "; domain="+notEvilAds_cookiedomain;
	if (notEvilAds_easycookie)
		document.cookie = notEvilAds_cookiename+"="+notEvilAds_status+expires+";path =/";
	else
		document.cookie = notEvilAds_cookiename+"="+notEvilAds_status+expires+path+domain;
	return true;
}

// Demande au serveur les informations relatives à l'affichage des pubs
// Puis enregistre ces informations dans les variables globales
function notEvilAdsGetStatus()
{
	notEvilAds_xmlhttp.onreadystatechange = function ()
	{
		if (notEvilAds_xmlhttp.readyState == 4 && notEvilAds_xmlhttp.status == 200 && notEvilAds_xmlhttp.responseXML)
		{
			notEvilAds_status = notEvilAds_xmlhttp.responseXML.getElementsByTagName('status').item(0).firstChild.data;
			notEvilAds_cookiename = notEvilAds_xmlhttp.responseXML.getElementsByTagName('cookiename').item(0).firstChild.data;
			notEvilAds_cookiedays = notEvilAds_xmlhttp.responseXML.getElementsByTagName('cookiedays').item(0).firstChild.data;
			notEvilAds_cookiepath = notEvilAds_xmlhttp.responseXML.getElementsByTagName('cookiepath').item(0).firstChild.data;
			notEvilAds_cookiedomain = notEvilAds_xmlhttp.responseXML.getElementsByTagName('cookiedome').item(0).firstChild.data;
			notEvilAds_easycookie = notEvilAds_xmlhttp.responseXML.getElementsByTagName('easycookie').item(0).firstChild.data;
			notEvilAds_elements = notEvilAds_xmlhttp.responseXML.getElementsByTagName('identifiers').item(0).firstChild.data.split(',');
			notEvilAds_functionnal = 1;
			document.body.style.cursor = 'default';
		}
	};
	
	notEvilAds_xmlhttp.open("POST",notEvilAds_xmlresponsefile,true);
	notEvilAds_xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	notEvilAds_xmlhttp.send("notEvilAdsGetStatus=1");
	document.body.style.cursor = 'wait';
}

// Télécharge l'élément selon son id sur le serveur et l'affiche
// Traitement asynchrone désactivé pour éviter plusieurs requêtes sur la même
// instance XMLHttpRequest
function notEvilAdsPrintElement(id)
{
	if (document.getElementById(id))
	{
		document.body.style.cursor = 'wait';
		notEvilAds_xmlhttp.open("POST",notEvilAds_xmlresponsefile,false);
		notEvilAds_xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		notEvilAds_xmlhttp.send("notEvilAdsGetContent="+id);
		document.getElementById(id).innerHTML = notEvilAds_xmlhttp.responseText;
	}
}

// Supprile le contenu d'un élément selon son id
function notEvilAdsEraseElement(id)
{
	if (document.getElementById(id))
	{
		document.getElementById(id).style.display = 'none';
	}
}

// Met à jour les bandeaux publicitaires
function notEvilAdsVisibilityTrigger()
{
	nb = notEvilAds_elements.length;
	if (notEvilAds_status)
		for (i=0; i<nb; i++)
			notEvilAdsPrintElement(notEvilAds_elements[i]);
	else
		for (i=0; i<nb; i++)
			notEvilAdsEraseElement(notEvilAds_elements[i]);
}

function notEvilAdsInit(XMLResponseFile)
{
	notEvilAds_xmlresponsefile = XMLResponseFile;
	
	if (notEvilAds_xmlhttp && document.getElementById && document.createElement)
		notEvilAdsGetStatus();
}

//Initialisation du script
var notEvilAds_functionnal = 0;
var notEvilAds_status = 1;
var notEvilAds_cookiename;
var notEvilAds_cookiedays;
var notEvilAds_cookiepath;
var notEvilAds_cookiedomain;
var notEvilAds_easycookie;
var notEvilAds_elements = new Array();
var notEvilAds_xmlhttp = notEvilAdsGetHTTPObject();
var notEvilAds_xmlresponsefile = 'xml.notevilads.php';

/* La partie Ajax de Not Evil Ads s'arrête là. Du code JavaScript est nécessaire
 * sur la page affichant les publicités afin d'appeller les différentes
 * fonctions de ce script. Son implémentatiion reste à la charge du Webmaster
 * et dépend de la façon dont il souhaite mettre en oeuvre l'interface
 * utilisateur pour Not evil ads.
 * Un exemple est toutefois fourni dans le fichier example.php
 */
