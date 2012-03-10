<?php
// Application: Christian Missions Connector
// File: 'profilein.php' 
//  add user profile to db
// 

include_once 'common.php';
header('Content-type: application/json');
$con = arena_connect();

$saferequest = cmc_safe_request_strip();
$has_error = FALSE;
$err_msg = '';

function stripslashes_deep($value)
{
  $value = is_array($value) ? array_map("stripslashes_deep", $value) : stripslashes($value);
  return $value;
}

$json = array();

// make sure all the required parameters are defined, else throw an error
// profiletype, fbid
if (array_key_exists('fbid', $saferequest) && array_key_exists('profileinfo',$saferequest)) {

  $fbid = $saferequest['fbid'];

  $myobj = json_decode(base64_decode($saferequest['profileinfo']));

  switch(json_last_error()) {
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

  $myobj = cmc_safe_object_strip($myobj);
}
else {
  // error case: all needed variables are not defined
  $has_error = TRUE;
  $err_msg = "Required parameters not defined.";
}

if (!$has_error) {

  // If profiletype == 1, then it is a volunteer profile, if profiletype==2, then it is a trip profile
  // For all other profiletypes, it is a mission profile

  if ($myobj->{'profiletype'}==1) {
    $isreceiver = 0;
    $is_trip = 0;
  }
  else if ($myobj->{'profiletype'} == 2) {
    $is_trip = 1;
    if (isset($myobj->{'membertype'})) {
      $membertype = $myobj->{'membertype'};
      if (($membertype < 1) || ($membertype > 3)) {
        $has_error = TRUE;
        $err_msg = "Member Type can be 1 or 2 or 3";
      }
    }
    else
      $membertype = 1;

  }
  else {
    $is_trip = 0;
    $isreceiver = 1;
  }

  
  if ($myobj->{'profiletype'} != 2) {

    // Zip code is a required field for volunteer or missions - return an error if the country is USA
    if (($isreceiver == 0) || ($isreceiver==1)) {
      if (isset($myobj->{'mycountry'})) {
        if (isset($myobj->{'zip'})) {
          // if we can, let's set the city and the state by looking it up 
          // instead of relying on the user to type it in --zack
          if (!isset($myobj->{'city'}) || !isset($myobj->{'state'})) {
            $sql = 'SELECT zipcodes.city, zipcodes.state, usstates.id '.
                   'FROM zipcodes INNER JOIN usstates '.
                   'ON zipcodes.state=usstates.shortname '.
                   'WHERE zipcodes.zipcode='.$myobj->{'zip'};
            $result = mysql_query($sql, $con);
            if (!$result) {
              setjsonmysqlerror($has_error, $err_msg, $sql);
            } else {
              $numrows = mysql_num_rows($result);
              if ($numrows > 0) {
                $row = mysql_fetch_array($result);
                if (!isset($myobj->{'city'})) {
                  $myobj->{'city'} = $row['city'];
                }
                if (!isset($myobj->{'state'})) {
                  $myobj->{'state'} = $row['id'];
                }
              } else {
                $has_error = TRUE;
                $err_msg = "Couldn't find that zipcode";
              }
            }
          }
        } else if (!strcmp($myobj->{'mycountry'},"1")) {
          $has_error = TRUE;
          $err_msg = "If country is USA, zip code is a required field";
        }
      }
    }
  }

  if (!$has_error) {

    if (isset($myobj->{'update'})) {
      $update = $myobj->{'update'};
    }
    else {
      $update = 0;
    }


    if (!$is_trip) {

      if (!empty($myobj->{'toggle'})) {

        if ($myobj->{'toggle'} == 1) {
          $sql = 'select * from users where userid="'.$fbid.'"';
          $result = mysql_query($sql,$con);
          if (!$result) {
            setjsonmysqlerror($has_error,$err_msg,$sql);
          }
          else {
            $numrows = mysql_num_rows($result);
            if ($numrows > 0) {
              $row = mysql_fetch_array($result);
              $misreceiver = $row['isreceiver'];
              if ($misreceiver == 1) {
                $newrecr = 0;
                $sql2 = 'UPDATE users SET isreceiver="'.$newrecr.'" where userid="'.$fbid.'"';
                $result = mysql_query($sql2,$con);
                if (!$result) {
                  setjsonmysqlerror($has_error,$err_msg,$sql2);
                }
                else
                  $isreceiver = $newrecr;
              }
              else {
                $newrecr = 1;
                $sql2 = 'UPDATE users SET isreceiver="'.$newrecr.'" where userid="'.$fbid.'"';
                $result = mysql_query($sql2,$con);
                if (!$result) {
                  setjsonmysqlerror($has_error,$err_msg,$sql2);
                }
                else
                  $isreceiver = $newrecr;
              }

            }
          }
        }
      }

      // function to validate user input
      function validateString($num) {
        if (strlen($num==0)) 
          return -1;
        else {
          //if ((preg_match("<iframe",trim($num))) || (stristr($num,"iframe")))
          if (preg_match("#^[a-zA-Z0-9 ]+$#i",trim($num)))
            return 1;
          else
            return 0;
        }
      }

      if (!$has_error) {

        $sql = "SELECT userid FROM users WHERE userid='".$fbid."'";
        $result = mysql_query($sql,$con);

        if (!$result) {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
        else {

          $num_userids = mysql_num_rows($result);

          if($num_userids > 0){

            $sql = 'UPDATE users SET isreceiver="'.$isreceiver.'"';

            if (!empty($myobj->{'name'}))
              $sql = $sql.', organization="'.$myobj->{'name'}.'"';

            //$sql = $sql.', isreceiver="'.$isreceiver.'"';

            if ($update) {

              if (empty($myobj->{'zip'}))
                $sql = $sql.', zipcode=NULL';
              else
                $sql = $sql.', zipcode="'.$myobj->{'zip'}.'"';

              if (empty($myobj->{'phone'}))
                $sql = $sql.', phone=NULL';
              else
                $sql = $sql.', phone="'.$myobj->{'phone'}.'"';

              if (empty($myobj->{'email'}))
                $sql = $sql.', email=NULL';
              else
                $sql = $sql.', email="'.$myobj->{'email'}.'"';	

              if (empty($myobj->{'misexp'}))
                $sql = $sql.', missionsexperience=NULL';
              else
                $sql = $sql.', missionsexperience="'.$myobj->{'misexp'}.'"';

              if (empty($myobj->{'relg'}))
                $sql = $sql.', religion=NULL';
              else
                $sql = $sql.', religion="'.$myobj->{'relg'}.'"';

              if (empty($myobj->{'about'}))
                $sql = $sql.', aboutme=NULL';
              else
                $sql = $sql.', aboutme="'.$myobj->{'about'}.'"';

              if (empty($myobj->{'languages'}))
                $sql = $sql.', Languages=NULL';
              else
                $sql = $sql.', Languages="'.$myobj->{'languages'}.'"';

              if (empty($myobj->{'state'}))
                $sql = $sql.', state=NULL';
              else
                $sql = $sql.', state="'.$myobj->{'state'}.'"';


              if (isset($myobj->{'city'})) {
                $mycity = $myobj->{'city'};
                if (empty($mycity))
                  $sql = $sql.', city=NULL';
                else {
                  if (validateString($mycity)==0) {
                    $has_error = TRUE;
                    $err_msg = "You entered an invalid city string";
                  }
                  else {
                    $sql = $sql.', city="'.$myobj->{'city'}.'"';
                  }
                }
              }

              if (isset($myobj->{'mycountry'})) {
                $mycountry = $myobj->{'mycountry'};
                if (empty($mycountry))
                  $sql = $sql.', country=NULL';
                else {
                  if (validateString($mycountry)==0) {
                    $has_error = TRUE;
                    $err_msg = "You entered an invalid country string";
                  }
                  else {
                    $sql = $sql.', country="'.$myobj->{'mycountry'}.'"';
                  }
                }
              }
              if (empty($myobj->{'url'}))
                $sql = $sql.', website=NULL';
              else
                $sql = $sql.', website="'.$myobj->{'url'}.'"';

            }
            else {
              if (!empty($myobj->{'zip'}))
                $sql = $sql.', zipcode="'.$myobj->{'zip'}.'"';
              if (!empty($myobj->{'phone'}))
                $sql = $sql.', phone = "'.$myobj->{'phone'}.'"';
              if (!empty($myobj->{'email'}))
                $sql = $sql.', email = "'.$myobj->{'email'}.'"';
              if (!empty($myobj->{'misexp'}))
                $sql = $sql.', missionsexperience = "'.$myobj->{'misexp'}.'"';
              if (!empty($myobj->{'relg'}))
                $sql = $sql.', religion = "'.$myobj->{'relg'}.'"';
              if (!empty($myobj->{'about'}))
                $sql = $sql.', aboutme = "'.$myobj->{'about'}.'"';
              if (!empty($myobj->{'languages'}))
                $sql = $sql.', Languages = "'.$myobj->{'languages'}.'"';
              if (!empty($myobj->{'state'}))
                $sql = $sql.',state ="'.$myobj->{'state'}.'"';
              $mycity = $myobj->{'city'};

              if (!empty($mycity)) {
                if (validateString($mycity)==0) {
                  $has_error = TRUE;
                  $err_msg = "You entered an invalid city string";
                }
                else {
                  $sql = $sql.', city ="'.$myobj->{'city'}.'"';
                }
              }

              $mycountry = $myobj->{'mycountry'};

              if (!empty($mycountry)) {
                if (validateString($mycountry)==0) {
                  $has_error = TRUE;
                  $err_msg = "You entered an invalid country string";
                }
                else {
                  $sql = $sql.', country ="'.$myobj->{'mycountry'}.'"';
                }
              }
              if (!empty($myobj->{'url'}))
                $sql = $sql.', website = "'.$myobj->{'url'}.'"';

            }

            $sql = $sql.', partnersite = "0" WHERE userid ='.$fbid;
          } else if($num_userids == 0) {

            $sql = 'INSERT INTO users '.
              '(userid, name, organization, isreceiver, state, city,country, zipcode, phone, email, missionsexperience,'.
              ' religion, aboutme, Languages, website, partnersite) '.
              'VALUES ("'.$fbid.'","'.$name.'","'.$myobj->{'name'}.'","'.$isreceiver.'","'.$myobj->{'state'}.'","'.strip_tags($myobj->{'city'}).'","'.strip_tags($myobj->{'mycountry'}).'","'.strip_tags($myobj->{'zip'}).'","'.
              strip_tags($myobj->{'phone'}).'","'.strip_tags($myobj->{'email'}).'","'.strip_tags($myobj->{'misexp'}).'","'.
              $myobj->{'relg'}.'","'.$myobj->{'about'}.'","'.$myobj->{'languages'}.'","'.$myobj->{'url'}.'","0")';
          }

          if (!$has_error) {
            //$json['sql'] = $sql;
            $result = mysql_query($sql,$con);
            if (!$result) {
              setjsonmysqlerror($has_error,$err_msg,$sql);
            }
          }

        }
      }
    }

    if ($is_trip) {

      // check to see if any trip exists within the same creator, description or destination
      // if so, set to update

      if ($update==0) {
        $changed=0;
        $sql = 'select * from trips where creatorid="'.$fbid.'"';
        if (!empty($myobj->{'name'})) {
          $sql = $sql.' and tripname="'.$myobj->{'name'}.'"';
        }

        $result = mysql_query($sql,$con);
        if (!$result) {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
        else {
          $numrows = mysql_num_rows($result);
          if ($numrows>0) {
            $changed = 1;
            $row = mysql_fetch_array($result,MYSQL_ASSOC);
            $update = $row['id'];
          }
        }
      }

      if (!$has_error) {

        if ($update) {
          $sql = 'update trips set ';
          $sql2 = ' where id="'.$update.'"';
          $sql1 = '';
        }
        else {
          $sql = 'INSERT INTO trips (creatorid';
          $sql2 = 'VALUES ("'.$fbid.'"';
          $sql1 = '';
        }

        $namemod = 0;

        if (isset($myobj->{'name'})) {
          $tripname = strip_tags($myobj->{'name'});
          if (!empty($tripname)){
            if ($update) {
              $namemod = 1;
              $sql1 = $sql1.'tripname="'.$tripname.'"';
            }
            else {
              $sql1 = $sql1.',tripname';
              $sql2 = $sql2.',"'.$tripname.'"';
            }
          }
          else {
            $has_error = TRUE;
            $err_msg = "Trip name is a required value";
          }
        }
        else {
          $has_error = TRUE;
          $err_msg = "Trip name is a required value";
        }

        if (isset($myobj->{'about'})) {
          $tripdesc = strip_tags($myobj->{'about'});
          if (!empty($tripdesc)) {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',tripdesc="'.$tripdesc.'"';
              else {
                $sql1 = $sql1.'tripdesc="'.$tripdesc.'"';
                $namemod=1;
              }
            }
            else {
              $sql1 = $sql1.',tripdesc';
              $sql2 = $sql2.',"'.$tripdesc.'"';
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',tripdesc=NULL';
              else {
                $sql1 = $sql1.'tripdesc=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',tripdesc=NULL';
            else {
              $sql1 = $sql1.'tripdesc=NULL';
              $namemod=1;
            }	
          }
        }

        function validatephone($phone) {
          if( !preg_match("/^([1]-)?[0-9]{3}-[0-9]{3}-[0-9]{4}$/i", $phone) ) {
            return false;
          }
          else
            return true;
        }

        if (isset($myobj->{'phone'})) {
          $tripphone = strip_tags($myobj->{'phone'});
          if (!empty($tripphone)) {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',phone="'.$tripphone.'"';
              else {
                $sql1 = $sql1.'phone="'.$tripphone.'"';
                $namemod = 1;
              }
            }
            else {
              $sql1 = $sql1.',phone';
              $sql2 = $sql2.',"'.$tripphone.'"';
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',phone=NULL';
              else {
                $sql1 = $sql1.'phone=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',phone=NULL';
            else {
              $sql1 = $sql1.'phone=NULL';
              $namemod=1;
            }	
          }
        }

        function validateemailid($email) {
          if (strlen($email)==0) {
            return -1;
          }
          else {
            if (preg_match("/^[a-zA-Z0-9_.-]+@$/",trim($email)))
              return 1;
            else
              return 0;
          }
        }

        function check_email_address($email) {
          // First, we check that there's one @ symbol, 
          // and that the lengths are right.
          if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
            // Email invalid because wrong number of characters 
            // in one section or wrong number of @ symbols.
            return false;
          }
          // Split it into sections to make life easier
          $email_array = explode("@", $email);
          $local_array = explode(".", $email_array[0]);
          for ($i = 0; $i < sizeof($local_array); $i++) {
            if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&.'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",$local_array[$i])) {
              return false;
            }
          }
          // Check if domain is IP. If not, 
          // it should be valid domain name
          if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
            $domain_array = explode(".", $email_array[1]);
            if (sizeof($domain_array) < 2) {
              return false; // Not enough parts to domain
            }
            for ($i = 0; $i < sizeof($domain_array); $i++) {
              if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|.([A-Za-z0-9]+))$",$domain_array[$i])) {
                return false;
              }
            }
          }
          return true;
        }


        if (isset($myobj->{'email'})) {
          $tripemail = strip_tags($myobj->{'email'});
          if (!empty($tripemail)) {
            if (!check_email_address($tripemail)) {
              $has_error = TRUE;
              $err_msg = "Invalid Email id";
            }
            else {
              if ($update) {
                if ($namemod)
                  $sql1 = $sql1.',email="'.$tripemail.'"';
                else {
                  $sql1 = $sql1.'email="'.$tripemail.'"';
                  $namemod=1;
                }
              }
              else {
                $sql1 = $sql1.',email';
                $sql2 = $sql2.',"'.$tripemail.'"';
              }
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',email=NULL';
              else {
                $sql1 = $sql1.'email=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',email=NULL';
            else {
              $sql1 = $sql1.'email=NULL';
              $namemod=1;
            }	
          }
        }

        // function to validate a url
        function isValidURL($url)
        {
          return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
        }

        if (isset($myobj->{'url'})) {
          $tripurl = strip_tags($myobj->{'url'});
          //if (!empty($myobj['url'])) {
          if (!empty($tripurl)) {
            //$tripurl = $myobj['url'];
            if (!isValidURL($tripurl)) {
              $has_error = TRUE;
              $err_msg = "Invalid URL for website";
            }
            else {
              if ($update) {
                if ($namemod)
                  $sql1 = $sql1.',website="'.$tripurl.'"';
                else {
                  $sql1 = $sql1.'website="'.$tripurl.'"';
                  $namemod=1;
                }

              }
              else {
                $sql1 = $sql1.',website';
                $sql2 = $sql2.',"'.$tripurl.'"';
              }
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',website=NULL';
              else {
                $sql1 = $sql1.'website=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',website=NULL';
            else {
              $sql1 = $sql1.'website=NULL';
              $namemod=1;
            }	
          }
        }

        if (isset($myobj->{'dur'})) {
          if (!empty($myobj->{'dur'})) {
            $tripdurid = $myobj->{'dur'};
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',durationid="'.$tripdurid[0].'"';
              else {
                $sql1 = $sql1.'durationid="'.$tripdurid[0].'"';
                $namemod=1;
              }
            }
            else {
              $sql1 = $sql1.',durationid';
              $sql2 = $sql2.',"'.$tripdurid[0].'"';
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',durationid=NULL';
              else {
                $sql1 = $sql1.'durationid=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',durationid=NULL';
            else {
              $sql1 = $sql1.'durationid=NULL';
              $namemod=1;
            }	
          }
        }

        if (isset($myobj->{'stage'})) {
          $tripstage = $myobj->{'stage'};
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',isinexecutionstage="'.$tripstage.'"';
            else {
              $sql1 = $sql1.'isinexecutionstage="'.$tripstage.'"';
              $namemod=1;
            }
          }
          else {
            $sql1 = $sql1.',isinexecutionstage';
            $sql2 = $sql2.',"'.$tripstage.'"';
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',isinexecutionstage=NULL';
            else {
              $sql1 = $sql1.'isinexecutionstage=NULL';
              $namemod=1;
            }	
          }
        }

        function getdatestring($year,$month,$date) {

          if ($month<10)
            $smonth = strval($month);
          else
            $smonth = strval($month);

          if ($date<10)
            $sdate = strval($date);
          else
            $sdate = strval($date);


          $res = $year.'-'.$smonth.'-'.$sdate.' '.'00:00:00';

          return $res;
        }
/*
function validate_date($val,$year,$month,$day,$update,&$has_error,&$err_msg) {

if ($month%2==0) {
  // special case for february
  if ($month==2) {
    if ($year%4 ==0) {
      if (($year%100 == 0) && ($year%400 !=0)) {
        if ($day > 28) {
          if ($val==1) {
            $has_error = TRUE;
            $err_msg = "Departure Date out of range";
          }
          else {
            $has_error = TRUE;
            $err_msg = "Return Date out of range";
          }
        }
      }
      else {
        if ($day > 29) {
          //echo 'Day should be less than 29 <br />';
          if ($val==1) {
            $has_error = TRUE;
            $err_msg = "Departure Date out of range";
          }
          else {
            $has_error = TRUE;
            $err_msg = "Return Date out of range";
          }
        }
      }
    }
    else {
      if ($day > 28) {
        if ($val==1) {
            $has_error = TRUE;
            $err_msg = "Departure Date out of range";
        }
        else {
            $has_error = TRUE;
            $err_msg = "Return Date out of range";
        }
      }
    }
  }
  else if ($month != 8) {
    if ($day > 30) {
    //echo 'Day must not be greater than 30 <br />';
    if ($val==1) {
      $has_error = TRUE;
      $err_msg = "Departure Date out of range";
    }
    else {
      $has_error = TRUE;
      $err_msg = "Return Date out of range";
    }
    }
  }
}

}
 */

        function validate_return($year1,$month1,$day1,$year2,$month2,$day2,$update,&$has_error,&$err_msg) {

          if ($year2 < $year1) {
            $has_error = TRUE;
            $err_msg = "Return Date cannot be before Departure Date";
          }
          else if ($year2 == $year1) {
            if ($month2 < $month1) {
              $has_error = TRUE;
              $err_msg = "Return Date cannot be before Departure Date";
            }
            else if ($month2==$month1) {
              if ($day2 < $day1) {
                $has_error = TRUE;
                $err_msg = "Return Date cannot be before Departure Date";
              }
            }
          }

        }

        if (isset($myobj->{'DepartYear'})) {
          if ((!empty($myobj->{'DepartYear'})) && (!empty($myobj->{'DepartMonth'})) && (!empty($myobj->{'DepartDay'}))) {
            //$thisyear = date("Y");

            if (!checkdate($myobj->{'DepartMonth'},$myobj->{'DepartDay'},$myobj->{'DepartYear'})) {
              $has_error = TRUE;
              $err_msg = "Invalid Departure Date";
            }	//validate_date(1,$myobj->{'DepartYear'},$myobj->{'DepartMonth'},$myobj->{'DepartDay'},$update,$has_error,$err_msg);

            $tripdpt = getdatestring($myobj->{'DepartYear'},$myobj->{'DepartMonth'},$myobj->{'DepartDay'});

            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',departure="'.$tripdpt.'"';
              else {
                $sql1 = $sql1.'departure="'.$tripdpt.'"';
                $namemod=1;
              }
            }
            else {
              //echo 'DEPARTING: '.$tripdpt;
              $sql1 = $sql1.',departure';
              $sql2 = $sql2.',"'.$tripdpt.'"';
            }
          }
        }

        if (isset($myobj->{'ReturnYear'})) {
          if ((!empty($myobj->{'ReturnYear'})) && (!empty($myobj->{'ReturnMonth'})) && (!empty($myobj->{'ReturnDay'}))) {

            if (!checkdate($myobj->{'ReturnMonth'},$myobj->{'ReturnDay'},$myobj->{'ReturnYear'})) {
              $has_error = TRUE;
              $err_msg = "Invalid Return Date";
            }
            //validate_date(2,$myobj->{'ReturnYear'},$myobj->{'ReturnMonth'},$myobj->{'ReturnDay'},$update,$has_error,$err_msg);

            validate_return($myobj->{'DepartYear'},$myobj->{'DepartMonth'},$myobj->{'DepartDay'},$myobj->{'ReturnYear'},$myobj->{'ReturnMonth'},$myobj->{'ReturnDay'},$update,$has_error,$err_msg);

            $tripret = getdatestring($myobj->{'ReturnYear'},$myobj->{'ReturnMonth'},$myobj->{'ReturnDay'});

            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',returning="'.$tripret.'"';
              else {
                $sql1 = $sql1.'returning="'.$tripret.'"';
                $namemod=1;
              }
            }
            else {
              $sql1 = $sql1.',returning';
              $sql2 = $sql2.',"'.$tripret.'"';
            }
          }
        }

        if (isset($myobj->{'city'})) {
          $tripdest = strip_tags($myobj->{'city'});
          if (!empty($tripdest)) {
            if ($update) {
              if ($namemod) 
                $sql1 = $sql1.',destination="'.$tripdest.'"';
              else {
                $sql1 = $sql1.'destination="'.$tripdest.'"';
                $namemod=1;
              }
            }
            else {
              $sql1 = $sql1.',destination';
              $sql2 = $sql2.',"'.$tripdest.'"';
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',destination=NULL';
              else {
                $sql1 = $sql1.'destination=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',destination=NULL';
            else {
              $sql1 = $sql1.'destination=NULL';
              $namemod=1;
            }	
          }
        }

        if (isset($myobj->{'languages'})) {
          $languages = $myobj->{'languages'};
          if (!empty($languages)) {
            $mystr = "";
            $ii=0;
            foreach($languages as $ms) {
				// store characters, not values from input
				$sqll = 'select * from languages where id="'.$ms.'"';
				$resultl = mysql_query($sqll, $con);

				if (!$resultl) {
					setjsonmysqlerror($has_error, $err_msg, $sqll);
				} else {			
				$rowl = mysql_fetch_array($resultl);
			
              $mystr = $mystr.$rowl['englishname'];
              $ii++;
              if ((count($languages)>1)&&($ii<count($languages))) {
                $mystr = $mystr.",";
              }
			  }
            }

            if ($update) {
              if ($namemod) 
                $sql1 = $sql1.',Languages="'.$mystr.'"';
              else {
                $sql1 = $sql1.'Languages="'.$mystr.'"';
                $namemod=1;
              }
            }
            else {
              $sql1 = $sql1.',Languages';
              $sql2 = $sql2.',"'.$mystr.'"';
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',Languages=NULL';
              else {
                $sql1 = $sql1.'Languages=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',Languages=NULL';
            else {
              $sql1 = $sql1.'Languages=NULL';
              $namemod=1;
            }	
          }
        }

/*
$languages = strip_tags($myobj->{'languages'});
if (!empty($languages)) {
  if (isset($update)) {
  if ($namemod) 
  $sql1 = $sql1.',Languages="'.$languages.'"';
  else {
  $sql1 = $sql1.'Languages="'.$languages.'"';
  $namemod=1;
  }
  }
  else {
  $sql1 = $sql1.',Languages';
  $sql2 = $sql2.',"'.$languages.'"';
  }
}
else {
  if (isset($update)) {
  if ($namemod)
  $sql1 = $sql1.',Languages=NULL';
  else {
  $sql1 = $sql1.'Languages=NULL';
  $namemod=1;
  }	
  }
}
 */

        if (isset($myobj->{'acco'})) {
          $acco = strip_tags($myobj->{'acco'});
          if (!empty($acco)) {
            if ($update) {
              if ($namemod) 
                $sql1 = $sql1.',accommodationlevel="'.$acco.'"';
              else {
                $sql1 = $sql1.'accommodationlevel="'.$acco.'"';
                $namemod=1;
              }
            }
            else {
              $sql1 = $sql1.',accommodationlevel';
              $sql2 = $sql2.',"'.$acco.'"';
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',accommodationlevel=NULL';
              else {
                $sql1 = $sql1.'accommodationlevel=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',accommodationlevel=NULL';
            else {
              $sql1 = $sql1.'accommodationlevel=NULL';
              $namemod=1;
            }	
          }
        }

        if (isset($myobj->{'mycountry'})) {
          if (!empty($myobj->{'mycountry'})) {
            $tripcountry = $myobj->{'mycountry'};
            $sql5 = 'select * from countries where id="'.$tripcountry[0].'"';
            $result5 = mysql_query($sql5);
            $row5 = mysql_fetch_array($result5);
            if ($update) {
              if ($namemod) 
                $sql1 = $sql1.',country="'.$row5['longname'].'"';
              else {
                $sql1 = $sql1.'country="'.$row5['longname'].'"';
                $namemod=1;
              }
            }
            else {
              $sql1 = $sql1.',country';
              $sql2 = $sql2.',"'.$row5['longname'].'"';
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',country=NULL';
              else {
                $sql1 = $sql1.'country=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',country=NULL';
            else {
              $sql1 = $sql1.'country=NULL';
              $namemod=1;
            }	
          }
        }

        if (isset($myobj->{'numpeople'})) {
          $tripnump = strip_tags($myobj->{'numpeople'});
          if (!empty($tripnump)) {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',numpeople="'.$tripnump.'"';
              else {
                $sql1 = $sql1.'numpeople="'.$tripnump.'"';
                $namemod=1;
              }
            }
            else {
              $sql1 = $sql1.',numpeople';
              $sql2 = $sql2.',"'.$tripnump.'"';
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',numpeople=NULL';
              else {
                $sql1 = $sql1.'numpeople=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',numpeople=NULL';
            else {
              $sql1 = $sql1.'numpeople=NULL';
              $namemod=1;
            }	
          }
        }

        if (isset($myobj->{'zip'})) {
          $tripzip = strip_tags($myobj->{'zip'});
          if (!empty($tripzip)) {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',zipcode="'.$tripzip.'"';
              else {
                $sql1 = $sql1.'zipcode="'.$tripzip.'"';
                $namemod=1;
              }
            }
            else {
              $sql1 = $sql1.',zipcode';
              $sql2 = $sql2.',"'.$tripzip.'"';
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',zipcode=NULL';
              else {
                $sql1 = $sql1.'zipcode=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',zipcode=NULL';
            else {
              $sql1 = $sql1.'zipcode=NULL';
              $namemod=1;
            }	
          }
        }

        if (isset($myobj->{'relg'})) {
          if (!empty($myobj->{'relg'})) {
            $triprelg = $myobj->{'relg'};
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',religion="'.$triprelg.'"';
              else {
                $sql1 = $sql1.'religion="'.$triprelg.'"';
                $namemod=1;
              }
            }
            else {
              $sql1 = $sql1.',religion';
              $sql2 = $sql2.',"'.$triprelg.'"';
            }
          }
          else {
            if ($update) {
              if ($namemod)
                $sql1 = $sql1.',religion=NULL';
              else {
                $sql1 = $sql1.'religion=NULL';
                $namemod=1;
              }	
            }
          }
        }
        else {
          if ($update) {
            if ($namemod)
              $sql1 = $sql1.',religion=NULL';
            else {
              $sql1 = $sql1.'religion=NULL';
              $namemod=1;
            }	
          }
        }

        if ($update) {
          $sql = $sql.$sql1.$sql2;
        }
        else {
          date_default_timezone_set('America/New_York');
          $todayy = date("Y");
          $todaym = date("m");
          $todayd = date("d");
          $today = getdatestring($todayy,$todaym,$todayd);
          $sql1 = $sql1.',dateadded';
          $sql2 = $sql2.',"'.$today.'"';

          $sql = $sql.$sql1.') '.$sql2.')';
        }

        //echo 'Main SQL string: '.$sql.'<br />';

        $result = mysql_query($sql,$con);
        if (!$result) {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
        else {
          if ($update) {
            $tripid = $update;
          }
          else {

            $sql = 'select max(id) as tripid from trips where creatorid="'.$fbid.'"';

            $result = mysql_query($sql,$con);
            if (!$result) {
              setjsonmysqlerror($has_error,$err_msg,$sql);
            }
            else {
              while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $tripid = $row['tripid'] + 0;  
                break;
              }

              // now update the trip members table
              $sql = 'INSERT into tripmembers (userid, tripid, isadmin, invited, accepted, type, datejoined) VALUES ("'.$fbid.'","'.$tripid.'","1","1","1","'.$membertype.'","'.$today.'")';

              $result = mysql_query($sql,$con);
              if (!$result) {
                setjsonmysqlerror($has_error,$err_msg,$sql);
              }	
            }
          }
        }

      } //if no error
    } //trip section ends

    if (!$is_trip) {
    // clear out the old entries so that we start fresh
    $sql = "DELETE FROM skillsselected WHERE userid='".$fbid."'";
    $result = mysql_query($sql,$con);
    if (!$result) {
      setjsonmysqlerror($has_error,$err_msg,$sql);
    }

    $sql = "DELETE FROM regionsselected WHERE userid='".$fbid."'";
    $result = mysql_query($sql,$con);
    if (!$result) {
      setjsonmysqlerror($has_error,$err_msg,$sql);
    }
    $sql = "DELETE FROM countriesselected WHERE userid='".$fbid."'";
    $result = mysql_query($sql,$con);
    if (!$result) {
      setjsonmysqlerror($has_error,$err_msg,$sql);
    }
    $sql = "DELETE FROM usstatesselected WHERE userid='".$fbid."'";
    $result = mysql_query($sql,$con);
    if (!$result) {
      setjsonmysqlerror($has_error,$err_msg,$sql);
    }
    $sql = "DELETE FROM durationsselected WHERE userid='".$fbid."'";
    $result = mysql_query($sql,$con);
    if (!$result) {
      setjsonmysqlerror($has_error,$err_msg,$sql);
    }

    if (isset($myobj->{'medfacil'})) {
      $medfacil = $myobj->{'medfacil'};
      foreach($medfacil as $ms) {
        $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
        $result = mysql_query($sql,$con);
        if(!$result){
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
      }
    }

    if (isset($myobj->{'nonmedfacil'})) {
      $nonmedfacil = $myobj->{'nonmedfacil'};
      foreach($nonmedfacil as $ms) {
        $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
        $result = mysql_query($sql,$con);
        if(!$result){
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
      }
    }

    if (isset($myobj->{'medskills'})) {
      $medskills = $myobj->{'medskills'};
      foreach($medskills as $ms) {
        $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
        $result = mysql_query($sql,$con);
        if(!$result){
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
      }
    }

    if (isset($myobj->{'otherskills'})) {
      $otherskills=$myobj->{'otherskills'};
      foreach($otherskills as $ms) {
        $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
        $result = mysql_query($sql,$con);
        if(!$result) {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
      }
    }

    if (isset($myobj->{'spiritserv'})) {
      $relgskills=$myobj->{'spiritserv'};
      foreach($relgskills as $ms) {
        $sql = "INSERT INTO skillsselected VALUES ('".$fbid."','".$ms."')";
        $result = mysql_query($sql,$con);
        if(!$result) {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
      }
    }

    if (isset($myobj->{'region'})) {
      $region=$myobj->{'region'};
      foreach($region as $ms) {
        $sql = "INSERT INTO regionsselected VALUES ('".$fbid."','".$ms."')";
        $result = mysql_query($sql,$con);
        if(!$result) {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
      }
    }

    if (isset($myobj->{'state'})) {
      $mystate = $myobj->{'state'};
      $sql = "INSERT INTO usstatesselected VALUES ('".$fbid."','".$mystate."')";
      $result = mysql_query($sql,$con);
      if(!$result) {
        setjsonmysqlerror($has_error,$err_msg,$sql);
      }
    }

    if (isset($myobj->{'country'})) {
      $country = $myobj->{'country'};
      if (count($country)>1) {
        foreach($country as $ms) {
          $sql = "INSERT INTO countriesselected VALUES ('".$fbid."','".$ms."')";
          $result = mysql_query($sql,$con);
          if(!$result) {
            setjsonmysqlerror($has_error,$err_msg,$sql);
          }
        }
      }
      else {
        $sql = "INSERT INTO countriesselected VALUES ('".$fbid."','".$country[0]."')";
        $result = mysql_query($sql,$con);
        if(!$result) {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
      }
    }

    if (isset($myobj->{'dur'})) {
      $dur = $myobj->{'dur'};
      if (count($dur) > 1) {
        foreach($dur as $ms) {
          $sql = "INSERT INTO durationsselected VALUES ('".$fbid."','".$ms."')";
          $result = mysql_query($sql,$con);
          if(!$result) {
            setjsonmysqlerror($has_error,$err_msg,$sql);
          }
        }
      }
      else {
        $sql = "INSERT INTO durationsselected VALUES ('".$fbid."','".$dur."')";
        $result = mysql_query($sql,$con);
        if(!$result) {
          setjsonmysqlerror($has_error,$err_msg,$sql);
        }
      }
    }
	
	}

  }

}

$json['has_error'] = $has_error;

if ($has_error) {
  $json['err_msg'] = $err_msg;
}

echo json_encode($json);

?>
