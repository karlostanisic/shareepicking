<?php

require_once 'header.php';
require_once 'htmlheader.php';

if ($loggedUserStatus == "owner") {
    $activeNavLink = "nav-my-friends";
} else {
    $activeNavLink = "nav-friends";
}

$friends = $profileUser->friends();

if ($loggedUserStatus == "owner") {
    $friendReqestsToUser = $loggedUser->friendRequests("to");

    if (count($friendReqestsToUser) > 0) {
        echo "<h4>You have " . count($friendReqestsToUser) . " friend requests</h4>";
    }

    foreach ($friendReqestsToUser as $frtu) {
        $forMe = new User();
        $forMe->select($frtu['senderID']);
        echo "<div class='friend'>";
        echo $forMe->printHTML();
        echo "<p class='message-reply'><a href='#' class='accept-friend' data-userid='$forMe->userID'>accept</a>&nbsp;";
        echo "<a href='#' class='decline-friend' data-userid='$forMe->userID'>decline</a></p>";
        echo "<p><a href='profile.php?userID=$forMe->userID'>View profile</a></p>";
        echo "</div>";
    }

    $friendReqestsFromUser = $loggedUser->friendRequests("from");
    
    if (count($friendReqestsFromUser) > 0) {
        echo "\r\n<h4 class='margin-top'>You have " . count($friendReqestsFromUser) . " unanswered friend requests</h4>";
    }

    foreach ($friendReqestsFromUser as $frfu) {
        $fromMe = new User();
        $fromMe->select($frfu['receiverID']);
        echo "<div class='friend'>";
        echo $fromMe->printHTML();
        if ($frfu['status'] == 0) {
            $frfuStatus = "Waiting for reply";
        } elseif ($frfu['status'] == 1) {
            $frfuStatus = "Accepted";
        } else {
            $frfuStatus = "Declined";
        }
        echo "<p class='request-status message-reply'><strong>Status:</strong> " . $frfuStatus . "<p>";
        echo "<a href='profile.php?userID=$fromMe->userID'>View profile</a>";
        echo "</div>";
    }

    $loggedUser->update("lastFriendsCheck");
}

?>

<h4 class="margin-top">Friends (<?php echo $profileUser->numberOfFriends(); ?>)</h4>

<div class="friends">
<?php
foreach ($friends as $friend) {
    echo "<div class='friend'>";
    echo $friend->printHTML();
    echo "<p class='message-reply'><a href='profile.php?userID=$friend->userID'>View profile</a> &nbsp;&nbsp;";
    if ($loggedUserStatus == "owner") {
        echo "<a href='write-message.php?receiverID=$friend->userID'>Send message</a>";
    }
    echo "</p></div>";
}
echo "</div>";
?>

<script type="text/javascript">
    $('.decline-friend').click(function() {
        resolveFriendRequest(<?php echo $loggedUser->userID; ?>, $(this).data('userid'), 'false', this);
    });
    
    $('.accept-friend').click(function() {
        resolveFriendRequest(<?php echo $loggedUser->userID; ?>, $(this).data('userid'), 'true', this);
    });
    
    function resolveFriendRequest(user1ID, user2ID, accept, element) {
        $.ajax({
            url: "resolve-friend-request.php",
            type: "post",
            context: this,
            data: {
                user1ID: user1ID,
                user2ID: user2ID,
                accept: accept
            },
            success: function(data, textStatus, jqXHR)
            {
                if (accept == 'true') {
                    accept = 'accepted';
                } else {
                    accept = 'declined';
                }
                $(element).closest('p').text(accept);
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert("Error"); 
            }
        });
    }
</script>

<?php
require_once 'footer.php';
