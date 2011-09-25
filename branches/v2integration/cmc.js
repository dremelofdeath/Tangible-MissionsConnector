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
  me : false,
  friends : false,
  profiledata :  {},
  isreceiver : false,
  profileexists : false,
  requestsOutstanding : 0,
  dialogsOpen : 0,
  version : "1.9.18",
  searchPageCache : [],
  currentDisplayedSearchPage : 0,
  searchPageImageClearJobQueue : [],
  profilePageImageClearJobQueue : [],
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

  handleGenericUnexpectedCallbackError : function(data, textStatus, jqXHR, requestType) {
    if (requestType == undefined) {
      requestType = "";
    } else if (requestType.length > 0 && requestType.charAt(0) != " ") {
      requestType = " " + requestType;
    }
    this.error("unexpected error occurred while trying to process a" + requestType + " callback.\ndata = " + data);
  },

  handleGenericServerError : function(jqXHR, textStatus, errorThrown) {
    this.error("can't contact server (" + textStatus + ") " + jqXHR.status + " " + errorThrown);
  },

  getProfileExistence : function () {
    this.beginFunction("getProfileExistence");
    $(".cmc-profile-result").each(function () { $(this).fadeOut('fast'); });
    this.log("Checking existence of the profile");
    this.ajaxNotifyStart(); // one for good measure, we want the spinner for profile existence check
    // check existence of the profile  
    $.ajax({
      type: "POST",
      url: "api/profileexistence.php",
      data: {
        fbid: this.me.id ? this.me.id : ""
      },
      dataType: "json",
      context: this,
      async: false,
      success: this.onProfileExistenceSuccess,
      error: this.onProfileExistenceError
    });

    this.endFunction("getProfileExistence");
  },  
  
  onProfileExistenceSuccess : function(data, textStatus, jqXHR) {
    this.beginFunction("onProfileExistenceSuccess");
    this.assert(data != undefined, "data is undefined in onProfileExistenceSuccess");
    if(data.has_error !== undefined && data.has_error !== null) {
      if(data.has_error) {
        // we have a known error, handle it
        this.handleProfileExistenceSuccessHasError(data);
      } else {
        if(data.exists == 1) {
          this.log("Obtaining data from the profile");
          this.ajaxNotifyStart(); // one for good measure, we want the spinner for the whole search
          $.ajax({
            type: "POST",
            url: "api/profile.php",
            data: {
              fbid: this.me.id ? this.me.id : ""
            },
            dataType: "json",
            context: this,
            async: false,
            success: this.onGetProfileDataSuccess,
            error: this.onGetProfileDataError
          });
        } else {
          this.showProfile(null);
        }
      }
    } else {
      // an unknown error occurred? do something!
      this.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "profile");
    }
    this.endFunction("onProfileExistenceSuccess");
  },

  onGetProfileDataSuccess : function(data, textStatus, jqXHR) {
    this.beginFunction("onGetProfileDataSuccess");
    this.assert(data != undefined, "data is undefined in onGetProfileDataSuccess");
    if(data.has_error !== undefined && data.has_error !== null) {
      if(data.has_error) {
        // we have a known error, handle it
        this.handleGetProfileDataSuccessHasError(data);
      } else {
        // This is the case of a volunteer profile
        if (data.isreceiver==0) {
          this.isreceiver = 0;
        } else {
          // profile is os a mission organizer
          this.isreceiver = 1;
        }
        this.profiledata = data;
        CMC.log("Calling showProfile with profile data");
        this.showProfile(data);
      }
    } else {
      // an unknown error occurred? do something!
      this.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "profile data");
    }
    this.endFunction("onGetProfileDataSuccess");
	},
  
  showProfile : function (data) {
    this.beginFunction("showProfile");
    if (data === undefined) {
      // this should be a bug! do NOT pass this function undefined! say null to inform it that you have no results!
      this.assert(data === undefined, "undefined passed as results for showProfile");
    } else if (data == null) {
      // no profile exists - so display the new profile creation dialogs
      $("#no-profile").fadeIn();
    } else {
      

      var imageLoadsCompleted = 0, __notifyprofileImageLoadCompleted = $.proxy(function() {
        imageLoadsCompleted++;
          this.animateShowProfile();
      }, this);

        var id = "#profilecontent";
        this.ajaxNotifyStart();
        this.assert(data.name !== undefined, "name is missing from result set");
        $(id).children("#colOne").children(".box2").children(".profile-name").html(data.name ? data.name : "");
        
        $(id).children("#colOne").children("#profileimage").children(".profile-picture").children("img").attr("src", "http://graph.facebook.com/"+this.me.id+"/picture?type=large");
        this.ajaxNotifyComplete();
			
        $(id).children("#colOne").children(".box2").children(".profile-about").html(data.about ? "<h4>" + data.about + "</h4>" : "");
	


      if (data.MedicalSkills == undefined) {
			  $(id).children("#colTwo").children(".box1").children(".profile-medskills").html(" ");
      }
      else {
      //display medical skills information
			if (data.MedicalSkills.length > 0) {
			var eachstr = "<ul>";
			for (var each in data.MedicalSkills) {
					eachstr += "<li> " + data.MedicalSkills[each] + "</li>";
			}
			eachstr += "</ul>";
		
			$(id).children("#colTwo").children(".box1").children(".profile-medskills").html(data.MedicalSkills ? eachstr : "");
			}
      else {
			$(id).children("#colTwo").children(".box1").children(".profile-medskills").html(" ");
      }
      }
			
			//display non-medical skills information
      if (data.Non_MedicalSkills == undefined) {
			$(id).children("#colTwo").children(".box1").children(".profile-nonmedskills").html("");
      }
      else {
			if (data.Non_MedicalSkills.length > 0) {
			var eachstr = "<ul>";
			for (var each in data.Non_MedicalSkills) {
					eachstr += "<li> " + data.Non_MedicalSkills[each] + "</li>";
			}
			eachstr += "</ul>";
		
			$(id).children("#colTwo").children(".box1").children(".profile-nonmedskills").html(data.Non_MedicalSkills ? eachstr : "");
			}
      else {
			$(id).children("#colTwo").children(".box1").children(".profile-nonmedskills").html("");
      }
      }
			
			//display profile information
      if (data.email == undefined) {
			$(id).children("#colTwo").children(".box1").children(".profile-email").html("<h6></h6>");
      }
      else {
			$(id).children("#colTwo").children(".box1").children(".profile-email").html(data.email ? "<h6>" + data.email + "</h6>" : "");
      }

      if (data.phone == undefined) {
			$(id).children("#colTwo").children(".box1").children(".profile-phone").html("<h6></h6>");
      }
      else {
			$(id).children("#colTwo").children(".box1").children(".profile-phone").html(data.phone ? "<h6>" + data.phone + "</h6>" : "");
      }

      if (data.country == undefined) {
			$(id).children("#colTwo").children(".box1").children(".profile-country").html("<h6></h6>");
      }
      else {
      $(id).children("#colTwo").children(".box1").children(".profile-country").html(data.country ? "<h6>" + data.country + "</h6>" : "");
      }
			

      if (data.zip == undefined) {
			$(id).children("#colTwo").children(".box1").children(".profile-zip").html("<h6></h6>");
      }
      else {
      $(id).children("#colTwo").children(".box1").children(".profile-zip").html(data.zip ? "<h6>" + data.zip + "</h6>" : "");
      }
			
      if (data.Durations == undefined) {
			$(id).children("#colTwo").children(".box1").children(".profile-dur").html("<h6></h6>");
      }
      else {
      $(id).children("#colTwo").children(".box1").children(".profile-dur").html(data.Durations.PreferredDurationofMissionTrips ? "<h6>" + data.Durations.PreferredDurationofMissionTrips + "</h6>" : "");
      }

			
      if (data.GeographicAreasofInterest == undefined) {
			$(id).children("#colTwo").children(".box1").children(".profile-countries").html("<h6></h6>");
      }
      else {
      $(id).children("#colTwo").children(".box1").children(".profile-countries").html(data.GeographicAreasofInterest.Countries ? "<h6>" + data.GeographicAreasofInterest.Countries + "</h6>" : "");
      }
		

      if (data.trips == undefined) {
			$(id).children("#colTwo").children("#table_wrapper").children("#tbody").html("<table></table>");
      }
      else {
			//finally update the trips information
			if (data.trips.length > 0) {
			
      var eachstr = "";
			for (var each in data.trips) {
			
        eachstr += "<tr>";
        eachstr += "<div class=\"profile-picture-\"" + each + ">";
        eachstr += "<img src=\"ajax-spinner.gif\"/>";
        eachstr += "</div>";
        eachstr += "<td><div class=\"box3\">";
        eachstr += "<div class=\"profile-tripname-\"" + each + ">";
        eachstr += "<h4> TripName </h4>";
        eachstr += "</div>";
        eachstr += "</td>";
        eachstr += "<td class=\"td2\"><input type=\"submit\" value=\"Trip Description\" class=\"button\" id=\"trip-desc-submit-\"" + each + "/></td>";
        eachstr += "<td class=\"td2\"><input type=\"submit\" value=\"Join This Trip\" class=\"button\" id=\"join-trip-submit-\"" + each + "/></td>";
        eachstr += "</tr>";
        eachstr += "</div>";
      }
      // replace the existing template with the new template that is the length of the trips array
			$(id).children("#colTwo").children("#table_wrapper").children("#tbody").html("<table>" + eachstr + "</table>");
			
      //Now update the new template with the trips information
      for (var each in data.trips) {      
      $(id).children("#colTwo").children("#table_wrapper").children("#tbody").children(".profile-picture-"+each).children("img").attr("src", "http://graph.facebook.com/"+this.me.id+"/picture?type=small");
			$(id).children("#colTwo").children("#table_wrapper").children("#tbody").children(".box3").children(".profile-tripname-"+each).html(data.trips[each] ? "<h4>" + data.trips[each] + "</h4>" : "");

			}			

			}
      else {
			$(id).children("#colTwo").children("#table_wrapper").children("#tbody").html("<table></table>");
      }

      }
      $("#show-profile").fadeIn();
    } // end else
    this.ajaxNotifyComplete(); // finish the one we started at the beginning of profile retrieval
    this.endFunction("showProfile");
  },	
  
  animateShowProfile : function () {
    this.beginFunction("animateShowProfile");
	  var id = "#profileimage";

      $("*").clearQueue("custom-ProfileQueue");
      if ($(id + " .profile-picture img").length > 1) {
        // cleanup the junk pictures, the user is clicking too quickly
        this.log("cleaning " + ($(id + " .profile-picture img").length - 1) + " junk result(s) while showing " + id);
        while ($(id + " .profile-picture img").length > 1) {
          $(id + " .profile-picture img:first").remove();
          $(id).hide(); // this will get shown again later
        }
      }
      $(id).queue("custom-ProfileQueue", function () {
        //var each = $(this).attr("id")[1];
        $(this)
          .stop(true, true)
          .delay(25)
          .show("drop", {direction: "right", distance: 50}, 250, _onShowComplete);
      }).dequeue("custom-ProfileQueue");
	  
    this.endFunction("animateShowProfile");
  },

  onProfileExistenceError : function(jqXHR, textStatus, errorThrown) {
    this.ajaxNotifyComplete();
    // we might also want to log this or surface an error message or something
    this.handleGenericServerError(jqXHR, textStatus, errorThrown);
  },

  handleProfileExistenceSuccessHasError : function(data) {
    this.beginFunction("handleProfileExistenceSuccessHasError");
    this.assert(data != undefined, "data is undefined in handleProfileExistenceSuccessHasError");
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
    this.endFunction("handleProfileExistenceSuccessHasError");
  },

  onGetProfileDataError : function(jqXHR, textStatus, errorThrown) {
    this.ajaxNotifyComplete();
    // we might also want to log this or surface an error message or something
    this.handleGenericServerError(jqXHR, textStatus, errorThrown);
  },

  handleGetProfileDataSuccessHasError : function(data) {
    this.beginFunction("handleGetProfileDataSuccessHasError");
    this.assert(data != undefined, "data is undefined in handleGetProfileDataSuccessHasError");
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
    this.endFunction("handleGetProfileDataSuccessHasError");
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
      if(!this.me.id) {
       this.log("this.me.id isn't available; using blank fbid for search");
      }
      this.ajaxNotifyStart(); // one for good measure, we want the spinner for the whole search
      $.ajax({
        url: "api/searchresults.php",
        data: {
          fbid: this.me.id ? this.me.id : "",
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
      this.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "search success");
    }
    this.updateSearchPagingControls();
    this.endFunction("onSearchSuccess");
  },

  onSearchError : function(jqXHR, textStatus, errorThrown) {
    this.ajaxNotifyComplete();
    // we might also want to log this or surface an error message or something
    this.handleGenericServerError(jqXHR, textStatus, errorThrown);
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
        $(".result-name").each(function () {
          $(this).html("");
        });
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
      if(!this.me.id) {
       this.log("this.me.id isn't available; using blank fbid for search");
      }
      $.ajax({
        url: "api/searchresults.php",
        data: {
          fbid: this.me.id ? this.me.id : "",
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
      this.cmc.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "search success");
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
    this.handleGenericServerError(jqXHR, textStatus, errorThrown);
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
      $("#cmc-search-results-pagingctl-prev").removeClass("ui-state-hover").button("disable");
    } else {
      $("#cmc-search-results-pagingctl-prev").button("enable");
    }
    if (this.searchPageCache[this.currentDisplayedSearchPage] != null) {
      $("#cmc-search-results-pagingctl-next").button("enable");
    } else {
      $("#cmc-search-results-pagingctl-next").removeClass("ui-state-hover").button("disable");
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
        .hide("drop", {direction: "right", distance: 115, easing: "easeOutQuart"}, 350, _onHideComplete)
        .show(0) // the 0 forces the show to be an animation event, and therefore happen after the hide() above
        .fadeTo(0, 0);
    });
    setTimeout(function () {
      $("#tabs").tabs('select', 1);
    }, 285);
    this.endFunction("animateSearchResultSelected");
  },
  
  handleSearchResultSelected : function (whichResult) {
    this.beginFunction("handleSearchResultSelected");
    if($(whichResult).children(".result-name").html() != "") {
      this.animateSearchResultSelected(whichResult);
    } else {
      this.log("search result clicked, but name is empty; ignoring");
    }
    this.endFunction("handleSearchResultSelected");
  },

  cacheFacebookData : function () {
    this.beginFunction("cacheFacebookData");
    CMC.ajaxNotifyStart();
    FB.api('/me', function (response) {
      CMC.log("got user data from Facebook");
      CMC.ajaxNotifyComplete();
      CMC.me = response;
    // now check whether profile is volunteer or mission organizer	  
      //alert('<img src="http://graph.facebook.com/' + CMC.me.id + '/picture" />');
      CMC.getProfileExistence();
		  CMC.log("user: " + CMC.me.name + " has just logged in to the app");
		  CMC.log("userid: " + CMC.me.id + " has just logged in to the app");
    });
    CMC.ajaxNotifyStart();
    FB.api('/me/friends', function (friends) {
      CMC.log("got friend data from Facebook");
      CMC.ajaxNotifyComplete();
      CMC.friends = friends.data;
    });
    this.endFunction("cacheFacebookData");
  },

  checkFacebookLoginStatus : function (callback) {
    this.beginFunction("checkFacebookLoginStatus");
    //@/BEGIN/DEBUGONLYSECTION
    $("#logged-in-user-value").html("(synchronizing)");
    //@/END/DEBUGONLYSECTION
    CMC.ajaxNotifyStart();
    FB.getLoginStatus(function(response) {
      CMC.ajaxNotifyComplete();
      CMC.log("got the response for FB.getLoginStatus()");
      if (response.authResponse) {
        //@/BEGIN/DEBUGONLYSECTION
        $("#logged-in-user-value").html(response.authResponse.userID);
      } else { // please be careful, this else case gets deleted as part of the debug removal process
        $("#logged-in-user-value").html("(not authorized)");
        //@/END/DEBUGONLYSECTION
      }
      if (callback) {
        callback(response);
      }
    });
    this.endFunction("checkFacebookLoginStatus");
  },

  login : function (callback) {
    // this is a wrapper API to handle user clicks that require Facebook authorization
    this.beginFunction("login");
    //@/BEGIN/DEBUGONLYSECTION
    $("#logged-in-user-value").html("(synchronizing)");
    //@/END/DEBUGONLYSECTION
    CMC.ajaxNotifyStart();
    FB.login(function (response) {
      CMC.ajaxNotifyComplete();
      if (response.authResponse) {
        CMC.log("user " + response.authResponse.userID + " has just logged in to the app");
        CMC.cacheFacebookData();
        //@/BEGIN/DEBUGONLYSECTION
        $("#logged-in-user-value").html(response.authResponse.userID);
        //@/END/DEBUGONLYSECTION
      } else {
        CMC.log("authResponse is null; user cancelled login or did not authorize");
        //@/BEGIN/DEBUGONLYSECTION
        $("#logged-in-user-value").html("(not authorized)");
        //@/END/DEBUGONLYSECTION
      }
      if (callback) {
        callback(response);
      }
    });
    this.endFunction("login");
  }

                 
};

