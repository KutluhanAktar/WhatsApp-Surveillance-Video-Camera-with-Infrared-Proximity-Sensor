<?php
// Implement Twilio's API to send data to WhatsApp.
require_once $_SERVER['DOCUMENT_ROOT'].'/path/to/vendor/autoload.php'; 
 
use Twilio\Rest\Client; 

if(!empty($_FILES["rasp_video"]["name"]) && !empty($_FILES["rasp_capture"]["name"])){
	// Get the brightness value from Raspberry Pi.
	$brightness = (isset($_POST['brightness'])) ? $_POST['brightness'] : "Not Detected!";
	
	// Get properties of the uploaded files.
	// Video File:
	$video_properties = array(
	    "name" => $_FILES["rasp_video"]["name"],
	    "tmp_name" => $_FILES["rasp_video"]["tmp_name"],
		"size" => $_FILES["rasp_video"]["size"],
		"extension" => pathinfo($_FILES["rasp_video"]["name"], PATHINFO_EXTENSION)
	);
	// Image File:
	$capture_properties = array(
	    "name" => $_FILES["rasp_capture"]["name"],
	    "tmp_name" => $_FILES["rasp_capture"]["tmp_name"],
		"size" => $_FILES["rasp_capture"]["size"],
		"extension" => pathinfo($_FILES["rasp_capture"]["name"], PATHINFO_EXTENSION)
	);
	// Check whether the uploaded file extensions are in allowed formats.
	$allowed_formats = array('jpg', 'png', 'mp4');
	if(!in_array($video_properties["extension"], $allowed_formats) || !in_array($capture_properties["extension"], $allowed_formats)){
		echo 'SERVER RESPONSE:\r\nFILE => File Format Not Allowed!';
	}else{
		// Check whether the uploaded file sizes exceed the data limit - 5MB.
		if($video_properties["size"] > 5000000 || $capture_properties["size"] > 5000000){
			echo 'SERVER RESPONSE:\r\nFILE => File size cannot exceed 5MB.';
		}else{
		    $video_root = $video_properties["name"];
		    $capture_root = $capture_properties["name"];
			$media = "[_URL_]".$capture_properties["name"]; // e.g., https://www.theamplituhedron.com/dashboard/WhatsApp-Surveillance-Camera/
			$video = "[_URL_]".$video_properties["name"];   // e.g., https://www.theamplituhedron.com/dashboard/WhatsApp-Surveillance-Camera/
			
			// Upload files - video and capture image.
		    move_uploaded_file($video_properties["tmp_name"], $video_root);
		    move_uploaded_file($capture_properties["tmp_name"], $capture_root);
			
		    // Send the information of the uploaded files, including temp names, to Raspberry Pi as the response message.
		    echo "SERVER RESPONSE:\r\nFILE => Files Uploaded Successfully!\r\nTMP_VIDEO => ".$video_properties["tmp_name"]."\r\nVIDEO_SIZE => ".$video_properties["size"]."\r\nTMP_CAPTURE => ".$capture_properties["tmp_name"]."\r\nCAPTURE_SIZE => ".$capture_properties["size"]."\r\nDATA => Received: ".$brightness;
			
			// Send the recently uploaded files to WhatsApp - video and capture image.
			// Define the required properties by Twilio's API:
            // SID, AUTH_TOKEN, FROM_PHONE_NUMBER, TO_PHONE_NUMBER, MESSAGE BODY, and MEDIA FILE PATH(CAPTURE).
			// Note: Refrain from adding '+' at the beginning of the phone numbers when entering them as send_data_to_WhatsApp() function parameters.
			send_data_to_WhatsApp("[_SID_]", "[_AUTH_TOKEN_]", "[_FROM_PHONE_NUMBER_]", "[_TO_PHONE_NUMBER_]", "Intrusion Detected by Raspberry Pi 🏠 ☣\n\nBrightness => ".$brightness."\n\nVideo =>\n".$video, $media);
			echo "\r\nAPI => Files Send to WhatsApp!";
		}
	}

}else{
	echo "SERVER RESPONSE:\r\nFILE => No File Detected!";
}

function send_data_to_WhatsApp($sid, $token, $from, $to, $body, $media){
	$twilio = new Client($sid, $token);
    $message = $twilio->messages->create("whatsapp:+".$to, array("from" => "whatsapp:+".$from, "body" => $body, "mediaUrl" => array($media)));
}

?>