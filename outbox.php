<?php
require_once 'header.php';
require_once 'htmlheader.php';
require_once './includes/class.message.php';

if (!checkAutorisation($loggedUserStatus, "owner")) {
    die("You are not authorised to see this page.");
}

$activeNavLink = "nav-messages";

$page = 0;
if (isset($_GET['page'])) {
    $page = sanitizeString($_GET['page']);
}
$messagesPerPage = 15;

echo "<h2>Outbox</h2>";
echo "<div class='messages'>";

$messages = Message::getMessagesFromUser($loggedUser->userID, $page, $messagesPerPage);
$numberOfMessages = Message::countMessagesFromUser($loggedUser->userID);

echo "<p>Showing messages: " . ($page * $messagesPerPage + 1) . "-" . ($page + 1) * $messagesPerPage . " (" . $numberOfMessages . ")<p>";

echo "<div class='messages'>";
foreach ($messages as $message) {
    echo $message->printHTML();
    echo "<p class='message-reply'></p>";
}
echo "</div>";

if ($numberOfMessages > $messagesPerPage) {
    echo "<p>Pages: ";
    $numberOfPages = (int)($numberOfMessages / $messagesPerPage) + 1;
    for ($pageNo = 0; $pageNo < $numberOfPages; $pageNo++) {
        if ($pageNo == $page) {
            echo ($pageNo + 1) . "&nbsp;&nbsp;";
        } else {
            echo "<a href='messages.php?page=$pageNo'>" . ($pageNo + 1) . "</a>&nbsp;&nbsp;";
        }
    } 
    echo "</p>";
}
echo "</div>";

require_once 'footer.php';