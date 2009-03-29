eventdata 0.3.1 (2009/03/25) plugin for Dotclear 2


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


III. Install:
=============

Voir la proc�dure d'installation des plugins Dotclear 2.


IV. Onlget "Administration":
============================

Cette page permet de g�rer l'activation et l'utilisation de l'extension dans son ensemble sur le blog en cours.

IV.1 G�n�rale:
--------------

"Activer le plugin"
Active ou d�sactive compl�tement l'utilisation du plugin.
  
"Icone de l'extension dans le menu Blog"
Place l'icon de l'extension soit dans la liste "Blog", soit dans la liste "Extensions".
  
"Activer la page public"
Ajoute un page cot� publique ou seront affich�s les �v�nements.

IV.2 Permissions:
-----------------

Il est possible de modifier les permissions des utilisateurs pour acc�der � certaines parties de ce plugin.
Les permissions sans rapport avec ce plugin ne sont pas affect�es. Les actions possibles d�pendent des permissions de l'utilisateur.

"Gestion des �v�nements sur les billets"
Permet la gestion des dates d'�venement depuis l'onglet "Billets" ou directement sur la page des billets.

"Gestion de la liste des cat�gories r�ordonn�es"
Permet la gestion des cat�gories r�ordonner ou pas,

"Gestion de la page public"
Permet la gestion du titre, de la description et du choix du template pour la page publique de la liste des �v�nement.

"Gestion du plugin"
Permet la gestion compl�te du plugin,


VI. Onglet "Billets":
=====================

Cette liste affiche les billets auquels sont associ�es des dates d'�v�nements.
Un billet peut apparaitre plusieurs fois si plusieurs dates d'�v�nement lui sont associ�es.

VI.1 Filtres:
-------------

Les filtres permettent de limiter et de trier la liste des billets affich�s selon diff�rents crit�res.

"Cat�gorie"
Filtrer les billets par cat�gorie.

"�tat"
- "en attente": en attente de publication,
- "programm�": billets mis en ligne aux date et heure indiqu�es dans le champ "Publi� le",
- "non publi�": billets hors ligne,
- "publi�: billets en ligne.

"S�lectionn�"
Aucun, billet marqu� comme s�lectionn� ou non s�lectionn�.

"Trier par"
Permet de trier les r�sultats de filtrage selon la date de publication, la date de d�but de l'�v�nement, 
la date de fin de l'�v�nement, le titre,la cat�gorie, l'auteur, l'�tat de publication ou l'�tat de s�lection.

"Trier"
Indique l'ordre dans lequel on souhaite effectuer le tri.

"P�riode"
- "Non d�but�": �v�nements futurs,
- "D�but�": �v�nements commenc�s,
- "Termin�": �v�nements pass�s,
- "Non termin�": �v�nements pass� ou en cours,
- "En cours": �v�nements commenc�s mais pas termin�s,
- "Pas en cours": �v�nements pas commenc�s ou d�j� termin�s.

"Billets par page"
Nombre de billets � afficher par page de r�sultat.

VI.2 Actions par lot sur les billets:
-------------------------------------

Il est possible d'effectuer un ensemble d'actions sur plusieurs billets, d'un seul coup. 
Les actions possibles d�pendent des permissions de l'utilisateur.
Ces actions sont principalements les m�mes que sur la page des billets avec en plus la possibilt� de supprimer les �v�nements. 
Cette option est �galement directement pr�sente sur la page des billets.

- "Supprimer les �v�nements": Enl�ve les dates d'�v�nement, les billets redeviennent des billets "normaux",
- "Publier": mettre le billet en ligne,
- "Hors ligne": mettre le billet hors ligne,
- "Programmer": programmer le billet pour mise en ligne � la date de publication,
- "En attente: en attente de publication,
- "Marquer comme selectionn�",
- "Marquer comme non selectionn�",
- "Changer de cat�gorie: envoie sur la liste des cat�gories pour changer celle des billets s�lectionn�s,
- "Changer l'auteur: permet de changer l'auteur du billet en indiquant l'identifiant de l'utilisateur qui deviendra le nouvel auteur,
- "Supprimer": supprime le billet (cette op�ration est irr�versible).


VII. Onglet Cat�gories:
=======================

Cette liste affiche les cat�gories et permet de modifier l'ordre d'affichage des billets qui y sont associ�s cot� publique.

VII.1 Etat:
-----------

- "Normal": Marqu� en rouge: L'ordre des billets suit l'ordre par d�faut du th�me.
- "R�ordonn�": Marqu� en vert: Les billets de la cat�gorie sont r�ordonn�s suivant leurs date de d�but d'�v�nement et dans l'ordre d�croissant.

VII.2 Actions par lot sur les cat�gories:
-----------------------------------------

Il est possible d'effectuer un ensemble d'actions sur plusieurs cat�gories, d'un seul coup.

- "Marquer comme r�ordonn�": R�ordonne l'affichage des billets par �v�nement,
- "Marquer comme normal": Enl�ve l'ordre d'affichage des billets par �v�nement.


VIII. Onlget "Mod�les":
=======================

