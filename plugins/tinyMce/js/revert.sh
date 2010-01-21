#!/bin/sh

cd tiny_mce_jquery/themes/advanced/

# http://www.cyberciti.biz/faq/bash-loop-over-file/
# FILES="*.htm"
# http://tldp.org/LDP/Bash-Beginners-Guide/html/sect_09_01.html

# htm files
for f in `ls *.htm`
do
	echo "Processing $f file..."
	mv $f'.bak' $f
done


# Javascript file
mv 'editor_template_src.js.bak' editor_template_src.js

# CSS files
cd skins/default/

for f in `ls *.css`
do
	echo "Processing $f file..."
	mv $f'.bak' $f
done
