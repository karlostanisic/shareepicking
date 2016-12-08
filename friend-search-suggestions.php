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
    if (isset($_POST['searchFrase'])) {
        $searchFrase = sanitizeString($_POST['searchFrase']);
        if ($searchFrase !== "") {
            $users = User::findUsers($searchFrase, 0, 5);
            $result = array();
            foreach ($users as $user) {
                $result[] = array(
                    'userID' => $user->userID,
                    'userName' => $user->userName,
                    'name' => $user->name,
                    'surname' => $user->surname
                );
            }
            header('Content-Type: application/json');
            echo json_encode($result);
        }
    }
}