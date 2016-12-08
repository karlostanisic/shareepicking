<?php

require_once 'header.php';
require_once './includes/class.album.php';
require_once './includes/class.image.php';

if (!checkAutorisation($loggedUserStatus, "owner")) {
    die("You are not authorised to see this page.");
}

if ($loggedUserStatus == "owner") {
    $activeNavLink = "nav-my-albums";
} else {
    $activeNavLink = "nav-albums";
}

if (isset($_GET['action'])) {
    $action = sanitizeString($_GET['action']);
    
    switch ($action) {
        case "edit":
            $albumID = sanitizeString($_GET['albumID']);
            $album = new Album();
            $album->select($albumID);
            $submit = "Update";
            break;
        case "add":
            $album = new Album();
            $album->userID = $loggedUser->userID;
            $submit = "Create";
            break;
        case "delete":
            $albumID = sanitizeString($_GET['albumID']);
            $album = new Album();
            $album->select($albumID);
            $submit = "Delete";
            break;
        default:
            die("This page cannot be displayed.");
            break;
    }
}

if (isset($_POST['action'])) {
    foreach ($_POST as $key => $value) {
        $$key = sanitizeString($value);
    }
    $album = new Album();
    $album->setValues($loggedUser->userID, $name, $description);
    
    switch ($action) {
        case "edit":
            $album->albumID = $albumID;
            $album->update();
            break;
        case "add":
            $album->create();
            break;
        case "delete":
            $album->albumID = $albumID;
            $album->delete();
            break;
        default:
            die("This page cannot be displayed.");
            break;
    }
    header('Location: albums.php', true, ($permanent === true) ? 301 : 302);
}

$albumImages = $album->images();
require_once 'htmlheader.php';
?>

<form id='album' action='album-form.php' method='post'>
    <input type="hidden" name="action" value="<?php echo $action; ?>">
    <input type="hidden" name="albumID" value="<?php echo $album->albumID; ?>">
    <input type="hidden" name="userID" value="<?php echo $loggedUser->userID; ?>">
    <fieldset>
        <legend>Album info</legend>
        <div class="form-group">
            <label class="sr-only" for="name">Album name:</label>
            <input type='text' class="form-control input-lg" size="50" name='name' id='name' value="<?php echo $album->name; ?>" placeholder="Album name"/>
        </div>
        <div class="form-group">
            <label class="sr-only" for="description">Description:</label>
            <textarea class="form-control input-lg" size="50" name='description' id='description'  placeholder="Description"><?php echo $album->description; ?></textarea>
        </div>
        
        <input type='submit' class="btn btn-success" name='submit' value='<?php echo $submit; ?>' />
        <a href="javascript:history.go(-1)" class="btn btn-danger">Cancel</a>
    </fieldset>
</form>
<div class="album-images">
    
<?php

foreach ($albumImages as $image) {
    $image->printThumbHTML();
}

echo "</div>";
    
require_once 'footer.php';

