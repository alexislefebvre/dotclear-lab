eventdata 0.5.0 (2009/04/14) plugin for Dotclear 2

Préambule:
==========

! Due to a conflict with an older plugin called "icsFeed", 
! versions of "eventdata" less than 0.3.4 are not compatible with the following versions.
! A recovery tool will attempt to transfer the dates in database, see tab "uninstall".
! But all changed themes will be updated manually.
! The main differences are the changes of names including "Event" to "Eventdata".
! (css class names, blocks names, values names)
! Events widgets must replaced.
! All eventdata settings are losts
! Sorry for the inconvenience.

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

Voir la procédure d'installation des plugins Dotclear 2.
Pour information, le plugin eventdata" créé la table "eventdata".


IV. Onlget "Administration":
============================

Cette page permet de gèrer l'activation et l'utilisation de l'extension dans son ensemble sur le blog en cours.

IV.1 Générale:
--------------

"Activer le plugin"
Active ou désactive complètement l'utilisation du plugin.
  
"Icone de l'extension dans le menu Blog"
Place l'icon de l'extension soit dans la liste "Blog", soit dans la liste "Extensions".
  
"Activer la page public"
Ajoute un page coté publique ou seront affichés les événements.

IV.2 Permissions:
-----------------

Il est possible de modifier les permissions des utilisateurs pour accèder à certaines parties de ce plugin.
Les permissions sans rapport avec ce plugin ne sont pas affectées. Les actions possibles dépendent des permissions de l'utilisateur.

"Gestion des événements sur les billets"
Permet la gestion des dates d'évenement depuis l'onglet "Billets" ou directement sur la page des billets.

"Gestion de la liste des catégories réordonnées"
Permet la gestion des catégories réordonner ou pas,

"Gestion de la page public"
Permet la gestion du titre, de la description et du choix du template pour la page publique de la liste des événement.

"Gestion du plugin"
Permet la gestion complète du plugin,


VI. Onglet "Billets":
=====================

Cette liste affiche les billets auquels sont associées des dates d'événements.
Un billet peut apparaitre plusieurs fois si plusieurs dates d'événement lui sont associées.

VI.1 Filtres:
-------------

Les filtres permettent de limiter et de trier la liste des billets affichés selon diffèrents critères.

"Catégorie"
Filtrer les billets par catégorie.

"État"
- "en attente": en attente de publication,
- "programmé": billets mis en ligne aux date et heure indiquées dans le champ "Publié le",
- "non publié": billets hors ligne,
- "publié: billets en ligne.

"Sélectionné"
Aucun, billet marqué comme sélectionné ou non sélectionné.

"Trier par"
Permet de trier les résultats de filtrage selon la date de publication, la date de début de l'événement, 
la date de fin de l'événement, le lieu de l'événement, le titre, la catégorie, l'auteur, 
l'état de publication ou l'état de sélection.

"Trier"
Indique l'ordre dans lequel on souhaite effectuer le tri.

"Période"
- "Non débuté": événements futurs,
- "Débuté": événements commencés,
- "Terminé": événements passés,
- "Non terminé": événements passé ou en cours,
- "En cours": événements commencés mais pas terminés,
- "Pas en cours": événements pas commencés ou déjà terminés.

"Billets par page"
Nombre de billets à afficher par page de résultat.

VI.2 Actions par lot sur les billets:
-------------------------------------

Il est possible d'effectuer un ensemble d'actions sur plusieurs billets, d'un seul coup. 
Les actions possibles dépendent des permissions de l'utilisateur.
Ces actions sont principalements les mêmes que sur la page des billets avec en plus la possibilté de supprimer les événements. 
Cette option est également directement présente sur la page des billets.

- "Supprimer les événements": Enlève les dates d'événement, les billets redeviennent des billets "normaux",
- "Publier": mettre le billet en ligne,
- "Hors ligne": mettre le billet hors ligne,
- "Programmer": programmer le billet pour mise en ligne à la date de publication,
- "En attente: en attente de publication,
- "Marquer comme selectionné",
- "Marquer comme non selectionné",
- "Changer de catégorie: envoie sur la liste des catégories pour changer celle des billets sélectionnés,
- "Changer l'auteur: permet de changer l'auteur du billet en indiquant l'identifiant de l'utilisateur qui deviendra le nouvel auteur,
- "Supprimer": supprime le billet (cette opération est irréversible).

