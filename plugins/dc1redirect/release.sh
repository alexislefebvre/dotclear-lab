#!/bin/bash
plugin=dc1redirect
svnurl=https://svn.dotclear.net/lab/plugins/$plugin
rev=$(LANG=C svn info "$svnurl" | sed -n 's/^Last Changed Rev: \([0-9]*\)$/\1/p')
echo    "$plugin-r$rev"
test -d "$plugin-r$rev" && rm -Rf "$plugin-r$rev"
mkdir   "$plugin-r$rev"
cd      "$plugin-r$rev"
svn export -r "$rev" "$svnurl"
test -f "$plugin/release.sh" && rm -f "$plugin/release.sh"
zip -R "$plugin-r$rev.zip" $(find "$plugin")
