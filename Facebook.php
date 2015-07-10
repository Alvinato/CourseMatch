<?php

include 'ChromePhp.php';

session_start();

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



$theEmail;
$thePassword;

// Checks if we have an email here...
 if(isset($_POST['email'])) { 
      $theEmail = $_POST['email'];
  }
// checks if we have a password here
 if(isset($_POST['password'])) { 
      $thePassword = $_POST['password'];
  }



// ----->> the facebook connection stuff

  // lets create the access token... 

  //$helper = new FacebookRedirectLoginHelper('http://localhost/CourseMatch/Facebook.php', '857265011029343', '6895d874134fec6bfe666c55de5d4034');

	



FacebookSession::setDefaultApplication('857265011029343', '6895d874134fec6bfe666c55de5d4034'); 


$helper = new FacebookRedirectLoginHelper('http://localhost/CourseMatch/Facebook.php');
Chromephp::log("this is the helper");
Chromephp::log($helper);
// gather the information from the login...
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

Chromephp::log($session);

// see if we have a session... 
if (isset($session)){

  // graph api request for user data...
  $request = new FacebookRequest($session, 'GET', '/me');

  $response = $request ->execute();

  // get the response... 

    $graphObject = $response->getGraphObject();
     $fbid = $graphObject->getProperty('id');              // To Get Facebook ID
     $fbfullname = $graphObject->getProperty('name'); // To Get Facebook full name
     $femail = $graphObject->getProperty('email');    // To Get Facebook email ID

     /* ---- Session Variables -----*/
     $_SESSION['FBID'] = $fbid;           
        $_SESSION['FULLNAME'] = $fbfullname;
     $_SESSION['EMAIL'] =  $femail;

     Chromephp::log("we have the user information here!!");
     Chromephp::log($fbid);
     Chromephp::log($fbfullname);
     Chromephp::log($femail);   // not getting the email here because we need to ask for further permissions...

     // lets just try to grab the friendslist from here first...
     // provide them with the ability to logout later... 

     // returns the persons friends who are also using the app... not the entire list as a whole...
     $friends = (new FacebookRequest( $session, 'GET', '/me/friends' ))->execute()->getGraphObject()->asArray();
     Chromephp::log("this is the list of friends");
      Chromephp::log($friends);
      Chromephp::log($friends['data']);
      Chromephp::log($friends['data'][0]->name);
      Chromephp::log($friends['data'][0]->id);

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
      save_user_data($fbid, $fbfullname, $femail, $friendsString);
     
      header('Location: http://localhost/CourseMatch/CourseFinder.php');



}else{

  // there is no session and we redirect to the login page... 
  Chromephp::log("there is no session here");
   $loginUrl = $helper->getLoginUrl(array("user_friends", "user_status","email","public_profile"));
   Chromephp::log($loginUrl);
 header("Location: ".$loginUrl);
}

function save_user_data($fbid, $fbfullname, $email, $friends){



  Chromephp::log("this function is being called right now");


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
  

// split up the username... 
$fullnameArray = explode(" ", $fbfullname);

$firstName = $fullnameArray[0];
$lastName = $fullnameArray[1];

// query and check...
$sql = "SELECT id, firstname, lastname FROM Users WHERE firstname = '$firstName' AND lastname = '$lastName'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
       
    }
} else {
    echo "0 results meaning that this person has not been saved inside the db yet... ";
    // save the user into the db...
    $sql = "INSERT INTO Users (id, firstname, lastname, email, friends)
    VALUES ($fbid, '$firstName', '$lastName', '$email', '$friends')";

    if ($conn->query($sql) === TRUE) {
    //echo "New record created successfully";
    } else {
    //echo "Error: " . $sql . "<br>" . $conn->error;
    }

}



/*
// sql to create table
$sql = "CREATE TABLE Users (
id INT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
firstname VARCHAR(30) NOT NULL,
lastname VARCHAR(30) NOT NULL,
email VARCHAR(50),
friends VARCHAR(100000),
reg_date TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
   // echo "Table Users created successfully";
} else {
    //echo "Error creating table: " . $conn->error;
}
*/




}







// If you're making app-level requests:
//$session = FacebookSession::newAppSession();

//ChromePhp::log($session);
//Chromephp::log($session->getAccessToken());
// this will give us the access token now we need to send it through to make a request of some sort...
//$accessToken = $session ->getAccessToken();





/*try {
  $session->validate();
} catch (FacebookRequestException $ex) {
  // Session not valid, Graph API returned an exception with the reason.
  Chromephp::log("Facebook Request Exception has been thrown");
  echo $ex->getMessage();
} catch (Exception $ex) {
  // Graph API returned info, but it may mismatch the current app or have expired.
Chromephp::log("Exception has been thrown");
  echo $ex->getMessage();
}*/



// here lets try to get friends from the list
// Add `use Facebook\FacebookRequest;` to top of file
/*$request = new FacebookRequest($session, 'GET', '/me');

Chromephp::log($request);
// with this request we need to create an access token


Chromephp::log("this line is working 1");
$response = $request->execute();
Chromephp::log("this line is working 2");
Chromephp::log("made it after we created the facebook session");
$graphObject = $response->getGraphObject();*/






?>