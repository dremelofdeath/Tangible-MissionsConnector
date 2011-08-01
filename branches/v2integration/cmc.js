// JavaScript code goes here

// first things first, some browser compatibility hacks
// add Object.keys() support to browsers that do not have it
if(!Object.keys) Object.keys = function(o){
  if (o !== Object(o))
    throw new TypeError('Object.keys called on non-object');
  var ret=[],p;
  for(p in o) if(Object.prototype.hasOwnProperty.call(o,p)) ret.push(p);
  return ret;
}

// the main CMC object
var CMC = {
  // variables
  loggedInUser : false,
  friends : false,
  requestsOutstanding : 0,
  dialogsOpen : 0,
  version : "1.9.18",
  searchPageCache : [],
  currentDisplayedSearchPage : 0,
  searchPageImageClearJobQueue : [],
  SearchState : {},
  // startup configuration settings
  StartupConfig : {
    //@/BEGIN/DEBUGONLYSECTION
    attachDebugLogHandlersByDefault : true
    //@/END/DEBUGONLYSECTION
  },

  // methods
  performStartupActions : function () {
    //@/BEGIN/DEBUGONLYSECTION
    if (this.StartupConfig.attachDebugLogHandlersByDefault) {
      this.attachDebugHandlers(this.DebugMode);
    }
    //@/END/DEBUGONLYSECTION
  },

  //@/BEGIN/DEBUGONLYSECTION
  DebugMode : {
    log : function(output, whereTo) {
      var content = '(' + (new Date()).getTime() + ') ' + output;
      if (whereTo === undefined) whereTo = "#debug-log";
      $(whereTo)
        .val($(whereTo).val() + content + "\n")
        .scrollTop(99999)
        .scrollTop($(whereTo).scrollTop()*12);
    },

    error : function(errmsg) {
      var message = "ERROR: " + errmsg;
      this.log(message);
    },

    assert : function(condition, bugmsg) {
      if (bugmsg === undefined && condition && typeof condition == "string" && condition != "") {
        // in this case, we've only been passed one parameter, and it's a
        // non-empty string. we will assume that the assert failed, and that the
        // string is the assert failure message --zack
        bugmsg = condition;
        condition = false;
      }
      if (!condition) {
        var message = "ASSERT FAILED!\nIf you're reporting this, please use this message:\n";
        message += bugmsg;
        this.log(message);
      }
    },

    beginFunction : function(fnName) {
      // check for scope corruption
      this.assert(this === CMC, "Scope corruption detected! this === CMC failed!");
      this.log("begin function: " + fnName);
    },

    endFunction : function(fnName, fnReturnValue) {
      if (fnReturnValue !== undefined) {
        this.log("end function: " + fnName + ", returning " + fnReturnValue.toString());
      } else {
        this.log("end function: " + fnName);
      }
    },
  },

  log : $.noop,
  error : $.noop,
  assert : $.noop,
  beginFunction : $.noop,
  endFunction : $.noop,

  attachDebugHandlers : function(handlerSet) {
    if ("log" in handlerSet) {
      this.log = handlerSet.log;
    }
    if ("error" in handlerSet) {
      this.error = handlerSet.error;
    }
    if ("assert" in handlerSet) {
      this.assert = handlerSet.assert;
    }
    if ("beginFunction" in handlerSet) {
      this.beginFunction = handlerSet.beginFunction;
    }
    if ("endFunction" in handlerSet) {
      this.endFunction = handlerSet.endFunction;
    }
    $("#debug-section").show();
  },

  detachDebugHandlers : function() {
    $("#debug-log").val("");
    this.log = $.noop;
    this.error = $.noop;
    this.assert = $.noop;
    this.beginFunction = $.noop;
    this.endFunction = $.noop;
    $("#debug-section").hide();
  },
  //@/END/DEBUGONLYSECTION

  page : function(from, to) {
    $(from).hide("drop", {direction: 'left'}, 250, function() {
      $(to).show("drop", {direction: 'right'}, 250, null);
    });
  },

  closeAllDialogs : function(except) {
    $(".ui-dialog:visible").each(function() {
      $(this).children(".ui-dialog-content").each(function() {
        if (this != except) {
          $(this).dialog('close');
        }
      });
    });
  },

  dialogOpen : function(dialog) {
    this.dialogsOpen++;
    this.closeAllDialogs(dialog);
    if ($.support.opacity && this.dialogsOpen == 1) {
      $("#tabs, #cmc-footer").fadeTo('fast', 0.5);
    }
  },

  dialogClose : function(dialog) {
    if ($.support.opacity && this.dialogsOpen == 1) {
      $("#tabs, #cmc-footer").fadeTo('fast', 1.0);
    }
    this.dialogsOpen--;
  },

  showAjaxSpinner : function() {
    $("#ajax-spinner").show();
  },

  hideAjaxSpinner : function() {
    $("#ajax-spinner").hide();
  },

  //@/BEGIN/DEBUGONLYSECTION
  updateDebugAjaxRequestInformation : function() {
    $("#requests-outstanding-value").html(""+this.requestsOutstanding);
  },
  //@/END/DEBUGONLYSECTION

  ajaxNotifyStart : function() {
    if (this.requestsOutstanding == 0) {
      this.showAjaxSpinner();
    }
    this.requestsOutstanding++;
    //@/BEGIN/DEBUGONLYSECTION
    this.updateDebugAjaxRequestInformation();
    //@/END/DEBUGONLYSECTION
  },

  ajaxNotifyComplete : function() {
    if (this.requestsOutstanding > 0) {
      this.requestsOutstanding--;
      if (this.requestsOutstanding == 0) { // not a bug if we just decremented ;)
        this.hideAjaxSpinner();
      }
    } else if (this.requestsOutstanding == 0) {
      // this is a bug, and needs to be logged. --zack
      this.assert("notified a completed request when none was made");
    }
    //@/BEGIN/DEBUGONLYSECTION
    this.updateDebugAjaxRequestInformation();
    //@/END/DEBUGONLYSECTION
  },

  recalculateTextareaLimit : function(messageID, labelID, limit, customText) {
    var len = $(messageID).val().length;
    limit = limit || 300;
    customText = customText || " characters left";
    $(labelID).html((limit - len) + customText);
  },

  recalculateProblemMessageLimit : function(limit) {
    this.recalculateTextareaLimit(
      "#report-problem-message",
      "#report-problem-characters-left",
      limit
    );
    $("#report-problem-characters-left").fadeIn();
  },

  handleSearchSelect : function(item) {
    this.beginFunction("handleSearchSelect");
    var value = null, errorWhileParsing = false;
    try {
      value = jQuery.parseJSON(item)._value;
    } catch(e) {
      errorWhileParsing = true;
      this.error("caught exception while parsing JSON:\n" + e);
    }
    if (!errorWhileParsing) {
      this.assert(typeof value == "string", "type of value was not a string, actual type = " + typeof value);
      if (value.substring(0,2) == "!!") {
        // this is a special value, we handle these differently
        if (value.substring(2,3) == "z") { // this detection could definitely be better
          // it's a zipcode
          if (this.SearchState.z == undefined) {
            this.SearchState.z = [value.substring(4,9), value.substring(10, value.length)];
          } else {
            // we have a problem, you can't have more than one zipcode
          }
        }
      } else {
        // this is a text item
        // (note: we are going to handle text items as names for now)
        this.SearchState.name = value;
      }
      this.search();
    }
    this.endFunction("handleSearchSelect");
  },

  handleSearchRemove : function(item) {
    this.beginFunction("handleSearchRemove");
    var value = null, errorWhileParsing = false;
    try {
      value = jQuery.parseJSON(item)._value;
    } catch(e) {
      errorWhileParsing = true;
      this.error("caught exception while parsing JSON:\n" + e);
    }
    if (!errorWhileParsing) {
      this.assert(typeof value == "string", "type of value was not a string, actual type = " + typeof value);
      if (value.substring(0,2) == "!!") {
        // this is a special value, we handle these differently
        if (value.substring(2,3) == "z") {
          // it's a zipcode. we don't care what it is, just nuke it
          delete this.SearchState.z;
        }
      } else {
        // this is a text item
        // note: since we are treating text items as names, we should just 
        // delete the name. This will need to be fixed in the future.
        delete this.SearchState.name;
      }
      this.search();
    }
    this.endFunction("handleSearchRemove");
  },

  search : function () {
    this.beginFunction("search");
    this.searchPageCache = [];
    this.currentDisplayedSearchPage = 1;
    this.updateSearchPagingControls();
    this.animateHideSearchResults();
    $(".cmc-search-result").each(function () { $(this).fadeOut('fast'); });
    if (Object.keys(this.SearchState).length == 0) {
      this.log("search is now blank; hide the results panels");
      var _fadeoutsCompleted = 0, _onSearchFadeoutComplete = $.proxy(function () {
        _fadeoutsCompleted++;
        this.log("_onSearchFadeoutComplete triggered, _fadeoutsCompleted="+_fadeoutsCompleted);
        if (_fadeoutsCompleted == 2) {
          $("#cmc-search-results").hide();
        }
      }, this);
      $("#cmc-search-results-title").fadeOut(400, _onSearchFadeoutComplete);
      $("#cmc-search-results-noresultmsg").fadeOut(400, _onSearchFadeoutComplete);
    } else {
      this.log("we have a new search to perform");
      this.ajaxNotifyStart(); // one for good measure, we want the spinner for the whole search
      $.ajax({
        url: "api/searchresults.php",
        data: {
          fbid: "25826994",
          searchkeys: encode64(JSON.stringify(this.SearchState)),
          page: this.currentDisplayedSearchPage,
          perpage: 20
        },
        dataType: "json",
        context: this,
        success: this.onSearchSuccess,
        error: this.onSearchError
      });

      $("#cmc-search-results-title").fadeIn();
    }
    this.endFunction("search");
  },

  onSearchSuccess : function(data, textStatus, jqXHR) {
    this.beginFunction("onSearchSuccess");
    this.assert(data != undefined, "data is undefined in onSearchSuccess");
    $(".cmc-search-result").each(function () {
      $(this).hide();
    });
    $("#cmc-search-results").show();
    if(data.has_error !== undefined && data.has_error !== null) {
      if(data.has_error) {
        // we have a known error, handle it
        this.handleSearchSuccessHasError(data);
      } else {
        if(data.searchids === undefined) {
          // hm, this is strange. probably means no results, but we 
          // might consider logging this in the future. --zack
          this.showSearchResults(null);
        } else if(data.searchids === null) {
          // this should DEFINITELY mean that we have no results
          this.showSearchResults(null);
        } else {
          var searchResults = data.searchids.length > 10 ? data.searchids.slice(0, 10) : data.searchids;
          this.searchPageCache[1] = data.searchids.length > 10 ? data.searchids.slice(10) : null;
          this.getDataForEachFBID(searchResults, $.proxy(function (results) {
            this.searchPageCache[0] = results;
            this.showSearchResults(results);
          }, this));
          if (data.searchids.length > 10 ) {
            this.getDataForEachFBID(this.searchPageCache[1], $.proxy(function (results) {
              this.searchPageCache[1] = results;
            }, this));
          }
        }
      }
    } else {
      // an unknown error occurred? do something!
      this.handleSearchSuccessUnknownError(data, textStatus, jqXHR);
    }
    this.updateSearchPagingControls();
    this.endFunction("onSearchSuccess");
  },

  onSearchError : function(jqXHR, textStatus, errorThrown) {
    this.ajaxNotifyComplete();
    // we might also want to log this or surface an error message or something
    this.handleSearchServerError(jqXHR, textStatus, errorThrown);
  },

  handleSearchSuccessHasError : function(data) {
    this.beginFunction("handleSearchSuccessHasError");
    this.assert(data != undefined, "data is undefined in handleSearchSuccessHasError");
    // we have a known error, handle it
    if(data.err_msg !== undefined) {
      if(data.err_msg != '') {
        this.error("caught an error from the server while searching: \""+data.err_msg+"\"");
      } else {
        this.error("caught an error from the server while searching, but it was blank");
      }
    } else {
      this.error("caught an error from the server while searching, but it did not return an error message");
    }
    this.endFunction("handleSearchSuccessHasError");
  },

  handleSearchServerError : function(jqXHR, textStatus, errorThrown) {
    this.beginFunction("handleSearchServerError");
    this.error("error while communicating with server (status: \""+textStatus+"\", error: \""+errorThrown+"\")");
    this.endFunction("handleSearchServerError");
  },

  handleSearchSuccessUnknownError : function(data, textStatus, jqXHR) {
    this.error("an unknown error occurred while trying to process a search success callback.\ndata = " + data);
  },

  getDataForEachFBID : function (fbids, callback, isRetryCall) {
    this.beginFunction("getDataForEachFBID");
    if (isRetryCall === null || isRetryCall === undefined) isRetryCall = false;
    var results = new Array(fbids.length), requestsCompleted = 0, idPosMap = {}, hasRetryPosted = false;
    this.log("starting timer __timerNotificationTimeout");
    var __timerNotificationTimeout = setTimeout($.proxy(function () {
      this.log("__timerNotificationTimeout is checking getDataForEachFBID");
      if (requestsCompleted != fbids.length) {
        this.log("only " + requestsCompleted + " of " + fbids.length + " FBID requests completed in time (2s)");
        this.log("dumping results variable:");
        for (var each in results) {
          var eachstr = "";
          for (var e in results[each]) {
            eachstr += (e + ": " + results[each][e] + "; ");
          }
          this.log("results["+each+"] = " + eachstr);
        }
        if(!isRetryCall) {
          this.log("first getDataForEachFBID attempt failed. retrying...");
          if(!hasRetryPosted) {
            this.getDataForEachFBID(fbids, callback, true);
          } else {
            this.assert("something just went horribly wrong.\nhasRetryPosted = true, while isRetryCall = false");
          }
          hasRetryPosted = true;
        } else {
          this.error("couldn't complete getDataForEachFBID on retry, bailing");
        }
      } else {
        this.log("__timerNotificationTimeout believes getDataForEachFBID completed");
      }
    }, this), 2000);
    var __notifyComplete = function () {
      requestsCompleted++;
      if (requestsCompleted == fbids.length && !hasRetryPosted) {
        clearTimeout(__timerNotificationTimeout); // cancel the timer, be nice and clean up
        callback(results);
      }
    };
    for(var each in fbids) {
      idPosMap[fbids[each]] = each;
      //this.ajaxNotifyStart();
      FB.api('/' + fbids[each], $.proxy(function (response) {
        if (!response) {
          this.error("response value was null in Facebook API call");
        } else if (response.error) {
          this.error("caught error from Facebook API call: " + response.error);
        } else {
          results[idPosMap[response.id]] = response;
          __notifyComplete();
        }
        //this.ajaxNotifyComplete();
      }, this));
    }
    this.endFunction("getDataForEachFBID");
  },

  showSearchResults : function (results) {
    this.beginFunction("showSearchResults");
    if (results === undefined) {
      // this is a bug! do NOT pass this function undefined! say null to inform it that you have no results!
      this.assert(results === undefined, "undefined passed as results for showSearchResults");
    } else if (results == null || results.length == 0) {
      // no results
      $("#cmc-search-results-noresultmsg").fadeIn();
    } else {
      var imageLoadsCompleted = 0, __notifyImageLoadCompleted = $.proxy(function() {
        imageLoadsCompleted++;
        //@/BEGIN/DEBUGONLYSECTION
        this.assert(imageLoadsCompleted <= results.length ?1:
          "loading more images than we have results for (" + imageLoadsCompleted + ")");
        //@/END/DEBUGONLYSECTION
        if(imageLoadsCompleted == results.length) {
          this.animateShowSearchResults(results);
        }
      }, this);
      this.assert(results.length <= 10, "more than 10 results passed to showSearchResults");
      //@/BEGIN/DEBUGONLYSECTION
      // Since this is a multiline assert, we need to put it within a debug-only
      // section to keep it from breaking ship code --zack
      this.assert($(".result-picture img").length == 0,
                  "found " + $(".result-picture img").length + " junk pictures lying around");
      //@/END/DEBUGONLYSECTION
      for(var each in results) {
        this.assert(results[each].id !== undefined, "id is missing from result at each=" + each);
        var id = "#cmc-search-result-" + each;
        this.ajaxNotifyStart();
        this.assert(results[each].name !== undefined, "name is missing from result at each=" + each);
        $(id).children(".result-name").html(results[each].name ? results[each].name : "");
        $(id).children("div.result-picture").children("img").remove();
        if (results[each].id) {
          $("<img />")
            .attr("src", "http://graph.facebook.com/"+results[each].id+"/picture")
            .attr("cmcid", id) // this is the id from above! not results[each].id!
            .addClass("srpic")
            .one('load', $.proxy(function(event) {
              // I never want to see more than one image here again. --zack
              $($(event.target).attr("cmcid")).children("div.result-picture").children("img").remove();
              $($(event.target).attr("cmcid")).children("div.result-picture").append($(event.target));
              this.ajaxNotifyComplete();
              __notifyImageLoadCompleted();
            }, this));
        } else {
          // this thing is probably intentionally blank, so don't load anything
          var i = 1;
          for (i = 1; i <= 4; i++) { // set up four timeouts to clear the pictures after they load
            this.searchPageImageClearJobQueue
              .push(setTimeout("$('" + id + "').children('.result-picture').children('img').hide();", 100 * i));
          }
          this.ajaxNotifyComplete();
          __notifyImageLoadCompleted();
        }
      } // end for
    } // end else
    this.ajaxNotifyComplete(); // finish the one we started at the beginning of the search
    this.endFunction("showSearchResults");
  },

  animateShowSearchResults : function (results) {
    this.beginFunction("animateShowSearchResults");
    var maxSearchResults = $(".cmc-search-result").length, i = 0;
    this.log("animating resultset starting with " + results[0].name);
    for(var each in results) {
      var id = "#cmc-search-result-" + each, showsCompleted = 0, _onShowComplete = $.proxy(function () {
            // this sure ain't the prettiest way to fix the incomplete
            // page quick click render bug, but it works --zack
            ++showsCompleted;
            if (showsCompleted == results.length) {
              if (results.length < maxSearchResults) {
                this.log("incomplete page, hiding the the results that need cleanup");
                for (var point = maxSearchResults - results.length; point > 0; point--) {
                  // clean up the slots that weren't being shown
                  var tempId = "#cmc-search-result-" + (maxSearchResults - point);
                  $(tempId).delay(4 * (maxSearchResults - point)).fadeOut('fast'); // at least fade out
                }
              }
            }
          }, this);
      $("*").clearQueue("custom-SearchResultsQueue");
      if ($(id + " .result-picture img").length > 1) {
        // cleanup the junk pictures, the user is clicking too quickly
        this.log("cleaning " + ($(id + " .result-picture img").length - 1) + " junk result(s) while showing " + id);
        while ($(id + " .result-picture img").length > 1) {
          $(id + " .result-picture img:first").remove();
          $(id + " .result-name div").html(""); // also kill the name
          $(id).hide(); // this will get shown again later
        }
      }
      $(id).queue("custom-SearchResultsQueue", function () {
        var each = $(this).attr("id").split("-")[3];
        $(this)
          .stop(true, true)
          .delay(25 * each)
          .show("drop", {direction: "right", distance: 50}, 250, _onShowComplete);
      }).dequeue("custom-SearchResultsQueue");
    }
    this.endFunction("animateShowSearchResults");
  },

  animateHideSearchResults : function(callback) {
    this.beginFunction("animateHideSearchResults");
    var fadesCompleted = 0, imagesDeleted = 0, _processFadeComplete = $.proxy(function () {
      fadesCompleted++;
      if (fadesCompleted == $(".cmc-search-result").length) {
        this.log("now killing pictures in _processFadeComplete");
        $(".result-picture").each($.proxy(function (index, element) {
          imagesDeleted++;
          $(element).children("img").remove();
          if (imagesDeleted == $(".result-picture").length) {
            if (callback != undefined) {
              this.assert(typeof callback == "function", "type of callback is not a function");
              callback();
            }
          }
        }, this));
      }
    }, this);
    $(".cmc-search-result").queue("custom-SearchResultsQueue", function () {
      $(this).stop(true, true).fadeOut('fast', function () {
        _processFadeComplete();
      });
    }).dequeue("custom-SearchResultsQueue");
    this.endFunction("animateHideSearchResults");
  },

  padSearchResults : function (results) {
    this.beginFunction("padSearchResults");
    // might we think about making this a constant or something?
    var maxSearchResults = $(".cmc-search-result").length, i = 0, ret = results.slice(0);
    if (results.length < maxSearchResults) {
      for (i = results.length; i < maxSearchResults; i++) {
        ret.push({id: false, name: false});
      }
    }
    this.endFunction("padSearchResults");
    return ret;
  },

  navigateToNextSearchPage : function () {
    this.beginFunction("navigateToNextSearchPage");
    var searchIndex = ++this.currentDisplayedSearchPage - 1, interval;
    this.updateSearchPagingControls();
    this.ajaxNotifyStart(); // we do this because showSearchResults expects its caller to post a notification like search()
    this.animateHideSearchResults($.proxy(function () {
      if (this.searchPageCache[searchIndex] !== undefined) {
        this.showSearchResults(this.padSearchResults(this.searchPageCache[searchIndex]));
      } else {
        this.log("next search page not ready yet, set an interval to check on it");
        interval = setInterval($.proxy(function () {
          this.log("listening for the next search page to cache...");
          if (this.searchPageCache[searchIndex] !== undefined) {
            this.log("got it! caching the search page and clearing the interval");
            this.showSearchResults(this.padSearchResults(this.searchPageCache[searchIndex]));
            clearInterval(interval);
          }
        }, this), 250);
      }
    }, this));
    this.cacheSearchPage(searchIndex + 1);
    this.endFunction("navigateToNextSearchPage");
  },

  cacheSearchPage : function(pageIndex) {
    this.beginFunction("cacheSearchPage");
    if (this.searchPageCache[pageIndex] === undefined) {
      // this is a page that we haven't cached yet
      this.log("[cacheSearchPage] fetching search page " + (pageIndex + 1));
      this.ajaxNotifyStart();
      $.ajax({
        url: "api/searchresults.php",
        data: {
          fbid: "25826994",
          searchkeys: encode64(JSON.stringify(this.SearchState)),
          page: pageIndex + 1, // page on the server is off by one
          perpage: 10
        },
        dataType: "json",
        context: {
          invokeData: {
            index: pageIndex
          },
          cmc: this // this is a terrible hack and I am sorry --zack
        },
        success: this.onCacheSearchPageSuccess,
        error: this.onCacheSearchPageServerError
      });
    }
    this.endFunction("cacheSearchPage");
  },

  onCacheSearchPageSuccess : function(data, textStatus, jqXHR) {
    this.cmc.beginFunction("onCacheSearchPageSuccess");
    //@/BEGIN/DEBUGONLYSECTION
    if (!("cmc" in this)) {
      if (CMC) {
        // definitely don't want this line floating around in production code --zack
        CMC.assert(false, '"cmc" not in this context for onCacheSearchPageSuccess');
      } // if this is unavailable, god help us all
    }
    //@/END/DEBUGONLYSECTION
    this.cmc.assert(data != undefined, "data is undefined in onCacheSearchPageSuccess");
    if("has_error" in data && data["has_error"] !== undefined && data.has_error !== null) {
      if(data.has_error) {
        // we have a known error, handle it
        this.cmc.handleSearchSuccessHasError(data);
      } else {
        this.cmc.log("[onCacheSearchPageSuccess] got data for page " + (this.invokeData.index + 1));
        if(!("searchids" in data)) {
          // peculiar. this is actually an important field -- why don't we have it?
          this.cmc.log('"searchids" was missing from the data map');
          this.cmc.searchPageCache[this.invokeData.index] = null;
        } else if(data["searchids"] === undefined) {
          // hm, this is strange. probably means no results, but we 
          // might consider logging this in the future. --zack
          this.cmc.searchPageCache[this.invokeData.index] = null;
        } else if(data["searchids"] == null) {
          // this should DEFINITELY mean that we have no results
          this.cmc.searchPageCache[this.invokeData.index] = null;
        } else {
          this.cmc.getDataForEachFBID(data.searchids, $.proxy(function (results) {
            this.cmc.searchPageCache[this.invokeData.index] = results;
            this.cmc.updateSearchPagingControls();
          }, this));
        }
      }
    } else {
      // an unknown error occurred? do something!
      this.cmc.searchPageCache[this.invokeData.index] = null; // this should stop the interval check
      // a thought from zack. if this is set to null, we'll never be able 
      // to get this page again. just a thought, but this is a bad user 
      // experience
      this.cmc.handleSearchSuccessUnknownError(data, textStatus, jqXHR);
    }
    this.cmc.updateSearchPagingControls();
    this.cmc.ajaxNotifyComplete();
    this.cmc.endFunction("onCacheSearchPageSuccess");
  },

  onCacheSearchPageServerError : function(jqXHR, textStatus, errorThrown) {
    this.beginFunction("onCacheSearchPageServerError");
    this.ajaxNotifyComplete();
    this.searchPageCache.push(null); // this should (hopefully) stop the interval check
    // we might also want to log this or surface an error message or something
    this.handleSearchServerError(jqXHR, textStatus, errorThrown);
    this.endFunction("onCacheSearchPageServerError");
  },

  navigateToPreviousSearchPage : function () {
    this.beginFunction("navigateToPreviousSearchPage");
    var fadesCompleted = 0, imagesDeleted = 0;
    this.currentDisplayedSearchPage--;
    this.updateSearchPagingControls();
    this.ajaxNotifyStart(); // we do this because showSearchResults expects its caller to post a notification like search()
    if (this.searchPageImageClearJobQueue.length > 0) {
      this.log("found " + this.searchPageImageClearJobQueue.length + " leftover image clearing jobs, stopping them");
      while (this.searchPageImageClearJobQueue.length > 0) {
        clearTimeout(this.searchPageImageClearJobQueue.pop());
      }
    }
    if (this.searchPageCache[this.currentDisplayedSearchPage - 1] !== undefined) {
      this.animateHideSearchResults($.proxy(function () {
        this.showSearchResults(this.searchPageCache[this.currentDisplayedSearchPage - 1]);
      }, this));
    } else {
      // something went horribly, horribly wrong, and we should probably know about it
      this.assert(false, "stumbled on an undefined page while navigating to the previous page");
    }
    this.updateSearchPagingControls();
    this.endFunction("navigateToPreviousSearchPage");
  },

  updateSearchPagingControls : function () {
    this.beginFunction("updateSearchPagingControls");
    this.assert(this.currentDisplayedSearchPage >= 1, "displaying search page that is negative or zero");
    $("#cmc-search-results-pagingctl-text").children(".ui-button-text").html("page " + this.currentDisplayedSearchPage);
    if (this.currentDisplayedSearchPage <= 1) {
      $("#cmc-search-results-pagingctl-prev").button("disable");
    } else {
      $("#cmc-search-results-pagingctl-prev").button("enable");
    }
    if (this.searchPageCache[this.currentDisplayedSearchPage] != null) {
      $("#cmc-search-results-pagingctl-next").button("enable");
    } else {
      $("#cmc-search-results-pagingctl-next").button("disable");
    }
    this.endFunction("updateSearchPagingControls");
  },

  animateSearchResultSelected : function (whichResult) {
    this.beginFunction("animateSearchResultSelected");
    $(".cmc-search-result").not(whichResult).each(function () {
      var _onHideComplete = function() {
        setTimeout($.proxy(function () {
          $(this).show().fadeTo(0, 1);
        }, this), 300);
      };
      $(this)
        .stop(true, true)
        .show()
        .delay(Math.floor(Math.random()*25))
        .hide("drop", {direction: "right", distance: 115, easing: "easeOutQuart"}, 350, _onHideComplete)
        .show(0)
        .fadeTo(0, 0);
    });
    setTimeout(function () {
      $("#tabs").tabs('select', 1);
    }, 285);
    this.endFunction("animateSearchResultSelected");
  }
};

