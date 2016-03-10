<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');
class Phpfox_Gateway_Api_Paypal implements Phpfox_Gateway_Interface
{
	private $_aParam = array();
	private $_aCurrency = array('USD', 'GBP', 'EUR', 'AUD', 'CAD', 'JPY', 'NZD', 'CHF', 'HKD', 'SGD', 'SEK', 'DKK', 'PLN', 'NOK', 'HUF', 'CZK', 'ILS', 'MXN', 'BRL', 'MYR', 'PHP', 'TWD', 'THB');
	public function __construct()
	{
		session_start();
	}	
	
	public function set($aSetting)
	{
		$this->_aParam = $aSetting;
		if (Phpfox::getLib('parse.format')->isSerialized($aSetting['setting']))
		{
			$this->_aParam['setting'] = unserialize($aSetting['setting']);
		}
	}
	
	public function getEditForm()
	{
		return array(
			'bank10_api' => array(
				'phrase' => 'bank10 API',
				'phrase_info' => 'insert your bank10 api.',
				'value' => (isset($this->_aParam['setting']['bank10_api']) ? $this->_aParam['setting']['bank10_api'] : '')
			),
			'bank10_id' => array(
				'phrase' => 'bank10 id',
				'phrase_info' => 'insert your bank10 id.',
				'value' => (isset($this->_aParam['setting']['bank10_id']) ? $this->_aParam['setting']['bank10_id'] : '')
			)
		);
	}
	
