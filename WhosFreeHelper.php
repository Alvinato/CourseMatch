<?php
// place the initial fb code at the top here
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
            
            if (!isset($_SESSION['PICTURE'])){

            FacebookSession::setDefaultApplication('857265011029343', '6895d874134fec6bfe666c55de5d4034'); 
            $helper = new FacebookRedirectLoginHelper('http://localhost/CourseMatch/CourseFinder.php');
            
                    try {
                  $session = $helper->getSessionFromRedirect();
                } catch( FacebookRequestException $ex ) {
                  Chromephp::log("FacebookRequestException");
                  Chromephp::log($ex);
            
                } catch( Exception $ex ) {
            
                  Chromephp::log("Exception");
                  Chromephp::log($ex);
            }

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

                    
                    //create the url... 
				$_SESSION['PICTURE']= $profile_pic = "http://graph.facebook.com/".$fbid."/picture";
                        echo "<div>";               
                        echo "<img src=\"" . $profile_pic . "\" />"; 

                        echo $fbfullname;

                        echo "</div>";

            }else{
                // there is no session and we redirect to the login page... 
                  Chromephp::log("there is no session here");
                   $loginUrl = $helper->getLoginUrl(array("user_friends", "user_status","email","public_profile", "user_photos"));
                   Chromephp::log($loginUrl);
                 header("Location: ".$loginUrl);
            }
}else{

echo "<div>";               
echo "<img src=\"" . $_SESSION['PICTURE'] . "\" />"; 
echo $_SESSION['FULLNAME'];
echo "</div>";
}



// this is going to give back the friends that we are going to show in javscript
$aResult = array();


Chromephp::log("inside whosfreehelper");

if(isset($_POST['functionname'])){
	
	if($_POST['functionname']='whosfree'){
		$aResult['result'] = whosfree($_POST['arguments']);
		Chromephp::log($aResult['result']);
	}

}


echo json_encode($aResult); // this i think is going to send the data back through to the javascript side?


function whosfree($arguments){

	$db = "CourseMatcher";
    $servername = "localhost";
    $username = "root";
    $password = "";

    $conn = new mysqli($servername, $username, $password, $db);

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } 

    Chromephp::log($_SESSION['FULLNAME']); 

    $name_array = explode(' ', $_SESSION['FULLNAME']); 
    $firstname = $name_array[0];
    $lastname = $name_array[1];
    
    
    // need to grab the current users info... meaning we need the initial fb code at the top...
      $sql = "SELECT * FROM Users WHERE firstname = '$firstname' AND lastname = '$lastname'";

      $friends = '';
      $courses = ''; 
     $result = $conn->query($sql);
     if ($result->num_rows > 0) {
    	while($row = $result->fetch_assoc()) {
        	$friends = $row['friends'];
        	$courses = $row["Courses"];
    	}
	}

	$friends = explode(', ', $friends);
	
	$conflicting_friends = array();
	for ($y = 0; $y < count($friends); $y++){

		$current_friend_name = $friends[$y];
		// for this friend find his or here schedule.	
		$name_array1 = explode(' ', $current_friend_name);
		$firstname = $name_array1[0];
		$lastname = $name_array1[1];
		$sql = "SELECT Courses FROM Users WHERE firstname = '$firstname' AND lastname = '$lastname'";

		$result = $conn->query($sql);		
		$daCourse = $result->fetch_assoc();
		$daCourse = $daCourse['Courses'];
		$daCourse  = str_replace('[', '', $daCourse);
		$daCourse = str_replace(']', '', $daCourse);
		
		$current_friend_courses = array();	

		$daCourse1 = explode('}', $daCourse);

		// this function populates the array of courses for each friend.
		for($b = 0; $b < count($daCourse1); $b++){ 
			if($daCourse1[$b] == ''){
				continue;
			}
			if(substr($daCourse1[$b], 0, 1) == ','){
				
				$daCourse2 = substr($daCourse1[$b], 1, strlen($daCourse1[$b]));
			
				$daCourse2 = $daCourse2.'}';
			}else{
				$daCourse2 = $daCourse1[$b].'}';
			} 

			array_push($current_friend_courses, $daCourse2);	
		}
//		Chromephp::log($current_friend_courses);
		//Chromephp::log(count($current_friend_courses));
		for($l = 0; $l < count($current_friend_courses); $l++){

		$thecourse = json_decode($current_friend_courses[$l]);
		$starttime = $thecourse->currentstart;
		$endtime = $thecourse->currentend;
		$day = $thecourse->currentday;
		
		$array_of_time = array();
		
			$minute;
			$hour;
			$added_time = $starttime;
		while($added_time != $endtime){
				$split_time = explode(":", $added_time);
				$minute = $split_time[1];

				$hour = $split_time[0];
			if ($minute == "00"){
				$minute = "30";
			}else{
				$minute = "00";
				$hour = intval($hour) + 1;
				$hour = strval($hour);
			}			
			
			$added_time = $hour.':'.$minute;
			array_push($array_of_time, $added_time);
		}

		//Chromephp::log($array_of_time);

		$dayoncalendar;
		// first check the day here... 
		if($arguments[1] == 0){
			$dayoncalendar = 'Sun';
		}
		if($arguments[1] == 1){
			$dayoncalendar = 'Mon';
		}
		if($arguments[1] == 2){
			$dayoncalendar = 'Tue';
		}
		if($arguments[1] == 3){
			$dayoncalendar = 'Wed';
		}
		if($arguments[1] == 4){
			$dayoncalendar = 'Thu';
		}
		if($arguments[1] == 5){
			$dayoncalendar = 'Fri';
		}
		if($arguments[1] == 6){
			$dayoncalendar = 'Sat';
		}
		if ($dayoncalendar == $day){
		for ($g = 0; $g < count($arguments); $g++){
			//Chromephp::log($arguments[$g]); 
			for($k = 0; $k < count($array_of_time); $k++){
				if($array_of_time[$k] == $arguments[$g]){
					array_push($conflicting_friends, $current_friend_name);
					break 3;
				}
			}
		}
	}
	}
}

	
	Chromephp::log($conflicting_friends);
	//$conflicting = json_encode($conflicting_friends);
	return $conflicting_friends;

	// i guess we dont need to return anything here as we can display the table in our own way with our own scripts on 
	// this page...

}


?>