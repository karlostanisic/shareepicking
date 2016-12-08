<?php

require_once 'header.php';
require_once 'htmlheader.php';
require_once './includes/class.activity.php';

$activeNavLink = "nav-notifications";

$page = 0;
if (isset($_GET['page'])) {
    $page = sanitizeString($_GET['page']);
}
$resultsPerPage = 15;

$notifications = Activity::getNotifications($loggedUser->userID, $page, $resultsPerPage);

if ($numberOfNewNotifications > 0) {
    echo "<h4>You have $numberOfNewNotifications new notifications</h4>";
} else { 
    echo "<h4>No new notifications</h4>";
}
echo "<div class='notifications'>";

foreach ($notifications as $notification) {
    echo $notification->printAsNotificationHTML();
}

echo "</div>";

if (count($notifications) == 15 || $page > 0) {
    echo "<p>";
    if ($page > 0) {
        echo "<a href='notifications.php?page=" . ($page - 1) . "'>&lt;&lt; previous page</a>&nbsp;&nbsp;";
    }
    if (count($notifications) == 15) {
        echo "<a href='notifications.php?page=" . ($page + 1) . "'>next page &gt;&gt;</a>";
    }
    echo "</p>";
}

$loggedUser->update("lastNotificationsCheck");

require_once 'footer.php';
