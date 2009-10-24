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

Voir la procédure d'installation des plugins Dotclear 2.
Pour information, le plugin eventdata crée la table "eventdata".


IV. Onlget "Administration":
============================

Cette page permet de gérer l'activation et l'utilisation de l'extension dans son ensemble sur le blog en cours.

IV.1 Générale:
--------------

"Activer le plugin"
Active ou désactive complètement l'utilisation du plugin.
 
"Icône de l'extension dans le menu Blog"
Place l'icône de l'extension soit dans la liste "Blog", soit dans la liste "Extensions".
 
"Activer la page public"
Ajoute une page côté public où seront affichés les événements.

IV.2 Permissions:
-----------------

Il est possible de modifier les permissions des utilisateurs pour accéder à certaines parties de ce plugin.
Les permissions sans rapport avec ce plugin ne sont pas affectées. Les actions possibles dépendent des permissions de l'utilisateur.

"Gestion des événements sur les billets"
Permet la gestion des dates d'événement depuis l'onglet "Billets" ou directement sur la page des billets.

"Gestion de la liste des catégories réordonnées"
Permet la gestion des catégories réordonnées ou pas.

"Gestion de la page publique"
Permet la gestion du titre, de la description et du choix du template pour la page publique de la liste des événements.

"Gestion du plugin"
Permet la gestion complète du plugin.


VI. Onglet "Billets":
=====================

Cette liste affiche les billets auxquels sont associées des dates d'événements.
Un billet peut apparaître plusieurs fois si plusieurs dates d'événement lui sont associées.

VI.1 Filtres:
-------------

Les filtres permettent de limiter et de trier la liste des billets affichés selon différents critères.

"Catégorie"
Filtrer les billets par catégorie.

"État"
- "en attente" : en attente de publication,
- "programmé" : billets mis en ligne aux date et heure indiquées dans le champ "Publié le",
- "non publié" : billets hors ligne,
- "publié" : billets en ligne.

"Sélectionné"
Aucun, billet marqué comme sélectionné ou non sélectionné.

"Trier par"
Permet de trier les résultats de filtrage selon la date de publication, la date de début de l'événement, 
la date de fin de l'événement, le lieu de l'événement, le titre, la catégorie, l'auteur, 
l'état de publication ou l'état de sélection.

"Trier"
Indique l'ordre dans lequel on souhaite effectuer le tri.

"Période"
- "Non débuté" : événements futurs,
- "Débuté" : événements commencés,
- "Terminé" : événements passés,
- "Non terminé" : événements passé ou en cours,
- "En cours" : événements commencés mais pas terminés,
- "Pas en cours" : événements pas commencés ou déjà terminés.

"Billets par page"
Nombre de billets à afficher par page de résultat.

VI.2 Actions par lot sur les billets :
--------------------------------------

Il est possible d'effectuer un ensemble d'actions sur plusieurs billets, d'un seul coup. 
Les actions possibles dépendent des permissions de l'utilisateur.
Ces actions sont principalement les mêmes que sur la page des billets avec en plus la possibilité de supprimer les événements. 
Cette option est également directement présente sur la page des billets.

- "Supprimer les événements : enlève TOUTES les dates d'événement de tous les billets sélectionnés ; les billets redeviennent des billets "normaux",
- "Publier" : mettre le billet en ligne,
- "Hors ligne" : mettre le billet hors ligne,
- "Programmer" : programmer le billet pour mise en ligne à la date de publication,
- "En attente" : en attente de publication,
- "Marquer comme sélectionné",
- "Marquer comme non sélectionné",
- "Changer de catégorie" : envoie sur la liste des catégories pour changer celle des billets sélectionnés,
- "Changer l'auteur" : permet de changer l'auteur du billet en indiquant l'identifiant de l'utilisateur qui deviendra le nouvel auteur,
- "Supprimer" : supprime le billet (cette opération est irréversible).

