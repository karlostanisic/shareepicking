<?php
require_once 'header.php';
require_once './includes/class.activity.php';

if (!checkAutorisation($loggedUserStatus, "owner")) {
    die("You are not authorised to see this page.");
}

$massageProfil = $massagePassword = null;

if (file_exists("images/users/$loggedUser->userID.jpg")) {
    $imgPath = "images/users/$loggedUser->userID.jpg";
} else {
    $imgPath = "images/users/dummy.jpg";
}

if (isset($_POST['action'])) {
    foreach ($_POST as $key => $value) {
        $$key = sanitizeString($value);
    }
    if ($action == "profile") {
        $loggedUser->setValues($userName, $name, $surname, $birthDate, $city);
        $loggedUser->update("username", "name", "surname", "birthDate", "city");
        if (isset($_FILES['profileImage']['name'])) {
            $saveto = "images/users/$loggedUser->userID.jpg";
            move_uploaded_file($_FILES['profileImage']['tmp_name'], $saveto);

            $typeok = TRUE;

            switch ($_FILES['profileImage']['type']) {
                case "image/gif": $src = imagecreatefromgif($saveto); break;
                case "image/jpeg":
                case "image/pjpeg": $src = imagecreatefromjpeg($saveto); break;
                case "image/png": $src = imagecreatefrompng($saveto); break;
                default: $typeok = FALSE; break;
            }

            if ($typeok) {
                list($w, $h) = getimagesize($saveto);

                $max = 150;
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
                imagejpeg($tmp, $saveto);
                imagedestroy($tmp);
                imagedestroy($src);
                
//                insert activity 0 - profile image change
                $activity = new Activity(0, array($loggedUser->userID));
                $activity->create();
                
                
            }
        }
        header('Location: profile.php', true, ($permanent === true) ? 301 : 302);
        die();
    } elseif ($action == "password") {
        if (password_verify($oldPassword, $loggedUser->hash) && $newPassword != "") {
            $loggedUser->setPassword($newPassword);
            $loggedUser->update("password");
            $massagePassword = "Password successfully changed.";
        } else {
            $massagePassword = "Password has not been changed. Please, try again.";
        }
    }
}
require_once 'htmlheader.php';
?>

        <form id='profile' action='profile-update.php' method='post' enctype='multipart/form-data'>
            <fieldset>
                <legend>Edit profile</legend>
            
                <span class="help-block"><?php echo $massageProfil ?></span>

                <input type="hidden" name="action" value="profile">
                <div class="form-group">
                    <img src="<?php echo "$imgPath"; ?>" alt="Profile image" id="image-placeholder">
                </div>

                <div class="form-group">
                    <label class="" for="profileImage">Profile image: </label>
                    <input type="file" name="profileImage" id="profileImage" size="14">
                </div>   

                <div class="form-group">
                    <label class="sr-only" for="userName">User name:</label>
                    <input type='text' class="form-control input-lg" size="50" name='userName' id='userName' value='<?php echo $loggedUser->userName ?>' maxlength="50"  placeholder="User name"/>
                </div>

                <div class="form-group">
                    <label class="sr-only" for="name">First name:</label>
                    <input type='text' class="form-control input-lg" size="50" name='name' id='name' value='<?php echo $loggedUser->name ?>' maxlength="50"  placeholder="First name"/>
                </div>

                <div class="form-group">
                    <label class="sr-only" for="surname">Last name:</label>
                    <input type='text' class="form-control input-lg" size="50" name='surname' id='surname' value='<?php echo $loggedUser->surname ?>' maxlength="50"  placeholder="Last name"/>
                </div>

                <div class="form-group">
                    <label class="sr-only" for="city">City:</label>
                    <input type='text' class="form-control input-lg" size="50" name='city' id='city' value='<?php echo $loggedUser->city ?>' maxlength="50"  placeholder="City"/>
                </div>

                <div class="form-group">
                    <label for="birthDate">Date of birth:</label>
                    <input type='text' class="form-control input-lg" size="50" name='birthDate' id='birthDate' value='<?php echo date("d/m/Y", strtotime($loggedUser->birthDate)); ?>' maxlength="50"  placeholder="dd/mm/yyyy"/>
                </div>

                <input type='submit' class="btn btn-success" name='submit' value='Update' />
                <a href="javascript:history.go(-1)" class="btn btn-danger">Cancel</a>
            </fieldset>
        </form>

        <form id='password' action='profile-update.php' method='post' style="margin-top: 2em;">
            <input type="hidden" name="action" value="password">
            <fieldset>
                <legend>Change password</legend>
                <span class="help-block"><?php echo $massagePassword ?></span>
                <div class="form-group">
                    <label class="sr-only" for="oldPassword">Old password:</label>
                    <input type='password' class="form-control input-lg" size="50" name='oldPassword' id='oldPassword' maxlength="50"  placeholder="Old password"/>
                </div>
                <div class="form-group">
                    <label class="sr-only" for="newPassword">Old password:</label>
                    <input type='password' class="form-control input-lg" size="50" name='newPassword' id='newPassword' maxlength="50"  placeholder="New password"/>
                </div>

                <input type='submit' class="btn btn-success" name='submit' value='Change password' />
                <a href="javascript:history.go(-1)" class="btn btn-danger">Cancel</a>
            </fieldset>
        </form>
<script>
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#image-placeholder').attr('src', e.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#profileImage").change(function(){
        readURL(this);
    });
</script>
<?php
require_once 'footer.php';