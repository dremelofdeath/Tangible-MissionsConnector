<?php
// Application: Christian Missions Connector
// File: 'searchresults.php' 
//  search results retrieved and displayed (sort by distance**)
// 
//require_once 'facebook.php';

include_once 'common.php';

header('Content-type: application/json');

$con = arena_connect();

$saferequest = cmc_safe_request_strip($con);
$has_error = FALSE;
$err_msg = '';

if (array_key_exists('fbid', $saferequest)) {
  $fbid = $saferequest['fbid'];
	if (array_key_exists('searchkeys',$saferequest)) {
		
  // The search fields are assumed to be sent from front-end to back-end in a json-encoded + base64_encoded object
  // It is first base64_decoded, then json_decoded here, and then used by the code

    $searchkeys = base64_decode($saferequest['searchkeys']);
	$searchkeys = json_decode($searchkeys);

    switch(json_last_error())
    {
      case JSON_ERROR_DEPTH:
         $has_error = TRUE;
         $err_msg = "Maximum stack depth exceeded";
         break;
      case JSON_ERROR_CTRL_CHAR:
         $has_error = TRUE;
         $err_msg = "Unexpected control character found";
          break;
      case JSON_ERROR_SYNTAX:
         $has_error = TRUE;
         $err_msg = "Syntax error, malformed JSON";
          break;
      case JSON_ERROR_NONE:
          break;
      }

    $searchkeys = cmc_safe_object_strip($con, $searchkeys);
	  
	  // If page and perpage are given in the arguments, then get that information
	  if (array_key_exists('page',$saferequest) && (array_key_exists('perpage',$saferequest))) {
			$page = $saferequest['page'];
			$perpage = $saferequest['perpage'];

      if (($page<1) || ($perpage<1)) {
        $has_error = TRUE;
        $err_msg = "Page number should be greater than 0 and results per page should be greater than 0";
      }
	  }
	  
	}
	else {
  		$has_error = TRUE;
  		$err_msg = "Search type and/or search fields not defined for advanced search";
	}
} else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

$json = array();