VI.3 Edition d'un événement:
----------------------------

Il est possible de modifier un événement pour un ou plusieurs billets.
La colone "modifier" de la liste des billets propose deux choix:

"Modifier cet événement pour tous les billets"
Si plusieurs billets ont la même date de début, de fin et lieu dévénement,
la modification de l'événement sera pris en compte pour tous ces billets.
Une liste permet de voir les billets associés en dessous du formulaire d'édition.

"Modifier cet événement pour ce billet"
La modification sera pris en compte uniquement pour le billet selectionné.
Les autres billets aillant le même événement ne seront pas affectés.
Une liste permet de voir le billet associé en dessous du formulaire d'édition.

VII. Onglet Catégories:
=======================

Cette liste affiche les catégories et permet de modifier l'ordre d'affichage des billets qui y sont associés coté publique.
Elle permet d'interdire l'affichage d'événements appartenant à cetaines catégories dans les widgets ou la page d'événements.

VII.1 Réordonné:
----------------

- "Normal": Marqué en rouge: L'ordre des billets suit l'ordre par défaut du thème.
- "Réordonné": Marqué en vert: Les billets de la catégorie sont réordonnés suivant leurs date de début d'événement et dans l'ordre décroissant.

VII.2 Caché:
------------

- "Normal": Marqué en vert: La catégorie sera prise en compte partout coté publique.
- "Caché": Marqué en rouge: La catégorie ne sera pas prise en compte dans les widgets (sauf si spécifié),
   ni sur la page d'événement (sauf si c'est la page de la catégorie réordonné).

VII.3 Actions par lot sur les catégories:
-----------------------------------------

Il est possible d'effectuer un ensemble d'actions sur plusieurs catégories, d'un seul coup.

- "Marquer comme réordonné": Réordonne l'affichage des billets par événement,
- "Marquer comme normal": Enlève l'ordre d'affichage des billets par événement,
- "Marquer comme caché": Les événement de cette catégories ne seront pas pris en compte sur la page générale des événements,
- "Marquer comme non caché": La catégorie se comportera normalement.


VIII. Onlget "Modèles":
=======================

Cette page permet de gèrer diffèrents éléments de la page publique "eventdatas.html".

VIII.1 Description:
-------------------

Les deux champs suivants seront remplacés par les titre et description d'une catégories 
lors de la redircetion de celle-ci vers la page des événements.

"Titre"
Titre de la page publique des événements. {{tpl:EventdataPageTitle}}
  
"Decription"
Description de la page publique des événements. {{tpl:EventdataPageDescription}}


VIII.2 Thèmes:
--------------

Des thèmes prédéfinis existent et la disponibilité du modèle dans un thème peut 
dépendre du super administrateur dans le cas d'un multiblog.

"Aide"
Des indications sont disponibles pour faciliter le choix du template de la page public.
- "Thème du blog en cours": Nom du thème utilisé actuellement,
- "Adaptation existante": Indique si l'extension à un theme adapaté au theme en cours,
- "Page existante dans le thème du blog": Indique si le thème en cours est modifié pour l'extension,
- "Thème alternatif": Nom du thème de l'extension utilisé si celui du thème n'existe pas,

"Préfixe du lien"
Permet de changer le lien vers la page publique.

"Choix du template prédéfini pour la page publique"
Permet de choisir un thème particulier de l'extentsion si celui du thème en cours n'existe pas.

"Désactiver la liste des dates d'événement d'un billet"
Par défault certains modèles possèdent l'affichage atomatique de la liste des dates d'événement sur un billet. 
Si vous préférez utiliser le widget (ou rien du tout) il suffit de désactiver cette option.


IX. Wigdets:
============

IX.1 Widget "Liste de événements":
---------------------------------

