<?php

require_once 'header.php';
require_once 'htmlheader.php';
require_once './includes/class.album.php';
require_once './includes/class.image.php';

if (!checkAutorisation($loggedUserStatus, "friend")) {
    die("Only friends can see this page.");
}

$albums = $profileUser->albums();

if ($loggedUserStatus == "owner") {
    echo "<p><a href='album-form.php?action=add'><button class='btn btn-success'>Create new album</button></a></p>";
}
if ($loggedUserStatus == "owner") {
    $activeNavLink = "nav-my-albums";
} else {
    $activeNavLink = "nav-albums";
}

echo "<div class='albums container-fluid'>";

$counter = 0;
foreach ($albums as $album) {
    if($counter % 3 == 0) {
        echo "<div class='row'>\r\n";
    }
    echo "<div class='col-sm-4'>\r\n";
    $albumImages = $album->images();
    if (count($albumImages) > 4) {
        $maxOffset = 4;
    } else {
        $maxOffset = count($albumImages);
    }
    echo "<div class='album-info'>\r\n<div class='wrapper'><a href='show-album.php?albumID=$album->albumID'>\r\n<div class='album-thumb'>\r\n";
    for ($i = 0; $i < $maxOffset; $i++) {
        echo "<div><img src='images/photos/" . $albumImages[$i]->name . "-thumb.jpg'></div>\r\n";
    }
    echo "</div></a>\r\n";
    echo "</div>\r\n";
    echo "<h5>$album->name (" . count($albumImages) . ")</h5>\r\n";
    if ($loggedUserStatus == "owner") {
        echo "<p>\r\n<a href='album-form.php?action=edit&albumID=$album->albumID' class='btn btn-default btn-sm'>Edit</a>\r\n";
        echo "<button name='album-share[]' data-albumid='$album->albumID' class='btn btn-default btn-sm' data-toggle='modal' data-target='#confirm-share' title='Share'>\r\n"
                . "<span class='glyphicon glyphicon-share'></span>\r\n"
                . "</button>\r\n";
        echo "<button name='album-delete[]' data-albumid='$album->albumID' class='btn btn-danger btn-sm' data-toggle='modal' data-target='#confirm-delete' title='delete'>\r\n"
                . "<span class='glyphicon glyphicon-remove'></span>\r\n"
                . "</button>\r\n"
                . "</p>\r\n";
        
    }
    echo "</div>\r\n";
    echo "</div>\r\n";
    if($counter % 3 == 2) {
        echo "</div>\r\n";
    }
    $counter++;
}
if($counter % 3 == 0) {
    echo "<div class='row'>\r\n";
}
echo "<div class='col-sm-4'>\r\n";
$albumImages = Image::getAllImagesFromUser($profileUser->userID);
if (count($albumImages) > 4) {
    $maxOffset = 4;
} else {
    $maxOffset = count($albumImages);
}

echo "<div class='album-info'><div class='wrapper'>\r\n<a href='show-album.php?albumID=0'><div class='album-thumb'>\r\n";
for ($i = 0; $i < $maxOffset; $i++) {
    echo "<div><img src='images/photos/" . $albumImages[$i]->name . "-thumb.jpg'></div>\r\n";
}
echo "</div></a>\r\n";
echo "</div>\r\n";
echo "<h5>All pictures (" . count($albumImages) . ")</h5>\r\n";
if ($loggedUserStatus == "owner") {
    echo "<p>Contains all your pictures</p>\r\n";
}
echo "</div>";
echo "</div>\r\n";
if($counter % 3 == 2) {
    echo "</div>\r\n";
}

?>
</div>

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">

            </div>
            <div class="modal-body">
                Are you sure you want to delete this album?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-ok" data-dismiss="modal">Yes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>

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
                Are you sure you want to share this album?
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
                url: "delete-album.php",
                type: "post",
                data: {
                    albumID : $(e.relatedTarget).data('albumid')
                },
                success: function(data, textStatus, jqXHR)
                {
                    $(e.relatedTarget).closest('.album-info').remove();
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    alert("Error"); 
                }
            });
        });
    });
    
    $('#confirm-share').on('show.bs.modal', function(e) {
        $(this).find('.btn-ok').click(function() {
            $.ajax({
                url: "share-album.php",
                type: "post",
                context: this,
                data: {
                    albumID : $(e.relatedTarget).data('albumid')
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
</script>

<?php

require_once 'footer.php';