/**
* Formats portion of the WHERE clause for a SQL statement.
* SELECTs points within the $distance radius
*
* Retrieved from: http://www.davidus.sk/web/main/index/article_id/8
* --zack
*
* @param float $lat Decimal latitude
* @param float $lon Decimal longitude
* @param float $distance Distance in kilometers
* @return string
*/
function mysqlHaversine($lat = 0, $lon = 0) {
  return ('
    (6372.797 * (2 *
    ATAN2(
      SQRT(
        SIN(('.($lat*1).' * (PI()/180)-latitude*(PI()/180))/2) *
        SIN(('.($lat*1).' * (PI()/180)-latitude*(PI()/180))/2) +
        COS(latitude* (PI()/180)) *
        COS('.($lat*1).' * (PI()/180)) *
        SIN(('.($lon*1).' * (PI()/180)-longitude*(PI()/180))/2) *
        SIN(('.($lon*1).' * (PI()/180)-longitude*(PI()/180))/2)
      ),
      SQRT(1-(
        SIN(('.($lat*1).' * (PI()/180)-latitude*(PI()/180))/2) *
        SIN(('.($lat*1).' * (PI()/180)-latitude*(PI()/180))/2) +
        COS(latitude* (PI()/180)) *
        COS('.($lat*1).' * (PI()/180)) *
        SIN(('.($lon*1).' * (PI()/180)-longitude*(PI()/180))/2) *
        SIN(('.($lon*1).' * (PI()/180)-longitude*(PI()/180))/2)
      ))
    )
  ))');
}

// function to get zip info
function getZipInfo($zip,&$has_error,&$err_msg,$con) {
  $sql  = "SELECT * FROM zipcodes WHERE zipcode='" . $zip . "'";
  $query = $con->query($sql);
  if (!$query) {
    setjsonmysqlerror($has_error,$err_msg,$sql,$con);
    return FALSE;
  }
  else {
    if($query->num_rows < 1)
      return FALSE;

    $zipInfo = $query->fetch_object();
    return $zipInfo;
  }
} //end getZipInfo

function getZipsWithin($zip, $miles, &$has_error, &$err_msg, $con) {
  if(($zipInfo = getZipInfo($zip,$has_error,$err_msg,$con)) === FALSE)
    return FALSE;

  $sql = "SELECT zipcode, "
    .mysqlHaversine($zipInfo->latitude, $zipInfo->longitude)." AS distance"
    ." FROM zipcodes"
    ." HAVING distance <= ".$miles
    ." ORDER BY distance;";

  $query = $con->query($sql);

  if (!$query) {
    setjsonmysqlerror($has_error,$err_msg,$sql,$con);
    return FALSE;
  }

  $retval = array();

  while($row = $query->fetch_row()) {
    $retval[] = $row[0];
  }

  return $retval;
}

function get_rest_of_string(&$sql3,&$sql1,&$sql2,$val,$searchkeys) {

  $sql2 = '';
  $sql1 = ' ';
  $sql3='';
  $joins = '';
  $usersinc=0;
  $firstone = 0;

  /*
  For name or general keyword, only the user names are searched with a "%like% statement in the mysql query.
  For other search items, exact criteria (for example religion id etc. are used)
   */

  if (isset($searchkeys->{'name'})) {
    if ($val ==1) {
      $usersinc = 1;
      $sql1 = ',users';
      $sql3 = $sql3.' and users.name like "%'.$searchkeys->{'name'}.'%"';
    } else {
      $pieces = explode(" ", $searchkeys->{'name'});
      $sql3 = $sql3.' users.name like "%';
      for ($i=0;$i<count($pieces);$i++) {
        if ($i==(count($pieces)-1)) {
          $sql3 = $sql3.$pieces[$i].'%"';
        } else {
          $sql3 = $sql3.$pieces[$i].'%';
        }
      }
      $firstone = 1;
    }
  }
  if (isset($searchkeys->{'relg'})) {
    if (strcmp($searchkeys->{'relg'},"Any")) {
      if ($val ==1) {
        $usersinc = 1;
        $sql1 = ',users';
        $sql3 = $sql3.' and users.religion="'.$searchkeys->{'relg'}.'"';
      } else {
        if ($firstone == 1) {
          $sql3 = $sql3.' and users.religion="'.$searchkeys->{'relg'}.'"';
        } else {
          $sql3 = $sql3.' users.religion="'.$searchkeys->{'relg'}.'"';
          $firstone = 1;
        }
      }
    }
  }
  if (isset($searchkeys->{'skills'})) {
    if ($searchkeys->{'skills'} != 0) {
      $skillsArray = $searchkeys->{'skills'};
      if (!is_array($searchkeys->{'skills'})) {
        $skillsArray = array(0 => $searchkeys->{'skills'});
      }
      $i = 0;
      foreach ($skillsArray as $value) {
        $joins .= "\nINNER JOIN skillsselected AS ss" . ++$i . " ON users.userid=ss" . $i . ".userid AND ss" . $i . ".id=\"" . $value . "\"";
      }
    }
  }
  if (isset($searchkeys->{'countries'})) {
    if ($searchkeys->{'countries'} != 0) {
      $countriesArray = $searchkeys->{'countries'};
      if (!is_array($searchkeys->{'countries'})) {
        $countriesArray = array(0 => $searchkeys->{'countries'});
      }
      $i = 0;
      foreach ($countriesArray as $value) {
        $joins .= "\nINNER JOIN countriesselected AS cs" . ++$i . " ON users.userid=cs" . $i . ".userid AND cs" . $i . ".id=\"" . $value . "\"";
      }
    }
  }
  if (isset($searchkeys->{'region'})) {
    if ($searchkeys->{'region'} != 0) {

      if ($firstone==1) {
        $sql3 = $sql3.' and regions.id="'.$searchkeys->{'region'}.'"';
      } else {
        $sql3 = $sql3.' regions.id="'.$searchkeys->{'region'}.'"';
        $firstone = 1;
      }

      if ($val==1) {
        if ($usersinc == 0) {
          $sql1 = $sql1.',users,regions,regionsselected';
          $usersinc = 1;
        }
      } else {
        $sql1 = $sql1.',regions,regionsselected';
      }

      $sql2 = $sql2.' and regions.id=regionsselected.id and users.userid=regionsselected.userid';
    }
  }
  if (isset($searchkeys->{'dur'})) {
    if ($searchkeys->{'dur'} != 0) {

      if ($firstone==1) {
        $sql3 = $sql3.' and durations.id="'.$searchkeys->{'dur'}.'"';
      } else {
        $sql3 = $sql3.' durations.id="'.$searchkeys->{'dur'}.'"';
        $firstone = 1;
      }

      if ($val==1) {
        if ($usersinc == 0) {
          $sql1 = $sql1.',users,durations,durationsselected';
          $usersinc = 1;
        }
      } else {
        $sql1 = $sql1.',durations,durationsselected';
      }

      $sql2 = $sql2.' and durations.id=durationsselected.id and users.userid=durationsselected.userid';
    }
  }
  $sql1 .= $joins;
}

function getzipsearchstring($result,$con,&$has_error,&$err_msg,&$sqlstr,&$sqlstr2,$sql3) {
  $j=0;
  if (empty($sql3)) {
    $sqlstr = ' users.zipcode in (';
  } else {
    $sqlstr = ' and users.zipcode in (';
  }

  $sqlstr2 = ' order by field(users.zipcode, ';

  for ($i=0;$i<count($result);$i++) {
    if ($i==(count($result)-1)) {
      $sqlstr = $sqlstr.$result[$i].')';
      $sqlstr2 = $sqlstr2.$result[$i].')';
    } else {
      $sqlstr = $sqlstr.$result[$i].', ';
      $sqlstr2 = $sqlstr2.$result[$i].', ';
    }
  }
}

function update_searchtables($fbid,$keywords,$con,&$has_error,&$err_msg) {
  $sql = 'insert into searches (userid) VALUES ("'.$fbid.'")';
  $result = $con->query($sql);
  if (!$result) {
    setjsonmysqlerror($has_error,$err_msg,$sql,$con);
  } else {
    $sql2 = 'select max(searchid) as searchid from searches where userid="'.$fbid.'"';
    $result2 = $con->query($sql2);
    if (!$result2) {
      setjsonmysqlerror($has_error,$err_msg,$sql2);
    } else {
      while ($row = $result2->fetch_array()) {
        $searchid = $row['searchid']+0;  
        break; // what the heck? --zack
      }

      // Now insert into searchterms table
      $sql2 = "insert into searchterms (searchid,searchquery) VALUES ('".$searchid."','".$keywords."')";
      $result2 = $con->query($sql2);
      if (!$result2) {
        setjsonmysqlerror($has_error,$err_msg,$sql2);
      }
    }
  }
}

if (!$has_error) {
  $profileid = $fbid;

  // This means that the user specified a zipcode constraint in the search keys
  if (isset($searchkeys->{'z'})) {
    $zipdata = $searchkeys->{'z'};
    if (count($zipdata)!=2) {
      $has_error = TRUE;
      $err_msg = "zipcode data should have zipcode and search-radius";
    } else {
      // Zip code entered by user
      $myzipcode = $zipdata[0];
      // search radius entered by user
      $searchradius = $zipdata[1];
      if ($searchradius > 500) {
        $has_error = TRUE;
        $err_msg = "Search radius too big, reduce to below 500 miles";
      }

    }
  } else {
    // get the zipcode of the current user if zipcode and search radius are not included in the search string
    $sql = 'select zipcode from users where userid="'.$fbid.'"';
    $result = $con->query($sql);
    if (!$result) {
      setjsonmysqlerror($has_error,$err_msg,$sql,$con);
    } else {
      if ($result->num_rows != 0) {
        $row = $result->fetch_array();
        $myzipcode = $row['zipcode'];
      }
    }
  }

  // This is main algorithm for generating search results
  if (!$has_error) {

    // if searchradius is not defined, simply use users zipcode to sort the results
    $friends=array();
    $sql='select users.userid,users.zipcode from users';

    get_rest_of_string($sql3,$sql1,$sql2,0,$searchkeys);  

  /*
  if (!isset($searchradius)) {
  // if search radius is not set, this means general search without the specification of zipcode
  // In this case, assign a huge number so that all relevant zip codes are included
  $searchradius = 1000;
  }
   */

    $sql4 = $sql5 = ''; // warning fix after isset($searchradius) below
    if (isset($searchradius)) {
      $result = getZipsWithin($myzipcode,$searchradius,$has_error,$err_msg,$con);
      if (!$result) {
        $has_error = TRUE;
        $err_msg = "Entered zipcode is not valid."
          . ($err_msg && $err_msg != "" ? " (Internal error: ".$err_msg.")" : "");
        // this should suppress an annoying warning from PHP that I don't want 
        // to bother fixing right now --zack
      }
      else {
        // this call gets additional filter strings for the mysql query
        getzipsearchstring($result,$con,$has_error,$err_msg,$sql4,$sql5,$sql3);
      }
    } else {
      // In this case no sorting is done, simply sends the relevant data to the front-end
      if (!empty($sql2) or !empty($sql3)) {
        $sql4 = ' where ';
      }
      $sql5 = '';
    }

    if (isset($searchradius)) 
      $sql = $sql.$sql1.' where '.$sql3.$sql2.$sql4.$sql5;
    else
      $sql = $sql.$sql1.$sql4.$sql5.$sql3.$sql2;

	if (isset($page) && isset($perpage)) {
	// counting the page offset
	$offset = ($page - 1) * $perpage;
	$sql = $sql." LIMIT ".$offset.",".$perpage;
	}

    $result = $con->query($sql);

    if($result) {
      if($result->num_rows==0){
        // Nothing to display or return, just stores the sql query in the database
      }
      else {
        $json['searchids'] = array();
        while($row = $result->fetch_array()) {
          $json['searchids'][] = $row['userid'];
        }
      }


      // store the mysql query information into searches tables
      if (!$has_error) {
        update_searchtables($fbid,$sql,$con,$has_error,$err_msg);
      }
    }
    else {
      setjsonmysqlerror($has_error,$err_msg,$sql,$con);
    }

  }

}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

