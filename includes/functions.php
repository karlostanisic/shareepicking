<?php
require_once './includes/class.user.php';

function queryMysql($query){
    global $connection;
//    echo $query . "<br>";
    $result = $connection->query($query);
    if (!$result) die($connection->error);
    return $result;
}

function destroySession() {
    $_SESSION = array();
    if (session_id() != "" || isset($_COOKIE[session_name()]))
        setcookie (session_name(), '', time() - 2592000, '/');
    session_destroy();
}

function sanitizeString($var) {
    $var = nl2br(htmlentities($var));
    return $var;
}

function generateRandomString($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $count = mb_strlen($chars);

    for ($i = 0, $result = ''; $i < $length; $i++) {
        $index = rand(0, $count - 1);
        $result .= mb_substr($chars, $index, 1);
    }

    return $result;
}

function insertUser($userName, $password, $name, $surname, $city, $birthDate) {
    if (checkUserName($userName) && $password !== "") {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        if (preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $birthDate, $matches)) {
            if (!checkdate($matches[2], $matches[1], $matches[3])) {
                $birthDate = "";
            } else {
                $birthDate = date("Y-m-d H:i:s",strtotime($birthDate));
            }
        } else {
            $birthDate = "";
        }
        queryMysql("INSERT INTO users (userName, password, name, surname, birthDate, city) "
            . "VALUES('$userName', '$hash', '$name', '$surname', '$birthDate', '$city')");
        return queryMysql("SELECT userID FROM users ORDER BY userID DESC LIMIT 1")->fetch_array(MYSQLI_ASSOC)['userID'];;
    }
    else {
        return FALSE;
    }
}

function checkUserName($userName) {
    $result = queryMysql("SELECT * FROM users WHERE userName='$userName'");
    return ($result->num_rows == 0);
}

function createCommentHTML ($userID, $userName, $text, $commentDate) {
    $res = "<article class='comment'>";
    
    if (file_exists("images/users/$userID.jpg")) $imgPath = "images/users/$userID.jpg";
    else $imgPath = "images/users/dummy.jpg";
    
    $res .= "<a href='profile.php?userID=$userID'><img src='" . $imgPath . "'></a>";
    $res .= "<div><p><span class='user-name'>$userName</span> ";
    $res .= date('Y/m/d h:m:s', intval($commentDate)) . "</p>";
    $res .= "<p>$text</p></div>";
    $res .= "</article>";
    return $res;
}

function checkAutorisation($status, $autorisation) {
    if ($autorisation == "owner" && $status !== "owner") {
        return FALSE;
    } elseif ($autorisation == "friend" && $status == "visitor") {
        return FALSE;
    } else {
        return TRUE;
    }
}

function checkIfUserIsLogged($sessionID) {
    if ($sessionID) {
        $loggedUser = new User();
        $visitorIsLogged = $loggedUser->select($sessionID);
        if ($visitorIsLogged) {
            return $loggedUser;
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}

function setProfileUser($sessionID) {
    if ($sessionID) {
        $profileUserID = $_SESSION['profileUserID'];
        $profileUser = new User();
        if ($profileUser->select($profileUserID)) {
            return $profileUser;
        } else {
            return FALSE;
        }
    }
}