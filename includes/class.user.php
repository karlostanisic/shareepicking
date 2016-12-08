<?php

require_once './includes/config.php';
require_once './includes/functions.php';

class User {
    static $db;
    
    public $userID;
    public $userName;
    public $name;
    public $surname;
    public $birthDate;
    public $city;
    public $signupDate;
    public $hash;
    public $password;
    public $lastMassagesCheck;
    public $lastFriendsCheck;
    public $lastNotificationsCheck;
    
    static function checkUserID($userID) {
        $stmt = self::$db->prepare("SELECT userID FROM users WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        if ($stmt->fetch()) {
            $stmt->close();
            return TRUE;
        } else {
            $stmt->close();
            return FALSE;
        }
    }
    
    static function checkVisitorStatus($ownerID, $visitorID) {
        if ($ownerID == $visitorID) {
            return "owner";
        } else {
            $owner = new User();
            $owner->select($ownerID);
            if ($owner->isFriend($visitorID)) {
                return "friend";
            } else {
                return "visitor";
            }
        }
    }
    
    static function checkUserName($userName) {
        $stmt = self::$db->prepare("SELECT userID FROM users WHERE userName = ?");
        $stmt->bind_param("s", $userName);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        if ($stmt->fetch()) {
            $stmt->close();
            return TRUE;
        } else {
            $stmt->close();
            return FALSE;
        }
    }
    
    static function makeFriends($user1ID, $user2ID) {
        if (self::checkUserID($user1ID) && self::checkUserID($user2ID)) {
            $user1 = new User();
            $user1->select($user1ID);
            if ($user1->isFriend($user2ID)) {
                return TRUE;
            } else {
                $stmt = self::$db->prepare("INSERT INTO friends (user1ID, user2ID) VALUES (?, ?)");
                $stmt->bind_param("ii", $user1ID, $user2ID);
                try {
                    $stmt->execute();
                } catch (Exception $ex) {
                    die($ex);
                }
                $stmt->close();
                return TRUE;
            }
        } else {
            return FALSE;
        }
    }
    
    static function countFindUsers($searchFrase) {
        if ($searchFrase == "") return 0;
        $searchFrase = explode(" ", $searchFrase);
        
        foreach ($searchFrase as &$frase) {
            $frase = "%" . sanitizeString($frase) . "%";
        }
        unset($frase);
        
        $stmtstr = "SELECT COUNT(userID) FROM users WHERE "
                . "(userName LIKE '$searchFrase[0]' "
                . "OR name LIKE '$searchFrase[0]' "
                . "OR surname LIKE '$searchFrase[0]')";
        for ($i = 1; $i < count($searchFrase); $i++) {
            $stmtstr .= " AND (userName LIKE '$searchFrase[$i]' "
                . "OR name LIKE '$searchFrase[$i]' "
                . "OR surname LIKE '$searchFrase[$i]')";
        }
        $stmt = self::$db->prepare($stmtstr);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
            die($ex);
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        return $res;
    }

    static function findUsers($searchFrase, $page = 0, $resultsPerPage = NULL) {
        if ($searchFrase == "") return [];
        $searchFrase = explode(" ", $searchFrase);
        
        foreach ($searchFrase as &$frase) {
            $frase = "%" . sanitizeString($frase) . "%";
        }
        unset($frase);
        
        if ($resultsPerPage) {
            $limit = intval($resultsPerPage);
            $offset = intval($page * $resultsPerPage);
        }
        
        $stmtstr = "SELECT userID FROM users WHERE "
                . "(userName LIKE '$searchFrase[0]' "
                . "OR name LIKE '$searchFrase[0]' "
                . "OR surname LIKE '$searchFrase[0]')";
        for ($i = 1; $i < count($searchFrase); $i++) {
            $stmtstr .= " AND (userName LIKE '$searchFrase[$i]' "
                . "OR name LIKE '$searchFrase[$i]' "
                . "OR surname LIKE '$searchFrase[$i]')";
        }
        if ($resultsPerPage) {
            $stmtstr .= " LIMIT $limit OFFSET $offset";
        }
        $stmt = self::$db->prepare($stmtstr);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $stmt->bind_result($userID);
        $userIDs = array();
        while($stmt->fetch()) {
            $userIDs[] = $userID;
        }
        $stmt->close();
        $users = array();
        foreach ($userIDs as $userID) {
            $user = new User();
            $user->select($userID);
            $users[] = $user;
        }
        return $users;
    }

