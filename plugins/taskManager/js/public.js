/*
# -- BEGIN LICENSE BLOCK -------------------------
# This file is part of taskManager, a plugin for Dotclear.
#
# Copyright (c) 2009 Louis Cherel
# cherel.louis@gmail.com
#
# Licensed under the GPL version 3.0 license.
# See COPIRIGHT file or
# http://www.gnu.org/licenses/gpl-3.0.txt
# -- END LICENCE BlOC -----------------------------
*/
var i=false;
function montre(texte){if(i==false){$("#curseur").css("visibility","visible");$("#curseur").text(texte);i=true;}}
function cache(){if(i==true){$("#curseur").text("");i=false;}}
function showObj(id){$("#object"+id).show("fast");$("#img"+id).hide(0);$("#img2"+id).show(0);}
function hideObj(id){$("#object"+id).hide("fast");$("#img"+id).show(0);$("#img2"+id).hide(0);}
function showTaskName(taskname,id){$("#taskbar"+id).text(taskname);}
function hideTaskName(taskname,id){$("#taskbar"+id).text(taskname+"...");}
function progressBar(progress,n){var m = n+1;$("#c"+m).text(parseInt(progress)+"%");$("#b"+m).css("width",parseInt(progress)+"px");}