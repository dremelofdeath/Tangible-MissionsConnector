SVNDIR = $(shell pwd | sed -E -e 's/\/(\w?\.?\_?\/?)+\///g')

.PHONY: clean ship cmc.min.js cmc.yui.js replacecmc
ship: replacecmc cmc.yui.js cmc.min.js

clean:
	rm -f cmc.min.js
	rm -f cmc.yui.js

cmc.min.js: cmc.js
	sed -E \
		-e 's/(this(\.cmc)?|CMC)\.(log|error|assert|beginFunction|endFunction)\(.*\)(;|,)?//g' \
		-e '/\/\/@\/BEGIN\/DEBUGONLYSECTION/,/\/\/@\/END\/DEBUGONLYSECTION/d' \
		$? > $@

cmc.yui.js: cmc.min.js
	if [ "$(SVNDIR)" == "trunk" ]; then \
	  java -jar ../tools/yuicompressor-2.4.7.jar -o $@ $?; \
	else \
		java -jar ../../tools/yuicompressor-2.4.7.jar -o $@ $?; \
	fi

replacecmc: cmc.yui.js cmc.min.js
	rm -f cmc.js
	mv cmc.yui.js cmc.js
	rm -f cmc.min.js