Un widget en rapport avec les événements est disponible. 
Il permet de lister les événements à la manière du widget "Derniers Billets" mais avec de multiples critères.

"Titre"
Titre du widget

"Catégorie"
Afficher seulement les événement d'une catégorie.

"Tag"
Si le plugin "Metadata" est installé, cela permet de limiter l'affichage uniquement à certains tags.

"Nombre maximum de billets"
Limite le nombre de billets affichés

"Trier par"
Permet de trier les résultats de filtrage selon la date de publication, la date de début de l'événement, 
la date de fin de l'événement, le titre, la catégorie, l'auteur, l'état de publication ou l'état de sélection.

"Trier"
Indique l'ordre dans lequel on souhaite effectuer le tri.

"Sélectionné"
Aucun, billet marqué comme sélectionné ou non sélectionné.
  
"Période"
- "vide": tout afficher,
- "Non débuté": événements futurs,
- "Débuté": événements commencés,
- "Terminé": événements passés,
- "Non terminé": événements passé ou en cours,
- "En cours": événements commencés mais pas terminés,
- "Pas en cours": événements pas commencés ou déjà terminés.

"Format des dates des billets"
La définition des caractères de formatage est identique à celle du blog.
Voir l'aide de la page paramètres du blog.

"Format du texte des billets"
Le titre de chaque billet peut être formaté suivant des caractères particuliers:
- "%T": titre du billet,
- "%C": titre de la catégories,
- "%S": date de début de l'événement,
- "%E": date de fin de l'événement,
- "%D": durée de l'événement,
- "%L": lieu de l'événement.

"Format de surbrillance des billets"
Le texte afficher lors du passage de la souris peut être formaté suivant les même critères que le texte ci-dessus.

"Uniquement sur la page d'accueil"
Affiche le widget uniquement sur la ge d'accueil


IX.2 Widget "Evènements d'un billet":
------------------------------------

Ce widget affiche les dates d'événements associés à un billet. 
Il est uniquement présent sur la page d'un billet.
Il supporte plusieurs critères.

"Titre"
Titre du widget

"Format des dates des billets"
La définition des caractères de formatage est identique à celle du blog.
Voir l'aide de la page paramètres du blog.
 
"Format du texte des billets"
Le titre de chaque billet peut être formaté suivant des caractères particuliers:
- "%S": date de début de l'événement,
- "%E": date de fin de l'événement,
- "%D": durée de l'événement,
- "%L": lieu de l'événement.


X. Modification de l'administration:
====================================

Des options sont ajoutés sur certaines pages d'administration.

X.1 Nouveau billet (post.php):
------------------------------

Sur la page de création et de modification de billet, dans la barre latérale, des choix de dates de début, de date de fin 
ainsi que de lieu d'événement sont ajoutés. 
Il suffit d'entrer une date de début et de fin pour associer un événement à un billet. Le lieu est facultatif.
Une listes des évènemets dèjà liés à un billet peut être présente.
Si le language javascript est actif, il est possible de supprimer ou d'ajouter des événements sans enregistrer le billet, 
sinon il faut enregistrer le billet pour que les changements sur les événements soit pris en compte.
Un lien vers l'édition d'un événement et également présent pour chaque événement associé au billet.

X.2 Billets (posts.php):
------------------------

Sur la page de la listes de billets, dans la listes d'actions, des choix d'ajout ou de suppression de date d'événement par paquet sont ajoutés. 
Pour l'ajout d'événement par paquet tous les billets selectionnés auront les mêmes dates et lieu d'événement.

X.3 Action sur les billets (posts_action.php):
----------------------------------------------

