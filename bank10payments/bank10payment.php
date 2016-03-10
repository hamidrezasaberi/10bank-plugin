<?php

if (!defined('_PS_VERSION_'))
    exit;

class bank10payment extends PaymentModule {

    private $_html = '';
    private $_postErrors = array();

    const _PAYLINE_ACTION_URL_ = 'http://payline.ir/payment/';

    public function __construct() {

        $this->name = 'bank10payment';
        $this->tab = 'payments_gateways';
        $this->version = '2.0';
        $this->author = 'iPresta.ir';
        $this->bootstrap = true;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('bank10 Payment');
        $this->description = $this->l('A free module to pay online for bank10.');
        $this->confirmUninstall = $this->l('Are you sure, you want to delete your details?');
        if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
            $this->warning = $this->l('No currency has been set for this module');

        $config = Configuration::getMultiple(array('IPRESTA_PAYLINE_PIN', ''));
        if (!isset($config['IPRESTA_PAYLINE_PIN']))
            $this->warning = $this->l('Your bank10 Pin Code must be configured in order to use this module');
    }

    public function install() {
        if (!parent::install()
                OR ! Configuration::updateValue('IPRESTA_PAYLINE_PIN', '')
                OR ! Configuration::updateValue('IPRESTA_PAYLINE_TEST_MODE', 0)
                OR ! Configuration::updateValue('IPRESTA_PAYLINE_DEBUG', 0)
                OR ! $this->registerHook('payment')
                OR ! $this->registerHook('paymentReturn')) {
            return false;
        }
        return true;
    }

    public function uninstall() {
        if (!Configuration::deleteByName('IPRESTA_PAYLINE_PIN')
                OR ! Configuration::deleteByName('PAYLINE_TEST_MODE')
                OR ! Configuration::deleteByName('IPRESTA_PAYLINE_DEBUG')
                OR ! parent::uninstall())
            return false;
        return true;
    }