VI.3 Édition d'un événement:
----------------------------

Il est possible de modifier un événement pour un ou plusieurs billets.
La colonne "modifier" de la liste des billets propose deux choix :

"Modifier cet événement pour tous les billets"
Si plusieurs billets ont la même date de début, de fin et lieu d'événement,
la modification de l'événement sera prise en compte pour tous ces billets.
Une liste permet de voir les billets associés en dessous du formulaire d'édition.

"Modifier cet événement pour ce billet"
La modification sera pris en compte uniquement pour le billet sélectionné.
Les autres billets aillant le même événement ne seront pas affectés.
Une liste permet de voir le billet associé en dessous du formulaire d'édition.

"Effacer cet événement pour ce billet"
La suppression concernera uniquement le billet sélectionné.


VII. Onglet Catégories:
=======================

Cette liste affiche les catégories et permet de modifier l'ordre d'affichage des billets qui y sont associés coté public.
Elle permet d'interdire l'affichage d'événements appartenant à certaines catégories dans les widgets ou la page d'événements.


VII.1 Réordonné:
----------------

- "Normal" (marqué en rouge) : l'ordre des billets suit l'ordre par défaut du thème.
- "Réordonné" (marqué en vert) : les billets de la catégorie sont réordonnés suivant leur date de début d'événement et dans l'ordre décroissant.


VII.2 Caché:
------------

- "Normal" (marqué en vert) : la catégorie sera prise en compte partout coté public.
- "Caché" (marqué en rouge) : la catégorie ne sera pas prise en compte dans les widgets (sauf si spécifié),
  ni sur la page d'événements (sauf si c'est la page de la catégorie réordonnée).


VII.3 Actions par lot sur les catégories:
-----------------------------------------

Il est possible d'effectuer un ensemble d'actions sur plusieurs catégories, d'un seul coup.

- "Marquer comme réordonné" : réordonne l'affichage des billets par événement,
- "Marquer comme normal" : enlève l'ordre d'affichage des billets par événement,
- "Marquer comme caché" : les événements de cette catégories ne seront pas pris en compte sur la page générale des événements,
- "Marquer comme non caché" : la catégorie se comportera normalement.


VIII. Onlget "Modèles":
=======================

Cette page permet de gérer différents éléments de la page publique "eventdatas.html".


VIII.1 Description :
--------------------

Les deux champs suivants seront remplacés par les titre et description d'une catégorie 
lors de la redirection de celle-ci vers la page des événements.

"Titre"
Titre de la page publique des événements. {{tpl:EventdataPageTitle}}
 
"Description"
Description de la page publique des événements. {{tpl:EventdataPageDescription}}


VIII.2 Thèmes :
---------------

Des thèmes prédéfinis existent et la disponibilité du modèle dans un thème peut 
dépendre du super administrateur dans le cas d'un multiblog.

"Aide"
Des indications sont disponibles pour faciliter le choix du template de la page publique.
- "Thème du blog en cours" : nom du thème utilisé actuellement,
- "Adaptation existante" : indique si l'extension à un theme adapaté au theme en cours,
- "Page existante dans le thème du blog" : indique si le thème en cours est modifié pour l'extension,
- "Thème alternatif" : nom du thème de l'extension utilisé si celui du thème n'existe pas.

"Préfixe du lien"
Permet de changer le lien vers la page publique.

"Choix du template prédéfini pour la page publique"
Permet de choisir un thème particulier de l'extentsion si celui du thème en cours n'existe pas.

"Désactiver la liste des dates d'événement d'un billet"
Par défault certains modèles possèdent l'affichage automatique de la liste des dates d'événement sur un billet. 
Si vous préférez utiliser le widget (ou rien du tout) il suffit de désactiver cette option.


IX. Wigdets :
============

IX.1 Widget "Liste des événements" :
---------------------------------

Un widget en rapport avec les événements est disponible. 
Il permet de lister les événements à la manière du widget "Derniers billets" mais avec de multiples critères.

