<?php
/**
 * @ Author www.10bank.ir
 * @ Copyright 2015
 */
if(file_exists("dbconnect.php"))
	{
		include("dbconnect.php");
		include("includes/functions.php");
	}else
	{
		include("init.php");
	}

include("includes/gatewayfunctions.php");
include("includes/invoicefunctions.php");
	

$invoiceid=intval($_POST['invoiceid']);
$systemurl=$_POST['systemurl'];
$callBackUrl = ($systemurl.'/modules/gateways/callback/bank10.php?invoiceid='.$invoiceid);
$results = select_query( "tblinvoices", "", array( "id" => $invoiceid ) );
$data = mysql_fetch_array( $results );

$GATEWAY = getGatewayVariables('bank10');
$gateway_api = trim($GATEWAY['gateway_api']);
$gateway_id = trim($GATEWAY['gateway_id']);
$Currencies = trim($GATEWAY['Currencies']);

$amount = $data['total']; 
$amount=strtok($amount,'.');
if($Currencies != 'Rial')
	$amount = $amount * 10;

$rand = substr(md5(time() . microtime()), 0, 10);	
$params = 'gateway_id=' . $gateway_id . '&amount=' . $amount . '&redirect_url=' . $callBackUrl . '&rand=' . $rand;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);

if ($res > 0 && is_numeric($res)) 
{
	update_query( "tblinvoices", array( "notes" => $rand ), array( "id" => $invoiceid ) );
    $go = 'http://10bank.ir/transaction/submit?id='.$res;
    header("location: $go");
	die("<script>window.location='$go';</script>");
}

?>