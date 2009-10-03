This plugin adds a widget for navigating topically from one post to another in
the same blog; for each tag, each category, each language, it presents links to
the previous and next article that shares the same tag, category or language.
  ___________________________________________________________________________

The maintenance page for this plugin is located at this address:
[1]http://jean-christophe.dubacq.fr/post/prevnext.

=== Installation ===

Use the zipped file attached to the maintenance page. The new widget appears in
the default widgets and is available in the widget area Blog > Presentation
widgets.

The parameters are simple: title of the widget (leave empty for no title) and
the symbols used on each line for the next or preceding post of the same nature.

This plugin is compatible with the [2]dctranslations plugin and lists the
available translations for the current post (when dctranslations is installed).

The [3]infoEntry has some common functionality (and others) but only for
categories (neither tags nor language). Other older plugins also covered this
area for Dotclear 2β6 ([4]SameCat).

The current version of this plugin is 1.2.3 (local svn 402).

This plugin is licensed under [5]GPL version 2.0.

=== Customisation ===

There are some pending projects about that, but it is currently very poor. The
widget allows to have custom left- and right-pointing symbols, and there are the
CSS rules that I use:
#prevnext ul {
    text-align: center;
    margin: 0 1ex;
}
#prevnext li {
    display: block;
    background: none;
    padding-left: 0;
    padding-right: 0;
}
#prevnext li.tags-sep +li {
    border-top: 1px solid;
    padding-top: 6px;
    display: block;
    background: none;
}

=== To tell me about a bug or helping this plugin ===

The best way is to contact me [6]by mail (for a bug) or leave a comment (telling
me you tested this extension) at the maintenance page. In case of an update, I
will modify the maintenance page accordingly.

Note: this changelog is not complete, automatically generated and probably not
even informative before 2009.
  * Local SVN release 402 (jcdubacq,2009-10-03)
  + Fix licence blocks
  * Local SVN release 395 (jcdubacq,2009-10-03)
  + Update for DC 2.1.6: delete references in declarations
  * Local SVN release 360 (jcdubacq,2009-04-27)
  + Simplify plugin, refactor code, remove dead code
  + Fix licence block
  + Remove static templates
  + Add documentation
  + Update translations
  + Add icon 16x16
  * Local SVN release 168 (jcdubacq,2008-05-06)
  + Order plugins and themes
  * Local SVN release 62 (jcdubacq,2008-02-22)
  + Evacuate everything not specific to prevnext
  + Adapt internals, switch to static
  + Create prevnext widget
  * Local SVN release 43 (jcdubacq,2008-02-15)
  + Adding all other personal plugins

=== To do ===

  * Allow customisation of the displayed areas of the widget
(id/date/category/language/tags, currently only category/language/tags is
possible).
  * Chain-display for multi-level categories.
  * Allow custom CSS.

References

   1. http://jean-christophe.dubacq.fr/post/prevnext
   2. http://jean-christophe.dubacq.fr/post/dctranslations
   3. http://lab.dotclear.org/wiki/plugin/infoEntry/fr
   4. http://aiguebrun.adjaya.info/post/070216/Plugin-sameCat-pour-dotclear-2?tag=dotclear_2
   5. http://www.gnu.org/licenses/gpl-2.0.html
   6. http://jean-christophe.dubacq.fr/pages/Contact
