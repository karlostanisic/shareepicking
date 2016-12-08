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
        $album->select($albumID);
        if ($album->userID == $loggedUser->userID) {
            $album->delete();
        }
    }
}
