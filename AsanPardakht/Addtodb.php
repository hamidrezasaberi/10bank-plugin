<?php
        $act = $_POST['action'];

//$act = filter_input($_POST,action) ;

        function addpayment($_post)
        {
            $setting = include 'data/Settings.php';
            extract($setting);
            $db = new PDO($dbstring, $dbuser, $dbpass);
            $name = $_post['name'];
            $email = $_post['email'];
            $price = $_post['price'];
            $time = time();
            $amount = $price; //RIAL
            $rand = substr(md5(time() . microtime()), 0, 16);
            $description = 'آزمایش پرداخت'; //optional
            $My_valid = md5($gateway_id . $amount . $gateway_api . $rand);
            $command = $db->prepare("INSERT INTO bank10_payment(name, email, price, time, rand) values (:name,:email,:price,:time,:rand)");
            $command->bindValue(':name', $name);
            $command->bindValue(':email', $email);
            $command->bindValue(':price', $price, PDO::PARAM_INT);
            $command->bindValue(':time', $time);
            $command->bindValue(':rand', $My_valid);

            if (!$command->execute())
                die('error');

            $redirect_url = baseUrl() . "/callback.php?i=1";
            //Transaction id
            $t_id = curl_func($gateway_id, $amount, $redirect_url, $rand, $description);
            if ($t_id > 0) {
                //SAVE DATA TO DATABASE
                $url = 'http://10bank.ir/transaction/submit?id=' . $t_id;
                header("location: $url");
            } else {
                //Show error
                echo $t_id;
            }
        }

        function curl_func($gateway_id, $amount, $redirect_url, $rand, $description = null)
        {

            $data = array(
                'gateway_id' => $gateway_id,
                'amount' => $amount,
                'redirect_url' => $redirect_url,
                'rand' => $rand,
                'description' => $description
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);
            curl_close($ch);
            return $res;
        }

        if ($act == "payment") {
            addpayment($_POST);
        }

        function baseUrl()
        {

            $secure = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';

            $http = $secure ? 'https' : 'http';

            if (isset($_SERVER['HTTP_HOST']))
                $hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            else
                $hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];


            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
            if (basename($_SERVER['SCRIPT_NAME']) === $scriptName)
                $scriptUrl = $_SERVER['SCRIPT_NAME'];
            elseif (basename($_SERVER['PHP_SELF']) === $scriptName)
                $scriptUrl = $_SERVER['PHP_SELF'];
            elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName)
                $scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false)
                $scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
            elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0)
                $scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
            else
                die('error');

            $baseUrl = rtrim(dirname($scriptUrl), '\\/');

            $url = $hostInfo . $baseUrl;

            return $url;
        }