"Titre"
Titre du widget

"Catégorie"
Afficher seulement les événements d'une catégorie.

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
- "vide" : tout afficher,
- "Non débuté" : événements futurs,
- "Débuté" : événements commencés,
- "Terminé" : événements passés,
- "Non terminé" : événements passés ou en cours,
- "En cours" : événements commencés mais pas terminés,
- "Pas en cours" : événements pas commencés ou déjà terminés.

"Format des dates des billets"
La définition des caractères de formatage est identique à celle du blog.
Voir l'aide de la page Paramètres du blog.

"Format du texte des billets"
Le titre de chaque billet peut être formaté suivant des caractères particuliers:
- "%T" : titre du billet,
- "%C" : titre de la catégories,
- "%S" : date de début de l'événement,
- "%E" : date de fin de l'événement,
- "%D" : durée de l'événement,
- "%L" : lieu de l'événement.

"Format de surbrillance des billets"
Le texte affiché lors du passage de la souris peut être formaté suivant les même critères que le texte ci-dessus.

"Uniquement sur la page d'accueil"
Affiche le widget uniquement sur la page d'accueil


IX.2 Widget "Événements d'un billet" :
--------------------------------------

Ce widget affiche les dates d'événements associés à un billet. 
Il est uniquement présent sur la page d'un billet.
Il supporte plusieurs critères.

"Titre"
Titre du widget

"Format des dates des billets"
La définition des caractères de formatage est identique à celle du blog.
Voir l'aide de la page paramètres du blog.
 
"Format du texte des billets"
Le titre de chaque billet peut être formaté suivant des caractères particuliers  :
- "%S" : date de début de l'événement,
- "%E" : date de fin de l'événement,
- "%D" : durée de l'événement,
- "%L" : lieu de l'événement.


X. Modification de l'administration :
=====================================

Des options sont ajoutées sur certaines pages d'administration.


X.1 Nouveau billet (post.php):
------------------------------

Sur la page de création et de modification de billet, dans la barre latérale, des choix de date de début, de date de fin 
ainsi que de lieu d'événement sont ajoutés. 
Il suffit d'entrer une date de début et de fin pour associer un événement à un billet. Le lieu est facultatif.
Une listes des événemets déjà liés à un billet peut être présente.
Si le langage javascript est actif, il est possible de supprimer ou d'ajouter des événements sans enregistrer le billet, 
sinon il faut enregistrer le billet pour que les changements sur les événements soit pris en compte.
Un lien vers l'édition d'un événement est également présent pour chaque événement associé au billet.


X.2 Billets (posts.php):
------------------------

Sur la page de la liste des billets, dans la liste d'actions, des choix d'ajout ou de suppression de date d'événement par lot sont ajoutés. 
Pour l'ajout d'événement par lot tous les billets sélectionnés auront les mêmes dates et lieu d'événement.


X.3 Action sur les billets (posts_action.php):
----------------------------------------------

