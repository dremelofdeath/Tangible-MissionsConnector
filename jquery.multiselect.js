(function(b,c){var a=0;b.widget("ech.multiselect",{options:{header:true,height:175,minWidth:225,classes:"",checkAllText:"Check all",uncheckAllText:"Uncheck all",noneSelectedText:"Select options",selectedText:"# selected",selectedList:0,show:"",hide:"",autoOpen:false,multiple:true,position:{}},_create:function(){var g=this.element.hide(),k=this.options;this.speed=b.fx.speeds._default;this._isOpen=false;var d=(this.button=b('<button type="button"><span class="ui-icon ui-icon-triangle-2-n-s"></span></button>')).addClass("ui-multiselect ui-widget ui-state-default ui-corner-all").addClass(k.classes).attr({title:g.attr("title"),"aria-haspopup":true,tabIndex:g.attr("tabIndex")}).insertAfter(g),f=(this.buttonlabel=b("<span />")).html(k.noneSelectedText).appendTo(d),j=(this.menu=b("<div />")).addClass("ui-multiselect-menu ui-widget ui-widget-content ui-corner-all").addClass(k.classes).insertAfter(d),i=(this.header=b("<div />")).addClass("ui-widget-header ui-corner-all ui-multiselect-header ui-helper-clearfix").appendTo(j),e=(this.headerLinkContainer=b("<ul />")).addClass("ui-helper-reset").html(function(){if(k.header===true){return'<li><a class="ui-multiselect-all" href="#"><span class="ui-icon ui-icon-check"></span><span>'+k.checkAllText+'</span></a></li><li><a class="ui-multiselect-none" href="#"><span class="ui-icon ui-icon-closethick"></span><span>'+k.uncheckAllText+"</span></a></li>"}else{if(typeof k.header==="string"){return"<li>"+k.header+"</li>"}else{return""}}}).append('<li class="ui-multiselect-close"><a href="#" class="ui-multiselect-close"><span class="ui-icon ui-icon-circle-close"></span></a></li>').appendTo(i),h=(this.checkboxContainer=b("<ul />")).addClass("ui-multiselect-checkboxes ui-helper-reset").appendTo(j);this._bindEvents();this.refresh(true);if(!k.multiple){j.addClass("ui-multiselect-single")}},_init:function(){if(this.options.header===false){this.header.hide()}if(!this.options.multiple){this.headerLinkContainer.find(".ui-multiselect-all, .ui-multiselect-none").hide()}if(this.options.autoOpen){this.open()}if(this.element.is(":disabled")){this.disable()}},refresh:function(j){var f=this.element,i=this.options,h=this.menu,g=this.checkboxContainer,d=[],e=[],k=f.attr("id")||a++;this.element.find("option").each(function(n){var o=b(this),v=this.parentNode,q=this.innerHTML,t=this.title,r=this.value,m=this.id||"ui-multiselect-"+k+"-option-"+n,u=this.disabled,l=this.selected,p=["ui-corner-all"],s;if(v.tagName.toLowerCase()==="optgroup"){s=v.getAttribute("label");if(b.inArray(s,d)===-1){e.push('<li class="ui-multiselect-optgroup-label"><a href="#">'+s+"</a></li>");d.push(s)}}if(u){p.push("ui-state-disabled")}if(l&&!i.multiple){p.push("ui-state-active")}e.push('<li class="'+(u?"ui-multiselect-disabled":"")+'">');e.push('<label for="'+m+'" title="'+t+'" class="'+p.join(" ")+'">');e.push('<input id="'+m+'" name="multiselect_'+k+'" type="'+(i.multiple?"checkbox":"radio")+'" value="'+r+'" title="'+q+'"');if(l){e.push(' checked="checked"');e.push(' aria-selected="true"')}if(u){e.push(' disabled="disabled"');e.push(' aria-disabled="true"')}e.push(" /><span>"+q+"</span></label></li>")});g.html(e.join(""));this.labels=h.find("label");this._setButtonWidth();this._setMenuWidth();this.button[0].defaultValue=this.update();if(!j){this._trigger("refresh")}},update:function(){var h=this.options,f=this.labels.find("input"),e=f.filter(":checked"),d=e.length,g;if(d===0){g=h.noneSelectedText}else{if(b.isFunction(h.selectedText)){g=h.selectedText.call(this,d,f.length,e.get())}else{if(/\d/.test(h.selectedList)&&h.selectedList>0&&d<=h.selectedList){g=e.map(function(){return this.title}).get().join(", ")}else{g=h.selectedText.replace("#",d).replace("#",f.length)}}}this.buttonlabel.html(g);return g},_bindEvents:function(){var d=this,e=this.button;function f(){d[d._isOpen?"close":"open"]();return false}e.find("span").bind("click.multiselect",f);e.bind({click:f,keypress:function(g){switch(g.which){case 27:case 38:case 37:d.close();break;case 39:case 40:d.open();break}},mouseenter:function(){if(!e.hasClass("ui-state-disabled")){b(this).addClass("ui-state-hover")}},mouseleave:function(){b(this).removeClass("ui-state-hover")},focus:function(){if(!e.hasClass("ui-state-disabled")){b(this).addClass("ui-state-focus")}},blur:function(){b(this).removeClass("ui-state-focus")}});this.header.delegate("a","click.multiselect",function(g){if(b(this).hasClass("ui-multiselect-close")){d.close()}else{d[b(this).hasClass("ui-multiselect-all")?"checkAll":"uncheckAll"]()}g.preventDefault()});this.menu.delegate("li.ui-multiselect-optgroup-label a","click.multiselect",function(k){k.preventDefault();var j=b(this),i=j.parent().nextUntil("li.ui-multiselect-optgroup-label").find("input:visible:not(:disabled)"),g=i.get(),h=j.parent().text();if(d._trigger("beforeoptgrouptoggle",k,{inputs:g,label:h})===false){return}d._toggleChecked(i.filter(":checked").length!==i.length,i);d._trigger("optgrouptoggle",k,{inputs:g,label:h,checked:g[0].checked})}).delegate("label","mouseenter.multiselect",function(){if(!b(this).hasClass("ui-state-disabled")){d.labels.removeClass("ui-state-hover");b(this).addClass("ui-state-hover").find("input").focus()}}).delegate("label","keydown.multiselect",function(g){g.preventDefault();switch(g.which){case 9:case 27:d.close();break;case 38:case 40:case 37:case 39:d._traverse(g.which,this);break;case 13:b(this).find("input")[0].click();break}}).delegate('input[type="checkbox"], input[type="radio"]',"click.multiselect",function(j){var i=b(this),k=this.value,h=this.checked,g=d.element.find("option");if(this.disabled||d._trigger("click",j,{value:k,text:this.title,checked:h})===false){j.preventDefault();return}i.attr("aria-selected",h);g.each(function(){if(this.value===k){this.selected=h;if(h){this.setAttribute("selected","selected")}else{this.removeAttribute("selected")}}else{if(!d.options.multiple){this.selected=false}}});if(!d.options.multiple){d.labels.removeClass("ui-state-active");i.closest("label").toggleClass("ui-state-active",h);d.close()}d.element.trigger("change");setTimeout(b.proxy(d.update,d),10)});b(document).bind("mousedown.multiselect",function(g){if(d._isOpen&&!b.contains(d.menu[0],g.target)&&!b.contains(d.button[0],g.target)&&g.target!==d.button[0]){d.close()}});b(this.element[0].form).bind("reset.multiselect",function(){setTimeout(function(){d.update()},10)})},_setButtonWidth:function(){var d=this.element.outerWidth(),e=this.options;if(/\d/.test(e.minWidth)&&d<e.minWidth){d=e.minWidth}this.button.width(d)},_setMenuWidth:function(){var d=this.menu,e=this.button.outerWidth()-parseInt(d.css("padding-left"),10)-parseInt(d.css("padding-right"),10)-parseInt(d.css("border-right-width"),10)-parseInt(d.css("border-left-width"),10);d.width(e||this.button.outerWidth())},_traverse:function(h,i){var f=b(i),e=h===38||h===37,d=f.parent()[e?"prevAll":"nextAll"]("li:not(.ui-multiselect-disabled, .ui-multiselect-optgroup-label)")[e?"last":"first"]();if(!d.length){var g=this.menu.find("ul:last");this.menu.find("label")[e?"last":"first"]().trigger("mouseover");g.scrollTop(e?g.height():0)}else{d.find("label").trigger("mouseover")}},_toggleCheckbox:function(e,d){return function(){!this.disabled&&(this[e]=d);if(d){this.setAttribute("aria-selected",true)}else{this.removeAttribute("aria-selected")}}},_toggleChecked:function(d,h){var f=(h&&h.length)?h:this.labels.find("input"),e=this;f.each(this._toggleCheckbox("checked",d));this.update();var g=f.map(function(){return this.value}).get();this.element.find("option").each(function(){if(!this.disabled&&b.inArray(this.value,g)>-1){e._toggleCheckbox("selected",d).call(this)}});if(f.length){this.element.trigger("change")}},_toggleDisabled:function(d){this.button.attr({disabled:d,"aria-disabled":d})[d?"addClass":"removeClass"]("ui-state-disabled");this.menu.find("input").attr({disabled:d,"aria-disabled":d}).parent()[d?"addClass":"removeClass"]("ui-state-disabled");this.element.attr({disabled:d,"aria-disabled":d})},open:function(i){var l=this,g=this.button,f=this.menu,h=this.speed,d=this.options;if(this._trigger("beforeopen")===false||g.hasClass("ui-state-disabled")||this._isOpen){return}var m=f.find("ul:last"),k=d.show,j=g.position();if(b.isArray(d.show)){k=d.show[0];h=d.show[1]||l.speed}m.scrollTop(0).height(d.height);if(b.ui.position&&!b.isEmptyObject(d.position)){d.position.of=d.position.of||g;f.show().position(d.position).hide().show(k,h)}else{f.css({top:j.top+g.outerHeight(),left:j.left}).show(k,h)}this.labels.eq(0).trigger("mouseover").trigger("mouseenter").find("input").trigger("focus");g.addClass("ui-state-active");this._isOpen=true;this._trigger("open")},close:function(){if(this._trigger("beforeclose")===false){return}var f=this.options,d=f.hide,e=this.speed;if(b.isArray(f.hide)){d=f.hide[0];e=f.hide[1]||this.speed}this.menu.hide(d,e);this.button.removeClass("ui-state-active").trigger("blur").trigger("mouseleave");this._isOpen=false;this._trigger("close")},enable:function(){this._toggleDisabled(false)},disable:function(){this._toggleDisabled(true)},checkAll:function(d){this._toggleChecked(true);this._trigger("checkAll")},uncheckAll:function(){this._toggleChecked(false);this._trigger("uncheckAll")},getChecked:function(){return this.menu.find("input").filter(":checked")},destroy:function(){b.Widget.prototype.destroy.call(this);this.button.remove();this.menu.remove();this.element.show();return this},isOpen:function(){return this._isOpen},widget:function(){return this.menu},_setOption:function(d,e){var f=this.menu;switch(d){case"header":f.find("div.ui-multiselect-header")[e?"show":"hide"]();break;case"checkAllText":f.find("a.ui-multiselect-all span").eq(-1).text(e);break;case"uncheckAllText":f.find("a.ui-multiselect-none span").eq(-1).text(e);break;case"height":f.find("ul:last").height(parseInt(e,10));break;case"minWidth":this.options[d]=parseInt(e,10);this._setButtonWidth();this._setMenuWidth();break;case"selectedText":case"selectedList":case"noneSelectedText":this.options[d]=e;this.update();break;case"classes":f.add(this.button).removeClass(this.options.classes).addClass(e);break}b.Widget.prototype._setOption.apply(this,arguments)}})})(jQuery);