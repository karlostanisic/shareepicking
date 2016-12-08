<?php

require_once './includes/config.php';
require_once './includes/functions.php';
require_once './includes/class.user.php';
require_once './includes/class.image.php';
require_once './includes/class.album.php';

class Activity {
    static $db;
    
    public $activityID;
    public $type;
    public $date;
    public $data;
    
    function __construct($type = null, $data = array()) {
        $this->type = $type; 
        for ($i = 0; $i < count($data); $i++) {
            $this->data[$i] = $data[$i];
        }
        for ($i = 0; $i < (4 - count($data)); $i++) {
            $this->data[] = null;
        }
    }
    
    public function setValues($activityID, $type, $date, $data) {
        $this->activityID = $activityID;
        $this->type = $type;
        $this->date = $date;
//        $this->data = $data;
        for ($i = 0; $i < count($data); $i++) {
            $this->data[$i] = $data[$i];
        }
        for ($i = 0; $i < (4 - count($data)); $i++) {
            $this->data[] = null;
        }
    }
    
    public function create() {
        if (!is_int($this->type)) {
            return FALSE;
        } else {
            $stmt = self::$db->prepare("INSERT INTO activities (type, data1, data2, data3, data4) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiii", $this->type, $this->data[0], $this->data[1], $this->data[2], $this->data[3]);
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                return FALSE;
            }
            $stmt->close();
            
            $stmt = self::$db->prepare("SELECT activityID FROM activities ORDER BY activityID DESC LIMIT 1");
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                return FALSE;
            }
            $stmt->bind_result($this->activityID);
            $stmt->fetch();
            $stmt->close();
            return TRUE;
        }
    }
    
    static function getUserActivity($userID, $page = 0, $resultsPerPage = NULL) {
        if ($resultsPerPage) {
            $limit = intval($resultsPerPage);
            $offset = intval($page * $resultsPerPage);
        }
        $stmtstr = "SELECT activityID, type, date, data1, data2, data3, data4 "
                . "FROM activities "
                . "WHERE data1 = ? OR (type = 3 AND data2 = ?) "
                . "ORDER BY date DESC";
        if ($resultsPerPage) {
            $stmtstr .= " LIMIT $limit OFFSET $offset";
        }
        $stmt = self::$db->prepare($stmtstr);
        $stmt->bind_param("ii", $userID, $userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result(
            $activityID,
            $type,
            $date,
            $data[0],
            $data[1],
            $data[2],
            $data[3]
        );
        $res = array();
        while($stmt->fetch()) {
            $activity = new Activity();
            
            $activity->setValues($activityID, $type, $date, $data);
            $res[] = $activity;
        }
        $stmt->close();
        return $res;
    }
  
    static function getNotifications($userID, $page = 0, $resultsPerPage = NULL) {
        if ($resultsPerPage) {
            $limit = intval($resultsPerPage);
            $offset = intval($page * $resultsPerPage);
        }
        $stmtstr = "SELECT activityID, type, date, data1, data2, data3, data4 "
                . "FROM activities "
                . "WHERE data1 IN "
                . "(SELECT user1ID FROM friends WHERE user2ID = ? UNION SELECT user2ID FROM friends WHERE user1ID = ?) "
                . "OR (type = 3 AND data2 IN "
                . "(SELECT user1ID FROM friends WHERE user2ID = ? UNION SELECT user2ID FROM friends WHERE user1ID = ?)) "
                . "ORDER BY date DESC";
        if ($resultsPerPage) {
            $stmtstr .= " LIMIT $limit OFFSET $offset";
        }
        $stmt = self::$db->prepare($stmtstr);
        $stmt->bind_param("iiii", $userID, $userID, $userID, $userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result(
            $activityID,
            $type,
            $date,
            $data[0],
            $data[1],
            $data[2],
            $data[3]
        );
        $res = array();
        while($stmt->fetch()) {
            $activity = new Activity();
            
            $activity->setValues($activityID, $type, $date, $data);
            $res[] = $activity;
        }
        $stmt->close();
        return $res;
    }
    
    public function printAsActivityHTML() {
        $user = new User();
        $user->select($this->data[0]);
        switch ($this->type) {
            case 0:
                $notificationIcon = "<span class='glyphicon glyphicon-user'></span>";
                $notificationText = "<span class='user-name'>$user->userName</span> changed <a href='profile.php?userID=$user->userID'>profile image</a>.";
                break;
            case 1:
                $notificationIcon = "<span class='glyphicon glyphicon-camera'></span>";
                $image = new Image($this->data[1]);
                $imageCaption = ($image->caption == "") ? $image->name : $image->caption;
                $notificationText = "<span class='user-name'>$user->userName</span> shared a <a href='show-image.php?imageID=" . $image->imageID . "'>photo ($imageCaption)</a>.";
                break;
            case 2:
                $notificationIcon = "<span class='glyphicon glyphicon-picture'></span>";
                $album = new Album();
                $album->select($this->data[1]);
                $notificationText = "<span class='user-name'>$user->userName</span> shared an <a href='show-album.php?albumID=" . $album->albumID . "'>album ($album->name)</a>.";
                break;
            case 3:
                $notificationIcon = "<span class='glyphicon glyphicon-heart-empty'></span>";
                $user2 = new User();
                $user2->select($this->data[1]); 
                $notificationText = "<span class='user-name'>$user->userName</span> accepted <span class='user-name'>$user2->userName</span> as a friend.";
                break;

            default:
                $notificationIcon = "<span class='glyphicon glyphicon-question-sign'></span>";
                $notificationText = "";
                break;
        }
        $res = "<div class='notification'>";
        $res .= "<div class='notification-icon'>$notificationIcon</div>";
        $res .= "<div class='notification-body'>";
        $res .= "<p class='notification-date'>$this->date</p>";
        $res .= "<p class='notification-text'>";
        $res .= $notificationText;
        $res .= "</p>";
        $res .= "</div>";
        $res .= "</div>";
        return $res;
    }
    
    public function printAsNotificationHTML() {
        $user = new User();
        $user->select($this->data[0]);
        if (file_exists("images/users/$user->userID.jpg")) {
            $imgPath = "images/users/$user->userID.jpg";
        } else {
            $imgPath = "images/users/dummy.jpg";
        }
        $res = "<div class='notification'>";
        $res .= "<img class='notification-image' src='$imgPath'>";
        $res .= "<div class='notification-body'>";
        $res .= "<p class='notification-date'>$this->date</p>";
        $res .= "<p class='notification-text'>";
        switch ($this->type) {
            case 0:
                $res .= "<span class='user-name'>$user->userName</span> changed <a href='profile.php?userID=$user->userID'>profile image</a>.";
                break;
            case 1:
                $image = new Image($this->data[1]);
                $imageCaption = ($image->caption == "") ? $image->name : $image->caption;
                $res .= "<span class='user-name'>$user->userName</span> shared a <a href='show-image.php?imageID=" . $image->imageID . "'>photo ($imageCaption)</a>.";
                break;
            case 2:
                $album = new Album();
                $album->select($this->data[1]);
                $res .= "<span class='user-name'>$user->userName</span> shared an <a href='show-album.php?albumID=" . $album->albumID . "'>album ($album->name)</a>.";
                break;
            case 3:
                $user2 = new User();
                $user2->select($this->data[1]); 
                $res .= "<span class='user-name'>$user->userName</span> accepted <span class='user-name'>$user2->userName</span> as a friend.";
                break;

            default:
                break;
        }
        $res .= "</p>";
        $res .= "</div>";
        $res .= "</div>";
        return $res;
    }
}

Activity::$db = $connection;