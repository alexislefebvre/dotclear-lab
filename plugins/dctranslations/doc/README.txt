If you want a blog in one language, Dotclear is totally adequate. If you just
want to mix in several languages, no special effort, no translation, Dotclear is
also fit. If you want to blog in two languages, everything translated, you can
also use the multiblog capabilities of Dotclear and simply make two different
blogs.

But, if you want to make some real bilingual content (incrementally or all at
once), linking articles in several languages between them, allowing bilingual
readers to jump from one language to the other, while displaying a maximum of
elements in each visitor's language (the navigation indications, and if an
article exists in several languages, the one preferred by the reader), then,
this plugin dctranslations is made for you to build your multilingual site.
  ___________________________________________________________________________

The maintenance page for this plugin is located at this address:
[1]http://jean-christophe.dubacq.fr/post/dctranslations.

=== Installation ===

Use the zipped file attached to the maintenance page. The administration area is
in Blog > Translations. The language of the blog must be put to a blank value
(it is done automatically when an administrator visits the blog parameters' page
and saves the results; it is important to also visit the Translations parameters
page to set the acceptable languages and the fallback language). We insist: the
plugin will not function correctly as long as the language is not the empty
string (one can check in Extensions > about:config).

This plugin adds a settings panel in the administration interface to choose the
languages available for the readers that will browse the site, an interface to
translate posts, static pages and another one to translate small bits of text
displayed in the public interface. The translation of the various widgets or
other things such as the description of categories is left to other extensions
(see below).

This plugin requires the use of the [2]stacker plugin, at least version 0.4.

This plugin redefines internal standard codes of Dotclear, it is thus
recommended after install to do Extensions -> Maintenance -> Empty templates
cache.

The autolocale plugin plays a similar role to this plugin for the change of the
navigation language according to the reader's preferences, but no finer control
is allowed (and no post translation is possible). For a complete multilingual
system, it is recommended that you also install the last version of the
[3]translatedwidgets plugin, as well as the [4]kezako plugin.

The current version of this plugin is 1.9.1 (local svn 374).

This plugin is licensed under [5]GPL version 2.0.

=== Usage ===

--- Simple user ---

When you browse a blog with this extension, Dotclear will automatically pick up
your preferred language (either the last one you chose manually, or the one set
in your browser preferences). If no suitable language is found, a fallback
language was chosen by the blog's author.

You normally can setup your browser so that your preferred languages are sent in
the right order. This page of the W3C organisation points out in which menu
(according to your browser) this setup can take place:
[6]http://www.w3.org/International/questions/qa-lang-priorities.

When a post (or a page) is available in several languages, the preferred
language is presented to you. A widget (which should usually be added to the
site by the author) allows to choose another translation (if a reader
understanding several languages wishes to see another version).

--- Simple author ---

When you are composing (or editing) a page or a post, a new area appears below
the usual edit area (to make a new translation) each time you click on the "New
translation" button, as well as a new area for each already entered translation.
Each area bears five entry fields: title, language, excerpt, content, URL (the
significance of each of these fields is the same as the corresponding fields for
the original language). The entry area can be partially masked, leaving seen
only the language and the title.

To delete an already-entered translation, check the dedicated check box beside
the translation entry. The translation will not be deleted as long as the page
has not been saved.

The URL field will be filled automatically from the title, unless it is entered
manually. The URL of a post is:
  * ...nomdusite/tpost/lang/url-of-the-translation (translated post)
  * ...nomdusite/opost/lang/url-of-the-post (original language)
  * ...nomdusite/post/url-of-the-post (uses automatically the preferred language
of the reader)

--- (Site-level) Translator ---

Because nothing is really perfect, it is sometimes necessary to enter manually
some key elements for the translation. For example, the translation of the tags,
the widget titles (when it is not the default title of those widgets), etc. need
to be translated manually. The preferences panel (Blog > Translations) allows
users with the editor role to enter translations which, added to the built-in
translations of Dotclear, help to translate all the navigation elements.

The administration panel is in two parts, one for the multilingual settings, and
another one for the translation elements. The blog settings is made in two very
simple parts: a value for the list of allowed languages (available for the
navigation). The second value is the fallback language, if ever no preferred
language of the reader is an allowed language. This value must be part of the
allowed languages, of course. The default values are "en,fr" (English and
French, the best supported languages in Dotclear) and "en" (English).

The second part displays the translation elements. To translate an element, it
is sufficient that the expression in the first column ("String"), and that the
corresponding translation be put in the corresponding column.

