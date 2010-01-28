#!/bin/sh

cd ../js/tiny_mce_jquery/themes/advanced/

# http://www.cyberciti.biz/faq/bash-loop-over-file/
# FILES="*.htm"
# http://tldp.org/LDP/Bash-Beginners-Guide/html/sect_09_01.html

# htm files
for f in `ls *.htm`
do
	echo "Processing $f file..."
	cp $f $f'.bak'
	# take action on each file. $f store current file name
	sed -i 's#src="#src="index.php?pf=tinyMce/js/tiny_mce_jquery/themes/advanced/#g' $f
	sed -i 's#/themes/advanced/../../#/#g' $f
done


# Javascript file
cp editor_template_src.js 'editor_template_src.js.bak'
#sed -i "s#tinymce.baseURL +#'plugin.php?p=tinyMce&tinyMce_file=' + #g" editor_template_src.js
#sed -i "s#tinymce.baseURL + \'#\'plugin.php?p=tinyMce&tinyMce_file=#g" editor_template_src.js

sed -i "s#tinymce.baseURL + ##g" editor_template_src.js
sed -i "s#/themes/#plugin.php?p=tinyMce\\&tinyMce_file=/themes/#g" editor_template_src.js

# CSS files
cd skins/default/

for f in `ls *.css`
do
	echo "Processing $f file..."
	cp $f $f'.bak'
	# take action on each file. $f store current file name
	sed -i 's#url(img#url(index.php?pf=tinyMce/js/tiny_mce_jquery/themes/advanced/skins/default/img#g' $f
	sed -i 's#url(../../img#url(index.php?pf=tinyMce/js/tiny_mce_jquery/themes/advanced/img#g' $f
done
