<?php
$settings = include 'data/Settings.php';

if ($settings['run']){
    header('Location: setting.php');
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
        <h3>اطلاعات پرداخت کننده را کامل کنید</h3>
        <p>پس از پرداخت، شماره ارجاع را یادداشت کنید.</p>
        <form action="Addtodb.php" method="Post">
         <input type="hidden" name="action" value="payment">    
        <label>
            <span>نام و نام خانوادگی</span>
            <input type="text" size="50" name="name" placeholder="نام و نام خانوادگی را وارد کنید">
        </label>
        <label>
            <span>ایمیل پرداخت کننده</span>
          <input type="email" size="50" name="email" placeholder="ایمیل خود را وارد کنید">
        </label>
        <label>
            <span>مبلغ پرداخت </span>
          <input type="text" name="price" placeholder="مبلغ را وارد کنید">
        </label>
         <button type="submit">ورود به درگاه بانک</button>
      </form>
    </section>
<?php include ('footer.php');
