<?php
session_start();

if (!isset($_SESSION['isAdmin'])) {
    header('Location: login.php');
    exit();
}
session_regenerate_id();

$setting = include 'data/Settings.php';
extract($setting);
$db = new PDO($dbstring, $dbuser, $dbpass);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>مدیریت</title>
        <link rel="stylesheet" href="style.css" media="all" type="text/css">
    </head>
    <body>  
        <section class="tables">
            <h3>تراکنش‌ها</h3>
            <div class="left">
                <a href="setting.php">تنظیمات اسکریپت</a>
                <a href="logout.php">خروج</a>
            </div>
            <table border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <th>شماره</th>
                    <th>نام و نام خانوادگی</th>
                    <th>ایمیل</th>
                    <th>مبلغ</th>
                    <th>وضعیت</th>
                    <th>زمان</th>
                    <th>شناسه تراکنش </th>
                </tr>
                <?php foreach ($db->query('SELECT * FROM bank10_payment') as $row) { ?>
                    <tr>
                        <td><?php echo $row['id'] ?></td>
                        <td><?php echo $row['name'] ?></td>
                        <td><?php echo $row['email'] ?></td>
                        <td><?php echo $row['price'] ?></td>
                        <td><?php
                            if ($row['status'] == 1) {
                                echo 'پرداخت شده';
                            } else {
                                echo 'در انتظار';
                            }
                            ?></td>
                        <td><?php echo date('Y/m/d - H:m:s',  $row['time']); ?></td>
                        <td><?php echo $row['trans_id'] ?></td>
                    </tr>
        <?php } ?>
            </table>
        </section>
<?php include ('footer.php'); ?>

