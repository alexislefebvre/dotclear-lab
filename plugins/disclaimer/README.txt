disclaimer 0.1 (2009-07-13) a plugin for Dotclear 2


I. Licence:
===========

This file is part of disclaimer, a plugin for Dotclear 2.
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

http://dotclear.jcdenis.com/
http://forum.dotclear.net

III. About:
===========

Ce plugin permet d'ajouter un texte d'avertissement 
pour le visiteur avant son entrée sur le blog.

Le code de cette extension est largement inspiré de 
l'extension "Private mode" d'Osku.


IV. Settings:
=============

La configuration de l'extension est situé dans 
la rubrique "Avertissement" de la page des paramètres du blog.

1) Activer l'extension:
-----------------------
Permet d'activer ou non la page d'avertissement.

2) Se souvenir de l'utilisateur:
--------------------------------
Permet d'envoyer un cookie au visiteur pour qu'il n'est pas 
a revalider l'avertissement lors de sa prochaine visite.

3) Titre:
---------
C'est le titre principale de la page d'avertissement.

4) Lien de sortie:
------------------
Lien vers lequel sera renvoyé le visiteur si il refuse les termes.

5) Avertissement:
-----------------
Texte principale de la page d'avertissement, cette page accepte le code html.
(sauf si l'attribue encode_html est actif dans les templates)

6) Liste des robots autorisés à indexer les pages du site
Liste séparés par un point-virgule des robots d'indexation.
Cela permet au robot utilisant ce user-agent de ne pas être bloquer par
le disclaimer.

7) Désactiver l'autorisation d'indexation par les moteurs de recherches
Permet de désactiver la fonction de recherche de user-agent et de rediriger
tout les user-agent vers le disclaimer.

V. Templates:
=============

Le fichier de template par default pour la page d'avertissement 
se situe dans le repertoire "/default-template/disclaimer.html" de l'extension.

Il peut être copié dans le repertoire /tpl d'un thème pour être modifié.

Pour modifier les CSS un fichier disclaimer.css est également disponible.
Il est accesible et modifiable de la même manière que le fichier html.


VI. Balises:
============

L'extension ajoute les baslises de template suivantes:

1) DisclaimerTitle:
-------------------
Titre de l'avertissement.

2) DisclaimerText:
------------------
Texte de l'avertissement.

3) DisclaimerFormURL:
---------------------
A mettre dans l'attribue "action" de la balise "form".

Ces balises supportent les attribus communs.

--------
JC Denis