<?php
session_start();

require_once './includes/functions.php';
require_once './includes/class.user.php';

if (isset($_SESSION['loggedUserID'])) {
    $loggedUser = checkIfUserIsLogged($_SESSION['loggedUserID']); 
} else {
    $loggedUser = FALSE;
}

if ($loggedUser) {
    foreach ($_POST as $key => $value) {
        $$key = sanitizeString($value);
    }
    $loggedUser->sendFriendRequest($receiverID);
}
