<?php

require_once 'header.php';
require_once './includes/class.image.php';
require_once './includes/class.album.php';

$albums = $loggedUser->albums();

$error = "";

$activeNavLink = "nav-upload-images";

if (isset($_GET['albumID'])) {
    $albumID = sanitizeString($_GET['albumID']);
} else {
    $albumID = -1;
}

if (isset($_SERVER['CONTENT_LENGTH']) && intval($_SERVER['CONTENT_LENGTH'])>0 && count($_POST)===0) {
    $error = "Total size of files to upload is to big.";
} else {
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] !== "") {
        if (isset($_POST['albumID'])) {
            $albumID = sanitizeString($_POST['albumID']);
        } else {
            $albumID = "";
        }

        if ($albumID !== "") {
            $album = new Album();
            if ($albumID == 0) {
                $newAlbumName = sanitizeString($_POST['newAlbumName']);
                if ($newAlbumName == "") {
                    $newAlbumName = date('Y-m-d', time());
                }
                $album->setValues($loggedUser->userID, $newAlbumName, NULL);
                $album->create();
            } else {
                $album->select($albumID);
            }
        }

        for($i = 0; $i < count($_FILES['image']['name']); $i++) {
            $image = new Image();
            if (count($_FILES['image']['name']) !== 1 && $_POST['caption'] !== "") {
                $image->caption = sanitizeString($_POST['caption']) . "-" . $i;
            } else {
                $image->caption = sanitizeString($_POST['caption']);
            }
            $image->userID = $loggedUser->userID;
            $image->createName();

            $saveto = "images/photos/$image->name.jpg";
            $savetothumb = "images/photos/$image->name-thumb.jpg";
            move_uploaded_file($_FILES['image']['tmp_name'][$i], $savetothumb);

            $typeok = TRUE;

            switch ($_FILES['image']['type'][$i]) {
                case "image/gif": $src = imagecreatefromgif($savetothumb); break;
                case "image/jpeg":
                case "image/pjpeg": $src = imagecreatefromjpeg($savetothumb); break;
                case "image/png": $src = imagecreatefrompng($savetothumb); break;
                default: $typeok = FALSE; break;
            }

            if ($typeok) {
                list($w, $h) = getimagesize($savetothumb);

                $max = 400;
                $tw = $w;
                $th = $h;

                if ($w > $h && $max < $w) {
                    $th = $max / $w * $h;
                    $tw = $max;
                } elseif ($h > $w && $max < $h) {
                    $tw = $max / $h * $w;
                    $th = $max;
                } elseif ($max < $w) {
                    $tw = $th = $max;
                }

                $tmp = imagecreatetruecolor($tw, $th);
                imagecopyresampled($tmp, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
                imageconvolution($tmp, array(array(-1, -1, -1), array(-1, 16, -1), array(-1, -1, -1)), 8, 0);
                imagejpeg($tmp, $savetothumb);
                imagejpeg($src, $saveto);
                imagedestroy($tmp);
                imagedestroy($src);
                $image->create();

                if (isset($album)) {
                    $album->addImage($image->imageID);
                } 
            }
        }
        if ($albumID == "") {
            header('Location: show-album.php?albumID=0', true, ($permanent === true) ? 301 : 302);
        } else {
            header('Location: show-album.php?albumID=' . $album->albumID, true, ($permanent === true) ? 301 : 302);
        }

    } elseif (isset ($_POST['caption'])) {
        $error = "You didn't choose image to upload.";
    }
}

require_once 'htmlheader.php';
?>

<form id='imageUpload' action='upload-image.php' method='post' enctype='multipart/form-data'>
    <fieldset>
        <legend>Upload new images</legend>
        
<?php
if ($error !== "") {
    echo "<div class='alert alert-warning'>$error</div>";
}
?>
        <div class="form-group">
            <label class="sr-only" for="images-to-upload">Image: </label>
            <input type="file" name="image[]" multiple="multiple" id="images-to-upload">
            <span class="help-block" id="fileEmptyWarning">Choose images you wish to upload.</span>
        </div>   
        
        <div class="form-group">
            <label class="sr-only" for="caption">Caption:</label>
            <input class="form-control input-default" name='caption' id='caption' placeholder="Image caption">
        </div>
        
        <div class="form-inline">
        <div class="form-group">
            <label class="sr-only" for="albumID">Add to album:</label>
            <select id="albumID" name="albumID" class="form-control input-default">
                <option value="" disabled selected hidden>Select album</option>
                <option value="0" >Create new album</option>
            
<?php
foreach ($albums as $album) {
    if ($album->albumID == $albumID) {
        $selected = 'selected';
    } else {
        $selected = "";
    }
    echo "<option value='$album->albumID' $selected>$album->name</option>";
}
?>
            </select>
        </div>
        
        <div class="form-group">
                <input type="text" name="newAlbumName" id="newAlbumName" placeholder="Type album name" class="form-control input-default">
            </div>
        </div>
        
        <div class="form-group" style="margin-top: 1em;">
            <input type='submit' class="btn btn-success" name='submit' value='Upload' />
        </div>
    </fieldset>
</form>
<p class="show-image">
    <img id="img-preview" src="#" alt="Image to upload" />
</p>


<script>
    $( document ).ready(function() {
        $('#newAlbumName').hide();
        $('#img-preview').hide();
        $('#albumID').change(function() {
            if($(this).val() == 0) {
                $('#newAlbumName').show(400);
            } else {
                $('#newAlbumName').hide(200);
                $('#newAlbumName').val("");
            }
        });
    });
    
    function checkFile() {
        if ($("#image").val()) {    
            return true;
        } else {
            $('#fileEmptyWarning').show();
            return false;
        }
    };
    
    function readURL(input) {

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#img-preview').attr('src', e.target.result);
                $('#img-preview').show();
                $('#fileEmptyWarning').hide();
            };

            reader.readAsDataURL(input.files[0]);
        }
    };
    
    $("#images-to-upload").change(function(){
        readURL(this);
    });
</script>
<?php
require_once 'footer.php';

