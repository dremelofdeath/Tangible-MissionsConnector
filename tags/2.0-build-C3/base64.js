var ua=navigator.userAgent.toLowerCase();if(ua.indexOf(" chrome/")>=0||ua.indexOf(" firefox/")>=0||ua.indexOf(" gecko/")>=0){var StringMaker=function(){this.str="";this.length=0;this.append=function(a){this.str+=a;this.length+=a.length};this.prepend=function(a){this.str=a+this.str;this.length+=a.length};this.toString=function(){return this.str}}}else{var StringMaker=function(){this.parts=[];this.length=0;this.append=function(a){this.parts.push(a);this.length+=a.length};this.prepend=function(a){this.parts.unshift(a);this.length+=a.length};this.toString=function(){return this.parts.join("")}}}var keyStr="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";function encode64(c){var b=new StringMaker();var j,g,e;var k,h,f,d;var a=0;while(a<c.length){j=c.charCodeAt(a++);g=c.charCodeAt(a++);e=c.charCodeAt(a++);k=j>>2;h=((j&3)<<4)|(g>>4);f=((g&15)<<2)|(e>>6);d=e&63;if(isNaN(g)){f=d=64}else{if(isNaN(e)){d=64}}b.append(keyStr.charAt(k)+keyStr.charAt(h)+keyStr.charAt(f)+keyStr.charAt(d))}return b.toString()}function decode64(c){var b=new StringMaker();var j,g,e;var k,h,f,d;var a=0;c=c.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(a<c.length){k=keyStr.indexOf(c.charAt(a++));h=keyStr.indexOf(c.charAt(a++));f=keyStr.indexOf(c.charAt(a++));d=keyStr.indexOf(c.charAt(a++));j=(k<<2)|(h>>4);g=((h&15)<<4)|(f>>2);e=((f&3)<<6)|d;b.append(String.fromCharCode(j));if(f!=64){b.append(String.fromCharCode(g))}if(d!=64){b.append(String.fromCharCode(e))}}return b.toString()};