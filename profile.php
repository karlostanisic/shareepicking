<?php

require_once 'header.php';
require_once 'htmlheader.php';
require_once './includes/class.image.php';

if ($loggedUserStatus !== "owner") {
    $activeNavLink = "nav-profile";
}

if (is_null($profileUser->birthDate)) {
    $birthDate = "";
} else {
    $birthDate = date("d/m/Y", strtotime($profileUser->birthDate));
}
?>
<div class="container-fluid profile-info">
    <div class="row">
        <div class="col col-sm-3">
            <p>
                <img src="<?php echo "$profileUserImgPath"; ?>" alt="Profile image">
            </p>
        </div>
        <div class="col col-sm-9">
            <h3><?php echo "$profileUser->name $profileUser->surname"; ?> (<?php echo "$profileUser->userName"; ?>)</h3>
            <p><strong>Birth date:</strong> <?php echo $birthDate; ?></p>
            <p><strong>City:</strong> <?php echo $profileUser->city; ?></p>
            <p><strong>Member from:</strong> <?php echo date("d/m/Y", strtotime($profileUser->signupDate)); ?></p>
            <p><strong>Number of friends:</strong> <?php echo $profileUser->numberOfFriends(); ?></p>
            <p><strong>Number of images:</strong> <?php echo count(Image::getAllImagesFromUser($profileUser->userID)); ?></p>
<?php
if ($loggedUserStatus == "owner") {
    echo "<a href='profile-update.php' class='btn btn-success'>Edit profile</a>";
} elseif ($loggedUserStatus == "friend") {
    echo "<a href='write-message.php?receiverID=$profileUser->userID'>Send message</a>"; 
} else {
    echo "<p><a class='friend-request' href='#' data-userid='$profileUser->userID'>Send friend request</a></p>";
}
?>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $('.friend-request').click(function(e) {
            e.preventDefault();
            $.ajax({
                url: "send-friend-request.php",
                type: "post",
                context: this,
                data: {
                    senderID: <?php echo $loggedUser->userID; ?>,
                    receiverID: $(this).data('userid')
                },
                success: function(data, textStatus, jqXHR)
                {
                    $(this).closest('p').text("Friend request sent");
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    alert("Error"); 
                }
            });
        });
    });
</script>
<?php
require_once 'footer.php';