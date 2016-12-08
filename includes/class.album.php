<?php

require_once './includes/config.php';
require_once './includes/functions.php';
require_once './includes/class.image.php';

class Album {
    static $db;
    
    public $albumID;
    public $userID;
    public $name;
    public $description;
    public $albumDate;
    
    public function select($albumID) {
        $stmt = self::$db->prepare("SELECT albumID, userID, name, description, albumDate FROM albums WHERE albumID = ?");
        $stmt->bind_param("i", $albumID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result(
                $this->albumID, 
                $this->userID, 
                $this->name, 
                $this->description, 
                $this->albumDate
        );
        if ($stmt->fetch()) {
            $stmt->close();
            return TRUE;
        } else {
            $stmt->close();
            return FALSE;
        }
    }
    
    public function setValues($userID, $name, $description) {
        $this->userID = $userID;
        $this->name = $name;
        $this->description = $description;
    }
    
    public function delete() {
        if (is_null($this->albumID)) {
            return FALSE;
        }
        $stmt = self::$db->prepare("DELETE FROM albums WHERE albumID = ?");
        $stmt->bind_param("i", $this->albumID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $stmt->close();
            return FALSE;
        }
        $stmt->close();
        $this->albumID = NULL;
        return TRUE;
    }
    
    public function update() {
        $stmt = self::$db->prepare("UPDATE albums SET userID = ?, name = ?, description = ? WHERE albumID = ?");
        $stmt->bind_param("issi", $this->userID, $this->name, $this->description, $this->albumID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $stmt->close();
            return FALSE;
        }
        return TRUE;
    }
    
    public function create() {
        if (!is_int($this->userID) || $this->name == "") {
            return FALSE;
        } else {
            $stmt = self::$db->prepare("INSERT INTO albums (userID, name, description) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $this->userID, $this->name, $this->description);
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                die($ex);
            }
            $stmt->close();
            
            $stmt = self::$db->prepare("SELECT albumID FROM albums ORDER BY albumID DESC LIMIT 1");
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                die($ex);
            }
            $stmt->bind_result($this->albumID);
            $stmt->fetch();
            $stmt->close();
            return TRUE;
        }
    }
    
    public function printHTML() {
        $res = "<div class='album'>\r\n"
        . "<p class='album-name'>$this->name (" . $this->numberOfImages() . ")</p>\r\n"
                . "<p class='album-description'>$this->description</p>\r\n"
                . "<p class='album-date'>$this->albumDate</p>\r\n"
                . "</div>";
        return $res;
    }
    
    public function numberOfImages() {
        $stmt = self::$db->prepare("SELECT COUNT(imageID) FROM album_images WHERE albumID = ?");
        $stmt->bind_param("i", $this->albumID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        $stmt->close();
        return $res;
    }
    
    public function images() {
        $stmt = self::$db->prepare("SELECT imageID FROM album_images WHERE albumID = ? ORDER BY dateAdded DESC");
        $stmt->bind_param("i", $this->albumID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;;
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
    
    public function addImage($imageID) {
        $stmt = self::$db->prepare("INSERT INTO album_images (albumID, imageID) VALUES (?, ?)");
        $stmt->bind_param("ii", $this->albumID, $imageID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->close();
        return TRUE;
    }
    
    public function removeImage($imageID) {
        $stmt = self::$db->prepare("DELETE FROM album_images WHERE imageID = ?");
        $stmt->bind_param("i", $imageID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->close();
        return TRUE;
    }
}

Album::$db = $connection;