Permet les actions par paquet. (Ajout/suppression d'événement sur plusieurs billets.)

X.4 Extension Evénements (plugin.php):
--------------------------------------

Bien sur, une page spéciale pour la gestion de l'extension est présente soit dans le menu "Blog" soit dans le menu "Extension".


XI. Modification des catégories:
================================

Les pages de catégories peuvent être réordonnées par date de début d'événement. (gèré dans l'onglet Catégories de l'extension)
Les catégories marquées comme réordonnées seront redirigées vers la page des événements restreint à la catégorie correspondante. 
L'extension utilise le behavior "tplBeforeData" pour rediriger la page.


XII. Page publique des événements:
==================================

Une page publique dédiés aux événements est disponible. 
L'url publique de cette page est modifiable et est par default "events".
Son thème ressemble à la page d'une catégorie. Des pages, des blocs et des valeurs ont été ajoutés:

XII.1 Liste des pages:
----------------------

"events":
C'est la page principale affichant la liste des événements suivant diffèrents critères qui sont les mêmes que pour les billets, 
avec la prise en compte de la pagination et de la période. Par exemple, si votre lien de page est <em>events</em> cela donne:
- http://.../events : Affiche tous les événements,
- http://.../events/ongoing : Affiche les événements en cours,
- http://.../events/scheduled : Affiche les événements à venir,
- http://.../events/finished/page/2 : Affiche la 2ème page des événements terminés,
- http://.../events/category/MaCatégorie/ongoing/page/3 : Affiche la 3émé page des événements en cours de la catégorie "MaCatégorie"
- http://.../events/feed/rss2 : Affiche le flux RSS des événements.
- http://.../events/feed/all.ics : Affiche le flux ICS de tous les événements.
De nombreuses combinaisons d'URL sont possible, à vous de les tester.

"eventstheme":
Ce n'est pas une page mais une redirection vers les fichiers de thème de l'extension, cela permet d'afficher des images, des css, etc...
Les url des images des fichiers css apellés depuis ce liens seront également réécrits.

XII.2 Liste des blocks:
-----------------------

"EventdataEntries"
Supporte les mêmes attribus que le bloc "Entries" avec en plus:
- Trie des billets par début, fin, lieu d'événement, {{tpl:EventdataEntries sortby="start"}}
- restriction du type d'événement, par default "eventdata", {{tpl:EventdataEntries eventdata_type="eventdata"}}
- restriction de periode d'événement (pas) en cours, (pas) commencé, (pas) fini, {{tpl:EventdataEntries period="ongoing"}}
- restriction de date de début ou de fin stricte. {{tpl:EventdataEntries eventdata_start="2012-12-25 23:59:00"}}

A l'interieur de ce bloc, la majorité des balises et blocs de "Entries" sont valables.

"EventdataPagination"
Supporte les mêmes attribus que le bloc "Pagination"
Permet de faire la pagination en fonction des événements. (Restore le bon comptage)

"EntryEventdataDates"
Supporte de nombreux attribus.
Ce bloc liste les événements associés à un billet.

"EventdataDatesHeader"
Voir categoriesHeader.
Utilisée dans le contexte de la boucle "EntryEventdataDates", le contenu de cette balise s'affiche uniquement pour la première date de la boucle.

"EventdataDatesFooter"
idem ci-dessus

XII.3 Liste des valeurs:
------------------------

"EventdataPageTitle"
Supporte les attribus communs.
Si c'est une catégorie réordonnée alors EventdataPageTitle affichera le nom de la catégorie.

"EventdataPageDescription"
Supporte les attribus communs.
Si c'est une catéorie réordonnée alors EventdataPageDescription affichera la description de la catégorie.

"EventdataPageURL"
Supporte les attribus communs.
L'URL de la page public des événements. (S'utilise comme {{tpl:BlogURL}} )

"EventdataPageNav"
Supporte les attribus communs.
Menu de choix de période d'événement à afficher. (ex: Non débuté, En cours, etc...)
Un attribu supplémantaire est ajouté: "menus", il permet de limiter le menu à des choix prédéfinis parmis les suivants:
 'ongoing','outgoing','notstarted','started','notfinished','finished','all'. Par exemple pour limiter le menu à 2 choix 
il faut utiliser {{tpl:EventdataPageNav menus="notstarted,ongoing"}} ce qui donnera le menu suivant:
"<div id="eventdata_nav"><ul><li><a href="...">Non débuté</a></li><li><a href="...">En cours</a></li></ul></div>"
Si un tri est reconnu la balise "li" prendra la class "active".

