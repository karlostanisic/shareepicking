<?php

require_once './includes/config.php';
require_once './includes/functions.php';

class Message {
    static $db;
    
    public $messageID;
    public $senderID;
    public $receiverID;
    public $subject;
    public $text;
    public $messageDate;
    
    function __construct($senderID = null, $receiverID = null, $subject = null, $text = null) {
        $this->senderID = $senderID; 
        $this->receiverID = $receiverID; 
        $this->subject = $subject; 
        $this->text = $text;
    }
    
    static function countMessagesFromUser ($userID) {
        $stmt = self::$db->prepare("SELECT COUNT(messageID) "
                . "FROM messages "
                . "WHERE senderID = ? ");
        $stmt->bind_param("i", $userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        return $res;
    }
    
    static function countMessagesToUser ($userID) {
        $stmt = self::$db->prepare("SELECT COUNT(messageID) "
                . "FROM messages "
                . "WHERE receiverID = ? ");
        $stmt->bind_param("i", $userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        return $res;
    }
    
    static function countChat ($user1ID, $user2ID) {
        $stmt = self::$db->prepare("SELECT COUNT(messageID) "
                . "FROM messages "
                . "WHERE (receiverID = ? AND senderID = ?) OR (receiverID = ? AND senderID = ?)");
        $stmt->bind_param("iiii", $user1ID, $user2ID, $user2ID, $user1ID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            die($ex);
        }
        $stmt->bind_result($res);
        $stmt->fetch();
        return $res;
    }

    static function getMessagesFromUser($userID, $page = 0, $resultsPerPage = NULL) {
        if ($resultsPerPage) {
            $limit = intval($resultsPerPage);
            $offset = intval($page * $resultsPerPage);
        }
        $stmtstr = "SELECT messageID, senderID, receiverID, subject, text, messageDate "
                . "FROM messages "
                . "WHERE senderID = ? "
                . "ORDER BY messageDate DESC";
        if ($resultsPerPage) {
            $stmtstr .= " LIMIT $limit OFFSET $offset";
        }
        $stmt = self::$db->prepare($stmtstr);
        $stmt->bind_param("i", $userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($messageID, $senderID, $receiverID, $subject, $text, $messageDate);
        $messages = array();
        while($stmt->fetch()) {
            $message = new Message;
            $message->setValues($messageID, $senderID, $receiverID, $subject, $text, $messageDate);
            $messages[] = $message;
        }
        $stmt->close();
        return $messages;
    }
    
    static function getMessagesToUser($userID, $page = 0, $noOfMsgs = 15) {
        $offset = intval($page * $noOfMsgs);
        $limit = intval($noOfMsgs);
        $stmt = self::$db->prepare("SELECT messageID, senderID, receiverID, subject, text, messageDate "
                . "FROM messages "
                . "WHERE receiverID = ? "
                . "ORDER BY messageDate DESC "
                . "LIMIT $limit OFFSET $offset");
        $stmt->bind_param("i", $userID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($messageID, $senderID, $receiverID, $subject, $text, $messageDate);
        $messages = array();
        while($stmt->fetch()) {
            $message = new Message;
            $message->setValues($messageID, $senderID, $receiverID, $subject, $text, $messageDate);
            $messages[] = $message;
        }
        $stmt->close();
        return $messages;
    }
    
    static function getChat($user1ID, $user2ID,  $page = 0, $noOfMsgs = 15) {
        $offset = intval($page * $noOfMsgs);
        $limit = intval($noOfMsgs);
        $stmt = self::$db->prepare("SELECT messageID, senderID, receiverID, subject, text, messageDate "
                . "FROM messages "
                . "WHERE (receiverID = ? AND senderID = ?) OR (receiverID = ? AND senderID = ?) "
                . "ORDER BY messageDate DESC "
                . "LIMIT $limit OFFSET $offset");
        $stmt->bind_param("iiii", $user1ID, $user2ID, $user2ID, $user1ID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            return FALSE;
        }
        $stmt->bind_result($messageID, $senderID, $receiverID, $subject, $text, $messageDate);
        $messages = array();
        while($stmt->fetch()) {
            $message = new Message;
            $message->setValues($messageID, $senderID, $receiverID, $subject, $text, $messageDate);
            $messages[] = $message;
        }
        $stmt->close();
        return $messages;
    }
    
    public function setValues($messageID, $senderID, $receiverID, $subject, $text, $messageDate) {
        $this->messageID = $messageID; 
        $this->senderID = $senderID; 
        $this->receiverID = $receiverID; 
        $this->subject = $subject; 
        $this->text = $text; 
        $this->messageDate = $messageDate;
    }
    
    public function send() {
        if (is_null($this->senderID) || is_null($this->receiverID)) {
            return FALSE;
        } else {
            $stmt = self::$db->prepare("INSERT INTO messages (senderID, receiverID, subject, text) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $this->senderID, $this->receiverID, $this->subject, $this->text);
            try {
                $stmt->execute();
            } catch (Exception $ex) {
                return FALSE;
            }
            $stmt->close();
            return TRUE;
        }
    }
    
    public function printHTML(){
        if (file_exists("images/users/$this->senderID.jpg")) {
            $imgPath = "images/users/$this->senderID.jpg";
        } else {
            $imgPath = "images/users/dummy.jpg";
        }
        
        $stmt = self::$db->prepare("SELECT userName FROM users WHERE userID = ?");
        $stmt->bind_param("i", $this->senderID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $stmt->close();
            return FALSE;
        }
        $stmt->bind_result($sender);
        $stmt->fetch();
        $stmt->close();
        
        $stmt = self::$db->prepare("SELECT userName FROM users WHERE userID = ?");
        $stmt->bind_param("i", $this->receiverID);
        try {
            $stmt->execute();
        } catch (Exception $ex) {
            $stmt->close();
            return FALSE;
        }
        $stmt->bind_result($receiver);
        $stmt->fetch();
        $stmt->close();

        $res = "<article class='message'>\r\n";
        $res .= "<img src='$imgPath'>\r\n";
        $res .= "<div><p><strong>From:</strong> <span class='user-name'>$sender</span> ";
        $res .= "<strong>To:</strong> <span class='user-name'>$receiver</span> \r\n";
        $res .= "$this->messageDate<br></p>\r\n";
        $res .= "<p><strong>Subject:</strong> $this->subject</p>\r\n";
        $res .= "<p>$this->text</p>\r\n";
        $res .= "</div>\r\n";
        $res .= "</article>\r\n";
        return $res;
    }
}

Message::$db = $connection;

