<?php
session_start();

require_once './includes/functions.php';
require_once './includes/class.album.php';

if (isset($_SESSION['loggedUserID'])) {
    $loggedUser = checkIfUserIsLogged($_SESSION['loggedUserID']); 
} else {
    $loggedUser = FALSE;
}

if ($loggedUser) {
    if (isset($_POST['albumID'])) {
        $albumID = sanitizeString($_POST['albumID']);
        $album = new Album();
        if ($album->select($albumID)) {
            if (isset($_POST['imageID'])) {
                $imageID = sanitizeString($_POST['imageID']);
                $album->removeImage($imageID);
            }
        }
    }
}

