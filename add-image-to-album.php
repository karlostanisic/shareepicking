<?php
session_start();

require_once './includes/functions.php';
require_once './includes/class.album.php';
require_once './includes/class.user.php';

if (isset($_SESSION['loggedUserID'])) {
    $loggedUser = checkIfUserIsLogged($_SESSION['loggedUserID']); 
} else {
    $loggedUser = FALSE;
}

if ($loggedUser) {
    if (isset($_POST['albumID'])) {
        $albumID = sanitizeString($_POST['albumID']);

        if ($albumID !== "") {
            $album = new Album();
            if ($albumID == 0) {
                $newAlbumName = sanitizeString($_POST['newAlbumName']);
                if ($newAlbumName == "") {
                    $newAlbumName = date('Y-m-d', time());
                }
                $album->setValues($loggedUser->userID, $newAlbumName, NULL);
                $album->create();
            } else {
                $album->select($albumID);
            }
            if ($album->userID == $loggedUser->userID) {
                if (isset($_POST['imageID'])) {
                    if (is_array($_POST['imageID'])) {
                        $imageIDs = $_POST['imageID'];
                    } else {
                        $imageIDs = array($_POST['imageID']);
                    }
                    foreach ($imageIDs as $imageID) {
                        $album->addImage($imageID);
                    }
                }
                header('Location: ' . "show-album.php?albumID=" . $albumID, true, ($permanent === true) ? 301 : 302);
                die();
            }
        }
    }
}




