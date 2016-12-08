<?php
session_start();

require_once './includes/functions.php';
require_once './includes/class.message.php';

if (isset($_SESSION['loggedUserID'])) {
    $loggedUser = checkIfUserIsLogged($_SESSION['loggedUserID']); 
} else {
    $loggedUser = FALSE;
}

if ($loggedUser) {
    foreach ($_POST as $key => $value) {
        $$key = sanitizeString($value);
    }
    $message = new Message($loggedUser->userID, $receiverID, $subject, $text);
    $message->send(); 
}