Cette page permet de g�rer diff�rents �l�ments de la page publique "events.html".

VIII.1 Description:
-------------------

Les deux champs suivants seront remplis par les titre et description d'une cat�gories lors de la redircetion de celle ci vers la page des �v�nements.

"Titre"
Titre de la page publique des �v�nements. {{tpl:EventPageTitle}}
  
"Decription"
Description de la page publique des �v�nements. {{tpl:EventPageDescription}}


VIII.2 Th�mes:
--------------

Des th�mes pr�d�finis existent et la disponibilit� du mod�le dans un th�me peut d�pendre du super administrateur dans le cas d'un multiblog.

"Aide"
Des indications sont disponibles pour faciliter le choix du template de la page public.
- "Th�me du blog en cours": Nom du th�me utilis� actuellement,
- "Adaptation existante": Indique si l'extension � un theme adapat� au theme en cours,
- "Page existante dans le th�me du blog": Indique si le th�me en cours est modifi� pour l'extension,
- "Th�me alternatif": Nom du th�me de l'extension utilis� si celui du th�me n'existe pas,

"Pr�fixe du lien"
Permet de changer le lien vers la page publique.

"Choix du template pr�d�fini pour la page publique"
Permet de choisir un th�me particulier de l'extentsion si celui du th�me en cours n'existe pas.

"D�sactiver la liste des dates d'�v�nement d'un billet"
Par d�fault certains mod�les poss�dent l'affichage atomatique de la liste des dates d'�v�nement sur un billet. 
Si vous pr�f�rez utiliser le widget (ou rien du tout) il suffit de d�sactiver cette option.


IX. Wigdets:
============

IX.1 Widget "Liste de �v�nements":
---------------------------------

Un widget en rapport avec les �v�nements est disponible. 
Il permet de lister les �v�nements � la mani�re du widget "Derniers Billets" mais avec de multiples crit�res.

"Titre"
Titre du widget

"Cat�gorie"
Afficher seulement les �v�nement d'une cat�gorie.

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
- "vide": tout afficher,
- "Non d�but�": �v�nements futurs,
- "D�but�": �v�nements commenc�s,
- "Termin�": �v�nements pass�s,
- "Non termin�": �v�nements pass� ou en cours,
- "En cours": �v�nements commenc�s mais pas termin�s,
- "Pas en cours": �v�nements pas commenc�s ou d�j� termin�s.

"Format des dates des billets"
La d�finition des caract�res de formatage est identique � celle du blog.
Voir l'aide de la page param�tres du blog.

"Format du texte des billets"
Le titre de chaque billet peut �tre format� suivant des caract�res particuliers:
- "%T": titre du billet,
- "%C": titre de la cat�gories,
- "%S": date de d�but de l'�v�nement,
- "%E": date de fin de l'�v�nement.

"Format de surbrillance des billets"
Le texte afficher lors du passage de la souris peut �tre format� suivant les m�me crit�res que le texte ci-dessus.

"Uniquement sur la page d'accueil"
Affiche le widget uniquement sur la ge d'accueil


IX.2 Widget "Ev�nements d'un billet":
------------------------------------

Ce widget affiche les dates d'�v�nements associ�s � un billet. 
Il est uniquement pr�sent sur la page d'un billet.
Il supporte plusieurs crit�res.

"Titre"
Titre du widget

"Format des dates des billets"
La d�finition des caract�res de formatage est identique � celle du blog.
Voir l'aide de la page param�tres du blog.
 
"Format du texte des billets"
Le titre de chaque billet peut �tre format� suivant des caract�res particuliers:
- "%S": date de d�but de l'�v�nement,
- "%E": date de fin de l'�v�nement.


X. Modification de l'administration:
====================================

Des options sont ajout�s sur certaines pages d'administration.

X.1 Nouveau billet (post.php):
------------------------------

Sur la page de cr�ation et de modification de billet, dans la barre lat�rale, des choix de dates de d�but et de fin sont ajout�s. 
Il suffit d'entrer une date de d�but et de fin pour associer un �v�nement � un billet. 
Une listes des �v�nemets d�j� li� � un billet peut �tre pr�sente, il suffit de la selectionner pour l'effacer lors de l'enregistrement du billet.

X.2 Billets (posts.php):
------------------------

Sur la page de la listes de billets, dans la listes d'actions, des choix d'ajout ou de suppression de date d'�v�nement par paquet sont ajout�s. 
Pour l'ajout d'�v�nement par paquet tous les billets selectionn�s auront les m�mes dates d'�v�nement.

X.3 Action sur les billets (posts_action.php):
----------------------------------------------

