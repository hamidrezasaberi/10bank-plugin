<?php
	/**
	* @ Author www.10bank.ir
	* @ Copyright 2015
	*/

	if(file_exists("../../../dbconnect.php"))
	{
		include("../../../dbconnect.php");
		include("../../../includes/functions.php");
	}else
	{
		include("../../../init.php");
		
	}
	
	include("../../../includes/gatewayfunctions.php");
	include("../../../includes/invoicefunctions.php");


	$gatewaymodule = "bank10"; 
	$GATEWAY = getGatewayVariables($gatewaymodule);

	if (!$GATEWAY["type"]) die("Module Not Activated"); 
	
	$invoiceNumber = $_GET['invoiceid'];
	$trans_id = $_GET["trans_id"];
	$valid = $_GET['valid'];

	$invoiceNumber = checkCbInvoiceID($invoiceNumber,$GATEWAY["name"]); 


	$results = select_query( "tblinvoices", "", array( "id" => $invoiceNumber ) );
	$data = mysql_fetch_array( $results );
	$db_amount = strtok($data['total'],'.');
	
	if($valid && $db_amount > 0)
	{
		checkCbTransID($trans_id); 
		$gateway_api = trim($GATEWAY['gateway_api']);
		$gateway_id = trim($GATEWAY['gateway_id']);
		$Currencies = trim($GATEWAY['Currencies']);
		if($Currencies != 'Rial')
			$db_amount = $db_amount * 10;
		
		$rand = $data['notes'];
		$verify_valid = md5($gateway_id . $db_amount . $gateway_api . $rand) == $valid;
		if ($verify_valid)
		{
			update_query( "tblinvoices", array( "notes" => '' ), array( "id" => $invoiceNumber ) );
			$transid = $trans_id ;
			$fee = 0;
			 if($Currencies != 'Rial')
				$db_amount = $db_amount / 10;
			addInvoicePayment($invoiceNumber,$transid,$db_amount,$fee,$gatewaymodule); 
			logTransaction($gatewaymodule,$_GET,"Successful"); 
			$url = $CONFIG['SystemURL'].'/viewinvoice.php?id='.$invoiceNumber;
			Header('Location: '.$CONFIG['SystemURL'].'/viewinvoice.php?id='.$invoiceNumber);
			die("<script>window.location='$url';</script>");
		}
	}

	logTransaction($gatewaymodule,$_GET,"Unsuccessful"); 
	$url = $CONFIG['SystemURL'].'/viewinvoice.php?id='.$invoiceNumber;
	Header('Location: '.$CONFIG['SystemURL'].'/viewinvoice.php?id='.$invoiceNumber);
	die("<script>window.location='$url';</script>");
	 
?>