<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<?php

// put PHP code here

function cmc_js_load($src) {
  echo '<script type="text/javascript" src="'.$src.'"></script>';
}

function cmc_library_load_jquery($ver) {
  cmc_js_load('http://code.jquery.com/jquery-'.$ver.'.js');
}

function cmc_library_load_jquery_ui($ver) {
  $src = 'https://ajax.googleapis.com/ajax/libs/jqueryui/'.$ver.'/jquery-ui.js';
  cmc_js_load($src);
}

function cmc_library_load_jquery_ui_theme($ver, $theme, $custom=false) {
  $href = '';
  if($custom) {
    // NOTE: $theme is ignored for custom themes -- perhaps allow multiple 
    // custom themes in the future? -zack
    $href .= 'css/custom-theme/jquery-ui-' . $ver . '.custom.css';
  } else {
    $href .= 'http://ajax.googleapis.com/ajax/libs/jqueryui/';
    $href .= $ver;
    $href .= '/themes/';
    $href .= $theme;
    $href .= '/jquery-ui.css';
  }
  echo '<link type="text/css" href="'.$href.'" rel="stylesheet" />';
}

function cmc_jquery_startup($jquery_version, $jquery_ui_version, $theme) {
  if($theme == 'custom' || $theme == 'custom-theme') {
    cmc_library_load_jquery_ui_theme($jquery_ui_version, $theme, true);
  } else {
    cmc_library_load_jquery_ui_theme($jquery_ui_version, $theme);
  }
  cmc_library_load_jquery($jquery_version);
  cmc_library_load_jquery_ui($jquery_ui_version);
  echo "\n";
}

function
cmc_big_button($title, $subtext=FALSE, $onclick=FALSE, $img=FALSE,
               $imgstyle=FALSE, $imgw=75, $imgh=75) {
  echo '<a href="#" class="cmc-button-link"';
  if($onclick) {
    echo ' onclick="'.$onclick.'"';
  }
  echo '>';

  echo '<div class="ui-state-default ui-corner-all cmc-big-button">';
  echo '<div class="cmc-big-button-icon">';

  if($img) {
    echo "<img src=\"$img\" width=\"$imgw\" height=\"$imgh\"";
    if($imgstyle) {
      echo " style=\"$imgstyle\"";
    }
    echo " />";
  }

  echo '</div>'; // cmc-big-button-icon

  echo '<h1 class="cmc-big-button-text"';
  if(!$subtext) {
    echo ' style="padding-top: 12px;"';
  }
  echo '>';
  echo $title;
  echo '</h1>';

  if($subtext) {
    echo '<p class="cmc-big-button-text">';
    echo $subtext;
    echo '</p>';
  }

  echo '</div>'; // cmc-big-button

  echo '</a>';
}

?>

