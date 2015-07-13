<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Course-Match Homepage</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="bootstrap-social/bootstrap-social.less" rel="stylesheet">
    <link href="bootstrap-social/bootstrap-social.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
  <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
  </head> 

  <div>
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



        if (isset($_SESSION['PICTURE'])){
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

                      $_SESSION['FRIENDS'] = $friendsString;
                      // now lets get the photo so we can display it... 


                      // with all this facebook information we need to save the courses that he is taking here... 

                     // Save_Courses_to_User($fbid, $fbfullname, $femail, $friendsString);

                        $_SESSION['PICTURE']= $profile_pic = "http://graph.facebook.com/".$fbid."/picture";
                        echo "<div>";               
                        echo "<img src=\"" . $profile_pic . "\" />"; 

                        echo $fbfullname;

                        echo "</div>";

                }else{
                   // there is no session and we redirect to the login page... 
                   //Chromephp::log("there is no session here");
                    $loginUrl = $helper->getLoginUrl(array("user_friends", "user_status","email","public_profile"));
                    
                  header("Location: ".$loginUrl);
                }
              }else{


              echo "<div>";               
              echo "<img src=\"" . $_SESSION['PICTURE'] . "\" />"; 
              echo $_SESSION['FULLNAME'];
              echo "</div>";
              }
              ?>
  </div>

<ul class="nav nav-tabs">
  <li role="presentation"><a href="CourseFinder.php">Course Browser</a></li>
  <li role="presentation" class="active" ><a href="#">Profile</a></li>
  <li role="presentation"><a href="StudyGroup.php">Study Groups</a></li>
  <li role="presentation"><a href="WhosFree.php">Who's Free</a></li>
</ul>


<body>



<?php

// what this does... saves the courses from the previous page. gets the user that is currently logged in and then saves it...


Save_Courses_to_User($_SESSION['FBID'], $_SESSION['FULLNAME'], $_SESSION['EMAIL'], $_SESSION['FRIENDS']);


if(isset($_GET['SaveCourses'])){

  Chromephp::log($_GET['cart']);
  $_SESSION['SaveCourses'] = $_GET['cart'];

}



// save the user data to the user...
function Save_Courses_to_User($fbid, $fbfullname, $femail, $friendstring){

	if(isset($_SESSION['SaveCourses'])){

    $cart = $_SESSION['SaveCourses'];
	
    $nameString = explode(' ',$fbfullname);
    $firstname = $nameString[0];
    $lastname = $nameString[1];

    
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


function find_friend_matches($friendstring, $courses, $conn){

$courses_array = json_decode($courses);
$friends_array = explode(', ' , $friendstring);

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

</body> 

</html>