FB.init({
  appId  : '153051888089898',
  status : true,
  cookie : true,
  fbml   : true
});

$(function() {
  //@/BEGIN/DEBUGONLYSECTION
  $("#debug-log").val("=== BEGIN DEBUG OUTPUT ===\n");
  //@/END/DEBUGONLYSECTION

  CMC.log("begin load callback");

  CMC.performStartupActions();

  //@/BEGIN/DEBUGONLYSECTION
  CMC.log("attaching global click event handler");
  $('*').live('click', function(event) {
    event.preventDefault();
    event.stopPropagation();
    var id = $(this).attr('id') == '' || $(this).attr('id') == undefined ? (
               $(this).parent().attr('id') == '' || $(this).parent().attr('id') == undefined ? (
                 $(this).parent().parent().attr('id') == ''  || $(this).parent().parent().attr('id') == undefined ?
                   "(unknown ID)"
                   : $(this).parent().parent().attr('id'))
                 : $(this).parent().attr('id'))
               : $(this).attr('id');
    CMC.log("click event: " + $(this).get(0).tagName.toLowerCase() + "#" + id);
  });
  //@/END/DEBUGONLYSECTION

  $("#make-profile, #make-volunteer, #make-organizer").hide();

  $(".cmc-big-button").hover(
    function() { $(this).addClass('ui-state-hover'); },
    function() { $(this).removeClass('ui-state-hover'); }
  );

  CMC.log("applying jQuery tabs");
  $("#tabs").tabs({
    fx: {
      opacity: 'toggle',
      duration: 'fast'
    }
  });
  
  CMC.log("setting up ajax spinner");
  $("#ajax-spinner")
    .hide()
    .ajaxStart(function() {
      CMC.ajaxNotifyStart();
    })
    .ajaxStop(function() {
      CMC.ajaxNotifyComplete();
    });

  CMC.log("configuring FCBKcomplete for search");
  $("#search-box-select").fcbkcomplete({
    addontab : true,
    cache : true,
    complete_text : "Start typing...",
    filter_hide : true,
    filter_selected : true,
    firstselected : true, // circumvent a selection bug
    height : 6,
    maxshownitems : 5,
    newel : true,
    onselect : function (item) { CMC.handleSearchSelect(item); },
    onremove : function (item) { CMC.handleSearchRemove(item); },
    // custom (i.e. undocumented) options here
    //cmc_icon_class : "ui-icon ui-icon-search" // broken.
    cmc_zipcode_detect : true,
    zipcode_target : "api/zipcode.php"
  });

  CMC.log("setting up tipbars");
  $("#search-tipbar-left .tipbar-link").tipTip({
    activation: 'focus',
    keepAlive: true,
    maxWidth: '230px',
    forceWidth: true,
    delay: 0,
    defaultPosition: 'bottom',
    forcePosition: true,
    content: $("#search-tipbar-left .tipbar-content").html()
  });

  $("#search-tipbar-right .tipbar-link").tipTip({
    activation: 'focus',
    keepAlive: true,
    maxWidth: '230px',
    forceWidth: true,
    delay: 0,
    defaultPosition: 'bottom',
    forcePosition: true,
    content: $("#search-tipbar-right .tipbar-content").html()
  });

  if ($.browser.msie && parseInt($.browser.version, 10) <= 7) {
    CMC.log("applying browser specific tipbar hack for IE version <= 7")
    $("#tiptip_content").css("background-color", "black");
  }

  $("#cmc-search-icon").click(function() {
    $("#cmc-search-box").children("ul").children("li.bit-input").children(".maininput").focus();
  });

  CMC.log("building page model for search results");

  $("#cmc-search-results-pagingctl-prev")
    .button({ text: false, icons: { primary: "ui-icon-circle-triangle-w" }})
    .click(function () {
      if (!$(this).button("option", "disabled")) {
        CMC.navigateToPreviousSearchPage();
      }
    });

  $("#cmc-search-results-pagingctl-next")
    .button({ text: false, icons: { primary: "ui-icon-circle-triangle-e" }})
    .click(function () {
      if (!$(this).button("option", "disabled")) {
        CMC.navigateToNextSearchPage();
      }
    });

  $("#cmc-search-results-title").hide();
  $("#cmc-search-results-noresultmsg").hide();
  $(".cmc-search-result")
    .click(function () { CMC.animateSearchResultSelected(this); })
    .each(function () { $(this).hide(); });

  // this should fix the junk picture assert on first search
  CMC.log("clearing the placeholder images");
  $(".result-picture img").remove();

  CMC.log("attempting to get facebook login status");
  CMC.ajaxNotifyStart();
  FB.getLoginStatus(function(response) {
    CMC.ajaxNotifyComplete();
    if (response.session) {
      CMC.loggedInUser = response.session.uid;
      CMC.ajaxNotifyStart();
      FB.api('/me/friends', function(friends) {
        CMC.ajaxNotifyComplete();
        CMC.friends = friends.data;
      });
    }
  });

  CMC.log("setting up dialog boxes");
  $("#copyrights-dialog").dialog({
    autoOpen: false,
    draggable: false,
    position: [227, 50],
    resizable: false,
    title: "Christian Missions Connector v" + CMC.version,
    open: function() {
      CMC.dialogOpen(this);
    },
    close: function() {
      CMC.dialogClose(this);
    }
  });

  $("#copyrights").click(function() {
    $("#copyrights-dialog").dialog('open');
  });

  $("#report-problem-dialog").dialog({
    autoOpen: false,
    draggable: false,
    position: [177, 90],
    resizable: false,
    width: 400,
    open: function() {
      CMC.dialogOpen(this);
    },
    close: function() {
      CMC.dialogClose(this);
      if ($("#report-problem-message").val().length <= 0) {
        $("#report-problem-characters-left").hide();
      }
    }
  });

  $("#report-problem").click(function() {
    $("#report-problem-dialog").dialog('open');
  });

  $("#report-problem-submit")
    .button()
    .click(function() {
      void false;
    });

  $("#report-problem-characters-left").hide();

  $("#report-problem-message")
    .click(function() {
      CMC.recalculateProblemMessageLimit();
    })
    .keyup(function() {
      CMC.recalculateProblemMessageLimit();
    })
    .keypress(function() {
      CMC.recalculateProblemMessageLimit();
    });

  // this should be the last thing that happens
  CMC.log("load callback complete, fading in canvas");
  $("#loading").fadeOut(function() {
    $("#tabs").hide().fadeIn(function() {
      $("#cmc-footer").hide().delay(150).fadeIn();
    });
  });

});
// vim: ai:et:ts=2:sw=2
