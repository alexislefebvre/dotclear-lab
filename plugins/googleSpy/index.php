<?php
# ***** BEGIN LICENSE BLOCK *****
# Widget SnapMe for DotClear.
# Copyright (c) 2007 Ludovic Toinel, All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

if (!defined('DC_CONTEXT_ADMIN')) { exit; }

?>

<html>
<head>
  <title>GoogleSpy</title>
  <?php echo dcPage::jsPageTabs(); ?>
</head>
  
<body>
<h2>GoogleSpy</h2>

<div class="multi-part" title="Installation">

<p>Après avoir installé ce plugin, éditez le fichier &quot;post.html&quot; de votre thème Dotclear2 et insérez le tag :</p>

<code class="xml">{{tpl:googleSpyPurposePosts num_links=&quot;6&quot; num_keywords=&quot;3&quot; title=&quot;A lire : &quot; description=&quot;ce que vous voulez&quot;}}</code><br/><br/>

<p>En dessous de :</p>

<code class="xml"><span style="color: #009900;"><span style="font-weight: bold; color: black;">&lt;div</span> <span style="color: #000066;">class</span>=<span style="color: #ff0000;">&quot;post-content&quot;</span><span style="font-weight: bold; color: black;">&gt;</span></span>{{tpl:EntryContent}}<span style="color: #009900;"><span style="font-weight: bold; color: black;">&lt;/div<span style="font-weight: bold; color: black;">&gt;</span></span></span></code><br/><br/>

<p>Liste des param&egrave;tres : </p>

<ul>
<li><strong>num_links</strong> = Le nombre billets à rechercher dans la base.</li>
<li><strong>num_keywords</strong> = Les 'n' premiers mots clefs google à utiliser dans la recherche d'article, plus ce chiffre est grand plus le temps d'affichage de la page peut devenir important.</li>

<li><strong>title</strong> = Le titre à positionner au dessus de la liste de liens.</li>
<li><strong>description</strong> = Le texte qui s'affichera en dessous du titre (title).</li>
</ul>

</div>

<div class="multi-part" title="A propos">
<p>
Plugin GoogleSpy v0.7 par Ludovic Toinel
<br /><br /><a href="http://www.geeek.org/post/2007/09/02/Plugin-Dotclear2-%3A-GoogleSpy-v01">Site officiel</a>
</p>
</div>

</body>
</html>
