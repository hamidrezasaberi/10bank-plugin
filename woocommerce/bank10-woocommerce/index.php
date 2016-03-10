<?php
/*
Plugin Name: درگاه بانک10
Plugin URI: http://10bank.ir
Description: 10bank.ir
Version: 1.0
Author: www.10bank.ir
Author URI: http://www.10bank.ir
Copyright: 2015 10bank.ir
*/

add_action('plugins_loaded', 'woocommerce_bank10_init', 0);

function woocommerce_bank10_init() 
{
    if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
	if($_GET['msg']!=''){add_action('the_content', 'showMessagebank10');}
	function showMessagebank10($content)
	{
			return '<div class="box '.htmlentities($_GET['type']).'-box">'.base64_decode($_GET['msg']).'</div>'.$content;
	}
    class WC_bank10 extends WC_Payment_Gateway 
	{
		protected $msg = array();
        public function __construct()
		{
            $this->id = 'bank10';
            $this->method_title = __('درگاه bank10', 'bank10');
            $this->has_fields = false;
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->settings['title'];
            $this->description = $this->settings['description'];
            $this->merchant_id = $this->settings['merchant_id'];
			$this->vahed = $this->settings['vahed'];
			$this->gates = $this->settings['gates'];
            $this->redirect_page_id = $this->settings['redirect_page_id'];
            $this->gateway_api = $this->settings['gateway_api'];
            $this->gateway_id = $this->settings['gateway_id'];
            $this->msg['message'] = "";
            $this->msg['class'] = "";
			add_action( 'woocommerce_api_wc_bank10', array( $this, 'check_bank10_response' ) );
            add_action('valid-bank10-request', array($this, 'successful_request'));
			
            if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) 
			{
                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            } else 
			{
                add_action( 'woocommerce_update_options_payment_gateways', array( $this, 'process_admin_options' ) );
            }
			
            add_action('woocommerce_receipt_bank10', array($this, 'receipt_page'));
        }

        function init_form_fields()
		{

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('فعال سازی/غیر فعال سازی', 'bank10'),
                    'type' => 'checkbox',
                    'label' => __('فعال سازی درگاه پرداخت bank10', 'bank10'),
                    'default' => 'no'),
                'title' => array(
                    'title' => __('عنوان', 'bank10'),
                    'type'=> 'text',
                    'description' => __('عنوانی که کاربر در هنگام پرداخت مشاهده می کند', 'bank10'),
                    'default' => __('پرداخت اینترنتی bank10', 'bank10')),
                'description' => array(
                    'title' => __('توضیحات', 'bank10'),
                    'type' => 'textarea',
                    'description' => __('توضیحات قابل نمایش به کاربر در هنگام انتخاب درگاه پرداخت', 'bank10'),
                    'default' => __('پرداخت از طریق درگاه bank10 با کارت های عضو شتاب', 'bank10')),
                'gateway_id' => array(
                    'title' => __('id درگاه یوپال', 'bank10'),
                    'type' => 'text',
                    'description' => __('id درگاه را وارد کنید')), 
				'gateway_api' => array(
                    'title' => __('api درگاه یوپال', 'bank10'),
                    'type' => 'text',
                    'description' => __('API درگاه را وارد کنید')),
				'vahed' => array(
                    'title' => __('واحد پولی'),
                    'type' => 'select',
                    'options' => array(
					'rial' => 'ریال',
					'toman' => 'تومان'
					),
                    'description' => "نیازمند افزونه ریال و تومان هست"),
                'redirect_page_id' => array(
                    'title' => __('صفحه بازگشت'),
                    'type' => 'select',
                    'options' => $this->get_pages('انتخاب برگه'),
                    'description' => "ادرس بازگشت از پرداخت در هنگام پرداخت"
                )
            );


        }

        public function admin_options()
		{
            echo '<h3>'.__('درگاه پرداخت bank10', 'bank10').'</h3>';
            echo '<p>'.__('درگاه پرداخت اینترنتی bank10').'</p>';
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }
		
        function payment_fields()
		{
            if($this->description) echo wpautop(wptexturize($this->description));
        }

        function receipt_page($order)
		{
            echo '<p>'.__('در حال اتصال به درگاه بانک10', 'bank10').'</p>';
            echo $this->generate_bank10_form($order);
        }

        function process_payment($order_id)
		{
            $order = &new WC_Order($order_id);
            return array('result' => 'success', 'redirect' => $order->get_checkout_payment_url( true )); 
        }

       function check_bank10_response()
		{
            global $woocommerce;
			$order_id = $woocommerce->session->order_id;
			$rand = $woocommerce->session->rand;
			$order = &new WC_Order($order_id);
			if($order_id != '')
			{
				if(strtolower($order->status) !='completed')
				{

					$amount = (int) ($_GET['amount']);
					$referenceId = (int) ($_GET['trans_id']);
					$valid = ($_GET['valid']);
					$amountdb = str_replace(".00", "", $order->order_total);
						if($this->vahed!='rial')
							$amountdb = $amountdb * 10;
					$verify_valid = md5($this->gateway_id.$amountdb.$this->gateway_api.$rand) == $valid;
					if ($verify_valid)
					{
						$this->msg['message'] = "پرداخت شما با موفقیت انجام شد<br/> کد ارجاع : $referenceId";
						$this->msg['class'] = 'success';
						$order->payment_complete();
						$order->add_order_note('پرداخت انجام شد<br/> - کد ارجاع : '.$referenceId );
						$order->add_order_note($this->msg['message']);
						$woocommerce->cart->empty_cart();
					}
					else
					{
						$this->msg['class'] = 'error';
						$this->msg['message'] = "پرداخت با موفقيت انجام نشد";
					}
					
				}
				else
				{
					$this->msg['class'] = 'error';
					$this->msg['message'] = "قبلا اين سفارش به ثبت رسيده يا صفارشي موجود نيست!";
				}
			}
			$redirect_url = ($this->redirect_page_id=="" || $this->redirect_page_id==0)?get_site_url() . "/":get_permalink($this->redirect_page_id);
			$redirect_url = add_query_arg( array('msg'=> base64_encode($this->msg['message']), 'type'=>$this->msg['class']), $redirect_url );
			wp_redirect( $redirect_url );
			exit;
		}
		
        function showMessage($content)
		{
            return '<div class="box '.$this->msg['class'].'-box">'.$this->msg['message'].'</div>'.$content;
        }
		private function curl_func($params) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$res = curl_exec($ch);
			curl_close($ch);
			return $res;
		}
        public function generate_bank10_form($order_id)
		{
            global $woocommerce;
            $order = new WC_Order($order_id);
            $redirect_url = ($this->redirect_page_id=="" || $this->redirect_page_id==0)?get_site_url() . "/":get_permalink($this->redirect_page_id);
			$redirect_url = add_query_arg( 'wc-api', get_class( $this ), $redirect_url );
			unset( $woocommerce->session->order_id );
			$woocommerce->session->order_id = $order_id;
			$amount = str_replace(".00", "", $order->order_total);
			if($this->vahed!='rial')
				$amount = $amount * 10;
			$redirect = ($redirect_url); 
			
			$rand = substr(md5(time() . microtime()), 0, 10);
			$params = 'gateway_id='.$this->gateway_id.'&amount='.$amount.'&redirect_url='.$redirect_url.'&rand='.$rand;
			
			$result = $this->curl_func($params);

			if ($result > 0 && is_numeric($result)) 
			{
				$woocommerce->session->rand = $rand;
				$go = "http://10bank.ir/transaction/submit?id=" . $result;
				header("Location: $go");
				exit;
			} else 
			{
				$Note = sprintf( __( 'خطا در هنگام ارسال به بانک : %s', 'woocommerce'), $result );
				$Note = apply_filters( 'WC_bank10_Send_to_Gateway_Failed_Note', $Note, $order_id, $result );
				$order->add_order_note( $Note );
				$Notice = sprintf( __( 'در هنگام اتصال به بانک خطای زیر رخ داده است : <br/>%s', 'woocommerce'), $result );
				$Notice = apply_filters( 'WC_bank10_Send_to_Gateway_Failed_Notice', $Notice, $order_id, $result );
				if ( $Notice )
					wc_add_notice( $Notice , 'error' );
				
				do_action('WC_bank10_Send_to_Gateway_Failed', $order_id, $result );
			}
		
        }
		
        function get_pages($title = false, $indent = true) 
		{
            $wp_pages = get_pages('sort_column=menu_order');
            $page_list = array();
            if ($title) $page_list[] = $title;
            foreach ($wp_pages as $page) 
			{
                $prefix = '';
                if ($indent) {
                    $has_parent = $page->post_parent;
                    while($has_parent) 
					{
                        $prefix .=  ' - ';
                        $next_page = get_page($has_parent);
                        $has_parent = $next_page->post_parent;
                    }
                }
                $page_list[$page->ID] = $prefix . $page->post_title;
            }
            return $page_list;
        }

    }

    function woocommerce_add_bank10_gateway($methods) 
	{
        $methods[] = 'WC_bank10';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_bank10_gateway' );
}

?>