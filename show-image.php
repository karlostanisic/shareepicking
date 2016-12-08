<?php
require_once 'header.php';
require_once './includes/class.image.php';
require_once './includes/class.album.php';

$error = "";

if (!isset($_GET['imageID'])) {
    $error = "Image does not exist.";
}

if ($loggedUserStatus == "owner") {
    $activeNavLink = "nav-my-albums";
} else {
    $activeNavLink = "nav-albums";
}

$imageID = sanitizeString($_GET['imageID']);
$image = new Image();

if (!$image->select($imageID)) {
    $error = "Image does not exist.";
}

if (!checkAutorisation($loggedUserStatus, "friend")) {
    $error = "Only friends can see this photo.";
}
require_once 'htmlheader.php';

if ($error !== "") {
    echo "<div class='alert alert-warning'>$error</div>";
    require_once 'footer.php';
    die();
}
?>
<p class="show-image">
    <img src="images/photos/<?php echo $image->name ?>.jpg" class="show-image">
</p>

<?php
if ($loggedUser->userID == $image->userID) {
?>
<p>
    <input type="submit" id="img-options-show" class="btn btn-default btn-sm" name='img-options-show'  value="Edit"> 
</p>

<div class="img-options">
    <div class="row">
        <div class="col-sm-4">
        <label class="sr-only" for="caption">Caption:</label>
        <input type="text" class="input-sm form-control" name="caption" id="caption" value="<?php echo $image->caption; ?>" placeholder="Caption">
            <button id="img-options-caption" class="btn btn-success btn-sm" name='img-options-caption'>
                Change
            </button>
        </div>
        <div class="col-sm-6">
            <label class="sr-only" for="albumID">Pick album:</label>
            <select id="albumID" name="albumID" class="input-sm form-control">
                <option value="" disabled selected hidden>Add to album...</option>
                <option value="0" >Create new album</option>
<?php
$albums = $loggedUser->albums();
foreach ($albums as $album) {
    echo "<option value='$album->albumID'>$album->name</option>";
}
?>
            </select>
            <span id="newAlbumNameContainer">
                <label class="sr-only" for="newAlbumName">Album name:</label>
                <input type="text" class="input-sm form-control" name="newAlbumName" id="newAlbumName" placeholder="Type album name">

            </span>
            <button id="img-options-album" class="btn btn-success btn-sm" name='img-options-album'>
                Add
            </button>
        </div>
        <div class="col-sm-2">
            <button id="img-options-share" class="btn btn-default btn-sm" name='img-options-share' data-toggle="modal" data-target="#confirm-share" title="Share">
                <span class="glyphicon glyphicon-share"></span>
            </button>
            <button id="img-options-delete" class="btn btn-danger btn-sm" name='img-options-delete' data-toggle="modal" data-target="#confirm-delete" title="Delete">
                <span class="glyphicon glyphicon-remove"></span>
            </button>
        </div>
    </div>
</div>   
<?php
} else {
    echo "<p>$image->caption</p>";
}

$usersLike = $image->getAllLikes();
$numberOfLikes = count($usersLike);
?>
<p>
    <button id="like" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="left" title="<?php echo implode(", ", $usersLike); ?>">
        Like <span class="glyphicon glyphicon-thumbs-up"></span> <span class="number-of-likes"><?php echo $numberOfLikes; ?></span>
    </button>
</p>
<p>
    <textarea type='text' class="form-control  input-default" name='comment' id='comment' placeholder="Write a comment..."></textarea>
</p>
<p>
    <button id="submit-comment" class="btn btn-success" name='submit'>Send</button>
</p>
<?php

$comments = $image->getAllComments();

