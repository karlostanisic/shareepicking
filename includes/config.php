<?php

define('DBHOST','localhost');
define('DBUSER','root');
define('DBPASS','');
define('DBNAME','shareepicking');

$connection = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if ($connection->connect_error) die($connection->connect_error);