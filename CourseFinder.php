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

            session_start();  // start the session so we can save stuff...

            if (!isset($_SESSION['PICTURE'])){

            FacebookSession::setDefaultApplication('857265011029343', '6895d874134fec6bfe666c55de5d4034'); 
            $helper = new FacebookRedirectLoginHelper('http://localhost/CourseMatch/CourseFinder.php');
 
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
                     //$name = explode(' ', $fbfullname);
    				 //$_SESSION['firstname'] = $name[0];
    				 //$_SESSION['lastname'] = $name[1];
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

		 ?>
	</div>

<ul class="nav nav-tabs">
  <li role="presentation" class="active"><a href="#">Course Browser</a></li>
  <li role="presentation"><a href="Profile.php">Profile</a></li>
  <li role="presentation"><a href="StudyGroup.php">Study Groups</a></li>
  <li role="presentation"><a href="WhosFree.php">Who's Free</a></li>
</ul>




<body>

<?php

// try to make this just a dropdown...


echo '<form action="CourseFinder.php">
  School: <select name="School">
          <option value="UBC">UBC</option>
          <option value="SFU">SFU</option>
           <option value="Langara">Langara</option>
          </select>
          <br> 
  Term: <select name="term">
				<option value="Term 1">Term 1</option>
				<option value="Term 2">Term 2</option>
				<option value="Term 1 & 2">Term 1&2</option>
		</select>
  				<br>
  Course Subject: <input type="text" name="CourseSubj" placeholder="eg. ENGL or EN*" required><br>
  Course Number: <input type="text" name="CourseNumb" placeholder="eg. 110 or 1*" required><br>
  <input type="submit" value="Search" >
</form>';

	
	if(isset($_GET['SaveCourses'])){
		// here we are going to update the profile db!!
		// the profile page is going to be the page that finds your matches...
  		//Chromephp::log($_GET['cart_stuff']);
  		$_SESSION['cart_stuff'] = $_GET['cart_stuff'];
  		Save_Courses($_SESSION['FBID'], $_SESSION['FULLNAME'], $_SESSION['EMAIL'], $_SESSION['FRIENDS']);
	}


// saves the courses to user.
function Save_Courses($fbid, $fbfullname, $femail, $friendstring){

	$db = "CourseMatcher";
    $servername = "localhost";
    $username = "root";
    $password = "";
    $update_string = '';
    $conn = new mysqli($servername, $username, $password, $db);
     // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } 
    $name = explode(' ', $fbfullname);
    $firstname = $name[0];
    $lastname = $name[1];
    $sql = "SELECT Courses FROM Users WHERE firstname= '$firstname' AND lastname= '$lastname'";
     $result = $conn->query($sql);
     
      if ($result->num_rows > 0) { 	
      	while($row = $result->fetch_assoc()) {
      		 $user_courses = $row['Courses'];  
          	$course_object = json_decode($user_courses); // these are the courses that are retrieved from the server.
          	$cart = $_SESSION['cart_stuff'];
          	$cart = json_decode($cart);
          	if(is_null($course_object)){ 
          		$update_string = json_encode($cart);
          	}else{
          		for ($k = 0; $k < count($cart); $k++){
          			array_push($course_object, $cart[$k]);
          		}
          	$update_string = json_encode($course_object);
          }
      	}
      }

      $sql = " UPDATE Users
      SET courses='$update_string'
      WHERE email='$femail'";

      if ($conn->query($sql) === TRUE) {
        Chromephp::log("we have updated successfully");
        } else {
        Chromephp::log( "Error Updating " . $conn->error);
      }
	
      $_SESSION['cart'] =[];
	     mysqli_close($conn);
}


	if(isset($_GET['delete'])){
	 	$course = $_GET['course1'];
 		$section = $_GET['section1'];
 		$type = $_GET['type1'];
 		$day = $_GET['day1'];
 		$start = $_GET['start1'];
 		$end = $_GET['end1'];
 		 Course_Cart($course, $section, $type, $day, $start, $end, 'delete');
 	}


 if(isset($_GET['add'])){
 	$course = $_GET['course'];
 	$section = $_GET['section'];
 	$type = $_GET['type'];
 	$day = $_GET['day'];
 	$start = $_GET['start'];
 	$end = $_GET['end'];
     Course_Cart($course, $section, $type, $day, $start, $end, 'add');
 }


Course_Cart_Displayer(); 

  // lets set the session for the school that he is searching for right now...
  if(isset($_GET['School'])){
    $_SESSION['School'] = $_GET['School'];
    }

// if the courses have already been chosen... when we reload the page.
if(isset($_SESSION['CourseSubj'])|| isset($_SESSION['CourseNumb']) || isset($_SESSION['term']))
{

	if (isset($_GET['CourseSubj']) || isset($_GET['CourseNumb']) || isset($_GET['term'])) {
		$_SESSION['CourseSubj'] = $_GET['CourseSubj'];  // reset the session variables...
		$_SESSION['CourseNumb'] = $_GET['CourseNumb'];
		$_SESSION['term'] = $_GET['term'];
	}		

	$course = $_SESSION['CourseSubj'];
 	$numb = $_SESSION['CourseNumb'];
 	$term = $_SESSION['term'];
 	
 	// save this to the session...
  if($_SESSION['School'] == 'UBC'){
    find_courses_and_display($course, $numb, $term);
  }
  if($_SESSION['School'] == 'SFU'){
    // this is going to be for the SFU course webpage... 
    find_courses_and_display_SFU($course, $numb, $term);
  }

}else{

 if (isset($_GET['CourseSubj']) || isset($_GET['CourseNumb']) || isset($_GET['term'])) {

 	$term = $_GET['term'];
 	$course = $_GET['CourseSubj'];
 	$numb = $_GET['CourseNumb'];

 	$_SESSION["CourseSubj"] = $course; 
 	$_SESSION["CourseNumb"] = $numb; 
 	$_SESSION['term'] = $term;


  // this function is only going to run with UBC courses...
  if($_SESSION['School'] == 'UBC'){
    find_courses_and_display($course, $numb, $term);
  }
  if($_SESSION['School'] == 'SFU'){
    find_courses_and_display_SFU($course, $numb, $term);
  }
 	
 	
 }
}



// this function is going to find and display the SFU courses.
// may have to submit a bunch of the hidden data on the webpage!!

// this mght be the reason why this webpage just simply isnt returning anything!!...
/*<div id="win0divPSHIDDENFIELDS" style="display:none"><input type="hidden" name="ICType" id="ICType" value="Panel">
<input type="hidden" name="ICElementNum" id="ICElementNum" value="0">
<input type="hidden" name="ICStateNum" id="ICStateNum" value="46">
<input type="hidden" name="ICAction" id="ICAction" value="None">
<input type="hidden" name="ICXPos" id="ICXPos" value="0">
<input type="hidden" name="ICYPos" id="ICYPos" value="0">
<input type="hidden" name="ResponsetoDiffFrame" id="ResponsetoDiffFrame" value="-1">
<input type="hidden" name="TargetFrameName" id="TargetFrameName" value="None">
<input type="hidden" name="GSrchRaUrl" id="GSrchRaUrl" value="None">
<input type="hidden" name="FacetPath" id="FacetPath" value="None">
<input type="hidden" name="ICFocus" id="ICFocus" value="">
<input type="hidden" name="ICSaveWarningFilter" id="ICSaveWarningFilter" value="0">
<input type="hidden" name="ICChanged" id="ICChanged" value="-1">
<input type="hidden" name="ICResubmit" id="ICResubmit" value="0">
<input type="hidden" name="ICSID" id="ICSID" value="kTlAcA+5RVTRC8aVeWfVOZn6WoA7GfHnEmjV72xkdDw=">
<input type="hidden" name="ICActionPrompt" id="ICActionPrompt" value="false">
<input type="hidden" name="ICTypeAheadID" id="ICTypeAheadID" value="">
<input type="hidden" name="ICFind" id="ICFind" value="">
<input type="hidden" name="ICAddCount" id="ICAddCount" value="">
</div>*/

function find_courses_and_display_SFU($CourseSubj, $CourseNumb, $term){
  Chromephp::log("inside the SFU course searcher right now!!");
  $userAgent=$_SERVER['HTTP_USER_AGENT'];
  try{
  $url3 = 'https://www.sfu.ca/students/enrollment/class-search.html';
  $url4 = 'https://go.sfu.ca/psp/paprd/EMPLOYEE/EMPL/h/?tab=PAPP_GUEST';
  $url = 'http://go.sfu.ca';
  $url1 = 'https://go.sfu.ca/paprd/signon.html';
  $url2 = "../psp/paprd/?cmd=login'";
  $ch = curl_init();
     
  if($ch === false)
    {
    die('Failed to create curl object');
    }
 
  curl_setopt ($ch, CURLOPT_URL, $url2);
  curl_setopt ($ch, CURLOPT_POST, true);
 
  
  
  // Chromephp::log($post_data);
  
  
  //curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
    //$params=array(
    
  // );
  // curl_setopt($ch, CURLOPT_POST, TRUE);
  //curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
  curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
  curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
  //curl_setopt($ch, CURLOPT_USERPWD, 'username:password');
  curl_setopt($ch, CURLOPT_USERAGENT, "booyah!");

  $output = curl_exec($ch);

  if(FALSE === $output)
    throw new Exception(curl_error($ch), curl_errno($ch));
//  curl_error($ch);
  curl_close ($ch);

  // we can open a new webpage then grab the information from that webpage and then try and navigate through it.
  //echo $output;

  // couple options I can do here 
  // 1.) try and make another window where we navigate through to the course search page
  // 2.) hold off on the chromephp echo and wait maybe it will redirect and display correctly... 
  // 3.) just try and use the course search page instead... 



  // we can redirect the webpage back to localhost while getting all the information on the page? 

//  header('Location: http://localhost/CourseMatch/CourseFinder.php?School=SFU&term=Term+1&CourseSubj=asdf&CourseNumb=sadf');


  Chromephp::log("before the output");
  sleep(1);
  Chromephp::log($output);
  // we need to search through the output here and go to the link that it redirects too...







  Chromephp::log("after the output");
}catch(Exception $e){
  Chromephp::log("exception was thrown!!");
  trigger_error(sprintf(
        'Curl failed with error #%d: %s',
        $e->getCode(), $e->getMessage()),
        E_USER_ERROR);


}

  // win0 is the form name!!
  // this is going to be the subject name.
  // name="SSR_CLSRCH_WRK_SUBJECT_SRCH$0" id="SSR_CLSRCH_WRK_SUBJECT_SRCH$0" 

  // <input type="text" name="SSR_CLSRCH_WRK_CATALOG_NBR$1" id="SSR_CLSRCH_WRK_CATALOG_NBR$1"
  //name="CLASS_SRCH_WRK2_STRM$45$" id="CLASS_SRCH_WRK2_STRM$45$"
  //<select name="SSR_CLSRCH_WRK_ACAD_CAREER$2" id="SSR_CLSRCH_WRK_ACAD_CAREER$2"  this is the type of classes we are searching.
  //<a name="CLASS_SRCH_WRK2_SSR_PB_CLASS_SRCH" id="CLASS_SRCH_WRK2_SSR_PB_CLASS_SRCH" ptlinktgt="pt_peoplecode" tabindex="1035" href="javascript:submitAction_win0(document.win0,'CLASS_SRCH_WRK2_SSR_PB_CLASS_SRCH');" class="SSSBUTTON_CONFIRMLINK">Search</a>
  // this is going to be the submit button post data i believe.
}




// this function is going to find the courses and display them...
function find_courses_and_display($CourseSubj, $CourseNumb, $term){


$url = 'https://courses.students.ubc.ca/cs/main?pname=subjarea&tname=sectsearch';
 
$ch = curl_init();
     
if($ch === false)
{
    die('Failed to create curl object');
}
 
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_POST, true);
 
$post_data = "subj=$CourseSubj&crsno=$CourseNumb&term=$term";

curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
 
$output = curl_exec($ch);
curl_close ($ch);

//echo $output;
// we need to check if we have this line in the output.
if(strpos($output,'<A NAME="search_results"></A>') == false){
  // this is if the ubc site returns no results... 
	echo "<div float:right>
			<p align='center' style='color:red' >No Courses Found!</p>
			<p align='center' style='color:red' >Make sure you entered the right course subject and course number!</p>
		</div>";
}else{
  // if the ubc site returns the correct results..
	echo "<p align='center' style='color:blue; font-size: 200%'>Search Results</p>";
	results_returned($output, $CourseSubj, $CourseNumb, $term);

}
}