Permet les actions par paquet. (Ajout d'�v�nement sur plusieurs billets.)

X.4 Extension Ev�nements (plugin.php):
--------------------------------------

Bien sur, une page sp�ciale pour la gestion de l'extension est pr�sente soit dans le menu "Blog" soit dans le menu "Extension".


XI. Modification des cat�gories:
================================

Les pages de cat�gories peuvent �tre r�ordonn�es par date de d�but d'�v�nement. (g�r� dans l'onglet Cat�gories de l'extension)
Les cat�gories marqu�es comme r�ordonn�es seront redirig�es vers la page des �v�nements restreint � la cat�gorie correspondante. 
L'extension utilise le behavior "tplBeforeData" pour rediriger la page.


XII. Page publique des �v�nements:
==================================

Une page publique d�di�s aux �v�nements est disponible. 
L'url public de cette page est modifiable et par default "events".
Son th�me ressemble � la page d'une cat�gorie. Des pages, des blocs et des valeurs ont �t� ajout�s:

XII.1 Liste des pages:
----------------------

"events":
C'est la page principale affichant la liste des �v�nements suivant diff�rents crit�res qui sont les m�mes que pour les billets, 
avec la prise en compte de la pagination et de la p�riode. Par exemple, si votre lien de page est <em>events</em> cela donne:
- http://.../events : Affiche tous les �v�nements,
- http://.../events/ongoing : Affiche les �v�nements en cours,
- http://.../events/scheduled : Affiche les �v�nements � venir,
- http://.../events/finished/page/2 : Affiche la 2�me page des �v�nements termin�s,
- http://.../events/feed/rss2 : Affiche le flux RSS des �v�nements.

"eventstheme":
Ce n'est pas une page mais une redirection vers les fichiers de th�me de l'extension, cela permet d'afficher des images, des css, etc...
Les url des images des fichiers css apell�s depuis ce liens seront �galement r��crits.

XII.2 Liste des blocks:
-----------------------

"EventEntries"
Supporte les m�mes attribus que le bloc "Entries" avec en plus:
- Trie des billets par d�but, fin d'�venement,
- restriction du type d'�v�nement, par default "event",
- restriction de periode d'�v�nement (pas) en cours, (pas) commenc�, (pas) fini,
- restriction de date de d�but ou de fin stricte.

A l'interieur de ce bloc, la majorit� des balises et blocs de "Entries" sont valable.

"EventPagination"
Supporte les m�mes attribus que le bloc "Pagination"
Permet de faire la pagination en fonction des �v�nements. (Restore le bon comptage)

"EntryEventDates"
Supporte de nombreux attribus.
Ce bloc liste les �v�nements associ�s � un billet.

"EventDatesHeader"
Voir categoriesHeader.
Utilis�e dans le contexte de la boucle "EntryEventDates", le contenu de cette balise s'affiche uniquement pour la premi�re date de la boucle.

"EventDatesFooter"
idem ci-dessus

XII.3 Liste des valeurs:
------------------------

"EventPageTitle"
Supporte les attribus communs.
Si c'est une cat�orie r�ordonn�e alors EventPageTitle affichera le nom de la cat�gorie.

"EventPageDescription"
Supporte les attribus communs.
Si c'est une cat�orie r�ordonn�e alors EventPageDescription affichera la description de la cat�gorie.

"EventPageURL"
Supporte les attribus communs.
L'URL de la page public des �v�nements. (S'utilise comme {{tpl:BlogURL}} )

"EventPeriod"
Supporte les attribus communs.
Affiche dans quel periode se trouve l'entr�e courante. 
Par exemple si le billet en cours � un �v�nement associ� qui est termin�, la period sera "finished"
Un attribu supl�mentaire est ajout�: "strict", si il est pr�sent, une des valeurs "sheduled", "ongoing", "finished" sera retourn�, 
cela peut servir pour les CSS par exemple.

"EventStartDate"
Supporte les m�mes attribus que "EntryDate".
Date de d�but d'�v�nement.

"EventStartTime"
Idem ci-dessus

"EventEndDate
idem ci-dessus

"EventEndTime"
idem ci-dessus

"EventFullDate"
Support les m�mes attribus que "EntryDate"
Ecrit la date comp�te d'un �v�nement en utilisant la valeur de langue "From %S to %D"
Les attribus supl�mentaires sont:
- "start_format": Pour formater la date de d�but,
- "end_format" : pour formater la date de fin.


Les valeurs "EventStartDate", "EventStartTime", "EventEndDate", "EventEndTime", "EventFullDate" peuvent �tre utilis�es 
soit sur la page "post.html", soit dans une bloc "EventEntries", soit dans un bloc "EntryEventDates".


XIII. Behaviors publiques:
==========================

Les modifications des pages publiques passent par des appelles aux behaviors � differents niveaux.

"publicBeforeDocument"
Inscrit dans le "core" le chemin vers le mod�le de l'extension.
 
"publicHeadContent"
Ajoute au "head" du document le fichier css du mod�le de l'extension.
 
"tplBeforeData"
Redirige les pages des cat�gories r�ordonn� vers la page "events".
 
"publicEntryBeforeContent"
Si le mod�le de l'extension poss�de un fichier "evententrybeforecontent.html"
le contenu de la balise "body" de ce fichier sera ajout� au document. 
Cela sert � ajouter dans un billet la liste des �v�nements li�s au billet sans toucher aux th�mes. 
Cet apelle peut-�tre d�sactiv� dans la page de gestion du mod�le au cas ou on pr�f�re utiliser le widget.
 
"publicEntryAfterContent"
Idem ci-dessus


-----------
End of file