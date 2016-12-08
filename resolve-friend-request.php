<?php
session_start();

require_once './includes/functions.php';
require_once './includes/class.user.php';
require_once './includes/class.activity.php';

if (isset($_SESSION['loggedUserID'])) {
    $loggedUser = checkIfUserIsLogged($_SESSION['loggedUserID']); 
} else {
    $loggedUser = FALSE;
}

if ($loggedUser) {
    foreach ($_POST as $key => $value) {
        $$key = sanitizeString($value);
    }

    if ($accept == "true") {
        $accept = TRUE;
    } elseif ($accept == "false") {
        $accept = FALSE;
    } else {
        die();
    }

    $user = new User();
    $user->select($user1ID);
    echo $user->acceptFriend($user2ID, $accept);
    
    if ($accept) {
        $activity = new Activity(3, array($user1ID, $user2ID));
        $activity->create();
    }
}