"EventdataPeriod"
Supporte les attribus communs.
Affiche dans quel periode se trouve l'entrée courante. 
Par exemple si le billet en cours à un événement associé qui est terminé, la period sera "finished"
Un attribu suplémentaire est ajouté: "strict", si il est présent, une des valeurs "scheduled", "ongoing", "finished" sera retourné, 
cela peut servir pour les CSS par exemple.

"EventdataLocation"
Supporte les attribus communs.
Lieu de l'événement.

"EventdataDuration"
Supporte les attribus communs.
Durée de l'événement.

"EventdataStartDate"
Supporte les mêmes attribus que "EntryDate".
Date de début d'événement.

"EventdataStartTime"
Idem ci-dessus

"EventdataEndDate
idem ci-dessus

"EventdataEndTime"
idem ci-dessus

"EventdataFullDate"
Support les mêmes attribus que "EntryDate"
Ecrit la date complète d'un événement en utilisant la valeur de langue "From %S to %E"
Les attribus suplémentaires sont:
- "start_format": Pour formater la date de début, {{tpl:EventdataFullDate start_format="%A %d %m"}}
- "end_format" : pour formater la date de fin.


Les valeurs "EventdataLocation", "EventdataDuration", "EventdataStartDate", "EventdataStartTime", 
"EventdataEndDate", "EventdataEndTime", "EventdataFullDate" peuvent être utilisées 
soit sur la page "post.html", soit dans un bloc "EventdataEntries", soit dans un bloc "EntryEventdataDates".


XIII. Behaviors publiques:
==========================

Les modifications des pages publiques passent par des appelles aux behaviors à differents niveaux.

"publicBeforeDocument"
Inscrit dans le "core" le chemin vers le modèle de l'extension.
 
"publicHeadContent"
Ajoute au "head" du document le fichier css du modèle de l'extension.
 
"tplBeforeData"
Redirige les pages des catégories réordonné vers la page "events".
 
"publicEntryBeforeContent"
Si le modèle de l'extension possède un fichier "eventdataentrybeforecontent.html"
le contenu du block "body" de ce fichier sera ajouté au document. 
Cela sert à ajouter dans un billet la liste des événements liés au billet sans toucher aux thèmes. 
Cet apelle peut-être désactivé dans la page de gestion du modèle au cas ou on préfère utiliser le widget.
 
"publicEntryAfterContent"
Idem ci-dessus


XIV. Pour aller plus loin:
==========================

XIV.1 RSS2.0 et Atom:
---------------------

Des flux RSS2 et Atom sont disponibles pour les événements.
Ces flux se présentent sous le même forme que les autres flux de Dotclear avec en plus 
le support de modules event (http://web.resource.org/rss/1.0/modules/event/)
Attention la date de publication d'un événement ne change pas et reste celle du billet associé.

Les chemin vers ses flux/fichiers passent par l'url publique de la page "events",
la restriction des billets peut être faites à des catégories et/ou à des périodes d'événements.

Exemples:

"http://.../events/feed/rss2/all" 
Affiche le flux RSS2 de tous les événements.

"http://.../events/feed/rss2/category/Cinéma/scheduled" 
Affiche le flux RSS2 des événements à venir de la catégorie "cinéma".

XIV.2 iCalendar:
----------------

Des flux iCal sont disponibles pour les événements.
Les chemin vers ses flux/fichiers passent par l'url publique de la page "events",
la restriction des billets peut être faites à des catégories et/ou à des périodes d'événements.

Le chemin vers ces flux doit se terminer par l'extention ".ics".

Exemples:

"http://.../events/feed/all.ics" 
Affiche le flux ICS de tous les événements.

"http://.../events/feed/category/Cinéma/scheduled.ics" 
Affiche le flux ICS des événements à venir de la catégorie "cinéma".


XV. Remerciements:
==================

Je tiens à remiercier les personnes qui ont eu la patience de tester toutes les versions d'essais
et de donner un coup de main. (Surtout Tomek et jmh2o)
Je remercie également toute l'équipe de Dotclear (que ce soit le patron, le lab, la ml, dotaddict...)

-----------
End of file