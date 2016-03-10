<?php
session_start();
$_SESSION = array(); 
session_unset();
session_destroy();
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();