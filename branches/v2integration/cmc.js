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
  accessToken : false,
  me : false,
  friends : false,
  profiledata :  {},
  profileshowflag: 0,
  isreceiver : false,
  profileexists : false,
  profileedit : false,
  requestsOutstanding : 0,
  showuserid : false,
  tripuserid : false,
  dialogsOpen : 0,
  version : "1.9.18",
  searchPageCache : [],
  currentDisplayedSearchPage : 0,
  searchPageImageClearJobQueue : [],
  SearchState : {},
  prelength : [],
  postlen : [],
  currentOptions : [],
  tripsjoinbtns : [],
  tripdescbtns : [],
  tripinvitebtns : [],
  tripsdescbtns : [],

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
  },

  getProfile : function(userid) {
    this.beginFunction();
    this.log("Obtaining data from the profile");
    this.ajaxNotifyStart(); // one for good measure, we want the spinner for the whole search
	
	  this.showuserid = userid;

    $.ajax({
      type: "POST",
      url: "api/profile.php",
      data: {
        fbid: userid
      },
      dataType: "json",
      context: this,
      success: this.onGetProfileDataSuccess,
      error: this.onGetProfileDataError
    });
    this.endFunction();
	},
  
  onGetProfileDataSuccess : function(data, textStatus, jqXHR) {
    this.beginFunction();
    this.assert(data != undefined, "data is undefined in onGetProfileDataSuccess");
    if(data.has_error !== undefined && data.has_error !== null) {
      if(data.has_error) {
        // first handle the no profile error - simply display a new profile creation form
        if (data.exists == 0) {
          this.showProfile(null);
        } else {
          // we have a known error, handle it
          this.handleGetProfileDataSuccessHasError(data);
        }
      } else {
        if (data.isreceiver==0) {
          // This is the case of a volunteer profile
          this.isreceiver = 0;
        } else {
          // profile is os a mission organizer
          this.isreceiver = 1;
        }
        this.profiledata = data;
        CMC.log("Calling showProfile with profile data");
        if (CMC.profileshowflag == 1) {
          this.showProfile(data);
        } else {
          this.ajaxNotifyComplete();
        }
      }
    } else {
      // an unknown error occurred? do something!
      this.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "profile data");
    }
    this.endFunction();
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
  
  showProfile : function (data) {
    this.beginFunction();
    if (data === undefined) {
      // this should be a bug! do NOT pass this function undefined! say null to inform it that you have no results!
      this.assert(data === undefined, "undefined passed as results for showProfile");
    } else if (data == null) {
      // no profile exists - so display the new profile creation dialogs
      $("#no-profile").fadeIn();
    } else {
      var id = "#profilecontent";
      this.ajaxNotifyStart();
      this.assert(data.name !== undefined, "name is missing from result set");
      this.updateProfileControls(CMC.showuserid);
      $("#profile-name").html(data.name ? data.name : "");
      $("img.profile-picture").attr("src", "http://graph.facebook.com/"+this.showuserid+"/picture?type=large");
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

          $(id).children("#profile-right-column").children(".box1").children(".profile-nonmedskills").html(data.Non_MedicalSkills ? eachstr : "");
        } else {
          $(id).children("#profile-right-column").children(".box1").children(".profile-nonmedskills").html("");
        }
      }

      //display profile information
      if (data.email == undefined) {
        $("#profile-email").html("<h6></h6>");
      } else {
        $("#profile-email").html(data.email ? "<h6>" + data.email + "</h6>" : "");
      }

      if (data.phone == undefined) {
        $("#profile-phone").html("<h6></h6>");
      } else {
        $("#profile-phone").html(data.phone ? "<h6>" + data.phone + "</h6>" : "");
      }

      if (data.country == undefined) {
        $("#profile-country").html("<h6></h6>");
      } else {
        $("#profile-country").html(data.country ? "<h6>" + data.country + "</h6>" : "");
      }


      if (data.zip == undefined) {
        $("#profile-zip").html("<h6></h6>");
      } else {
        $("#profile-zip").html(data.zip ? "<h6>" + data.zip + "</h6>" : "");
      }

      if (data.Durations == undefined) {
        $("#profile-dur").html("<h6></h6>");
      } else {
        $("#profile-dur").html(data.Durations.PreferredDurationofMissionTrips ? "<h6>" + data.Durations.PreferredDurationofMissionTrips + "</h6>" : "");
      }


      if (data.GeographicAreasofInterest == undefined) {
        $("#profile-countries").html("<h6></h6>");
      } else {
        $("#profile-countries").html(data.GeographicAreasofInterest.Countries ? "<h6>" + data.GeographicAreasofInterest.Countries + "</h6>" : "");
      }


      if (data.trips == undefined) {
        $(id).children("#profile-right-column").children("#table_wrapper").children("#tbody").html("<table></table>");
      } else {
        //finally update the trips information
        if (data.trips.length > 0) {

          var eachstr = "";
          for (var each in data.trips) {

            eachstr += "<tr>";
            eachstr += "<td>";
            eachstr += "<div class=\"profile-picture-" + each + "\">";
            eachstr += "<img src=\"ajax-spinner.gif\"/>";
            eachstr += "</div>";
            eachstr += "</td>";
            eachstr += "<td><div class=\"box3\">";
            eachstr += "<div class=\"profile-tripname-" + each + "\">";
            eachstr += "<h4> TripName </h4>";
            eachstr += "</div>";
            eachstr += "</td>";
            eachstr += "<td class=\"td2\"><input type=\"submit\" value=\"Trip Description\" class=\"button\" id=\"trip-desc-submit-" + each + "\"/></td>";
            eachstr += "<td class=\"td2\"><input type=\"submit\" value=\"Invite To Trip\" class=\"button\" id=\"invite-trip-submit-" + each + "\"/></td>";
            eachstr += "</tr>";
            eachstr += "</div>";

            this.tripinvitebtns[each] = "invite-trip-submit-"+each;
            this.tripdescbtns[each] = "trip-desc-submit-"+each;

          }

          // replace the existing template with the new template that is the length of the trips array
          $(id).children("#profile-right-column").children("#table_wrapper").children("#tbody").html("<table>" + eachstr + "</table>");

          //Now update the new template with the trips information
          for (var each in data.trips) {      
            $(id).children("#profile-right-column").children("#table_wrapper").children("#tbody").find(".profile-picture-"+each).children("img").attr("src", "http://graph.facebook.com/"+this.me.id+"/picture?type=small");
            $(id).children("#profile-right-column").children("#table_wrapper").children("#tbody").find(".profile-tripname-"+each).html(data.trips[each] ? data.trips[each] : "");
          }			

        } else {
          $(id).children("#profile-right-column").children("#table_wrapper").children("#tbody").html("<table></table>");
        }

      }
      $("#show-profile").fadeIn();
    } // end else
    this.ajaxNotifyComplete(); // finish the one we started at the beginning of profile retrieval
    this.endFunction();
  },	
  
  onGetProfileDataError : function(jqXHR, textStatus, errorThrown) {
    // we might also want to log this or surface an error message or something
    this.handleGenericServerError(jqXHR, textStatus, errorThrown);
  },

  handleGetProfileDataSuccessHasError : function(data) {
    this.beginFunction();
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

  getFutureTrips : function() {
    this.beginFunction();
    this.log("Obtaining future trip information from the database");
    $.ajax({
      type: "POST",
      url: "api/searchtrips.php",
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
          this.UpdateFutureTrips(data);
      }
    } else {
      // an unknown error occurred? do something!
      this.handleGenericUnexpectedCallbackError(data, textStatus, jqXHR, "future trips data");
    }
    this.endFunction();
	},  

  UpdateFutureTrips : function (data) {
    this.beginFunction();
    if (data === undefined) {
      // this should be a bug! do NOT pass this function undefined! say null to inform it that you have no results!
      this.assert(data === undefined, "undefined passed as results for UpdateFutureTrips");
    } else if (data === null) {
      // no future trips exist - so display new trip creation dialog
      $("#no-trip").fadeIn();
    } else {

        var id = "#show-trips";
		
        this.assert(data.tripnames !== undefined, "Trip names are missing from result set");
		this.assert(data.tripids !== undefined, "Trip IDs are missing from result set");

      if (data.tripnames === undefined) {
			$("#no-trip").fadeIn();
      }
      else {
			//finally update the upcoming trips information
			if (data.tripnames.length > 0) {
			
			var eachstr = "<h2>Upcoming Trips:</h2>";
			
			eachstr += "<table>";	
			for (var each in data.tripnames) {
				eachstr += "<tr>";
				//eachstr += "<td><div class=\"box3\">";
				eachstr += "<td>";
				eachstr += "<div class=\"trips-tripname-" + each + "\">";
				eachstr += "<h4> TripName </h4>";
				eachstr += "</div>";
				eachstr += "</td>";
				eachstr += "<td class=\"td2\"><input type=\"submit\" value=\"Trip Description\" class=\"button\" id=\"trips-desc-submit-" + each + "\"/></td>";
				eachstr += "<td class=\"td2\"><input type=\"submit\" value=\"Join This Trip\" class=\"button\" id=\"join-trips-submit-" + each + "\"/></td>";
				eachstr += "</tr>";
				//eachstr += "</div>";
				
				this.tripsjoinbtns[each] = "join-trips-submit-"+each;
				this.tripsdescbtns[each] = "trips-desc-submit-"+each;
			}
			eachstr += "</table>";
	
			// replace the existing template with the new template that is the length of the upcoming trips array
			$(id).html(eachstr);
		

			//Now update the new template with the trips information
			for (var each in data.tripnames) {      
				$(id).find(".trips-tripname-"+each).html(data.tripnames[each] ? "<h4> "+data.tripnames[each] + "</h4>" : "");
			}			
			
			$("#show-trip-profile").fadeOut();
      $("#backtotrips").hide();
			$("#show-trips").fadeIn();

			}
			else {
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

  GetTripProfile : function(index) {
	  this.beginFunction();
      $.ajax({
        type: "POST",
        url: "api/profileT.php",
        data: {
		    tripid: parseInt(CMC.profiledata.tripid[index],10),
            fbid: CMC.me.id ? CMC.me.id : ""
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
		
        $(id).children("#profile-left-column").children(".box2").children(".profile-trip-owner").html(data.tripowner ? data.tripowner : "");
       
        this.tripuserid = data.creatorid;

        $(id).children("#profile-left-column").children("#tripprofileimage").children(".trip-owner-picture").children("img").attr("src", "http://graph.facebook.com/"+data.creatorid+"/picture?type=large");
        this.ajaxNotifyComplete();
			
        $(id).children("#profile-left-column").children(".box2").children(".trip-profile-about").html(data.tripdesc ? "<h4>" + data.tripdesc + "</h4>" : "");
			
	  //display Trip profile information
      if (data.tripname === undefined) {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-name").html("<h6></h6>");
      }
      else {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-name").html(data.tripname ? "<h6>" +data.tripname + "</h6>": "");
      }
	 
      if (data.website === undefined) {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-url").html("<h6></h6>");
      }
      else {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-url").html(data.website ? "<h6>" +data.website + "</h6>": "");
      }	 
	  
      if ((data.destination === undefined) && (data.destinationcountry === undefined)) {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-dest").html("<h6></h6>");
      }
      else if (data.destination === undefined) {
		$(id).children("#profile-right-column").children(".box1").children(".profile-trip-dest").html("<h6>" +data.destinationcountry+ "</h6>");
	  }
	  else if (data.destinationcountry === undefined) {
		$(id).children("#profile-right-column").children(".box1").children(".profile-trip-dest").html("<h6>" +data.destination+ "</h6>");
	  }
	  else {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-dest").html("<h6>" +data.destination + "," +data.destinationcountry+ "</h6>");
      }		  
	  
      if (data.email === undefined) {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-email").html("<h6></h6>");
      }
      else {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-email").html(data.email ? "<h6>" + data.email+ "</h6>" : "");
      }

      if (data.phone === undefined) {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-phone").html("<h6></h6>");
      }
      else {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-phone").html(data.phone ? "<h6>" + data.phone+ "</h6>" : "");
      }
	  
      if (data.tripstage === undefined) {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-stage").html("<h6></h6>");
      }
      else {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-stage").html(data.tripstage ? "<h6>" + data.tripstage + "</h6>": "");
      }	  

	  if (data.departyear === undefined) {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-depart").html("<h6></h6>");
      }
      else {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-depart").html("<h6>" +data.departyear ? data.departmonth +"/"+data.departday+"/"+data.departyear +"</h6>": "");
      }	
	  
	  if (data.returnyear === undefined) {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-return").html("<h6></h6>");
      }
      else {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-return").html("<h6>" + data.returnyear ? data.returnmonth +"/"+data.returnday+"/"+data.returnyear + "</h6>": "");
      }	  

      if (data.religion === undefined) {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-religion").html("<h6></h6>");
      }
      else {
      $(id).children("#profile-right-column").children(".box1").children(".profile-trip-religion").html(data.religion ? "<h6>" + data.religion + "</h6>" : "");
      }
	  
      if (data.numpeople === undefined) {
			$(id).children("#profile-right-column").children(".box1").children(".profile-trip-numpeople").html("<h6></h6>");
      }
      else {
      $(id).children("#profile-right-column").children(".box1").children(".profile-trip-numpeople").html(data.numpeople ? "<h6>" + data.numpeople + "</h6>" : "");
      }

      // change to the Trips Tab
      $("#tabs").tabs('select', 2);

	    $("#show-trips").hide();
      $("#backtotrips").fadeIn();
      $("#show-trip-profile").fadeIn();
    } // end else
	
    this.endFunction();
  },
  
     JoinTrip : function(index) {
      $.ajax({
        type: "POST",
        url: "api/addtripmember.php",
        data: {
		    tripid: CMC.profiledata.tripid[index],
			  fbid: CMC.me.id ? CMC.me.id : "",
		    type: 2
        },
        dataType: "json",
        context: this,
        success: function(data) {
          if (!data.has_error)
            alert('You have successfully joined this trip');
          else 
            alert('Sorry, you could not be added to the trip due to: ' + data.err_msg);
        },
        error: function(data) {
            alert('Sorry, you could not be added to the trip due to: ' + data.err_msg);
        }
      });
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
    this.endFunction();
  },

  search : function () {
    this.beginFunction();
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
      //@/BEGIN/DEBUGONLYSECTION
      if(!this.me.id) {
       this.log("this.me.id isn't available; using blank fbid for search");
      }
      //@/END/DEBUGONLYSECTION
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
    this.endFunction();
  },

  showSearchResults : function (results) {
    this.beginFunction();
    if (results === undefined) {
      // this is a bug! do NOT pass this function undefined! say null to inform it that you have no results!
      this.assert(results === undefined, "undefined passed as results for showSearchResults");
    } else if (results == null || results.length == 0) {
      // no results
      $("#cmc-search-results-noresultmsg").fadeIn();
    } else {
      var imageLoadsCompleted = 0, __notifyImageLoadCompleted = $.proxy(function() {
        imageLoadsCompleted++;
        this.assert(imageLoadsCompleted <= results.length, "loading more images than we have results for (" + imageLoadsCompleted + ")");
        if(imageLoadsCompleted == results.length) {
          this.animateShowSearchResults(results);
        }
      }, this);
      this.assert(results.length <= 10, "more than 10 results passed to showSearchResults");
      this.assert($(".result-picture img").length == 0, "found " + $(".result-picture img").length + " junk pictures lying around");
      for(var each in results) {
        this.assert(results[each].id !== undefined, "id is missing from result at each=" + each);
        var id = "#cmc-search-result-" + each;
        this.ajaxNotifyStart();
        this.assert(results[each].name !== undefined, "name is missing from result at each=" + each);
        $(id).children(".result-name").html(results[each].name ? results[each].name : "");
        $(id).attr("fbid", results[each].id);
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
                  this.log("incomplete page, hiding the the results that need cleanup");
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
      $(this).stop(true, true).fadeOut('fast', function () {
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
      this.assert(whichFBID != null && whichFBID != "", "fbid attr is null for clicked search result");
      this.getProfile(whichFBID);      
      this.animateSearchResultSelected(whichResult);
    } else {
      this.log("search result clicked, but name is empty; ignoring");
    }
    this.endFunction();
  },

  editProfile : function () {
    this.beginFunction();
    this.profileedit = 1;

    // Retrieve the profile data from the backend again to make sure it is the latest information, no need to show profile
    this.profileshowflag=0;
    this.getProfile(this.me.id);

      for (var i=0; i <7; i++) {
          this.currentOptions[i]=new Array();
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
          var arparts = JSON.stringify(this.profiledata.MedicalSkills).split(',');
          this.prelength[0] = arparts.length-1;
          this.postlen[0] = arparts.length;
          for (var each in this.profiledata.MedicalSkills) {
            $('select#profile-medical option[value="' + this.profiledata.MedicalSkillsid[each] + '"]').attr('selected', 'selected');
            this.currentOptions[0][each] = $('select#profile-medical option[value="' + this.profiledata.MedicalSkillsid[each] + '"]').attr('id');
          }
        }
        else {
          this.prelength[0] = 0;
          this.postlen[0] = 0;
        }
      }
      if (this.profiledata.Non_MedicalSkills !== undefined) {
        if (this.profiledata.Non_MedicalSkills.length > 0) {
          var arparts = JSON.stringify(this.profiledata.Non_MedicalSkills).split(',');
          this.prelength[1] = arparts.length-1;
          this.postlen[1] = arparts.length;
          for (var each in this.profiledata.Non_MedicalSkills) {
            $('select#profile-nonmedical option[value="' + this.profiledata.Non_MedicalSkillsid[each] + '"]').attr('selected', 'selected');
            this.currentOptions[1][each] = $('select#profile-nonmedical option[value="' + this.profiledata.Non_MedicalSkillsid[each] + '"]').attr('id');
          }
        }
        else {
          this.prelength[1] = 0;
          this.postlen[1] = 0;
        }
      }
      if (this.profiledata.SpiritualSkills !== undefined) {
        if (this.profiledata.SpiritualSkills.length > 0) {
          var arparts = JSON.stringify(this.profiledata.SpiritualSkills).split(',');
          this.prelength[2] = arparts.length-1;
          this.postlen[2] = arparts.length;
          for (var each in this.profiledata.SpiritualSkills) {
            $('select#profile-spiritual option[value="' + this.profiledata.SpiritualSkillsid[each] + '"]').attr('selected', 'selected');
            this.currentOptions[2][each] = $('select#profile-spiritual option[value="' + this.profiledata.SpiritualSkillsid[each] + '"]').attr('id');
          }
        }
        else {
          this.prelength[2] = 0;
          this.postlen[2] = 0;
        }
      }
      if (this.profiledata.relg !== undefined) {
        $("input#profile-religion").val(this.profiledata.relg);
      }

      if (this.profiledata.Durations !== undefined) {
        if (this.profiledata.Durations.PreferredDurationofMissionTrips !== undefined) {
          $("input#profile-duration").val(this.profiledata.Durations.PreferredDurationofMissionTrips);
        }
      }

      if (this.profiledata.States !== undefined) {
        if (this.profiledata.States.state !== undefined) {
          $("input#profile-state").val(this.profiledata.States.state);
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
            var arparts = JSON.stringify(this.profiledata.GeographicAreasofInterest.Regions).split(',');
            this.prelength[3] = arparts.length-1;
            this.postlen[3] = arparts.length;
            for (var each in this.profiledata.GeographicAreasofInterest.Regions) {
              $('select#profile-region option[value="' + this.profiledata.GeographicAreasofInterest.Regionsid[each] + '"]').attr('selected', 'selected');
              this.currentOptions[3][each] = $('select#profile-region option[value="' + this.profiledata.GeographicAreasofInterest.Regionsid[each] + '"]').attr('id');
            }
          }
          else {
            this.prelength[3] = 0;
            this.postlen[3] = 0;
          }
        }
      }
      if (this.profiledata.GeographicAreasofInterest !== undefined) {
        if (this.profiledata.GeographicAreasofInterest.Countries !== undefined) {
          if (this.profiledata.GeographicAreasofInterest.Countries.length > 0) {
            var arparts = JSON.stringify(this.profiledata.GeographicAreasofInterest.Countries).split(',');
            this.prelength[4] = arparts.length-1;
            this.postlen[4] = arparts.length;
            for (var each in this.profiledata.GeographicAreasofInterest.Countries) {
              $('select#profile-countryserved option[value="' + this.profiledata.GeographicAreasofInterest.Countriesid[each] + '"]').attr('selected', 'selected');
              this.currentOptions[4][each] = $('select#profile-countryserved option[value="' + this.profiledata.GeographicAreasofInterest.Countriesid[each] + '"]').attr('id');
            }
          }
          else {
            this.prelength[4] = 0;
            this.postlen[4] = 0;
          }
        }
      }
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
          var arparts = JSON.stringify(this.profiledata.FacilityNon_MedicalOfferings).split(',');
          this.prelength[0] = arparts.length-1;
          this.postlen[0] = arparts.length;
          for (var each in this.profiledata.FacilityMedicalOfferings) {
            $('select#profile-org-offer option[value="' + this.profiledata.FacilityMedicalOfferingsid[each] + '"]').attr('selected', 'selected');
            this.currentOptions[0][each] = $('select#profile-org-offer option[value="' + this.profiledata.FacilityMedicalOfferingsid[each] + '"]').attr('id');
          }
        }
        else {
          this.prelength[0] = 0;
          this.postlen[0] = 0;
        }
      }
      if (this.profiledata.FacilityNon_MedicalOfferings !== undefined) {
        if (this.profiledata.FacilityNon_MedicalOfferings.length > 0) {
          var arparts = JSON.stringify(this.profiledata.FacilityNon_MedicalOfferings).split(',');
          this.prelength[1] = arparts.length-1;
          this.postlen[1] = arparts.length;
          for (var each in this.profiledata.FacilityNon_MedicalOfferings) {
            $('select#profile-org-offern option[value="' + this.profiledata.FacilityNon_MedicalOfferingsid[each] + '"]').attr('selected', 'selected');
            this.currentOptions[1][each] = $('select#profile-org-offern option[value="' + this.profiledata.FacilityNon_MedicalOfferingsid[each] + '"]').attr('id');
          }
        }
        else {
          this.prelength[1] = 0;
          this.postlen[1] = 0;
        }
      }
      if (this.profiledata.MedicalSkills !== undefined) {
        if (this.profiledata.MedicalSkills.length > 0) {
          var arparts = JSON.stringify(this.profiledata.MedicalSkills).split(',');
          this.prelength[2] = arparts.length-1;
          this.postlen[2] = arparts.length;
          for (var each in this.profiledata.MedicalSkills) {
            $('select#profile-org-medical option[value="' + this.profiledata.MedicalSkillsid[each] + '"]').attr('selected', 'selected');
            this.currentOptions[2][each] = $('select#profile-org-medical option[value="' + this.profiledata.MedicalSkillsid[each] + '"]').attr('id');
          }
        }
        else {
          this.prelength[2] = 0;
          this.postlen[2] = 0;
        }
      }
      if (this.profiledata.Non_MedicalSkills !== undefined) {
        if (this.profiledata.Non_MedicalSkills.length > 0) {
          var arparts = JSON.stringify(this.profiledata.Non_MedicalSkills).split(',');
          this.prelength[3] = arparts.length-1;
          this.postlen[3] = arparts.length;
          for (var each in this.profiledata.Non_MedicalSkills) {
            $('select#profile-org-nonmedical option[value="' + this.profiledata.Non_MedicalSkillsid[each] + '"]').attr('selected', 'selected');
            this.currentOptions[3][each] = $('select#profile-org-nonmedical option[value="' + this.profiledata.Non_MedicalSkillsid[each] + '"]').attr('id');
          }
        }
        else {
          this.prelength[3] = 0;
          this.postlen[3] = 0;
        }
      }
      if (this.profiledata.SpiritualSkills !== undefined) {
        if (this.profiledata.SpiritualSkills.length > 0) {
          var arparts = JSON.stringify(this.profiledata.SpiritualSkills).split(',');
          this.prelength[4] = arparts.length-1;
          this.postlen[4] = arparts.length;
          for (var each in this.profiledata.SpiritualSkills) {
            $('select#profile-org-spiritual option[value="' + this.profiledata.SpiritualSkillsid[each] + '"]').attr('selected', 'selected');
            this.currentOptions[4][each] = $('select#profile-org-spiritual option[value="' + this.profiledata.SpiritualSkillsid[each] + '"]').attr('id');
          }
        }
        else {
          this.prelength[4] = 0;
          this.postlen[4] = 0;
        }
      }
      if (this.profiledata.relg !== undefined) {
        $("input#profile-org-religion").val(this.profiledata.relg);
      }

      if (this.profiledata.Durations !== undefined) {
        if (this.profiledata.Durations.PreferredDurationofMissionTrips !== undefined) {
          $("input#profile-org-duration").val(this.profiledata.Durations.PreferredDurationofMissionTrips);
        }
      }

      if (this.profiledata.States !== undefined) {
        if (this.profiledata.States.state !== undefined) {
          $("input#profile-org-state").val(this.profiledata.States.state);
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
            var arparts = JSON.stringify(this.profiledata.GeographicAreasofInterest.Regions).split(',');
            this.prelength[5] = arparts.length-1;
            this.postlen[5] = arparts.length;
            for (var each in this.profiledata.GeographicAreasofInterest.Regions) {
              $('select#profile-org-region option[value="' + this.profiledata.GeographicAreasofInterest.Regionsid[each] + '"]').attr('selected', 'selected');
              this.currentOptions[5][each] = $('select#profile-org-region option[value="' + this.profiledata.GeographicAreasofInterest.Regionsid[each] + '"]').attr('id');
            }
          }
          else {
            this.prelength[5] = 0;
            this.postlen[5] = 0;
          }
        }
      }
      if (this.profiledata.GeographicAreasofInterest !== undefined) {
        if (this.profiledata.GeographicAreasofInterest.Countries !== undefined) {
          if (this.profiledata.GeographicAreasofInterest.Countries.length > 0) {
            var arparts = JSON.stringify(this.profiledata.GeographicAreasofInterest.Countries).split(',');
            this.prelength[6] = arparts.length-1;
            this.postlen[6] = arparts.length;
            for (var each in this.profiledata.GeographicAreasofInterest.Countries) {
              $('select#profile-org-countryserved option[value="' + this.profiledata.GeographicAreasofInterest.Countriesid[each] + '"]').attr('selected', 'selected');
              this.currentOptions[6][each] = $('select#profile-org-countryserved option[value="' + this.profiledata.GeographicAreasofInterest.Countriesid[each] + '"]').attr('id');
            }
          }
          else {
            this.prelength[6] = 0;
            this.postlen[6] = 0;
          }
        }
      }
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
      $("#profile-organizer-dialog").dialog('open');
    }
    this.endFunction();
  },

  createTrip : function () {
    $("#profile-trip-dialog").dialog('open');
  },

  cacheFacebookResponseProperties : function (response) {
    this.beginFunction();
    if (response.hasOwnProperty("authResponse")) {
      if (!response.authResponse.hasOwnProperty("accessToken")) {
        this.assert("Facebook authResponse did not contain an access token");
        this.accessToken = null;
        // this is about the time that we should surface an error to the user, no? --zack
      } else {
        this.accessToken = response.authResponse.accessToken;
      }
    } else {
      this.assert("Facebook response didn't contain an authResponse to cache");
      // this is also probably a nice time to surface an error, although this
      // shouldn't happen ever --zack
    }
    this.endFunction();
  },

  cacheFacebookData : function () {
    this.beginFunction();
    CMC.ajaxNotifyStart();
    FB.api('/me', function (response) {
      CMC.log("got user data from Facebook");
      CMC.ajaxNotifyComplete();
      CMC.me = response;
      // now check whether profile is volunteer or mission organizer	  
      CMC.profileshowflag=1;
      // This is the default profile display - showing the logged in user's profile
      CMC.getProfile(CMC.me.id);
      CMC.log(CMC.me.name + " (" + CMC.me.id + ") logged in to the app");
      // Get upcoming trips information
      CMC.getFutureTrips();
    });
    CMC.ajaxNotifyStart();
    FB.api('/me/friends', function (friends) {
      CMC.log("got friend data from Facebook");
      CMC.ajaxNotifyComplete();
      CMC.friends = friends.data;
    });
    this.endFunction();
  },

  checkFacebookLoginStatus : function (callback) {
    this.beginFunction();
    //@/BEGIN/DEBUGONLYSECTION
    $("#logged-in-user-value").html("(synchronizing)");
    //@/END/DEBUGONLYSECTION
    CMC.ajaxNotifyStart();
    FB.getLoginStatus(function(response) {
      CMC.ajaxNotifyComplete();
      CMC.log("got the response for FB.getLoginStatus()");
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
      
    // FIXME: This stuff should never be in the debug click event logger! --zack
      if (id.indexOf("trip-desc-submit") >=0) {
        var descparts = id.split('-');
        var index = parseInt(descparts[descparts.length-1],10);
	      CMC.GetTripProfile(index);
      }

      if (id.indexOf("trips-desc-submit") >=0) {
        var descparts = id.split('-');
        var index = parseInt(descparts[descparts.length-1],10);
	      CMC.GetTripProfile(index);
      }

      if (id.indexOf("join-trips-submit") >=0) {
        var descparts = id.split('-');
        var index = parseInt(descparts[descparts.length-1],10);
	      CMC.JoinTrip(index);
      }

      if (id.indexOf("invite-trip-submit") >=0) {
      var tripparts = this.id.split('-');
      var index = parseInt(tripparts[tripparts.length-1],10);;
	      //invitetotrip(index);
      }

      if (id.indexOf("tripprofileimage") >= 0) {
        // change to the Profile Tab
        $("#tabs").tabs('select', 1);
        // Show the Trip owner's profile
        CMC.getProfile(CMC.tripuserid);
      }

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
        CMC.getProfile(CMC.me.id);
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
    .click(function() {
      CMC.recalculateProblemMessageLimit();
    })
    .focus(function() {
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
    } else if (country != "United States") {
      if (!regex.test(fld)) {
        error = "The phone number is not a valid International Number.\n";
      }
    } else if (!(stripped.length == 10)) {
      error = "The phone number is the wrong length. Make sure you included an area code.\n";
    }
    return error;
  }

  $("#profile-submit").click(function() {
    var mtype = $("#profile-volunteer-form").find('.profile-ddl-type-medical');
    var nmtype = $("#profile-volunteer-form").find('.profile-ddl-type-nonmedical');
    var sptype = $("#profile-volunteer-form").find('.profile-ddl-type-spiritual');
    var reltype = $("#profile-volunteer-form").find('.profile-ddl-type-religious');
    var durtype = $("#profile-volunteer-form").find('.profile-ddl-type-duration');
    var state = $("#profile-volunteer-form").find('.profile-ddl-type-state');
    var city = $("#profile-volunteer-form").find('.profile-input-city');
    var zipcode = $("#profile-volunteer-form").find('.profile-input-zipcode');

    var country = $("#profile-volunteer-form").find('.profile-ddl-type-country');
    var region = $("#profile-volunteer-form").find('.profile-ddl-type-region');
    var countriesserved = $("#profile-volunteer-form").find('.profile-ddl-type-countriesserved');
    var phone = $("#profile-volunteer-form").find('.profile-input-phone');
    var email = $("#profile-volunteer-form").find('.profile-input-email');
    var misexp = $("#profile-volunteer-form").find('.profile-input-experience');           

    var zipisvalid = false;
    var emailisvalid = false;
    var reason="";
    var errornum=1;

    if (zipcode !== undefined) {
    if (zipcode.val() != "") {
      zipisvalid = validateZipCode(zipcode.val());
      if (!zipisvalid) {
        reason += errornum+'. Incorrect Zipcode format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
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

    if (reason != "") {
      alert('Some input fields need correction:\n'+ reason);
      return false;
    } else {
      var profileformdata = {};
      
      profileformdata.profiletype=1;
      if (CMC.profileedit == 1)
         profileformdata.update = 1;

      if (mtype.val() != "")
        profileformdata.medskills= mtype.val();
      if (nmtype.val() != "")
        profileformdata.otherskills=nmtype.val();         
      if (sptype.val() != "")
        profileformdata.spiritserv=sptype.val();        
      if (region.val() != "")
        profileformdata.region=region.val();  
      if (country.val() != "")
        profileformdata.country=country.val();  
      if (state.val() != "Select your State")
        profileformdata.state=state.val();  
      if (durtype.val() != "")
        profileformdata.dur=durtype.val();
      if (reltype.val() != "")
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
      
      $.ajax({
        type: "POST",
        url: "api/profilein.php",
        data: {
          fbid: CMC.me.id ? CMC.me.id : "",
        profileinfo: JSON.stringify(profileformdata)
        },
        dataType: "json",
        context: this,
        success: function(data) {
          if (!data.has_error) {
            $("#profile-volunteer-dialog").dialog('close');
            alert('Thank you - your submission has been successfully entered into our database');
          }
          else {
           alert('We are sorry - there was an error: ' + data.err_msg);
          }
        },
        error: function(data) {
           alert('We are sorry - there was an error: ' + data.err_msg);
        }
      });
      return true;
    }
  });

    $("#profile-org-submit").click(function() {

    var aname = $("#profile-organizer-form").find('.profile-org-name');
    var aurl = $("#profile-organizer-form").find('.profile-org-website');
    var aabout = $("#profile-organizer-form").find('.profile-org-about');
    var medfacil = $("#profile-organizer-form").find('.profile-org-offer');
    var nonmedfacil = $("#profile-organizer-form").find('.profile-org-offern');
	
    var mtype = $("#profile-organizer-form").find('.profile-org-medical');
    var nmtype = $("#profile-organizer-form").find('.profile-org-nonmedical');
    var sptype = $("#profile-organizer-form").find('.profile-org-spiritual');
    var reltype = $("#profile-organizer-form").find('.profile-org-religion');
    var durtype = $("#profile-organizer-form").find('.profile-org-duration');
    var state = $("#profile-organizer-form").find('.profile-org-state');
    var city = $("#profile-organizer-form").find('.profile-org-city');
    var zipcode = $("#profile-organizer-form").find('.profile-org-zipcode');

    var country = $("#profile-organizer-form").find('.profile-org-country');
    var region = $("#profile-organizer-form").find('.profile-org-region');
    var countriesserved = $("#profile-organizer-form").find('.profile-org-countryserved');
    var phone = $("#profile-organizer-form").find('.profile-org-phone');
    var email = $("#profile-organizer-form").find('.profile-org-email');
    var misexp = $("#profile-organizer-form").find('.profile-org-experience');           

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
	
    if (zipcode !== undefined) {
    if (zipcode.val() != "") {
      zipisvalid = validateZipCode(zipcode.val());
      if (!zipisvalid) {
        reason += errornum+'. Incorrect Zipcode format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
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


    if (reason != "") {
      alert('Some input fields need correction:\n'+ reason);
      return false;
    } else {
      var profileformdata = {};
      profileformdata.profiletype=3;

      if (CMC.profileedit == 1) {
         profileformdata.update = 1;
      }

      if (aname.val() != "")
        profileformdata.name= aname.val();	  
      if (aabout.val() != "")
        profileformdata.about= aabout.val();
      if (aurl.val() != "")
        profileformdata.url= aurl.val();
      if (medfacil.val() != "")
        profileformdata.medfacil= medfacil.val();
      if (nonmedfacil.val() != "")
        profileformdata.nonmedfacil= nonmedfacil.val();		
      if (mtype.val() != "")
        profileformdata.medskills= mtype.val();
      if (nmtype.val() != "")
        profileformdata.otherskills=nmtype.val();         
      if (sptype.val() != "")
        profileformdata.spiritserv=sptype.val();        
      if (region.val() != "")
        profileformdata.region=region.val();  
      if (country.val() != "")
        profileformdata.country=country.val();  
      if (state.val() != "Select your State")
        profileformdata.state=state.val();  
      if (durtype.val() != "")
        profileformdata.dur=durtype.val();
      if (reltype.val() != "")
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
     
     // alert("AJAX Submit: " + JSON.stringify(profileformdata));
      $.ajax({
        type: "POST",
        url: "api/profilein.php",
        data: {
           fbid: CMC.me.id ? CMC.me.id : "",
           profileinfo: JSON.stringify(profileformdata)
        },
        dataType: "json",
        context: this,
        success: function(data) {
          if (!data.has_error) {
          // now close the profile submission window
          $("#profile-organizer-dialog").dialog('close');
          alert('Thank you - your submission has been successfully entered into our database ');
          }
          else {
            alert("We are sorry, there was an error :  " + data.err_msg);
          }
        },
        error: function(data, textStatus, errorThrown) {
                alert("We are sorry, the profile was not submitted with the following error: " + data.err_msg);
        }
      });
   
      // Now show the updated profile
      CMC.profileshowflag=1;
      CMC.getProfile(CMC.me.id);

      return true;
    }

    
  });
  
  $("#profile-trip-submit").click(function() {
  
    var aname = $("#profile-trip-form").find('.profile-trip-name');
	var aurl = $("#profile-trip-form").find('.profile-trip-website');
	var aabout = $("#profile-trip-form").find('.profile-trip-about');
	
    var reltype = $("#profile-trip-form").find('.profile-trip-religion');
    var durtype = $("#profile-trip-form").find('.profile-trip-duration');
    var city = $("#profile-trip-form").find('.profile-trip-city');
    var zipcode = $("#profile-trip-form").find('.profile-trip-zipcode');

    var country = $("#profile-trip-form").find('.profile-trip-country');
    var languages = $("#profile-trip-form").find('.profile-trip-languages');
    var phone = $("#profile-trip-form").find('.profile-trip-phone');
    var email = $("#profile-trip-form").find('.profile-trip-email');
    var stage = $("#profile-trip-form").find('.profile-trip-stage');
    var tripdepart = $("#profile-trip-form").find('.profile-trip-depart');
    var tripreturn = $("#profile-trip-form").find('.profile-trip-return');
    var numberofmembers = $("#profile-trip-form").find('.profile-trip-number');

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
	
    if (zipcode !== undefined) {
    if (zipcode.val() != "") {
      zipisvalid = validateZipCode(zipcode.val());
      if (!zipisvalid) {
        reason += errornum+'. Incorrect Zipcode format entered\n';
        errornum = errornum + 1;
        isValid = false;
      }
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
	
    // Logic to determine that the trip begin date is before the trip end date
      if (tripdepart.val() != "") {
          var departdate = tripdepart.val().split(".");
          var DepartMonth=parseInt(departdate[0],10);	  
          var DepartDay=parseInt(departdate[1],10);	  
          var DepartYear=parseInt(departdate[2],10);
		  var TDeparture = new Date();
		  TDeparture.setFullYear(DepartYear,DepartMonth,DepartDay);
      }
      if (tripreturn.val() != "") {
          var returndate = tripreturn.val().split(".");
          var ReturnMonth=parseInt(returndate[0],10);	  
          var ReturnDay=parseInt(returndate[1],10);	  
          var ReturnYear=parseInt(returndate[2],10);	
		  var TReturn = new Date();
		  TReturn.setFullYear(ReturnYear,ReturnMonth,ReturnDay);		  
      }
	  
	  if (TDeparture > TReturn) {
	        reason += errornum + ' ' + 'Trip departure date should be before the return date' + '\n';
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
	    if (aabout.val() != "")
        profiletripformdata.about= aabout.val();
	    if (aurl.val() != "")
        profiletripformdata.url = aurl.val();
	    if (stage.val() != "")
        profiletripformdata.stage= stage.val();
      if ((tripdepart !== undefined) && (tripdepart.val() !==  null)) {
          var departdate = tripdepart.val().split(".");
          profiletripformdata.DepartMonth=parseInt(departdate[0],10);	  
          profiletripformdata.DepartDay=parseInt(departdate[1],10);	  
          profiletripformdata.DepartYear=parseInt(departdate[2],10);	  
      }
      if ((tripreturn !== undefined) && (tripreturn.val() !==  null)) {
          var returndate = tripreturn.val().split(".");
          profiletripformdata.ReturnMonth=parseInt(returndate[0],10);	  
          profiletripformdata.ReturnDay=parseInt(returndate[1],10);	  
          profiletripformdata.ReturnYear=parseInt(returndate[2],10);	  
      }

	    if (numberofmembers.val() != "")
        profiletripformdata.numpeople= numberofmembers.val();
      
	    if (country.val() != "")
        profiletripformdata.country=country.val();  
	    if (languages.val() != "")
        profiletripformdata.languages=languages.val(); 
	    if (durtype.val() != "")
        profiletripformdata.dur=durtype.val();
	    if (reltype.val() != "")
        profiletripformdata.relg=reltype.val();           
	    if (zipcode.val() != "")
        profiletripformdata.zip=zipcode.val();
	    if (email.val() != "")
        profiletripformdata.email=email.val();
	    if (city.val() != "")
        profiletripformdata.city=city.val();
	    if (phone.val() != "")
        profiletripformdata.phone=phone.val();
     
      $.ajax({
        type: "POST",
        url: "api/profilein.php",
        data: {
           fbid: CMC.me.id ? CMC.me.id : "",
		      profileinfo: JSON.stringify(profiletripformdata)
        },
        dataType: "json",
        context: this,
        success: function(data) {
          if (!data.has_error) {
	        $("#profile-trip-dialog").dialog('close');
          // refresh the profile page
          $("#tabs").tabs('load', 1);
          alert('Thank you - your submission has been successfully entered into our database');
          }
          else {
            alert("We are sorry, the trip was not created due to: " + data.err_msg);
          }
        },
        error: function(data) {
                 alert('We are sorry, the trip was not created due to: ' + data.err_msg);
        }
      });
      
      return true;
    }
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


    /*
  $.each(CMC.tripsjoinbtns, function() {
    $("#" +this).click(function() {
      var tripparts = this.id.split('-');
      var index = parseInt(tripparts[tripparts.length-1],10);;
      alert("Trip Index = " + index + " " + CMC.profiledata.tripid[index]);
	  jointrip(index);
    });
  });

  $.each(CMC.tripsdescbtns, function() {
    $("#" +this).click(function() {
      alert("Came into Trips description click");
      var tripparts = this.id.split('-');
      var index = parseInt(tripparts[tripparts.length-1],10);;
      alert("Trips Index = " + index + " " + CMC.profiledata.tripid[index]);
	  CMC.GetTripProfile(index);
    });
  });

  $.each(CMC.tripinvitebtns, function() {
    $("#" +this).click(function() {
      alert("Came into Trip Invite click");
      var tripparts = this.id.split('-');
      var index = parseInt(tripparts[tripparts.length-1],10);;
      alert("Trip Index = " + index + " " + CMC.profiledata.tripid[index]);
	  //invitetotrip(index);
    });
  });

  $.each(CMC.tripdescbtns, function() {
    $("#" +this).click(function() {
      alert("Came into trip desctiption");
      var descparts = this.id.split('-');
      var index = parseInt(descparts[descparts.length-1],10);
      alert("TripDesc Index = " + index + " " + CMC.profiledata.tripid[index]);
	  CMC.GetTripProfile(index);
    });
  });
  */

  // this should be the last thing that happens
  CMC.log("load callback complete, fading in canvas");
  $("#loading").fadeOut(function() {
    $("#tabs").hide().fadeIn(function() {
      $("#cmc-footer").hide().delay(150).fadeIn();
    });
  });

});
// vim: ai:et:ts=2:sw=2
