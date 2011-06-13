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
    <title><?php echo STR_APP_NAME; ?></title>
  </head>
  <body>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <!-- Include jQuery stuff and link stylesheet to the specified theme -->
    <?php cmc_jquery_startup("1.5.1", "1.8.11", "custom-theme"); ?>
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

        // methods
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
          CMC.dialogsOpen++;
          CMC.closeAllDialogs(dialog);
          if ($.support.opacity && CMC.dialogsOpen == 1) {
            $("#tabs, #cmc-footer").fadeTo('fast', 0.5);
          }
        },

        dialogClose : function(dialog) {
          if ($.support.opacity && CMC.dialogsOpen == 1) {
            $("#tabs, #cmc-footer").fadeTo('fast', 1.0);
          }
          CMC.dialogsOpen--;
        },

        showAjaxSpinner : function() {
          $("#ajax-spinner").show();
        },

        hideAjaxSpinner : function() {
          $("#ajax-spinner").hide();
        },

        ajaxNotifyStart : function() {
          if (CMC.requestsOutstanding == 0) {
            CMC.showAjaxSpinner();
          }
          CMC.requestsOutstanding++;
        },

        ajaxNotifyComplete : function() {
          if (CMC.requestsOutstanding > 0) {
            CMC.requestsOutstanding--;
            if (CMC.requestsOutstanding == 0) {
              CMC.hideAjaxSpinner();
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
          CMC.recalculateTextareaLimit(
            "#report-problem-message",
            "#report-problem-characters-left",
            limit
          );
          $("#report-problem-characters-left").fadeIn();
        },

        handleSearchSelect : function(item) {
          var value = jQuery.parseJSON(item)._value;
          if (value.substring(0,2) == "!!") {
            // this is a special value, we handle these differently
            if (value.substring(2,3) == "z") { // this detection could definitely be better
              // it's a zipcode
              if (CMC.SearchState.z == undefined) {
                CMC.SearchState.z = [value.substring(4,9), value.substring(10, value.length)];
              } else {
                // we have a problem, you can't have more than one zipcode
              }
            }
          } else {
            // this is a text item
          }
          CMC.search();
        },

        handleSearchRemove : function(item) {
          var value = jQuery.parseJSON(item)._value;
          if (value.substring(0,2) == "!!") {
            // this is a special value, we handle these differently
            if (value.substring(2,3) == "z") {
              // it's a zipcode. we don't care what it is, just nuke it
              delete CMC.SearchState.z;
            }
          } else {
            // this is a text item
          }
          CMC.search();
        },

        search : function () {
          CMC.searchPageCache = [];
          CMC.currentDisplayedSearchPage = 1;
          CMC.updateSearchPagingControls();
          $(".cmc-search-result").each(function () { $(this).fadeOut('fast'); });
          if (Object.keys(CMC.SearchState).length == 0) {
            // search is now blank. hide the results panels
            $("#cmc-search-results-title").fadeOut();
            $("#cmc-search-results-noresultmsg").fadeOut();
          } else {
            // otherwise, we have a new search to perform
            CMC.ajaxNotifyStart(); // one for good measure, we want the spinner for the whole search
            $.ajax({
              url: "api/searchresults.php",
              data: {
                fbid: "25826994",
                searchkeys: encode64(JSON.stringify(CMC.SearchState)),
                page: CMC.currentDisplayedSearchPage,
                perpage: 20
              },
              dataType: "json",
              success: function(data, textStatus, jqXHR) {
                if(data.has_error !== undefined && data.has_error !== null) {
                  if(data.has_error) {
                    // we have a known error, handle it
                  } else {
                    if(data.searchids === undefined) {
                      // hm, this is strange. probably means no results, but we 
                      // might consider logging this in the future. --zack
                      CMC.showSearchResults(null);
                    } else if(data.searchids === null) {
                      // this should DEFINITELY mean that we have no results
                      CMC.showSearchResults(null);
                    } else {
                      var searchResults = data.searchids.length > 10 ? data.searchids.slice(0, 10) : data.searchids;
                      CMC.searchPageCache[1] = data.searchids.length > 10 ? data.searchids.slice(10) : null;
                      CMC.getDataForEachFBID(searchResults, function (results) {
                        CMC.searchPageCache[0] = results;
                        CMC.showSearchResults(results);
                      });
                      if (data.searchids.length > 10 ) {
                        CMC.getDataForEachFBID(CMC.searchPageCache[1], function (results) {
                          CMC.searchPageCache[1] = results;
                        });
                      }
                    }
                  }
                } else {
                  // an unknown error occurred? do something!
                }
                CMC.updateSearchPagingControls();
              },
              error: function(jqXHR, textStatus, errorThrown) {
                CMC.ajaxNotifyComplete();
                // we might also want to log this or surface an error message or something
              }
            });

            $("#cmc-search-results-title").fadeIn();
          }
        },

        getDataForEachFBID : function (fbids, callback) {
          var results = new Array(fbids.length), requestsCompleted = 0, idPosMap = {};
          var __notifyComplete = function () {
            requestsCompleted++;
            if (requestsCompleted == fbids.length) {
              callback(results);
            }
          };
          for(var each in fbids) {
            idPosMap[fbids[each]] = each;
            CMC.ajaxNotifyStart();
            FB.api('/' + fbids[each], function (response) {
              CMC.ajaxNotifyComplete();
              results[idPosMap[response.id]] = response;
              __notifyComplete();
            });
          }
        },

        showSearchResults : function (results) {
          if (results === undefined) {
            // this is a bug! do NOT pass this function undefined! say null to 
            // inform it that you have no results!
          } else if (results == null || results.length == 0) {
            // no results
            $("#cmc-search-results-noresultmsg").fadeIn();
          } else {
            var imageLoadsCompleted = 0, __notifyImageLoadCompleted = function() {
              imageLoadsCompleted++;
              if(imageLoadsCompleted == results.length) {
                CMC.animateShowSearchResults(results);
                CMC.ajaxNotifyComplete(); // finish the one we started at the beginning of the search
              } else if (imageLoadsCompleted >= results.length) {
                // loading more images than we have results for? bug. log it.
              }
            };
            for(var each in results) {
              var id = "#cmc-search-result-" + each;
              CMC.ajaxNotifyStart();
              $(id).children(".result-name").html(results[each].name);
              $(id).children("div.result-picture").children("img").remove();
              $("<img />")
                .attr("src", "http://graph.facebook.com/"+results[each].id+"/picture")
                .attr("cmcid", id)
                .addClass("srpic")
                .one('load', function() {
                  $($(this).attr("cmcid")).children("div.result-picture").append($(this));
                  CMC.ajaxNotifyComplete();
                  __notifyImageLoadCompleted();
                });
            } // end for
          } // end else
        },

        animateShowSearchResults : function (results) {
          for(var each in results) {
            $("#cmc-search-result-" + each)
              .delay(25 * each)
              .show("drop", {direction: "right", distance: 50}, 250, null);
          }
        },

        navigateToNextSearchPage : function () {
          var fadesCompleted = 0, imagesDeleted = 0, searchIndex = ++CMC.currentDisplayedSearchPage - 1, interval;
          CMC.updateSearchPagingControls();
          $(".cmc-search-result").each(function () {
            $(this).fadeOut('fast', function () {
              fadesCompleted++;
              if (fadesCompleted == $(".cmc-search-result").length) {
                $(".result-picture").each(function () {
                  imagesDeleted++;
                  $(this).children("img").remove();
                  if (imagesDeleted == $(".result-picture").length) {
                    if (CMC.searchPageCache[searchIndex] !== undefined) {
                      CMC.showSearchResults(CMC.searchPageCache[searchIndex]);
                    } else {
                      // if it's not ready yet, set a timeout to check on it
                      interval = setInterval(function () {
                        if (CMC.searchPageCache[searchIndex] !== undefined) {
                          CMC.showSearchResults(CMC.searchPageCache[searchIndex]);
                          clearInterval(interval);
                        }
                      }, 250);
                    }
                  }
                });
              }
            });
          });
          if(CMC.searchPageCache[searchIndex + 1] === undefined) {
            // this is a page that we haven't cached yet
            CMC.ajaxNotifyStart();
            $.ajax({
              url: "api/searchresults.php",
              data: {
                fbid: "25826994",
                searchkeys: encode64(JSON.stringify(CMC.SearchState)),
                page: searchIndex + 2, // page on the server is off by one
                perpage: 10
              },
              dataType: "json",
              success: function(data, textStatus, jqXHR) {
                if(data.has_error !== undefined && data.has_error !== null) {
                  if(data.has_error) {
                    // we have a known error, handle it
                  } else {
                    if(data["searchids"] === undefined) {
                      // hm, this is strange. probably means no results, but we 
                      // might consider logging this in the future. --zack
                      CMC.searchPageCache[searchIndex + 1] = null;
                    } else if(data.searchids == null) {
                      // this should DEFINITELY mean that we have no results
                      CMC.searchPageCache[searchIndex + 1] = null;
                    } else {
                      CMC.getDataForEachFBID(data.searchids, function (results) {
                        CMC.searchPageCache[searchIndex + 1] = results;
                      });
                    }
                  }
                } else {
                  CMC.searchPageCache[searchIndex + 1] = null; // this should stop the interval check
                  // an unknown error occurred? do something!
                }
                CMC.updateSearchPagingControls();
                CMC.ajaxNotifyComplete();
              },
              error: function(jqXHR, textStatus, errorThrown) {
                CMC.ajaxNotifyComplete();
                CMC.searchPageCache.push(null); // this should (hopefully) stop the interval check
                // we might also want to log this or surface an error message or something
              }
            });
          }
        },

        navigateToPreviousSearchPage : function () {
          var fadesCompleted = 0, imagesDeleted = 0;
          CMC.currentDisplayedSearchPage--;
          CMC.updateSearchPagingControls();
          if (CMC.searchPageCache[CMC.currentDisplayedSearchPage - 1] !== undefined) {
            $(".cmc-search-result").each(function () {
              $(this).fadeOut('fast', function () {
                fadesCompleted++;
                if (fadesCompleted == $(".cmc-search-result").length) {
                  $(".result-picture").each(function () {
                    imagesDeleted++;
                    $(this).children("img").remove();
                    if (imagesDeleted == $(".result-picture").length) {
                      CMC.showSearchResults(CMC.searchPageCache[CMC.currentDisplayedSearchPage - 1]);
                    }
                  });
                }
              });
            });
          } else {
            // something went horribly, horribly wrong, and we should probably know about it
          }
          CMC.updateSearchPagingControls();
        },

        updateSearchPagingControls : function () {
          $("#cmc-search-results-pagingctl-text").children(".ui-button-text").html("page " + CMC.currentDisplayedSearchPage);
          if (CMC.currentDisplayedSearchPage <= 1) {
            $("#cmc-search-results-pagingctl-prev").button("disable");
          } else {
            $("#cmc-search-results-pagingctl-prev").button("enable");
          }
          if (CMC.searchPageCache[CMC.currentDisplayedSearchPage] != null) {
            $("#cmc-search-results-pagingctl-next").button("enable");
          } else {
            $("#cmc-search-results-pagingctl-next").button("disable");
          }
        }
      };

      FB.init({
        appId  : '153051888089898',
        status : true,
        cookie : true,
        fbml   : true
      });

      $(function() {
        $("#make-profile, #make-volunteer, #make-organizer").hide();

        $(".cmc-big-button").hover(
          function() { $(this).addClass('ui-state-hover'); },
          function() { $(this).removeClass('ui-state-hover'); }
        );

        $("#tabs").tabs({
          fx: {
            //height: 'toggle',
            opacity: 'toggle',
            duration: 'fast'
          }
        });
        
        $("#ajax-spinner")
          .hide()
          .ajaxStart(function() {
            CMC.ajaxNotifyStart();
          })
          .ajaxStop(function() {
            CMC.ajaxNotifyComplete();
          });

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
          $("#tiptip_content").css("background-color", "black");
        }

        $("#cmc-search-icon").click(function() {
          $("#cmc-search-box").children("ul").children("li.bit-input").children(".maininput").focus();
        });

        $("#cmc-search-results-pagingctl-prev")
          .button({ text: false, icons: { primary: "ui-icon-circle-triangle-w" }})
          .click(function () {
            if (!$("#cmc-search-results-pagingctl-prev").button("option", "disabled")) {
              CMC.navigateToPreviousSearchPage();
            }
          });

        $("#cmc-search-results-pagingctl-next")
          .button({ text: false, icons: { primary: "ui-icon-circle-triangle-e" }})
          .click(function () {
            if (!$("#cmc-search-results-pagingctl-next").button("option", "disabled")) {
              CMC.navigateToNextSearchPage();
            }
          });

        $("#cmc-search-results-title").hide();
        $("#cmc-search-results-noresultmsg").hide();
        $(".cmc-search-result").each(function () { $(this).hide(); });

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
        $("#loading").fadeOut(function() {
          $("#tabs").hide().fadeIn(function() {
            $("#cmc-footer").hide().delay(150).fadeIn();
          });
        });

      });
    </script>
    <script type="text/javascript">
      $(function() {
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

      #tabs {
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
        margin-left: 17px;
        margin-top: 13px;
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
    <!-- Do not place HTML markup below this line -->
  </body>
</html>
