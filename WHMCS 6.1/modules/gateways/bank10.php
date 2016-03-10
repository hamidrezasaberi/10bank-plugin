<?php
/**
     * @ Author www.10bank.ir
     * @ Copyright 2015
     */

function bank10_config() {
	$configarray = array(
	"FriendlyName" => array("Type" => "System", "Value"=>"درگاه پرداخت بانک10"),
	"gateway_id" => array("FriendlyName" => "gateway_id", "Type" => "text", "Size" => "50", ),
	"gateway_api" => array("FriendlyName" => "gateway_api", "Type" => "text", "Size" => "50", ),
	"Currencies" => array("FriendlyName" => "Currencies", "Type" => "dropdown", "Options" => "Rial,Toman", ),
	);
	return $configarray;
}

function bank10_link($params) {

	$invoiceid = $params['invoiceid'];
	$companyname = $params['companyname'];
	$systemurl = $params['systemurl'];
	$callBackUrl = ($params['systemurl'].'/modules/gateways/callback/bank10.php?invoiceid='.$invoiceid);

	$code = '
    	<form method="post" action="./bank10.php">
        <input type="hidden" name="invoiceid" value="'.$invoiceid.'" />
        <input type="hidden" name="systemurl" value="'.$systemurl.'" />
        <input type="submit" name="pay" value="برای پرداخت کلیک کنید" />
    	</form>';
	return $code;
}
?>