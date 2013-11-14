# README

## QU'EST CE QUE DISCLAIMER ?

"disclaimer" est un plugin pour l'outil open source de 
publication web nomm� Dotclear.

Ce plugin permet d'ajouter un texte d'avertissement 
pour le visiteur avant son entr�e sur le blog.

Le code de cette extension est largement inspir� de 
l'extension "Private mode" d'Osku.

## LICENCE

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

## SUPPORT

http://jcd.lv/q=disclaimer
http://forum.dotclear.net

### Note

Certains th�mes ne sont pas compatibles avec ce plugin.

## USAGE

### Param�tres

La configuration de l'extension est situ� dans 
la rubrique "Avertissement" de la page des param�tres du blog.

#### Activer l'extension

Permet d'activer ou non la page d'avertissement.

#### Se souvenir de l'utilisateur

Permet d'envoyer un cookie au visiteur pour qu'il n'est pas 
a revalider l'avertissement lors de sa prochaine visite.

#### Titre

C'est le titre principale de la page d'avertissement.

#### Lien de sortie

Lien vers lequel sera renvoy� le visiteur si il refuse les termes.

#### Avertissement

Texte principale de la page d'avertissement, cette page accepte le code html.
(sauf si l'attribue encode_html est actif dans les templates)

#### Liste des robots autoris�s � indexer les pages du site

Liste s�par�s par un point-virgule des robots d'indexation.
Cela permet au robot utilisant ce user-agent de ne pas �tre bloquer par
le disclaimer.

#### D�sactiver l'autorisation d'indexation par les moteurs de recherches

Permet de d�sactiver la fonction de recherche de user-agent et de rediriger
tout les user-agent vers le disclaimer.

### Templates

Le fichier de template par default pour la page d'avertissement 
se situe dans le repertoire "/default-template/disclaimer.html" de l'extension.

Il peut �tre copi� dans le repertoire /tpl d'un th�me pour �tre modifi�.

Pour modifier les CSS un fichier disclaimer.css est �galement disponible.
Il est accesible et modifiable de la m�me mani�re que le fichier html.


### Balises

L'extension ajoute les baslises de template suivantes:

#### DisclaimerTitle

Titre de l'avertissement.

#### DisclaimerText

Texte de l'avertissement.

#### DisclaimerFormURL

A mettre dans l'attribue "action" de la balise "form".

Ces balises supportent les attribus communs.


---
Cordialement et en fran�ais dans le texte,
Jean-Christian Denis