// handles the html page when search results are returned!!.
function results_returned($output, $CourseSubj, $CourseNumb, $term){
	
$outputSplit = explode('<A NAME="search_results"></A>', $output);

$outputSplit = explode('<!-- end of Main Content -->', $outputSplit[1]);

$outputSplit = explode('<tr', $outputSplit[0]);

// create one of these everytime and push into courses...
$Courses = [];

for($x = 2; $x < count($outputSplit); $x++){

	//Chromephp::log($outputSplit[$x]);
	$outputSplit1 = explode("$CourseSubj", $outputSplit[$x]); // this takes away the CPSC part...
	
	$outputSplit2 = substr($outputSplit1[2], 5);	
	

	$section = substr($outputSplit2, 0, 3); // this gets the section... 
	$type = '';
	$day = '';
	$start = '';
	$end = '';
	
	$outputSplit3 = explode('<td>', $outputSplit2);

	for ($r = 0; $r < count($outputSplit3); $r++){

		// check for the type...
		if(strpos($outputSplit3[$r],'Laboratory') !== false){
			// if it does then change the type... 
			$type = 'Laboratory';
		}
		if(strpos($outputSplit3[$r],'Discussion') !== false){
			// if it does then change the type... 
			$type = 'Discussion';
		}
		if(strpos($outputSplit3[$r],'Lecture') !== false){
			// if it does then change the type... 
			$type = 'Lecture';
		}
		if(strpos($outputSplit3[$r],'Waiting List') !== false){
			// if it does then change the type... 
			$type = 'Waiting List';
		}
		if(strpos($outputSplit3[$r],'Tutorial') !== false){
			// if it does then change the type... 
			$type = 'Tutorial';
		}

		//---->> this finds the correct day...
		if(strpos($outputSplit3[$r],'Mon') !== false){
			if ($day == ''){
				$day = 'Mon';
			}else{
				$day = $day. ' Mon';
			}
		}
		if(strpos($outputSplit3[$r],'Tue') !== false){
			if ($day == ''){
				$day = 'Tue';
			}else{
				$day = $day. ' Tue';
			}
		}
		if(strpos($outputSplit3[$r],'Wed') !== false){
			if ($day == ''){
				$day = 'Wed';
			}else{
				$day = $day. ' Wed';
			}
		}
		if(strpos($outputSplit3[$r],'Thu') !== false){
			if ($day == ''){
				$day = 'Thu';
			}else{
				$day = $day.' Thu';
			}
		}
		if(strpos($outputSplit3[$r],'Fri') !== false){
			if ($day == ''){
				$day = 'Fri';
			}else{
				$day = $day. ' Fri';
			}		
		}

		//---->>> we need to find the correct time now... check if there is a colon... 
		if(strpos($outputSplit3[$r],':') !== false){
			if($start == ''){

			$start = substr($outputSplit3[$r],0,5);
			if (!is_numeric(substr($start, -1)))
			{	
				$start = substr($start, 0, -1);
			}
		}else{
			$end = substr($outputSplit3[$r],0,5);
			if (!is_numeric(substr($end, -1)))
			{
				$start = substr($end, 0 , -1);
			}
		}
		}
	}

	$CourseParts = array (
    "Section"  => $section,	
    "Type" => $type,  
    "Day"   => $day, 
    "Start" => $start, 
    "End" => $end
	);


	array_push($Courses, $CourseParts); 

}

CourseDisplayer($Courses, $CourseSubj, $CourseNumb);
}


