<?php

if ('bank10-class.php' == basename($_SERVER['SCRIPT_FILENAME']))

     die ('<h2>Direct File Access Prohibited</h2>');


class bank10_class {

   var $last_error;                 // holds the last error encountered
   var $ipn_response;               // holds the IPN response from CCNow
   var $ipn_data = array();         // array contains the POST values for IPN
   var $fields = array();           // array holds the fields to submit to CCNow

   function bank10_class() {
      $this->last_error = '';
      $this->ipn_response = '';
   }

   function add_field($field, $value) {
      // Creates an key=>value pair array, which will be sent to CCNow as POST variables
      $this->fields["$field"] = $value;
   }

   function submit_bank10_post() {
      // Redirect user to CCNow.
      $echo= "<form method=\"post\" class=\"eshop eshop-confirm\" action=\"".$this->autoredirect."\"><div>\n";

	  // Get the POST data
      foreach ($this->fields as $name => $value) {
			$pos = strpos($name, 'amount');
			if ($pos === false) {
			   $echo.= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
			}else{
				$echo .= eshopTaxCartFields($name,$value);
      	    }
      }

      // Changes the standard text of the redirect page.
      $refid=uniqid(rand());
      $echo .= "<input type=\"hidden\" name=\"bank10option1\" value=\"$refid\" />\n";
      $echo .= '<label for="ppsubmit" class="finalize"><small>'.__('<strong> ثبت و پرداخت توسط بانک ۱۰.</strong>','eshop').'</small><br />
      		    <input class="button submit2" type="submit" id="ppsubmit" name="ppsubmit" value="'.__('اتصال به درگاه &raquo;','eshop').'" /></label>';
	  $echo .= "</div></form>\n";

      return $echo;
   }

	function eshop_submit_bank10_post($_POST) {

      // Get the POST data
      global $wpdb, $eshopoptions, $blog_id;

		$echo_bank10='<div id="process">
         <p><strong>'.__(' لطفا صبر کنید &#8230;','eshop').'</strong></p>
	     <p>'. __('اگر تا چند ثانیه به درگاه بانک ۱۰ متصل نشدید کلیک کنید.','eshop').'</p>
         <form method="post" id="eshopgatbank10" class="eshop" action="'.$this->bank10_url.'">
          <p>';

		$bank10 = $eshopoptions['bank10'];
		$decimal_places = 0;


		$sequence	= rand(1, 1000);
		$fp_arg_list = 'x_login^x_fp_arg_list^x_fp_sequence^x_amount^x_currency_code';

		// Fingerprint
		$fingerprint = md5($bank10['id'] . '^' . $fp_arg_list . '^' . $sequence . '^' . $_POST['amount'] . '^' . $eshopoptions['currency'] . '^' . $bank10['key']);

		// General info
		$echo_bank10.='
			<input type="hidden" name="x_login" value="'.$bank10['id'].'" />
			<input type="hidden" name="x_version" value="1.0">
			<input type="hidden" name="x_fp_sequence" value="'.$sequence.'">
			<input type="hidden" name="x_fp_arg_list" value="'.$fp_arg_list.'">
			<input type="hidden" name="x_fp_hash" value="'.$fingerprint.'">
			<input type="hidden" name="x_currency_code" value="'.$eshopoptions['currency'].'">
			<input type="hidden" name="x_method" value="'.$bank10['_method'].'">
			<input type="hidden" name="x_amount" value="'.number_format($_POST['amount'], $decimal_places, '', '').'">';

			// Products info
			for ($zz=1; $zz <= $_POST['numberofproducts']; $zz++) {
				$echo_bank10.='
					<input type="hidden" name="x_product_sku_'.$zz.'" value="'.$_POST['eshopident_'.$zz].'">
					<input type="hidden" name="x_product_title_'.$zz.'" value="'.$_POST['item_name_'.$zz].'">
					<input type="hidden" name="x_product_quantity_'.$zz.'" value="'.$_POST['quantity_'.$zz].'">
					<input type="hidden" name="x_product_unitprice_'.$zz.'" value="'.number_format(($_POST['amount_'.$zz]), $decimal_places, '', '').'">
				';

					// Fetching product url from posts table
					$product_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $wpdb->posts . "` WHERE `id`= %d LIMIT 1", $_POST['postid_'.$zz]), ARRAY_A);
					$product_data = $product_data[0];