    public function renderForm() {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Gateway ID'),
                        'name' => 'bank10_GATEWAY_ID',
                        'class' => 'fixed-width-lg',
                        'required' => true,
                        'default' => 'show',
                        'title' => $this->l('Save'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Gateway API'),
                        'name' => 'bank10_GATEWAY_API',
                        'class' => 'fixed-width-lg',
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'bank10Submit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues() {
        return array(
            'bank10_GATEWAY_ID' => Tools::getValue('bank10_GATEWAY_ID', Configuration::get('bank10_GATEWAY_ID')),
            'bank10_GATEWAY_API' => Tools::getValue('bank10_GATEWAY_API', Configuration::get('bank10_GATEWAY_API')),
        );
    }

    public function getContent() {

        $output = '';
        $errors = array();
        if (isset($_POST['bank10Submit'])) {

            if (empty($_POST['bank10_GATEWAY_ID']) || empty($_POST['bank10_GATEWAY_API']))
                $errors[] = $this->l('Your merchant code is required.');

            if (!count($errors)) {
                Configuration::updateValue('bank10_GATEWAY_ID', $_POST['bank10_GATEWAY_ID']);
                Configuration::updateValue('bank10_GATEWAY_API', $_POST['bank10_GATEWAY_API']);

                $output = $this->displayConfirmation($this->l('Your settings have been updated.'));
            } else {
                $output = $this->displayError(implode('<br />', $errors));
            }
        }
        return $output . $this->renderForm();
    }

    protected function curl_func($params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://10bank.ir/transaction/create');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function prePayment() {


        include_once("sender.php");

        $gateway_id = Configuration::get('bank10_GATEWAY_ID');

        $gateway_api = Configuration::get('bank10_GATEWAY_API');



        //Currency
        $purchase_currency = new Currency(Currency::getIdByIsoCode('IRR'));
        $current_currency = new Currency($this->context->cookie->id_currency);

        if ($current_currency->id == $purchase_currency->id)
            $amount = number_format($this->context->cart->getOrderTotal(true, 3), 0, '', '');
        else
            $amount = number_format($this->convertPriceFull($this->context->cart->getOrderTotal(true, 3), $current_currency, $purchase_currency), 0, '', '');


        //redirect_url link

        $redirect_url = urlencode($this->context->link->getModuleLink('bank10payment', 'validation'));


        $rand = substr(md5(time() . microtime()), 0, 10);

        $url = 'http://10bank.ir/transaction/create';


        $params = 'gateway_id=' . $gateway_id . '&amount=' . $amount . '&redirect_url=' . $redirect_url . '&rand=' . $rand;

        $res_num = substr($this->context->cart->id . rand(), -8);

        try {

            $result = $this->curl_func($params);
        } catch (PrestaShopException $e) {
            $this->context->controller->errors[] = $this->l('Could not connect to bank or service.');
            return false;
        }

        if (isset($result) && $result > 0 && is_numeric($result)) {
            $this->context->cookie->__set("RefId", $res_num);
            $this->context->cookie->__set("amount", (int) $amount);
            $this->context->cookie->__set("rand", $rand);
            //d($this->context->cookie->__get("amount"));
            $this->context->smarty->assign(array(
                'redirect_link' => 'http://10bank.ir/transaction/submit?id=' . $result,
                'ref_id' => $result
            ));
            return true;
        }

        //       return $res;
        else {
            $this->context->controller->errors[] = $this->showMessages($result);
            return false;
        }
    }

    public function verify($trans_id,$valid) {


        $purchase_currency = new Currency(Currency::getIdByIsoCode('IRR'));
        $current_currency = new Currency($this->context->cookie->id_currency);




        if ($current_currency->id == $purchase_currency->id)
            $amount = number_format($this->context->cart->getOrderTotal(true, 3), 0, '', '');
        else
            $amount = number_format($this->convertPriceFull($this->context->cart->getOrderTotal(true, 3), $current_currency, $purchase_currency), 0, '', '');
        if ((int) $amount != (int) $this->context->cookie->__get("amount")) {
            $this->context->controller->errors[] = $this->l('Payment amount is incorrect.');
            return false;
        }

        $verify_valid = md5(Configuration::get('bank10_GATEWAY_ID') . $amount . Configuration::get('bank10_GATEWAY_API') . $this->context->cookie->__get("rand")) == $valid;

        if ($verify_valid == true) {
            return true;
        } else {
            $this->context->controller->errors[] = $this->showReturnMessages($result);
            return $result;
        }

        return false;
    }

    public function showMessages($result) {
        $err = $this->l('Error!');
        switch ($result) {
            case -1: $err = $this->l('API ارسالی نامعتبر است');
                break;
            case -2: $err = $this->l('مبلغ تراکنش کمتر از 1000 ریال است');
                break;
            case -3: $err = $this->l('آدرس بازگشت نامعتبر است');
                break;
            case -4: $err = $this->l('تراکنش وجود ندارد یا نامعتبر است');
                break;
        }
        return $err;
    }

    public function showReturnMessages($result) {

        $err = $this->l('Error!');
        switch ($result) {
            case -1: $err = $this->l('API ارسالی نامعتبر است');
                break;
            case -2: $err = $this->l('trans_id .ارسال شده معتبر نمی باشد');
                break;
            case -3: $err = $this->l('id_get ارسال شده معتبر نمی باشد');
                break;
            case -4: $err = $this->l('تراکنش وجود ندارد یا نامعتبر است');
                break;
        }
        return $err;
    }

    public function hookPayment($params) {
        if (!$this->active)
            return;
        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentReturn($params) {
        if (!$this->active)
            return;

        $order = new Order(Tools::getValue('id_order'));

        $this->context->smarty->assign(array(
            'id_order' => Tools::getValue('id_order'),
            'reference' => $order->reference,
            'ref_num' => Tools::getValue('ref_num'),
            'res_num' => Tools::getValue('res_num'),
            'ver' => $this->version,
        ));

        return $this->display(__FILE__, 'confirmation.tpl');
    }

    /**
     *
     * @return float converted amount from a currency to an other currency
     * @param float $amount
     * @param Currency $currency_from if null we used the default currency
     * @param Currency $currency_to if null we used the default currency
     */
    public static function convertPriceFull($amount, Currency $currency_from = null, Currency $currency_to = null) {


        if ($currency_from === $currency_to)
            return $amount;
        if ($currency_from === null)
            $currency_from = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        if ($currency_to === null)
            $currency_to = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        if ($currency_from->id == Configuration::get('PS_CURRENCY_DEFAULT'))
            $amount *= $currency_to->conversion_rate;
        else {
            $conversion_rate = ($currency_from->conversion_rate == 0 ? 1 : $currency_from->conversion_rate);
            // Convert amount to default currency (using the old currency rate)
            $amount = Tools::ps_round($amount / $conversion_rate, 2);
            // Convert to new currency
            $amount *= $currency_to->conversion_rate;
        }
        return Tools::ps_round($amount, 2);
    }

}
