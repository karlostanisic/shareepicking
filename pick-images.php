<?php
require_once 'header.php';
require_once './includes/class.image.php';
require_once 'htmlheader.php';

if (!checkAutorisation($loggedUserStatus, "owner")) {
    die("You are not authorised to see this page.");
}

if (isset($_GET['albumID'])) {
    $albumID = sanitizeString($_GET['albumID']);
} else {
    die("Album is not picked.");
}

if ($loggedUserStatus == "owner") {
    $activeNavLink = "nav-my-albums";
} else {
    $activeNavLink = "nav-albums";
}

$images = Image::getAllImagesFromUser($loggedUser->userID);
?>

<h4>Click on images to select them</h4>
<form id='images' action='add-image-to-album.php' method='post'>
    <input type="hidden" name="albumID" value="<?php echo $albumID; ?>">
    <div class="container-fluid">

<?php
$counter = 0;
foreach ($images as $image) {
    if($counter % 3 == 0) {
        echo "<div class='row'>\r\n";
    }
    echo "<div class='col-sm-4'>\r\n";
    echo "<div name='image[]' class='image-info' style='cursor:pointer;'>\r\n";
    echo "<div class='image-wrapper'><img src='images/photos/$image->name-thumb.jpg' style='outline-offset: -2px;'></div>\r\n";
    echo "<input type='checkbox' name='imageID[]' id='imageID[]' value='$image->imageID'>\r\n";
    echo "</div>\r\n";
    echo "</div>\r\n";
    if($counter % 3 == 2) {
        echo "</div>\r\n";
    }
    $counter++;
}
?>
    </div>
    <div class="text-center">
        <input type="submit" class="btn btn-success" value="Add">
    </div>
</form>

<script type="text/javascript">

$( document ).ready(function() {
    $('input[name=imageID\\[\\]]').each(function() {
        $(this).hide();
    });
    $('div[name=image\\[\\]]').each(function() {
        $(this).click(function(){
            imgCheck = $(this).children('input');
            imgCheck.prop("checked", !imgCheck.prop("checked"));
            if (imgCheck.prop('checked')) {
                $(this).children('.image-wrapper').css("border", "2px solid #337ab7");
            } else {
                $(this).children('.image-wrapper').css("border", "1px solid lightgrey");
            }
        });
    });
});

</script>

<?php
require_once 'footer.php';