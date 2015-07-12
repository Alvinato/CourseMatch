<?php

// this is going to take in the info about the courses and its going to save it to the users profile and display it properly... 
include 'ChromePhp.php';
//define('FACEBOOK_SDK_V4_SRC_DIR', '/path/to/fb-php-sdk-v4/src/Facebook/');
require __DIR__ . '/facebook-php-sdk/autoload.php';
//require __DIR__ . '/facebook-php-sdk/src/Facebook/FacebookSession.php'
//require_once 'autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;
use Facebook\Entities\AccessToken;
use Facebook\HttpClients\FacebookCurlHttpClient;
use Facebook\HttpClients\FacebookHttpable;


session_start();

if(isset($_GET['SaveCourses'])){

  Chromephp::log($_GET['cart']);
  $_SESSION['SaveCourses'] = $_GET['cart'];

}


  
FacebookSession::setDefaultApplication('857265011029343', '6895d874134fec6bfe666c55de5d4034'); 
$helper = new FacebookRedirectLoginHelper('http://localhost/CourseMatch/Profile.php');


// here we need to create the session... 
	try {
  $session = $helper->getSessionFromRedirect();
} catch( FacebookRequestException $ex ) {
  Chromephp::log("FacebookRequestException");
  Chromephp::log($ex);
  // When Facebook returns an error
} catch( Exception $ex ) {
  // When validation fails or other local issues
  Chromephp::log("Exception");
  Chromephp::log($ex);
}

	if (isset($session)){
			
		
  			$request = new FacebookRequest($session, 'GET', '/me');

  			$response = $request ->execute();

    		$graphObject = $response->getGraphObject();
     		$fbid = $graphObject->getProperty('id');              // To Get Facebook ID
     		$fbfullname = $graphObject->getProperty('name'); // To Get Facebook full name
     		$femail = $graphObject->getProperty('email');    // To Get Facebook email ID

     		
     		$_SESSION['FBID'] = $fbid;           
        	$_SESSION['FULLNAME'] = $fbfullname;
     		$_SESSION['EMAIL'] =  $femail;

     		$friends = (new FacebookRequest( $session, 'GET', '/me/friends' ))->execute()->getGraphObject()->asArray();
     		

      		$friendsString = ''; // use this string to save into the friendslist...

      		$sizeofFriendlist = count($friends['data']);

      		for($x = 0; $x < $sizeofFriendlist; $x++){
        			$currentFriend = $friends['data'][$x]->name;
			        if ($friendsString == ''){
          				$friendsString = $currentFriend;
          					continue;
        			}
        			$friendsString = $friendsString.', '.$currentFriend;
      			} 

      			// with all this facebook information we need to save the courses that he is taking here... 

      			Save_Courses_to_User($fbid, $fbfullname, $femail, $friendsString);

			}else{
				 // there is no session and we redirect to the login page... 
 				 //Chromephp::log("there is no session here");
   				$loginUrl = $helper->getLoginUrl(array("user_friends", "user_status","email","public_profile"));
   				
 				header("Location: ".$loginUrl);
			}





// save the user data to the user...
function Save_Courses_to_User($fbid, $fbfullname, $femail, $friendstring){

  //Chromephp::log("This is working");

  //Chromephp::log($_SESSION['SaveCourses']);

	if(isset($_SESSION['SaveCourses'])){

    $cart = $_SESSION['SaveCourses'];
		//Chromephp::log($cart);  // dont need this as an array leave it as a string... 
    //$cart = json_decode($cart);
		//Chromephp::log($cart);

    // split the fullname into first and last names... 
    $nameString = explode(' ',$fbfullname);
    $firstname = $nameString[0];
    $lastname = $nameString[1];

    //Chromephp::log($firstname);
    //Chromephp::log($lastname);
    // we have to find the row to alter based on this input info...

    // connect to the db here 
    $db = "CourseMatcher";
    $servername = "localhost";
    $username = "root";
    $password = "";

    $conn = new mysqli($servername, $username, $password, $db);

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } 

      $sql = " UPDATE Users
      SET courses='$cart'
      WHERE email='$femail'";

      if ($conn->query($sql) === TRUE) {
        echo "We have updated successfully";
        } else {
        echo "Error Updating " . $conn->error;
      }
		}

    find_friend_matches($friendstring, $cart, $conn);


	}


// this function is going to go through each course and then through each course we try to find each friend that is taking that course!
function find_friend_matches($friendstring, $courses, $conn){

// lets create the listing of friends that are taking the same courses as this person... 

// go through all the courses first though... 
$courses_array = json_decode($courses);
$friends_array = explode(', ' , $friendstring);
//Chromephp::log($courses_array);
//Chromephp::log($friends_array);
//Chromephp::log($friendstring);
// we go through the list of courses... 

// for each course we look up each friend and their courses...
for($r = 0; $r < count($courses_array); $r++){

  Chromephp::log("inside the first loop right now");

  $current_course = $courses_array[$r];
  $current_course_string = json_encode($current_course);
  
  // here lets create the starts of a list tag...

  echo "<h2>$current_course_string</h2>";
  echo "<ul>";
  for($x = 0; $x < count($friends_array); $x++){
    $current_friend = $friends_array[$x];
    $current_friend_name_array = explode(' ', $current_friend);
    $current_friend_firstname = $current_friend_name_array[0];
    $current_friend_lastname = $current_friend_name_array[1];
    $current_friend_courses;
      $sql = "SELECT Courses FROM Users WHERE firstname= '$current_friend_firstname' AND lastname= '$current_friend_lastname'";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          //echo $row['Courses'];
          // go through each course in the friends and see if it matches this instance of course.
          $current_friend_courses = $row['Courses'];  
          $current_friend_courses_string = json_encode($current_friend_courses);
          $current_friend_courses = str_replace(array('[',']'), '',$current_friend_courses);
          
            if ($current_course_string == $current_friend_courses){
              // we need to take that friend and the course that he is matched with... 

              echo "<li>$current_friend</li>";
              
            }
       
         }
      } 
    }
  echo "</ul>";

  }

}




?>