				$echo_bank10.='
					<input type="hidden" name="x_product_url_'.$zz.'" value="'.$product_data['guid'].'">
				';
			}

			// Fetch user info for this order
			$_cost=$_POST['amount'];
			$_theid=$eshopoptions['bank10']['id'];
			$_cost=number_format($_cost,2);
			$_checkid=md5($_POST['bank10option1'].$_theid.'$'.$_cost);

			$user_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $wpdb->prefix.'eshop_orders' . "` WHERE `checkid`= %d LIMIT 1", $_checkid), ARRAY_A);
			$user_info = $user_info[0];
			$wpdb->update($wpdb->prefix.'eshop_orders', array('status' => 'Waiting'), array('checkid' => $_checkid), array('%d', '%s'));

			// Taxes
			$_tax_data['x_tax_amount']   = number_format($eshopoptions['tax_1'] + $eshopoptions['tax_2'] + $eshopoptions['tax_3'], $decimal_places, '.', '');

			if($_tax_data['x_tax_amount'] > 0){
				$echo_bank10 .= '
				   	<input type="hidden" name="x_tax_amount" value="'.number_format($eshopoptions['tax_1'] + $eshopoptions['tax_2'] + $eshopoptions['tax_3'], $decimal_places, '.', '').'">
				   	<input type="hidden" name="x_tax_label" value="Tax">
				';
			}

			$echo_bank10.='
				<input type="hidden" name="x_name" value="'.$user_info['first_name'].' '.$user_info['last_name'].'">
				<input type="hidden" name="x_company" value="'.$user_info['company'].'">
				<input type="hidden" name="x_address" value="'.$user_info['address1'].'">
				<input type="hidden" name="x_address2" value="'.$user_info['address2'].'">
				<input type="hidden" name="x_country" value="'.$user_info['country'].'">
				<input type="hidden" name="x_city" value="'.$user_info['city'].'">
				<input type="hidden" name="x_state" value="'.$user_info['state'].'">
				<input type="hidden" name="x_zip" value="'.$user_info['zip'].'">
				<input type="hidden" name="x_phone" value="'.$user_info['phone'].'">
				<input type="hidden" name="x_email" value="'.$user_info['email'].'">

				<input type="hidden" name="x_ship_to_name" value="'.$user_info['ship_name'].'">
				<input type="hidden" name="x_ship_to_company" value="'.$user_info['ship_company'].'">
				<input type="hidden" name="x_ship_to_address" value="'.$user_info['ship_address'].'">
				<input type="hidden" name="x_ship_to_address2" value="'.$user_info['ship_address'].'">
				<input type="hidden" name="x_ship_to_country" value="'.$user_info['ship_country'].'">
				<input type="hidden" name="x_ship_to_city" value="'.$user_info['ship_city'].'">
				<input type="hidden" name="x_ship_to_state" value="'.$user_info['ship_state'].'">
				<input type="hidden" name="x_ship_to_zip" value="'.$user_info['ship_postcode'].'">
				<input type="hidden" name="x_ship_to_phone" value="'.$user_info['ship_phone'].'">';

			// Miscelanous info - optional fields
			$echo_bank10 .='
				<input type="hidden" name="x_invoice_num" value="'.$_POST['bank10option1'].'">
                                <input type="hidden" name="x_instructions" value="'.@$_POST['comments'].'">
				<input class="button" type="submit" id="ppsubmit" name="ppsubmit" value="'. __('پرداخت با بانک ۱۰ &raquo;','eshop').'" /></p>
	     </form>

	  </div>
	  <script type="text/javascript">document.getElementById("eshopgatbank10").submit();</script>';
		return $echo_bank10;
   }   

   function validate_ipn() {
      // generate the POST string from the _POST vars also load the
      // _POST vars into an array so we can use them from the calling script.
      foreach ($_REQUEST as $field=>$value) { 
         $this->ipn_data["$field"] = $value;
      }     
   }

}  

?>