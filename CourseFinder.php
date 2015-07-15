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
            // start here by checking the facebook login information...


// if we have the session then we dont have to go through this again... 
            if (!isset($_SESSION['PICTURE'])){

            FacebookSession::setDefaultApplication('857265011029343', '6895d874134fec6bfe666c55de5d4034'); 
            $helper = new FacebookRedirectLoginHelper('http://localhost/CourseMatch/CourseFinder.php');
            // use this facebook login and just display the users name and hopefully profile picture...

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

/*
<input list="browsers" name="browser">
<datalist id="browsers">
  <option value="Internet Explorer">
  <option value="Firefox">
  <option value="Chrome">
  <option value="Opera">
  <option value="Safari">
</datalist>*/

echo '<form action="CourseFinder.php">
  Term: <input list="term" name="term">
  			<datalist id="term">
  				<option value="Term 1">Term 1</option>
  				<option value="Term 2">Term 2</option>
  				<option value="Term 1-2">Term 1 & 2</option>
				</datalist>
  				<br>
  Course Subject: <input type="text" name="CourseSubj"><br>
  Course Number: <input type="text" name="CourseNumb"><br>
  <input type="submit" value="Search" >
</form>';
	


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
 	Chromephp::log("inside the add function");	
 	$course = $_GET['course'];
 	$section = $_GET['section'];
 	$type = $_GET['type'];
 	$day = $_GET['day'];
 	$start = $_GET['start'];
 	$end = $_GET['end'];

     Course_Cart($course, $section, $type, $day, $start, $end, 'add');
 }
 

// if session variables are set and the button was pressed...

if(isset($_SESSION['CourseSubj'])|| isset($_SESSION['CourseNumb']) || isset($_SESSION['term']))
{
	//Chromephp::log("the session variables has been set");
	if (isset($_GET['CourseSubj']) || isset($_GET['CourseNumb']) || isset($_GET['term'])) {
		$_SESSION['CourseSubj'] = $_GET['CourseSubj'];  // reset the session variables...
		$_SESSION['CourseNumb'] = $_GET['CourseNumb'];
		$_SESSION['term'] = $_GET['term'];
	}		

	$course = $_SESSION['CourseSubj'];
 	$numb = $_SESSION['CourseNumb'];
 	$term = $_SESSION['term'];
 	//Chromephp::log($course);
 	//Chromephp::log($numb);

 	// save this to the session...
 	find_courses_and_display($course, $numb, $term);
}else{
// only one of these needs to run...

	// check if the term got set here...

 if (isset($_GET['CourseSubj']) || isset($_GET['CourseNumb']) || isset($_GET['term'])) {
	
	//Chromephp::log("wow");
 	//Chromephp::log($_GET['CourseSubj']);
 	//Chromephp::log($_GET['CourseNumb']); 
 	// save the courses to the session and we can just render them again...
 	$term = $_GET['term'];
 	$course = $_GET['CourseSubj'];
 	$numb = $_GET['CourseNumb'];

 	$_SESSION["CourseSubj"] = $course; 
 	$_SESSION["CourseNumb"] = $numb; 
 	$_SESSION['term'] = $term;
 	find_courses_and_display($course, $numb, $term);
 	
 }
}

// this function is going to find the courses and display them...
function find_courses_and_display($CourseSubj, $CourseNumb, $term){


// term is not working right now because we might be posting the wrong type...
Chromephp::log($term);



$url = 'https://courses.students.ubc.ca/cs/main?pname=subjarea&tname=sectsearch';
 
$ch = curl_init();
     
if($ch === false)
{
    die('Failed to create curl object');
}
 
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_POST, true);
 
// The submitted form data, encoded as query-string-style name-value pairs

// this is the data that we need to post... 
//Chromephp::log($CourseSubj);
//Chromephp::log($CourseNumb);
$post_data = "subj=$CourseSubj&crsno=$CourseNumb&term=$term";
//$post_data = "subj=CPSC&crsno=304";
curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
 
