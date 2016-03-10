<?php

class bank
{

    const TRAN_URL = 'http://10bank.ir/transaction/create';
    const PAY_URL = 'http://10bank.ir/transaction/submit';
    
    private $_gatewayApi, $_gatewayId, $_redirectUrl, $_transactionId, $_rand;
    
    public static $errorCodes = array(
        1 => 'خطا در پرداخت',
        2 => 'تراکنش با موفقیت انجام شد',
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
    

    public function __construct($gatewayApi, $gatewayId, $redirectUrl = null)
    {
        $this->_gatewayApi = $gatewayApi;
        $this->_gatewayId = $gatewayId;
        if ($redirectUrl)
            $this->setRedirectUrl($redirectUrl);
    }

    public function setRedirectUrl($url)
    {
        if (!strpos($url, '//')) {

            $secure = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';

            $http = $secure ? 'https' : 'http';

            if (isset($_SERVER['HTTP_HOST']))
                $hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            else
                $hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];

            $url = $hostInfo . $url;
        }

        $this->_redirectUrl = urlencode($url);
    }

    public function setRand($rand)
    {
        $this->_rand = $rand;
    }

    public function getRand()
    {
        if (!$this->_rand)
            $this->_rand = substr(md5(time() . microtime() . uniqid()), 0, 16);

        return $this->_rand;
    }

    public function getTransactionId()
    {
        return $this->_transactionId;
    }

    /**
     * @param int $amount payment ammount
     * @param string $description payment description
     * @return int the id of bank10 transaction 
     * @throws Exception on error
     */
    public function transaction($amount, $description = null, $rand = null, $redirect = true)
    {
        if ($rand)
            $this->setRand($rand);

        $params = http_build_query(array(
            'gateway_id' => $this->_gatewayId,
            'amount' => $amount,
            'description' => $description,
            'redirect_url' => $this->_redirectUrl,
            'rand' => $this->getRand()
        ));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::TRAN_URL);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        if (is_numeric($response)) {
            $this->_transactionId = (int) $response;
            if ($redirect)
                $this->payment();
            return $this->_transactionId;
        }

        throw new Exception($response);
    }

    public function getPayUrl()
    {
        if (!$this->_transactionId)
            throw new Exception('call transaction method first');

        return self::PAY_URL . '?id=' . $this->_transactionId;
    }

    public function payment()
    {
        header('Location: ' . $this->getPayUrl());
    }

    public function checkPayment($params, $amount, $rand)
    {
        if (isset($params['trans_id'], $params['valid'])) {
            $valid = md5($this->_gatewayId . $amount . $this->_gatewayApi . $rand);
            if ($valid === trim($params['valid'])) {
                return true;
            }
        }

        if (isset($params['error']) && isset(self::$errorCodes[$params['error']]))
            throw new Exception (self::$errorCodes[$params['error']]);
        
        throw new Exception('خطا در پرداخت');
    }

}
