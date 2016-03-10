<html>
    <head>
        <meta charset="UTF-8">
        <title>وضعیت پرداخت</title>
        <link rel="stylesheet" href="style.css" media="all" type="text/css">
    </head>
    <body>
        <section>
            <?php
            include_once 'JDateTime.php';
            $setting = include 'data/Settings.php';
            extract($setting);
            $db = new PDO($dbstring, $dbuser, $dbpass);
            if (isset($_GET['valid'], $_GET['trans_id'])) {
                $Valid = $_GET['valid'];
                $stmt = $db->prepare("SELECT * FROM bank10_payment WHERE rand=?");
                $stmt->execute(array($Valid));
                $row = $stmt->fetch();

                if ($row) {
                    $stmt = $db->prepare("UPDATE bank10_payment SET status=? , trans_id=? WHERE id=?");
                    $stmt->execute(array(1, $_GET['trans_id'], $row['id']));
                    ?>
                    <h3>تراکنش با موفقیت انجام شد</h3>
                    <p> <?php echo $row['name'] ?> عزیز 
                        واریز مبلغ
                        <?php echo $row['price'] ?>
                        ریال<br>
                        
                        با موفقیت انجام شد.</br>
                        شماره ارجاع <?php echo $_GET['trans_id'] ?></p>
                    <a href="index.php">بازگشت  </a>
                </section>
                <?php
                include ('footer.php');
                return;
            }
        }
        ?>
        <?php
        $errors = array(
            3 => 'تراکنش بوسیله خریدار کنسل شده',
            4 => 'مبلغ سند برگشتی از مبلغ تراکنش اصلی بیشتر است',
            5 => 'درخواست برگشت تراکنش رسیده است در حالی که تراکنش اصلی پیدا نمی شود',
            6 => 'شماره کارت اشتباه است',
            7 => 'چنین صادر کننده کارتی وجود ندارد',
            8 => 'از تاریخ انقضای کارت گذشته است',
            9 => 'رمز کارت اشتباه است pin',
            10 => 'موجودی به اندازه کافی در حساب شما نیست',
            11 => 'سیستم کارت بانک صادر کننده فعال نیست',
            12 => 'خطا در شبکه بانکی',
            13 => 'مبلغ بیش از سقف برداشت است',
            14 => 'امکان سند خوردن وجود ندارد',
            15 => 'رمز کارت 3 مرتبه اشتباه وارد شده کارت شما غیر فعال اخواهد شد',
            16 => 'تراکنش در شبکه بانکی تایم اوت خورده',
            17 => 'اشتباه وارد شده cvv2 ویا ExpDate فیلدهای'
        );
        ?>
        <h3>خطا در پرداخت</h3>
        <?php if(isset($_GET['error']) && isset($errors[$_GET['error']])): ?>
        <p><?php echo $errors[$_GET['error']]; ?></p>
        <?php else: ?>
        <p>لطفا مجددا امتحان کنید.<br>در صورتی که مبلغ از حساب شما کم شده باشد،  بانک طی ۷۲ ساعت آینده مبلغ را به حساب شما بازگشت می‌دهد.</p>
        <?php endif; ?>
        <a href="index.php">تلاش مجدد</a>

    </section>
    <?php include ('footer.php'); ?>
