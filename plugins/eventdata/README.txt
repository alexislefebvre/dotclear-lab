eventdata 0.5.4 (2009/06/15) plugin for Dotclear 2

I. Licence:
===========

This file is part of eventdata, a plugin for Dotclear 2.
Copyright (c) 2009 JC Denis and contributors
jcdenis@gdwd.com
Licensed under the GPL version 2.0 license.
A copy of this license is available at
http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Some icons from Silk icon set 1.3 by Mark James at:
http://www.famfamfam.com/lab/icons/silk/
under a Creative Commons Attribution 2.5 License at
http://creativecommons.org/licenses/by/2.5/


II. Support:
============

http://blog.jcdenis.com/?q=dotclear+plugin+eventdata
http://forum.dotclear.net


III. Installation:
==================

Voir la proc�dure d'installation des plugins Dotclear 2.
Pour information, le plugin eventdata cr�e la table "eventdata".


IV. Onlget "Administration":
============================

Cette page permet de g�rer l'activation et l'utilisation de l'extension dans son ensemble sur le blog en cours.

IV.1 G�n�rale:
--------------

"Activer le plugin"
Active ou d�sactive compl�tement l'utilisation du plugin.
 
"Ic�ne de l'extension dans le menu Blog"
Place l'ic�ne de l'extension soit dans la liste "Blog", soit dans la liste "Extensions".
 
"Activer la page public"
Ajoute une page c�t� public o� seront affich�s les �v�nements.

IV.2 Permissions:
-----------------

Il est possible de modifier les permissions des utilisateurs pour acc�der � certaines parties de ce plugin.
Les permissions sans rapport avec ce plugin ne sont pas affect�es. Les actions possibles d�pendent des permissions de l'utilisateur.

"Gestion des �v�nements sur les billets"
Permet la gestion des dates d'�v�nement depuis l'onglet "Billets" ou directement sur la page des billets.

"Gestion de la liste des cat�gories r�ordonn�es"
Permet la gestion des cat�gories r�ordonn�es ou pas.

"Gestion de la page publique"
Permet la gestion du titre, de la description et du choix du template pour la page publique de la liste des �v�nements.

"Gestion du plugin"
Permet la gestion compl�te du plugin.


VI. Onglet "Billets":
=====================

Cette liste affiche les billets auxquels sont associ�es des dates d'�v�nements.
Un billet peut appara�tre plusieurs fois si plusieurs dates d'�v�nement lui sont associ�es.

VI.1 Filtres:
-------------

Les filtres permettent de limiter et de trier la liste des billets affich�s selon diff�rents crit�res.

"Cat�gorie"
Filtrer les billets par cat�gorie.

"�tat"
- "en attente" : en attente de publication,
- "programm�" : billets mis en ligne aux date et heure indiqu�es dans le champ "Publi� le",
- "non publi�" : billets hors ligne,
- "publi�" : billets en ligne.

"S�lectionn�"
Aucun, billet marqu� comme s�lectionn� ou non s�lectionn�.

"Trier par"
Permet de trier les r�sultats de filtrage selon la date de publication, la date de d�but de l'�v�nement, 
la date de fin de l'�v�nement, le lieu de l'�v�nement, le titre, la cat�gorie, l'auteur, 
l'�tat de publication ou l'�tat de s�lection.

"Trier"
Indique l'ordre dans lequel on souhaite effectuer le tri.

"P�riode"
- "Non d�but�" : �v�nements futurs,
- "D�but�" : �v�nements commenc�s,
- "Termin�" : �v�nements pass�s,
- "Non termin�" : �v�nements pass� ou en cours,
- "En cours" : �v�nements commenc�s mais pas termin�s,
- "Pas en cours" : �v�nements pas commenc�s ou d�j� termin�s.

"Billets par page"
Nombre de billets � afficher par page de r�sultat.

VI.2 Actions par lot sur les billets :
--------------------------------------

Il est possible d'effectuer un ensemble d'actions sur plusieurs billets, d'un seul coup. 
Les actions possibles d�pendent des permissions de l'utilisateur.
Ces actions sont principalement les m�mes que sur la page des billets avec en plus la possibilit� de supprimer les �v�nements. 
Cette option est �galement directement pr�sente sur la page des billets.

