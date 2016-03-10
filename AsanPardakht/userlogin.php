<?php
session_start();
session_regenerate_id();
$setting = include 'data/Settings.php';
extract($setting);

$password = $_POST['password'];
$username = $_POST['username'];

if ($username == $adminName && $password == $adminPass) {
    $_SESSION['isAdmin'] = 1;
    header('Location: admin.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>آسان پرداخت</title>
        <link rel="stylesheet" href="style.css" media="all" type="text/css">
    </head>
    <body>

        <section>
            <h3>خطا</h3>
            <p>نام کاربری یا رمز عبور اشتباه است.</p>
        </section>
        <?php
        include ('footer.php');
        