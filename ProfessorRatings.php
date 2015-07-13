<?php


// try to access ratemyprofessor
include 'ChromePhp.php';

$url = 'http://www.ratemyprofessors.com/';
 
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
$post_data = "professor-name=wolfman"; 
//$post_data = "subj=CPSC&crsno=304";
curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
 
$output = curl_exec($ch);
curl_close ($ch);


Chromephp::log($output);


?>