- "Supprimer les �v�nements : enl�ve TOUTES les dates d'�v�nement de tous les billets s�lectionn�s ; les billets redeviennent des billets "normaux",
- "Publier" : mettre le billet en ligne,
- "Hors ligne" : mettre le billet hors ligne,
- "Programmer" : programmer le billet pour mise en ligne � la date de publication,
- "En attente" : en attente de publication,
- "Marquer comme s�lectionn�",
- "Marquer comme non s�lectionn�",
- "Changer de cat�gorie" : envoie sur la liste des cat�gories pour changer celle des billets s�lectionn�s,
- "Changer l'auteur" : permet de changer l'auteur du billet en indiquant l'identifiant de l'utilisateur qui deviendra le nouvel auteur,
- "Supprimer" : supprime le billet (cette op�ration est irr�versible).

VI.3 �dition d'un �v�nement:
----------------------------

Il est possible de modifier un �v�nement pour un ou plusieurs billets.
La colonne "modifier" de la liste des billets propose deux choix :

"Modifier cet �v�nement pour tous les billets"
Si plusieurs billets ont la m�me date de d�but, de fin et lieu d'�v�nement,
la modification de l'�v�nement sera prise en compte pour tous ces billets.
Une liste permet de voir les billets associ�s en dessous du formulaire d'�dition.

"Modifier cet �v�nement pour ce billet"
La modification sera pris en compte uniquement pour le billet s�lectionn�.
Les autres billets aillant le m�me �v�nement ne seront pas affect�s.
Une liste permet de voir le billet associ� en dessous du formulaire d'�dition.

"Effacer cet �v�nement pour ce billet"
La suppression concernera uniquement le billet s�lectionn�.


VII. Onglet Cat�gories:
=======================

Cette liste affiche les cat�gories et permet de modifier l'ordre d'affichage des billets qui y sont associ�s cot� public.
Elle permet d'interdire l'affichage d'�v�nements appartenant � certaines cat�gories dans les widgets ou la page d'�v�nements.


VII.1 R�ordonn�:
----------------

- "Normal" (marqu� en rouge) : l'ordre des billets suit l'ordre par d�faut du th�me.
- "R�ordonn�" (marqu� en vert) : les billets de la cat�gorie sont r�ordonn�s suivant leur date de d�but d'�v�nement et dans l'ordre d�croissant.


VII.2 Cach�:
------------

- "Normal" (marqu� en vert) : la cat�gorie sera prise en compte partout cot� public.
- "Cach�" (marqu� en rouge) : la cat�gorie ne sera pas prise en compte dans les widgets (sauf si sp�cifi�),
  ni sur la page d'�v�nements (sauf si c'est la page de la cat�gorie r�ordonn�e).


VII.3 Actions par lot sur les cat�gories:
-----------------------------------------

Il est possible d'effectuer un ensemble d'actions sur plusieurs cat�gories, d'un seul coup.

- "Marquer comme r�ordonn�" : r�ordonne l'affichage des billets par �v�nement,
- "Marquer comme normal" : enl�ve l'ordre d'affichage des billets par �v�nement,
- "Marquer comme cach�" : les �v�nements de cette cat�gories ne seront pas pris en compte sur la page g�n�rale des �v�nements,
- "Marquer comme non cach�" : la cat�gorie se comportera normalement.


VIII. Onlget "Mod�les":
=======================

Cette page permet de g�rer diff�rents �l�ments de la page publique "eventdatas.html".


VIII.1 Description :
--------------------

Les deux champs suivants seront remplac�s par les titre et description d'une cat�gorie 
lors de la redirection de celle-ci vers la page des �v�nements.

"Titre"
Titre de la page publique des �v�nements. {{tpl:EventdataPageTitle}}
 
"Description"
Description de la page publique des �v�nements. {{tpl:EventdataPageDescription}}


VIII.2 Th�mes :
---------------

Des th�mes pr�d�finis existent et la disponibilit� du mod�le dans un th�me peut 
d�pendre du super administrateur dans le cas d'un multiblog.

"Aide"
Des indications sont disponibles pour faciliter le choix du template de la page publique.
- "Th�me du blog en cours" : nom du th�me utilis� actuellement,
- "Adaptation existante" : indique si l'extension � un theme adapat� au theme en cours,
- "Page existante dans le th�me du blog" : indique si le th�me en cours est modifi� pour l'extension,
- "Th�me alternatif" : nom du th�me de l'extension utilis� si celui du th�me n'existe pas.

"Pr�fixe du lien"
Permet de changer le lien vers la page publique.

"Choix du template pr�d�fini pour la page publique"
Permet de choisir un th�me particulier de l'extentsion si celui du th�me en cours n'existe pas.

"D�sactiver la liste des dates d'�v�nement d'un billet"
Par d�fault certains mod�les poss�dent l'affichage automatique de la liste des dates d'�v�nement sur un billet. 
Si vous pr�f�rez utiliser le widget (ou rien du tout) il suffit de d�sactiver cette option.


