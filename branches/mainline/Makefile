SVNDIR = $(shell pwd | sed -E -e 's/\/(\w?\.?\_?\/?)+\///g')
JSFILES = $(shell ls *.js)

.PHONY: clean ship buildfinalize unship
ship: buildfinalize $(JSFILES:.js=.yui.js) cmc.ship.js index.ship.php

clean:
	$(RM) *.ship.js
	$(RM) *.yui.js
	$(RM) *.ship.php

cmc.ship.js: cmc.js
	sed -E \
		-e 's/(this(\.cmc)?|CMC)\.(log|error|assert|beginFunction|endFunction)\(.*\)(;|,)?//g' \
		-e '/\/\/@\/BEGIN\/DEBUGONLYSECTION/,/\/\/@\/END\/DEBUGONLYSECTION/d' \
		$? > $@

%.yui.js: %.js
	if [ "$(SVNDIR)" == "trunk" ]; then \
	  java -jar ../tools/yuicompressor-2.4.7.jar -o $@ $?; \
	else \
		java -jar ../../tools/yuicompressor-2.4.7.jar -o $@ $?; \
	fi

index.ship.php: index.php
	sed -E \
		-e '/<!-- @\/BEGIN\/ADMINCODEBLOCK -->/,/<!-- @\/END\/ADMINCODEBLOCK -->/d' \
		$? > $@

buildfinalize: index.ship.php $(JSFILES:.js=.yui.js) cmc.ship.yui.js cmc.ship.js
	for jsfile in `ls *.yui.js`; do \
		orig=`echo $$jsfile | sed -E -e 's/\.yui//'`; \
		$(RM) $$orig; \
		mv $$jsfile $$orig; \
		done
	$(RM) index.php
	mv index.ship.php index.php
	$(RM) cmc.js
	mv cmc.ship.js cmc.js
	$(RM) $?

unship: clean
	svn revert *.js cmc.js *.php
