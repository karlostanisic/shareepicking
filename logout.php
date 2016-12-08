<?php
require_once './includes/functions.php';
destroySession();
header('Location: index.php', true, ($permanent === true) ? 301 : 302);
die();