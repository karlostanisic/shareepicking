<?php
require_once 'header.php';
require_once 'htmlheader.php';
require_once './includes/class.message.php';

$activeNavLink = "nav-messages";

$page = 0;
if (isset($_GET['page'])) {
    $page = sanitizeString($_GET['page']);
}
$resultsPerPage = 15;

$messages = Message::getMessagesToUser($loggedUser->userID, $page, $resultsPerPage);
$numberOfMessages = Message::countMessagesToUser($loggedUser->userID);

echo "<h2>Inbox</h2>";
echo "<div class='messages'>";
echo "<p>Showing messages: " . ($page * $resultsPerPage + 1) . "-" . ($page + 1) * $resultsPerPage . " (" . $numberOfMessages . ")<p>";

echo "<div class='messages'>";
foreach ($messages as $message) {
    
    echo $message->printHTML();
    echo "<p class='message-reply'><a href='write-message.php?receiverID=$message->senderID'>Reply</a></p>";
}
echo "</div>";

if ($numberOfMessages > $resultsPerPage) {
    echo "<p>Pages: ";
    $numberOfPages = (int)($numberOfMessages / $resultsPerPage) + 1;
    for ($pageNo = 0; $pageNo < $numberOfPages; $pageNo++) {
        if ($pageNo == $page) {
            echo ($pageNo + 1) . "&nbsp;&nbsp;";
        } else {
            echo "<a href='messages.php?page=$pageNo'>" . ($pageNo + 1) . "</a>&nbsp;&nbsp;";
        }
    } 
    echo "</p>";
}
$loggedUser->update("lastMassagesCheck");

echo "</div>";

require_once 'footer.php';