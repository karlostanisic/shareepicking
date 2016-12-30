<?php 

session_start();

require_once './includes/config.php';
require_once './includes/functions.php';
require_once './includes/class.user.php';

$action = $message = $userName = $password = $name = $surname = $city = $birthDate = "";

if (isset($_POST['submit'])) {
    foreach ($_POST as $key => $value) {
        $$key = sanitizeString($value);
    }
    if ($action == "login") {
        $user = new User($userName, $password);
        if ($user->login()) {
            $_SESSION['loggedUserID'] = $user->userID;
            $_SESSION['profileUserID'] = $user->userID;
//            header("Location: http://localhost/shareepicking/profile.php");
            header('Location: albums.php', true, ($permanent === true) ? 301 : 302);
        } else {
             $message = "The username or password you entered doesn't belong to an account. Please try again.";
        }
    } elseif ($action == "register") {
        $user = new User($userName, $password, $name, $surname, $birthDate, $city);
        if ($user->create()) {
            $_SESSION['loggedUserID'] = $user->userID;
            $_SESSION['profileUserID'] = $user->userID;
//            header("Location: http://localhost/shareepicking/profile.php");
            header('Location: profile.php', true, ($permanent === true) ? 301 : 302);
        } else {
            $message = "The username is taken. Please try again.";
        }
    }
}
?>

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
    <style>
<?php
if ($action == "register") {
    echo "#login-form";
} else {
    echo "#register-form";
}
?>
        {
            display: none;
        }
        
        body {
            background: #ffffff;
        }
        
        .vertical-center {
            min-height: 100%;
            min-height: 100vh;
            
            display: -webkit-box;
            display: -moz-box;
            display: -ms-flexbox;
            display: -webkit-flex;
            display: flex; 

              -webkit-box-align : center;
            -webkit-align-items : center;
                 -moz-box-align : center;
                 -ms-flex-align : center;
                    align-items : center;

            width: 100%;

                   -webkit-box-pack : center;
                      -moz-box-pack : center;
                      -ms-flex-pack : center;
            -webkit-justify-content : center;
                    justify-content : center;
          }

    </style>
  </head>
  <body>
      <div class="vertical-center">
    <div class="container text-center">
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
        <img src="images/login-graphic.png">
        
        <div id="login-form">
            <form id='login' action='index.php' method='post'>
                <input type="hidden" name="action" value="login">
                <span class="help-block"><?php echo $message ?></span>
                <div class="form-group">
                    <label class="sr-only" for="userName">User name:</label>
                    <input type='text' class="form-control input-lg" name='userName' id='userName' value='<?php echo $userName ?>' maxlength="50"  placeholder="User name"/>
                </div>

                <div class="form-group">
                    <label class="sr-only" for="password">Password:</label>
                    <input type='password' class="form-control input-lg" name='password' id='password' maxlength="50"  placeholder="Password"/>
                    
                </div>   

                <input type='submit' class="btn btn-default" name='submit' value='Log in' />

            </form>
            <p>Don't have an account? <a href="#" class="toggle-link">Register here</a>.</p>
        </div>
        
        <div id="register-form">
            <form id='register' action='index.php' method='post'>
                <input type="hidden" name="action" value="register">
                <span class="help-block"><?php echo $message ?></span>



                <div class="form-group">
                    <label class="sr-only" for="userName">User name:</label>
                    <input type='text' class="form-control input-lg" name='userName' id='userName' value='<?php echo $userName ?>' maxlength="50"  placeholder="User name"/>
                </div>
                
                
                <div class="form-group">
                    <label class="sr-only" for="pass">Password:</label>
                    <input type='password' class="form-control input-lg" name='password' id='password' maxlength="50"  placeholder="Password"/>
                    <span class="help-block"></span>
                </div>   
                
                <div class="form-group">
                    <label class="sr-only" for="name">Name:</label>
                    <input type='text' class="form-control input-lg" name='name' id='name' value='<?php echo $name ?>' maxlength="50"  placeholder="Name"/>
                </div>
                
                <div class="form-group">
                    <label class="sr-only" for="surname">Surname:</label>
                    <input type='text' class="form-control input-lg" name='surname' id='surname' value='<?php echo $surname ?>' maxlength="50"  placeholder="Surname"/>
                </div>
                
                <div class="form-group">
                    <label class="sr-only" for="city">City:</label>
                    <input type='text' class="form-control input-lg" name='city' id='city' value='<?php echo $city ?>' maxlength="50"  placeholder="City"/>
                </div>
                
                <div class="form-group">
                    <label for="birthDate">Date of birth:</label>
                    <input type='text' class="form-control input-lg" name='birthDate' id='birthDate' value='<?php echo $birthDate ?>' maxlength="50"  placeholder="dd/mm/yyyy"/>
                </div>


                <input type='submit' class="btn btn-default" name='submit' value='Register' />

            </form>
            <p>Already have an account? <a href="#" class="toggle-link">Login here</a>.</p>
        </div>
        </div>
        </div>
    </div>
      </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script>
        
        $('.toggle-link').click(function(){ toogleFormsVisibility(); return false; });
        
        function toogleFormsVisibility() {
            $('#login-form').toggle();
            $('#register-form').toggle();
        }
    </script>
  </body>
</html>