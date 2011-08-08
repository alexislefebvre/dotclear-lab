#!/bin/bash
[[ -d .svn ]] && plugin=$(LANG=C svn info | sed -n 's!^URL: http.*/!!p')
[[ -z "$plugin" ]] && plugin=$(basename "$(pwd)")
svnurl=https://svn.dotclear.net/lab/plugins/$plugin
rev=$(LANG=C svn info "$svnurl" | sed -n 's/^Last Changed Rev: \([0-9]*\)$/\1/p')
tmpdir="$plugin-r$rev"
echo    "$tmpdir"
test -d "$tmpdir" && rm -Rf "$tmpdir"
mkdir   "$tmpdir"
cd      "$tmpdir"
svn export -r "$rev" "$svnurl" || exit $?
output="../$plugin-r$rev.zip"
rm -f "$output"
find "$plugin" | zip "$output" -@ -x "$plugin/release.sh"
rm -Rf "$tmpdir"
