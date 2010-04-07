NAME = odt

ifndef V
	TAG = HEAD
	VER = svn
else
	TAG = v$(V)
	VER = $(V)
endif

ARCHIVE = plugin-$(NAME)-$(VER).zip

tarball: $(ARCHIVE)
$(ARCHIVE):
	@if [ "$(TAG)" != "HEAD" -a ! -f .git/refs/tags/$(TAG) ]; then echo "No such tag: $(TAG)"; exit 1; fi
	git archive --format=zip --prefix=$(NAME)/ $(TAG) > $@

clean:
	rm -f $(ARCHIVE)

docs:
	phpdoc -o HTML:Smarty:default -d $(CURDIR) -s -ti "Dotclear ODT export" -t $(CURDIR)/docs

.PHONY: tarball clean docs
