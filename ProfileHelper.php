<?php

include 'ChromePhp.php';



// this is going to delete the course from the user db... then have it redirect back... 
// lets send the correct stuff through!!
if(isset($_GET['remove'])){
          $course = $_GET['course'];
          $section = $_GET['section'];
          $start = $_GET['start'];
          $end = $_GET['end'];
          $type = $_GET['type'];
          $firstname = $_GET['firstname'];
          $lastname = $_GET['lastname'];
          $day = $_GET['day'];
          // lets get rid of the "/"
          Chromephp::log($course);
          Chromephp::log($section);
          Chromephp::log($lastname);
          Chromephp::log($firstname);
          Chromephp::log($end);
          Chromephp::log($start);
          Chromephp::log($day);
          Chromephp::log($type);
         Chromephp::log("the delete button was pressed here!!");
          echo "this is being called";

          $firstname = explode('/', $firstname);
          $firstname = $firstname[0];

          $lastname = explode('/', $lastname);
          $lastname = $lastname[0];


          // lets delete this course from the user course list... 

          // first we are going to have to query the users courses...

          $db = "CourseMatcher";
   		 $servername = "localhost";
   		 $username = "root";
    	$password = "";

    	$conn = new mysqli($servername, $username, $password, $db);

		    // Check connection
		    if ($conn->connect_error) {
		      die("Connection failed: " . $conn->connect_error);
		    } 

		     $sql = "SELECT Courses FROM Users WHERE firstname= '$firstname' AND lastname= '$lastname'";
		     $result = $conn->query($sql);
		     //Chromephp::log($result);
		     // we need to go through the course json object here and delete the correcet stuff...
		     if ($result->num_rows > 0) { 	
      			while($row = $result->fetch_assoc()) {
      				 $user_courses = $row['Courses'];  
      		
          			$course_object = json_decode($user_courses);
          			//Chromephp::log($user_courses);
          			//Chromephp::log($course_object);

          			for ($r = 0; $r < count($course_object); $r++){
          				if ($course_object[$r]->course == $course &&	
          					$course_object[$r]->section == $section &&
          					$course_object[$r]->currentday == $day &&
          					$course_object[$r]->currentstart == $start &&
          					$course_object[$r]->currentend == $end &&
          					$course_object[$r]->type == $type 
          					){
          					unset($course_object[$r]);
          				}
          			}

          			//$array = json_decode(json_encode($course_object), false);
          			Chromephp::log($course_object);
          			Chromephp::log("this is the new and improved array");
          		
          			$correct_format_array = [];

          			for ($x = 0; $x <= count($course_object); $x++){
          				Chromephp::log($course_object[$x]);
          				if(!is_null($course_object[$x]))
          				array_push($correct_format_array, $course_object[$x]);
          			}

          			Chromephp::log($correct_format_array);


          			// lets just create new array here...
          			// just create a recursive function

          			// now that we have the correct array we have to encode and then save... 
          			$update_string = json_encode($correct_format_array);
          			 Chromephp::log($update_string);
          			 $sql = " UPDATE Users
     				SET courses='$update_string'
      				WHERE firstname= '$firstname' AND lastname= '$lastname'";

      				 if ($conn->query($sql) === TRUE) {
        					Chromephp::log("we have updated successfully");
       						 } else {
       						 Chromephp::log( "Error Updating " . $conn->error);
     						 }
      			}
      		}

         header('Location: Profile.php');
        }

?>