$output = curl_exec($ch);
curl_close ($ch);

//echo $output;

// we have to go through the entire output html to find the courses that actually need to be taken... 


// we need to place this in the loop....
$outputSplit = explode('<A NAME="search_results"></A>', $output);

//Chromephp::log($outputSplit[1]);

$outputSplit = explode('<!-- end of Main Content -->', $outputSplit[1]);

//Chromephp::log($outputSplit[0]);
$outputSplit = explode('<tr', $outputSplit[0]);

// create one of these everytime and push into courses...
$Courses = [];

for($x = 2; $x < count($outputSplit); $x++){

	//Chromephp::log($outputSplit[$x]);
	$outputSplit1 = explode("$CourseSubj", $outputSplit[$x]); // this takes away the CPSC part...
	
	// now substring by three to get the section number... 
	$outputSplit2 = substr($outputSplit1[2], 5);	// this is going to get rid of the course number... 
	//Chromephp::log($outputSplit2);
	// grab the section number first and then spit it up by <td>.... 

	$section = substr($outputSplit2, 0, 3); // this gets the section... 
	$type = '';
	$day = '';
	$start = '';
	$end = '';
	// 
	$outputSplit3 = explode('<td>', $outputSplit2);

	// we need to go through outputSplit 3 ... 

	//Chromephp::log($outputSplit3);

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

	//Chromephp::log($day);
	//Chromephp::log($start);
	//Chromephp::log($end);
	$CourseParts = array (
    "Section"  => $section,	// this will give the actual number 101, 102, l1b etc...
    "Type" => $type,  // this will describe whether this is a lecture, lab, or waiting list.
    "Day"   => $day, 
    "Start" => $start, // this will hold the time at which this course is being held...
    "End" => $end
	);

	//Chromephp::log($CourseParts);
	array_push($Courses, $CourseParts); 
//	Chromephp::log($Courses);	 
}

// now we need to call something that will display this info for us... 
CourseDisplayer($Courses, $CourseSubj, $CourseNumb);

}

// this should maybe be javascript later on... but for now use php...
function CourseDisplayer($courses, $coursesubj, $coursenumb){


	//Chromephp::log($courses);

	// we have to loop through all the courses... 
		echo "<table border='1' style='width:100%''>";
		// first lets creat the criteria rows first... 
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


// this is going to be the courses...
// we need to save the session variables
// one variable that says either it is a delete or it is an add...
function Course_Cart($Course, $currentsection, $currenttype, $currentday, $currentstart, $currentend, $option){
	// now lets show this... 
	
	echo "Courses Cart: ";

	if(isset($_SESSION["cart"])){
		// if it set then what we do is we add onto the cart

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
		array_push($cart, $course_info_array);
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

			
		//Chromephp::log($cart);

		$_SESSION["cart"] = $cart;

		Course_Cart_Displayer();


		}else{
			// if its not set then we save it as a session variable... 
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

				Course_Cart_Displayer();
		}
}


// this displays the course displayer...
function Course_Cart_Displayer(){

	$cart = $_SESSION['cart'];
	//Chromephp::log($cart);

	echo "<table>";

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

	echo "<form action= 'Profile.php'>
			<input type='hidden' name='cart' value ='$string_cart' />
			<input type='submit' name = 'SaveCourses' value='Save'></button>
			</form>";

}
/*
					<form action= 'CourseFinder.php'>
    				<input type='hidden' name='course' value =$Course />
    				<input type='hidden' name='section' value =$currentsection />
    				<input type='hidden' name='type' value =$currenttype />
    				<input type='hidden' name='day' value =$currentday />
    				<input type='hidden' name='start' value =$currentstart />
    				<input type='hidden' name='end' value =$currentend />
    				<input type='submit' name = 'delete' value='Add'></button> 
    				</form>*/

?>


</body>

</html>