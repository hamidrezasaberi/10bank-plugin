<?php

/*
Plugin Name: ماژول یوپال برای eShop
Plugin URI: http://www.10bank.ir
* Author: hamidrezasaberi
*/

register_activation_hook(__FILE__,'eshopbank10_activate');

function eshopbank10_activate(){
	/*
	* Activation routines
	*/
	global $wpdb;
	$opts=get_option('active_plugins');
	$eshopthere=false;

	foreach($opts as $opt){
		if($opt=='eshop/eshop.php')
			$eshopthere=true;
	}

	if($eshopthere==false){
		deactivate_plugins('eshop-bank10-mg.php'); //Deactivate ourself
		wp_die(__('خطا افزونه eShop فعال نیست.','eshop')); 
	}

	/*
	* Insert email template for use with this merchant gateway, if 151 is changed, then bank10.php needs amending as well 
	*/
	$table = $wpdb->prefix ."eshop_emails";
	$esubject=__('سفارش شما در سایت  ','eshop').get_bloginfo('name');
	$wpdb->query("INSERT INTO ".$table." (id,emailType,emailSubject) VALUES ('151','".__('Automatic bank10 email','eshop')."','$esubject')"); 

}

add_action('eshop_setting_merchant_load','eshopmgpage');

function eshopmgpage($thist){
	/*
	* Adding the meta box for this gateway
	*/
	add_meta_box('eshop-m-bank10', __('bank10','eshop'), 'bank10_box', $thist->pagehook, 'normal', 'core');
}

function bank10_box($eshopoptions) {

	/*
	* The meta box content, obviously you have to set up the required fields for your gateway here
	*/

	if(isset($eshopoptions['bank10'])){
		$eshopbank10 = $eshopoptions['bank10']; 
	}else{
		$eshopbank10['email']='';
		$eshopbank10['id']='';
		$eshopbank10['secret']='';
		$eshopbank10['key']='';
		$eshopbank10['_method']='';
		$eshopbank10['desc']='';
	}

	// Add the image
	$eshopmerchantimgpath=WP_PLUGIN_DIR.'/eshop-bank10-mg/bank10.png';
	$eshopmerchantimgurl=WP_PLUGIN_URL.'/eshop-bank10-mg/bank10.png';
	$dims[3]='';
	if(file_exists($eshopmerchantimgpath))
	$dims=getimagesize($eshopmerchantimgpath);
	echo '<fieldset>';
echo '<p class="eshopgateway"><img src="'.$eshopmerchantimgurl.'" '.$dims[3].' alt="CCNow" title="CCNow" /></p><br/><br/>'."\n";

?>

<?php 
	$selected_C = '';
	$selected_P = '';
	$selected_T = '';
	$selected_N = '';

	if($eshopbank10['_method'] == 'CC') {
		$selected_C = ' selected="selected"';
	} else if($eshopbank10['_method'] == 'PAYPAL') {
		$selected_P = ' selected="selected"';
	} else if($eshopbank10['_method'] == 'TEST') {
		$selected_T = ' selected="selected"';
	} else if($eshopbank10['_method'] == 'NONE') {
		$selected_N = ' selected="selected"';
	}
?>
	<p class="cbox"><input id="eshop_methodbank10" name="eshop_method[]" type="checkbox" value="bank10"<?php if(in_array('bank10',(array)$eshopoptions['method'])) echo ' checked="checked"'; ?> /><label for="eshop_methodbank10" class="eshopmethod"><?php _e('یوپال فعال باشد ؟','eshop'); ?></label></p>
	<label for="eshop_bank10email"><?php _e('ایمیل مدیر سایت','eshop'); ?></label><input id="eshop_bank10email" name="bank10[email]" type="text" value="<?php echo $eshopbank10['email']; ?>" size="30" maxlength="50" /><br />
	<label for="eshop_bank10id"><?php _e('id درگاه','eshop'); ?></label><input id="eshop_bank10id" name="bank10[id]" type="text" value="<?php echo $eshopbank10['id']; ?>" size="30" maxlength="50" /><br />
	<label for="eshop_bank10api"><?php _e('api درگاه','eshop'); ?></label><input id="eshop_bank10api" name="bank10[api]" type="text" value="<?php echo $eshopbank10['api']; ?>" size="30" maxlength="50" /><br />

	<br />

	</fieldset>

<?php

}

add_filter('eshop_setting_merchant_save','bank10save',10,2);

function bank10save($eshopoptions,$posted){

	/*
	* Save routine for the fields you added above
	*/

	global $wpdb;
	$bank10post['email']=$wpdb->escape($posted['bank10']['email']);
	$bank10post['id']=$wpdb->escape($posted['bank10']['id']);
	$bank10post['api']=$wpdb->escape($posted['bank10']['api']);


	$eshopoptions['bank10']=$bank10post;
	return $eshopoptions;
}

add_action('eshop_include_mg_ipn','eshopbank10');

function eshopbank10($eshopaction){

	/* Adding the necessary link for the instant payment notification of your gateway */
	if($eshopaction=='bank10ipn'){
		include_once WP_PLUGIN_DIR.'/eshop-bank10-mg/bank10.php';
	}
}

add_filter('eshop_merchant_img_bank10','bank10img');
function bank10img($array){

	/* Adding the image for this gateway, for use on the front end of the site	*/
	$array['path']=WP_PLUGIN_DIR.'/eshop-bank10-mg/bank10.png';
	$array['url']=WP_PLUGIN_URL.'/eshop-bank10-mg/bank10.png';
	return $array;

}
add_filter('eshop_mg_inc_path','bank10path',10,2);

function bank10path($path,$paymentmethod){
	/* Adding another necessary link for the instant payment notification of your gateway */
	if($paymentmethod=='bank10')
		return WP_PLUGIN_DIR.'/eshop-bank10-mg/bank10.php';
	return $path;

}

add_filter('eshop_mg_inc_idx_path','bank10idxpath',10,2);
function bank10idxpath($path,$paymentmethod){
	/* Adding the necessary link to the class for this gateway */
	if($paymentmethod=='bank10')
		return WP_PLUGIN_DIR.'/eshop-bank10-mg/bank10-class.php';
	return $path;
}

// Message on fail.
add_filter('eshop_show_success', 'eshop_bank10_return_fail',10,3);
function eshop_bank10_return_fail($echo, $eshopaction, $postit){
	/* Payment failed */
	// Success codes, all others fail
	$bank10rescodes=array('00','08','10','11','16');
	if($eshopaction=='bank10ipn'){
		if($postit['bank10TrxnStatus']=='False' && !in_array($postit['bank10responseCode'],$bank10rescodes))
			$echo .= '<p>There was a problem with your order, please contact admin@ ... quoting Error Code '.$postit['bank10responseCode']."</p>\n";
	}
	return $echo;
}

?>