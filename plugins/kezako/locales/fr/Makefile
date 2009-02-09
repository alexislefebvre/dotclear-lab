POFILES := $(wildcard *.po)
main.lang.php: $(POFILES)
	echo '<?php' > $@
	cat $(POFILES) | perl -ni -e 'chomp if (/^msgid/);if (/^msgid/) { $$a=$$_; $$a =~ s/\x27/\x5c\x27/g; $$a=~ s/msgid \x22(.*)\x22$$/\$$GLOBALS[\x27__l10n\x27][\x27$${1}\x27]=\x27/;} if (/^msgstr/) {/^msgstr \x22(.*)\x22$$/;$$b=$$1;$$b =~ s/\x27/\x5c\x27/g;print $$a."$$b\x27;\n" unless ($$a =~ /^.GLOBALS..__l10n...\x27\x27./);}' >> $@
	echo '?>' >> $@