function CourseDisplayer($courses, $coursesubj, $coursenumb){

		echo "<table border='1' style='width:100%''>";
		echo "<tr>
				<td>Course</td>
				<td>Section</td>
    			<td>Type</td> 
    			<td>Day</td>
    			<td>Start Time</td>
    			<td>End Time</td>
  			</tr> ";
	for($x = 0; $x < count($courses); $x++){
		$currentsection = $courses[$x]['Section'];
		$currenttype = $courses[$x]['Type'];
		$currentday = $courses[$x]['Day'];
		$currentstart = $courses[$x]['Start'];
		$currentend = $courses[$x]['End'];
		$Course = $coursesubj.$coursenumb;
		// we also need to add a button to each row here... and this will add the course to the persons course cart...
		// have the button add the row to the added column...
		echo "<tr> 
				<td>$Course</td>
    			<td>$currentsection</td> 
    			<td>$currenttype</td>
    			<td>$currentday</td>
    			<td>$currentstart</td>
    			<td>$currentend</td>
    			<td> 
    				<form action= 'CourseFinder.php'>
    				<input type='hidden' name='course' value =$Course />
    				<input type='hidden' name='section' value =$currentsection />
    				<input type='hidden' name='type' value =$currenttype />
    				<input type='hidden' name='day' value =$currentday />
    				<input type='hidden' name='start' value =$currentstart />
    				<input type='hidden' name='end' value =$currentend />
    				<input type='submit' name = 'add' value='Add'></button> 
    				</form>
    					
    			</td>  
  			</tr>" ;    
	}
		echo "</table>";

}




