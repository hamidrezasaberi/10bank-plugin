<?php

namespace app\components\modules\payment;

use framework\request\Request;
use framework\session\Session;
/**
 * payment with bank10
 *
 * @author		http://www.10bank.ir
 * @since		1.0
 * @package		payment module
 * @copyright   (c) 2015 all rights reserved
 */
class bank10 extends Payment
{
	/**
	 * request to bank10 gateway
	 *
	 * @param integer $id, trans id (primary key)
	 * @param integer $au, trans authority code
	 * @param integer $price, trans price
	 * @param array $module, information of this module
	 * @param integer $product, product id
	 *
	 * @access public  
	 * @return void 
	 */
	public function request( $id, $au, $price, $module, $product )
	{ 
		$session = Session::instance();
		$rand = substr(md5(time() . microtime()), 0, 10);
		$session->set('rand',$rand);
		$price = $price * 10;
		$params = 'gateway_id='.$module['id']['value'].'&amount='.$price.'&redirect_url='.$this->getCallbackUrl( $au, false ).'?'.'&rand='.$rand;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
	
		if( is_numeric( $result ) and $result > 0 ) {
			$this->updateAu( $id, $result );
			$this->redirect( "http://10bank.ir/transaction/submit?id=" . $result );
		} else {
			$this->setFlash( 'danger', $this->lang()->getIndex( 'bank10', 'error' ) . $result );
		}
	}

	/**
	 * request to bank10 for verify transaction
	 *
	 * @param integer $id, trans id (primary key)
	 * @param integer $au, trans authority code
	 * @param integer $price, trans price
	 * @param array $module, information of this module
	 * @param integer $product, product id
	 *
	 * @access public
	 * @return array|boolean
	 */
	public function verify($id,$au,$price,$module,$product)
	{

		if( !Request::isQuery( 'trans_id' ) OR !Request::isQuery( 'trans_id' ) ) {
			$this->setFlash( 'danger', $this->lang()->getIndex( 'bank10', 'inputNotValid' ) );
			return false;
		}
		if( !Request::isQuery( 'valid' ) OR !Request::isQuery( 'valid' ) ) {
			$this->setFlash( 'danger', $this->lang()->getIndex( 'bank10', 'inputNotValid' ) );
			return false;
		}
		$session = Session::instance();
		$rand = $session->get('rand');

		$price = $price * 10;
		$verify_valid = md5($module['id']['value'] . $price . $module['api']['value'] . $rand) == Request::getQuery( 'valid' );
		if( $verify_valid ) {
			return array( 'au' => Request::getQuery( 'trans_id' ) );
		} else {
			$this->setFlash( 'danger', $this->lang()->getIndex( 'bank10', 'error' ) .'تراکنش ناموفق' );
		}
	}


	/**
	 * module fields for install this
	 *
	 * @access public
	 * @return array
	 */
	public function fields()
	{
		return array(
			'id' => array(
				'label' => $this->lang()->getIndex( 'bank10', 'id' ),
				'value' => '',
			),
			'api' => array(
				'label' => $this->lang()->getIndex( 'bank10', 'api' ),
				'value' => '',
			),
		);
	}
}