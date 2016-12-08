<?php
require_once 'header.php';
require_once 'htmlheader.php';
require_once './includes/class.activity.php';

if (!checkAutorisation($loggedUserStatus, "friend")) {
    die("Only friends can see this page.");
}

$activeNavLink = "nav-activity";

$page = 0;
if (isset($_GET['page'])) {
    $page = sanitizeString($_GET['page']);
}
$resultsPerPage = 15;

$activities = Activity::getUserActivity($profileUser->userID);

echo "<h4>Activities</h4>";
echo "<div class='notifications'>";

foreach ($activities as $activity) {
    echo $activity->printAsActivityHTML();
}

echo "</div>";

if (count($activities) == 15 || $page > 0) {
    echo "<p>";
    if ($page > 0) {
        echo "<a href='activity.php?page=" . ($page - 1) . "'>&lt;&lt; previous page</a>&nbsp;&nbsp;";
    }
    if (count($activities) == 15) {
        echo "<a href='activity.php?page=" . ($page + 1) . "'>next page &gt;&gt;</a>";
    }
    echo "</p>";
}

require_once 'footer.php';