Permet les actions par paquet. (Ajout/suppression d'événement sur plusieurs billets.)


X.4 Extension Événements (plugin.php):
--------------------------------------

Bien sûr, une page spéciale pour la gestion de l'extension est présente soit dans le menu "Blog" soit dans le menu "Extensions".


XI. Modification des catégories:
================================

Les pages de catégories peuvent être réordonnées par date de début d'événement (géré dans l'onglet Catégories de l'extension).
Les catégories marquées comme réordonnées seront redirigées vers la page des événements restreints à la catégorie correspondante. 
L'extension utilise le behavior "tplBeforeData" pour rediriger la page.


XII. Page publique des événements :
===================================

Une page publique dédiée aux événements est disponible. 
L'CSS publique de cette page est modifiable et est par default "events".
Son thème ressemble à la page d'une catégorie. Des pages, des blocs et des valeurs ont été ajoutés :


XII.1 Liste des pages :
-----------------------

"events":
C'est la page principale affichant la liste des événements suivant différents critères qui sont les mêmes que pour les billets, 
avec la prise en compte de la pagination et de la période. Par exemple, si votre lien de page est <em>events</em> cela donne:
- http://.../events : affiche tous les événements,
- http://.../events/ongoing : affiche les événements en cours,
- http://.../events/scheduled : affiche les événements à venir,
- http://.../events/finished/page/2 : affiche la 2e page des événements terminés,
- http://.../events/category/MaCatégorie/ongoing/page/3 : affiche la 3e page des événements en cours de la catégorie "MaCatégorie"
- http://.../events/feed/rss2 : affiche le flux RSS des événements,
- http://.../events/feed/all.ics : affiche le flux ICS de tous les événements.
De nombreuses combinaisons d'URL sont possibles, à vous de les tester.

"eventstheme":
Ce n'est pas une page mais une redirection vers les fichiers de thème de l'extension, cela permet d'afficher des images, des CSS, etc.
Les URLs des images des fichiers CSS appelées depuis ce lien seront également réécrites.


XII.2 Liste des blocs :
-----------------------

"EventdataEntries"
Supporte les mêmes attribus que le bloc "Entries" avec en plus :
- tri des billets par début, fin, lieu d'événement, {{tpl:EventdataEntries sortby="start"}}
- restriction du type d'événement, par défault "eventdata", {{tpl:EventdataEntries eventdata_type="eventdata"}}
- restriction de période d'événement (pas) en cours, (pas) commencé, (pas) fini, {{tpl:EventdataEntries period="ongoing"}}
- restriction de date de début ou de fin stricte. {{tpl:EventdataEntries eventdata_start="2012-12-25 23:59:00"}}

À l'intérieur de ce bloc, la majorité des balises et blocs de "Entries" sont valables.

"EventdataPagination"
Supporte les mêmes attributs que le bloc "Pagination".
Permet de faire la pagination en fonction des événements. (Restaure le bon comptage)

"EntryEventdataDates"
Supporte de nombreux attributs.
Ce bloc liste les événements associés à un billet.

"EventdataDatesHeader"
Voir categoriesHeader.
Utilisée dans le contexte de la boucle "EntryEventdataDates", le contenu de cette balise s'affiche uniquement pour la première date de la boucle.

"EventdataDatesFooter"
Idem ci-dessus.


XII.3 Liste des valeurs :
-------------------------

"EventdataPageTitle"
Supporte les attributs communs.
Si c'est une catégorie réordonnée, alors EventdataPageTitle affichera le nom de la catégorie.

"EventdataPageDescription"
Supporte les attributs communs.
Si c'est une catéorie réordonnée, alors EventdataPageDescription affichera la description de la catégorie.

"EventdataPageURL"
Supporte les attribus communs.
L'URL de la page publique des événements. S'utilise comme {{tpl:BlogURL}}.

"EventdataPageNav"
Supporte les attributs communs.
Menu de choix de période d'événements à afficher (ex : Non débuté, En cours, etc.).
Un attribut supplémantaire est ajouté : "menus", il permet de limiter le menu à des choix prédéfinis parmi les suivants : 'ongoing','outgoing','notstarted','started','notfinished','finished','all'. Par exemple pour limiter le menu à 2 choix 
il faut utiliser {{tpl:EventdataPageNav menus="notstarted,ongoing"}} ce qui donnera le menu suivant :
"<div id="eventdata_nav"><ul><li><a href="...">Non débuté</a></li><li><a href="...">En cours</a></li></ul></div>"
Si un tri est reconnu, la balise "li" prendra la class "active".

"EventdataPeriod"
Supporte les attributs communs.
Affiche dans quelle période se trouve l'entrée courante. 
Par exemple si le billet en cours a un événement associé qui est terminé, la période sera "finished".
Un attribut supplémentaire est ajouté : "strict". S'il est présent, une des valeurs "scheduled", "ongoing", "finished" sera retournée, 
cela peut servir pour les CSS par exemple.

"EventdataLocation"
Supporte les attributs communs.
Lieu de l'événement.

"EventdataDuration"
Supporte les attributs communs.
Durée de l'événement.

"EventdataStartDate"
Supporte les mêmes attributs que "EntryDate".
Date de début d'événement.

"EventdataStartTime"
Idem ci-dessus.

"EventdataEndDate
Supporte les mêmes attributs que "EntryDate".
Date de fin d'événement.

"EventdataEndTime"
Idem ci-dessus.

"EventdataFullDate"
Supporte les mêmes attributs que "EntryDate"
Écrit la date complète d'un événement en utilisant la valeur de langue "From %S to %E"
Les attributs supplémentaires sont :
- "start_format" : pour formater la date de début, {{tpl:EventdataFullDate start_format="%A %d %m"}}
- "end_format" : pour formater la date de fin.

Les valeurs "EventdataLocation", "EventdataDuration", "EventdataStartDate", "EventdataStartTime", 
"EventdataEndDate", "EventdataEndTime", "EventdataFullDate" peuvent être utilisées 
soit sur la page "post.html", soit dans un bloc "EventdataEntries", soit dans un bloc "EntryEventdataDates".


XIII. Behaviors publics :
=========================

Les modifications des pages publiques passent par des appels aux behaviors à différents niveaux.

"publicBeforeDocument"
Inscrit dans le "core" le chemin vers le modèle de l'extension.
 
"publicHeadContent"
Ajoute au "head" du document le fichier CSS du modèle de l'extension.
 
"tplBeforeData"
Redirige les pages des catégories réordonnées vers la page "events".
 
"publicEntryBeforeContent"
Si le modèle de l'extension possède un fichier "eventdataentrybeforecontent.html"
le contenu du bloc "body" de ce fichier sera ajouté au document. 
Cela sert à ajouter dans un billet la liste des événements liés au billet sans toucher aux thèmes. 
Cet appel peut-être désactivé dans la page de gestion du modèle au cas où on préfère utiliser le widget.
 
"publicEntryAfterContent"
Idem ci-dessus.


XIV. Pour aller plus loin:
==========================

XIV.1 RSS2.0 et Atom:
---------------------

Des flux RSS2 et Atom sont disponibles pour les événements.
Ces flux se présentent sous la même forme que les autres flux de Dotclear avec en plus 
le support de modules event (http://web.resource.org/rss/1.0/modules/event/).
Attention la date de publication d'un événement ne change pas et reste celle du billet associé.

Les chemins vers ses flux/fichiers passent par l'URL publique de la page "events",
la restriction des billets peut être faite à des catégories et/ou à des périodes d'événements.

Exemples:

"http://.../events/feed/rss2/all" 
Affiche le flux RSS2 de tous les événements.

"http://.../events/feed/rss2/category/Cinéma/scheduled" 
Affiche le flux RSS2 des événements à venir de la catégorie "Cinéma".


XIV.2 iCalendar:
----------------

Des flux iCal sont disponibles pour les événements.
Les chemin vers ses flux/fichiers passent par l'URL publique de la page "events",
la restriction des billets peut être faite à des catégories et/ou à des périodes d'événements.

Le chemin vers ces flux doit se terminer par l'extension ".ics".

Exemples:

"http://.../events/feed/all.ics" 
Affiche le flux ICS de tous les événements.

"http://.../events/feed/category/Cinéma/scheduled.ics" 
Affiche le flux ICS des événements à venir de la catégorie "cinéma".


XV. Remerciements:
==================

Je tiens à remercier les personnes qui ont eu la patience de tester toutes les versions d'essai
et de donner un coup de main (surtout Tomek et jmh2o).
Je remercie également toute l'équipe de Dotclear (que ce soit le patron, le lab, la ml, DotAddict...)

-----------
End of file