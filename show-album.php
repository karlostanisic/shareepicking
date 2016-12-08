<?php

require_once 'header.php';
require_once './includes/class.album.php';
require_once './includes/class.image.php';

if (!checkAutorisation($loggedUserStatus, "friend")) {
    die("Only friends can see this page.");
}

if (!isset($_GET['albumID'])) {
    die("Album does not exist.");
}

if ($loggedUserStatus == "owner") {
    $activeNavLink = "nav-my-albums";
} else {
    $activeNavLink = "nav-albums";
}

$albumID = sanitizeString($_GET['albumID']);
$album = new Album();
if ($album->select($albumID)) {
    $albumImages = $album->images();
} else {
    $album->albumID = 0;
    $album->setValues($profileUser->userID, "All images", "In this album you can find all your images");
    $albumImages = Image::getAllImagesFromUser($profileUser->userID);
}
require_once 'htmlheader.php';
?>

<h3>Album name: <?php echo $album->name; ?></h3>
<p><?php echo $album->description; ?></p>
<?php
if ($loggedUserStatus == "owner") {
?>
<a href="upload-image.php?albumID=<?php echo $album->albumID; ?>" class="btn btn-success">Upload images</a>
<a href="pick-images.php?albumID=<?php echo $album->albumID; ?>" class="btn btn-success">Add images</a>
<?php
}
echo "<div class='album-images container-fluid'>";
$albumImagesNames = array();
$counter = 0;
foreach ($albumImages as $image) {
    if($counter % 3 == 0) {
        echo "<div class='row'>\r\n";
    }
    echo "<div class='col-sm-4'>\r\n";
    echo "<div class='image-info' data-imagename='$image->name'>\r\n";
    echo "<div class='image-thumb'>\r\n"
                . "<div class='image-wrapper'>\r\n"
                . "<img src='images/photos/$image->name-thumb.jpg' class='start-slide-show' data-index='$counter'>\r\n"
                . "</div>\r\n"
                . "<h5>$image->caption</h5>\r\n"
                . "</div>\r\n";
    if ($album->albumID == 0) {
        $dataToggle = "delete image";
    } else {
        $dataToggle = "remove from the album";
    }
    $usersLike = $image->getAllLikes();
    $numberOfLikes = count($usersLike);
?>
            <button name="like[]" data-imageid="<?php echo $image->imageID?>" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="left" title="<?php echo implode(", ", $usersLike); ?>">
                Like <span class="glyphicon glyphicon-thumbs-up"></span> <span class="number-of-likes"><?php echo $numberOfLikes; ?></span>
            </button>
            <a href="show-image.php?imageID=<?php echo $image->imageID?>" class="btn btn-default btn-sm" title="Write comment">
                <span class="glyphicon glyphicon-pencil"></span>
            </a>
<?php
    if ($loggedUserStatus == "owner") {
?>
            <button name="img-options-share[]" data-imageid="<?php echo $image->imageID?>" class="btn btn-default btn-sm" data-toggle="modal" data-target="#confirm-share" title="Share">
                <span class="glyphicon glyphicon-share"></span>
            </button>
            <button name="img-options-delete[]" data-imageid="<?php echo $image->imageID?>" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-delete" title="<?php echo $dataToggle; ?>">
                <span class="glyphicon glyphicon-remove"></span>
            </button>
<?php
    }
    echo "</div>\r\n";
    echo "</div>\r\n";
    if($counter % 3 == 2) {
        echo "</div>\r\n";
    }
    $counter++;
}

if ($album->albumID == 0) {
    $ajaxURL = "delete-image.php";
    $deleteMessage = "Are you sure you want to delete this image from your account?";
} else {
    $ajaxURL = "remove-image-from-album.php";
    $deleteMessage = "Are you sure you want to remove this image from album?";
}

?>
</div>

<div id="gallery-frame">
    <div id="image-frame">
        <span class="gallery-backward" unselectable="on">&laquo;</span>
        <span class="gallery-forward" unselectable="on">&raquo;</span>
        <span class="gallery-counter"></span>
        <img id="gallery-image">
    </div>
</div>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
            </div>
            <div class="modal-body">
                <?php echo $deleteMessage; ?>
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


<script type="text/javascript">
    $('#confirm-delete').on('show.bs.modal', function(e) {
        $(this).find('.btn-ok').click(function() {
            $.ajax({
                url: "<?php echo $ajaxURL; ?>",
                type: "post",
                context: this,
                data: {
                    albumID : <?php echo $album->albumID; ?>,
                    imageID : $(e.relatedTarget).data('imageid')
                },
                success: function(data, textStatus, jqXHR)
                {
                    $(e.relatedTarget).closest('.image-info').remove();
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    alert("Error"); 
                }
            });
        });
    });

    $('button[name=like\\[\\]]').click(function() {
        $.ajax({
            url: "add-like.php",
            type: "post",
            context: this,
            data: {
                imageID : $(this).data('imageid'),
                userID : <?php echo $loggedUser->userID; ?>
            },
            success: function(data, textStatus, jqXHR)
            {
                $(this).find('.number-of-likes').text(data);
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
                    imageID : $(e.relatedTarget).data('imageid')
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
            $(this).unbind('click');
        });
    });
    
    var imagenames = new Array();
    $('.image-info').each(function(index, el) {
        imagenames.push($(el).data('imagename')); 
    });

    var curentIndex = 0;
    var maxIndex = imagenames.length - 1;

    function changeGalleryImage(index) {
        $('#gallery-image').attr("src","images/photos/" + imagenames[index] + ".jpg");
        $('.gallery-counter').text((index + 1) + "/" + (maxIndex + 1));
    }

    $('.start-slide-show').click(function() {
        $('#gallery-frame').show();
        curentIndex = $(this).data('index');
        changeGalleryImage(curentIndex);
    });
    $('#image-frame').click(function(e) {
        e.stopPropagation();
    });
    $('#gallery-frame').click(function() {
        $('#gallery-frame').hide();
    });
    $('.gallery-backward').click(function() {
        curentIndex = curentIndex > 0 ? curentIndex - 1 : maxIndex;
        changeGalleryImage(curentIndex);
    });
    $('.gallery-forward').click(function() {
        curentIndex = curentIndex < maxIndex ? curentIndex + 1 : 0;
        changeGalleryImage(curentIndex);
    });
</script>

<?php
require_once 'footer.php';