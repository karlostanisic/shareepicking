<?php
session_start();

require_once './includes/config.php';
require_once './includes/functions.php';
require_once './includes/class.user.php';

if (isset($_SESSION['loggedUserID'])) {
    $loggedUser = checkIfUserIsLogged($_SESSION['loggedUserID']); 
} else {
    $loggedUser = FALSE;
}

if (!$loggedUser) {
    destroySession();
    die("You need to be logged to see this page. <a href='index.php'>Sign in</a>");
}

if (isset($_GET['userID'])) {
    $_SESSION['profileUserID'] = sanitizeString($_GET['userID']);
}

if (isset($_SESSION['profileUserID'])) {
    $profileUser = setProfileUser($_SESSION['profileUserID']);
} else {
    $profileUser = FALSE;
}

if (!$profileUser) {
    $profileUser = $loggedUser;
}

$loggedUserStatus = User::checkVisitorStatus($profileUser->userID, $loggedUser->userID);

$numberOfNewMessages = $loggedUser->numberOfNewMessages();
$numberOfNewFriendRequests = $loggedUser->numberOfNewFriendRequests();
$numberOfNewNotifications = $loggedUser->numberOfNewNotifications();

if ($loggedUserStatus !== "owner") {
    $numberOfAlbums = $profileUser->numberOfAlbums();
    $numberOfFriends = $profileUser->numberOfFriends();
}

$activeNavLink = "";

if (file_exists("images/users/$loggedUser->userID.jpg")) {
    $loggedUserImgPath = "images/users/$loggedUser->userID.jpg";
} else {
    $loggedUserImgPath = "images/users/dummy.jpg";
}
if (file_exists("images/users/$profileUser->userID.jpg")) {
    $profileUserImgPath = "images/users/$profileUser->userID.jpg";
} else {
    $profileUserImgPath = "images/users/dummy.jpg";
}