function course_duplicate_checker($course, $section, $type, $day, $start, $end){

	$db = "CourseMatcher";
    $servername = "localhost";
    $username = "root";
    $password = "";

    $conn = new mysqli($servername, $username, $password, $db);

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } 

	
	$fullname = $_SESSION['FULLNAME'];
    $name = explode(' ', $fullname);
    $firstname = $name[0];
    $lastname = $name[1];
     $sql = "SELECT Courses FROM Users WHERE firstname= '$firstname' AND lastname= '$lastname'";
     $result = $conn->query($sql);
     
      if ($result->num_rows > 0) { 	
      	while($row = $result->fetch_assoc()) {
      		 $user_courses = $row['Courses'];  
      		
          	$course_object = json_decode($user_courses);
          	
          	// lets loop through this course object and makesure that we have no duplicates here... 

          	for($f = 0; $f < count($course_object); $f++){
        		
          		// we have to check everyone of these now...
          		if(
        	  	$course_object[$f]->course == $course &&
          		$course_object[$f]->type == $type &&	
          		$course_object[$f]->currentday == $day &&
          		$course_object[$f]->currentstart == $start &&
          		$course_object[$f]->currentend == $end
          		){

          			echo '<script language="javascript">';
					echo 'alert("You already have this course saved to your profile!")';
					echo '</script>';
          			return false;
          		}
          	}	
      	}
      }
		
	mysqli_close($conn);

	$cart = $_SESSION['cart'];
	for($x = 0; $x < count($cart); $x++){

			if($cart[$x]['course'] == $course && 
				$cart[$x]['section'] == $section &&
				$cart[$x]['type'] == $type &&
				$cart[$x]['currentday'] == $day &&
				$cart[$x]['currentstart'] == $start &&
				$cart[$x]['currentend'] == $end){
				echo '<script language="javascript">';
				echo 'alert("You cannot add this course twice to the list")';
				echo '</script>';
				return false;
			}
	}
	return true;

}



