<?php
require_once 'header.php';

$message = "";
$page = 0;
$searchFrase = "";
$resultsPerPage = 10;

if (isset($_GET['page']) && isset($_GET['searchFrase'])) {
    $page = sanitizeString($_GET['page']);
    $searchFrase = sanitizeString($_GET['searchFrase']);
    $users = User::findUsers($searchFrase, $page, $resultsPerPage);
}

if (isset($_POST['searchFrase'])) {
    $searchFrase = sanitizeString($_POST['searchFrase']);
    if ($searchFrase !== "") {
        $users = User::findUsers($searchFrase, $page, $resultsPerPage);
    }
}

if (isset($users)) {
    if (count($users) == 0) {
        $message = "<p>No result found.</p>";
    }
} else {
    $users = array();
}

require_once 'htmlheader.php';
?>
<form id='find-friends' action='find-friends.php' method='post' autocomplete="off">
    <div class="input-group">
        <label class="sr-only" for="searchFrase">Find friends:</label>
        <input type="text" id="searchFrase" class="form-control input-lg" name="searchFrase" value="<?php echo $searchFrase; ?>" placeholder="Find friends...">
        
        <div class="input-group-btn">
            <button class="btn btn-success btn-lg" type="submit">
                <i class="glyphicon glyphicon-search"></i>
            </button>
        </div>
    </div>
</form>

<div id="search-suggest"></div>

<div class="friends">
<?php
if (count($users) > 0) {
    $numberOfFriends = User::countFindUsers($searchFrase);

    echo "<p>Showing users: " . ($page * $resultsPerPage + 1) . "-" . ($page + 1) * $resultsPerPage . " (" . $numberOfFriends . ")<p>";

    foreach ($users as $user) {
        echo "<div class='friend'>";
        echo $user->printHTML();
        echo "<p class='message-reply'></p>";
        if (!$user->isFriend($loggedUser->userID)) {
            echo "<p><a class='friend-request' href='#' data-userid='$user->userID'>Send friend request</a><p>";
        } else {
            echo "<p>You are friends with <span class='user-name'>$user->userName</span>!</p>";
        }
        echo "<p><a href='profile.php?userID=$user->userID'>View profile</a></p>";
        echo "</div>";
    }

    if ($numberOfFriends > $resultsPerPage) {
        echo "<p>Pages: ";
        $numberOfPages = ceil(($numberOfFriends / $resultsPerPage));
        for ($pageNo = 0; $pageNo < $numberOfPages; $pageNo++) {
            if ($pageNo == $page) {
                echo ($pageNo + 1) . "&nbsp;&nbsp;";
            } else {
                echo "<a href='find-friends.php?page=$pageNo&searchFrase=$searchFrase'>" . ($pageNo + 1) . "</a>&nbsp;&nbsp;";
            }
        } 
        echo "</p>";
    }
} else {
    echo $message;
}
?>    
</div>

<script type="text/javascript">
    
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
        
        
        $('#searchFrase').keyup(function() {
            if ($(this).val() !== "") {
                $.ajax({
                    url: "friend-search-suggestions.php",
                    type: "post",
                    context: this,
                    data: {
                        searchFrase: $(this).val()
                    },
                    success: function(data, textStatus, jqXHR)
                    {
                        fillSearchSuggest(data);
                    },
                    error: function(jqXHR, textStatus, errorThrown)
                    {
                        alert("Error"); 
                    }
                }); 
            } else {
                $('#search-suggest').hide();
            }
        });
        
        function fillSearchSuggest(data) {
            searchSuggestList = '<ul>';
            for(var i = 0; i < data.length; i++) {
                var user = data[i];
                searchSuggestList += '<li><a href="profile.php?userID=' + user.userID + '"><img src="images/users/' + user.userID + '.jpg" onerror="this.src=\'images/users/dummy.jpg\'"><div><p>' + user.name + ' ' + user.surname + '</p><p>(' + user.userName +  ')</p></div></a></li>';
            }
            searchSuggestList += '</ul>';
//            console.log(searchSuggestList);
            $('#search-suggest').html(searchSuggestList);
            if ($('#searchFrase').val() !== "") {
                $('#search-suggest').show();
            } else {
                $('#search-suggest').hide();
            }
        }
    });
    
</script>

<?php
require_once 'footer.php';