echo "<div class='comments'>";
foreach ($comments as $key => $value) {
    echo createCommentHTML($comments[$key]['userID'], $comments[$key]['userName'], $comments[$key]['text'], strtotime ($comments[$key]['commentDate']));
    echo "<p class='message-reply'>&nbsp;</p>";
}
echo "</div>";
?>
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
            </div>
            <div class="modal-body">
                Are you sure you want to delete this image from your account?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Yes</button>
                <button type="button" class="btn btn-success" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm-share" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
            </div>
            <div class="modal-body">
                Are you sure you want to share this image?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Yes</button>
                <button type="button" class="btn btn-success" data-dismiss="modal">No</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm-caption-change" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
            </div>
            <div class="modal-body">
                Image caption is changed.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirm-adding-to-album" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
            </div>
            <div class="modal-body">
                Image is added to album.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div id="gallery-frame">
    <div id="image-frame">
        <img id="gallery-image" src="images/photos/<?php echo $image->name ?>.jpg">
    </div>
</div>

<script type="text/javascript">
    $('.show-image').click(function() {
        $('#gallery-frame').show();
    });
    $('#image-frame').click(function(e) {
        e.stopPropagation();
    });
    $('#gallery-frame').click(function() {
        $('#gallery-frame').hide();
    });
    
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
        $('#newAlbumNameContainer').hide();
        $('.img-options').hide();
        $('#img-options-show').click(function() {
            $('.img-options').toggle(200);
        });
    });
    
    $('#albumID').change(function() {
        if ($(this).val() == 0) {
            $('#newAlbumNameContainer').show(200);
        } else {
            $('#newAlbumNameContainer').hide(200);
        }
    });
 
    $("#submit-comment").click(function(){
        $.ajax({
            url: "upload-comment.php",
            type: "post",
            data: {
                imageID: <?php echo $image->imageID; ?>,
                    userID: <?php echo $loggedUser->userID; ?>,
                    text: $("#comment").val()
            },
            success: function(data, textStatus, jqXHR)
            {
                addNewComment(data);
                $("#comment").val("");
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert("Error"); 
            }
        });
    });

    $("#img-options-album").click(function() {
        $.ajax({
            url: "add-image-to-album.php",
            type: "post",
            data: {
                imageID : <?php echo $image->imageID; ?>,
                albumID : $('#albumID').val(),
                newAlbumName : $('#newAlbumName').val()
            },
            success: function(data, textStatus, jqXHR)
            {
                $('#confirm-adding-to-album').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert("Error"); 
            }
        });
    });

    $("#like").click(function() {
        $.ajax({
            url: "add-like.php",
            type: "post",
            data: {
                imageID : <?php echo $image->imageID; ?>,
                userID : <?php echo $loggedUser->userID; ?>
            },
            success: function(data, textStatus, jqXHR)
            {
                $('.number-of-likes').text(data);
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert("Error"); 
            }
        });
    });

    $('#confirm-share').on('show.bs.modal', function(e) {
        $(this).find('.btn-ok').click(function() {
            $.ajax({
                url: "share-image.php",
                type: "post",
                context: this,
                data: {
                    imageID : <?php echo $image->imageID; ?>
                },
                success: function(data, textStatus, jqXHR)
                {
                    $(e.relatedTarget).addClass('disabled');
                    $(e.relatedTarget).html('<span class="glyphicon glyphicon-ok"></span>');
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    alert("Error"); 
                }
            });
        });
    });

    $('#confirm-delete').on('show.bs.modal', function(e) {
        $(this).find('.btn-ok').click(function() {
            $.ajax({
                url: "delete-image.php",
                type: "post",
                data: {
                    imageID : <?php echo $image->imageID; ?>
                },
                success: function(data, textStatus, jqXHR)
                {
                    window.history.go(-1);
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    alert("Error"); 
                }
            });
        });
    });
    
    $('#img-options-caption').click(function() {
        $.ajax({
            url: "update-image-caption.php",
            type: "post",
            data: {
                imageID : <?php echo $image->imageID; ?>,
                caption: $('#caption').val()
            },
            success: function(data, textStatus, jqXHR)
            {
                $('#confirm-caption-change').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown)
            {
                alert("Error"); 
            }
        });
    });
    
    function addNewComment(comment){
        $(".comments").prepend( comment + "<p class='message-reply'>&nbsp;</p>" );
    };
</script>

<?php
require_once 'footer.php';


