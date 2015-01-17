# README

## QU'EST CE QUE DISCLAIMER ?

"disclaimer" est un plugin pour l'outil open source de 
publication web nommé Dotclear.

Ce plugin permet d'ajouter un texte d'avertissement 
pour le visiteur avant son entrée sur le blog.

Le code de ce plugin est largement inspiré du plugin
"Private mode" d'Osku.

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

http://forum.dotclear.net

### Note

Certains thèmes ne sont pas compatibles avec ce plugin.

## USAGE

### Paramètres

La configuration du plugin est situé dans
la rubrique "Disclaimer" de la page des paramètres du blog.

#### Activer le plugin

Permet d'activer ou non la page d'avertissement.

#### Se souvenir de l'utilisateur

Permet d'envoyer un cookie au visiteur pour qu'il n'ait pas
à revalider l'avertissement lors d'une visite ultérieure.

#### Titre

C'est le titre principale de la page d'avertissement.

#### Lien de sortie

Lien vers lequel sera renvoyé le visiteur s'il refuse les termes.

#### Avertissement

Texte principal de la page d'avertissement, cette page accepte le code html.
(sauf si l'attribut encode_html est actif dans les templates)

#### Liste des robots autorisés à indexer les pages du site

Liste des robots d'indexation séparés par un point-virgule.
Cela permet au robot utilisant ce user-agent de ne pas être bloqué par
le disclaimer.

#### Désactiver l'autorisation d'indexation par les moteurs de recherches

Permet de désactiver la fonction de recherche de user-agent et de rediriger
tous les user-agent vers le disclaimer.

### Templates

Le fichier de template par default pour la page d'avertissement 
se situe dans le repertoire "/default-template/mustek/disclaimer.html" ou
"/default-template/currywurst/disclaimer.html" du plugin.

Il faut le copier dans le repertoire /tpl du thème pour le modifier.


### Balises

Le plugin ajoute les balises de template suivantes :

#### DisclaimerTitle

Titre de l'avertissement.

#### DisclaimerText

Texte de l'avertissement.

#### DisclaimerFormURL

A mettre dans l'attribut "action" de la balise "form".

Ces balises supportent les attributs communs.


---
Cordialement et en français dans le texte,
Jean-Christian Denis