FB.init({
  appId  : '207688579246956',
  status : true,
  cookie : true,
  fbml   : true,
  oauth  : true
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

  $("#make-trip, #profile-trip-dialog").hide();

  $("#make-volunteer").click(function() {
	$("#profile-volunteer-dialog").dialog('open');
  });

  $("#profile-volunteer-dialog").dialog({
    autoOpen: false,
    draggable: true,
    position: [477, 190],
    resizable: true,
    width: 700,
    open: function() {
      CMC.dialogOpen(this);
    },
    close: function() {
      CMC.dialogClose(this);
    }
  });
  
  $("#edit-volunteer-dialog").dialog({
    autoOpen: false,
    draggable: true,
    position: [477, 190],
    resizable: true,
    width: 700,
    open: function() {
      CMC.dialogOpen(this);
    },
    close: function() {
      CMC.dialogClose(this);
    }
  });  

  CMC.log("2");
  
  $("#make-organizer").click(function() {
	$("#profile-organizer-dialog").dialog('open');
  });

  $("#profile-organizer-dialog").dialog({
    autoOpen: false,
    draggable: true,
    position: [477, 190],
    resizable: true,
    width: 700,
    open: function() {
      CMC.dialogOpen(this);
    },
    close: function() {
      CMC.dialogClose(this);
    }
  });  
  
  $("#make-trip").click(function() {
    $("#profile-trip-dialog").dialog('open');
  });

  $("#profile-trip-dialog").dialog({
    autoOpen: false,
    draggable: true,
    position: [477, 190],
    resizable: true,
    width: 700,
    open: function() {
      CMC.dialogOpen(this);
    },
    close: function() {
      CMC.dialogClose(this);
    }
  });

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
    .click(function () { CMC.handleSearchResultSelected(this); })
    .each(function () { $(this).hide(); });

  // this should fix the junk picture assert on first search
  CMC.log("clearing the placeholder images");
  $(".result-picture img").remove();

  CMC.log("attempting to get facebook login status");
  CMC.checkFacebookLoginStatus(function (response) {
    if (response.authResponse) {
	  CMC.loggedInUserID = response.authResponse.userID;
      CMC.log("user " + CMC.loggedInUserID + " is already logged in, cache their data");
      //CMC.log("user " + response.authResponse.userID + " is already logged in, cache their data");
      CMC.cacheFacebookData();
    } else {
      CMC.log("authResponse is null; no user session, do not cache data yet");
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

    function validateZipCode(elementValue){
      var zisValid = false;
      var zipCodePattern = /^\d{5}$|^\d{5}-\d{4}$/;
      zisValid = zipCodePattern.test(elementValue);
      return zisValid;
    }

    function validateEmail(email){
      var eisValid =  false;
      var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+[\.]{1}[a-zA-Z]{2,4}$/;  
      eisValid = emailPattern.test(email);  
      return eisValid;
    } 
    function isUrl(s) {
		var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
		return regexp.test(s);
	  }

     function validatePhone(fld,country) {
      var error = "";
      var stripped = fld.replace(/[\(\)\.\-\ ]/g, ''); 
      // for international numbers
      var regex = /^\+(?:[0-9] ?){6,14}[0-9]$/;

      if (isNaN(parseInt(stripped))) {
        error = "The phone number contains illegal characters.\n";
      }
      else if (country != "United States") {
        if (!regex.test(fld)) {
          error = "The phone number is not a valid International Number.\n";
        }
      }
      else if (!(stripped.length == 10)) {
        error = "The phone number is the wrong length. Make sure you included an area code.\n";
      }

      return error;
    }

  $("#profile-submit").click(function() {
    var mtype = $("form").find('.profile-ddl-type-medical');
    var nmtype = $("form").find('.profile-ddl-type-nonmedical');
    var sptype = $("form").find('.profile-ddl-type-spiritual');
    var reltype = $("form").find('.profile-ddl-type-religious');
    var durtype = $("form").find('.profile-ddl-type-duration');
    var state = $("form").find('.profile-ddl-type-state');
    var city = $("form").find('.profile-input-city');
    var zipcode = $("form").find('.profile-input-zipcode');

    var country = $("form").find('.profile-ddl-type-country');
    var region = $("form").find('.profile-ddl-type-region');
    var countriesserved = $("form").find('.profile-ddl-type-countriesserved');
    var phone = $("form").find('.profile-input-phone');
    var email = $("form").find('.profile-input-email');
    var misexp = $("form").find('.profile-input-experience');           

    var zipisvalid = false;
    var emailisvalid = false;
    var reason="";
    var errornum=1;

    if (zipcode.val() != "") {
      zipisvalid = CMC.validateZipCode(zipcode.val());
      if (!zipisvalid) {
        reason += errornum+'. Incorrect Zipcode format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (email.val() != "") {
      emailisvalid = CMC.validateEmail(email.val());
      if (!emailisvalid) {
        reason += errornum + '. Incorrect Email format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (phone.val() != "") {
      var phoneerror = CMC.validatePhone(phone.val(),country.val());
      if (phoneerror != "") {
        reason += errornum + ' ' + phoneerror + '\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (reason != "") {
      alert('Some input fields need correction:\n'+ reason);
      return false;
    } else {
      var profileformdata = {};
      profileformdata.profiletype=1;
      if (mtype.val() != 0)
        profileformdata.medskills= mtype.val();
      if (nmtype.val() != 0)
        profileformdata.otherskills=nmtype.val();         
      if (sptype.val() != 0)
        profileformdata.spiritserv=sptype.val();        
      if (region.val() != 0)
        profileformdata.region=region.val();  
      if (country.val() != "")
        profileformdata.country=country.val();  
      if (state.val() != "Select your State")
        profileformdata.state=state.val();  
      if (durtype.val() != 0)
        profileformdata.dur=durtype.val();
      if (reltype.val() != 0)
        profileformdata.relg=reltype.val();           
      if (zipcode.val() != "")
        profileformdata.zip=zipcode.val();
      if (email.val() != "")
        profileformdata.email=email.val();
      if (city.val() != "")
        profileformdata.city=city.val();
      if (phone.val() != "")
        profileformdata.phone=phone.val();
      if (misexp.val() != "")
        profileformdata.misexp=misexp.val();            

      alert('AJAX form submission = ' + JSON.stringify(profileformdata));

      $.ajax({
        type: "POST",
        url: "api/profilein.php",
        data: {
          fbid: CMC.me.id ? CMC.me.id : "",
        profiledata: JSON.stringify(profileformdata)
        },
        dataType: "json",
        success: function() {
          alert('Success');
        },
        error: function() {
                 alert('Failure');
               }
      });
      return true;
    }

    
  });

    $("#profile-org-submit").click(function() {
	
    var aname = $("form").find('.profile-org-name');
    var aurl = $("form").find('.profile-org-website');
    var aabout = $("form").find('.profile-org-about');
    var medfacil = $("form").find('.profile-org-offer');
    var nonmedfacil = $("form").find('.profile-org-offern');
	
    var mtype = $("form").find('.profile-org-medical');
    var nmtype = $("form").find('.profile-org-nonmedical');
    var sptype = $("form").find('.profile-org-spiritual');
    var reltype = $("form").find('.profile-org-religion');
    var durtype = $("form").find('.profile-org-duration');
    var state = $("form").find('.profile-org-state');
    var city = $("form").find('.profile-org-city');
    var zipcode = $("form").find('.profile-org-zipcode');

    var country = $("form").find('.profile-org-country');
    var region = $("form").find('.profile-org-region');
    var countriesserved = $("form").find('.profile-org-countryserved');
    var phone = $("form").find('.profile-org-phone');
    var email = $("form").find('.profile-org-email');
    var misexp = $("form").find('.profile-org-experience');           

    var zipisvalid = false;
    var emailisvalid = false;
    var reason="";
    var errornum=1;

	if (aurl.val() != "") {
		if (!isUrl(aurl.val())) {
			reason += errornum+'. Incorrect Website Entered\n';
			errornum = errornum + 1;
			isValid = false;		
		}
	}
	
    if (zipcode.val() != "") {
      zipisvalid = CMC.validateZipCode(zipcode.val());
      if (!zipisvalid) {
        reason += errornum+'. Incorrect Zipcode format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (email.val() != "") {
      emailisvalid = CMC.validateEmail(email.val());
      if (!emailisvalid) {
        reason += errornum + '. Incorrect Email format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (phone.val() != "") {
      var phoneerror = CMC.validatePhone(phone.val(),country.val());
      if (phoneerror != "") {
        reason += errornum + ' ' + phoneerror + '\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (reason != "") {
      alert('Some input fields need correction:\n'+ reason);
      return false;
    } else {
      var profileformdata = {};
      profileformdata.profiletype=3;
      if (aname.val() != 0)
        profileformdata.name= aname.val();	  
      if (aabout.val() != 0)
        profileformdata.about= aabout.val();
      if (aurl.val() != 0)
        profileformdata.url= aurl.val();
      if (medfacil.val() != 0)
        profileformdata.medfacil= medfacil.val();
      if (nonmedfacil.val() != 0)
        profileformdata.nonmedfacil= nonmedfacil.val();		
      if (mtype.val() != 0)
        profileformdata.medskills= mtype.val();
      if (nmtype.val() != 0)
        profileformdata.otherskills=nmtype.val();         
      if (sptype.val() != 0)
        profileformdata.spiritserv=sptype.val();        
      if (region.val() != 0)
        profileformdata.region=region.val();  
      if (country.val() != "")
        profileformdata.country=country.val();  
      if (state.val() != "Select your State")
        profileformdata.state=state.val();  
      if (durtype.val() != 0)
        profileformdata.dur=durtype.val();
      if (reltype.val() != 0)
        profileformdata.relg=reltype.val();           
      if (zipcode.val() != "")
        profileformdata.zip=zipcode.val();
      if (email.val() != "")
        profileformdata.email=email.val();
      if (city.val() != "")
        profileformdata.city=city.val();
      if (phone.val() != "")
        profileformdata.phone=phone.val();
      if (misexp.val() != "")
        profileformdata.misexp=misexp.val();            

      alert('AJAX form submission = ' + JSON.stringify(profileformdata));

      $.ajax({
        type: "POST",
        url: "api/profilein.php",
        data: {
          fbid: CMC.me.id,
        profiledata: JSON.stringify(profileformdata)
        },
        dataType: "json",
        success: function() {
          alert('Success');
        },
        error: function() {
                 alert('Failure');
               }
      });
      return true;
    }

    
  });
  
  $("#profile-trip-submit").click(function() {
  
    var aname = $("form").find('.profile-trip-name');
	var aurl = $("form").find('.profile-trip-website');
	var aabout = $("form").find('.profile-trip-about');
	
    var reltype = $("form").find('.profile-trip-religion');
    var durtype = $("form").find('.profile-trip-duration');
    var city = $("form").find('.profile-trip-city');
    var zipcode = $("form").find('.profile-trip-zipcode');

    var country = $("form").find('.profile-trip-country');
    var phone = $("form").find('.profile-trip-phone');
    var email = $("form").find('.profile-trip-email');
    var stage = $("form").find('.profile-trip-stage');
    var tripdepart = $("form").find('.profile-trip-depart');
    var tripreturn = $("form").find('.profile-trip-return');
    var numberofmembers = $("form").find('.profile-trip-number');

    var zipisvalid = false;
    var emailisvalid = false;

    var reason="";
    var errornum=1;

	if (aurl.val() != "") {
		if (!isUrl(aurl.val())) {
			reason += errornum+'. Incorrect Website Entered\n';
			errornum = errornum + 1;
			isValid = false;		
		}
	}
	
    if (zipcode.val() != "") {
      zipisvalid = validateZipCode(zipcode.val());
      if (!zipisvalid) {
        reason += errornum+'. Incorrect Zipcode format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (email.val() != "") {
      emailisvalid = validateEmail(email.val());
      if (!emailisvalid) {
        reason += errornum + '. Incorrect Email format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (phone.val() != "") {
      var phoneerror = validatePhone(phone.val(),country.val());
      if (phoneerror != "") {
        reason += errornum + ' ' + phoneerror + '\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (tripdepart.val() == "select") {
      reason += errornum + ' ' + 'Trip should have a depart date' + '\n';
      errornum = errornum + 1;
      isValid = false;
    }
    if (tripreturn.val() == "select") {
      reason += errornum + ' ' + 'Trip should have a return date' + '\n';
      errornum = errornum + 1;
      isValid = false;
    }
    
    if (reason != "") {
      alert('Some input fields need correction:\n'+ reason);
      return false;
    } else {
      var profiletripformdata = {};
      profiletripformdata.profiletype=2;
      if (aname.val() != "")
        profiletripformdata.name= aname.val();
      if (aurl.val() != "")
        profiletripformdata.url=aurl.val();
      if (aabout.val() != "")
        profiletripformdata.about= aabout.val();		
      if (stage.val() != 0)
        profiletripformdata.stage= stage.val();
      if (tripdepart.val() != "") {
          //alert("departdate1 = " + tripdepart.val());
          var departdate = tripdepart.val().split(".");
          //alert("departdate = " + departdate[0] + "  " + departdate[1] + " " + departdate[2]);
          profiletripformdata.DepartMonth=parseInt(departdate[0]);	  
          profiletripformdata.DepartDay=parseInt(departdate[1]);	  
          profiletripformdata.DepartYear=parseInt(departdate[2]);	  
      }
      if (tripreturn.val() != "") {
          var returndate = tripreturn.val().split(".");
          profiletripformdata.ReturnMonth=parseInt(returndate[0]);	  
          profiletripformdata.ReturnDay=parseInt(returndate[1]);	  
          profiletripformdata.ReturnYear=parseInt(returndate[2]);	  
      }
      if (numberofmembers.val() != "")
        profiletripformdata.numpeople=numberofmembers.val();         
      if (country.val() != "")
        profiletripformdata.country=country.val();  
      if (durtype.val() != 0)
        profiletripformdata.durationid=durtype.val();
      if (reltype.val() != 0)
        profiletripformdata.relg=reltype.val();           
      if (zipcode.val() != "")
        profiletripformdata.zip=zipcode.val();
      if (email.val() != "")
        profiletripformdata.email=email.val();
      if (city.val() != "")
        profiletripformdata.destination=city.val();
      if (phone.val() != "")
        profiletripformdata.phone=phone.val();

      //alert('AJAX TRIP form submission = ' + JSON.stringify(profiletripformdata));
      
      
      $.ajax({
        type: "POST",
        url: "api/profilein.php",
        data: {
          fbid: CMC.me.id,
		      profiledata: JSON.stringify(profiletripformdata)
        },
        dataType: "json",
        success: function() {
          alert('Success');
        },
        error: function() {
                 alert('Failure');
               }
      });
      
	    $("#profile-trip-dialog").dialog('close');
      return true;
    }
    });

  // Handles the live form validation
  $("#profile-medical").validate({
    expression: "if (VAL) return true; else return false;",
    message: "Please enter the Required field"
  });
  $("#profile-org-website").validate({
		expression: "if (VAL.test(/(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/) && VAL) return true; else if (!VAL) return true; else return false;",
		message: "Please enter a valid website"
  });
  $("#profile-email").validate({
    expression: "if (VAL.match(/^[^\\W][a-zA-Z0-9\\_\\-\\.]+([a-zA-Z0-9\\_\\-\\.]+)*\\@[a-zA-Z0-9_]+(\\.[a-zA-Z0-9_]+)*\\.[a-zA-Z]{2,4}$/) && VAL) return true; else if (!VAL) return true; else return false;",
    message: "Please enter a valid Email ID"
  }); 
  $("#profile-org-email").validate({
    expression: "if (VAL.match(/^[^\\W][a-zA-Z0-9\\_\\-\\.]+([a-zA-Z0-9\\_\\-\\.]+)*\\@[a-zA-Z0-9_]+(\\.[a-zA-Z0-9_]+)*\\.[a-zA-Z]{2,4}$/) && VAL) return true; else if (!VAL) return true; else return false;",
    message: "Please enter a valid Email ID"
  });   
  $("#profile-trip-email").validate({
    expression: "if (VAL.match(/^[^\\W][a-zA-Z0-9\\_\\-\\.]+([a-zA-Z0-9\\_\\-\\.]+)*\\@[a-zA-Z0-9_]+(\\.[a-zA-Z0-9_]+)*\\.[a-zA-Z]{2,4}$/) && VAL) return true; else if (!VAL) return true; else return false;",
    message: "Please enter a valid Email ID"
  }); 
  $("#profile-zipcode").validate({
    expression: "if (VAL.match(new RegExp(/(^[0-9]{5}$)|(^[0-9]{5}-[0-9]{4}$)/)) && VAL) return true; else if (!VAL) return true; else return false;",
    message: "Please enter a valid Zipcode"
  });
  $("#profile-org-zipcode").validate({
    expression: "if (VAL.match(new RegExp(/(^[0-9]{5}$)|(^[0-9]{5}-[0-9]{4}$)/)) && VAL) return true; else if (!VAL) return true; else return false;",
    message: "Please enter a valid Zipcode"
  });  
  $("#profile-trip-zipcode").validate({
    expression: "if (VAL.match(new RegExp(/(^[0-9]{5}$)|(^[0-9]{5}-[0-9]{4}$)/)) && VAL) return true; else if (!VAL) return true; else return false;",
    message: "Please enter a valid Zipcode"
  }); 
  $("#profile-phone").validate({
    expression: "if (VAL.match(new RegExp(/(^[0-9]{10}$)/)) && VAL) return true; else if (!VAL) return true; else return false;",
    message: "Please enter a valid Phone Number"
  }); 
  $("#profile-org-phone").validate({
    expression: "if (VAL.match(new RegExp(/(^[0-9]{10}$)/)) && VAL) return true; else if (!VAL) return true; else return false;",
    message: "Please enter a valid Phone Number"
  });   
  $("#profile-trip-phone").validate({
    expression: "if (VAL.match(new RegExp(/(^[0-9]{10}$)/)) && VAL) return true; else if (!VAL) return true; else return false;",
    message: "Please enter a valid Phone Number"
  }); 

  $('.profile-ddl-contents').css('display', 'none');
  $('.profile-ddl-type-country').css('display', 'United States');
  $('.profile-ddl-header').toggle(function() {
    toggleContents($(this).parent().find('.profile-ddl-contents'));
  }, function() { toggleContents($(this).parent().find('.profile-ddl-contents')); });

  function toggleContents(el) {
    $('.profile-ddl-contents').css('display', 'none');
    if (el.css('display') == 'none') el.fadeIn("slow");
    else el.fadeOut("slow");
  }
  $('.profile-ddl-contents a').click(function() {
    $(this).parent().parent().find('.profile-ddl-o select').attr('selectedIndex', $('.profile-ddl-contents a').index(this));
    $(this).parent().parent().find('.profile-ddl-title').html($(this).html());
    $(this).parent().parent().find('.profile-ddl-contents').fadeOut("slow");
  });


  var profileshow = "hide";
  $("#EditProfile").click(function() {  

    if (CMC.receiver ==0) {
      alert("Volunteer profile" + CMC.profiledata);
    }
    else {
      // First modify the profile organizer dialog form, then display for editing

        var id = "#profile-organizer-dialog";
        //CMC.log($('#profile-org-zipcode').html());

        if (CMC.profiledata.zip != undefined) {
          $("input#profile-org-zipcode").val(CMC.profiledata.zip);
        }
        if (CMC.profiledata.AgencyName != undefined) {
          $("input#profile-org-name").val(CMC.profiledata.AgencyName);
        }
        if (CMC.profiledata.AgencyWebsite != undefined) {
          $("input#profile-org-website").val(CMC.profiledata.AgencyWebsite);
        }
        if (CMC.profiledata.AboutAgency != undefined) {
          $("input#profile-org-about").val(CMC.profiledata.AboutAgency);
        }
        if (CMC.profiledata.FacilityMedicalOfferings != undefined) {
          if (CMC.profiledata.FacilityMedicalOfferings.length>0) {
          var selstr = "[";
          for (var each in CMC.profiledata.FacilityMedicalOfferings) {
            if (each > 0) {
              selstr += ", ";
            }
            selstr += CMC.profiledata.FacilityMedicalOfferings[each];
          }
          selstr += "]";

          $("input#profile-org-offer").val(selstr);
          }
        }
        if (CMC.profiledata.FacilityNon_MedicalOfferings != undefined) {
          if (CMC.profiledata.FacilityNon_MedicalOfferings.length > 0) {
          var selstr = "[";
          for (var each in CMC.profiledata.FacilityNon_MedicalOfferings) {
            if (each > 0) {
              selstr += ", ";
            }
            selstr += CMC.profiledata.FacilityNon_MedicalOfferings[each];
          }
          selstr += "]";

          $("input#profile-org-offern").val(selstr);
          }
        }
        if (CMC.profiledata.MedicalSkills != undefined) {
          if (CMC.profiledata.MedicalSkills.length > 0) {
          var selstr = "[";
          for (var each in CMC.profiledata.MedicalSkills) {
            if (each > 0) {
              selstr += ", ";
            }
            selstr += CMC.profiledata.MedicalSkills[each];
          }
          selstr += "]";

          $("input#profile-org-medical").val(selstr);
          }
        }
        if (CMC.profiledata.Non_MedicalSkills != undefined) {
          if (CMC.profiledata.Non_MedicalSkills.length > 0) {
          var selstr = "[";
          for (var each in CMC.profiledata.Non_MedicalSkills) {
            if (each > 0) {
              selstr += ", ";
            }
            selstr += CMC.profiledata.Non_MedicalSkills[each];
          }
          selstr += "]";

          $("input#profile-org-nonmedical").val(selstr);
          }
        }
        if (CMC.profiledata.SpiritualSkills != undefined) {
          if (CMC.profiledata.SpiritualSkills.length > 0) {
          var selstr = "[";
          for (var each in CMC.profiledata.SpiritualSkills) {
            if (each > 0) {
              selstr += ", ";
            }
            selstr += CMC.profiledata.SpiritualSkills[each];
          }
          selstr += "]";

          $("input#profile-org-spiritual").val(selstr);
          }
        }
        if (CMC.profiledata.relg != undefined) {
          $("input#profile-org-religion").val(CMC.profiledata.relg);
        }
       
        if (CMC.profiledata.Durations != undefined) {
        if (CMC.profiledata.Durations.PreferredDurationofMissionTrips != undefined) {
          $("input#profile-org-duration").val(CMC.profiledata.Durations.PreferredDurationofMissionTrips);
        }
        }
        
        if (CMC.profiledata.States != undefined) {
        if (CMC.profiledata.States.state != undefined) {
          $("input#profile-org-state").val(CMC.profiledata.States.state);
        }
        }
        if (CMC.profiledata.city != undefined) {
          $("input#profile-org-city").val(CMC.profiledata.city);
        }
        if (CMC.profiledata.country != undefined) {
          $("input#profile-org-country").val(CMC.profiledata.country);
        }
        if (CMC.profiledata.GeographicAreasofInterest != undefined) {
        if (CMC.profiledata.GeographicAreasofInterest.Regions != undefined) {
          if (CMC.profiledata.GeographicAreasofInterest.Regions.length > 0) {
          var selstr = "[";
          for (var each in CMC.profiledata.GeographicAreasofInterest.Regions) {
            if (each > 0) {
              selstr += ", ";
            }
            selstr += CMC.profiledata.GeographicAreasofInterest.Regions[each];
          }
          selstr += "]";

          $("input#profile-org-region").val(selstr);
          }
        }
        }
        if (CMC.profiledata.GeographicAreasofInterest != undefined) {
        if (CMC.profiledata.GeographicAreasofInterest.Countries != undefined) {
          if (CMC.profiledata.GeographicAreasofInterest.Countries.length > 0) {
          var selstr = "[";
          for (var each in CMC.profiledata.GeographicAreasofInterest.Countries) {
            if (each > 0) {
              selstr += ", ";
            }
            selstr += CMC.profiledata.GeographicAreasofInterest.Countries[each];
          }
          selstr += "]";

          $("input#profile-org-countryserved").val(selstr);
          }
        }
        }
        if (CMC.profiledata.phone != undefined) {
          $("input#profile-org-phone").val(CMC.profiledata.phone);
        }
        if (CMC.profiledata.email != undefined) {
          $("input#profile-org-email").val(CMC.profiledata.email);
        }
        if (CMC.profiledata.misexp != undefined) {
          $("input#profile-org-experience").val(CMC.profiledata.misexp);
        }
       
        $(id).children("form").children("#wrapper").children("#contents").children(".profile-container").children(".profile-header").html("Please edit your profile information");   
        $("#profile-organizer-dialog").dialog('open');
      
    }

  });

  $("#CreateTrip").click(function() {
    $("#profile-trip-dialog").dialog('open');
  });   

  if (CMC.profiledata.trips != undefined) {

  var tripjoinbtns = new Array();
  for (var i=0;i<CMC.profiledata.trips.length;i++) {
    tripjoinbtns[i] = "join-trip-submit-"+i;
  }

  $.each(tripjoinbtns, function() {
    $("#" +this).click(function() {
      var tripparts = this.id.split('-');
      var index = parseInt(tripparts[tripparts.length-1]);;
      alert("Trip Index = " + index);
    });
  });

  var tripdescbtns = new Array();
  for (var i=0;i<CMC.profiledata.trips.length;i++) {
    tripdescbtns[i] = "trip-desc-submit-"+i;
  }

  $.each(tripdescbtns, function() {
    $("#" +this).click(function() {
      var descparts = this.id.split('-');
      var index = parseInt(descparts[descparts.length-1]);;
      alert("Desc Trip Index = " + index);
    });
  });

  }


  // this should be the last thing that happens
  CMC.log("load callback complete, fading in canvas");
  $("#loading").fadeOut(function() {
    $("#tabs").hide().fadeIn(function() {
      $("#cmc-footer").hide().delay(150).fadeIn();
    });
  });

});
// vim: ai:et:ts=2:sw=2
// graphicAreasofInterest.Countries