    private function prepareDate($date) {
        if ( preg_match('/^(?P<day>\d+)[-\/](?P<month>\d+)[-\/](?P<year>\d+)$/', $date, $matches) && 
                checkdate($matches['month'], $matches['day'], $matches['year'])) {
            $dtime = mktime(0, 0, 0, $matches['month'], $matches['day'], $matches['year']);
            return date("Y-m-d H:i:s", $dtime);
        } else {
            return FALSE;
        }
    }
          
    function __construct($userName = NULL, $password = NULL, $name = NULL, $surname = NULL, $birthDate = NULL, $city = NULL){
        $this->userName = $userName;
        $this->name = $name;
        $this->surname = $surname;
        if ($this->prepareDate($birthDate)) {
            $this->birthDate = $this->prepareDate($birthDate);
        }
        $this->city = $city;
        $this->password = $password;
        $this->hash = password_hash($password, PASSWORD_DEFAULT);
    }
    
    function setValues($userName = NULL, $name = NULL, $surname = NULL, $birthDate = NULL, $city = NULL) {
        $this->userName = $userName;
        $this->name = $name;
        $this->surname = $surname;
        if ($this->prepareDate($birthDate)) {
            $this->birthDate = $this->prepareDate($birthDate);
        }
        $this->city = $city;
    }
    
