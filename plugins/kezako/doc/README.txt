The Kezako plugin (french word meaning "what is that?") is a plugin that was
designed to fill two specific gaps in Dotclear 2 interface:
  * One could not describe tags. When a selection of articles is done based on a
keyword, such as [1]debian or [2]photo, one had only the keyword written at the
top with no further explanations.
  * One could describe a category, but only in one language. Adding a language
management could be done through some complicated work (which I did).

I implemented such a system, allowing to attach a description in one or more
languages to a specific tag or category (thus replacing the existing system for
categories). It can in fact be attached at anything (but nothing was done to
exploit it in a context other than a tag or a category). This is the Kezako
plugin.
  ___________________________________________________________________________

This system is flexible enough so that if nothing is specifically defined for
some tag or category, the old system is used instead (displaying the tag or the
category's description). There is no mandatory description for each and every
tag.

This extension for Dotclear 2 requires the modification of one page of the theme
used for the blog (it is now very easy with the theme editor included with
Dotclear 2). It is necessary in the tag.html page, where the description of the
tag should be, to surround the current presentation of the tag by
<tpl:TagDescription>...</tpl:TagDescription>. For example:
<tpl:TagDescription><h2>{{tpl:lang Keyword}} - {{tpl:MetaID}}</h2></tpl:TagDescr
iption>

This extension adds a table in the database. It was not tested with PostgresQL
(but I do not see why it should not work, I use only very simple operations).

The maintenance page for this plugin is located at this address:
[3]http://jean-christophe.dubacq.fr/post/kezako.

=== Installation ===

Use the zipped file attached to this post. Go then to the administration area,
and give the editor permission to the users allowed to attach descriptions to
tags or categories. The Kezako menu should appear in the administration
interface for these users. Do also the theme modification mentioned above. If
the table was not created, you probably did not go to the administration area.

There are two settings for this plugin: one that allows to override the category
description, and one that allows to use descriptions in several languages (and
not only in the blog's language). Since most blogs are monolingual and that in
this case, the category override is not useful, both of these settings are
disabled by default. For a full usage of this plugin, they can be enabled.
Remark that the language of your blog must be set if you use only the blog's
language.

I did not find any matching plugin elsewhere.

The current version of this plugin is 0.6 (local svn 366).

This plugin is licensed under [4]GPL version 2.0.

=== Usage ===

In the "Blog" section of the administration area, you access the two parameters
and the list of all known descriptions. You can modify or delete descriptions,
or insert a new one. When you insert or modify a description, you use the usual
visual editor of Dotclear. The identifier of the description for a tag is the
tag itself, the language is to be chosen from the menu (if you chose to use the
many languages option). Your description is completely up to you. Remark that
for tags, it will replace the former display (Keyword - tag), not be appended to
it.

If a description for a category is not found by Kezako in its own tables, the
description internal to Dotclear is used instead. Thus, Kezako has no visible
effect on a blog where the plugin is installed but unused.

=== To tell me about a bug or helping this plugin ===

The best way is to contact me [5]by mail (for a bug) or leave a comment (telling
me you tested this extension) at the maintenance page. In case of an update, I
will modify the maintenance page accordingly.

Note: this changelog is not complete, automatically generated and probably not
even informative before 2009.
  * Local SVN release 366 (jcdubacq,2009-04-30)
  + Fix licence blocks
  + Clean up code
  + New design for administration interface
  * Local SVN release 270 (jcdubacq,2008-12-22)
  + Add common filtering attributes
  * Local SVN release 251 (jcdubacq,2008-11-13)
  + Use categories descriptions by default, fix doc
  * Local SVN release 196 (jcdubacq,2008-07-19)
  + Reindent kezako plugin, clean, fix, and generally make better. Document it,
also.
  * Local SVN release 168 (jcdubacq,2008-05-06)
  + Order plugins and themes
  * Local SVN release 83 (jcdubacq,2008-02-28)
  + Add the editor permission support
  * Local SVN release 76 (jcdubacq,2008-02-27)
  + Initial creation of Kezako plugin

=== To do ===

  * Check that it works without the dctranslation framework (it should)
  * [DEL: Allow the disabling of categories to work for multiblog. :DEL]

References

   1. http://jean-christophe.dubacq.fr/tag/debian
   2. http://jean-christophe.dubacq.fr/tag/photo
   3. http://jean-christophe.dubacq.fr/post/kezako
   4. http://www.gnu.org/licenses/gpl-2.0.html
   5. http://jean-christophe.dubacq.fr/pages/Contact