IX. Wigdets :
============

IX.1 Widget "Liste des �v�nements" :
---------------------------------

Un widget en rapport avec les �v�nements est disponible. 
Il permet de lister les �v�nements � la mani�re du widget "Derniers billets" mais avec de multiples crit�res.

"Titre"
Titre du widget

"Cat�gorie"
Afficher seulement les �v�nements d'une cat�gorie.

"Tag"
Si le plugin "Metadata" est install�, cela permet de limiter l'affichage uniquement � certains tags.

"Nombre maximum de billets"
Limite le nombre de billets affich�s

"Trier par"
Permet de trier les r�sultats de filtrage selon la date de publication, la date de d�but de l'�v�nement, 
la date de fin de l'�v�nement, le titre, la cat�gorie, l'auteur, l'�tat de publication ou l'�tat de s�lection.

"Trier"
Indique l'ordre dans lequel on souhaite effectuer le tri.

"S�lectionn�"
Aucun, billet marqu� comme s�lectionn� ou non s�lectionn�.
 
"P�riode"
- "vide" : tout afficher,
- "Non d�but�" : �v�nements futurs,
- "D�but�" : �v�nements commenc�s,
- "Termin�" : �v�nements pass�s,
- "Non termin�" : �v�nements pass�s ou en cours,
- "En cours" : �v�nements commenc�s mais pas termin�s,
- "Pas en cours" : �v�nements pas commenc�s ou d�j� termin�s.

"Format des dates des billets"
La d�finition des caract�res de formatage est identique � celle du blog.
Voir l'aide de la page Param�tres du blog.

"Format du texte des billets"
Le titre de chaque billet peut �tre format� suivant des caract�res particuliers:
- "%T" : titre du billet,
- "%C" : titre de la cat�gories,
- "%S" : date de d�but de l'�v�nement,
- "%E" : date de fin de l'�v�nement,
- "%D" : dur�e de l'�v�nement,
- "%L" : lieu de l'�v�nement.

"Format de surbrillance des billets"
Le texte affich� lors du passage de la souris peut �tre format� suivant les m�me crit�res que le texte ci-dessus.

"Uniquement sur la page d'accueil"
Affiche le widget uniquement sur la page d'accueil


IX.2 Widget "�v�nements d'un billet" :
--------------------------------------

Ce widget affiche les dates d'�v�nements associ�s � un billet. 
Il est uniquement pr�sent sur la page d'un billet.
Il supporte plusieurs crit�res.

"Titre"
Titre du widget

"Format des dates des billets"
La d�finition des caract�res de formatage est identique � celle du blog.
Voir l'aide de la page param�tres du blog.
 
"Format du texte des billets"
Le titre de chaque billet peut �tre format� suivant des caract�res particuliers  :
- "%S" : date de d�but de l'�v�nement,
- "%E" : date de fin de l'�v�nement,
- "%D" : dur�e de l'�v�nement,
- "%L" : lieu de l'�v�nement.


X. Modification de l'administration :
=====================================

Des options sont ajout�es sur certaines pages d'administration.


X.1 Nouveau billet (post.php):
------------------------------

Sur la page de cr�ation et de modification de billet, dans la barre lat�rale, des choix de date de d�but, de date de fin 
ainsi que de lieu d'�v�nement sont ajout�s. 
Il suffit d'entrer une date de d�but et de fin pour associer un �v�nement � un billet. Le lieu est facultatif.
Une listes des �v�nemets d�j� li�s � un billet peut �tre pr�sente.
Si le langage javascript est actif, il est possible de supprimer ou d'ajouter des �v�nements sans enregistrer le billet, 
sinon il faut enregistrer le billet pour que les changements sur les �v�nements soit pris en compte.
Un lien vers l'�dition d'un �v�nement est �galement pr�sent pour chaque �v�nement associ� au billet.


X.2 Billets (posts.php):
------------------------

Sur la page de la liste des billets, dans la liste d'actions, des choix d'ajout ou de suppression de date d'�v�nement par lot sont ajout�s. 
Pour l'ajout d'�v�nement par lot tous les billets s�lectionn�s auront les m�mes dates et lieu d'�v�nement.


X.3 Action sur les billets (posts_action.php):
----------------------------------------------

