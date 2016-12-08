<?php

require_once './includes/config.php';
require_once './includes/functions.php';

class Image {
    static $db;
    
    public $imageID;
    public $name;
    public $caption;
    public $userID;
    
    function __construct($imageID = NULL) {
        if ($imageID) {
            return $this->select($imageID);
        }
    }
    
    public function select($imageID) {
        $stmt = self::$db->prepare("SELECT imageID, name, caption, userID FROM images WHERE imageID = ? ORDER BY imageDate DESC");
        $stmt->bind_param("i", $imageID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result(
                $this->imageID, 
                $this->name, 
                $this->caption, 
                $this->userID 
        );
        if ($stmt->fetch()) {
            $stmt->close();
            return TRUE;
        } else {
            $stmt->close();
            return FALSE;
        }
    }
    
    public function delete() {
        if (is_null($this->imageID)) {
            return FALSE;
        }
        $stmt = self::$db->prepare("DELETE FROM images WHERE imageID = ?");
        $stmt->bind_param("i", $this->imageID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $stmt->close();
            return FALSE;
        }
        $stmt->close();
        if (file_exists("images/photos/$this->name.jpg")) {
            unlink("images/photos/$this->name.jpg");
        }
        if (file_exists("images/photos/$this->name-thumb.jpg")) {
            unlink("images/photos/$this->name-thumb.jpg");
        }
        $this->imageID = NULL;
        return TRUE;
    }
    
    public function update() {
        $stmt = self::$db->prepare("UPDATE images SET caption = ? WHERE imageID = ?");
        $stmt->bind_param("si", $this->caption, $this->imageID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $stmt->close();
            return FALSE;
        }
        return TRUE;
    }


    public function createName() {
        $this->name = generateRandomString();
    }
    
    public function create() {
        if (!is_int($this->userID) || $this->name == "") {
            return FALSE;
        } else {
            $stmt = self::$db->prepare("INSERT INTO images (name, caption, userID) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $this->name, $this->caption, $this->userID);
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                die($ex);
            }
            $stmt->close();
            
            $stmt = self::$db->prepare("SELECT imageID FROM images ORDER BY imageID DESC LIMIT 1");
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                die($ex);
            }
            $stmt->bind_result($this->imageID);
            $stmt->fetch();
            $stmt->close();
            return TRUE;
        }
    }
    
    public function addComment($userID, $text){
        $stmt = self::$db->prepare("INSERT INTO images_comments (imageID, userID, text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $this->imageID, $userID, $text);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $stmt->close();
    }
    
    public function addLike($userID){
        
        $stmt = self::$db->prepare("SELECT * FROM image_likes WHERE userID = ? AND imageID = ?");
        $stmt->bind_param("ii", $userID, $this->imageID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        if ($stmt->fetch()) {
            $stmt->close();
            return FALSE;
        } else {
            $stmt->close();

            $stmt = self::$db->prepare("INSERT INTO image_likes (imageID, userID) VALUES (?, ?)");
            $stmt->bind_param("ii", $this->imageID, $userID);
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                return FALSE;
            }
            $stmt->close();
           
            return TRUE;
        }
    }
    
    public function getAllLikes() {
        $stmt = self::$db->prepare("SELECT users.userName "
                . "FROM image_likes INNER JOIN users "
                . "ON image_likes.userID = users.userID "
                . "WHERE image_likes.imageID = ? "
                . "ORDER BY image_likes.likeDate DESC ");
        $stmt->bind_param("i", $this->imageID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $res = array();
        $stmt->bind_result($userName);
        while ($stmt->fetch()) {
            $res[] = $userName;
        }
        $stmt->close();
        return $res;
    }
    
    public function getAllComments() {
        $stmt = self::$db->prepare("SELECT users.userID, users.userName, images_comments.text, images_comments.commentDate "
                . "FROM images_comments INNER JOIN users ON images_comments.userID = users.userID "
                . "WHERE images_comments.imageID = ? "
                . "ORDER BY images_comments.commentDate DESC");
        $stmt->bind_param("i", $this->imageID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $stmt->bind_result($userID, $userName, $text, $commentDate);
        $comments = array();
        while($stmt->fetch()) {
            $comments[] = array(
                'userID' => $userID,
                'userName' => $userName,
                'text' => $text,
                'commentDate' => $commentDate
            );
        }
        $stmt->close();
        return $comments;
    }
    
    public function printThumbHTML() {
        $res = "<div class='image-thumb'>\r\n"
                . "<div class='image-wrapper'>\r\n"
                . "<img src='images/photos/$this->name-thumb.jpg'>\r\n"
                . "</div>\r\n"
                . "<h5>$this->caption</h5>\r\n"
                . "</div>\r\n";
        return $res;
    }
    
    static function getAllImagesFromUser($userID) {
        $stmt = self::$db->prepare("SELECT imageID FROM images WHERE userID = ? ORDER BY imageDate DESC");
        $stmt->bind_param("i", $userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($imageID);
        $imageIDs = array();
        while($stmt->fetch()) {
            $imageIDs[] = $imageID;
        }
        $stmt->close();
        $images = array();
        foreach ($imageIDs as $imageID) {
            $image = new Image($imageID);
            $images[] = $image;
        }
        return $images;
    }
}

Image::$db = $connection;
