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
        $image = new Image($imageID);
        if (isset($_POST['text'])) {
            $text = sanitizeString($_POST['text']);
            $image->addComment($loggedUser->userID, $text);

            echo createCommentHTML($loggedUser->userID, $loggedUser->userName, $text, time());
        }
    }
}