Permet les actions par paquet. (Ajout/suppression d'�v�nement sur plusieurs billets.)


X.4 Extension �v�nements (plugin.php):
--------------------------------------

Bien s�r, une page sp�ciale pour la gestion de l'extension est pr�sente soit dans le menu "Blog" soit dans le menu "Extensions".


XI. Modification des cat�gories:
================================

Les pages de cat�gories peuvent �tre r�ordonn�es par date de d�but d'�v�nement (g�r� dans l'onglet Cat�gories de l'extension).
Les cat�gories marqu�es comme r�ordonn�es seront redirig�es vers la page des �v�nements restreints � la cat�gorie correspondante. 
L'extension utilise le behavior "tplBeforeData" pour rediriger la page.


XII. Page publique des �v�nements :
===================================

Une page publique d�di�e aux �v�nements est disponible. 
L'CSS publique de cette page est modifiable et est par default "events".
Son th�me ressemble � la page d'une cat�gorie. Des pages, des blocs et des valeurs ont �t� ajout�s :


XII.1 Liste des pages :
-----------------------

"events":
C'est la page principale affichant la liste des �v�nements suivant diff�rents crit�res qui sont les m�mes que pour les billets, 
avec la prise en compte de la pagination et de la p�riode. Par exemple, si votre lien de page est <em>events</em> cela donne:
- http://.../events : affiche tous les �v�nements,
- http://.../events/ongoing : affiche les �v�nements en cours,
- http://.../events/scheduled : affiche les �v�nements � venir,
- http://.../events/finished/page/2 : affiche la 2e page des �v�nements termin�s,
- http://.../events/category/MaCat�gorie/ongoing/page/3 : affiche la 3e page des �v�nements en cours de la cat�gorie "MaCat�gorie"
- http://.../events/feed/rss2 : affiche le flux RSS des �v�nements,
- http://.../events/feed/all.ics : affiche le flux ICS de tous les �v�nements.
De nombreuses combinaisons d'URL sont possibles, � vous de les tester.

"eventstheme":
Ce n'est pas une page mais une redirection vers les fichiers de th�me de l'extension, cela permet d'afficher des images, des CSS, etc.
Les URLs des images des fichiers CSS appel�es depuis ce lien seront �galement r��crites.


XII.2 Liste des blocs :
-----------------------

"EventdataEntries"
Supporte les m�mes attribus que le bloc "Entries" avec en plus :
- tri des billets par d�but, fin, lieu d'�v�nement, {{tpl:EventdataEntries sortby="start"}}
- restriction du type d'�v�nement, par d�fault "eventdata", {{tpl:EventdataEntries eventdata_type="eventdata"}}
- restriction de p�riode d'�v�nement (pas) en cours, (pas) commenc�, (pas) fini, {{tpl:EventdataEntries period="ongoing"}}
- restriction de date de d�but ou de fin stricte. {{tpl:EventdataEntries eventdata_start="2012-12-25 23:59:00"}}

� l'int�rieur de ce bloc, la majorit� des balises et blocs de "Entries" sont valables.

"EventdataPagination"
Supporte les m�mes attributs que le bloc "Pagination".
Permet de faire la pagination en fonction des �v�nements. (Restaure le bon comptage)

"EntryEventdataDates"
Supporte de nombreux attributs.
Ce bloc liste les �v�nements associ�s � un billet.

"EventdataDatesHeader"
Voir categoriesHeader.
Utilis�e dans le contexte de la boucle "EntryEventdataDates", le contenu de cette balise s'affiche uniquement pour la premi�re date de la boucle.

"EventdataDatesFooter"
Idem ci-dessus.


XII.3 Liste des valeurs :
-------------------------

"EventdataPageTitle"
Supporte les attributs communs.
Si c'est une cat�gorie r�ordonn�e, alors EventdataPageTitle affichera le nom de la cat�gorie.

"EventdataPageDescription"
Supporte les attributs communs.
Si c'est une cat�orie r�ordonn�e, alors EventdataPageDescription affichera la description de la cat�gorie.

"EventdataPageURL"
Supporte les attribus communs.
L'URL de la page publique des �v�nements. S'utilise comme {{tpl:BlogURL}}.

"EventdataPageNav"
Supporte les attributs communs.
Menu de choix de p�riode d'�v�nements � afficher (ex : Non d�but�, En cours, etc.).
Un attribut suppl�mantaire est ajout� : "menus", il permet de limiter le menu � des choix pr�d�finis parmi les suivants : 'ongoing','outgoing','notstarted','started','notfinished','finished','all'. Par exemple pour limiter le menu � 2 choix 
il faut utiliser {{tpl:EventdataPageNav menus="notstarted,ongoing"}} ce qui donnera le menu suivant :
"<div id="eventdata_nav"><ul><li><a href="...">Non d�but�</a></li><li><a href="...">En cours</a></li></ul></div>"
Si un tri est reconnu, la balise "li" prendra la class "active".

"EventdataPeriod"
Supporte les attributs communs.
Affiche dans quelle p�riode se trouve l'entr�e courante. 
Par exemple si le billet en cours a un �v�nement associ� qui est termin�, la p�riode sera "finished".
Un attribut suppl�mentaire est ajout� : "strict". S'il est pr�sent, une des valeurs "scheduled", "ongoing", "finished" sera retourn�e, 
cela peut servir pour les CSS par exemple.

"EventdataLocation"
Supporte les attributs communs.
Lieu de l'�v�nement.

"EventdataDuration"
Supporte les attributs communs.
Dur�e de l'�v�nement.

"EventdataStartDate"
Supporte les m�mes attributs que "EntryDate".
Date de d�but d'�v�nement.

"EventdataStartTime"
Idem ci-dessus.

"EventdataEndDate
Supporte les m�mes attributs que "EntryDate".
Date de fin d'�v�nement.

"EventdataEndTime"
Idem ci-dessus.

"EventdataFullDate"
Supporte les m�mes attributs que "EntryDate"
�crit la date compl�te d'un �v�nement en utilisant la valeur de langue "From %S to %E"
Les attributs suppl�mentaires sont :
- "start_format" : pour formater la date de d�but, {{tpl:EventdataFullDate start_format="%A %d %m"}}
- "end_format" : pour formater la date de fin.

Les valeurs "EventdataLocation", "EventdataDuration", "EventdataStartDate", "EventdataStartTime", 
"EventdataEndDate", "EventdataEndTime", "EventdataFullDate" peuvent �tre utilis�es 
soit sur la page "post.html", soit dans un bloc "EventdataEntries", soit dans un bloc "EntryEventdataDates".


XIII. Behaviors publics :
=========================

Les modifications des pages publiques passent par des appels aux behaviors � diff�rents niveaux.

"publicBeforeDocument"
Inscrit dans le "core" le chemin vers le mod�le de l'extension.
 
"publicHeadContent"
Ajoute au "head" du document le fichier CSS du mod�le de l'extension.
 
"tplBeforeData"
Redirige les pages des cat�gories r�ordonn�es vers la page "events".
 
"publicEntryBeforeContent"
Si le mod�le de l'extension poss�de un fichier "eventdataentrybeforecontent.html"
le contenu du bloc "body" de ce fichier sera ajout� au document. 
Cela sert � ajouter dans un billet la liste des �v�nements li�s au billet sans toucher aux th�mes. 
Cet appel peut-�tre d�sactiv� dans la page de gestion du mod�le au cas o� on pr�f�re utiliser le widget.
 
"publicEntryAfterContent"
Idem ci-dessus.


XIV. Pour aller plus loin:
==========================

XIV.1 RSS2.0 et Atom:
---------------------

Des flux RSS2 et Atom sont disponibles pour les �v�nements.
Ces flux se pr�sentent sous la m�me forme que les autres flux de Dotclear avec en plus 
le support de modules event (http://web.resource.org/rss/1.0/modules/event/).
Attention la date de publication d'un �v�nement ne change pas et reste celle du billet associ�.

Les chemins vers ses flux/fichiers passent par l'URL publique de la page "events",
la restriction des billets peut �tre faite � des cat�gories et/ou � des p�riodes d'�v�nements.

Exemples:

"http://.../events/feed/rss2/all" 
Affiche le flux RSS2 de tous les �v�nements.

"http://.../events/feed/rss2/category/Cin�ma/scheduled" 
Affiche le flux RSS2 des �v�nements � venir de la cat�gorie "Cin�ma".


XIV.2 iCalendar:
----------------

Des flux iCal sont disponibles pour les �v�nements.
Les chemin vers ses flux/fichiers passent par l'URL publique de la page "events",
la restriction des billets peut �tre faite � des cat�gories et/ou � des p�riodes d'�v�nements.

Le chemin vers ces flux doit se terminer par l'extension ".ics".

Exemples:

"http://.../events/feed/all.ics" 
Affiche le flux ICS de tous les �v�nements.

"http://.../events/feed/category/Cin�ma/scheduled.ics" 
Affiche le flux ICS des �v�nements � venir de la cat�gorie "cin�ma".


XV. Remerciements:
==================

Je tiens � remercier les personnes qui ont eu la patience de tester toutes les versions d'essai
et de donner un coup de main (surtout Tomek et jmh2o).
Je remercie �galement toute l'�quipe de Dotclear (que ce soit le patron, le lab, la ml, DotAddict...)

-----------
End of file