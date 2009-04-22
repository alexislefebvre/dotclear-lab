The LaTeXrender plugin is an extension that allows a server for Dotclear 2 and
an installation of [tex]\LaTeX[/tex] (a word procesor, very much used for its
mathematical typesetting capabilities and the reference format for scientific
publication in many areas) to display (as embedded pictures) bits of text
composed by [tex]\LaTeX[tex]. It is bound with a settings interface to allow for
unusual paths for the auxiliary programs.

This plugin is on one hand, the conversion of an already existing class (the
[1]latexrender class, written in PHP for a few interfaces; on the other hand,
the class was simplified, adapted to the restricted framework of Dotclear 2, and
enhanced for the vertical offset computation (after a patch by Mike Boyle
modified again by me) and enhanced again by adding a colour management (some
people design web sites and themes written yellow or white on a dark background,
and the former class only produced black text, which was pretty unreadable).
  ___________________________________________________________________________

The maintenance page for this plugin is located at this address:
[2]http://jean-christophe.dubacq.fr/post/latexrender.

=== Installation ===

Use the zipped file attached to this post.Go then to the administration area as
super-admin, and fill in the various access paths to the auxiliary programs (the
default values should be fine on most servers ; [tex]\LaTeX[/tex] and the
[3]ImageMagick set of programs are used) as well as a few other parameters.
Their meaning should be self-evident; if this is not the case, please submit a
bug report.

The extension requires an installation on the server of latex and the
ImageMagick set of programs (mostly the convert and identify utilities). Most
providers do not install those utilities (latex especially, ImageMagick may be
more frequent), but on specific hosting platforms (self-hosted, enterprise-level
servers, lab or university web servers) this is usually very simple to install,
these are very classical software packages under Linux.

This extension requires the [4]stacker extension to be installed. A solution
that does not require this extension is in the works, but without that it will
be necessary to conflict with other extensions that do redefine the access to
the posts' contents (note: as the author uses several of them, this is therefore
considered as a bug).

The current version of this plugin is 0.9.1 (local svn 348).

This plugin is licensed under [5]GPL version 2.0.

=== Usage ===

--- Basic usage ---

The basic usage is really easy, it is sufficient to type the [tex]\LaTeX[/tex]
code in between two pseudo-markup [tex]...[/tex]. For example,
[tex]$\displaystyle\int_{0}^{1}\frac{x^{4}\left(1-x\right)^{4}}{1+x^{2}}dx
=\frac{22}{7}-\pi$[/tex].

The equations are represented as images and the LaTeX code is used as the title
for the image.

The limitations on what can be typeset with [tex]\LaTeX[/tex] are a restriction
of allowed instructions (no \special, for example), the code pieces must be
composed in horizontal mode (if one wants to compose in vertical mode, for big
equations for example or many paragraphs, one has to put a minipage environment
around the typeset part: \begin{minipage}{500px}...\end{minipage}). This is a
restriction coming from [tex]\LaTeX[/tex], not from the extension.

The pictures are stored in the blogs' public directory, by default in a
subdirectory called latexrender/images.

--- Colour management ---

The original class could not manage colours: the text was always rendered black
(on a transparent background in the last versions). Some themes feature a dark
background, making the plugin unusable in such circumstances. It is possible to
inform LaTeXrender about the main color wanted for the rendered text in the
page. For example, on this page, the original theme Grey Silence does no
specific post-treatement, but the theme LIPN-hiver was white on black.

If you choose the colorization method, and you have a recent enough version of
Imagemagick (it works at least with ImageMagick 6.2.4 02/10/07 Q16), you will be
able to use this possibility; it is sufficient to add in one of your theme files
(just before the last line, which should read ?>), the expression
$core->theme_color='FFFFFF';. This line can be added before the last line of
either _public.php or _prepend.php. If neither of these files are present, a
file _public.php consisting of one line can be added:
<?php $core->theme_color='FFFFFF'; ?>

FFFFFF is to be replaced by the desired colour, coded in hexadecimal (here,
white; yellow is FFFF00, red is FF0000).

=== To tell me about a bug or helping this plugin ===

The best way is to contact me [6]by mail (for a bug) or leave a comment (telling
me you tested this extension) at the maintenance page. In case of an update, I
will modify the maintenance page accordingly.

Note: this changelog is not complete, automatically generated and probably not
even informative before 2009.
  * Local SVN release 348 (jcdubacq,2009-04-22)
  + Fix documentation
  * Local SVN release 346 (jcdubacq,2009-04-22)
  + Fix licence block, reindent
  + Switch to LGPL 2.1 in order to keep the original licence
  * Local SVN release 333 (jcdubacq,2009-04-18)
  + Allow for public path to be an absolute path, release
  * Local SVN release 303 (jcdubacq,2009-01-27)
  + Fix locales
  * Local SVN release 297 (jcdubacq,2009-01-26)
  + Adapt to version 0.3 of stacker (behavior initStacker)
  * Local SVN release 292 (jcdubacq,2009-01-22)
  + Update documentation, release new version 0.6
  * Local SVN release 259 (jcdubacq,2008-11-18)
  + Fix public url usage in admin area
  * Local SVN release 205 (jcdubacq,2008-07-24)
  + Add documentation, new version
  * Local SVN release 187 (jcdubacq,2008-07-09)
  + Add admin page, rewrite latexrender class to better fit the needs of DC2
  * Local SVN release 168 (jcdubacq,2008-05-06)
  + Order plugins and themes
  * Local SVN release 47 (jcdubacq,2008-02-15)
  + Add latexrender plugin

=== To do ===

  * Make a version of this plugin that does not depend on stacker plugin
  * Communicate with the initial authors of class.latexrender.php
  * Manage colours independently of the themes
  * Work with Sacha to make a client/server version of latexrender that could
convey both image and vertical offset, with enough security (the image
computation may have a CPU cost too high and too risky if anybody could call the
server)

References

   1. http://www.mayer.dial.pipex.com/tex.htm
   2. http://jean-christophe.dubacq.fr/post/latexrender
   3. http://www.imagemagick.org/script/index.php
   4. http://jean-christophe.dubacq.fr/post/stacker
   5. http://www.gnu.org/licenses/lgpl-2.1.html
   6. http://jean-christophe.dubacq.fr/pages/Contact
