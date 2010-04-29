#!/bin/sh

set -e

PROJECT="prvcat"
BUILDDIR=./build
DEST=$BUILDDIR/$PROJECT

VERSION=$(sed _define.php -n -e "s/^\s\+\/\* Version \*\/\s\+'\([0-9.]\+\)',\s*/\1/p")

rm -rf $BUILDDIR
mkdir -p $DEST

for f in *; do
    if [ $f != "build" ]; then
        cp -a $f $DEST
    fi
done
rm -f $DEST/locales.help
rm -f $DEST/build.sh
rm -f $DEST/locales/*/*.po $DEST/locales/*/*.pot

cd $BUILDDIR && zip -ru $PROJECT.$VERSION.zip $PROJECT

echo "build success"
