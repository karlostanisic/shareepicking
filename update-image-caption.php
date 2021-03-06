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
        $imageID = sanitizeString($_POST['imageID']);
        $caption = sanitizeString($_POST['caption']);

        $image = new Image($imageID);
        if ($image->userID == $loggedUser->userID) {
            $image->caption = $caption;
            $image->update(); 
        }
    }
}
