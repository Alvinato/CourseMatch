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
    
    <link href="bootstrap-social/bootstrap-social.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
  <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
  </head> 

  <div>
    <?php
        include 'ChromePhp.php';
        require __DIR__ . '/facebook-php-sdk/autoload.php';
    
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


if(isset($_GET['remove'])){
          $course = $_GET['$course'];
          Chromephp::log($course);
          Chromephp::log("the delete button was pressed here!!");
          echo "this is being called";
        }


$name = $_SESSION['FULLNAME'];
$name_array = explode(' ', $name);
$_SESSION['firstname'] = $firstname = $name_array[0];
$_SESSION['lastname']= $lastname = $name_array[1];


  //Chromephp::log($_GET['remove']);
  //Chromephp::log($_POST['remove']);
 //if(isset($_POST['remove'])){
 // Chromephp::log("the remove button has been pressed");
// }
  
 

gather_user_courses();



 
// this function is going to gather the courses of the user
function gather_user_courses(){ 

  $db = "CourseMatcher";
    $servername = "localhost";
    $username = "root";
    $password = "";

    $conn = new mysqli($servername, $username, $password, $db);

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } 

  $firstname = $_SESSION['firstname'];
  $lastname = $_SESSION['lastname'];
  $sql = "SELECT Courses FROM Users WHERE firstname= '$firstname' AND lastname= '$lastname'";

   $result = $conn->query($sql);
     
      if ($result->num_rows > 0) {  
        while($row = $result->fetch_assoc()) {
           $user_courses = $row['Courses'];  
           $course_object = json_decode($user_courses);
           $_SESSION['user_courses'] = $course_object; // this holds the user courses...
        }
      }

      friend_matches($conn);
}


function friend_matches($conn){
  $firstname = $_SESSION['firstname'];
  $lastname = $_SESSION['lastname'];
  $friendstring = $_SESSION['FRIENDS'];
  $courses_array = $_SESSION['user_courses'];
  $friends_array = explode(', ' , $friendstring);
  for($r = 0; $r < count($courses_array); $r++){
    $current_course = $courses_array[$r];
    $current_course_string = json_encode($current_course);
   
    echo "<table border='1' style='width:100%''>";
    
    echo "<tr>
              <td>Course</td>
              <td>Section</td>
              <td>Type</td> 
              <td>Day</td>
              <td>Start Time</td>
              <td>End Time</td>
          </tr>";

  $course = $current_course->course;
  $section = $current_course->section;
  $type = $current_course->type;
  $start = $current_course->currentstart;
  $end = $current_course->currentend ;
  $day = $current_course->currentday;
      echo"<tr> 
        <td>$course</td>
          <td>$section</td> 
          <td>$type</td>
          <td>$day</td>
          <td>$start</td>
          <td>$end</td>
          <td> 
            <form action= 'ProfileHelper.php'>  
            <input type='hidden' name='course' value =$course />
            <input type='hidden' name='section' value =$section />
            <input type='hidden' name='type' value =$type />
            <input type='hidden' name='day' value =$day />
            <input type='hidden' name='start' value =$start />
            <input type='hidden' name='end' value =$end />
            <input type='hidden' name='firstname' value =$firstname/>
            <input type='hidden' name='lastname' value =$lastname/>
            <input type='submit' name = 'remove' value='Remove'></button> 
            </form>
          </td>  
        </tr>";
// we could try calling another function and having the page reload again...

        // inside this table we are going to list every friend that is matched wit it
     echo "<tr>
              <td>Matched Friends---->>></td>
              <td>
                <ul>";
  
  for($x = 0; $x < count($friends_array); $x++){
    //Chromephp::log("inside the friends loop right now");
    $current_friend = $friends_array[$x];
    $current_friend_name_array = explode(' ', $current_friend);
    $current_friend_firstname = $current_friend_name_array[0];
    $current_friend_lastname = $current_friend_name_array[1];
    $current_friend_courses;
      $sql = "SELECT Courses FROM Users WHERE firstname= '$current_friend_firstname' AND lastname= '$current_friend_lastname'";
      $result = $conn->query($sql);
     // Chromephp::log($current_friend);
      if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          $current_friend_courses = $row['Courses'];  
          $current_friend_courses = json_decode($current_friend_courses);
           for ($i=0; $i < count($current_friend_courses); $i++){
        //    Chromephp::log($current_friend_courses[$i]);
            $friend_course_string = json_encode($current_friend_courses[$i]);
                 if ($current_course_string == $friend_course_string){
                       echo "<li>$current_friend</li>";
              }  
           }
       
         }
      } 
    }
      echo "      </ul>
                </td>
                 </tr>
            </table>";
  }
}
?>

</body> 

</html>



