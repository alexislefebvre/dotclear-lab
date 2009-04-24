The presentation of this plugin is short: it is only useful if you use the
[1]dctranslations plugin. It is a very slightly modified version of the standard
plugin widgets that can take into account language changes.
  ___________________________________________________________________________

The maintenance page for this plugin is located at this address:
[2]http://jean-christophe.dubacq.fr/post/translatedwidgets.

The current version of this plugin is 1.6.2 (local svn 354).

This plugin is licensed under [3]GPL version 2.0.

This extention has no other purpose than to work with [4]dctranslations. It is a
set of very small modifications (for example, all tags should be translated on
display, titles of posts should be translated in the best-of collection, etc.).
It completely replaces the widgets standard plugin.

No technical details, since it is merely a barely modified copy. The main
difference is that widgets titles are translated on display, not when setting
the widgets. Thus, it is possible to display the English title for English
readers, the Spanish title for Spanish readers, etc.

=== Installation ===

Use the zipped file attached to the maintenance page. It is very important to
deactivate the standard plugin widgets. This can be done just before
installation of this plugin, with the System > Extensions > widgets > Deactivate
menu. There is no need to delete the extension.

A new widget is installed: to avoid interference with the default-bundled plugin
metadata, a widget doing the same work but with tags translation is also
delivered.

After installing this plugin for the first time, please reinstall your widgets
one by one. The internal id of the translated tags widget is mtags, in case your
theme calls upon this widget through the function {{tpl:Widget}}.

In the widgets administration area, the only difference is that the registered
title for the widget (which will be translated for display) is shown between
parentheses. You can enter the new title in your language or in English.

=== To tell me about a bug or helping this plugin ===

The best way is to contact me [5]by mail (for a bug) or leave a comment (telling
me you tested this extension) at the maintenance page. In case of an update, I
will modify the maintenance page accordingly.

Note: this changelog is not complete, automatically generated and probably not
even informative before 2009.
  * Local SVN release 354 (jcdubacq,2009-04-24)
  + Remove blogroll widget
  * Local SVN release 351 (jcdubacq,2009-04-24)
  + Fix licence block
  + New system for getting titles in reader's language
  + General overhaul of this plugin, lighter modifications wrt original code
  * Local SVN release 277 (jcdubacq,2008-12-26)
  + Add stub doc directory to translatedwidgets (need rehaul)
  * Local SVN release 231 (jcdubacq,2008-11-05)
  + Update to Dotclear 2.1
  * Local SVN release 168 (jcdubacq,2008-05-06)
  + Order plugins and themes
  * Local SVN release 43 (jcdubacq,2008-02-15)
  + Adding all other personal plugins

References

   1. http://jean-christophe.dubacq.fr/post/dctranslations
   2. http://jean-christophe.dubacq.fr/post/translatedwidgets
   3. http://www.gnu.org/licenses/gpl-2.0.html
   4. http://jean-christophe.dubacq.fr/post/dctranslations
   5. http://jean-christophe.dubacq.fr/pages/Contact
