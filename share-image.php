<?php
session_start();

require_once './includes/functions.php';
//require_once './includes/class.message.php';
require_once './includes/class.image.php';
require_once './includes/class.activity.php';

if (isset($_SESSION['loggedUserID'])) {
    $loggedUser = checkIfUserIsLogged($_SESSION['loggedUserID']); 
} else {
    $loggedUser = FALSE;
}

if ($loggedUser) {
    if (isset($_POST['imageID'])) {
        $imageID = sanitizeString($_POST['imageID']);

        $image = new Image();
        if ($image->select($imageID)) {
            $activity = new Activity(1, array($loggedUser->userID, $image->imageID));
            $activity->create();
        }
    }

    
    
    
    
//    $subject = $loggedUser->userName . " shared a picture";
//    if ($image->caption == "") {
//        $text = "<a href='show-image.php?imageID=$image->imageID'>$image->name</a>";
//    } else {
//        $text = "<a href='show-image.php?imageID=$image->imageID'>$image->caption</a>";
//    }

//    $friends = $loggedUser->friends();
//    foreach ($friends as $friend) {
//        $message = new Message($loggedUser->userID, $friend->userID, $subject, $text);
//        $message->send();
//    }
}