// this function adds or deletes the course from the cart...
function Course_Cart($Course, $currentsection, $currenttype, $currentday, $currentstart, $currentend, $option){

	if(isset($_SESSION["cart"])){

		$cart = $_SESSION["cart"];
		// then what we do is just add onto the cart... 
		$course_info_array = array(
				'course' => $Course,
				 'section' => $currentsection,
				 'type' => $currenttype,
				 'currentday' => $currentday,
				 'currentstart' => $currentstart,
				 'currentend' => $currentend,
				); 

		if ($option == 'add'){

			$result = course_duplicate_checker($Course, $currentsection, $currenttype, $currentday, $currentstart, $currentend);	
			if($result){	
				array_push($cart, $course_info_array);
			}	
		}

		
		// we need to reset the session variable...
		if ($option == 'delete'){
			for($t = 0; $t < count($cart); $t++){
				
				if($cart[$t]['section'] == $currentsection &&
					$cart[$t]['course'] == $Course &&
					$cart[$t]['type'] == $currenttype &&
					$cart[$t]['currentday'] == $currentday &&
					$cart[$t]['currentstart'] == $currentstart &&
					$cart[$t]['currentend'] == $currentend
					){
						unset($cart[$t]);
						$cart = array_values($cart);
					
				}
			}

			}

		$_SESSION["cart"] = $cart;

		}else{
			
			$cart = [];
			$course_info_array = array(
				'course' => $Course,
				 'section' => $currentsection,
				 'type' => $currenttype,
				 'currentday' => $currentday,
				 'currentstart' => $currentstart,
				 'currentend' => $currentend,
				); 

			if ($option == 'add'){
				array_push($cart, $course_info_array);
				}

			if ($option == 'delete'){
				array_push($cart, $course_info_array);
				}

				
				$_SESSION["cart"] = $cart;

		}
}