For example, if the tag "calendar" does exist, I can add the french translation
"agenda" in the "Français" column, and the german translation in the "Deutsch"
column. I leave the english column empty, since there is nothing to translate. I
could have chosen to use the tag "agenda" and translate it in English and German
by "calendar" and "Kalendar".

To add a new entry, there is always an empty line in page 1 (the translations
are displayed 16 by 16). A small menu allows to display individual lines for
every category title and every tag (the complete list of untranslated terms is
displayed below).

To delete a translation, the first column "String" has to be left intact, and
all the translations have to be deleted. A better interface is in the works.

L'effet est normalement visible immédiatement en zone publique.

--- Administrator ---

As explained above, all necessary extensions must be installed (also the ones
that are recommended), that the blog's language is set to the empty string. The
widgets must also be replaced by their equivalents (the translated ones from the
translatedwidgets plugin). The "editor" permission must be bestowed upon the
users that are allowed to take care of the interface translation (not the posts,
but the tags and other small things). The blog administrators do not require
this permission.

Three widgets are available and can be added to the widgets sets. The first one,
active only on posts or static pages, lists all the existing translations of the
post.

The second one, "Navigation language", allows the reader of the blog to choose a
language for navigation (it supersedes the preferred languages list) without
modification of the preferences of the browser. However, it may be necessary
sometimes to reload previously-read pages overriding the browser's cache, most
often by using shift-F5 or quitting the browser and relaunching it.

The third widget allows to replace the original language widget (still called
"Languages of the blog") by a similar one that can select articles not only if
their original language is the one selected, but all articles that have been
translated (or whose original language is) in some language. This new widget is
called "Only show posts in ...". The other one is kept for historical reasons.

=== To tell me about a bug or helping this plugin ===

The best way is to contact me [7]by mail (for a bug) or leave a comment (telling
me you tested this extension) at the maintenance page. In case of an update, I
will modify the maintenance page accordingly.

Note: this changelog is not complete, automatically generated and probably not
even informative before 2009.
  * Local SVN release 374 (jcdubacq,2009-04-30)
  + Update translations
  * Local SVN release 372 (jcdubacq,2009-04-30)
  + Tabs in administration area
  + List of updated words
  + Clean up code
  + New screenshots
  * Local SVN release 364 (jcdubacq,2009-04-28)
  + Allow deleting a translation in a more natural way
  + Allow several translations in one edition cycle
  * Local SVN release 358 (jcdubacq,2009-04-25)
  + Remove unnecessary tweaks for translatedwidgets
  * Local SVN release 343 (jcdubacq,2009-04-22)
  + Reindent code
  + Change licence to GPL version 2.0
  * Local SVN release 322 (jcdubacq,2009-02-07)
  + Translate blog name and description also
  * Local SVN release 305 (jcdubacq,2009-01-27)
  + Fix (mostly german) locales
  * Local SVN release 299 (jcdubacq,2009-01-26)
  + Adapt to version 0.3 of stacker (behavior initStacker)
  * Local SVN release 278 (jcdubacq,2009-01-01)
  + Finalize documentation, make new release utility, release
  * Local SVN release 275 (jcdubacq,2008-12-26)
  + Reinstate version keyword
  * Local SVN release 274 (jcdubacq,2008-12-26)
  + Delete svn counter
  * Local SVN release 273 (jcdubacq,2008-12-26)
  + Fix tag feed, add SVN revision to number
  * Local SVN release 260 (jcdubacq,2008-11-18)
  + Merge supportedlang and dctranslations
  * Local SVN release 242 (jcdubacq,2008-11-11)
  + Reformatting
  * Local SVN release 168 (jcdubacq,2008-05-06)
  + Order plugins and themes
  * Local SVN release 43 (jcdubacq,2008-02-15)
  + Adding all other personal plugins

=== To do ===

  * [DEL: Allow deleting a translation in a more natural way. :DEL]
  * [DEL: Allow several translations in one edition cycle. :DEL]
  * translatedwidget: make the Text plugin care for several languages.

References

   1. http://jean-christophe.dubacq.fr/post/dctranslations
   2. http://jean-christophe.dubacq.fr/post/stacker
   3. http://jean-christophe.dubacq.fr/post/translatedwidgets
   4. http://jean-christophe.dubacq.fr/post/kezako
   5. http://www.gnu.org/licenses/gpl-2.0.html
   6. http://www.w3.org/International/questions/qa-lang-priorities
   7. http://jean-christophe.dubacq.fr/pages/Contact
