<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SHAREePICking</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/mystyles.css" rel="stylesheet">
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  </head>
<body>
    <nav class="nav-bar" id="main-nav">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar" style="background-color: black;"></span>
                    <span class="icon-bar" style="background-color: black;"></span>
                    <span class="icon-bar" style="background-color: black;"></span>
                </button>
                <h1 class="navbar-brand">
                    <span class="other-color">SHARE</span>e<span class="other-color">PIC</span>king
                </h1>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                            <?php echo $loggedUser->userName; ?> <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="profile.php?userID=<?php echo $loggedUser->userID; ?>">My profile</a></li>
                            <li><a href="logout.php">Sign out</a></li>
                        </ul>
                    </li>
                </ul>
                <form class="navbar-form navbar-right" method="post" action="find-friends.php" autocomplete="off">
                    <div class="input-group">
                        <input type="text" class="form-control" name="searchFrase" placeholder="Find friends...">
                        <div class="input-group-btn">
                            <button class="btn btn-success" type="submit">
                                <i class="glyphicon glyphicon-search"></i>
                            </button>
                        </div>
                        
                    </div>
                    
                </form>
                
            </div>
        </div>
    </nav>
    <div id="sticky-nav-placeholder"></div>
    <div id="sticky-nav" class="container-fluid">
        <div class="row">
            <div class="col-lg-6 col-lg-offset-3">
                <ul class="main-navigation">
                    <li><a href="albums.php?userID=<?php echo $loggedUser->userID; ?>" id="nav-my-albums" title="My albums"><span class="glyphicon glyphicon-picture"></span><span class="nav-link-text"> My Albums</span></a></li>
                    <li><a href="upload-image.php?userID=<?php echo $loggedUser->userID; ?>" id="nav-upload-images" title="Upload images"><span class="glyphicon glyphicon-upload"></span><span class="nav-link-text"> Upload images</span></a></li>
                    <li><a href="messages.php?userID=<?php echo $loggedUser->userID; ?>" id="nav-messages" title="Messages"><span class="glyphicon glyphicon-envelope"></span><span class="nav-link-text"> Messages <?php echo $numberOfNewMessages !== 0 ? "(" . $numberOfNewMessages . ")" : ""; ?></span></a></li>
                    <li><a href="friends.php?userID=<?php echo $loggedUser->userID; ?>" id="nav-my-friends" title="My friends"><span class="glyphicon glyphicon-heart-empty"></span><span class="nav-link-text"> My friends <?php echo $numberOfNewFriendRequests !== 0 ? "(" . $numberOfNewFriendRequests . ")" : ""; ?></span></a></li>
                    <li><a href="notifications.php?userID=<?php echo $loggedUser->userID; ?>" id="nav-notifications" title="Notifications"><span class="glyphicon glyphicon-exclamation-sign"></span><span class="nav-link-text"> Notifications <?php echo $numberOfNewNotifications !== 0 ? "(" . $numberOfNewNotifications . ")" : ""; ?></span></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div id="container" class="container-fluid  col-lg-6 col-lg-offset-3">
        
<?php
if ($loggedUserStatus !== "owner") {
?>            
        <div class="profile-image">
            <h2><img src="<?php echo $profileUserImgPath; ?>">&nbsp;<?php echo "$profileUser->userName"; ?></h2>
        </div>
        <ul class="main-navigation">
            <li></li>
            <li><a href="profile.php" id="nav-profile"><span class="glyphicon glyphicon-user"></span> Profile</a></li>
            <li><a href="albums.php" id="nav-albums"><span class="glyphicon glyphicon-picture"></span> Albums (<?php echo $numberOfAlbums; ?>)</a></li>
            <li><a href="friends.php" id="nav-friends"><span class="glyphicon glyphicon-heart-empty"></span> Friends (<?php echo $numberOfFriends; ?>)</a></li>
            <li><a href="activity.php" id="nav-activity"><span class="glyphicon glyphicon-info-sign"></span> Activity</a></li>
        </ul>
<?php
}
?>         
        <div id="content">
            

