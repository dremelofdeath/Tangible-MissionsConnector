FOURTHDIR = $(shell pwd | perl -pe 's|^(/?)(.*?/){3}||' | perl -pe 's|(/.*)||')
SVNDIR = $(shell pwd | sed -E -e 's/\/(\w?\.?\_?\/?)+\///g')
JSFILES = $(shell ls *.js | grep -v -E '\.yui\.' | grep -v -E '\.ship\.')
BUILDNUMBER = $(shell echo "ibase=10;obase=16;`svnversion | sed -E -e 's/.*://' -e 's/[^0-9]//g'`" | bc)
DEPCLEANPENDING = no

.PHONY: clean unfinalize buildnumber __depcleanpending

all: ship

ship: $(JSFILES:.js=.yui.js) cmc.ship.js cmc.ship.yui.js index.ship.php

cuts: index.ship.php cmc.ship.js

clean:
	$(RM) *.ship.js
	$(RM) *.yui.js
	$(RM) *.ship.php

cmc.ship.js: cmc.js
	sed -E \
		-e 's/(this(\.cmc)?|CMC)\.(log|error|assert|beginFunction|endFunction)\(.*\)(;|,)?//g' \
		-e '/\/\/@\/BEGIN\/DEBUGONLYSECTION/,/\/\/@\/END\/DEBUGONLYSECTION/d' \
		-e '/\/\/@\/BEGIN\/CUTSECTION/,/\/\/@\/END\/CUTSECTION/d' \
		-e 's/\/\* @\/VERSIONMARKER \*\/.*$$/"2.0 Build $(BUILDNUMBER)",/' \
		$? > $@

%.yui.js: %.js
	if [ "$(SVNDIR)" == "trunk" -o "$(FOURTHDIR)" == "htdocs" ]; then \
	  java -jar ../tools/yuicompressor-2.4.7.jar -o $@ $?; \
	else \
		java -jar ../../tools/yuicompressor-2.4.7.jar -o $@ $?; \
	fi

index.ship.php: index.php
	sed -E \
		-e '/<!-- @\/BEGIN\/ADMINCODEBLOCK -->/,/<!-- @\/END\/ADMINCODEBLOCK -->/d' \
		-e '/<!-- @\/BEGIN\/CUTSECTION -->/,/<!-- @\/END\/CUTSECTION -->/d' \
		$? > $@

minifyfinalize: $(JSFILES:.js=.yui.js) cmc.ship.js cmc.ship.yui.js
	for jsfile in `ls *.yui.js`; do \
		orig=`echo $$jsfile | sed -E -e 's/\.yui//'`; \
		$(RM) $$orig; \
		mv $$jsfile $$orig; \
		done
	if [ "$(DEPCLEANPENDING)" == "no" ]; then \
		$(RM) $?; \
	fi

cutsfinalize: cuts
	$(RM) index.php
	mv index.ship.php index.php
	$(RM) cmc.js
	mv cmc.ship.js cmc.js
	if [ "$(DEPCLEANPENDING)" == "no" ]; then \
		$(RM) $?; \
	fi

__depcleanpending:
	$(eval DEPCLEANPENDING := yes)
	@echo depclean is pending...

buildfinalize: __depcleanpending minifyfinalize cutsfinalize ship
	$(RM) $?

unfinalize: clean
	svn revert $(JSFILES) cmc.js *.php

buildnumber:
	@echo $(BUILDNUMBER)
