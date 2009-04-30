The stacker plugin is an extension that fulfills one simple role in Dotclear 2:
when the display of the posts has to be adapted according to the time and
circumstances of the reading (if not, the post can be modified at writing time),
two internal functions must be redefined. If two plugins are trying to do the
same thing... you are out of luck. Unless you know exactly which plugin, exactly
how each plugin does its stuff, etc. The stacker plugin allows these extensions
to declare themselves and collaborate. They are called in a specific order. Here
are some things I thought it could be useful for:
  * a list of specific words to be linked to some kind of definition (the list
can be enriched after compositing the original post)
  * switch chunk of texts for equivalent pictures (smilies, for example; managed
already by Dotclear; but also interpretation of mathematical formulae) :-)
  * transform radically the whole text of a post (translation)
  * change text chunks for others (censorship, abbreviations)
  ___________________________________________________________________________

The maintenance page for this plugin is located at this address:
[1]http://jean-christophe.dubacq.fr/post/stacker.

=== Installation ===

Use the zipped file attached to the maintenance page. The administration area is
in its own tab of System > Extensions. It is possible to use it to check/uncheck
the activation of a specific text transformer (the deactivated transformers are
on a grey background).

I did not find plugins matching the capabilities of this one. The [2]LaTeXrender
and [3]dctranslations plugins (written by me) use it.

The current version of this plugin is 0.4.1 (local svn 376).

This plugin is licensed under [4]GPL version 2.0.

=== Usage ===

--- Basic usage ---

In the System > Extensions menu of the administration area, you can access the
list of all registered text transformers, in the order in which they are
applied. The deactivated text transformers are on a grey background. They can be
deactivated by simply clicking the checkbox in front of the line and clicking
"modify". By default, nothing should be done (but deactivating the test
transformer, that replaces all instances of Dotclear in the text by Dotcleår).

--- Developer ---

If you have an extension that could use stacker, you need to register (declare)
your text transformer. For this, create a file _prepend.php containing:
$core->addBehavior('initStacker',array('tplStacker','initStacker'));
class tplStacker {
    public static function initStacker(&$core) {
        $core->stacker->addFilter('TestStackerFilter',
                                  'tplStacker',  // Class
                                  'SwedishA',    // Function
                                  'textonly',    // Context
                                  100,           // Priority
                                  'stacker',     // Origin
                                  __('Test replacing Dotclear with Dotcleår'),
                                  '/Dotclear/'   // Trigger
                                  );
    }
}

(if you have several transformers, repeat the addFilter).

The first argument is the name of the transformer, the second and the third are
respectively the class and the function name that has to be called for the
transformation (the callback function). The fourth, called the context, must
take one of these four values: any, excerpt, content ou textonly. A context of
excerpt calls the callback if the content of an excerpt is to be generated, a
context of content calls the callback if the main part of the post is to be
generated, a context of any calls the callback in both cases. Finally, a context
of textonly splits the content (of excerpts and main contents alike) in small
chunks of text (only the text nodes of the XHTML document, no markup), i.e. it
splits them in between all markup, and passes each chunk to the callback.

The fifth position must contain the name of the extension (your plugin)
providing/requesting the transformation, the sixth position is an explanatory
text about what the transformer does. This string must be translated using the
usual mechanisms.

Last, but not least, the seventh argument is useful only if the context is
textonly. It can be set to null if the context is other. It is a regular
expression that should match if the callback is to be called on some chunk. It
should be fast and simple (the callback can perform all sorts of complicated
tests if needed).

The prototype of the callback function if the first three cases must be:
public static function modifyContent(&$rs,$text,$absolute_urls=false)

and for the fourth case:
public static function SwedishA(&$rs,$text,$stack,$elements)

In all those cases, the callback function must return the modified content (as a
string). $rs is the representation of the data containing the whole article.

In the fourth case, $stack is an array containing the list of all markup
including the given chunk of text (e.g. div > ul > li > a > span), and $elements
an array counting the elements of $stack of a given type (e.g.
$elements['span']=1). This is useful, for example, so as not to turn the text
chunks inside a link into another link (if $elements['a']>0, return the original
value).

=== To tell me about a bug or helping this plugin ===

The best way is to contact me [5]by mail (for a bug) or leave a comment (telling
me you tested this extension) at the maintenance page. In case of an update, I
will modify the maintenance page accordingly.

Note: this changelog is not complete, automatically generated and probably not
even informative before 2009.
  * Local SVN release 376 (jcdubacq,2009-04-30)
  + Fix licence blocks
  + Clean up code
  + After modification, return to the right tab
  * Local SVN release 302 (jcdubacq,2009-01-27)
  + Fix locales
  * Local SVN release 295 (jcdubacq,2009-01-26)
  + Add a behavior to init stacker
  + Fix examples
  + Release version 0.3
  * Local SVN release 234 (jcdubacq,2008-11-05)
  + Fix disabled/enabled bug for stacker
  * Local SVN release 194 (jcdubacq,2008-07-16)
  + Reindent, and add frenchtypography, markup parsing capabilities
  * Local SVN release 176 (jcdubacq,2008-06-23)
  + Big fixes
  * Local SVN release 172 (jcdubacq,2008-05-07)
  + New plugin: stacker

=== To do ===

  * Many things

References

   1. http://jean-christophe.dubacq.fr/post/stacker
   2. http://jean-christophe.dubacq.fr/post/latexrender
   3. http://jean-christophe.dubacq.fr/post/latexrender
   4. http://www.gnu.org/licenses/gpl-2.0.html
   5. http://jean-christophe.dubacq.fr/pages/Contact
