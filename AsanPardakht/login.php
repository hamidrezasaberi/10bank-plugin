<html>
  <head>
      <meta charset="UTF-8">
      <title>ورود</title>
      <link rel="stylesheet" href="style.css" media="all" type="text/css">
  </head>
  <body>
    <section>
      <h3>ورود به بخش مدیریت</h3>
       <form action="userlogin.php" method="post">
        <label>
          <span>نام کاربری</span>
          <input name="username">
        </label>
        <label>
          <span>رمز عبور</span>
          <input type="password" name="password">
        </label>
        <button type="submit" >ورود</button>
      </form>
    </section>
<?php include ('footer.php'); ?>