	public function getForm()
	{
		if (!in_array($this->_aParam['currency_code'], $this->_aCurrency))
		{
			if (isset($this->_aParam['alternative_cost']))
			{
				$aCosts = unserialize($this->_aParam['alternative_cost']);
				$bPassed = false;
				foreach ($aCosts as $sCode => $iPrice)
				{
					if (in_array($sCode, $this->_aCurrency))
					{
						$this->_aParam['amount'] = $iPrice;
						$this->_aParam['currency_code'] = $sCode;
						$bPassed = true;
						break;
					}
				}

				if ($bPassed === false)
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

        $bank10_id = $this->_aParam['setting']['bank10_id'];
        $amount = $this->_aParam['amount'];
        $ReturnPath =  Phpfox::getLib('gateway')->url('paypal').'&ok';//$this->_aParam['return'];
		$rand = substr(md5(time() . microtime()), 0, 10);
		Phpfox::getLib('session')->set('rand', $rand);
		Phpfox::getLib('session')->set('amount', $amount);
		Phpfox::getLib('session')->set('item_number', $this->_aParam['item_number']);

		$params = 'gateway_id='.$bank10_id.'&amount='.$amount.'&redirect_url='.$ReturnPath.'&rand='.$rand;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		if ($result > 0 && is_numeric($result)) 
		{
			$aForm = array(
			'url' =>  "http://10bank.ir/transaction/submit?id=".$result ,
			'param' => array(
			'a' => 1
			)
			); 
			return $aForm;
		}
		else
		{
			return false;
		}
       
	}
	
	public function callback()
	{
		Phpfox::log('Starting Paypal callback');

        $messagePage = '<html xmlns="http://www.w3.org/1999/xhtml">
        <head runat="server">
            <title>نتيجه پرداخت </title>
            <meta http-equiv="Content-Type" content="Type=text/html; charset=utf-8" />
        </head>
        <body style="text-align:center">
            <br/><br/><br/><br/>
            <div style="border: 1px solid;margin:auto;padding:15px 10px 15px 50px; width:600px;font-size:8pt; line-height:25px;$Style$">
             $Message$
            </div> <br /></br> <a href="./index.php" style="font:size:8pt ; color:#333333; font-family:tahoma; font-size:7pt" >بازگشت به صفحه اصلي</a>
        </body>
        </html>';

        $style = 'font-family:tahoma; text-align:right; direction:rtl';
        $style_succ = 'color: #4F8A10;background-color: #DFF2BF;'.$style;
        $style_alrt = 'color: #9F6000;background-color: #FEEFB3;'.$style;
        $style_errr = 'color: #D8000C;background-color: #FFBABA;'.$style;

        $bank10_id = $this->_aParam['setting']['bank10_id'];
        $bank10_api = $this->_aParam['setting']['bank10_api'];

		$rand = $amount = $item_number = null;
		if(Phpfox::getLib('session')->get('rand') == null)
		{
			$sSessionSavePath = (PHPFOX_OPEN_BASE_DIR ? PHPFOX_DIR_FILE . 'session' . PHPFOX_DS : session_save_path());
			$ses = file_get_contents($sSessionSavePath.'sess_'.$_COOKIE['PHPSESSID']);
			$ses = str_replace('coreeca6|','',$ses);
			$ses = unserialize($ses);
			$rand = $ses['rand'];
			$amount = $ses['amount'];
			$item_number = $ses['item_number'];
			
		}else
		{
			$rand = Phpfox::getLib('session')->get('rand');
			$amount = Phpfox::getLib('session')->get('amount');
			$item_number = Phpfox::getLib('session')->get('item_number');
		}
        Phpfox::log('Attempting callback');
		$referenceId = (int) ($_GET['trans_id']);
		$valid = ($_GET['valid']);

        if($valid)
        {
			 
			$aParts = explode('|',$item_number);
			
			$verify_valid = md5($bank10_id.$amount.$bank10_api.$rand) == $valid;
			 
			if($verify_valid) // Your Peyment Code Only This Event
			{
				Phpfox::log('Callback OK');

				Phpfox::log('Attempting to load module: ' . $aParts[0]);

				if (Phpfox::isModule($aParts[0]))
				{
					Phpfox::log('Module is valid.');
					Phpfox::log('Checking module callback for method: paymentApiCallback');
					if (Phpfox::hasCallback($aParts[0], 'paymentApiCallback'))
					{
						Phpfox::log('Module callback is valid.');

						$sStatus = 'completed';

						Phpfox::log('Status built: ' . $sStatus);

						Phpfox::log('Executing module callback');
						Phpfox::callback($aParts[0] . '.paymentApiCallback', array(
								'gateway' => 'paypal',
								'ref' => $referenceId,
								'status' => $sStatus,
								'item_number' => $aParts[1],
								'total_paid' => $amount
							)
						);
						Phpfox::getLib('session')->set('rand', 1);
						Phpfox::getLib('session')->set('amount',1);
						Phpfox::getLib('session')->set('item_number', 1);
						header('HTTP/1.1 200 OK');
						$mss = 'کاربر گرامي ، عمليات پرداخت با موفقيت به پايان رسيد .<br><br>جهت پيگيري هاي آتي شماره رسيد پرداخت خود را ياداشت فرماييد : '.$referenceId.'<br> با تشکر <br>';
						$messagePage = str_replace('$Message$',$mss,$messagePage);
						$messagePage = str_replace('$Style$',$style_succ,$messagePage);
						echo $messagePage;

						return;
					}
					else
					{
						Phpfox::log('Module callback is not valid.');
					}
				}
				else
				{
					Phpfox::log('Module is not valid.');
				}
			}
			else
			{
				Phpfox::log('Callback '.$Status);
				$sStatus = $Status;
				$mss = 'کاربر گرامي ، عمليات  اعتبار سنجي پرداخت شما با خطا مواجه گرديد .<br>';
				$messagePage = str_replace('$Message$',$mss,$messagePage);
				$messagePage = str_replace('$Style$',$style_alrt,$messagePage);
				echo $messagePage;
				return;
			}
		}
		else
		{
			Phpfox::log('Callback FAILED');
			$sStatus = 'cancel';
			header('HTTP/1.1 200 OK');
			$mss = 'پرداخت ناموفق / خطا در عمليات پرداخت ! کاربر گرامي ، فرايند پرداخت با خطا مواجه گرديد !<br>';
			$messagePage = str_replace('$Message$',$mss,$messagePage);
			$messagePage = str_replace('$Style$',$style_errr,$messagePage);
			echo $messagePage;
		}
    }
	

}
?>