    public function create() {
        if ($this->userName == "" || $this->password == "" || self::checkUserName($this->userName)) {
            return FALSE;
        } else {
            $stmt = self::$db->prepare("INSERT INTO users (userName, name, surname, birthDate, city, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $this->userName, $this->name, $this->surname, $this->birthDate, $this->city, $this->hash);
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                die($ex);
            }
            $stmt->close();
            
            $stmt = self::$db->prepare("SELECT userID FROM users ORDER BY userID DESC LIMIT 1");
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                die($ex);
            }
            $stmt->bind_result($this->userID);
            $stmt->fetch();
            $stmt->close();
            return TRUE;
        }
    }
    
    public function delete() {
        $stmt = self::$db->prepare("DELETE FROM users WHERE userID = ?");
        $stmt->bind_param("i", $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $stmt->close();
            return FALSE;
        }
        $stmt->close();
        $this->userID = NULL;
        return TRUE;
    }
    
    public function select($userID) {
        $stmt = self::$db->prepare("SELECT userID, userName, name, surname, birthDate, city, signupDate, password, lastMassagesCheck, lastFriendsCheck, lastNotificationsCheck FROM users WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $stmt->close();
            return FALSE;
        }
        $stmt->bind_result(
                $this->userID, 
                $this->userName, 
                $this->name, 
                $this->surname, 
                $this->birthDate, 
                $this->city, 
                $this->signupDate, 
                $this->hash, 
                $this->lastMassagesCheck, 
                $this->lastFriendsCheck, 
                $this->lastNotificationsCheck
        );
        if ($stmt->fetch()) {
            $stmt->close();
            return TRUE;
        } else {
            $stmt->close();
            return FALSE;
        }
    }
    
    public function update() {
        foreach (func_get_args() as $arg) {
            $stmt = self::$db->prepare("UPDATE users SET " . $arg . " = ? WHERE userID = ?");
            if (substr($arg, 0, 4) == "last") {
                $now = new DateTime();
                $this->$arg = $now->format('Y-m-d H:i:s');
            } elseif ($arg == "password") {
                 $arg = "hash";
            }
            $stmt->bind_param("si", $this->$arg, $this->userID);
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                die($ex);
            }
            $stmt->close();
        }
        return TRUE;
    }
    
    public function setPassword($password) {
        if ($password !== "") {
            $this->password = $password;
            $this->hash = password_hash($password, PASSWORD_DEFAULT);
            return TRUE;
        }
        return FALSE;
    }
    
    public function login() {
        $stmt = self::$db->prepare("SELECT userID, password FROM users WHERE userName = ?");
        $stmt->bind_param("s", $this->userName);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $stmt->bind_result($this->userID, $this->hash);
        if ($stmt->fetch()) {
            $stmt->close();
            if (password_verify($this->password, $this->hash)) {
                $this->select($this->userID);
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            $stmt->close();
            return FALSE;
        }
    }
    
    public function printHTML() {
        if (file_exists("images/users/$this->userID.jpg")) {
            $imgPath = "images/users/$this->userID.jpg";
        } else {
            $imgPath = "images/users/dummy.jpg";
        }
        if (!is_null($this->birthDate)) {
            $birthDate = date("d/m/Y", strtotime($this->birthDate));
        } else {
            $birthDate = "unknown";
        }
        $signupDate = date("d/m/Y", strtotime($this->signupDate));
        
        $res = "<article class='user'>\r\n";
        $res .= "<a href='profile.php?userID=$this->userID'><img src='$imgPath' alt='Profile image'></a>\r\n";
        $res .= "<div><h5><strong>$this->name $this->surname ($this->userName)</strong></h5>\r\n";
        $res .= "<p><strong>Birth date:</strong> $birthDate</p>\r\n";
        $res .= "<p><strong>City:</strong> $this->city</p>\r\n";
        $res .= "<p><strong>Member from:</strong> $signupDate</p>\r\n";
        $res .= "</div></article>\r\n";
        return $res;
    }
    
    public function numberOfFriends() {
        $stmt = self::$db->prepare("SELECT count(user1ID) FROM friends WHERE user1ID = ? OR user2ID = ?");
        $stmt->bind_param("ii", $this->userID, $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($res);
        if ($stmt->fetch()) {
            $stmt->close();
            return $res;
        } else {
            $stmt->close();
            return 0;
        }
    }
    
    public function friends() {
        $stmt = self::$db->prepare("SELECT user1ID, user2ID FROM friends WHERE user1ID = ? OR user2ID = ? ORDER BY friendsDate DESC");
        $stmt->bind_param("ii", $this->userID, $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($user1ID, $user2ID);
        $userIDs = array();
        while($stmt->fetch()) {
            if ($user1ID == $this->userID) {
                $userIDs[] = $user2ID;
            } else {
                $userIDs[] = $user1ID;
            }
        }
        $stmt->close();
        $users = array();
        foreach ($userIDs as $userID) {
            $user = new User();
            $user->select($userID);
            $users[] = $user;
        }
        return $users;
    }
    
    public function isFriend($userID) {
        if ($userID == $this->userID) {
            return TRUE;
        }
        $stmt = self::$db->prepare("SELECT friendsID FROM friends WHERE (user1ID = ? AND user2ID = ?) OR (user1ID = ? AND user2ID = ?)");
        $stmt->bind_param("iiii", $this->userID, $userID, $userID, $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        if ($stmt->fetch()) {
            $stmt->close();
            return TRUE;
        } else {
            $stmt->close();
            return FALSE;
        }
    }
    
    public function sendFriendRequest($userID) {
        if ($this->isFriend($userID)) return FALSE;
        $stmt = self::$db->prepare("SELECT friendRequestID FROM friend_requests WHERE senderID = ? AND receiverID = ? AND status != 2");
        $stmt->bind_param("ii", $this->userID, $userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $stmt->close();
            return FALSE;
        }
        if ($stmt->fetch()) {
            $stmt->close();
            return FALSE;
        } else {
            $stmt->close();
            $stmt = self::$db->prepare("INSERT INTO friend_requests (senderID, receiverID) VALUES (?, ?)");
            $stmt->bind_param("ii", $this->userID, $userID);
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                die($ex);
            }
            $stmt->close();
            return TRUE;
        }
    }
    
    public function friendRequests($source) {
        if ($source == "to") {
            $stmt = self::$db->prepare("SELECT friendRequestID, senderID, receiverID, requestDate, status FROM friend_requests WHERE receiverID = ? AND status = 0 ORDER BY requestDate DESC");
        } elseif ($source == "from") {
            $stmt = self::$db->prepare("SELECT friendRequestID, senderID, receiverID, requestDate, status FROM friend_requests WHERE senderID = ? AND (status = 0 OR (status != 0 AND requestDate > '$this->lastFriendsCheck')) ORDER BY status DESC, requestDate DESC");
        } else {
            return FALSE;
        }
        $stmt->bind_param("i", $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $stmt->bind_result($friendRequestID, $senderID, $receiverID, $requestDate, $status);
        $requests = array();
        while($stmt->fetch()) {
            $requests[] = array(
                'friendRequestID' => $friendRequestID,
                'senderID' => $senderID,
                'receiverID' => $receiverID,
                'requestDate' => $requestDate,
                'status' => $status
            );
        }
        $stmt->close();
        return $requests;
    }

    public function acceptFriend($userID, $accept) {
        $stmt = self::$db->prepare("SELECT friendRequestID FROM friend_requests WHERE senderID = ? AND receiverID = ? AND status = 0");
        $stmt->bind_param("ii", $userID, $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $stmt->bind_result($friendRequestID);
        
        if ($stmt->fetch()) {
            $stmt->close();
            if ($accept) {
                $stmt = self::$db->prepare("UPDATE friend_requests SET status = 1, statusChangeDate = now() WHERE friendRequestID = ?");
            } else {
                $stmt = self::$db->prepare("UPDATE friend_requests SET status = 2, statusChangeDate = now() WHERE friendRequestID = ?");
            }
            $stmt->bind_param("i", $friendRequestID);
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                die($ex);
            }
            $stmt->close();
            if ($accept) {
                self::makeFriends($this->userID, $userID);
            }
            return TRUE;
        } else {
            $stmt->close();
            return FALSE;
        }
    }
    
    public function albums() {
        $stmt = self::$db->prepare("SELECT albumID FROM albums WHERE userID = ?");
        $stmt->bind_param("i", $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $stmt->bind_result($albumID);
        $albumIDs = array();
        while($stmt->fetch()) {
            $albumIDs[] = $albumID;
        }
        $stmt->close();
        $albums = array();
        foreach ($albumIDs as $albumID) {
            $album = new Album();
            $album->select($albumID);
            $albums[] = $album;
        }
        return $albums;
    }
    
    public function numberOfNewMessages() {
        $stmt = self::$db->prepare("SELECT COUNT(messageID) FROM messages WHERE receiverID = ? AND messageDate > '$this->lastMassagesCheck'");
        $stmt->bind_param("i", $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        return $res;
    }
    
    public function numberOfNewFriendRequests() {
        $stmt = self::$db->prepare("SELECT COUNT(friendRequestID) FROM friend_requests WHERE receiverID = ? AND requestDate > '$this->lastFriendsCheck'");
        $stmt->bind_param("i", $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        return $res;
    }
    
    public function numberOfImages() {
        $stmt = self::$db->prepare("SELECT COUNT(imageID) FROM images WHERE userID = ?");
        $stmt->bind_param("i", $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        return $res;
    }
    
    public function numberOfAlbums() {
        $stmt = self::$db->prepare("SELECT COUNT(albumID) FROM albums WHERE userID = ?");
        $stmt->bind_param("i", $this->userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        return $res;
    }
    
    public function numberOfNewNotifications() {
        $stmtstr = "SELECT COUNT(activityID) "
                . "FROM activities "
                . "WHERE (data1 IN "
                . "(SELECT user1ID FROM friends WHERE user2ID = ? UNION SELECT user2ID FROM friends WHERE user1ID = ?) "
                . "OR (type = 3 AND data2 IN "
                . "(SELECT user1ID FROM friends WHERE user2ID = ? UNION SELECT user2ID FROM friends WHERE user1ID = ?))) "
                . "AND date > ?";
        $stmt = self::$db->prepare($stmtstr);
        $stmt->bind_param("iiiis", $this->userID, $this->userID, $this->userID, $this->userID, $this->lastNotificationsCheck);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        return $res;
    }
}

User::$db = $connection;