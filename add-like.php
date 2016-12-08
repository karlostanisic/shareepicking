<?php
session_start();

require_once './includes/functions.php';
require_once './includes/class.image.php';

if (isset($_SESSION['loggedUserID'])) {
    $loggedUser = checkIfUserIsLogged($_SESSION['loggedUserID']); 
} else {
    $loggedUser = FALSE;
}

if ($loggedUser) {
    if (isset($_POST['imageID'])) {
        $image = new Image(sanitizeString($_POST['imageID']));
        if ($loggedUser->isFriend($image->userID)) {
            $image->addLike($loggedUser->userID);
            echo count($image->getAllLikes());
//            echo $loggedUser->userID;
        }   
    }
}



