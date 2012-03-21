// first things first, some polyfills (browser compatibility hacks)
if (!Object.keys) Object.keys = function(o){
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
  accessToken : false,
  me : false,
  friends : false,
  profiledata :  {},
  tripdata : {},
  isreceiver : false,
  profileexists : false,
  profileedit : false,
  tripedit : false,
  requestsOutstanding : 0,
  tripuserid : false,
  dialogsOpen : 0,
  currentlyShownUserProfileID : null,
  version : /* @/VERSIONMARKER */ "2.0 Debug",
  ignorableFormFields : null, // access this with fetchIgnorableFormFields()
  _searchLockKeyExpected : null,
  _lastSearchLockKeyGenerated : 0,
  isSearchLocked : false,
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

  // backend translation maps -- describe the mapping of form IDs to server API
  // call variables
  BackendTranslation : {
    Profile : {
      "profile-medical-skills" : "medskills",
      "profile-nonmedical-skills" : "otherskills",
      "profile-spiritual-skills" : "spiritserv",
      "profile-religion" : "relg",
      "profile-duration" : "dur",
      "profile-state" : "state",
      "profile-city" : "city",
      "profile-zipcode" : "zip",
      "profile-country" : "mycountry",
      "profile-region" : "region",
      "profile-country-served" : "country",
      "profile-phone" : "phone",
      "profile-email" : "email",
      "profile-experience" : "misexp"
    },
    OrganizerProfile : {
      "profile-org-name" : "name",
      "profile-org-website" : "url",
      "profile-org-about" : "about",
      "profile-org-offer" : "medfacil",
      "profile-org-offern" : "nonmedfacil",
      "profile-org-medical" : "medskills",
      "profile-org-nonmedical" : "otherskills",
      "profile-org-spiritual" : "spiritserv",
      "profile-org-religion" : "relg",
      "profile-org-duration" : "dur",
      "profile-org-state" : "state",
      "profile-org-city" : "city",
      "profile-org-zipcode" : "zip",
      "profile-org-country" : "mycountry",
      "profile-org-region" : "region",
      "profile-org-countryserved" : "country",
      "profile-org-phone" : "phone",
      "profile-org-email" : "email",
      "profile-org-experience" : "misexp"
    },
    TripProfile : {
      "profile-trip-name" : "name",
      "profile-trip-website" : "url",
      "profile-trip-about" : "about",
      "profile-trip-religion" : "relg",
      "profile-trip-duration" : "dur",
      "profile-trip-city" : "city",
      "profile-trip-zipcode" : "zip",
      "profile-trip-country" : "mycountry",
      "profile-trip-languages" : "languages",
      "profile-trip-phone" : "phone",
      "profile-trip-email" : "email",
      "profile-trip-stage" : "stage",
      "profile-trip-depart" : "depart",
      "profile-trip-return" : "return",
	    "profile-trip-number" : "numpeople",
      "profile-trip-acco" : "acco"
    }	
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
      if (whereTo === undefined) whereTo = "#debug-log";
      var depth = "";
      for (var i = 0; i < this.debugFunctionDepth; i++) {
        depth += " ";
      }
      var content = $(whereTo).val() + depth + output;
      content = content.slice(content.length - 5000); // only keep the most recent 5000 characters
      $(whereTo)
        .val(content + "\n")
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
        $("#assert-message").html(bugmsg);
        $("#assert-dialog").dialog('open');
      }
    },

    findFunctionNameFor : function (obj, context) {
      if (context === undefined) context = this; // context, such as CMC --zack
      for (var each in context) {
        try {
          if (context[each] == obj) {
            return each;
          }
        } catch(e) {}
      }
      return false;
    },

    beginFunction : function() {
      // check for scope corruption
      this.assert(this === CMC, "Scope corruption detected! this === CMC failed!");
      this.log("begin function: " + this.findFunctionNameFor(this.beginFunction.caller));
      this.debugFunctionDepth++;
    },

    endFunction : function() {
      this.debugFunctionDepth--;
      this.assert(this.debugFunctionDepth >= 0, "debugFunctionDepth went negative");
      this.log("end function: " + this.findFunctionNameFor(this.endFunction.caller));
    },
  },

  log : $.noop,
  error : $.noop,
  assert : $.noop,
  findFunctionNameFor : $.noop,
  beginFunction : $.noop,
  endFunction : $.noop,

  // debug state variables go here. please don't make a lot of these. --zack
  debugFunctionDepth : 0,

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
    if ("findFunctionNameFor" in handlerSet) {
      this.findFunctionNameFor = handlerSet.findFunctionNameFor;
    }
    if ("beginFunction" in handlerSet) {
      this.beginFunction = handlerSet.beginFunction;
    }
    if ("endFunction" in handlerSet) {
      this.endFunction = handlerSet.endFunction;
    }
    this.assert(this.hasOwnProperty("debugFunctionDepth"), "debugFunctionDepth is missing! where could it be?");
    $("#debug-section").show();
  },

  detachDebugHandlers : function() {
    $("#debug-log").val("");
    this.log = $.noop;
    this.error = $.noop;
    this.assert = $.noop;
    this.findFunctionNameFor = $.noop;
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
    this.beginFunction();
    this.dialogsOpen++;
    this.closeAllDialogs(dialog);
    if ($.support.opacity && this.dialogsOpen == 1) {
      $("#tabs, #cmc-footer").fadeTo('fast', 0.5);
    }
    this.endFunction();
  },

  dialogClose : function(dialog) {
    this.beginFunction();
    if ($.support.opacity && this.dialogsOpen == 1) {
      $("#tabs, #cmc-footer").fadeTo('fast', 1.0);
    }
    if (this.dialogsOpen <= 0) {
      this.assert("closing a dialog when none was open!");
    } else {
      this.dialogsOpen--;
    }
    this.endFunction();
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

  applyTranslationMap : function(object, translationMap) {
    this.beginFunction();
    var ret = {};
    if (object) {
      if (translationMap) {
        for (var each in object) {
          if (object.hasOwnProperty(each)) {
            if (translationMap.hasOwnProperty(each)) {
              ret[translationMap[each]] = object[each];
            } else {
              this.assert("don't know how to map property '" + each + "' -- possibly update map?");
            }
          }
        }
      } else {
        this.assert("translationMap was null. you must specify a translation map");
      }
    } else {
      this.assert("null object passed for applying translation map");
    }
    this.endFunction();
    return ret;
  },

  recalculateTextareaLimit : function(messageID, labelID, limit, customText) {
    var len = $(messageID).val().length;
    limit = limit || 300;
    customText = customText || " characters left";
    $(labelID).html((limit - len) + customText);
  },

  recalculateProblemMessageLimit : function(limit) {
    this.beginFunction();
    this.recalculateTextareaLimit(
      "#report-problem-message",
      "#report-problem-characters-left",
      limit
    );
    $("#report-problem-characters-left").fadeIn();
    this.endFunction();
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
    this.assert(textStatus != "parsererror", "Web service failure!<br/>Response contents:<br/><br/>" + jqXHR.responseText);
  },

  handleGetProfileCallbackSave : function(data) {
    this.beginFunction();
    if (data.isreceiver==0) {
      // This is the case of a volunteer profile
      this.isreceiver = 0;
    } else {
      // profile is os a mission organizer
      this.isreceiver = 1;
    }
    this.profiledata = data;
    this.endFunction();
  },

  handleGetProfileCallbackSaveAndShow : function(data) {
    this.beginFunction();
    this.handleGetProfileCallbackSave(data);
    this.showProfile(data);
    this.endFunction();
  },

  getProfile : function(userid, callback) {
    this.beginFunction();
    this.log("Obtaining data from the profile");
    if (callback === undefined) callback = this.handleGetProfileCallbackSave;
    if (userid) {
      this.ajaxNotifyStart(); // one for good measure, we want the spinner for the whole search
      $.ajax({
        type: "POST",
        url: "api/profile.php",
        data: {
          fbid: userid
        },
        dataType: "json",
        context: {
          invokeData: {
            "callback": callback
          },
          cmc: this
        },
        success: this.onGetProfileDataSuccess,
        error: this.onGetProfileDataError
      });
    } else {
      this.assert("userid was blank when calling getProfile()");
    }
    this.endFunction();
  },
 
  onGetProfileDataSuccess : function(data, textStatus, jqXHR) {
    this.cmc.beginFunction();
    this.cmc.assert(data != undefined, "data is undefined in onGetProfileDataSuccess");
    if(data.has_error !== undefined && data.has_error !== null) {
      if(data.has_error) {
        // first handle the no profile error - simply display a new profile creation form
        if (data.exists == 0) {
          this.cmc.showProfile(null);
        } else {
          // we have a known error, handle it
          this.cmc.handleGetProfileDataSuccessHasError(data);
        }
      } else {
        this.cmc.assert(this.invokeData.callback != undefined, "callback was undefined when handling profile data");
        if (this.invokeData.callback) {
          this.cmc.log("invoking callback for getProfile");
          $.proxy(this.invokeData.callback, this.cmc)(data);
          this.cmc.ajaxNotifyComplete(); // finish the one we started at the beginning of profile retrieval
        }
      }
    } else {
      // an unknown error occurred? do something!
      this.cmc.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "profile data");
    }
    this.cmc.endFunction();
  },

  updateProfileControls : function(userid) {
    this.beginFunction();
    // Check whether the user is viewing own profile or someone else's profile
    // If viewing someone else's profile, provide a link to go back to own profile
    if (userid != CMC.me.id) {
      $("#profile-controls-back-to-my-profile").show();
      $("#profile-controls-edit").hide();
      $("#profile-controls-create-trip").hide();
    } else {
      $("#profile-controls-back-to-my-profile").hide();
      $("#profile-controls-edit").show();
      $("#profile-controls-create-trip").show();
    }
    this.endFunction();
  },

  hideIntermediateNewProfileCreationSteps : function () {
    this.beginFunction();
    $("#make-volunteer").fadeOut();
    $("#make-organizer").fadeOut();
    $("#make-profile").fadeOut();
    this.endFunction();
  },
 
  showCreateNewProfileUI : function () {
    this.beginFunction();
    this.hideIntermediateNewProfileCreationSteps();
    $("#no-profile").fadeIn();
    this.endFunction();
  },

  showProfile : function (data) {
    this.beginFunction();
    if (data === undefined) {
      // this should be a bug! do NOT pass this function undefined! say null to inform it that you have no results!
      this.assert(data === undefined, "undefined passed as results for showProfile");
    } else if (data == null) {
      // no profile exists - so display the new profile creation dialogs
      this.showCreateNewProfileUI();
      this.currentlyShownUserProfileID = null;
    } else {
      var id = "#profilecontent";
      var __setProfileLinkInternal = function (link) {
        $("#profile-name-link").attr('href', link);
        $("#profile-picture-link").attr('href', link);
      };
      this.hideIntermediateNewProfileCreationSteps();
      this.ajaxNotifyStart();
      this.assert(data.id != undefined, "id is missing from result set");
      this.currentlyShownUserProfileID = data.id;
      this.updateProfileControls(data.id);
      this.assert(data.name != undefined, "name is missing from result set");

      $("#profile-name").html(data.name ? data.name : "");
 
      if (!data.link) {
        FB.api(data.id, function(response) {
          __setProfileLinkInternal(response.link);
        });
      } else {
        __setProfileLinkInternal(data.link);
      }

      $("img.profile-picture").attr("src", "http://graph.facebook.com/"+data.id+"/picture?type=large");

      this.ajaxNotifyComplete();

      $("#profile-section-about-me-content").html(data.about ? data.about : "");
      if (data.MedicalSkills == undefined) {
        $("#profile-medskills").html("");
      } else {
        //display medical skills information
        if (data.MedicalSkills.length > 0) {
          var eachstr = "<ul>";
          for (var each in data.MedicalSkills) {
            eachstr += "<li> " + data.MedicalSkills[each] + "</li>";
          }
          eachstr += "</ul>";

          $("#profile-medskills").html(data.MedicalSkills ? eachstr : "");
        } else {
          $("#profile-medskills").html("");
        }
      }

      //display non-medical skills information
      if (data.Non_MedicalSkills == undefined) {
        $("#profile-nonmedskills").html("");
      } else {
        if (data.Non_MedicalSkills.length > 0) {
          var eachstr = "<ul>";
          for (var each in data.Non_MedicalSkills) {
            eachstr += "<li> " + data.Non_MedicalSkills[each] + "</li>";
          }
          eachstr += "</ul>";

          $("#profile-nonmedskills").html(data.Non_MedicalSkills ? eachstr : "");
        } else {
          $("#profile-nonmedskills").html("");
        }
      }

      //display profile information
      if (data.email) {
        $("span#profile-email").html(data.email);
        $("#profile-email-display").show();
      } else {
        $("#profile-email-display").hide();
        $("span#profile-email").html("");
      }

      if (data.AgencyWebsite) {
        $("span#profile-website").html(data.AgencyWebsite);
        $("#profile-website").show();
      } else {
        $("#profile-website").hide();
        $("span#profile-website").html("");
      }

      if (data.phone) {
        $("span#profile-phone").html(data.phone);
        $("#profile-phone-display").show();
      } else {
        $("#profile-phone-display").hide();
        $("span#profile-phone").html("");
      }

      if (data.country) {
        $("span#profile-country").html(data.country);
        $("#profile-country-display").show();
      } else {
        $("#profile-country-display").hide();
        $("span#profile-country").html("");
      }

      if (data.zip) {
        if (data.city) {
          if (data.States) {
            $("span#profile-zip").html(data.zip + " (" + data.city + ", " + data.States.shortname + ")");
          } else {
            $("span#profile-zip").html(data.zip + " (" + data.city + ")");
          }
        } else {
          if (data.States) {
            $("span#profile-zip").html(data.zip + " (" + data.States.State + ")");
          } else {
            $("span#profile-zip").html(data.zip);
          }
        }
        $("#profile-zip-display").show();
      } else {
        $("#profile-zip-display").hide();
        $("span#profile-zip").html("");
      }

      if (data.Durations && data.Durations.PreferredDurationofMissionTrips) {
        var durationsstring = "";
        for(var each in data.Durations.PreferredDurationofMissionTrips) {
          durationsstring += data.Durations.PreferredDurationofMissionTrips[each] + ", ";
        }
        durationsstring = durationsstring.slice(0, durationsstring.length-2);
        $("span#profile-dur").html(durationsstring);
        $("#profile-dur-display").show();
      } else {
        $("#profile-dur-display").hide();
        $("span#profile-dur").html("");
      }

      if (data.GeographicAreasofInterest && data.GeographicAreasofInterest.Countries) {
        var countriesstring = "";
        for(var each in data.GeographicAreasofInterest.Countries) {
          countriesstring += data.GeographicAreasofInterest.Countries[each] + ", ";
        }
        countriesstring = countriesstring.slice(0, countriesstring.length-2);
        $("span#profile-countries").html(countriesstring);
        $("#profile-countries-display").show();
      } else {
        $("#profile-countries-display").hide();
        $("span#profile-countries").html("");
      }

      if (data.trips == undefined || data.trips.length <= 0) {
        $("div#profile-trips-list-section").hide();
      } else {
        var ul = this.buildTripList(data, true, true, false, false, data.id == this.me.id);
        ul.attr("id", "profile-trip-list");
        $("ul#profile-trip-list").remove();
        $("div#profile-trips-list-section").append(ul).show();
      }
      $("#show-profile").fadeIn();
    } // end else
    this.endFunction();
  },  
  
  onGetProfileDataError : function(jqXHR, textStatus, errorThrown) {
    // we might also want to log this or surface an error message or something
    this.cmc.ajaxNotifyComplete();
    this.cmc.handleGenericServerError(jqXHR, textStatus, errorThrown);
  },

  handleGetProfileDataSuccessHasError : function(data) {
    this.beginFunction();
    this.ajaxNotifyComplete();
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
    this.endFunction();
  },

  refreshUserProfile : function (data) {
    this.beginFunction();
    if (this.me.id == this.currentlyShownUserProfileID) {
      this.getProfile(this.me.id, this.handleGetProfileCallbackSaveAndShow);
    } else {
      this.getProfile(this.me.id, this.handleGetProfileCallbackSave);
    }
    this.endFunction();
  },

  getFutureTrips : function() {
    this.beginFunction();
    this.log("Obtaining future trip information from the database");
    $.ajax({
      type: "POST",
      url: "api/searchtrips.php",
      data: {
        fbid: CMC.me.id ? CMC.me.id : ""
      },
      dataType: "json",
      context: this,
      success: this.onGetTripsDataSuccess,
      error: this.onGetTripsDataError
    });
    this.endFunction();
  },

  onGetTripsDataSuccess : function(data, textStatus, jqXHR) {
    this.beginFunction();
    this.assert(data != undefined, "data is undefined in onGetTripsDataSuccess");
    if(data.has_error !== undefined && data.has_error !== null) {
      if(data.has_error) {
          // we have a known error, handle it
          this.handleGetTripsDataSuccessHasError(data);
      } else {
          this.updateFutureTrips(data);
      }
    } else {
      // an unknown error occurred? do something!
      this.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "future trips data");
    }
    this.endFunction();
  },

  buildTripListEx : function (data, buttons) {
    this.beginFunction();
    var ul = $("<ul></ul>", {
      "class": "trip-list"
    });
    for (var each in data.trips) {
      var li = $("<li></li>", {
        "class": "trip-list-item"
      });
      var span = $("<span></span>", {
        "class": "trip-item-name",
        text: data.trips[each].tripname
      });
      li.append(span);
      for (var eachButton in buttons) {
        var shouldDisplay = true;
        if (buttons[eachButton].hasOwnProperty("test")) {
          shouldDisplay = buttons[eachButton].test(data.trips[each]);
        }
        if (shouldDisplay) {
          var button = $("<div></div>", {
            "class": "trip-item-control",
            text: buttons[eachButton]["text"],
            tripid: data.trips[each].id
          }).button({
            text: true,
            icons: { primary: buttons[eachButton]["icon"]}
          }).click(buttons[eachButton]["action"]);
          li.append(button);
        }
      }
      ul.append(li);
    }
    this.endFunction();
    return ul;
  },

  buildTripList : function (data, showDescription, showEditIfOwner, showJoin, showInvite, showLeave) {
    this.beginFunction();
    var buttons = [];
    if (showLeave) {
      buttons.push({
        text: "Leave Trip",
        icon: "ui-icon-close",
        action: function () {
          CMC.leaveTrip($(this).attr("tripid"));
        }
      });
    }
    if (showDescription) {
      buttons.push({
        text: "More Info",
        icon: "ui-icon-info",
        action: function () {
          CMC.getTripProfile($(this).attr("tripid"));
        }
      });
    }
    if (showEditIfOwner) {
      buttons.push({
        text: "Edit Trip",
        icon: "ui-icon-pencil",
        action: function () {
          CMC.editTripProfile($(this).attr("tripid"));
        },
        test: function (trip) {
          return trip && trip.isadmin && trip.isadmin != 0;
        }
      });
    }
    if (showJoin) {
      buttons.push({
        text: "Join Trip",
        icon: "ui-icon-check",
        action: function () {
          CMC.joinTrip($(this).attr("tripid"));
        }
      });
    }
    if (showInvite) {
      this.assert("Invite button for trip lists cut for 2.0.");
    }
    var ul = this.buildTripListEx(data, buttons);
    this.endFunction();
    return ul;
  },

  updateFutureTrips : function (data) {
    this.beginFunction();
    if (data === undefined) {
      // this should be a bug! do NOT pass this function undefined! say null to inform it that you have no results!
      this.assert(data === undefined, "undefined passed as results for updateFutureTrips");
    } else if (data === null) {
      // no future trips exist - so display new trip creation dialog
      $("#no-trip").fadeIn();
    } else {
      var id = "#show-trips";
      this.assert(data.trips !== undefined, "Trips are missing from result set");
      if (!data.trips || data.trips.length <= 0) {
        $("#no-trip").fadeIn();
      } else {
        //finally update the upcoming trips information
        if (data.trips.length > 0) {
          var ul = this.buildTripList(data, true, true, true).attr("id", "upcoming-trip-list");
          $("ul#upcoming-trip-list").remove();
          $("div#upcoming-trips-list-section").append(ul);

          $("#show-trip-profile").fadeOut();
          $("#backtotrips").hide();
          $("#show-trips").fadeIn();
        } else {
          // No upcoming trips, prompt user to create a trip instead
          $("#no-trip").fadeIn();
        }
      } 

    } // end else
    this.endFunction();
  },

  handleGetTripsDataSuccessHasError : function(data) {
    this.beginFunction();
    this.assert(data != undefined, "data is undefined in handleGetTripsDataSuccessHasError");
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
    this.endFunction();
  },

  ToggleProfile : function() {
    this.beginFunction();
    this.log("Toggling profile information");
    var profileformdata = {};

    profileformdata.toggle=1;

    $.ajax({
      type: "POST",
      url: "api/toggleprofile.php",
      data: {
        fbid: CMC.me.id ? CMC.me.id : "",
        profileinfo: encode64(JSON.stringify(profileformdata))
      },
      dataType: "json",
      context: this,
      success: this.onToggleSuccess,
      error: this.onToggleError
    });
    this.endFunction();
  },

  onToggleSuccess : function(data, textStatus, jqXHR) {
    this.beginFunction();
    this.assert(data != undefined, "data is undefined in onToggleSuccess");
    if(data.has_error !== undefined && data.has_error !== null) {
      if(data.has_error) {
        // we have a known error, handle it
        this.handleToggleSuccessHasError(data);
      } else {
        // This means original dialog open was that of agency, so close that dialog
        if (CMC.isreceiver == 1) {
          $("#profile-organizer-dialog").dialog('close');
          $("#profile-toggle-dialog").dialog('open');
          $("#profile-toggle-dialog").dialog().dialog("widget").find(".ui-dialog-titlebar-close").hide();
          CMC.isreceiver = 0;
          CMC.editProfile();
        } else if (CMC.isreceiver == 0) {
          $("#profile-volunteer-dialog").dialog('close');   
          $("#profile-toggle-dialog").dialog('open');
          $("#profile-toggle-dialog").dialog().dialog("widget").find(".ui-dialog-titlebar-close").hide();     
          CMC.isreceiver = 1;
          CMC.editProfile();
        }
      }
    } else {
      // an unknown error occurred? do something!
      this.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "future trips data");
    }
    this.endFunction();
  },

  onToggleError : function(jqXHR, textStatus, errorThrown) {
    // we might also want to log this or surface an error message or something
    this.handleGenericServerError(jqXHR, textStatus, errorThrown);
  },
  
  handleToggleSuccessHasError : function(data) {
    this.beginFunction();
    this.assert(data != undefined, "data is undefined in handleToggleSuccessHasError");
    // we have a known error, handle it
    if(data.err_msg !== undefined) {
      if(data.err_msg != '') {
        this.error("caught an error from the server while performing profile toggle: \""+data.err_msg+"\"");
      } else {
        this.error("caught an error from the server while performing profile toggle, but it was blank");
      }
    } else {
      this.error("caught an error from the server while performing profile toggle, but it did not return an error message");
    }
    this.endFunction();
  },
  
  editTripProfile : function(tripid) {
    this.beginFunction();
    //First get trip profile information and then use that to popuplate the trip form
    $.ajax({
      type: "POST",
      url: "api/profileT.php",
      data: {
        tripid: tripid,
        fbid: CMC.me.id ? CMC.me.id : "",
      },
      dataType: "json",
      context: this,
      success: this.onGetTripProfileDataSuccessEdit,
      error: this.onGetTripProfileDataError
    });
    this.endFunction();
  },

  getTripProfile : function(tripid) {
    this.beginFunction();
    //this.log("Getting Trip information for : " + CMC.profiledata.tripid[index],10);
    $.ajax({
      type: "POST",
      url: "api/profileT.php",
      data: {
        tripid: tripid,
        fbid: CMC.me.id ? CMC.me.id : "",
      },
      dataType: "json",
      context: this,
      success: this.onGetTripProfileDataSuccess,
      error: this.onGetTripProfileDataError
    });
    this.endFunction();
  },  

  onGetTripProfileDataSuccess : function(data, textStatus, jqXHR) {
    this.beginFunction();
    this.assert(data != undefined, "data is undefined in onGetTripProfileDataSuccess");
    if(data.has_error !== undefined && data.has_error !== null) {
      if(data.has_error) {
          // we have a known error, handle it
          this.handleGetTripProfileDataSuccessHasError(data);
      } else {
          this.showTripProfile(data);
      }
    } else {
      // an unknown error occurred? do something!
      this.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "trip profile data");
    }
    this.endFunction();
  },  
  
  onGetTripProfileDataSuccessEdit : function(data, textStatus, jqXHR) {
    this.beginFunction();
    this.assert(data != undefined, "data is undefined in onGetTripProfileDataSuccess");
    if(data.has_error !== undefined && data.has_error !== null) {
      if(data.has_error) {
          // we have a known error, handle it
          this.handleGetTripProfileDataSuccessHasError(data);
      } else {
          this.editTrip(data);
      }
    } else {
      // an unknown error occurred? do something!
      this.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "trip profile data");
    }
    this.endFunction();
  },

  handleGetTripProfileDataSuccessHasError : function(data) {
    this.beginFunction();
    this.assert(data != undefined, "data is undefined in handleGetTripProfileDataSuccessHasError");
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
    this.endFunction();
  },  
  
  showTripProfile : function (data) {
    this.beginFunction();
    if (data === undefined) {
      // this should be a bug! do NOT pass this function undefined! say null to inform it that you have no results!
      this.assert(data === undefined, "undefined passed as results for showTripProfile");
    } else if (data == null) {
      // no trip profile exists - // This condition should never be met
      $("#no-trip").fadeIn();
    } else {

      var id = "#tripprofilecontent";

      this.ajaxNotifyStart();
      this.assert(data.tripname !== undefined, "Trip name is missing from result set");

      $("#profile-trip-owner").html(data.tripowner ? data.tripowner : "");

      this.tripuserid = data.creatorid;

      $("#trip-owner-picture").children("img").attr("src", "http://graph.facebook.com/"+data.creatorid+"/picture?type=large");

      $("#trip-profile-about").html(data.tripdesc ? "<h4>" + data.tripdesc + "</h4>" : "");

      //display Trip profile information
      if (data.tripname === undefined) {
        $("#profile-trip-name").html("");
      }
      else {
        $("#profile-trip-name").html(data.tripname ? data.tripname : "");
      }

      if (data.website === undefined) {
        $("#profile-trip-url").html("");
      }
      else {
        $("#profile-trip-url").html(data.website ? data.website : "");
      }  
    
      if ((data.destination === undefined) && (data.destinationcountry === undefined)) {
        $("#profile-trip-dest").html("");
      }
      else if (data.destination === undefined) {
        $("#profile-trip-dest").html(data.destinationcountry);
        $("#profile-trip-dest").html(data.destination);
      }
      else {
        $("#profile-trip-dest").html(data.destination + "," +data.destinationcountry);
      }     
    
      if (data.email === undefined) {
        $("#profile-trip-email").html("");
      }
      else {
        $("#profile-trip-email").html(data.email ? data.email : "");
      }

      if (data.phone === undefined) {
        $("#profile-trip-phone").html("");
      }
      else {
        $("#profile-trip-phone").html(data.phone ? data.phone : "");
      }
    
      if (data.tripstage === undefined) {
        $("#profile-trip-stage").html("");
      }
      else {
        $("#profile-trip-stage").html(data.tripstage ? data.tripstage : "");
      }   

      if (data.departyear === undefined) {
        $("#profile-trip-depart").html("");
      }
      else {
        $("#profile-trip-depart").html(data.departyear ? data.departmonth +"/"+data.departday+"/"+data.departyear : "");
      } 

      if (data.returnyear === undefined) {
        $("#profile-trip-return").html("");
      }
      else {
        $("#profile-trip-return").html(data.returnyear ? data.returnmonth + "/" + data.returnday + "/" + data.returnyear : "");
      }   

      if (data.religion === undefined) {
        $("#profile-trip-religion").html("");
      }
      else {
        $("#profile-trip-religion").html(data.religion ?  data.religion : "");
      }

      if (data.numpeople === undefined) {
        $("#profile-trip-numpeople").html("");
      }
      else {
        $("#profile-trip-numpeople").html(data.numpeople ? data.numpeople : "");
      }

      if (data.memberids === undefined) {
        //$(id).children("#trip-profile-right-column").children(".box1").children(".profile-trip-people").html("<h6></h6>");
        $("#profile-trip-people").html("");
      }
      else {
        //display trip member information
        if (data.memberids.length > 0) {
          //first update the html part
          var eachstr = "";
          for (var each in data.memberids) {

            eachstr += "<div id=\"cmc-trip-member-"+each+"\" class=\"cmc-tripmember-results\">";
            eachstr += "<div id=\"profile-tripmember-image\">";

            eachstr += "<img class=\"profile-tripmember-picture\" src=\"ajax-spinner.gif\">";
            eachstr += "</div>";
            eachstr += "<div class=\"profile-tripmember-name\">Member Name</div>";			
            eachstr += "</div>";
          }

          $("#profile-trip-people").html(data.memberids ? eachstr : "");

          for (var each in data.memberids) {
            id2 = "#profile-trip-people";
            FB.api(data.memberids[each], function(response) {
                $(id2).children("#cmc-trip-member-"+each).children(".profile-tripmember-name").html(response.name ? response.name : "");
                $(id2).children("#cmc-trip-member-"+each).children("#profile-tripmember-image").children("img.profile-tripmember-picture").attr("src", "http://graph.facebook.com/"+data.memberids[each]+"/picture");	
                //$(id2).children("#cmc-trip-member-"+each).children("#profile-tripmember-image").children("img.profile-tripmember-picture").wrap('<a href="' + response.link + '" target="_blank"></a>');
                });

            $("#cmc-trip-member-"+each).attr("fbid", data.memberids[each]);

            $(".cmc-tripmember-results")
              .click(function () { CMC.handleShowTripMemberProfile(this); });
          }
        } else {
          $("#profile-trip-people").html("");
        }	  
      }	  

      // change to the Trips Tab
      $("#tabs").tabs('select', 2);

      this.ajaxNotifyComplete();
      $("#show-trips").hide();
      $("#backtotrips").fadeIn();
      $("#show-trip-profile").fadeIn();
    } // end else

    this.endFunction();
  },
  
  joinTrip : function(trip) {
    $.ajax({
      type: "POST",
      url: "api/addtripmember.php",
      data: {
        fbid: CMC.me.id ? CMC.me.id : undefined,
        tripid: trip,
        type: 2
      },
      dataType: "json",
      context: this,
      success: function(data) {
        if (!data.has_error) {
          CMC.getProfile(CMC.me.id, CMC.handleGetProfileCallbackSaveAndShow);
          alert('You have successfully joined the trip.');
        } else {
          alert('Sorry, you could not be added to the trip because: ' + data.err_msg);
        }
      },
      error: function(data) {
          alert('Sorry, you could not be added to the trip because: ' + data.err_msg);
      }
    });
  },

  leaveTrip : function(trip) {
    $.ajax({
      type: "POST",
      url: "api/deletetripmembers.php",
      data: {
        fbid: CMC.me.id ? CMC.me.id : undefined,
        tripid: trip
      },
      dataType: "json",
      context: this,
      success: function(data) {
        if (!data.has_error) {
          CMC.getProfile(CMC.me.id, CMC.handleGetProfileCallbackSaveAndShow);
          alert('You have successfully left the trip.');
        } else {
          // special case for the case when you trip has just a single member
          // In this case, delete the tripmember as well as the trip itself
          if (data.membercount == 1) {
            $.ajax({
              type: "POST",
              url: "api/deletetrips.php",
              data: {
                tripid: trip
              },
              dataType: "json",
              context: this,
              success: function(data) {
              if (!data.has_error) {
                CMC.getProfile(CMC.me.id, CMC.handleGetProfileCallbackSaveAndShow);
                alert('You have successfully left the trip.');
              } else {
                alert('Sorry, you could not leave the trip because: ' + data.err_msg);
              }
              },
              error: function(data) {
                alert('Sorry, you could not leave the trip because: ' + data.err_msg);
              }
            });
          }
          else {
            alert('Sorry, you could not leave the trip because: ' + data.err_msg);
          }
        }
      },
      error: function(data) {
          alert('Sorry, you could not leave the trip because: ' + data.err_msg);
      }
    });
  },

  // This will return you a key, which you will provide to releaseSearchLock()
  // when you are finished locking search. For as long as search is locked,
  // calling search() will trigger an assert.
  obtainSearchLock : function() {
    this.beginFunction();
    var key = null;
    if (this.isSearchLocked) {
      this.assert("attempted to obtain the search lock while it was already held!");
    } else {
      this._searchLockKeyExpected = "lock" + this._lastSearchLockKeyGenerated;
      key = this._lastSearchLockKeyGenerated;
      this.isSearchLocked = true;
      this._lastSearchLockKeyGenerated++;
    }
    this.endFunction();
    return key;
  },

  releaseSearchLock : function(key) {
    this.beginFunction();
    if (this.isSearchLocked) {
      if ("lock" + key == this._searchLockKeyExpected) {
        this.isSearchLocked = false;
      } else {
        this.assert("wrong key used to try to release the search lock!");
      }
    } else {
      this.assert("tried to release the search lock when no lock was held!");
    }
    this.endFunction();
  },

  handleSearchSelect : function(item) {
    this.beginFunction();
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
          if (this.SearchState.z != undefined) {
            var oldValue = "!!z:" + this.SearchState.z[0] + ":" + this.SearchState.z[1];
            var oldTitleText = $('li.bit-box[rel="' + oldValue + '"]').filter(":first").html().split("<", 1)[0]; // godforsaken hack
            // we have a problem, you can't have more than one zipcode
            // ...so let's solve it by removing the previous one and replacing it with the new one --zack
            // note: this trigger (somewhat surprisingly) implicitly calls handleSearchRemove(), so we are going to
            // lock search to prevent multiple searches from being fired. --zack
            var key = this.obtainSearchLock();
            $("#search-box-select").trigger('removeItem', { value: oldValue });
            if (value == oldValue) {
              // we'll have to add it back in, since removeItem just killed both of them
              this.log("silly user, you were already using that zipcode!");
              // and yes. if you were thinking it already, you get a cookie.
              // this call does indeed call handleSearchSelect() implicitly (this function) O_O --zack
              $("#search-box-select").trigger('addItem', [{ title: oldTitleText, value: oldValue }]);
            }
            this.releaseSearchLock(key);
          }
          this.SearchState.z = [value.substring(4,9), value.substring(10, value.length)];
        } else if (value.substring(2,3) == "s") { // still could be better
          // it's a skill
          var skillid = value.substring(4, value.length);
          if (this.SearchState.skills != undefined) {
            this.SearchState.skills.push(skillid);
          } else {
            this.SearchState.skills = [skillid];
          }
        } else if (value.substring(2,3) == "c") {
          // it's a country
          var countryid = value.substring(4, value.length);
          if (this.SearchState.countries != undefined) {
            this.SearchState.countries.push(countryid);
          } else {
            this.SearchState.countries = [countryid];
          }
        } else {
          this.assert("incoming unknown object type '" + value.substring(2,3) + "' can't be handled!");
        }
      } else {
        // this is a text item
        // (note: we are going to handle text items as names for now)
        this.SearchState.name = value;
      }
      // it's rare, but handleSearchSelect can be called with while the search lock is held.
      if (!this.isSearchLocked) {
        this.search();
      }
    }
    this.endFunction();
  },

  handleSearchRemove : function(item) {
    this.beginFunction();
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
        var objectType = value.substring(2,3);
        if (objectType == "z") {
          // it's a zipcode. we don't care what it is, just nuke it
          delete this.SearchState.z;
        } else if (objectType == "s") {
          this.deleteObjectFromSearchState("skills", value.substring(4, value.length));
        } else if (objectType == "c") {
          this.deleteObjectFromSearchState("countries", value.substring(4, value.length));
        } else {
          this.assert("outgoing unknown object type '" + value.substring(2,3) + "' can't be handled!");
        }
      } else {
        // this is a text item
        // note: since we are treating text items as names, we should just 
        // delete the name. This will need to be fixed in the future.
        delete this.SearchState.name;
      }
      // We will expect that sometimes when handleSearchRemove() is called, it's
      // because of a manually triggered remove, and therefore we should lock
      // search. If search is locked, we'll skip search().
      if (!this.isSearchLocked) {
        this.search();
      }
    }
    this.endFunction();
  },

  deleteObjectFromSearchState : function(whichObjectType, obj) {
    this.beginFunction();
    if (this.SearchState[whichObjectType]) {
      if (this.SearchState[whichObjectType].length <= 1) {
        delete this.SearchState[whichObjectType];
      } else {
        var foundObject = false;
        var i = 0; // this is a really stupid bug in the chrome JS engine
        for (i = 0; i < this.SearchState[whichObjectType].length; i++) {
          if (this.SearchState[whichObjectType][i] == obj) {
            if (foundObject) {
              this.assert("found multiple copies of the same object you're trying to delete! (delete " + obj + " from " + whichObjectType + ")");
            } else {
              this.SearchState[whichObjectType].splice(i, 1);
              foundObject = true;
            }
          }
        }
        this.assert(foundObject, "couldn't find the object you're trying to delete! (delete " + obj + " from " + whichObjectType + ")");
      }
    } else {
      this.assert("trying to delete a object when the container object is dead!");
    }
    this.endFunction();
  },

  search : function () {
    this.beginFunction();
    if (this.isSearchLocked) {
      this.assert("called search() while search was locked!");
    } else {
      var searchUnlockKey = this.obtainSearchLock();
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
        $("#cmc-search-results-noresultmsg").fadeOut(200);
        //@/BEGIN/DEBUGONLYSECTION
        if(!this.me.id) {
         this.log("this.me.id isn't available; using blank fbid for search");
        }
        //@/END/DEBUGONLYSECTION
        this.ajaxNotifyStart(); // one for good measure, we want the spinner for the whole search
        $.ajax({
          type: "POST",
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
        $("#cmc-search-results-title").stop(true, true).fadeIn();
      }
      this.releaseSearchLock(searchUnlockKey);
    }
    this.endFunction();
  },

  onSearchSuccess : function(data, textStatus, jqXHR) {
    this.beginFunction();
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
    this.endFunction();
  },

  onSearchError : function(jqXHR, textStatus, errorThrown) {
    this.ajaxNotifyComplete();
    // we might also want to log this or surface an error message or something
    this.handleGenericServerError(jqXHR, textStatus, errorThrown);
  },

  handleSearchSuccessHasError : function(data) {
    this.beginFunction();
    this.ajaxNotifyComplete();
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
    this.endFunction();
  },

  getDataForEachFBID : function (fbids, callback, isRetryCall) {
    this.beginFunction();
    if (isRetryCall === null || isRetryCall === undefined) isRetryCall = false;
    var results = new Array(fbids.length), requestsCompleted = 0, hasRetryPosted = false;
    this.log("starting timer __timerNotificationTimeout");
    var __timerNotificationTimeout = setTimeout($.proxy(function () {
      this.log("__timerNotificationTimeout is checking getDataForEachFBID");
      if (requestsCompleted != fbids.length) {
        this.log("only " + requestsCompleted + " of " + fbids.length + " FBID requests completed in time (2s)");
        this.log("dumping results variable:");
        //@/BEGIN/DEBUGONLYSECTION
        for (var each in results) {
          var eachstr = "";
          for (var e in results[each]) {
            eachstr += (e + ": " + results[each][e] + "; ");
          }
          this.log("results["+each+"] = " + eachstr);
        }
        //@/END/DEBUGONLYSECTION
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
      var __handleApiCall = function (response) {
        if (!response) {
          CMC.error("response value was null in Facebook API call");
        } else if (response.error) {
          CMC.handleFacebookResponseError(response);
        }
        if (this.target) {
          results[this.target] = response;
        } else {
          CMC.assert("couldn't read target for __handleApiCall");
        }
        __notifyComplete();
      };
      __handleApiCall.target = each;
      FB.api('/' + fbids[each], $.proxy(__handleApiCall, __handleApiCall));
    }
    this.endFunction();
  },

  coalesceDefinedSearchResults : function (results) {
    this.beginFunction();
    var resultsOffset = 0;
    var __swapResults = function (results, firstid, secondid) {
      var temp = results[firstid];
      results[firstid] = results[secondid];
      results[secondid] = temp;
    };
    for (var each in results) {
      if (results[each].id === undefined || results[each].id === undefined) {
        resultsOffset++;
      } else if (resultsOffset > 0) {
        __swapResults(results, each - resultsOffset, each);
      }
    }
    this.endFunction();
  },

  showSearchResults : function (results, isRetryCall) {
    this.beginFunction();
    isRetryCall = isRetryCall || false; // optional parameter
    if (isRetryCall) {
      $("#cmc-search-results").show();
    }
    if (results === undefined) {
      // this is a bug! do NOT pass this function undefined! say null to inform it that you have no results!
      this.assert(results === undefined, "undefined passed as results for showSearchResults");
    } else if (results == null || results.length == 0) {
      // no results
      $("#cmc-search-results-noresultmsg").stop(true, true).fadeIn();
    } else {
      var imageLoadsCompleted = 0, __notifyImageLoadCompleted = $.proxy(function() {
        imageLoadsCompleted++;
        this.assert(imageLoadsCompleted <= results.length, "loading more images than we have results for (" + imageLoadsCompleted + ")");
        if(imageLoadsCompleted == results.length) {
          this.animateShowSearchResults(results);
        }
      }, this);
      this.assert(results.length <= 10, "more than 10 results passed to showSearchResults");
      if (!isRetryCall) {
        this.coalesceDefinedSearchResults(results); // fixes the "missing teeth" problem with invalid search results
      }
      if ($(".result-picture img").length > 0) {
        this.log("found " + $(".result-picture img").length + " junk pictures lying around");
        if (!isRetryCall) {
          this.log("delaying and retrying showSearchResults");
          setTimeout($.proxy(function () {
            this.showSearchResults(results, true);
          }, this), 200);
          this.endFunction();
          return;
        }
      }
      for(var each in results) {
        this.assert(results[each] !== undefined, "result[each] is missing at each=" + each);
        //this.assert(results[each].id !== undefined, "id is missing from result at each=" + each);
        var id = "#cmc-search-result-" + each;
        this.ajaxNotifyStart();
        //this.assert(results[each].name !== undefined, "name is missing from result at each=" + each);
        $(id).children(".result-name").html(results[each].name ? results[each].name : "");
        $(id).attr("fbid", results[each].id);
        $(id).attr("fblink", results[each].link);
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
            var timeout = setTimeout("$('" + id + "').children('.result-picture').children('img').hide();", 100 * i);
            this.searchPageImageClearJobQueue.push(timeout);
          }
          this.ajaxNotifyComplete();
          __notifyImageLoadCompleted();
        }
      } // end for
    } // end else
    this.ajaxNotifyComplete(); // finish the one we started at the beginning of the search
    this.endFunction();
  },

  animateShowSearchResults : function (results) {
    this.beginFunction();
    var maxSearchResults = $(".cmc-search-result").length, i = 0;
    this.log("animating resultset starting with " + results[0].name);
    $("#cmc-search-results").clearQueue("custom-SearchResultsQueue");
    var _doOneResultPageAnimation = function () {
      CMC.assert(this.hasOwnProperty("results"), "search results not correctly assigned to animation delegate");
      for(var each in this.results) {
        var id = "#cmc-search-result-" + each, showsCompleted = 0, _onShowComplete = $.proxy(function () {
              // this sure ain't the prettiest way to fix the incomplete
              // page quick click render bug, but it works --zack
              ++showsCompleted;
              if (showsCompleted == this.results.length) {
                if (this.results.length < maxSearchResults) {
                  CMC.log("incomplete page, hiding the the results that need cleanup");
                  for (var point = maxSearchResults - this.results.length; point > 0; point--) {
                    // clean up the slots that weren't being shown
                    var tempId = "#cmc-search-result-" + (maxSearchResults - point);
                    $(tempId).delay(4 * (maxSearchResults - point)).fadeOut('fast'); // at least fade out
                  }
                }
              }
            }, this);
        if ($(id + " .result-picture img").length > 1) {
          // cleanup the junk pictures, the user is clicking too quickly
          this.log("cleaning " + ($(id + " .result-picture img").length - 1) + " junk result(s) while showing " + id);
          while ($(id + " .result-picture img").length > 1) {
            $(id + " .result-picture img").filter(":first").remove();
            $(id + " .result-name div").html(""); // also kill the name
            $(id).hide(); // this will get shown again later
          }
        }
        $(id)
          .stop(true, true)
          .delay(25 * each)
          .show("drop", {direction: "right", distance: 50}, 250, _onShowComplete);
      }
      $("#cmc-search-results").dequeue("custom-SearchResultsQueue");
    }
    _doOneResultPageAnimation.results = results;
    _doOneResultPageAnimation = $.proxy(_doOneResultPageAnimation, _doOneResultPageAnimation); // I've seen worse hacks, I promise --zack
    $("#cmc-search-results").filter(":first").queue("custom-SearchResultsQueue", _doOneResultPageAnimation).dequeue("custom-SearchResultsQueue");
    this.endFunction();
  },

  animateHideSearchResults : function(callback) {
    this.beginFunction();
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
      $(this).stop(true, true).fadeOut(100, function () {
        _processFadeComplete();
      });
    }).dequeue("custom-SearchResultsQueue");
    this.endFunction();
  },

  padSearchResults : function (results) {
    this.beginFunction();
    // might we think about making this a constant or something?
    var maxSearchResults = $(".cmc-search-result").length, i = 0, ret = results.slice(0);
    if (results.length < maxSearchResults) {
      for (i = results.length; i < maxSearchResults; i++) {
        ret.push({id: false, name: false});
      }
    }
    this.endFunction();
    return ret;
  },

  navigateToNextSearchPage : function () {
    this.beginFunction();
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
    this.endFunction();
  },

  cacheSearchPage : function(pageIndex) {
    this.beginFunction();
    if (this.searchPageCache[pageIndex] === undefined) {
      // this is a page that we haven't cached yet
      this.log("[cacheSearchPage] fetching search page " + (pageIndex + 1));
      this.ajaxNotifyStart();
      if(!this.me.id) {
       this.log("this.me.id isn't available; using blank fbid for search");
      }
      $.ajax({
        type: "POST",
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
    this.endFunction();
  },

  onCacheSearchPageSuccess : function(data, textStatus, jqXHR) {
    //@/BEGIN/DEBUGONLYSECTION
    if (!("cmc" in this)) {
      if (CMC) {
        // definitely don't want this line floating around in production code --zack
        CMC.assert(false, '"cmc" not in this context for onCacheSearchPageSuccess');
      } // if this is unavailable, god help us all
    }
    //@/END/DEBUGONLYSECTION
    this.cmc.beginFunction(); // I know this isn't at the beginning --zack
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
    this.cmc.endFunction();
  },

  onCacheSearchPageServerError : function(jqXHR, textStatus, errorThrown) {
    this.beginFunction();
    this.ajaxNotifyComplete();
    this.searchPageCache.push(null); // this should (hopefully) stop the interval check
    // we might also want to log this or surface an error message or something
    this.handleGenericServerError(jqXHR, textStatus, errorThrown);
    this.endFunction();
  },

  navigateToPreviousSearchPage : function () {
    this.beginFunction();
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
    this.endFunction();
  },

  updateSearchPagingControls : function () {
    this.beginFunction();
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
    this.endFunction();
  },

  animateSearchResultSelected : function (whichResult) {
    this.beginFunction();
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
    this.endFunction();
  },
  
  handleSearchResultSelected : function (whichResult) {
    this.beginFunction();
    if($(whichResult).children(".result-name").html() != "") {
      var whichFBID = $(whichResult).attr("fbid");
      var whichFBLink = $(whichResult).attr("fblink");
      var __showProfileWithLinkPrecacheInternal = function (data) { // should match prototype for showProfile()
        if (!data.link) {
          data.link = whichFBLink;
        }
        CMC.showProfile(data);
      };
      this.assert(whichFBID != null && whichFBID != "", "fbid attr is null for clicked search result");
      this.assert(whichFBLink != null && whichFBLink != "", "fblink attr is null for clicked search result");
      this.getProfile(whichFBID, __showProfileWithLinkPrecacheInternal);      
      this.animateSearchResultSelected(whichResult);
    } else {
      this.log("search result clicked, but name is empty; ignoring");
    }
    this.endFunction();
  },

  animateTripMemberResultSelected : function (whichResult) {
    this.beginFunction();
    $(".cmc-tripmember-results").not(whichResult).each(function () {
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
    this.endFunction();
  },  
  
  handleShowTripMemberProfile : function (whichResult) {
    this.beginFunction();
    if($(whichResult).children(".profile-tripmember-name").html() != "") {
      var whichFBID = $(whichResult).attr("fbid");
      this.assert(whichFBID != null && whichFBID != "", "fbid attr is null for clicked search result");
      this.getProfile(whichFBID, this.showProfile);      
      this.animateTripMemberResultSelected(whichResult);
    } else {
      this.log("search result clicked, but name is empty; ignoring");
    }
    this.endFunction();
  },
  
  emptyTripForm : function () {
    this.beginFunction();
    $(':input', '#profile-trip-form').removeAttr('checked').removeAttr('selected');
    $(':text, :password, :file, SELECT', '#profile-trip-form').val('');
    this.endFunction();
  },

  editTrip : function (data) {
    this.beginFunction();
  
    this.tripedit = 1;

    this.checkFacebookLoginStatus($.proxy(function (response) {
      if (response && response.authResponse) {
        // Retrieve the profile data from the backend again to make sure it is the latest information, no need to show profile
        if (this.me && this.me.id) {
          this.getProfile(this.me.id);
        }
          
          this.emptyTripForm();
          var id = "#profile-trip-dialog";

          if (data.tripname !== undefined) {
            $("input#profile-trip-name").val(data.tripname);
            $("input#profile-trip-name").attr('disabled','disabled');
          }
          if (data.website !== undefined) {
            $("input#profile-trip-website").val(data.website);
          }
          if (data.tripdesc !== undefined) {
            $("input#profile-trip-about").val(data.tripdesc);
          }
          if (data.religion !== undefined) {
            $('select#profile-trip-religion option[value="' + parseInt(data.religion,10) + '"]').attr('selected', 'selected');
          }
          if (data.numpeople !== undefined) {
            $("input#profile-trip-number").val(data.numpeople);
          }
          if (data.departyear !== undefined) {
            var mydepart = String(data.departmonth) + '.' + String(data.departday) + '.' + String(data.departyear); 
            $("input#profile-trip-depart").val(mydepart);
          }
          if (data.returnyear !== undefined) {
            var myreturn = String(data.returnmonth) + '.' + String(data.returnday) + '.' + String(data.returnyear); 
            $("input#profile-trip-return").val(myreturn);
          }
          if (data.duration !== undefined) {
            $('select#profile-trip-duration option[value="' + parseInt(data.duration,10) + '"]').attr('selected', 'selected');
          }
          if (data.acco !== undefined) {
            $('select#profile-trip-acco option[value="' + parseInt(data.acco,10) + '"]').attr('selected', 'selected');
          }
          if (data.tripstage !== undefined) {
            $('select#profile-trip-stage option[value="' + parseInt(data.tripstage,10) + '"]').attr('selected', 'selected');
          }
          if (data.destination !== undefined) {
            $("input#profile-trip-city").val(data.destination);
          }
          if (data.destinationcountry !== undefined) {
            $('select#profile-trip-country option[value="' + parseInt(data.countryid[0],10) + '"]').attr('selected', 'selected');
          }
          if (data.zip !== undefined) {
            $("input#profile-trip-zipcode").val(data.zip);
          }
          
          if (data.languages !== undefined) {
            var mysplitlang = data.languages.split(",");
            if (mysplitlang.length > 0) {
              for (var each in mysplitlang) {
                $('select#profile-trip-languages option[value="' + parseInt(data.languageid[each],10) + '"]').attr('selected', 'selected');
              }
            }
          }
          if (data.email !== undefined) {
            $("input#profile-trip-email").val(data.email);
          }
          if (data.phone !== undefined) {
            $("input#profile-trip-phone").val(data.phone);
          }

          $("#profile-trip-dialog").dialog('open');
      } 
    }, this));
    this.endFunction();
  },

  editProfile : function (isNewProfile) {
    this.beginFunction();
    isNewProfile = isNewProfile === undefined ? false : isNewProfile;
    this.profileedit = !isNewProfile;

    this.checkFacebookLoginStatus($.proxy(function (response) {
      if (response && response.authResponse) {
        // Retrieve the profile data from the backend again to make sure it is the latest information, no need to show profile
        if (this.me && this.me.id) {
          this.getProfile(this.me.id);
        }

        if (this.isreceiver ==0) {
          var id = "#profile-volunteer-dialog";

          if (this.profiledata.zip !== undefined) {
            $("input#profile-zipcode").val(this.profiledata.zip);
          }
          if (this.profiledata.about !== undefined) {
            $("input#profile-about").val(this.profiledata.about);
          }

          if (this.profiledata.MedicalSkills !== undefined) {
            if (this.profiledata.MedicalSkills.length > 0) {
              for (var each in this.profiledata.MedicalSkills) {
                $('select#profile-medical-skills option[value="' + this.profiledata.MedicalSkillsid[each] + '"]').attr('selected', 'selected');
              }
            }
          }
            $("#profile-medical-skills").multiselect();
          if (this.profiledata.Non_MedicalSkills !== undefined) {
            if (this.profiledata.Non_MedicalSkills.length > 0) {
              for (var each in this.profiledata.Non_MedicalSkills) {
                $('select#profile-nonmedical-skills option[value="' + this.profiledata.Non_MedicalSkillsid[each] + '"]').attr('selected', 'selected');
              }
            }
          }
            $("#profile-nonmedical-skills").multiselect();
          if (this.profiledata.SpiritualSkills !== undefined) {
            if (this.profiledata.SpiritualSkills.length > 0) {
              for (var each in this.profiledata.SpiritualSkills) {
                $('select#profile-spiritual-skills option[value="' + this.profiledata.SpiritualSkillsid[each] + '"]').attr('selected', 'selected');
              }
            }
          }
            $("#profile-spiritual-skills").multiselect();
          if (this.profiledata.relg !== undefined) {
            $("input#profile-religion").val(this.profiledata.relg);
          }

          if (this.profiledata.Durations !== undefined) {
            if (this.profiledata.Durations.PreferredDurationofMissionTrips !== undefined) {
              $("input#profile-duration").val(this.profiledata.Durations.PreferredDurationofMissionTrips);
            }
          }

          if (this.profiledata.States !== undefined) {
            if (this.profiledata.States.State !== undefined) {
              $("input#profile-state").val(this.profiledata.States.State);
            }
          }
          if (this.profiledata.city !== undefined) {
            $("input#profile-city").val(this.profiledata.city);
          }
          if (this.profiledata.country !== undefined) {
            $("input#profile-country").val(this.profiledata.country);
          }
          if (this.profiledata.GeographicAreasofInterest !== undefined) {
            if (this.profiledata.GeographicAreasofInterest.Regions !== undefined) {
              if (this.profiledata.GeographicAreasofInterest.Regions.length > 0) {
                for (var each in this.profiledata.GeographicAreasofInterest.Regionsid) {
                  $('select#profile-region option[value="' + this.profiledata.GeographicAreasofInterest.Regionsid[each] + '"]').attr('selected', 'selected');
                }
              }
            }
          }
          $("#profile-region").multiselect();
          if (this.profiledata.GeographicAreasofInterest !== undefined) {
            if (this.profiledata.GeographicAreasofInterest.Countries !== undefined) {
              if (this.profiledata.GeographicAreasofInterest.Countries.length > 0) {
                for (var each in this.profiledata.GeographicAreasofInterest.Countriesid) {
                  $('select#profile-country-served option[value="' + this.profiledata.GeographicAreasofInterest.Countriesid[each] + '"]').attr('selected', 'selected');
                }
              }
            }
          }
          $("#profile-country-served").multiselect();
          if (this.profiledata.phone !== undefined) {
            $("input#profile-phone").val(this.profiledata.phone);
          }
          if (this.profiledata.email !== undefined) {
            $("input#profile-email").val(this.profiledata.email);
          }
          if (this.profiledata.misexp !== undefined) {
            $("input#profile-experience").val(this.profiledata.misexp);
          }  

          $(id).children("form").children("#wrapper").children("#contents").children(".profile-container").children(".profile-header").html("Please edit your profile information"); 
        //$("#profile-toggle-dialog").dialog('close');    
          $("#profile-volunteer-dialog").dialog('open');
        } else {
          // First modify the profile organizer dialog form, then display for editing

          var id = "#profile-organizer-dialog";

          if (this.profiledata.zip !== undefined) {
            $("input#profile-org-zipcode").val(this.profiledata.zip);
          }
          if (this.profiledata.AgencyName !== undefined) {
            $("input#profile-org-name").val(this.profiledata.AgencyName);
          }
          if (this.profiledata.AgencyWebsite !== undefined) {
            $("input#profile-org-website").val(this.profiledata.AgencyWebsite);
          }
          if (this.profiledata.about !== undefined) {
            $("input#profile-org-about").val(this.profiledata.about);
          }

          if (this.profiledata.FacilityMedicalOfferings !== undefined) {
            if (this.profiledata.FacilityMedicalOfferings.length>0) {
              for (var each in this.profiledata.FacilityMedicalOfferings) {
                $('select#profile-org-offer option[value="' + this.profiledata.FacilityMedicalOfferingsid[each] + '"]').attr('selected', 'selected');
              }
            }
          }
            $("#profile-org-offer").multiselect({selectedList: 11});
          if (this.profiledata.FacilityNon_MedicalOfferings !== undefined) {
            if (this.profiledata.FacilityNon_MedicalOfferings.length > 0) {
              for (var each in this.profiledata.FacilityNon_MedicalOfferings) {
                $('select#profile-org-offern option[value="' + this.profiledata.FacilityNon_MedicalOfferingsid[each] + '"]').attr('selected', 'selected');
              }
            }
          }
            $("#profile-org-offern").multiselect();
          if (this.profiledata.MedicalSkills !== undefined) {
            if (this.profiledata.MedicalSkills.length > 0) {
              for (var each in this.profiledata.MedicalSkills) {
                $('select#profile-org-medical option[value="' + this.profiledata.MedicalSkillsid[each] + '"]').attr('selected', 'selected');
              }
            }
          }
            $("#profile-org-medical").multiselect();
          if (this.profiledata.Non_MedicalSkills !== undefined) {
            if (this.profiledata.Non_MedicalSkills.length > 0) {
              for (var each in this.profiledata.Non_MedicalSkills) {
                $('select#profile-org-nonmedical option[value="' + this.profiledata.Non_MedicalSkillsid[each] + '"]').attr('selected', 'selected');
              }
            }
          }
            $("#profile-org-nonmedical").multiselect();
          if (this.profiledata.SpiritualSkills !== undefined) {
            if (this.profiledata.SpiritualSkills.length > 0) {
              for (var each in this.profiledata.SpiritualSkills) {
                $('select#profile-org-spiritual option[value="' + this.profiledata.SpiritualSkillsid[each] + '"]').attr('selected', 'selected');
              }
            }
          }
            $("#profile-org-spiritual").multiselect();
          if (this.profiledata.relg !== undefined) {
            $('select#profile-org-religion option[value="' + this.profiledata.relg + '"]').attr('selected', 'selected');
          }

          if (this.profiledata.Durations !== undefined) {
            if (this.profiledata.Durations.PreferredDurationofMissionTrips !== undefined) {
              $('select#profile-org-duration option[value="' + this.profiledata.Durations.PreferredDurationofMissionTripsid + '"]').attr('selected', 'selected');
            }
          }

          if (this.profiledata.States !== undefined) {
            if (this.profiledata.States.State !== undefined) {
              $('select#profile-org-state option[value="' + this.profiledata.States.Stateid + '"]').attr('selected', 'selected');
            }
          }
          if (this.profiledata.city !== undefined) {
            $("input#profile-org-city").val(this.profiledata.city);
          }
          if (this.profiledata.country !== undefined) {
            $("input#profile-org-country").val(this.profiledata.country);
          }
          if (this.profiledata.GeographicAreasofInterest !== undefined) {
            if (this.profiledata.GeographicAreasofInterest.Regions !== undefined) {
              if (this.profiledata.GeographicAreasofInterest.Regions.length > 0) {
                for (var each in this.profiledata.GeographicAreasofInterest.Regions) {
                  $('select#profile-org-region option[value="' + this.profiledata.GeographicAreasofInterest.Regionsid[each] + '"]').attr('selected', 'selected');
                }
              }
            }
          }
              $("#profile-org-region").multiselect();
          if (this.profiledata.GeographicAreasofInterest !== undefined) {
            if (this.profiledata.GeographicAreasofInterest.Countries !== undefined) {
              if (this.profiledata.GeographicAreasofInterest.Countries.length > 0) {
                for (var each in this.profiledata.GeographicAreasofInterest.Countries) {
                  $('select#profile-org-countryserved option[value="' + this.profiledata.GeographicAreasofInterest.Countriesid[each] + '"]').attr('selected', 'selected');
                }
              }
            }
          }
              $("#profile-org-countryserved").multiselect();
          if (this.profiledata.phone !== undefined) {
            $("input#profile-org-phone").val(this.profiledata.phone);
          }
          if (this.profiledata.email !== undefined) {
            $("input#profile-org-email").val(this.profiledata.email);
          }
          if (this.profiledata.misexp !== undefined) {
            $("input#profile-org-experience").val(this.profiledata.misexp);
          }

          $(id).children("form").children("#wrapper").children("#contents").children(".profile-container").children(".profile-header").html("Please edit your profile information");
        //$("#profile-toggle-dialog").dialog('close');    
          $("#profile-organizer-dialog").dialog('open');
        }
      } else {
        this.login($.proxy(function (response) {
          if (response && response.authResponse) {
            this.editProfile(isNewProfile);
          }
        }, this));
      }
    }, this));
    this.endFunction();
  },

  retrieveOneFormField : function (formSelector, fieldSelector, allowEmptyFieldValues) {
    this.beginFunction();
    allowEmptyFieldValues = allowEmptyFieldValues || false; // optional argument
    var fieldObject = $(formSelector).find(fieldSelector);
    var ret = undefined;
    if (fieldObject) {
      if (fieldObject.size() > 0) {
        if (fieldObject.size() == 1) {
          var fieldValue = fieldObject.val();
          if (fieldValue != "" || allowEmptyFieldValues) {
            ret = fieldValue;
          }
        } else {
          this.assert("more than one result for form selector: " + formSelector + ", field: " + fieldSelector);
        }
      } else {
        this.assert("form field is missing! form: " + formSelector + ", field: " + fieldSelector);
      }
    } else {
      this.assert("null value from form selector: " + formSelector + ", field: " + fieldSelector);
    }
    this.endFunction();
    return ret;
  },

  // Please DON'T access this.ignorableFormFields directly. Always use
  // fetchIgnorableFormFields()! It'll make your life easier, promise :) --zack
  fetchIgnorableFormFields : function (forceRefetch) {
    this.beginFunction();
    forceRefetch = forceRefetch || false; // optional argument
    if (!this.ignorableFormFields || forceRefetch) {
      this.ignorableFormFields = {};
      var subcontext = this;
      $(".cmc-default-opt").each(function (index, element) {
        var elem = $(element);
        var value = elem.val();
        var html = elem.html();
        $.proxy(function () {
          if (value && value != "" && !this.ignorableFormFields.hasOwnProperty(value)) {
            this.ignorableFormFields[value] = true;
          }
          if (html && html != "" && html != value && !this.ignorableFormFields.hasOwnProperty(html)) {
            this.ignorableFormFields[html] = true;
          }
        }, subcontext)();
      });
    }
    this.endFunction();
    return this.ignorableFormFields;
  },

  stripIgnorableFormFields : function (dataToStrip) {
    this.beginFunction();
    var ignorableFields = this.fetchIgnorableFormFields();
    for (var each in dataToStrip) {
      if (dataToStrip.hasOwnProperty(each)) {
        if (dataToStrip[each] !== undefined && ignorableFields.hasOwnProperty(dataToStrip[each]) && ignorableFields[dataToStrip[each]]) {
          delete dataToStrip[each];
        }
      }
    }
    this.endFunction();
    return dataToStrip;
  },

  getFormData : function (formSelector, allowEmptyFieldValues) {
    this.beginFunction();
    allowEmptyFieldValues = allowEmptyFieldValues || false; // optional argument
    var ret = {};
    var formFieldContainerObject = $(formSelector).find(".cmc-form-spec");
    this.assert(formFieldContainerObject.size() > 0, "form " + formSelector + " did not contain any values!");
    formFieldContainerObject.each(function (index, element) {
      var fieldID = $(element).attr("id");
      var field = CMC.retrieveOneFormField(formSelector, "#" + fieldID, allowEmptyFieldValues);
      if (field != undefined) {
        ret[fieldID] = field;
      }
    });
    ret = this.stripIgnorableFormFields(ret);
    this.endFunction();
    return ret;
  },

  submitProfile : function (profileData) {
    this.beginFunction();
    var zipisvalid = false;
    var emailisvalid = false;
    var reason="";
    var errornum=1;
    var ret = false;

    if (profileData.hasOwnProperty("profile-zipcode")) {
      zipisvalid = this.validateZipCode(profileData["profile-zipcode"]);
      if (!zipisvalid) {
        reason += errornum+'. Incorrect Zipcode format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (profileData.hasOwnProperty("profile-email")) {
      emailisvalid = this.validateEmail(profileData["profile-email"]);
      if (!emailisvalid) {
        reason += errornum + '. Incorrect Email format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (profileData.hasOwnProperty("profile-phone")) {
      var country = profileData.hasOwnProperty("profile-country") ? profileData["profile-country"] : null;
      var phoneerror = this.validatePhone(profileData["profile-phone"], country);
      if (phoneerror != "") {
        reason += errornum + ' ' + phoneerror + '\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (reason != "") {
      alert('Some input fields need correction:\n'+ reason);
    } else {
      var profileformdata = {};

      profileformdata.profiletype=1;
      if (CMC.profileedit == 1) {
        profileformdata.update = 1;
      }

      $.extend(profileformdata, this.applyTranslationMap(profileData, this.BackendTranslation.Profile));

      $.ajax({
        type: "POST",
        url: "api/profilein.php",
        data: {
          fbid: CMC.me.id ? CMC.me.id : "",
          profileinfo: encode64(JSON.stringify(profileformdata))
        },
        dataType: "json",
        context: this,
        success: this.onSubmitSuccess,
        error: this.onSubmitFailure
      });
      ret = true;
    }
    this.endFunction();
    return ret;
  },
  
   onSubmitSuccess : function(data, textStatus, jqXHR) {
     this.beginFunction();
     if (!data.has_error) {
       CMC.getProfile(CMC.me.id, this.handleGetProfileCallbackSaveAndShow);
       $("#profile-volunteer-dialog").dialog('close');
       alert('Thank you - your submission has been successfully entered into our database');
     } else {
       alert('We are sorry - there was an error: ' + data.err_msg);
     }
     this.endFunction();
   },

   onSubmitFailure : function(data, textStatus, jqXHR) {
     this.beginFunction();
     alert('We are sorry - there was an error: ' + data.err_msg);
     this.endFunction();
   },

   submitorgProfile : function (profileData) {
    this.beginFunction();
    var zipisvalid = false;
    var emailisvalid = false;
    var reason="";
    var isValid;
    var errornum=1;
    var ret = false;

    if (profileData.hasOwnProperty("profile-org-website")) {
      if (!CMC.isUrl(profileData["profile-org-website"])) {
        reason += errornum+'. Incorrect Website Entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }
    
    if (profileData.hasOwnProperty("profile-org-zipcode")) {
      zipisvalid = CMC.validateZipCode(profileData["profile-org-zipcode"]);
      if (!zipisvalid) {
        reason += errornum+'. Incorrect Zipcode format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (profileData.hasOwnProperty("profile-org-email")) {
      emailisvalid = CMC.validateEmail(profileData["profile-org-email"]);
      if (!emailisvalid) {
        reason += errornum + '. Incorrect Email format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (profileData.hasOwnProperty("profile-org-phone")) {
      var country = profileData.hasOwnProperty("profile-org-country") ? profileData["profile-org-country"] : null;
      var phoneerror = CMC.validatePhone(profileData["profile-org-phone"], country);
      if (phoneerror != "") {
        reason += errornum + ' ' + phoneerror + '\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }
    
    if (reason != "") {
      alert('Some input fields need correction:\n'+ reason);
    } else {
      var profileformdata = {};
      profileformdata.profiletype=3;

      if (this.profileedit == 1) {
         profileformdata.update = 1;
      }

      $.extend(profileformdata, this.applyTranslationMap(profileData, this.BackendTranslation.OrganizerProfile));

      $.ajax({
        type: "POST",
        url: "api/profilein.php",
        data: {
           fbid: CMC.me.id ? CMC.me.id : "",
           profileinfo: encode64(JSON.stringify(profileformdata))
        },
        context : this,
        dataType: "json",
        success: this.onSubmitorgSuccess,
        error: this.onSubmitorgFailure
      });
   
      ret = true;
    }
    
    this.endFunction();
    return ret;
  },

  onSubmitorgSuccess : function(data, textStatus, jqXHR) {
    this.beginFunction();
    if (!data.has_error) {
      this.getProfile(this.me.id, this.handleGetProfileCallbackSaveAndShow);
      $("#profile-organizer-dialog").dialog('close');
      alert('Thank you - your submission has been successfully entered into our database');
    } else {
      alert('We are sorry - there was an error: ' + data.err_msg);
    }
    this.endFunction();
  },

  onSubmitorgFailure : function(data, textStatus, jqXHR) {
    this.beginFunction();
    alert("We are sorry, the profile was not submitted with the following error: " + data.err_msg);
    this.endFunction();
  },

  submitTripProfile : function (profileData) {
    this.beginFunction();
    var zipisvalid = false;
    var emailisvalid = false;
    var reason="";
    var isValid;
    var errornum=1;
    var ret = false;
     var tripdepart;
     var tripreturn;
     var DepartMonth,DepartDay,DepartYear;
     var ReturnMonth,ReturnDay,ReturnYear;
	
    if (profileData.hasOwnProperty("profile-trip-website")) {
      if (!CMC.isUrl(profileData["profile-trip-website"])) {
        reason += errornum+'. Incorrect Website Entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }
    
    if (profileData.hasOwnProperty("profile-trip-zipcode")) {
      zipisvalid = CMC.validateZipCode(profileData["profile-trip-zipcode"]);
      if (!zipisvalid) {
        reason += errornum+'. Incorrect Zipcode format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (profileData.hasOwnProperty("profile-trip-email")) {
      emailisvalid = CMC.validateEmail(profileData["profile-trip-email"]);
      if (!emailisvalid) {
        reason += errornum + '. Incorrect Email format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

    if (profileData.hasOwnProperty("profile-trip-phone")) {
      var country = profileData.hasOwnProperty("profile-trip-country") ? profileData["profile-trip-country"] : null;
      var phoneerror = CMC.validatePhone(profileData["profile-trip-phone"], country);
      if (phoneerror != "") {
        reason += errornum + ' ' + phoneerror + '\n';
        errornum = errornum + 1;
        isValid = false;
      }
    }

	if (profileData.hasOwnProperty("profile-trip-depart")) {
	tripdepart = profileData["profile-trip-depart"];
    if (tripdepart == "select") {
      reason += errornum + ' ' + 'Trip should have a depart date' + '\n';
      errornum = errornum + 1;
      isValid = false;
    }
	}
	if (profileData.hasOwnProperty("profile-trip-return")) {
	tripreturn = profileData["profile-trip-return"];
    if (tripreturn == "select") {
      reason += errornum + ' ' + 'Trip should have a return date' + '\n';
      errornum = errornum + 1;
      isValid = false;
    }
	}

     var TDeparture, TReturn;
    // Logic to determine that the trip begin date is before the trip end date
      if (tripdepart != "") {
          var departdate = tripdepart.split(".");
          DepartMonth=parseInt(departdate[0],10);   
          DepartDay=parseInt(departdate[1],10);   
          DepartYear=parseInt(departdate[2],10);
      TDeparture = new Date();
      TDeparture.setFullYear(DepartYear,DepartMonth,DepartDay);
      }
      if (tripreturn != "") {
          var returndate = tripreturn.split(".");
          ReturnMonth=parseInt(returndate[0],10);   
          ReturnDay=parseInt(returndate[1],10);   
          ReturnYear=parseInt(returndate[2],10);  
      TReturn = new Date();
      TReturn.setFullYear(ReturnYear,ReturnMonth,ReturnDay);      
      }
    
    if (TDeparture > TReturn) {
          reason += errornum + ' ' + 'Trip departure date should be before the return date' + '\n';
      errornum = errornum + 1;
      isValid = false;
    }
    
    if (reason != "") {
      alert('Some input fields need correction:\n'+ reason);
    } else {
      var profiletripformdata = {};
      profiletripformdata.profiletype=2;

      profiletripformdata.DepartMonth = DepartMonth;
	  profiletripformdata.DepartDay = DepartDay;
	  profiletripformdata.DepartYear = DepartYear;
	  profiletripformdata.ReturnMonth = ReturnMonth;
	  profiletripformdata.ReturnDay = ReturnDay;
	  profiletripformdata.ReturnYear = ReturnYear;

      $.extend(profiletripformdata, this.applyTranslationMap(profileData, this.BackendTranslation.TripProfile));

      $.ajax({
        type: "POST",
        url: "api/profilein.php",
        data: {
           fbid: CMC.me.id ? CMC.me.id : "",
           profileinfo: encode64(JSON.stringify(profiletripformdata))
        },
        context : this,
        dataType: "json",
        success: this.onSubmitTripSuccess,
        error: this.onSubmitTripFailure
      });
   
      ret = true;
    }
    
    this.endFunction();
    return ret;
  },	
  
  onSubmitTripSuccess : function(data, textStatus, jqXHR) {
    this.beginFunction();
    if (!data.has_error) {
      $("#profile-trip-dialog").dialog('close');
      this.refreshUserProfile();
      if (this.tripedit) {
        alert('Congratulations! Your trip has been edited!');
      } else {
        alert('Congratulations! Your trip has been created!');
      }	  
    } else {
      if (this.tripedit) {
        alert('We are sorry, your trip was not edited because: ' + data.err_msg);
      }
      else {
        alert('We are sorry - there was an error: ' + data.err_msg);
      }
    }
    this.endFunction();
  },

  onSubmitTripFailure : function(data, textStatus, jqXHR) {
    this.beginFunction();
	  if (this.tripedit) {
      alert('We are sorry, your trip was not edited because: ' + data.err_msg);
	  } else {	
      alert("We are sorry, the trip was not submitted with the following error: " + data.err_msg);
	  }
    this.endFunction();
  },  
  
  createTrip : function () {
    $("#profile-trip-dialog").dialog('open');
  },

  // validation functions

  validateZipCode : function (elementValue) {
    var zisValid = false;
    var zipCodePattern = /^\d{5}$|^\d{5}-\d{4}$/;
    zisValid = zipCodePattern.test(elementValue);
    return zisValid;
  },

  validateEmail : function (email) {
    var eisValid =  false;
    var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+[\.]{1}[a-zA-Z]{2,4}$/;  
    eisValid = emailPattern.test(email);  
    return eisValid;
  },

  isUrl : function (s) {
    var regexp = /((ftp|http|https):\/\/)?(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
      return regexp.test(s);
  },

  validatePhone : function (fld,country) {
    var error = "";
    /*var stripped = fld.replace(/[\(\)\.\-\ ]/g, ''); 
    // for international numbers
    var regex = /^\+(?:[0-9] ?){6,14}[0-9]$/;
    if (isNaN(parseInt(stripped))) {
      error = "The phone number contains illegal characters.\n";
    } else if (country != "United States") {
      if (!regex.test(fld)) {
        error = "The phone number is not a valid International Number.\n";
      }
    } else if (!(stripped.length == 10)) {
      error = "The phone number is the wrong length. Make sure you included an area code.\n";
    }*/
    return error;
  },

  // login functions

  handleFacebookResponseError : function (response) {
    this.beginFunction();
    if (response.error) {
      this.assert(response.error.message, "facebook error response didn't contain a message!");
      this.assert(response.error.type, "facebook error response didn't contain a type!");
      this.error("caught error from Facebook API call -- " + response.error.type + ": " + response.error.message);
    } else {
      this.assert("handling a Facebook error when apparently none happened.");
    }
    this.endFunction();
  },

  handleUserUnauthorized : function () {
    this.beginFunction();
    $("#no-profile").fadeIn();
    this.endFunction();
  },

  cacheFacebookResponseProperties : function (response) {
    this.beginFunction();
    if (response) {
      if (response.hasOwnProperty("authResponse") && response.authResponse) {
        if (response.authResponse.hasOwnProperty("accessToken")) {
          this.accessToken = response.authResponse.accessToken;
        } else {
          this.assert("Facebook authResponse did not contain an access token");
          this.accessToken = null;
          // this is about the time that we should surface an error to the user, no? --zack
        }
      } else {
        // this is actually a valid case -- this happens when the user has not
        // authorized the app. --zack
        this.accessToken = null;
      }
    } else {
      this.assert("Facebook did not return a response at all, can't cache data");
    }
    this.endFunction();
  },

  cacheFacebookData : function (callback) {
    this.beginFunction();
    CMC.ajaxNotifyStart();
    var fbapiCallbacksCompleted = 0;
    var __notifyFBAPICallbackCompleted = function () {
      if (++fbapiCallbacksCompleted >= 3) { // number must match number of calls to __notifyFBAPICallbackCompleted below
        if (callback) {
          callback();
        }
      }
    };
    FB.api('/me', $.proxy(function (response) {
      this.ajaxNotifyComplete();
      if (response) {
        if (response.error) {
          this.handleFacebookErrorResponse(response);
        } else {
          this.log("got user data from Facebook");
          this.me = response;
          // This is the default profile display - showing the logged in user's profile
          this.getProfile(this.me.id, function (data) {
            this.handleGetProfileCallbackSave(data);
            __notifyFBAPICallbackCompleted();
          });
          this.log(this.me.name + " (" + this.me.id + ") logged in to the app");
          // Get upcoming trips information
          this.getFutureTrips();
          __notifyFBAPICallbackCompleted();
        }
      } else {
        this.error("FB API call failed: can't cache user data (invalid response)");
      }
    }, this));
    CMC.ajaxNotifyStart();
    FB.api('/me/friends', $.proxy(function (friends) {
      this.ajaxNotifyComplete();
      if (friends) {
        if (friends.error) {
          this.handleFacebookErrorResponse(friends);
        } else {
          this.log("got friend data from Facebook");
          this.friends = friends.data;
        }
        __notifyFBAPICallbackCompleted();
      } else {
        this.error("FB API call failed: can't get friends list (invalid response)");
      }
    }, this));
    this.endFunction();
  },

  checkFacebookLoginStatus : function (callback) {
    this.beginFunction();
    //@/BEGIN/DEBUGONLYSECTION
    $("#logged-in-user-value").html("(synchronizing)");
    //@/END/DEBUGONLYSECTION
    CMC.ajaxNotifyStart();
    var userLoggedIn = false;
    var __timerLoginTimeout = setTimeout($.proxy(function () {
      this.ajaxNotifyComplete();
      if (!userLoggedIn) {
        this.error("Facebook did not respond to login status handshake in time");
        //@/BEGIN/DEBUGONLYSECTION
        $("#logged-in-user-value").html("(handshake failure!)");
        //@/END/DEBUGONLYSECTION
      } else {
        this.assert("userLoggedIn=true, but the timeout still fired");
      }
    }, this), 2500);
    var __notifyLoginComplete = function () {
      userLoggedIn = true;
      clearTimeout(__timerLoginTimeout);
    };
    FB.getLoginStatus(function(response) {
      CMC.ajaxNotifyComplete();
      CMC.log("got the response for FB.getLoginStatus()");
      CMC.assert(!userLoggedIn, "user already logged in when the handshake callback fired");
      __notifyLoginComplete();
      CMC.cacheFacebookResponseProperties(response);
      //@/BEGIN/DEBUGONLYSECTION
      if (response.authResponse) {
        $("#logged-in-user-value").html(response.authResponse.userID);
      } else {
        $("#logged-in-user-value").html("(not authorized)");
      }
      //@/END/DEBUGONLYSECTION
      if (callback) {
        callback(response);
      }
    });
    this.endFunction();
  },

  login : function (callback) {
    // this is a wrapper API to handle user clicks that require Facebook authorization
    this.beginFunction();
    //@/BEGIN/DEBUGONLYSECTION
    $("#logged-in-user-value").html("(synchronizing)");
    //@/END/DEBUGONLYSECTION
    CMC.ajaxNotifyStart();
    FB.login(function (response) {
      CMC.ajaxNotifyComplete();
      if (response.authResponse) {
        CMC.log("user " + response.authResponse.userID + " has just logged in to the app");
        CMC.cacheFacebookResponseProperties(response);
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
    this.endFunction();
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

  $("#make-profile, #make-volunteer, #make-organizer").hide();

  $("#make-trip, #profile-trip-dialog").hide();

  $("#make-volunteer-link").click(function() {
    CMC.isreceiver = false;
    CMC.editProfile(true /* isNewProfile */);
  });

  $("#profile-volunteer-dialog").dialog({
    autoOpen: false,
    draggable: true,
    position: [25, 25],
    resizable: true,
    width: 700,
    height: 465,
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
    position: [25, 25],
    resizable: true,
    width: 700,
    height: 465,
    open: function() {
      CMC.dialogOpen(this);
    },
    close: function() {
      CMC.dialogClose(this);
    }
  });  

  $("#make-organizer-link").click(function() {
    CMC.isreceiver = true;
    CMC.editProfile(true /* isNewProfile */);
  });

  $("#profile-organizer-dialog").dialog({
    autoOpen: false,
    draggable: true,
    position: [25, 25],
    resizable: true,
    width: 700,
    height: 465,
    open: function() {
      CMC.dialogOpen(this);
    },
    close: function() {
      CMC.dialogClose(this);
    }
  });  
  
  $("#profile-toggle-dialog").dialog({
    autoOpen: false,
    draggable: false,
    position: ['center', 200],
    resizable: false,
    width: 150,
    height: 75,
    open: function() {
      CMC.dialogOpen(this);
    },
    close: function() {
      CMC.dialogClose(this);
    }
  });  
  
  //@/BEGIN/DEBUGONLYSECTION
  $("#assert-dialog").dialog({
    autoOpen: false,
    draggable: false,
    position: ['center', 100],
    resizable: false,
    modal: true,
    width: 400,
    height: 275,
  });  
  //@/END/DEBUGONLYSECTION
  
  $("#make-trip").click(function() {
    $("#profile-trip-languages").multiselect();
    $("#profile-trip-dialog").dialog('open');
  });

  $("#profile-trip-dialog").dialog({
    autoOpen: false,
    draggable: true,
    position: [25, 25],
    resizable: true,
    width: 700,
    height: 465,
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

  CMC.log("setting up profile control buttons");

  $("#profile-controls-edit")
    .button({ text: false, icons: { primary: "ui-icon-pencil" }})
    .click(function () {
      // FIXME: This appears to be a bug in jQuery; button disable state is not
      // set properly on the first access, as the statement inside never fires.
      // --zack
      //if (!$(this).button("option", "disabled")) {
        CMC.editProfile();
      //}
    });

  $("#profile-controls-create-trip")
    .button({ text: false, icons: { primary: "ui-icon-suitcase" }})
    .click(function () {
      // FIXME: Same bug as #profile-controls-edit.
      //if (!$(this).button("option", "disabled")) {
        CMC.createTrip();
      //}
    });

  $("#profile-controls-back-to-my-profile")
    .button({ text: false, icons: { primary: "ui-icon-arrowreturnthick-1-w" }})
    .click(function () {
      // FIXME: Same bug as #profile-controls-edit.
      //if (!$(this).button("option", "disabled")) {
        //CMC.navigateToPreviousSearchPage();
        CMC.getProfile(CMC.me.id, CMC.handleGetProfileCallbackSaveAndShow);
      //}
    });

  $("#trip-profile-controls-back-to-trips")
    .button( { text: false, icons: { primary: "ui-icon-arrowreturnthick-1-w" }})
    .click(function () {
        // FIXME: Same as #profile-controls-edit.
        //if (!$(this).button("option, "disabled")) {
          CMC.getFutureTrips();
        //}
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
  $("#search-tipbar-left .tipbar-link").click(function () {
    $(this).focus();
    $(this).tipTip('show');
  }).blur(function () {
    $(this).tipTip('hide');
  }).tipTip({
    activation: 'manual',
    keepAlive: true,
    maxWidth: '230px',
    forceWidth: true,
    delay: 0,
    defaultPosition: 'bottom',
    forcePosition: true,
    content: $("#search-tipbar-left .tipbar-content").html()
  });

  $("#search-tipbar-right .tipbar-link").click(function () {
    $(this).focus();
    $(this).tipTip('show');
  }).blur(function () {
    $(this).tipTip('hide');
  }).tipTip({
    activation: 'manual',
    keepAlive: true,
    maxWidth: '230px',
    forceWidth: true,
    delay: 25,
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

//    .click(function () { CMC.getProfile(data.memberids[each], CMC.showProfile); })	
	
  // this should fix the junk picture assert on first search
  CMC.log("clearing the placeholder images");
  $(".result-picture img").remove();

  CMC.log("attempting to get facebook login status");
  CMC.checkFacebookLoginStatus(function (response) {
    if (response.authResponse) {
      CMC.loggedInUserID = response.authResponse.userID;
      CMC.log("user " + CMC.loggedInUserID + " is already logged in, cache their data");
      CMC.cacheFacebookData(function () {
        CMC.showProfile(CMC.profiledata);
      });
    } else {
      CMC.log("authResponse is null; no user session, do not cache data yet");
      CMC.handleUserUnauthorized();
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
      if ($("#report-problem-message").val().length <= 0 && !$.browser.webkit) {
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

  if (!$.browser.webkit) {
    // it appears that webkit-based browsers like Safari and Chrome have
    // problems with rendering this. not sure why, but fixing it anyway --zack
    $("#report-problem-characters-left").hide();
  }

  $("#report-problem-message")
    .click(function() { CMC.recalculateProblemMessageLimit(); })
    .focus(function() { CMC.recalculateProblemMessageLimit(); })
    .keyup(function() { CMC.recalculateProblemMessageLimit(); })
    .keypress(function() { CMC.recalculateProblemMessageLimit(); });

  $("#profile-submit").click(function() {
    CMC.submitProfile(CMC.getFormData("#profile-volunteer-form"));
  });

  $("#profile-org-submit").click(function() {
      CMC.submitorgProfile(CMC.getFormData("#profile-organizer-form"));
  });

  $("#profile-trip-submit").click(function() {
	CMC.submitTripProfile(CMC.getFormData("#profile-trip-form"));
 });

  // Handles the live form validation
  $("#profile-trip-name").validate({
    expression: "if (VAL) return true; else return false;",
    message: "Trip name is a required field"
  });
  //$("#profile-org-website").validate[optional,custom[url]];
  //$("#profile-trip-website").validate[optional,custom[url]];  
  
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
    message: "Please enter a valid Phone Number: For Example: 1234567899"
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

  // this should be the last thing that happens
  CMC.log("load callback complete, fading in canvas");
  $("#loading").fadeOut(function() {
    $("#tabs").hide().fadeIn(function() {
      $("#cmc-footer").hide().delay(150).fadeIn();
    });
  });

});
// vim: ai:et:ts=2:sw=2
