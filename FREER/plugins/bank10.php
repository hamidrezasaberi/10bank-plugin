<?php

$pluginData[bank10][type] = 'payment';
$pluginData[bank10][name] = 'پرداخت انلاین  ۱۰ بانک';
$pluginData[bank10][uniq] = 'bank10';
$pluginData[bank10][description] = 'درکاه پرداخت ۱۰ بانک';
$pluginData[bank10][author][name] = '10bank.ir';
$pluginData[bank10][author][url] = 'http://10bank.ir';
$pluginData[bank10][author][email] = 'info@10bank.ir';


$pluginData[bank10][field][config][1][title] = 'id درگاه را وارد کنید ';
$pluginData[bank10][field][config][1][name] = 'gateway_id';


$pluginData[bank10][field][config][2][title] = 'API درگاه را وارد کنید ';
$pluginData[bank10][field][config][2][name] = 'gateway_api';

function DoIt($params) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function gateway__bank10($data) {

    global $config, $smarty, $db;
    $gateway_id = $data['gateway_id'];
    $gateway_api = $data['gateway_api'];
    $amount = $data[amount];
    $redirect_url = $data[callback];
    $order_id = $data[invoice_id];
    $rand = substr(md5(time() . microtime()), 0, 10);
    $url = 'http://10bank.ir/transaction/create';

    $params = 'gateway_id=' . $gateway_id . '&amount=' . $amount . '&redirect_url=' . $redirect_url . '&rand=' . $rand;

    //Transaction id
    $result = DoIt($params);

    if ($result > 0 && is_numeric($result)) {

        $update[payment_rand] = $result . '-' . $rand;

        $sql = $db->queryUpdate('payment', $update, 'WHERE `payment_rand` = "' . $order_id . '" LIMIT 1;');
        $db->execute($sql);
        $go = "http://10bank.ir/transaction/submit?id=" . $result;
        header("Location: $go");
        exit;
    } else {

        $data[title] = 'خطای سیستمی!';
        $data[message] = '<font color="red">در ارتباط با بانک ۱۰  مشکلی به وجود آمده است.</font> خطا :  ' . $result . '<br /><a href="index.php" class="button">بازگشت</a>';
        $smarty->assign('data', $data);
        $smarty->display('message.tpl');
        exit;
    }
}

function callback__bank10($data) {
    global $db, $get, $smarty;
    $gateway_id = $data['gateway_id'];
    $gateway_api = $data['gateway_api'];
    $amount = (int) ($_GET['amount']);

    $trans_id = (int) ($_GET['trans_id']);
    $valid = ($_GET['valid']);



    $sql = 'SELECT * FROM `payment` WHERE `payment_rand` LIKE "' . $trans_id . '-%" LIMIT 1;';
    $payment = $db->fetch($sql);
    $rand = end(explode('-', $payment[payment_rand]));

    $verify_valid = md5($gateway_id . $payment[payment_amount] . $gateway_api . $rand) == $valid;

    if ($verify_valid) {

        if ($payment) {
            if ($payment[payment_status] == 1) {
                $output[status] = 1;
                $output[res_num] = $trans_id;
                $output[payment_id] = $payment[payment_id];
            } else {
                $output[status] = 0;
                $output[message] = 'چنین سفارشی تعریف نشده است.';
            }
        } else {
            $output[status] = 0;
            $output[message] = 'اطلاعات پرداخت کامل نیست.';
        }
    } else {
        $output[status] = 0;
        $output[message] = 'پرداخت موفقيت آميز نبود';
    }
    return $output;
}
