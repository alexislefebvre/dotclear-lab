Last Comments Widget for Dotclear 2
===============================================================================
J'ai r�alis� ou plut�t je devrais dire d�riv� ce plugin du plugin LastComments de 
Vincent Simonin au d�part le plugin initial permet d'afficher les derniers commentaires 
ost�s sur votre blog. 

Cette version de plugin sert a afficher la liste de billet de 1 a x de votre blog avec un 
lien vers le billet concern�.

C'est tellement d�riv� que j'ai presque fait que du copier coller remplac� du plugin original 
en rempla�ant le terme "comments" par "posts"

Installation
------------

Placez le dossier "lastestPosts" de cette archive dans le dossier "plugins" du r�pertoire de Dotclear 2.
Rendez vous dans l'interface d'administration ou vous y trouverez le nouveau widget et placez le ou bon vous semble :)

Utilisation du widget
---------------------

Vous pouvez afficher le nombre de billet ( ou post ) que vous d�sirez en jouant sur la valeur de "limit" 
ainsi que le nombre param�trable de caract�res extraits du titre du billet. Si vous ne laissez aucune valeur pour
"limit", 1O billets seront affich�s, la valeur par d�faut. Vous pouvez aussi choisir de 
s�parer les billets par le nom de leurs cat�gories d'appartenance.

Depuis la version 1.4 la possibilit� d'inclure la liste des billets prot�g�s.

Voil�.

Utilisation dans les gabarits
-----------------------------

Si vous d�sirez utiliser le plugins directement dans vos gabarits, utiliser cette syntaxe :

{{tpl:lastestPosts limit="(integer)" nb_letter="(integer)" categ_show="boolean" protect_show="boolean"}}

note : remplacez @@(integer)@@ par un nombre, remplacez @@(categ_show)@@ par true ou false.


l'attribut "limit" repr�sente le nombre de billet � afficher et "nb_letter" le nombre de caract�re a conserver,
protect_show si il faut afficher les billets prot�g�s

Exemple : 

{{tpl:lastestPosts limit="5" nb_letter="60" categ_show=false protect_show=true}}

affichera les 60 premiers caract�res des titres de vos 5 derniers billets y compris ceux qui ont un mot de passe.

On peut aussi faire ceci :

{{tpl:lastestPosts}}

qui affichera le titre de vos 10 derniers billets.

Evolution future
----------------
Je compte maintenant grace au travail de Vincent Simonin continuer cette approche pour en arriver quasiment au plugin 
TOC (Table des Mati�res).