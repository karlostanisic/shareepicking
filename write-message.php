<?php
require_once 'header.php';
require_once './includes/class.message.php';
require_once 'htmlheader.php';

if (isset($_GET['receiverID'])) {
    $receiverID = sanitizeString($_GET['receiverID']);
}

if (isset($_POST['submit'])) {
    echo $_POST['text'];
    foreach ($_POST as $key => $value) {
        $$key = sanitizeString($value);
    }
    
    $message = new Message($loggedUser->userID, $receiverID, $subject, $text);
    $message->send();
}

if (!isset($receiverID)) {
    die("Sorry, you didn't choose friend you want to write to.");
}

$receiver = new User();
$receiver->select($receiverID);

$activeNavLink = "nav-messages";

$messagesPerPage = 15;

echo "<h4>New message to: $receiver->userName</h4>";
?>

<form id='message' action='write-message.php' method='post'>
    <input type="hidden" name="receiverID" value="<?php echo $receiver->userID; ?>">
    <div class="form-group">
        <label class="sr-only" for="subject">Find friends:</label>
        <input type='text' class='form-control' size='50' name='subject' id='subject' maxlength='50' placeholder='Subject'/>
    </div>
    <div class="form-group">
        <label class="sr-only" for="text">Find friends:</label>
        <textarea type='text' class='form-control' name='text' id='text' placeholder='Write a message...'></textarea>
    </div>
    <input type='submit' class="btn btn-success" name='submit' value='Send' />
    <a href="write-message.php?receiverID=<?php echo $receiverID; ?>" class="btn btn-success">Refresh</a>
</form>

<div class="messages">
<?php

$messages = Message::getChat($loggedUser->userID, $receiver->userID, 0, $messagesPerPage);

foreach ($messages as $message) {
    if ($message->senderID == $loggedUser->userID) {
        echo "<div class='right-message'>";
    } else {
        echo "<div class='left-message'>";
    }
    echo $message->printHTML();
    echo "</div>";
}
echo "<p class='message-reply'></p>";
echo "</div>";

require_once 'footer.php';