<html>
  <head>
    <meta charset="utf-8">
    <title>Christian Missions Connector</title>
  </head>
  <body>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <!-- Include jQuery stuff and link stylesheet to the specified theme -->
    <?php cmc_jquery_startup("1.6.1", "1.8.11", "custom-theme"); ?>
    <link rel="stylesheet" href="fcbkcomplete-style.css" type="text/css"
          media="screen" charset="utf-8" />
    <link rel="stylesheet" href="tipTip.css" type="text/css" />
    <script src="jquery.fcbkcomplete.js" type="text/javascript"></script>
    <script src="jquery.tipTip.js" type="text/javascript"></script>
    <!-- imagesLoaded plugin obtained from https://gist.github.com/268257 -->
    <script src="jquery.imagesLoaded.js" type="text/javascript"></script>
    <div id="fb-root"></div>
    <script src="https://connect.facebook.net/en_US/all.js"></script>
    <script src="base64.js"></script>
    <script src="json2-min.js"></script>
    <script type="text/javascript">
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
        SearchState : {},
        // startup configuration settings
        StartupConfig : {
          attachDebugLogHandlersByDefault : true
        },

        // methods
        performStartupActions : function () {
          if (this.StartupConfig.attachDebugLogHandlersByDefault) {
            this.attachDebugHandlers(this.DebugMode);
          }
        },

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
            if (!condition) {
              var message = "ASSERT FAILED!\nIf you're reporting this, please use this message:\n" + bugmsg;
              this.log(message);
            }
          },

          beginFunction : function(fnName) {
            this.log("begin function: " + fnName);
            // check for scope corruption
            this.assert(this === CMC, "Scope corruption detected! this === CMC failed!");
          },

          endFunction : function(fnName, fnReturnValue) {
            if (fnReturnValue !== undefined) {
              this.log("end function: " + fnName + ", returning " + fnReturnValue.toString());
            } else {
              this.log("end function: " + fnName);
            }
          }
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
          this.log = $.noop;
          this.error = $.noop;
          this.assert = $.noop;
          this.beginFunction = $.noop;
          this.endFunction = $.noop;
          $("#debug-section").hide();
        },

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

        ajaxNotifyStart : function() {
          if (this.requestsOutstanding == 0) {
            this.showAjaxSpinner();
          }
          this.requestsOutstanding++;
        },

        ajaxNotifyComplete : function() {
          if (this.requestsOutstanding > 0) {
            this.requestsOutstanding--;
            if (this.requestsOutstanding == 0) {
              this.hideAjaxSpinner();
            }
          } else {
            // this is a bug, and needs to be logged. --zack
          }
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
          var value = jQuery.parseJSON(item)._value;
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
          this.endFunction("handleSearchSelect");
        },

        handleSearchRemove : function(item) {
          this.beginFunction("handleSearchRemove");
          var value = jQuery.parseJSON(item)._value;
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
            var _fadeoutsCompleted = 0, _onSearchFadeoutComplete = function () {
              if (++_fadeoutsCompleted == 2) {
                $("#cmc-search-results").hide();
              }
            };
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

        getDataForEachFBID : function (fbids, callback) {
          this.beginFunction("getDataForEachFBID");
          var results = new Array(fbids.length), requestsCompleted = 0, idPosMap = {};
          var __notifyComplete = function () {
            requestsCompleted++;
            if (requestsCompleted == fbids.length) {
              callback(results);
            }
          };
          for(var each in fbids) {
            idPosMap[fbids[each]] = each;
            this.ajaxNotifyStart();
            FB.api('/' + fbids[each], $.proxy(function (response) {
              this.ajaxNotifyComplete();
              results[idPosMap[response.id]] = response;
              __notifyComplete();
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
            this.ajaxNotifyComplete(); // finish the one we started at the beginning of the search
            $("#cmc-search-results-noresultmsg").fadeIn();
          } else {
            var imageLoadsCompleted = 0, __notifyImageLoadCompleted = $.proxy(function() {
              imageLoadsCompleted++;
              if(imageLoadsCompleted == results.length) {
                this.animateShowSearchResults(results);
                this.ajaxNotifyComplete(); // finish the one we started at the beginning of the search
              } else if (imageLoadsCompleted >= results.length) {
                this.assert(false, "loading more images than we have results for");
              }
            }, this);
            this.assert(results.length <= 10, "more than 10 results passed to showSearchResults");
            this.assert($(".result-picture img").length == 0,
                        "found " + $(".result-picture img").length + " junk pictures lying around");
            for(var each in results) {
              var id = "#cmc-search-result-" + each;
              this.ajaxNotifyStart();
              $(id).children(".result-name").html(results[each].name);
              $(id).children("div.result-picture").children("img").remove();
              $("<img />")
                .attr("src", "http://graph.facebook.com/"+results[each].id+"/picture")
                .attr("cmcid", id)
                .addClass("srpic")
                .one('load', $.proxy(function(event) {
                  $($(event.target).attr("cmcid")).children("div.result-picture").append($(event.target));
                  this.ajaxNotifyComplete();
                  __notifyImageLoadCompleted();
                }, this));
            } // end for
          } // end else
          this.endFunction("showSearchResults");
        },

        animateShowSearchResults : function (results) {
          this.beginFunction("animateShowSearchResults");
          this.log("animating resultset starting with " + results[0].name);
          $(".cmc-search-result").queue("custom-SearchResultsQueue", []);
          for(var each in results) {
            var id = "#cmc-search-result-" + each, maxSearchResults = $(".cmc-search-result").length,
                showsCompleted = 0, _onShowComplete = $.proxy(function () {
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
                    if (!$("#cmc-search-results").is(":visible")) {
                      this.log("this search has displayed, but is veiled; deleting pictures");
                      $("img.srpic").remove();
                    }
                  }
                }, this);
            $(id).queue("custom-SearchResultsQueue", function () {
              $(this).stop(true, true);
            }).dequeue("custom-SearchResultsQueue");
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
          $(".cmc-search-result").queue("custom-SearchResultsQueue", []);
          $(".cmc-search-result").queue("custom-SearchResultsQueue", function () {
            $(this).stop(true, true).fadeOut('fast', function () {
              _processFadeComplete();
            });
          }).dequeue("custom-SearchResultsQueue");
          this.endFunction("animateHideSearchResults");
        },

        navigateToNextSearchPage : function () {
          this.beginFunction("navigateToNextSearchPage");
          var searchIndex = ++this.currentDisplayedSearchPage - 1, interval;
          this.updateSearchPagingControls();
          this.animateHideSearchResults($.proxy(function () {
            if (this.searchPageCache[searchIndex] !== undefined) {
              this.showSearchResults(this.searchPageCache[searchIndex]);
            } else {
              this.log("next search page not ready yet, set an interval to check on it");
              interval = setInterval($.proxy(function () {
                this.log("listening for the next search page to cache...");
                if (this.searchPageCache[searchIndex] !== undefined) {
                  this.log("got it! caching the search page and clearing the interval");
                  this.showSearchResults(this.searchPageCache[searchIndex]);
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
          if (!("cmc" in this)) {
            if (CMC) {
              CMC.assert(false, '"cmc" not in this context for onCacheSearchPageSuccess');
            } // if this is unavailable, god help us all
          }
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
        }
      };

      FB.init({
        appId  : '153051888089898',
        status : true,
        cookie : true,
        fbml   : true
      });

      $(function() {
        $("#debug-log").val("=== BEGIN DEBUG OUTPUT ===\n");

        CMC.log("begin load callback");

        CMC.performStartupActions();

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
        $(".cmc-search-result").each(function () { $(this).hide(); });

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
    </script>
    <script type="text/javascript">
      $(function() {
        CMC.log("loading admin only code");
        $("#secret-hideout-dialog").dialog({
          autoOpen: false,
          draggable: false,
          position: [25, 25],
          resizable: false,
          height: 465,
          width: 700,
          open: function() {
            CMC.dialogOpen(this);
          },
          close: function() {
            CMC.dialogClose(this);
          }
        });

        $("#secret-hideout").click(function() {
          $("#secret-hideout-dialog").dialog('open');
        });

        CMC.log("admin load complete");
      });
    </script>
    <!-- Custom CSS markup goes here -->
    <style type="text/css">

      body {
        font: 11px "Trebuchet MS", sans-serif;
      }

      h1 {
        font: 18px "Trebuchet MS", sans-serif;
      }

      img {
        border: 0px none;
      }

      #tabs, #loading {
        height: 500px;
      }

      #ajax-spinner {
        position: absolute;
        float: right;
        top: 5px;
        right: 2px;
        margin-top: 6px;
        margin-right: 5px;
      }

      #debug-section {
        display: inline-block;
        margin-left: 0px auto;
        margin-top: 20px;
        z-index: -1;
      }

      .cmc-infobar {
        padding-left: 0.7em;
        padding-right: 0.7em;
      }

      .cmc-infobar-text {
        display: inline-block;
      }

      .cmc-infobar-icon {
        float: left;
        margin-right: 0.3em;
      }

      .cmc-big-button {
        height: 75px;
        padding-top: 7px;
        padding-bottom: 7px;
        margin-bottom: 0.3em;
      }

      .cmc-big-button-icon {
        padding-left: 0.7em;
        padding-right: 1.1em;
        float: left;
        position: relative;
        display: inline-block;
        height: 75px;
        width: 75px;
      }

      #search-box {
        position: relative;
        left: -0.7em;
        width: 500px;
        margin-left: auto;
        margin-right: auto;
      }

      .cmc-search-icon {
        position: absolute;
        float: right;
        clear: both;
        right: -5px;
        margin-top: 7px;
        margin-left: 0px;
        vertical-align: middle;
      }

      .tipbar-link {
        text-decoration: none;
      }

      .tipbar-content h4, #recent-searches h4, #saved-searches h4 {
        margin-top: 5px !important;
        margin-bottom: 3px !important;
      }

      .inner-tipbar-content li {
        margin: 15px;
        line-height: 0px;
      }
      
      h1.cmc-big-button-text {
        margin-bottom: 5px;
      }

      p.cmc-big-button-text {
        margin-top: 7px;
      }

      a.cmc-button-link {
        text-decoration: none;
      }

      #cmc-search-results-title {
        margin-bottom: 24px;
      }

      #cmc-search-results-pagingctl {
        position: absolute;
        right: 18px;
        margin-top: -1px;
      }

      #cmc-search-results-pagingctl-text {
        position: relative;
        display: inline-block;
        top: -8px;
        margin-right: 0.1em;
        text-align: center;
        vertical-align: middle;
        height: 20px;
        width: 75px !important;
      }

      .cmc-pagingctl-button {
        height: 20px;
        width: 20px !important;
      }

      .cmc-search-result {
        height: 60px;
        width: 200px;
        position: relative;
      }

      .cmc-search-result .result-picture {
        display: inline;
        float: left;
        margin-right: 7px;
        width: 50px;
        height: 50px;
      }

      .cmc-search-result .result-name {
        font-weight: bold;
      }

      .cmc-search-result-col {
        display: block;
        position: absolute;
      }

      #cmc-search-result-col-0 {
        margin-left: 0px;
      }

      #cmc-search-result-col-1 {
        left: 0px;
        margin-left: 50%;
      }

      img.srloading {
        z-index: -1;
        display: none;
      }

      #cmc-footer a {
        text-decoration: none;
        color: #102030;
      }

      #cmc-footer .leftside {
        position: absolute;
        left: 8px;
      }

      #cmc-footer .rightside {
        position: absolute;
        right: 8px;
      }

      #report-problem-characters-left {
        float: right;
        margin-top: 7px;
        margin-right: 14px;
      }
      
    </style>
    <!-- HTML markup goes here -->
    <div id="loading">
      <div style="vertical-align: middle; text-align: center; display: block">
        <div style="margin-top: 80px; margin-left: auto; margin-right: auto">
          <img src="loading-spinner.gif" />
        </div>
      </div>
    </div>
    <div id="tabs" style="display: none">
      <ul>
        <li><a href="#welcome-tab">Welcome!</a></li>
        <li><a href="#profile-tab">Profile</a></li>
        <li><a href="#trips-tab">Trips</a></li>
        <li><a href="#search-tab">Search</a></li>
        <li><a href="#network-tab">My Network</a></li>
        <li><a href="#invite-tab">Invite</a></li>
      </ul>
      <div id="ajax-spinner">
        <img src="ajax-spinner.gif" />
      </div>
      <div id="welcome-tab">
        <h1>Welcome to Christian Missions Connector.</h1>
        <p>Are you interested in missions work? Do you want to connect with people and organizations who share your passion for missions? Whether you want to find a missions organization, start a mission team, join a mission team or just connect with others who have a passion for missions, Christian Missions Connector can help.</p>
      </div>
      <div id="profile-tab">
        <div id="make-volunteer">
          <h1>Cool! You're a volunteer.</h1>
          <p>We're excited you're here! Now we'd like to sync with your Facebook profile so we can connect you with mission trips all over the world. We'll also let you know if anyone invites you to join their trip!</p>
        </div>
        <div id="make-organizer">
          <h1>Awesome! You're an organizer.</h1>
          <p>It's great to have you onboard! We'd like to take a chance to sync with your Facebook profile so we can connect you to volunteers all over the world. If you have a Facebook page, you can link that too. We'll be sure to let you know when people join your trips!</p>
        </div>
        <div id="make-profile">
          <h1>Create a Profile: Who Are You?</h1>
          <p>Don't worry. If you change your mind, you can come back later and pick something else.</p>
          <?php
            cmc_big_button(
              "I'm a volunteer",
              "I'm interested in supporting or going on mission trips",
              "CMC.page('#make-profile', '#make-volunteer');",
              "icon-volunteer.png",
              "padding-top: 5px;",
              65, 65
            );
            cmc_big_button(
              "I'm an organizer",
              "I lead a missions team or represent an organization",
              "CMC.page('#make-profile', '#make-organizer');",
              "icon-organizer.png"
            );
          ?>
        </div>
        <div id="no-profile">
          <div class="ui-state-highlight ui-corner-all ui-widget cmc-infobar">
            <p class="cmc-infobar-text">
              <span class="ui-icon ui-icon-info cmc-infobar-icon"></span>
              <strong>Oops!</strong>
              You haven't created a profile yet! Create one now and get involved.
            </p>
          </div>
          <h1>
            <a href="#" onclick="CMC.page('#no-profile', '#make-profile');">Create a Profile Now &gt;&gt;</a>
          </h1>
        </div>
      </div>
      <div id="trips-tab">
        <p>Proin elit arcu, rutrum commodo, vehicula tempus, commodo a, risus. Curabitur nec arcu. Donec sollicitudin mi sit amet mauris. Nam elementum quam ullamcorper ante. Etiam aliquet massa et lorem. Mauris dapibus lacus auctor risus. Aenean tempor ullamcorper leo. Vivamus sed magna quis ligula eleifend adipiscing. Duis orci. Aliquam sodales tortor vitae ipsum. Aliquam nulla. Duis aliquam molestie erat. Ut et mauris vel pede varius sollicitudin. Sed ut dolor nec orci tincidunt interdum. Phasellus ipsum. Nunc tristique tempus lectus.</p>
      </div>
      <div id="search-tab">
        <div id="search-box">
          <div id="search-tipbar" style="position: relative; height: 16px">
            <div id="search-tipbar-left" style="position: absolute; left: 2px;">
              <a class="tipbar-link" href="#">Search history...</a>
              <div class="tipbar-content" style="display: none">
                <div id="recent-searches">
                  <h4>Recent Searches</h4>
                  <p>You have no recent searches. Perform a search first!</p>
                </div>
                <div id="search-history-spacer" style="display: block; height: 3px;"></div>
                <div id="saved-searches">
                  <h4>Saved Searches</h4>
                  <p>You haven't saved any searches yet. Perform a search, then click the save icon.</p>
                </div>
              </div>
            </div>
            <div id="search-tipbar-right" style="position: absolute; right: -10px;">
              <a class="tipbar-link" href="#">Need some help?</a>
              <div class="tipbar-content" style="display: none">
                <div class="inner-tipbar-content">
                  Type in the characteristics of the sort of volunteer or mission trip you are looking for. Try searching for things like:
                    <li>Profession (ex, translator)</li>
                    <li>Skills (ex, computer science)</li>
                    <li>Countries (ex, Nicaragua)</li>
                    <li>US Zip Code (ex, 98034)</li>
                    <li>...and more!</li>
                </div>
              </div>
            </div>
          </div>
          <div id="search-box-box">
            <div id="cmc-search-icon" class="ui-icon ui-icon-search cmc-search-icon"></div>
            <div id="cmc-search-box">
              <select id="search-box-select" name="search-box-select">
                <option value="1">Advanced Practice Nursing</option>
                <option value="2">Dental Professional</option>
                <option value="3">Medical Educator</option>
                <option value="4">Mental Health Professional</option>
                <option value="5">Nurse</option>
                <option value="6">Optometrist or Opthalmologist</option>
                <option value="7">Pharmacist</option>
                <option value="8">Physician</option>
                <option value="9">Physician Assistant</option>
                <option value="10">Physical or Occupational Therapist</option>
                <option value="11">Public Health/Community Development Worker</option>
                <option value="12">Speech Therapist</option>
                <option value="13">Other</option>
                <option value="14">General Help/Labor</option>
                <option value="15">Team Leader/Primary Organizer</option>
                <option value="16">Account and/or Business Management</option>
                <option value="17">Skilled Construction and/or Maintenance</option>
                <option value="18">Computer Science/Other Technical</option>
                <option value="19">Agriculture and/or Animal Husbandry</option>
                <option value="45">Mechanic</option>
                <option value="46">Office/Secretarial</option>
                <option value="47">Teaching</option>
                <option value="48">Veterinary</option>
                <option value="49">Water Supply Improvement</option>
                <option value="50">Writing and/or Translating</option>
                <option value="52">Engineering</option>
                <option value="20">Team Spiritual Leader</option>
                <option value="21">Individual Outreach (Prayer or Counseling)</option>
                <option value="22">Evangelism</option>
                <option value="44">Worship Team</option>
                <option value="51">Public Speaking</option>
              </select>
            </div>
          </div>
        </div>
        <div id="cmc-search-results-spacer" style="display: block; height: 16px"></div>
        <div id="cmc-search-results">
          <div id="cmc-search-results-title" style="display: none">
            <div id="cmc-search-results-pagingctl">
              <div id="cmc-search-results-pagingctl-prev" class="cmc-pagingctl-button"></div>
              <div id="cmc-search-results-pagingctl-text" class="ui-state-default ui-corner-all">
                <!-- placeholder text, should be localized elsewhere -->
                <span class="ui-button-text">page 0</span>
              </div>
              <div id="cmc-search-results-pagingctl-next" class="cmc-pagingctl-button"></div>
            </div>
            <h1 id="cmc-search-results-title-text">Search Results:</h1>
          </div>
          <div id="cmc-search-results-subtitles">
            <h2 id="cmc-search-results-noresultmsg" style="display: none">Sorry, no results were found. :(</h2>
          </div>
          <div id="cmc-search-result-list">
            <div id="cmc-search-result-col-0" class="cmc-search-result-col">
              <div id="cmc-search-result-0" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-1" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-2" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-3" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-4" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
            </div>
            <div id="cmc-search-result-col-1" class="cmc-search-result-col">
              <div id="cmc-search-result-5" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-6" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-7" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-8" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
              <div id="cmc-search-result-9" class="cmc-search-result">
                <div class="result-picture">
                  <img src="ajax-spinner.gif" class="srpic srloading"/>
                </div>
                <div class="result-name">Search Result</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="network-tab">
        <p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
      </div>
      <div id="invite-tab">
        <p>Mauris eleifend est et turpis. Duis id erat. Suspendisse potenti. Aliquam vulputate, pede vel vehicula accumsan, mi neque rutrum erat, eu congue orci lorem eget lorem. Vestibulum non ante. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce sodales. Quisque eu urna vel enim commodo pellentesque. Praesent eu risus hendrerit ligula tempus pretium. Curabitur lorem enim, pretium nec, feugiat nec, luctus a, lacus.</p>
        <p>Duis cursus. Maecenas ligula eros, blandit nec, pharetra at, semper at, magna. Nullam ac lacus. Nulla facilisi. Praesent viverra justo vitae neque. Praesent blandit adipiscing velit. Suspendisse potenti. Donec mattis, pede vel pharetra blandit, magna ligula faucibus eros, id euismod lacus dolor eget odio. Nam scelerisque. Donec non libero sed nulla mattis commodo. Ut sagittis. Donec nisi lectus, feugiat porttitor, tempor ac, tempor vitae, pede. Aenean vehicula velit eu tellus interdum rutrum. Maecenas commodo. Pellentesque nec elit. Fusce in lacus. Vivamus a libero vitae lectus hendrerit hendrerit.</p>
      </div>
    </div>
    <div id="cmc-footer" style="display: none">
      <div class="leftside">
        <a href="#" id="secret-hideout">Admin</a> :
        <a href="#" id="report-problem">Report a Problem</a> : <a href="#" id="contact-link">Contact Us</a>
      </div>
      <div class="rightside">
        <a href="#" id="copyrights">Copyrights</a> :
        <a href="http://www.tangiblesoft.net/" target="_blank" id="tangible-link">Tangible, LLC</a>
      </div>
    </div>
    <!-- Dialogs and such should go here -->
    <div id="dialogs" style="display: none">
      <div id="copyrights-dialog" title="Copyrights">
        <p>Christian Missions Connector is Copyright 2009-2011 Tangible, LLC. All rights reserved.</p>
        <p>Christian Missions Connector uses the jQuery and jQuery UI libraries. For more information, visit <a href="http://www.jquery.com" target="_blank">www.jquery.com</a>.</p>
        <p>Portions adapted from FCBKcomplete 2.7.5 and TipTip 1.3. FCBKcomplete is Copyright 2010 Emposha (<a href="http://www.emposha.com" target="_blank">www.emposha.com</a>). TipTip is Copyright 2011 Drew Wilson (<a href="http://code.drewwilson.com/entry/tiptip-jquery-plugin" target="_blank">code.drewwilson.com</a>). Both are used and modified with permission under the <a href="http://www.opensource.org/licenses/mit-license.php" target="_blank">MIT license.</a></p>
        <p>Contains content obtained from The Noun Project (<a href="http://www.thenounproject.com" target="_blank">www.thenounproject.com</a>). "Community" reproduced under the Creative Commons Attribution 3.0 Unported license. For licensing information, please visit <a href="http://creativecommons.org/licenses/by/3.0/" target="_blank">http://creativecommons.org/licenses/by/3.0/</a>.</p>
        <p>"Arriving Flights" by Roger Cook and Don Shanosky, 1974. Obtained from the public domain. </p>
      </div>
      <div id="report-problem-dialog" title="What seems to be the matter?">
        <p>Tell us what's wrong, and we'll look into it right away.</p>
        <form id="report-problem-form">
          <textarea id="report-problem-message" height="4" cols="55" style="width: 98%;"></textarea>
          <div style="float: right; margin-right: 1px;" id="report-problem-submit">Submit</div>
          <div id="report-problem-characters-left" class="ui-state-disabled">
            300 characters left<!-- just some placeholder text -->
          </div>
        </form>
      </div>
      <div id="secret-hideout-dialog" title="Administration">
        <p>This is an area for magical unicorns and rainbows.</p>
      </div>
    </div>
    <!-- The debug log. Should not be displayed by default. Enable via the admin panel. -->
    <div id="debug-section" style="display: none">
      <textarea id="debug-log" rows="10" cols="80" spellcheck="false">
      Please wait, loading debug console...
      </textarea>
    </div>
    <!-- Do not place HTML markup below this line -->
  </body>
</html>
