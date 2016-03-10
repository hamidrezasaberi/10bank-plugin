<?php
session_start();
session_regenerate_id();
$settings = include 'data/Settings.php';
extract($settings);
if ($run)
    $_SESSION['isAdmin'] = 1;

if (!isset($_SESSION['isAdmin'])) {
    header('Location: login.php');
    exit();
}

extract($settings);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>تنظیمات اسکریپت آسان پرداخت</title>
        <link rel="stylesheet" href="style.css" media="all" type="text/css">
    </head>
    <body>
        <section>
            <div>
                <form action="saveSetting.php" method="post" class="setting">
                    <h3>تنظیمات دیتابیس</h3>
                    <label>
                        <span>(Host) هاست</span>
                        <input name="host" required value="<?php echo $host ?>">  
                    </label>
                    <label> 
                        <span>(Database Name) نام دیتابیس</span>
                        <input name="dbname" required value="<?php echo $dbname ?>"> 
                    </label>

                    <label> 
                        <span>(Database Username) نام کاربری دیتابیس</span>
                        <input name="dbuser" required value="<?php echo $dbuser ?>"> 
                    </label>
                    <label>  
                        <span>(Database Password) رمز عبور دیتابیس</span>
                        <input name="dbpass" value="<?php echo $dbpass ?>">                    
                    </label>    
                    <hr>
                    <h3>تنظیمات بانک ۱۰</h3>
                    <label> 
                        <span>(Gateway ID) شماره درگاه</span>
                        <input name="gateway_id" required value="<?php echo $gateway_id ?>">         
                    </label>
                    <label>  
                        <span>(Gateway api) کد api درگاه</span>
                        <input name="gateway_api" required value="<?php echo $gateway_api ?>">
                    </label>
                    <hr>
                    <h3>تنظیمات مدیر سایت</h3>
                    <label>    
                        <span>نام کاربری مدیر</span>
                        <input name="username" required value="<?php echo $adminName ?>">
                    </label>
                    <label>
                        <span>رمز عبور مدیر</span>
                        <input name="password" required="required" value="<?php echo $adminPass ?>" type="password" id="password" />
                    </label>   
                    <label>
                        <span>تکرار رمز عبور</span>
                        <input name="password_confirm" required="required" value="<?php echo $adminPass ?>" type="password" id="password_confirm" oninput="check(this)"  />
                    </label>        
                    <script language='javascript' type='text/javascript'>
                        function check(input) {
                            if (input.value != document.getElementById('password').value) {
                                input.setCustomValidity('فیلدهای رمز عبور باید یکسان باشند');
                            } else {
                                // input is valid -- reset the error message
                                input.setCustomValidity('');
                            }
                        }
                    </script>
                    <hr>
                    <button type="submit" >ثبت</button>
                </form>
            </div>
            <p>

            </p>
            <?php
            // put your code here
            ?>
        </section>
        <?php include ('footer.php'); ?>