// this displays the course displayer...
function Course_Cart_Displayer(){


	echo "<p align= 'center' style='font-size: 200%; color:blue'>Courses Selected</p>";

	if(isset($_SESSION['cart'])){
	$cart = $_SESSION['cart'];
	}else{
		$cart = [];
	}			
	//Chromephp::log($cart);

	echo "<table border='1' style='width:100%''>";

	echo "<tr>
				<td>Course</td>
				<td>Section</td>
    			<td>Type</td> 
    			<td>Day</td>
    			<td>Start Time</td>
    			<td>End Time</td>
  			</tr> ";

  	

  	if(count($cart) == 0){
  		//Chromephp::log("there are no courses selected");	
  	}
  		
	for($x = 0; $x < count($cart); $x++){

		//Chromephp::log($x);	
		//Chromephp::log($cart[$x]);
		if(is_null($cart[$x])){

			continue;
		}
		$currentCourse = $cart[$x]; 
		//Chromephp::log($currentCourse['course']);
		$Course1 = $currentCourse['course'];
		$currentsection1 = $currentCourse['section'];
		$currenttype1 = $currentCourse['type'];
		$currentday1 = $currentCourse['currentday'];
		$currentstart1 = $currentCourse['currentstart'];
		$currentend1 = $currentCourse['currentend'];
		

		
		// we need to create a delete button here...
		echo "<tr> 
				<td>$Course1</td>
    			<td>$currentsection1</td> 
    			<td>$currenttype1</td>
    			<td>$currentday1</td>
    			<td>$currentstart1</td>
    			<td>$currentend1</td>
    			<td> 
    				<form action= 'CourseFinder.php'>
    				<input type='hidden' name='course1' value =$Course1 />
    				<input type='hidden' name='section1' value =$currentsection1 />
    				<input type='hidden' name='type1' value =$currenttype1 />
    				<input type='hidden' name='day1' value =$currentday1 />
    				<input type='hidden' name='start1' value =$currentstart1 />
    				<input type='hidden' name='end1' value =$currentend1 />
    				<input type='submit' name = 'delete' value='Delete'></button> 
    				</form>
    			</td>  
  			</tr>" ;   
	}

	echo "</table>";


	$string_cart = json_encode($cart);

	echo "<form action= 'CourseFinder.php'>
    			<input type='hidden' name='cart_stuff' value ='$string_cart' />
				<input type='submit' name = 'SaveCourses' value='Save'></button>
    		</form>";


}



?>


</body>

</html>